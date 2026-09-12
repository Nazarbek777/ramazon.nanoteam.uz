<?php

namespace App\Modules\Parvoz\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Modules\Parvoz\Models\ParvozBot;
use App\Modules\Parvoz\Models\ParvozGrade;
use App\Modules\Parvoz\Models\ParvozGroup;
use App\Modules\Parvoz\Models\ParvozState;
use App\Modules\Parvoz\Models\ParvozStudent;
use App\Modules\Parvoz\Models\ParvozSubject;
use App\Modules\Parvoz\Models\ParvozTeacher;
use App\Modules\Parvoz\Services\ParvozTelegramService;

class ParvozWebhookController
{
    protected ParvozTelegramService $telegram;
    protected ParvozBot $bot;

    // Menyu tugmalari
    private const BTN_ADD_GRADE  = '➕ Ball kiritish';
    private const BTN_MY_GROUPS  = '👥 Mening guruhlarim';
    private const BTN_LAST       = '🕘 Oxirgi baholar';
    private const BTN_MY_GRADES  = '📊 Baholarim';
    private const BTN_AVERAGE    = '📈 O\'rtacha ball';
    private const BTN_RATING     = '🏆 Reyting';
    private const BTN_PROFILE    = '👤 Profil';
    private const BTN_CANCEL     = '❌ Bekor qilish';

    public function handle(Request $request, int $botId): JsonResponse
    {
        $this->bot = ParvozBot::findOrFail($botId);

        if ($this->bot->webhook_secret && $this->bot->webhook_secret !== $request->query('secret')) {
            Log::warning('[ParvozBot] Invalid webhook secret', ['bot_id' => $botId]);
            return response()->json(['ok' => false], 403);
        }

        if (!$this->bot->is_active) {
            return response()->json(['ok' => true, 'message' => 'Bot inactive']);
        }

        $this->telegram = new ParvozTelegramService($this->bot->token);

        $update = $request->all();

        if (isset($update['message'])) {
            $this->onMessage($update['message']);
        } elseif (isset($update['callback_query'])) {
            $this->onCallback($update['callback_query']);
        }

        return response()->json(['ok' => true]);
    }

    // ─────────────────────────────────────────────── Xabarlar

    protected function onMessage(array $msg): void
    {
        $chatId = $msg['chat']['id'];
        $text   = trim($msg['text'] ?? '');

        if (isset($msg['contact'])) {
            $this->onContact($chatId, $msg['contact']);
            return;
        }

        if (str_starts_with($text, '/start')) {
            $this->onStart($chatId);
            return;
        }

        $teacher = $this->findTeacher($chatId);
        $student = $teacher ? null : $this->findStudent($chatId);

        if (!$teacher && !$student) {
            $this->askPhone($chatId);
            return;
        }

        if ($text === self::BTN_CANCEL) {
            ParvozState::for($chatId)->clear();
            $teacher ? $this->teacherMenu($chatId, '❌ Bekor qilindi.') : $this->studentMenu($chatId, '❌ Bekor qilindi.');
            return;
        }

        // Ball kiritish sehrgarining oxirgi bosqichi — matn kutilmoqda
        $state = ParvozState::for($chatId);
        if ($teacher && in_array($state->state, ['awaiting_score', 'awaiting_comment'], true)) {
            $this->onWizardText($chatId, $teacher, $state, $text);
            return;
        }

        if ($teacher) {
            match ($text) {
                self::BTN_ADD_GRADE => $this->startGradeWizard($chatId, $teacher),
                self::BTN_MY_GROUPS => $this->teacherGroups($chatId, $teacher),
                self::BTN_LAST      => $this->teacherLastGrades($chatId, $teacher),
                default             => $this->teacherMenu($chatId, '🤔 Quyidagi tugmalardan foydalaning:'),
            };
            return;
        }

        match ($text) {
            self::BTN_MY_GRADES => $this->studentGrades($chatId, $student),
            self::BTN_AVERAGE   => $this->studentAverage($chatId, $student),
            self::BTN_RATING    => $this->studentRating($chatId, $student),
            self::BTN_PROFILE   => $this->studentProfile($chatId, $student),
            default             => $this->studentMenu($chatId, '🤔 Quyidagi tugmalardan foydalaning:'),
        };
    }

