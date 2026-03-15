<?php

namespace App\Modules\Book\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Modules\Book\Services\TelegramService;
use App\Modules\Book\Services\BookService;

class WebhookController
{
    protected TelegramService $telegram;
    protected BookService $bookService;

    /**
     * Kanallar ro'yxati — foydalanuvchi shu kanallarga a'zo bo'lishi shart.
     */
    protected array $requiredChannels = [
        ['username' => '@nurkitoblari_m', 'name' => '📢 Nur kitoblar'],
    ];

    public function __construct()
    {
        $this->telegram = new TelegramService();
        $this->bookService = new BookService();
    }

    // ──────────────────────────────────────────────────────
    //  WEBHOOK ENTRY POINT
    // ──────────────────────────────────────────────────────

    public function handle(Request $request): JsonResponse
    {
        $update = $request->all();

        Log::info('[BookBot] Webhook received', ['update_id' => $update['update_id'] ?? 'unknown']);

        try {
            if (isset($update['message'])) {
                $this->handleMessage($update['message']);
            } elseif (isset($update['callback_query'])) {
                $this->handleCallbackQuery($update['callback_query']);
            }
        } catch (\Exception $e) {
            Log::error('[BookBot] Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        return response()->json(['ok' => true]);
    }

    // ──────────────────────────────────────────────────────
    //  MESSAGE ROUTER
    // ──────────────────────────────────────────────────────

    protected function handleMessage(array $message): void
    {
        $chatId = $message['chat']['id'];
        $text = trim($message['text'] ?? '');
        $from = $message['from'] ?? [];

        if (str_starts_with($text, '/start')) {
            $this->handleStart($chatId, $text, $from);
        } elseif ($text === '/profile' || $text === '/profil') {
            $this->handleProfile($chatId, $from);
        } elseif ($text === '/leaderboard' || $text === '/reyting') {
            $this->handleLeaderboard($chatId);
        } elseif ($text === '/books' || $text === '/kitoblar') {
            $this->handleBooks($chatId);
        } elseif ($text === '/menu') {
            $this->showMenu($chatId);
        }
    }

    // ──────────────────────────────────────────────────────
    //  CALLBACK ROUTER
    // ──────────────────────────────────────────────────────

    protected function handleCallbackQuery(array $callbackQuery): void
    {
        $chatId = $callbackQuery['message']['chat']['id'] ?? null;
        $data = $callbackQuery['data'] ?? '';
        $callbackId = $callbackQuery['id'];
        $from = $callbackQuery['from'] ?? [];

        if (!$chatId)
            return;

        $this->telegram->answerCallbackQuery($callbackId);

        if ($data === 'check_channels') {
            $this->handleCheckChannels($chatId, $from);
            return;
        }

        // Har bir buyruq uchun kanal tekshiruvi
        $userId = $from['id'] ?? $chatId;
        if (!$this->userJoinedAllChannels($userId)) {
            $this->sendJoinChannelsMessage($chatId);
            return;
        }

        match ($data) {
            'profile' => $this->handleProfile($chatId, $from),
            'leaderboard' => $this->handleLeaderboard($chatId),
            'books' => $this->handleBooks($chatId),
            'referral' => $this->handleReferral($chatId, $from),
            'menu' => $this->showMenu($chatId),
            default => $this->handleOtherCallback($chatId, $data),
        };
    }

    // ──────────────────────────────────────────────────────
    //  /start COMMAND
    // ──────────────────────────────────────────────────────

    protected function handleStart(int $chatId, string $text, array $from): void
    {
        $tgUser = [
            'id' => $from['id'] ?? $chatId,
            'username' => $from['username'] ?? null,
            'first_name' => $from['first_name'] ?? '',
            'last_name' => $from['last_name'] ?? '',
        ];

        // Referral ID ni parse qilish (/start 12345)
        $referrerId = null;
        if (str_contains($text, ' ')) {
            $parts = explode(' ', $text);
            if (isset($parts[1]) && is_numeric($parts[1])) {
                $referrerId = (int) $parts[1];
            }
        }

        $user = $this->bookService->getOrCreateUser($tgUser, $referrerId);
        $userId = $from['id'] ?? $chatId;

        // Kanal tekshiruvi
        if (!$this->userJoinedAllChannels($userId)) {
            $this->sendAfisha($chatId);
            $this->sendJoinChannelsMessage($chatId);
            return;
        }

        // Hammasi yaxshi — to'liq xush kelibsiz
        $this->sendAfisha($chatId);
        $this->sendReferralInfo($chatId, $user);
        $this->showMenu($chatId);
    }

    // ──────────────────────────────────────────────────────
    //  AFISHA (Konkurs ma'lumoti)
    // ──────────────────────────────────────────────────────

    protected function sendAfisha(int $chatId): void
    {
        $text = <<<TEXT
╔═══📚🌙🌸═══╗
   *BAYRAMONA KONKURS*
╚═══🌸🌙📚═══╝

Hayit va Navroʻz bayramlari munosabati bilan *"Nur kitoblar"* doʻkoni kitobxonlar uchun sovrinli tanlov eʼlon qiladi! 🎉

🗓 *Muddati:* 21-martgacha

🎁 *Sovrinlar:*

🥇 *1-oʻrin* — Qurʼoni Karim (maʼnolarining tarjima va tafsiri)

🥈 *2-oʻrin* — Odam boʻlish qiyin, Zirapcha qiz

🥉 *3-oʻrin* — Lobar, Lobar, Lobarim mening, Gʻoyib boʻlgan atirgul

🏅 *4-oʻrin* — Zamonga yengilma, Alanga ichidagi ayol

🚀 Faol boʻling va gʻoliblar qatoridan joy oling!
⚡️ Har bir qoʻshilgan odam sizni gʻalabaga yaqinlashtiradi!
TEXT;

        $this->telegram->sendMessage($chatId, $text);
    }

    // ──────────────────────────────────────────────────────
    //  KANAL TEKSHIRUVI
    // ──────────────────────────────────────────────────────

    protected function userJoinedAllChannels(int $userId): bool
    {
        foreach ($this->requiredChannels as $channel) {
            if (!$this->telegram->isUserInChannel($channel['username'], $userId)) {
                return false;
            }
        }
        return true;
    }

    protected function sendJoinChannelsMessage(int $chatId): void
    {
        $keyboard = [];
        foreach ($this->requiredChannels as $channel) {
            $cleanName = ltrim($channel['username'], '@');
            $keyboard[] = [['text' => $channel['name'], 'url' => "https://t.me/{$cleanName}"]];
        }
        $keyboard[] = [['text' => '✅ Tekshirish', 'callback_data' => 'check_channels']];

        $text = "⚠️ *Tanlovda qatnashish uchun quyidagi kanalga aʼzo boʻling:*\n\n";
        foreach ($this->requiredChannels as $channel) {
            $text .= "{$channel['name']} — {$channel['username']}\n";
        }
        $text .= "\nAʼzo boʻlganingizdan soʻng *\"✅ Tekshirish\"* tugmasini bosing.";

        $this->telegram->sendMessageWithKeyboard($chatId, $text, $keyboard);
    }

    protected function handleCheckChannels(int $chatId, array $from): void
    {
        $userId = $from['id'] ?? $chatId;

        if ($this->userJoinedAllChannels($userId)) {
            $user = $this->bookService->getOrCreateUser([
                'id' => $userId,
                'username' => $from['username'] ?? null,
                'first_name' => $from['first_name'] ?? '',
                'last_name' => $from['last_name'] ?? '',
            ]);

            $this->telegram->sendMessage($chatId, "✅ *Ajoyib!* Siz kanalga aʼzo boʻlgansiz!");
            $this->sendReferralInfo($chatId, $user);
            $this->showMenu($chatId);
        } else {
            $this->telegram->sendMessage($chatId, "❌ Siz hali kanalga aʼzo boʻlmagansiz. Iltimos, avval kanalga qoʻshiling.");
            $this->sendJoinChannelsMessage($chatId);
        }
    }

    // ──────────────────────────────────────────────────────
    //  REFERRAL MA'LUMOTI
    // ──────────────────────────────────────────────────────

    protected function sendReferralInfo(int $chatId, $user): void
    {
        $botInfo = $this->telegram->getMe();
        $botUsername = $botInfo['result']['username'] ?? 'bot';
        $referralLink = "https://t.me/{$botUsername}?start={$user->telegram_id}";

        $text = "👥 Doʻstlaringizni taklif qiling va har bir doʻstingiz uchun *1 ball* oling!\n\n";
        $text .= "🔗 *Sizning havolangiz:*\n`{$referralLink}`";

        $this->telegram->sendMessage($chatId, $text);
    }

    // ──────────────────────────────────────────────────────
    //  PROFIL
    // ──────────────────────────────────────────────────────

    protected function handleProfile(int $chatId, array $from): void
    {
        $user = $this->bookService->getOrCreateUser([
            'id' => $from['id'] ?? $chatId,
            'username' => $from['username'] ?? null,
            'first_name' => $from['first_name'] ?? '',
            'last_name' => $from['last_name'] ?? '',
        ]);

        $botInfo = $this->telegram->getMe();
        $botUsername = $botInfo['result']['username'] ?? 'bot';
        $referralLink = "https://t.me/{$botUsername}?start={$user->telegram_id}";

        $name = $user->first_name ?: 'Foydalanuvchi';
        $referralCount = $user->referrals()->count();

        $text = "👤 *{$name} — Profil*\n\n";
        $text .= "💰 Ballaringiz: *{$user->points}*\n";
        $text .= "👥 Taklif qilganlaringiz: *{$referralCount}* ta\n\n";
        $text .= "🔗 *Sizning havolangiz:*\n`{$referralLink}`";

        $this->telegram->sendMessageWithKeyboard($chatId, $text, [
            [['text' => '🔙 Menyu', 'callback_data' => 'menu']],
        ]);
    }

    // ──────────────────────────────────────────────────────
    //  REYTING (Leaderboard)
    // ──────────────────────────────────────────────────────

    protected function handleLeaderboard(int $chatId): void
    {
        $leaders = $this->bookService->getLeaderboard(10);

        $text = "🏆 *Top 10 — Eng faol ishtirokchilar*\n\n";

        if ($leaders->isEmpty()) {
            $text .= "Hozircha ishtirokchilar yoʻq. Birinchi boʻling! 🚀";
        } else {
            $medals = ['🥇', '🥈', '🥉'];
            foreach ($leaders as $index => $leader) {
                $name = $leader->first_name ?: 'Foydalanuvchi';
                $medal = $medals[$index] ?? ($index + 1) . '.';
                $text .= "{$medal} {$name} — *{$leader->points}* ball\n";
            }
        }

        $this->telegram->sendMessageWithKeyboard($chatId, $text, [
            [['text' => '🔙 Menyu', 'callback_data' => 'menu']],
        ]);
    }

    // ──────────────────────────────────────────────────────
    //  KITOBLAR
    // ──────────────────────────────────────────────────────

    protected function handleBooks(int $chatId): void
    {
        $books = \App\Modules\Book\Models\Book::all();

        if ($books->isEmpty()) {
            $this->telegram->sendMessageWithKeyboard(
                $chatId,
                "📚 Hozircha kitoblar roʻyxati boʻsh.\nTez kunda yangi kitoblar qoʻshiladi!",
                [[['text' => '🔙 Menyu', 'callback_data' => 'menu']]]
            );
            return;
        }

        $this->telegram->sendMessage($chatId, "📚 *Mavjud kitoblar:*");

        foreach ($books as $book) {
            $text = "📖 *{$book->title}*\n";
            $text .= "✍️ {$book->author}\n";
            $text .= "💰 {$book->price} soʻm | 📦 {$book->stock} dona";

            $this->telegram->sendMessageWithKeyboard($chatId, $text, [
                [['text' => '🛒 Sotib olish', 'callback_data' => "buy_{$book->id}"]],
            ]);
        }
    }

    // ──────────────────────────────────────────────────────
    //  REFERRAL (Taklif qilish)
    // ──────────────────────────────────────────────────────

    protected function handleReferral(int $chatId, array $from): void
    {
        $user = $this->bookService->getOrCreateUser([
            'id' => $from['id'] ?? $chatId,
            'username' => $from['username'] ?? null,
            'first_name' => $from['first_name'] ?? '',
            'last_name' => $from['last_name'] ?? '',
        ]);

        $this->sendReferralInfo($chatId, $user);
    }

    // ──────────────────────────────────────────────────────
    //  BOSHQA CALLBACK
    // ──────────────────────────────────────────────────────

    protected function handleOtherCallback(int $chatId, string $data): void
    {
        if (str_starts_with($data, 'buy_')) {
            $bookId = str_replace('buy_', '', $data);
            $book = \App\Modules\Book\Models\Book::find($bookId);
            $bookName = $book ? $book->title : "#{$bookId}";
            $this->telegram->sendMessage(
                $chatId,
                "📖 *{$bookName}* kitobini sotib olish uchun doʻkonga tashrif buyuring:\n\n🔗 @nurkitoblari\\_m"
            );
        }
    }

    // ──────────────────────────────────────────────────────
    //  MENYU
    // ──────────────────────────────────────────────────────

    protected function showMenu(int $chatId): void
    {
        $this->telegram->sendMessageWithKeyboard($chatId, "📋 *Asosiy menyu:*", [
            [
                ['text' => '🏆 Reyting', 'callback_data' => 'leaderboard'],
                ['text' => '👤 Profil', 'callback_data' => 'profile'],
            ],
            [
                ['text' => '🔗 Taklif qilish', 'callback_data' => 'referral'],
            ],
        ]);
    }
}
