<?php

namespace App\Modules\Parvoz\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Parvoz\Models\ParvozBot;
use App\Modules\Parvoz\Models\ParvozGrade;
use App\Modules\Parvoz\Models\ParvozStudent;
use App\Modules\Parvoz\Models\ParvozSubject;
use App\Modules\Parvoz\Models\ParvozTeacher;
use App\Modules\Parvoz\Services\ParvozTelegramService;
use Illuminate\Http\Request;

/**
 * O'qituvchi uchun soddalashtirilgan panel: kod bilan kiradi, o'quvchiga ball qo'yadi.
 */
class ParvozTeacherPanelController extends Controller
{
    protected const SESSION_KEY = 'parvoz_teacher_id';

    public function login(Request $request)
    {
        if ($this->teacher($request)) {
            return redirect()->route('parvoz.panel');
        }

        return view('parvoz.login');
    }

    public function loginSubmit(Request $request)
    {
        $request->validate(['code' => 'required|string|max:10']);

        $teacher = ParvozTeacher::where('access_code', trim($request->code))
            ->where('is_active', true)
            ->first();

        if (!$teacher) {
            return back()->withErrors(['code' => "Kod noto'g'ri. Administratordan tekshiring."]);
        }

        $request->session()->put(self::SESSION_KEY, $teacher->id);

        return redirect()->route('parvoz.panel');
    }

    public function logout(Request $request)
    {
        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('parvoz.login');
    }

    public function panel(Request $request)
    {
        $teacher = $this->teacher($request);
        if (!$teacher) {
            return redirect()->route('parvoz.login');
        }

        // Barcha guruhlar (bo'shlari ham) — panel to'liq nazorat beradi
        $groups = \App\Modules\Parvoz\Models\ParvozGroup::where('is_active', true)
            ->with([
                'students' => fn ($q) => $q->where('is_active', true)->orderBy('full_name'),
                'teachers',
            ])
            ->orderBy('name')
            ->get();

        $ungrouped = ParvozStudent::whereNull('parvoz_group_id')
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get();

        $subjects = ParvozSubject::orderBy('name')->get();

        $allGroups = \App\Modules\Parvoz\Models\ParvozGroup::where('is_active', true)
            ->with('teachers')
            ->orderBy('name')
            ->get();

        $allStudents = ParvozStudent::where('is_active', true)
            ->with('group')
            ->orderBy('full_name')
            ->get();

        $teachers = ParvozTeacher::where('is_active', true)
            ->withCount('grades')
            ->orderBy('full_name')
            ->get();

        $myGroupIds = $teacher->groups()->pluck('parvoz_groups.id')->all();

        $lastGrades = $teacher->grades()
            ->with(['student', 'subject'])
            ->latest('graded_at')
            ->limit(10)
            ->get();

        return view('parvoz.panel', compact(
            'teacher', 'groups', 'ungrouped', 'subjects', 'allGroups', 'allStudents', 'teachers', 'myGroupIds', 'lastGrades'
        ));
    }