    protected function onStart(int $chatId): void
    {
        if ($teacher = $this->findTeacher($chatId)) {
            $this->teacherMenu($chatId, "👋 Assalomu alaykum, <b>{$teacher->full_name}</b>!\n\n👨‍🏫 O'qituvchi kabineti.");
            return;
        }

        if ($student = $this->findStudent($chatId)) {
            $this->studentMenu($chatId, "👋 Assalomu alaykum, <b>{$student->full_name}</b>!\n\n🎓 Shaxsiy kabinetingiz.");
            return;
        }

        $this->askPhone($chatId);
    }

    protected function askPhone(int $chatId): void
    {
        $this->telegram->sendContactRequest(
            $chatId,
            "👋 <b>Parvoz o'quv markazi</b>ga xush kelibsiz!\n\n" .
            "📱 Kabinetingizga kirish uchun telefon raqamingizni yuboring."
        );
    }

    /** Telefon raqam bo'yicha o'quvchi/o'qituvchini bog'lash */
    protected function onContact(int $chatId, array $contact): void
    {
        $phone = $this->normalizePhone($contact['phone_number'] ?? '');

        if ($phone === '') {
            $this->telegram->sendMessage($chatId, "❌ Raqam olinmadi. Qaytadan urinib ko'ring.");
            return;
        }

        $teacher = ParvozTeacher::where('is_active', true)
            ->get()
            ->first(fn ($t) => $this->normalizePhone($t->phone) === $phone);

        if ($teacher) {
            $teacher->update(['telegram_id' => (string) $chatId]);
            $this->teacherMenu($chatId, "✅ Xush kelibsiz, <b>{$teacher->full_name}</b>!\n\n👨‍🏫 O'qituvchi kabineti ochildi.");
            return;
        }

        $student = ParvozStudent::where('is_active', true)
            ->get()
            ->first(fn ($s) => $this->normalizePhone($s->phone) === $phone);

        if ($student) {
            $student->update(['telegram_id' => (string) $chatId]);
            $this->studentMenu($chatId, "✅ Xush kelibsiz, <b>{$student->full_name}</b>!\n\n🎓 Kabinetingiz ochildi.");
            return;
        }

        $this->telegram->sendMessage(
            $chatId,
            "❌ Bu raqam ro'yxatda topilmadi.\n\n" .
            "Iltimos, o'quv markazi administratoriga murojaat qiling."
        );
    }

    // ─────────────────────────────────────────────── Menyular

    protected function teacherMenu(int $chatId, string $text): void
    {
        $this->telegram->sendWithMenu($chatId, $text, [
            [['text' => self::BTN_ADD_GRADE]],
            [['text' => self::BTN_MY_GROUPS], ['text' => self::BTN_LAST]],
        ]);
    }

    protected function studentMenu(int $chatId, string $text): void
    {
        $this->telegram->sendWithMenu($chatId, $text, [
            [['text' => self::BTN_MY_GRADES], ['text' => self::BTN_AVERAGE]],
            [['text' => self::BTN_RATING], ['text' => self::BTN_PROFILE]],
        ]);
    }

    // ─────────────────────────────────────────────── O'qituvchi: ball kiritish

    protected function startGradeWizard(int $chatId, ParvozTeacher $teacher): void
    {
        $groups = $teacher->groups()->where('is_active', true)->orderBy('name')->get();

        if ($groups->isEmpty()) {
            $this->telegram->sendMessage($chatId, "❌ Sizga guruh biriktirilmagan. Administratorga murojaat qiling.");
            return;
        }

        ParvozState::for($chatId)->set('choosing_group');

        $this->telegram->sendWithInline(
            $chatId,
            "👥 <b>Guruhni tanlang:</b>",
            $groups->map(fn ($g) => [['text' => $g->name, 'callback_data' => "g:{$g->id}"]])->all()
        );
    }

