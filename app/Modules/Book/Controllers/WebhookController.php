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
        ['username' => '@Nurkitoblari_m1', 'name' => '📢 Nur kitoblar'],
    ];

    public function __construct()
    {
        $this->telegram = new TelegramService();
        $this->bookService = new BookService();
    }

    public function handle(Request $request): JsonResponse
    {
        $update = $request->all();

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
        $isJoined = $this->isJoinedAll($userId);
        Log::info("[BookBot] Membership", ['u_id' => $userId, 'is_joined' => $isJoined]);

        if (!$isJoined) {
            $this->askJoinChannel($chatId);
            return;
        }

        Log::info("[BookBot] onMessage", ['chat_id' => $chatId, 'text' => $text, 'u_id' => $userId]);

        if (str_contains($text, 'Reyting')) {
            Log::info("[BookBot] Route: Reyting");
            $this->onLeaderboard($chatId);
        } elseif (str_contains($text, 'Profil')) {
            Log::info("[BookBot] Route: Profil");
            $this->onProfile($chatId, $from);
        } elseif (str_contains($text, 'Taklif')) {
            Log::info("[BookBot] Route: Taklif");
            $this->onReferral($chatId, $from);
        } elseif (str_contains($text, 'Sovrin')) {
            Log::info("[BookBot] Route: Sovrin");
            $this->sendAfisha($chatId);
        } elseif (str_contains($text, 'riqnoma')) {
            Log::info("[BookBot] Route: Yoriqnoma");
            $this->sendYoriqnoma($chatId);
        } else {
            Log::info('[BookBot] Unmatched', ['text' => $text]);
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
                "🌙 <b>Ramazon hayiti munosabati bilan ajoyib konkurs!</b>\n\nAssalomu alaykum! 🎉\n\"Nur kitoblar\" doʻkoni konkursiga xush kelibsiz!\n\n🎁 Ishtirok etish va sovg'alarga ega bo'lish uchun telefon raqamingizni yuboring 👇"
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
        $this->telegram->sendMessage($chatId, "🌙 <b>Ramazon hayiti munosabati bilan ajoyib konkurs!</b>\n\nSiz ro'yxatdan o'tgansiz. Konkursda omad tilaymiz! 🎁");
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

        // Kanal tekshiruvi
        if (!$this->isJoinedAll($userId)) {
            $this->askJoinChannel($chatId, "✅ Raqamingiz saqlandi!\n\n⚠️ Konkursda ishtirok etish uchun eng avvalo quyidagi guruhga a'zo bo'lishingiz shart.");
            return;
        }

        $this->telegram->sendMessage($chatId, "✅ Raqam saqlandi! Konkursda ishtirok etishni boshlashingiz mumkin.");
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

    protected function askJoinChannel(int $chatId, string $message = null): void
    {
        $keyboard = [];
        foreach ($this->requiredChannels as $ch) {
            $name = ltrim($ch['username'], '@');
            $keyboard[] = [['text' => $ch['name'], 'url' => "https://t.me/{$name}"]];
        }
        $keyboard[] = [['text' => '✅ Tekshirish', 'callback_data' => 'check_channels']];

        $text = $message ?? "⚠️ Botdan to'liq foydalanish va konkursda qatnashish uchun quyidagi guruhga a'zo bo'lishingiz shart.\n\nA'zo bo'lgach <b>\"✅ Tekshirish\"</b> tugmasini bosing.";

        $this->telegram->sendMessageWithKeyboard($chatId, $text, $keyboard);
    }

    protected function onCheckChannels(int $chatId, array $from): void
    {
        $userId = $from['id'] ?? $chatId;

        if ($this->isJoinedAll($userId)) {
            $user = BookUser::where('telegram_id', $userId)->first();
            $this->telegram->sendMessage($chatId, "✅ Ajoyib! Siz barcha guruhlarga aʼzo boʻldingiz!");
            $this->sendMainKeyboard($chatId);
        } else {
            $this->telegram->sendMessage($chatId, "❌ Hali guruhlarga aʼzo boʻlmagansiz.");
            $this->askJoinChannel($chatId);
        }
    }

    // ── AFISHA ───────────────────────────────────────────

    protected function sendAfisha(int $chatId): void
    {
        $text = "<b>╔═══📚🌙🌸═══╗\n";
        $text .= "   BAYRAMONA KONKURS\n";
        $text .= "╚═══🌸🌙📚═══╝</b>\n\n";
        $text .= "Ramazon hayiti munosabati bilan <b>\"Nur kitoblar\"</b> doʻkoni yirik sovrinli konkursni eʼlon qiladi! 🎉\n\n";
        $text .= "🎁 <b>Sovrinlar:</b>\n\n";
        $text .= "🥇 <b>1-oʻrin</b>\n";
        $text .= "📖 Qurʼoni Karim\n";
        $text .= "(maʼnolarining tarjima va tafsiri)\n\n";
        $text .= "🥈 <b>2-oʻrin</b>\n";
        $text .= "📚 Odam boʻlish qiyin\n";
        $text .= "📚 Zirapcha qiz (Qishloqlik Romeo va Juletta)\n\n";
        $text .= "🥉 <b>3-oʻrin</b>\n";
        $text .= "📚 Lobar, Lobar, Lobarim mening\n";
        $text .= "📚 Gʻoyib boʻlgan atirgul\n\n";
        $text .= "🏅 <b>4-oʻrin</b>\n";
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

        $text = "🔗 <b>Sizning havolangiz:</b>\n\n";
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
        $text = "📋 <b>QOIDA VA SHARTLAR</b>\n";
        $text .= "━━━━━━━━━━━━━━━━━━\n\n";

        $text .= "📌 <b>Konkurs haqida:</b>\n";
        $text .= "\"Nur kitoblar\" doʻkoni Ramazon hayiti munosabati bilan konkurs eʼlon qiladi.\n\n";

        $text .= "📝 <b>Qanday qatnashish mumkin:</b>\n";
        $text .= "1️⃣ Botga /start bosib roʻyxatdan oʻting\n";
        $text .= "2️⃣ Telefon raqamingizni yuboring\n";
        $text .= "3️⃣ Kerakli guruhlarga aʼzo boʻling\n";
        $text .= "4️⃣ Doʻstlaringizga o'z havolangizni yuboring\n\n";

        $text .= "⚡️ <b>Ball tizimi:</b>\n";
        $text .= "Sizning havolangiz orqali kirgan har bir doʻstingiz uchun <b>1 ball</b> beriladi.\n\n";

        $text .= "🎁 <b>Sovrinlar:</b>\n";
        $text .= "🥇 1-oʻrin — Qurʼoni Karim (tarjima va tafsiri)\n";
        $text .= "🥈 2-oʻrin — 2 ta kitob\n";
        $text .= "🥉 3-oʻrin — 2 ta kitob\n";
        $text .= "🏅 4-oʻrin — 2 ta kitob\n\n";

        $text .= "━━━━━━━━━━━━━━━━━━\n";

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

        $text = "👤 <b>{$name}</b>\n\n";
        $text .= "💰 Ball: <b>{$user->points}</b>\n";
        $text .= "👥 Taklif: <b>{$count}</b> ta\n";
        $text .= "📱 Raqam: {$user->phone}\n\n";
        $text .= "🔗 Havola:\n<code>{$link}</code>";

        $this->telegram->sendMessage($chatId, $text);
    }

    // ── REYTING ──────────────────────────────────────────

    protected function onLeaderboard(int $chatId): void
    {
        $leaders = $this->bookService->getLeaderboard(10);

        $text = "🏆 <b>Top 10</b>\n\n";
        $medals = ['🥇', '🥈', '🥉'];

        foreach ($leaders as $i => $l) {
            $m = $medals[$i] ?? ($i + 1) . '.';
            $n = $l->first_name ?: 'Foydalanuvchi';
            $text .= "{$m} {$n} — <b>{$l->points}</b> ball\n";
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