    public function storeGrade(Request $request)
    {
        $teacher = $this->teacher($request);
        if (!$teacher) {
            return redirect()->route('parvoz.login');
        }

        $data = $request->validate([
            'student_id' => 'required|exists:parvoz_students,id',
            'score'      => 'required|string|max:20',
            'subject_id' => 'nullable|exists:parvoz_subjects,id',
            'comment'    => 'nullable|string|max:500',
        ]);

        // "9", "9.5" yoki "9/10" formatini qabul qilamiz (botdagi bilan bir xil)
        if (!preg_match('/^(\d+(?:[.,]\d+)?)(?:\s*\/\s*(\d+(?:[.,]\d+)?))?$/u', trim($data['score']), $m)) {
            $msg = "Ball noto'g'ri. Masalan: 9 yoki 9/10";
            return $request->wantsJson()
                ? response()->json(['message' => $msg], 422)
                : back()->withErrors(['score' => $msg])->withInput();
        }

        $score = (float) str_replace(',', '.', $m[1]);
        $max   = isset($m[2]) ? (float) str_replace(',', '.', $m[2]) : null;

        if ($max !== null && $score > $max) {
            $msg = "Ball maksimal balldan katta bo'lishi mumkin emas.";
            return $request->wantsJson()
                ? response()->json(['message' => $msg], 422)
                : back()->withErrors(['score' => $msg])->withInput();
        }

        $student = ParvozStudent::findOrFail($data['student_id']);

        if (!$student->parvoz_group_id) {
            $msg = "❌ {$student->full_name} hech qaysi guruhda emas. Avval guruhga qo'shing.";
            return $request->wantsJson()
                ? response()->json(['message' => $msg], 422)
                : back()->withErrors(['student_id' => $msg]);
        }

        $grade = ParvozGrade::create([
            'parvoz_student_id' => $student->id,
            'parvoz_teacher_id' => $teacher->id,
            'parvoz_subject_id' => $data['subject_id'] ?? null,
            'score'             => $score,
            'max_score'         => $max,
            'comment'           => $data['comment'] ?? null,
            'graded_at'         => now(),
        ]);

        $this->notifyStudent($student, $teacher, $grade);

        $msg = "✅ {$student->full_name} — {$grade->scoreLabel()} saqlandi.";

        return $request->wantsJson()
            ? response()->json(['message' => $msg])
            : back()->with('success', $msg);
    }

    /** Qo'lda yangi o'quvchi qo'shish (to'g'ridan-to'g'ri guruhga) */
    public function storeStudent(Request $request)
    {
        $teacher = $this->teacher($request);
        if (!$teacher) {
            return redirect()->route('parvoz.login');
        }

        $data = $request->validate([
            'full_name' => 'required|string|min:3|max:100',
            'phone'     => 'nullable|string|max:30',
            'group_id'  => 'nullable|exists:parvoz_groups,id',
        ]);

        // Shu raqamli o'quvchi allaqachon bormi?
        $digits = substr(preg_replace('/\D/', '', (string) ($data['phone'] ?? '')), -9);

        if ($digits !== '') {
            $exists = ParvozStudent::where('phone', 'like', '%' . $digits)->first();

            if ($exists) {
                $msg = "❌ Bu raqam allaqachon ro'yxatda: {$exists->full_name}";
                return $request->wantsJson()
                    ? response()->json(['message' => $msg], 422)
                    : back()->withErrors(['phone' => $msg]);
            }
        }

        $student = ParvozStudent::create([
            'full_name'       => $data['full_name'],
            'phone'           => $data['phone'] ?? null,
            'parvoz_group_id' => $data['group_id'] ?? null,
        ]);

        $msg = "✅ {$student->full_name} qo'shildi."
            . ($digits !== '' ? " Botga shu raqam bilan kirsa, kabineti avtomatik ochiladi." : '');

        return $request->wantsJson()
            ? response()->json(['message' => $msg, 'id' => $student->id])
            : back()->with('success', $msg);
    }

    /** O'quvchi ismini tahrirlash */
    public function renameStudent(Request $request, ParvozStudent $student)
    {
        $teacher = $this->teacher($request);
        if (!$teacher) {
            return redirect()->route('parvoz.login');
        }
        abort_unless($this->canManage($teacher, $student), 403);

        $data = $request->validate([
            'full_name' => 'required|string|min:3|max:100',
            'phone'     => 'nullable|string|max:30',
        ]);

        $student->full_name = $data['full_name'];

        if ($request->has('phone')) {
            $student->phone = $data['phone'] ?? null;
        }

        $student->save();

        $msg = "✏️ Saqlandi: {$student->full_name}";

        return $request->wantsJson()
            ? response()->json(['message' => $msg, 'full_name' => $student->full_name])
            : back()->with('success', $msg);
    }

    /** O'quvchini bloklash (panel va botdan yashiriladi) */
    public function blockStudent(Request $request, ParvozStudent $student)
    {
        $teacher = $this->teacher($request);
        if (!$teacher) {
            return redirect()->route('parvoz.login');
        }
        abort_unless($this->canManage($teacher, $student), 403);

        $student->update(['is_active' => false]);

        $msg = "🚫 {$student->full_name} bloklandi. (Qayta ochish — admin panelda)";

        return $request->wantsJson()
            ? response()->json(['message' => $msg])
            : back()->with('success', $msg);
    }