    protected function onCallback(array $cb): void
    {
        $chatId = $cb['message']['chat']['id'] ?? null;
        $data   = $cb['data'] ?? '';

        $this->telegram->answerCallbackQuery($cb['id']);

        if (!$chatId) return;

        $teacher = $this->findTeacher($chatId);
        if (!$teacher) return;

        $state = ParvozState::for($chatId);
        [$type, $id] = array_pad(explode(':', $data, 2), 2, null);

        match ($type) {
            'g' => $this->chooseStudent($chatId, $state, (int) $id),
            's' => $this->chooseSubject($chatId, $state, (int) $id),
            'f' => $this->askScore($chatId, $state, (int) $id),
            default => null,
        };
    }

    protected function chooseStudent(int $chatId, ParvozState $state, int $groupId): void
    {
        $students = ParvozStudent::where('parvoz_group_id', $groupId)
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get();

        if ($students->isEmpty()) {
            $this->telegram->sendMessage($chatId, "❌ Bu guruhda o'quvchi yo'q.");
            return;
        }

        $state->set('choosing_student', ['group_id' => $groupId]);

        $this->telegram->sendWithInline(
            $chatId,
            "🎓 <b>O'quvchini tanlang:</b>",
            $students->map(fn ($s) => [['text' => $s->full_name, 'callback_data' => "s:{$s->id}"]])->all()
        );
    }

    protected function chooseSubject(int $chatId, ParvozState $state, int $studentId): void
    {
        $subjects = ParvozSubject::where('is_active', true)->orderBy('name')->get();
        $payload  = array_merge($state->payload ?? [], ['student_id' => $studentId]);

        if ($subjects->isEmpty()) {
            // Fan yo'q bo'lsa — to'g'ridan-to'g'ri ball so'raymiz
            $state->set('awaiting_score', $payload);
            $this->promptScore($chatId, $studentId);
            return;
        }

        $state->set('choosing_subject', $payload);

        $this->telegram->sendWithInline(
            $chatId,
            "📚 <b>Fanni tanlang:</b>",
            $subjects->map(fn ($f) => [['text' => $f->name, 'callback_data' => "f:{$f->id}"]])->all()
        );
    }

    protected function askScore(int $chatId, ParvozState $state, int $subjectId): void
    {
        $payload = array_merge($state->payload ?? [], ['subject_id' => $subjectId]);
        $state->set('awaiting_score', $payload);

        $this->promptScore($chatId, $payload['student_id'] ?? null);
    }

    protected function promptScore(int $chatId, ?int $studentId): void
    {
        $name = $studentId ? (ParvozStudent::find($studentId)?->full_name ?? '') : '';

        $this->telegram->sendWithMenu(
            $chatId,
            "✍️ <b>{$name}</b> uchun ballni yozing.\n\n" .
            "Masalan: <code>9</code> yoki <code>9/10</code> yoki <code>85/100</code>",
            [[['text' => self::BTN_CANCEL]]]
        );
    }

    /** Ball va izoh matnini qabul qilish */
    protected function onWizardText(int $chatId, ParvozTeacher $teacher, ParvozState $state, string $text): void
    {
        $payload = $state->payload ?? [];

        if ($state->state === 'awaiting_score') {
            if (!preg_match('~^\s*(\d+(?:[.,]\d+)?)\s*(?:/\s*(\d+(?:[.,]\d+)?))?\s*$~u', $text, $m)) {
                $this->telegram->sendMessage($chatId, "❌ Ball noto'g'ri. Masalan: <code>9</code> yoki <code>9/10</code>");
                return;
            }

            $score = (float) str_replace(',', '.', $m[1]);
            $max   = isset($m[2]) ? (float) str_replace(',', '.', $m[2]) : null;

            if ($max !== null && $score > $max) {
                $this->telegram->sendMessage($chatId, "❌ Ball maksimal balldan katta bo'lishi mumkin emas.");
                return;
            }

            $payload['score']     = $score;
            $payload['max_score'] = $max;
            $state->set('awaiting_comment', $payload);

            $this->telegram->sendWithMenu(
                $chatId,
                "💬 Izoh yozing (ixtiyoriy).\n\nIzoh kerak bo'lmasa <code>-</code> yuboring.",
                [[['text' => self::BTN_CANCEL]]]
            );
            return;
        }

        // awaiting_comment
        $comment = ($text === '-' || $text === '') ? null : mb_substr($text, 0, 255);
        $this->saveGrade($chatId, $teacher, $payload, $comment);
        $state->clear();
    }

