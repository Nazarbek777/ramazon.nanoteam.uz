<?php

namespace App\Modules\Book\Controllers;

use App\Modules\Book\Services\BookService;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class BotController
{
    const TOKEN = '7294865765:AAFrcw4uNAmK-QOuZDW1hhDzzrUY3NXU9cs';

    public function __construct(
        protected BookService $bookService
    ) {
    }

    public function start(Nutgram $bot): void
    {
        $tgUser = [
            'id' => $bot->userId(),
            'username' => $bot->user()->username,
            'first_name' => $bot->user()->first_name,
            'last_name' => $bot->user()->last_name,
        ];

        // Parse referral parameter from /start [referrerId]
        $referrerId = null;
        $parameter = $bot->message()?->text;
        if (str_contains($parameter, ' ')) {
            $parts = explode(' ', $parameter);
            if (isset($parts[1]) && is_numeric($parts[1])) {
                $referrerId = (int) $parts[1];
            }
        }

        $user = $this->bookService->getOrCreateUser($tgUser, $referrerId);

        // The referral link is needed here for the new message, so it's generated.
        // This assumes the user's telegram_id is available after getOrCreateUser.
        $referralLink = "https://t.me/" . $bot->getMe()->username . "?start=" . $user->telegram_id;

        $bot->sendMessage("Do'stlaringizni taklif qiling va har bir do'stingiz uchun 1 ballga ega bo'ling! \n\nSizning havolangiz: \n`{$referralLink}`", [
            'parse_mode' => 'Markdown'
        ]);

        $this->showMenu($bot);
    }

    public function showMenu(Nutgram $bot): void
    {
        $bot->sendMessage("Tanlang:", [
            'reply_markup' => InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('📚 Kitoblar', callback_data: 'books'),
                    InlineKeyboardButton::make('🏆 Reyting', callback_data: 'leaderboard')
                )
                ->addRow(
                    InlineKeyboardButton::make('👤 Profil', callback_data: 'profile'),
                    InlineKeyboardButton::make('🔗 Taklif qilish', callback_data: 'referral')
                )
        ]);
    }

    public function profile(Nutgram $bot): void
    {
        $user = $this->bookService->getOrCreateUser([
            'id' => $bot->userId(),
            'username' => $bot->user()->username,
            'first_name' => $bot->user()->first_name,
            'last_name' => $bot->user()->last_name,
        ]);

        $referralLink = "https://t.me/" . $bot->getMe()->username . "?start=" . $user->telegram_id;

        $text = "👤 *Profil*\n\n";
        $text .= "🆔 ID: `{$user->telegram_id}`\n";
        $text .= "💰 Ballar: *{$user->points}*\n";
        $text .= "👥 Taklif qilinganlar: *" . $user->referrals()->count() . "*\n\n";
        $text .= "🔗 Sizning referral havolangiz:\n`{$referralLink}`";

        $bot->sendMessage($text, [
            'parse_mode' => 'Markdown'
        ]);
    }

    public function leaderboard(Nutgram $bot): void
    {
        $leaders = $this->bookService->getLeaderboard(10);

        $text = "🏆 *Top 10 Ishtirokchilar*\n\n";
        foreach ($leaders as $index => $leader) {
            $name = $leader->first_name ?: "Foydalanuvchi";
            $text .= ($index + 1) . ". {$name} - *{$leader->points}* ball\n";
        }

        $bot->sendMessage($text, [
            'parse_mode' => 'Markdown'
        ]);
    }

    public function books(Nutgram $bot): void
    {
        $books = \App\Modules\Book\Models\Book::all();

        if ($books->isEmpty()) {
            $bot->sendMessage("Hozircha kitoblar ro'yxati bo'sh. Tez kunda yangi kitoblar qo'shiladi!");
            return;
        }

        $bot->sendMessage("📚 *Mavjud kitoblar ro'yxati:*", ['parse_mode' => 'Markdown']);

        foreach ($books as $book) {
            $text = "📖 *{$book->title}*\n";
            $text .= "👤 Muallif: {$book->author}\n";
            $text .= "💰 Narxi: {$book->price} so'm\n";
            $text .= "📦 Qoldiq: {$book->stock} dona\n";

            $bot->sendMessage($text, [
                'parse_mode' => 'Markdown',
                'reply_markup' => InlineKeyboardMarkup::make()
                    ->addRow(InlineKeyboardButton::make("Sotib olish", callback_data: "buy_{$book->id}"))
            ]);
        }
    }
}
