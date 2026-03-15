<?php

namespace App\Modules\Book\Handlers;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class MenuHandler
{
    public function __invoke(Nutgram $bot): void
    {
        $bot->sendMessage(
            text: "Tanlang:",
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('📚 Kitoblar', callback_data: 'books'),
                    InlineKeyboardButton::make('🏆 Reyting', callback_data: 'leaderboard')
                )
                ->addRow(
                    InlineKeyboardButton::make('👤 Profil', callback_data: 'profile'),
                    InlineKeyboardButton::make('🔗 Taklif qilish', callback_data: 'referral')
                )
        );
    }
}