    protected function saveGrade(int $chatId, ParvozTeacher $teacher, array $payload, ?string $comment): void
    {
        $student = ParvozStudent::find($payload['student_id'] ?? null);

        if (!$student) {
            $this->teacherMenu($chatId, "❌ O'quvchi topilmadi. Qaytadan boshlang.");
            return;
        }

        $grade = ParvozGrade::create([
            'parvoz_student_id' => $student->id,
            'parvoz_teacher_id' => $teacher->id,
            'parvoz_subject_id' => $payload['subject_id'] ?? null,
            'score'             => $payload['score'],
            'max_score'         => $payload['max_score'] ?? null,
            'comment'           => $comment,
            'graded_at'         => now(),
        ]);

        $subjectName = $grade->subject?->name ?? 'Umumiy';

        $this->teacherMenu(
            $chatId,
            "✅ <b>Ball saqlandi!</b>\n\n" .
            "🎓 {$student->full_name}\n" .
            "📚 {$subjectName}\n" .
            "⭐ {$grade->scoreLabel()}" .
            ($comment ? "\n💬 {$comment}" : '')
        );

        // O'quvchiga xabar
        if ($student->telegram_id) {
            $this->telegram->sendMessage(
                $student->telegram_id,
                "📊 <b>Sizga yangi baho qo'yildi!</b>\n\n" .
                "📚 Fan: {$subjectName}\n" .
                "⭐ Ball: <b>{$grade->scoreLabel()}</b>\n" .
                "👨‍🏫 O'qituvchi: {$teacher->full_name}" .
                ($comment ? "\n💬 Izoh: {$comment}" : '')
            );
        }
    }

    // ─────────────────────────────────────────────── O'qituvchi: ko'rish

    protected function teacherGroups(int $chatId, ParvozTeacher $teacher): void
    {
        $groups = $teacher->groups()->withCount('students')->orderBy('name')->get();

        if ($groups->isEmpty()) {
            $this->telegram->sendMessage($chatId, "❌ Sizga guruh biriktirilmagan.");
            return;
        }

        $text = "👥 <b>Mening guruhlarim:</b>\n\n";
        foreach ($groups as $i => $g) {
            $text .= ($i + 1) . ". {$g->name} — {$g->students_count} ta o'quvchi\n";
        }

        $this->telegram->sendMessage($chatId, $text);
    }

    protected function teacherLastGrades(int $chatId, ParvozTeacher $teacher): void
    {
        $grades = $teacher->grades()
            ->with(['student', 'subject'])
            ->latest('graded_at')
            ->limit(15)
            ->get();

        if ($grades->isEmpty()) {
            $this->telegram->sendMessage($chatId, "📭 Siz hali ball kiritmagansiz.");
            return;
        }

        $text = "🕘 <b>Oxirgi baholar:</b>\n\n";
        foreach ($grades as $g) {
            $date = $g->graded_at?->format('d.m.Y') ?? '';
            $text .= "• {$g->student?->full_name} — <b>{$g->scoreLabel()}</b> ({$g->subject?->name}) {$date}\n";
        }

        $this->telegram->sendMessage($chatId, $text);
    }

    // ─────────────────────────────────────────────── O'quvchi kabineti

