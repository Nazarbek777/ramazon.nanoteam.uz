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
     * Required channels that users must join.
     */
    protected array $requiredChannels = [
        '@Nurkitoblari_m1',
        '@nurkitoblari_m',
    ];

    public function __construct()
    {
        $this->telegram = new TelegramService();
        $this->bookService = new BookService();
    }

    /**
     * Handle incoming Telegram webhook update.
     */
    public function handle(Request $request): JsonResponse
    {
        $update = $request->all();

        Log::info('[BookBot] Webhook received', ['update' => json_encode($update)]);

        try {
            if (isset($update['message'])) {
                Log::info('[BookBot] Processing message', ['text' => $update['message']['text'] ?? 'no text']);
                $this->handleMessage($update['message']);
            } elseif (isset($update['callback_query'])) {
                Log::info('[BookBot] Processing callback', ['data' => $update['callback_query']['data'] ?? 'no data']);
                $this->handleCallbackQuery($update['callback_query']);
            } else {
                Log::info('[BookBot] Unknown update type', ['keys' => array_keys($update)]);
            }
        } catch (\Exception $e) {
            Log::error('[BookBot] Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Handle text messages and commands.
     */
    protected function handleMessage(array $message): void
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';
        $from = $message['from'] ?? [];

        // Parse command
        if (str_starts_with($text, '/start')) {
            $this->handleStart($chatId, $text, $from);
        } elseif ($text === '/profile') {
            $this->handleProfile($chatId, $from);
        } elseif ($text === '/leaderboard') {
            $this->handleLeaderboard($chatId);
        } elseif ($text === '/books') {
            $this->handleBooks($chatId);
        } elseif ($text === '/menu') {
            $this->showMenu($chatId);
        }
    }

    /**
     * Handle callback queries from inline keyboard buttons.
     */
    protected function handleCallbackQuery(array $callbackQuery): void
    {
        $chatId = $callbackQuery['message']['chat']['id'] ?? null;
        $data = $callbackQuery['data'] ?? '';
        $callbackId = $callbackQuery['id'];
        $from = $callbackQuery['from'] ?? [];

        if (!$chatId)
            return;

        // Answer callback to remove loading state
        $this->telegram->answerCallbackQuery($callbackId);

        // Handle "check_channels" callback separately
        if ($data === 'check_channels') {
            $this->handleCheckChannels($chatId, $from);
            return;
        }

        // For all other actions, check channel membership first
        $userId = $from['id'] ?? $chatId;
        if (!$this->userJoinedAllChannels($userId)) {
            $this->sendChannelJoinMessage($chatId);
            return;
        }

        match ($data) {
            'profile' => $this->handleProfile($chatId, $from),
            'leaderboard' => $this->handleLeaderboard($chatId),
            'books' => $this->handleBooks($chatId),
            'referral' => $this->handleReferral($chatId, $from),
            default => $this->handleBuyCallback($chatId, $data),
        };
    }

    // ─── Channel Membership ──────────────────────────────

    /**
     * Check if user has joined all required channels.
     */
    protected function userJoinedAllChannels(int $userId): bool
    {
        foreach ($this->requiredChannels as $channel) {
            if (!$this->telegram->isUserInChannel($channel, $userId)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Send message asking user to join required channels.
     */
    protected function sendChannelJoinMessage(int $chatId): void
    {
        $keyboard = [];

        foreach ($this->requiredChannels as $channel) {
            $cleanName = ltrim($channel, '@');
            $keyboard[] = [['text' => "📢 {$cleanName}", 'url' => "https://t.me/{$cleanName}"]];
        }

        // Add a "Check" button
        $keyboard[] = [['text' => '✅ Tekshirish', 'callback_data' => 'check_channels']];

        $text = "⚠️ *Tanlovda qatnashish uchun quyidagi kanallarga a'zo bo'ling:*\n\n";
        foreach ($this->requiredChannels as $channel) {
            $text .= "📢 {$channel}\n";
        }
        $text .= "\nA'zo bo'lgach, *✅ Tekshirish* tugmasini bosing.";

        $this->telegram->sendMessageWithKeyboard($chatId, $text, $keyboard);
    }

    /**
     * Handle the "check_channels" callback.
     */
    protected function handleCheckChannels(int $chatId, array $from): void
    {
        $userId = $from['id'] ?? $chatId;

        if ($this->userJoinedAllChannels($userId)) {
            $this->telegram->sendMessage($chatId, "✅ *Ajoyib!* Siz barcha kanallarga a'zo bo'lgansiz. Davom eting!");
            $this->showMenu($chatId);
        } else {
            $this->sendChannelJoinMessage($chatId);
        }
    }

    // ─── Command Handlers ────────────────────────────────

    protected function handleStart(int $chatId, string $text, array $from): void
    {
        $tgUser = [
            'id' => $from['id'] ?? $chatId,
            'username' => $from['username'] ?? null,
            'first_name' => $from['first_name'] ?? '',
            'last_name' => $from['last_name'] ?? '',
        ];

        // Parse referral ID from "/start 12345"
        $referrerId = null;
        if (str_contains($text, ' ')) {
            $parts = explode(' ', $text);
            if (isset($parts[1]) && is_numeric($parts[1])) {
                $referrerId = (int) $parts[1];
            }
        }

        $user = $this->bookService->getOrCreateUser($tgUser, $referrerId);

        // Check channel membership
        $userId = $from['id'] ?? $chatId;
        if (!$this->userJoinedAllChannels($userId)) {
            // Send the afisha first
            $this->sendAfisha($chatId);
            // Then ask to join channels
            $this->sendChannelJoinMessage($chatId);
            return;
        }

        // Get bot username for referral link
        $botInfo = $this->telegram->getMe();
        $botUsername = $botInfo['result']['username'] ?? 'bot';
        $referralLink = "https://t.me/{$botUsername}?start={$user->telegram_id}";

        // Send afisha welcome
        $this->sendAfisha($chatId);

        $this->telegram->sendMessage(
            $chatId,
            "👥 Do'stlaringizni taklif qiling va har bir do'stingiz uchun *1 ballga* ega bo'ling!\n\n" .
            "Sizning havolangiz:\n`{$referralLink}`"
        );

        $this->showMenu($chatId);
    }

    /**
     * Send the competition afisha/poster.
     */
    protected function sendAfisha(int $chatId): void
    {
        $afisha = "╔═══📚🌙🌸═══╗\n";
        $afisha .= "   *BAYRAMONA KONKURS*\n";
        $afisha .= "╚═══🌸🌙📚═══╝\n\n";
        $afisha .= "Hayit va Navroʻz bayramlari munosabati bilan \"Nur kitoblar\" doʻkoni kitobxonlar uchun sovrinli tanlov eʼlon qiladi! 🎉\n\n";
        $afisha .= "🗓 *Muddati:* 21-martgacha.\n\n";
        $afisha .= "🎁 *Sovrinlar:*\n\n";
        $afisha .= "🥇 *1-o'rin*\n";
        $afisha .= "📖 Qurʼoni Karim (maʼnolarining tarjima va tafsiri)\n\n";
        $afisha .= "🥈 *2-o'rin*\n";
        $afisha .= "📚 Odam bo'lish qiyin\n";
        $afisha .= "📚 Zirapcha qiz (Qishloqlik Romeo va Juletta)\n\n";
        $afisha .= "🥉 *3-o'rin*\n";
        $afisha .= "📚 Lobar, Lobar, Lobarim mening\n";
        $afisha .= "📚 Gʻoyib bo'lgan atirgul\n\n";
        $afisha .= "🏅 *4-o'rin*\n";
        $afisha .= "📚 Zamonga yengilma\n";
        $afisha .= "📚 Alanga ichidagi ayol\n\n";
        $afisha .= "👥 *Doʻstlaringizni taklif qiling va qimmatli kitoblarni yutib oling!*\n\n";
        $afisha .= "🚀 Faol bo'ling va g'oliblar qatoridan joy oling!\n";
        $afisha .= "⚡️ Har bir qoʻshilgan odam sizni gʻalabaga yaqinlashtiradi!";

        $this->telegram->sendMessage($chatId, $afisha);
    }

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

        $text = "👤 *Profil*\n\n";
        $text .= "🆔 ID: `{$user->telegram_id}`\n";
        $text .= "💰 Ballar: *{$user->points}*\n";
        $text .= "👥 Taklif qilinganlar: *" . $user->referrals()->count() . "*\n\n";
        $text .= "🔗 Sizning referral havolangiz:\n`{$referralLink}`";

        $this->telegram->sendMessage($chatId, $text);
    }

    protected function handleLeaderboard(int $chatId): void
    {
        $leaders = $this->bookService->getLeaderboard(10);

        $text = "🏆 *Top 10 Ishtirokchilar*\n\n";
        foreach ($leaders as $index => $leader) {
            $name = $leader->first_name ?: "Foydalanuvchi";
            $text .= ($index + 1) . ". {$name} — *{$leader->points}* ball\n";
        }

        $this->telegram->sendMessage($chatId, $text);
    }

    protected function handleBooks(int $chatId): void
    {
        $books = \App\Modules\Book\Models\Book::all();

        if ($books->isEmpty()) {
            $this->telegram->sendMessage($chatId, "Hozircha kitoblar ro'yxati bo'sh. Tez kunda yangi kitoblar qo'shiladi!");
            return;
        }

        $this->telegram->sendMessage($chatId, "📚 *Mavjud kitoblar ro'yxati:*");

        foreach ($books as $book) {
            $text = "📖 *{$book->title}*\n";
            $text .= "👤 Muallif: {$book->author}\n";
            $text .= "💰 Narxi: {$book->price} so'm\n";
            $text .= "📦 Qoldiq: {$book->stock} dona";

            $this->telegram->sendMessageWithKeyboard($chatId, $text, [
                [['text' => '🛒 Sotib olish', 'callback_data' => "buy_{$book->id}"]],
            ]);
        }
    }

    protected function handleReferral(int $chatId, array $from): void
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

        $this->telegram->sendMessage(
            $chatId,
            "Do'stlaringizni taklif qiling va har bir do'stingiz uchun *1 ballga* ega bo'ling!\n\n" .
            "Sizning havolangiz:\n`{$referralLink}`"
        );
    }

    protected function handleBuyCallback(int $chatId, string $data): void
    {
        if (str_starts_with($data, 'buy_')) {
            $bookId = str_replace('buy_', '', $data);
            $this->telegram->sendMessage($chatId, "Sotib olish funksiyasi tez kunda qo'shiladi! (ID: {$bookId})");
        }
    }

    // ─── Menu ────────────────────────────────────────────

    protected function showMenu(int $chatId): void
    {
        $this->telegram->sendMessageWithKeyboard($chatId, "Tanlang:", [
            [
                ['text' => '📚 Kitoblar', 'callback_data' => 'books'],
                ['text' => '🏆 Reyting', 'callback_data' => 'leaderboard'],
            ],
            [
                ['text' => '👤 Profil', 'callback_data' => 'profile'],
                ['text' => '🔗 Taklif qilish', 'callback_data' => 'referral'],
            ],
        ]);
    }
}