    /** O'quvchini guruhga biriktirish */
    public function assignGroup(Request $request, ParvozStudent $student)
    {
        $teacher = $this->teacher($request);
        if (!$teacher) {
            return redirect()->route('parvoz.login');
        }
        abort_unless($this->canManage($teacher, $student), 403);

        $data = $request->validate(['group_id' => 'nullable|exists:parvoz_groups,id']);

        if (empty($data['group_id'])) {
            $student->update(['parvoz_group_id' => null]);
            $msg = "↩️ {$student->full_name} guruhdan chiqarildi.";
        } else {
            $group = \App\Modules\Parvoz\Models\ParvozGroup::findOrFail($data['group_id']);
            $student->update(['parvoz_group_id' => $group->id]);
            $msg = "👥 {$student->full_name} — \"{$group->name}\" guruhiga qo'shildi.";
        }

        return $request->wantsJson()
            ? response()->json(['message' => $msg])
            : back()->with('success', $msg);
    }

    /** Panelga kod bilan kirgan o'qituvchi barcha o'quvchilarni boshqara oladi (to'liq nazorat) */
    protected function canManage(ParvozTeacher $teacher, ParvozStudent $student): bool
    {
        return true;
    }

    // ─────────────────────────────────────────────── O'qituvchi boshqaruvi

    /** Yangi o'qituvchi qo'shish (kirish kodi avtomatik yaratiladi) */
    public function storeTeacher(Request $request)
    {
        if (!$this->teacher($request)) {
            return redirect()->route('parvoz.login');
        }

        $data = $request->validate([
            'full_name' => 'required|string|min:3|max:100',
            'phone'     => 'nullable|string|max:30',
        ]);

        $new = ParvozTeacher::create([
            'full_name' => $data['full_name'],
            'phone'     => $data['phone'] ?? null,
        ]);

        $msg = "✅ {$new->full_name} qo'shildi. Kirish kodi: {$new->access_code}";

        return $request->wantsJson()
            ? response()->json(['message' => $msg, 'id' => $new->id, 'code' => $new->access_code])
            : back()->with('success', $msg);
    }

    /** O'qituvchi ma'lumotlarini tahrirlash */
    public function updateTeacher(Request $request, ParvozTeacher $target)
    {
        if (!$this->teacher($request)) {
            return redirect()->route('parvoz.login');
        }

        $data = $request->validate([
            'full_name' => 'required|string|min:3|max:100',
            'phone'     => 'nullable|string|max:30',
        ]);

        $target->update([
            'full_name' => $data['full_name'],
            'phone'     => $data['phone'] ?? null,
        ]);

        $msg = "✏️ Saqlandi: {$target->full_name}";

        return $request->wantsJson()
            ? response()->json(['message' => $msg, 'full_name' => $target->full_name])
            : back()->with('success', $msg);
    }

    /** O'qituvchiga yangi kirish kodi berish */
    public function resetTeacherCode(Request $request, ParvozTeacher $target)
    {
        if (!$this->teacher($request)) {
            return redirect()->route('parvoz.login');
        }

        $target->update(['access_code' => ParvozTeacher::generateAccessCode()]);

        $msg = "🔑 {$target->full_name} uchun yangi kod: {$target->access_code}";

        return $request->wantsJson()
            ? response()->json(['message' => $msg, 'code' => $target->access_code])
            : back()->with('success', $msg);
    }

    /** O'qituvchini o'chirish (o'zini o'chira olmaydi; qo'ygan ballari saqlanib qoladi) */
    public function deleteTeacher(Request $request, ParvozTeacher $target)
    {
        $teacher = $this->teacher($request);
        if (!$teacher) {
            return redirect()->route('parvoz.login');
        }

        if ($target->id === $teacher->id) {
            $msg = "❌ O'zingizni o'chira olmaysiz.";
            return $request->wantsJson()
                ? response()->json(['message' => $msg], 422)
                : back()->withErrors(['teacher' => $msg]);
        }

        $name = $target->full_name;
        $target->delete();

        $msg = "🗑 {$name} o'chirildi. (Qo'ygan ballari saqlanib qoldi)";

        return $request->wantsJson()
            ? response()->json(['message' => $msg])
            : back()->with('success', $msg);
    }