    protected function studentGrades(int $chatId, ParvozStudent $student): void
    {
        $grades = $student->grades()->with(['subject', 'teacher'])->latest('graded_at')->limit(30)->get();

        if ($grades->isEmpty()) {
            $this->telegram->sendMessage($chatId, "📭 Sizda hali baho yo'q.");
            return;
        }

        $text = "📊 <b>Baholaringiz:</b>\n\n";
        foreach ($grades->groupBy(fn ($g) => $g->subject?->name ?? 'Umumiy') as $subject => $rows) {
            $text .= "📚 <b>{$subject}</b>\n";
            foreach ($rows as $g) {
                $date = $g->graded_at?->format('d.m.Y') ?? '';
                $text .= "   • <b>{$g->scoreLabel()}</b> — {$date}" . ($g->comment ? " ({$g->comment})" : '') . "\n";
            }
            $text .= "\n";
        }

        $this->telegram->sendMessage($chatId, $text);
    }

    protected function studentAverage(int $chatId, ParvozStudent $student): void
    {
        $count = $student->grades()->count();

        if ($count === 0) {
            $this->telegram->sendMessage($chatId, "📭 Sizda hali baho yo'q.");
            return;
        }

        $text = "📈 <b>O'rtacha ball</b>\n\n";
        $text .= "⭐ Umumiy o'rtacha: <b>{$student->averageScore()}</b>\n";

        if ($percent = $student->averagePercent()) {
            $text .= "📊 Foizda: <b>{$percent}%</b>\n";
        }

        $text .= "🗂 Jami baholar: {$count} ta\n\n";

        // Fan bo'yicha
        $bySubject = $student->grades()->with('subject')->get()
            ->groupBy(fn ($g) => $g->subject?->name ?? 'Umumiy');

        if ($bySubject->count() > 1) {
            $text .= "<b>Fanlar bo'yicha:</b>\n";
            foreach ($bySubject as $subject => $rows) {
                $avg = round($rows->avg('score'), 2);
                $text .= "   • {$subject}: <b>{$avg}</b>\n";
            }
        }

        $this->telegram->sendMessage($chatId, $text);
    }

    protected function studentRating(int $chatId, ParvozStudent $student): void
    {
        if (!$student->parvoz_group_id) {
            $this->telegram->sendMessage($chatId, "❌ Siz guruhga biriktirilmagansiz.");
            return;
        }

        $students = ParvozStudent::where('parvoz_group_id', $student->parvoz_group_id)
            ->where('is_active', true)
            ->withAvg('grades', 'score')
            ->orderByDesc('grades_avg_score')
            ->get();

        $group = ParvozGroup::find($student->parvoz_group_id);
        $text  = "🏆 <b>Reyting — {$group?->name}</b>\n\n";

        foreach ($students as $i => $s) {
            $avg   = $s->grades_avg_score ? round((float) $s->grades_avg_score, 2) : '—';
            $medal = match ($i) { 0 => '🥇', 1 => '🥈', 2 => '🥉', default => ($i + 1) . '.' };
            $me    = $s->id === $student->id ? ' 👈' : '';
            $text .= "{$medal} {$s->full_name} — <b>{$avg}</b>{$me}\n";
        }

        $this->telegram->sendMessage($chatId, $text);
    }

    protected function studentProfile(int $chatId, ParvozStudent $student): void
    {
        $this->telegram->sendMessage(
            $chatId,
            "👤 <b>Profil</b>\n\n" .
            "🎓 F.I.O: {$student->full_name}\n" .
            "👥 Guruh: " . ($student->group?->name ?? '—') . "\n" .
            "📱 Telefon: " . ($student->phone ?? '—') . "\n" .
            "🗂 Baholar: {$student->grades()->count()} ta"
        );
    }

    // ─────────────────────────────────────────────── Yordamchilar

    protected function findTeacher(int $chatId): ?ParvozTeacher
    {
        return ParvozTeacher::where('telegram_id', (string) $chatId)->where('is_active', true)->first();
    }

    protected function findStudent(int $chatId): ?ParvozStudent
    {
        return ParvozStudent::where('telegram_id', (string) $chatId)->where('is_active', true)->first();
    }

    /** Raqamni solishtirish uchun oxirgi 9 ta raqamni olamiz (+998 bor-yo'qligidan qat'i nazar) */
    protected function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);
        return $digits === '' ? '' : substr($digits, -9);
    }
}
