<?php

namespace App\Modules\Book\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Modules\Book\Services\TelegramService;
use App\Modules\Book\Services\BookService;
use App\Modules\Book\Models\BookUser;

class WebhookController
{
    protected TelegramService $telegram;
    protected BookService $bookService;

    protected array $requiredChannels = [
        ['username' => '@nurkitoblari_m', 'name' => '📢 Nur kitoblar'],
    ];

    public function __construct()
    {
        $this->telegram = new TelegramService();
        $this->bookService = new BookService();
    }

    public function handle(Request $request): JsonResponse
    {
        $update = $request->all();

        Log::info('[BookBot] Update', ['id' => $update['update_id'] ?? '-']);

        try {
            if (isset($update['message'])) {
                $this->onMessage($update['message']);
            } elseif (isset($update['callback_query'])) {
                $this->onCallback($update['callback_query']);
            }
        } catch (\Exception $e) {
            Log::error('[BookBot] ' . $e->getMessage(), ['line' => $e->getLine()]);
        }

        return response()->json(['ok' => true]);
    }

    // ── MESSAGE ──────────────────────────────────────────

    protected function onMessage(array $msg): void
    {
        $chatId = $msg['chat']['id'];
        $text = trim($msg['text'] ?? '');
        $from = $msg['from'] ?? [];
        $userId = $from['id'] ?? $chatId;

        if (isset($msg['contact'])) {
            $this->onContact($chatId, $msg['contact'], $from, $msg['message_id'] ?? 0);
            return;
        }

        if (str_starts_with($text, '/start')) {
            $this->onStart($chatId, $text, $from);
            return;
        }

        // Barcha tugmalar uchun kanalga a'zolikni tekshirish
        if (!$this->isJoinedAll($userId)) {
            $this->askJoinChannel($chatId);
            return;
        }

        switch ($text) {
            case '🏆 Reyting':
                $this->onLeaderboard($chatId);
                break;
            case '👤 Profil':
                $this->onProfile($chatId, $from);
                break;
            case '🔗 Taklif qilish':
                $this->onReferral($chatId, $from);
                break;
            case '🎁 Sovrinlar':
                $this->sendAfisha($chatId);
                break;
            case '📋 Yoʻriqnoma':
                $this->sendYoriqnoma($chatId);
                break;
        }
    }

    // ── CALLBACK ─────────────────────────────────────────

    protected function onCallback(array $cb): void
    {
        $chatId = $cb['message']['chat']['id'] ?? null;
        $data = $cb['data'] ?? '';
        $from = $cb['from'] ?? [];

        if (!$chatId)
            return;

        $this->telegram->answerCallbackQuery($cb['id']);

        if ($data === 'check_channels') {
            $this->onCheckChannels($chatId, $from);
        }
    }

    // ── /start ───────────────────────────────────────────

    protected function onStart(int $chatId, string $text, array $from): void
    {
        $referrerId = null;
        if (str_contains($text, ' ')) {
            $parts = explode(' ', $text);
            if (isset($parts[1]) && is_numeric($parts[1])) {
                $referrerId = (int) $parts[1];
            }
        }

        $user = $this->bookService->getOrCreateUser([
            'id' => $from['id'] ?? $chatId,
            'username' => $from['username'] ?? null,
            'first_name' => $from['first_name'] ?? '',
            'last_name' => $from['last_name'] ?? '',
        ], $referrerId);

        // Telefon yo'q — ro'yxatdan o'tish (Motivation + Contact)
        if (empty($user->phone)) {
            $this->telegram->sendContactRequest(
                $chatId,
                "Assalomu alaykum! 🎉\n\"Nur kitoblar\" doʻkoni tomonidan oʻtkaziladigan bayramona tanlovga xush kelibsiz!\n\n🎁 Ishtirok etish uchun roʻyxatdan oʻting 👇"
            );
            return;
        }

        // Kanal tekshiruvi
        $userId = $from['id'] ?? $chatId;
        if (!$this->isJoinedAll($userId)) {
            $this->askJoinChannel($chatId);
            return;
        }

        // Tayyor (mavjud foydalanuvchi uchun xush kelibsiz)
        $this->telegram->sendMessage($chatId, "Assalomu alaykum! 👋\n\"Nur kitoblar\" doʻkoni tanlovida ishtirok etayotganingizdan xursandmiz!");
        $this->sendAfisha($chatId);
        $this->sendReferralLink($chatId, $user);
        $this->sendMainKeyboard($chatId);
    }

    // ── KONTAKT (telefon) ────────────────────────────────