    // ─────────────────────────────────────────────── Guruh boshqaruvi

    /** Yangi guruh yaratish (yaratgan o'qituvchi unga avtomatik biriktiriladi) */
    public function storeGroup(Request $request)
    {
        $teacher = $this->teacher($request);
        if (!$teacher) {
            return redirect()->route('parvoz.login');
        }

        $data = $request->validate([
            'name'       => 'required|string|min:2|max:100|unique:parvoz_groups,name',
            'teacher_id' => 'nullable|exists:parvoz_teachers,id',
        ]);

        $group = \App\Modules\Parvoz\Models\ParvozGroup::create(['name' => $data['name'], 'is_active' => true]);
        $group->teachers()->sync([$data['teacher_id'] ?? $teacher->id]);

        $msg = "✅ \"{$group->name}\" guruhi yaratildi.";

        return $request->wantsJson()
            ? response()->json(['message' => $msg, 'id' => $group->id])
            : back()->with('success', $msg);
    }

    /** Guruh nomini o'zgartirish */
    public function renameGroup(Request $request, \App\Modules\Parvoz\Models\ParvozGroup $group)
    {
        $teacher = $this->teacher($request);
        if (!$teacher) {
            return redirect()->route('parvoz.login');
        }

        $data = $request->validate([
            'name'       => 'required|string|min:2|max:100|unique:parvoz_groups,name,' . $group->id,
            'teacher_id' => 'nullable|exists:parvoz_teachers,id',
        ]);

        $group->update(['name' => $data['name']]);

        if ($request->has('teacher_id')) {
            $group->teachers()->sync(array_filter([$data['teacher_id'] ?? null]));
        }

        $msg = "✏️ Guruh saqlandi: {$group->name}";

        return $request->wantsJson()
            ? response()->json(['message' => $msg])
            : back()->with('success', $msg);
    }

    /** Guruhni o'chirish (o'quvchilari guruhsiz bo'lib qoladi, o'chmaydi) */
    public function deleteGroup(Request $request, \App\Modules\Parvoz\Models\ParvozGroup $group)
    {
        $teacher = $this->teacher($request);
        if (!$teacher) {
            return redirect()->route('parvoz.login');
        }

        $name = $group->name;
        $group->delete();

        $msg = "🗑 \"{$name}\" guruhi o'chirildi. O'quvchilari guruhsiz bo'limiga o'tdi.";

        return $request->wantsJson()
            ? response()->json(['message' => $msg])
            : back()->with('success', $msg);
    }

    /** O'quvchiga bot orqali xabar (bot ulanmagan bo'lsa jimgina o'tib ketadi) */
    protected function notifyStudent(ParvozStudent $student, ParvozTeacher $teacher, ParvozGrade $grade): void
    {
        if (!$student->telegram_id) {
            return;
        }

        $bot = ParvozBot::where('is_active', true)->first();
        if (!$bot) {
            return;
        }

        $subjectName = $grade->subject?->name ?? 'Umumiy';

        try {
            (new ParvozTelegramService($bot->token))->sendMessage(
                $student->telegram_id,
                "📊 <b>Sizga yangi baho qo'yildi!</b>\n\n" .
                "📚 Fan: {$subjectName}\n" .
                "⭐ Ball: <b>{$grade->scoreLabel()}</b>\n" .
                "👨‍🏫 O'qituvchi: {$teacher->full_name}" .
                ($grade->comment ? "\n💬 Izoh: {$grade->comment}" : '')
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('[Parvoz] Student notify failed: ' . $e->getMessage());
        }
    }

    protected function teacher(Request $request): ?ParvozTeacher
    {
        $id = $request->session()->get(self::SESSION_KEY);

        return $id
            ? ParvozTeacher::where('id', $id)->where('is_active', true)->first()
            : null;
    }
}
