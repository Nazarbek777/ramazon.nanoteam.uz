<?php

namespace App\Modules\Book;

use SergiX44\Nutgram\Nutgram;
use App\Modules\Book\Handlers\StartHandler;
use App\Modules\Book\Handlers\ProfileHandler;
use App\Modules\Book\Handlers\LeaderboardHandler;
use App\Modules\Book\Handlers\BooksHandler;
use App\Modules\Book\Handlers\MenuHandler;

class BotManager
{
    const TOKEN = '7294865765:AAFrcw4uNAmK-QOuZDW1hhDzzrUY3NXU9cs';

    public function registerHandlers(Nutgram $bot): void
    {
        // Commands
        $bot->onCommand('start', StartHandler::class);
        $bot->onCommand('profile', ProfileHandler::class);
        $bot->onCommand('leaderboard', LeaderboardHandler::class);
        $bot->onCommand('books', BooksHandler::class);
        $bot->onCommand('menu', MenuHandler::class);

        // Callbacks
        $bot->onCallbackQueryData('profile', ProfileHandler::class);
        $bot->onCallbackQueryData('leaderboard', LeaderboardHandler::class);
        $bot->onCallbackQueryData('books', BooksHandler::class);
        $bot->onCallbackQueryData('referral', function (Nutgram $bot) {
            $user = (new \App\Modules\Book\Services\BookService())->getOrCreateUser([
                'id' => $bot->userId(),
                'username' => $bot->user()->username,
                'first_name' => $bot->user()->first_name,
                'last_name' => $bot->user()->last_name,
            ]);

            $referralLink = "https://t.me/" . $bot->getMe()->username . "?start=" . $user->telegram_id;

            $bot->sendMessage(text: "Do'stlaringizni taklif qiling va har bir do'stingiz uchun 1 ballga ega bo'ling! \n\nSizning havolangiz: \n`{$referralLink}`", parse_mode: 'Markdown');
        });

        $bot->onCallbackQueryData('buy_{id}', function (Nutgram $bot, $id) {
            $bot->answerCallbackQuery(text: "Sotib olish funksiyasi tez kunda qo'shiladi! (ID: $id)");
        });
    }
}
