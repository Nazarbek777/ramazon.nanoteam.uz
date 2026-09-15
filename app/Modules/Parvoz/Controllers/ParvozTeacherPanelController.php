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

        $lastGrades = $teacher->grades()
            ->with(['student', 'subject'])
            ->latest('graded_at')
            ->limit(10)
            ->get();

        return view('parvoz.panel', compact('teacher', 'groups', 'ungrouped', 'subjects', 'lastGrades'));
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
            return back()->withErrors(['score' => "Ball noto'g'ri. Masalan: 9 yoki 9/10"])->withInput();
        }

        $score = (float) str_replace(',', '.', $m[1]);
        $max   = isset($m[2]) ? (float) str_replace(',', '.', $m[2]) : null;

        if ($max !== null && $score > $max) {
            return back()->withErrors(['score' => "Ball maksimal balldan katta bo'lishi mumkin emas."])->withInput();
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

        return back()->with('success', "✅ {$student->full_name} — {$grade->scoreLabel()} saqlandi.");
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
