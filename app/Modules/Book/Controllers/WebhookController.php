<?php

namespace App\Modules\Book\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Modules\Book\Services\TelegramService;
use App\Modules\Book\Services\BookService;

class WebhookController
{
    protected TelegramService $telegram;
    protected BookService $bookService;

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

        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        } elseif (isset($update['callback_query'])) {
            $this->handleCallbackQuery($update['callback_query']);
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

        match ($data) {
            'profile' => $this->handleProfile($chatId, $from),
            'leaderboard' => $this->handleLeaderboard($chatId),
            'books' => $this->handleBooks($chatId),
            'referral' => $this->handleReferral($chatId, $from),
            default => $this->handleBuyCallback($chatId, $data),
        };
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

        // Get bot username for referral link
        $botInfo = $this->telegram->getMe();
        $botUsername = $botInfo['result']['username'] ?? 'bot';
        $referralLink = "https://t.me/{$botUsername}?start={$user->telegram_id}";

        $this->telegram->sendMessage(
            $chatId,
            "Assalomu alaykum, *{$user->first_name}*! 🎉\n\n" .
            "Do'stlaringizni taklif qiling va har bir do'stingiz uchun *1 ballga* ega bo'ling!\n\n" .
            "Sizning havolangiz:\n`{$referralLink}`"
        );

        $this->showMenu($chatId);
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