    protected function onContact(int $chatId, array $contact, array $from, int $msgId = 0): void
    {
        $phone = $contact['phone_number'] ?? '';
        $userId = $from['id'] ?? $chatId;

        $user = BookUser::where('telegram_id', $userId)->first();
        if ($user) {
            $user->phone = $phone;
            $user->save();
        }

        // Eski xabarlarni o'chirish
        if ($msgId > 0) {
            $this->telegram->deleteMessage($chatId, $msgId);     // kontakt xabarni o'chirish
            $this->telegram->deleteMessage($chatId, $msgId - 1); // "raqamni yuboring" xabarni o'chirish
        }

        $this->telegram->sendMessage($chatId, "✅ Raqam saqlandi!");

        // Kanal tekshiruvi
        if (!$this->isJoinedAll($userId)) {
            $this->sendAfisha($chatId);
            $this->askJoinChannel($chatId);
            return;
        }

        $this->sendAfisha($chatId);
        $this->sendReferralLink($chatId, $user);
        $this->sendMainKeyboard($chatId);
    }

    // ── KANAL TEKSHIRUVI ─────────────────────────────────

    protected function isJoinedAll(int $userId): bool
    {
        foreach ($this->requiredChannels as $ch) {
            if (!$this->telegram->isUserInChannel($ch['username'], $userId)) {
                return false;
            }
        }
        return true;
    }

    protected function askJoinChannel(int $chatId): void
    {
        $keyboard = [];
        foreach ($this->requiredChannels as $ch) {
            $name = ltrim($ch['username'], '@');
            $keyboard[] = [['text' => $ch['name'], 'url' => "https://t.me/{$name}"]];
        }
        $keyboard[] = [['text' => '✅ Tekshirish', 'callback_data' => 'check_channels']];

        $this->telegram->sendMessageWithKeyboard(
            $chatId,
            "⚠️ Tanlovda qatnashish uchun kanalga aʼzo boʻling va *\"✅ Tekshirish\"* tugmasini bosing.",
            $keyboard
        );
    }

    protected function onCheckChannels(int $chatId, array $from): void
    {
        $userId = $from['id'] ?? $chatId;

        if ($this->isJoinedAll($userId)) {
            $user = BookUser::where('telegram_id', $userId)->first();
            $this->telegram->sendMessage($chatId, "✅ Ajoyib! Siz kanalga aʼzo boʻlgansiz!");
            if ($user)
                $this->sendReferralLink($chatId, $user);
            $this->sendMainKeyboard($chatId);
        } else {
            $this->telegram->sendMessage($chatId, "❌ Hali kanalga aʼzo boʻlmagansiz.");
            $this->askJoinChannel($chatId);
        }
    }

    // ── AFISHA ───────────────────────────────────────────

    protected function sendAfisha(int $chatId): void
    {
        $text = "╔═══📚🌙🌸═══╗\n";
        $text .= "   *BAYRAMONA KONKURS*\n";
        $text .= "╚═══🌸🌙📚═══╝\n\n";
        $text .= "Hayit va Navroʻz bayramlari munosabati bilan *\"Nur kitoblar\"* doʻkoni kitobxonlar uchun sovrinli tanlov eʼlon qiladi! 🎉\n\n";
        $text .= "🎁 *Sovrinlar:*\n\n";
        $text .= "🥇 *1-oʻrin*\n";
        $text .= "📖 Qurʼoni Karim\n";
        $text .= "(maʼnolarining tarjima va tafsiri)\n\n";
        $text .= "🥈 *2-oʻrin*\n";
        $text .= "📚 Odam boʻlish qiyin\n";
        $text .= "📚 Zirapcha qiz (Qishloqlik Romeo va Juletta)\n\n";
        $text .= "🥉 *3-oʻrin*\n";
        $text .= "📚 Lobar, Lobar, Lobarim mening\n";
        $text .= "📚 Gʻoyib boʻlgan atirgul\n\n";
        $text .= "🏅 *4-oʻrin*\n";
        $text .= "📚 Zamonga yengilma\n";
        $text .= "📚 Alanga ichidagi ayol\n\n";
        $text .= "👥 Doʻstlaringizni taklif qiling va qimmatli kitoblarni yutib oling!\n\n";
        $text .= "🚀 Faol boʻling va gʻoliblar qatoridan joy oling!\n";
        $text .= "⚡️ Har bir qoʻshilgan odam sizni gʻalabaga yaqinlashtiradi!";

        $this->telegram->sendMessage($chatId, $text);
    }

    // ── REFERRAL LINK ────────────────────────────────────

    protected function sendReferralLink(int $chatId, $user): void
    {
        $botInfo = $this->telegram->getMe();
        $username = $botInfo['result']['username'] ?? 'bot';
        $link = "https://t.me/{$username}?start={$user->telegram_id}";

        $text = "🔗 *Sizning havolangiz:*\n\n";
        $text .= "Quyidagi xabarni doʻstlaringizga yuboring:\n\n";
        $text .= "👇👇👇\n\n";
        $text .= "📚 Kitob yutishni xohlaysizmi?\n";
        $text .= "\"Nur kitoblar\" doʻkonining bayramona tanlovida qatnashing!\n";
        $text .= "🎁 Qurʼoni Karim va boshqa qimmatli kitoblar sovg'a!\n\n";
        $text .= "👉 {$link}\n\n";
        $text .= "⚡️ Botga kirib /start bosing!";

        $this->telegram->sendMessage($chatId, $text);
    }

