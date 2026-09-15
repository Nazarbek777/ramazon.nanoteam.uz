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

        $groups = $teacher->groups()
            ->with(['students' => fn ($q) => $q->where('is_active', true)->orderBy('full_name')])
            ->orderBy('name')
            ->get();

        // Guruh biriktirilmagan bo'lsa — barcha o'quvchilar;
        // aks holda botdan o'zi ro'yxatdan o'tgan (hali guruhsiz) o'quvchilar ham ko'rinsin
        $ungrouped = $groups->isEmpty()
            ? ParvozStudent::where('is_active', true)->orderBy('full_name')->get()
            : ParvozStudent::whereNull('parvoz_group_id')->where('is_active', true)->orderBy('full_name')->get();

        $subjects = ParvozSubject::orderBy('name')->get();

        $allGroups = \App\Modules\Parvoz\Models\ParvozGroup::where('is_active', true)
            ->with('teachers')
            ->orderBy('name')
            ->get();

        $lastGrades = $teacher->grades()
            ->with(['student', 'subject'])
            ->latest('graded_at')
            ->limit(10)
            ->get();

        return view('parvoz.panel', compact('teacher', 'groups', 'ungrouped', 'subjects', 'allGroups', 'lastGrades'));
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

    /** O'quvchi ismini tahrirlash */
    public function renameStudent(Request $request, ParvozStudent $student)
    {
        $teacher = $this->teacher($request);
        if (!$teacher) {
            return redirect()->route('parvoz.login');
        }
        abort_unless($this->canManage($teacher, $student), 403);

        $data = $request->validate(['full_name' => 'required|string|min:3|max:100']);
        $student->update(['full_name' => $data['full_name']]);

        $msg = "✏️ Ism yangilandi: {$student->full_name}";

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

        $data = $request->validate(['group_id' => 'required|exists:parvoz_groups,id']);

        $group = \App\Modules\Parvoz\Models\ParvozGroup::findOrFail($data['group_id']);
        $student->update(['parvoz_group_id' => $group->id]);

        $msg = "👥 {$student->full_name} — \"{$group->name}\" guruhiga biriktirildi.";

        return $request->wantsJson()
            ? response()->json(['message' => $msg])
            : back()->with('success', $msg);
    }

    /** O'qituvchi shu o'quvchini boshqara oladimi (panelda ko'rinish qoidasi bilan bir xil) */
    protected function canManage(ParvozTeacher $teacher, ParvozStudent $student): bool
    {
        if ($student->parvoz_group_id === null) {
            return true;
        }

        if ($teacher->groups()->count() === 0) {
            return true;
        }

        return $teacher->groups()->where('parvoz_groups.id', $student->parvoz_group_id)->exists();
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
