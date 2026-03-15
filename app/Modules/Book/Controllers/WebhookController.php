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

        // Kontakt (telefon raqam) kelsa
        if (isset($msg['contact'])) {
            $this->onContact($chatId, $msg['contact'], $from);
            return;
        }

        // Komandalar
        if (str_starts_with($text, '/start')) {
            $this->onStart($chatId, $text, $from);
        } elseif ($text === '🏆 Reyting') {
            $this->onLeaderboard($chatId);
        } elseif ($text === '👤 Profil') {
            $this->onProfile($chatId, $from);
        } elseif ($text === '🔗 Taklif qilish') {
            $this->onReferral($chatId, $from);
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
        // Referral parse
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

        // 1. Telefon raqam yo'q — so'rash
        if (empty($user->phone)) {
            $this->telegram->sendMessage($chatId, "Assalomu alaykum, *{$user->first_name}*! 🎉\n\nTanlovda qatnashish uchun telefon raqamingizni yuboring:");
            $this->telegram->sendContactRequest($chatId, "📱 Quyidagi tugmani bosing:");
            return;
        }

        // 2. Kanal tekshiruvi
        $userId = $from['id'] ?? $chatId;
        if (!$this->isJoinedAll($userId)) {
            $this->sendAfisha($chatId);
            $this->askJoinChannel($chatId);
            return;
        }

        // 3. Hammasi tayyor
        $this->sendAfisha($chatId);
        $this->sendReferralLink($chatId, $user);
        $this->sendMainKeyboard($chatId);
    }

    // ── KONTAKT (telefon) ────────────────────────────────

    protected function onContact(int $chatId, array $contact, array $from): void
    {
        $phone = $contact['phone_number'] ?? '';
        $userId = $from['id'] ?? $chatId;

        // Raqamni saqlash
        $user = BookUser::where('telegram_id', $userId)->first();
        if ($user) {
            $user->update(['phone' => $phone]);
        }

        $this->telegram->sendMessage($chatId, "✅ Raqamingiz saqlandi: *{$phone}*");

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
        $text .= "🎉 *\"Nur kitoblar\"* doʻkonidan sovrinli tanlov!\n\n";
        $text .= "🗓 Muddati: *21-martgacha*\n\n";
        $text .= "🥇 1-oʻrin — Qurʼoni Karim\n";
        $text .= "🥈 2-oʻrin — 2 ta kitob\n";
        $text .= "🥉 3-oʻrin — 2 ta kitob\n";
        $text .= "🏅 4-oʻrin — 2 ta kitob\n\n";
        $text .= "⚡️ Har bir doʻstingiz = *1 ball*";

        $this->telegram->sendMessage($chatId, $text);
    }

    // ── REFERRAL LINK ────────────────────────────────────

    protected function sendReferralLink(int $chatId, $user): void
    {
        $botInfo = $this->telegram->getMe();
        $username = $botInfo['result']['username'] ?? 'bot';
        $link = "https://t.me/{$username}?start={$user->telegram_id}";

        $this->telegram->sendMessage($chatId, "🔗 *Sizning havolangiz:*\n`{$link}`\n\nDoʻstlaringizga yuboring!");
    }

    // ── ASOSIY KEYBOARD (pastdan chiqadigan) ─────────────

    protected function sendMainKeyboard(int $chatId): void
    {
        $this->telegram->sendMessageWithReplyKeyboard($chatId, "📋 Menyu:", [
            [['text' => '🏆 Reyting'], ['text' => '👤 Profil']],
            [['text' => '🔗 Taklif qilish']],
        ]);
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