    // ── ASOSIY KEYBOARD (pastdan chiqadigan) ─────────────

    protected function sendMainKeyboard(int $chatId): void
    {
        $this->telegram->sendMessageWithReplyKeyboard($chatId, "📋 Menyu:\n🗓 15-mart — 21-mart", [
            [['text' => '🏆 Reyting'], ['text' => '👤 Profil']],
            [['text' => '🔗 Taklif qilish']],
            [['text' => '🎁 Sovrinlar'], ['text' => '📋 Yoʻriqnoma']],
        ]);
    }

    // ── YOʻRIQNOMA ──────────────────────────────────────

    protected function sendYoriqnoma(int $chatId): void
    {
        $text = "📋 *YOʻRIQNOMA*\n";
        $text .= "━━━━━━━━━━━━━━━━━━\n\n";

        $text .= "📌 *Tanlov haqida:*\n";
        $text .= "\"Nur kitoblar\" doʻkoni Hayit va Navroʻz bayramlari munosabati bilan kitobxonlar oʻrtasida tanlov eʼlon qiladi.\n\n";

        $text .= "🗓 *Muddat:*\n";
        $text .= "Boshlanish: *15-mart*\n";
        $text .= "Tugash: *21-mart*\n\n";

        $text .= "📝 *Qanday qatnashish mumkin:*\n";
        $text .= "1️⃣ Botga /start bosib roʻyxatdan oʻting\n";
        $text .= "2️⃣ Telefon raqamingizni yuboring\n";
        $text .= "3️⃣ Kanalga aʼzo boʻling\n";
        $text .= "4️⃣ Doʻstlaringizga referral havolangizni yuboring\n\n";

        $text .= "⚡️ *Ball tizimi:*\n";
        $text .= "Har bir doʻstingiz sizning havolangiz orqali botga qoʻshilsa, sizga *1 ball* beriladi.\n\n";

        $text .= "🏆 *Gʻoliblar qanday aniqlanadi:*\n";
        $text .= "21-mart kuni eng koʻp ball toʻplagan *4 nafar* ishtirokchi gʻolib deb topiladi.\n\n";

        $text .= "🎁 *Sovrinlar:*\n";
        $text .= "🥇 1-oʻrin — Qurʼoni Karim (tarjima va tafsiri)\n";
        $text .= "🥈 2-oʻrin — 2 ta kitob\n";
        $text .= "🥉 3-oʻrin — 2 ta kitob\n";
        $text .= "🏅 4-oʻrin — 2 ta kitob\n\n";

        $text .= "━━━━━━━━━━━━━━━━━━\n";
        $text .= "🔗 @nurkitoblari\\_m";

        $this->telegram->sendMessage($chatId, $text);
    }

    // ── PROFIL ───────────────────────────────────────────

    protected function onProfile(int $chatId, array $from): void
    {
        $user = $this->bookService->getOrCreateUser([
            'id' => $from['id'] ?? $chatId,
            'username' => $from['username'] ?? null,
            'first_name' => $from['first_name'] ?? '',
            'last_name' => $from['last_name'] ?? '',
        ]);

        $name = $user->first_name ?: 'Foydalanuvchi';
        $count = $user->referrals()->count();

        $botInfo = $this->telegram->getMe();
        $username = $botInfo['result']['username'] ?? 'bot';
        $link = "https://t.me/{$username}?start={$user->telegram_id}";

        $text = "👤 *{$name}*\n\n";
        $text .= "💰 Ball: *{$user->points}*\n";
        $text .= "👥 Taklif: *{$count}* ta\n";
        $text .= "📱 Raqam: {$user->phone}\n\n";
        $text .= "🔗 Havola:\n`{$link}`";

        $this->telegram->sendMessage($chatId, $text);
    }

    // ── REYTING ──────────────────────────────────────────

    protected function onLeaderboard(int $chatId): void
    {
        $leaders = $this->bookService->getLeaderboard(10);

        $text = "🏆 *Top 10*\n\n";
        $medals = ['🥇', '🥈', '🥉'];

        foreach ($leaders as $i => $l) {
            $m = $medals[$i] ?? ($i + 1) . '.';
            $n = $l->first_name ?: 'Foydalanuvchi';
            $text .= "{$m} {$n} — *{$l->points}* ball\n";
        }

        if ($leaders->isEmpty()) {
            $text .= "Hozircha ishtirokchilar yoʻq.";
        }

        $this->telegram->sendMessage($chatId, $text);
    }

    // ── TAKLIF QILISH ────────────────────────────────────

    protected function onReferral(int $chatId, array $from): void
    {
        $user = $this->bookService->getOrCreateUser([
            'id' => $from['id'] ?? $chatId,
            'username' => $from['username'] ?? null,
            'first_name' => $from['first_name'] ?? '',
            'last_name' => $from['last_name'] ?? '',
        ]);

        $this->sendReferralLink($chatId, $user);
    }
}
