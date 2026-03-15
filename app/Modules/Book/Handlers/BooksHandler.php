<?php

namespace App\Modules\Book\Handlers;

use App\Modules\Book\Models\Book;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class BooksHandler
{
    public function __invoke(Nutgram $bot): void
    {
        $books = Book::all();

        if ($books->isEmpty()) {
            $bot->sendMessage(text: "Hozircha kitoblar ro'yxati bo'sh. Tez kunda yangi kitoblar qo'shiladi!");
            return;
        }

        $bot->sendMessage(text: "📚 *Mavjud kitoblar ro'yxati:*", parse_mode: 'Markdown');

        foreach ($books as $book) {
            $text = "📖 *{$book->title}*\n";
            $text .= "👤 Muallif: {$book->author}\n";
            $text .= "💰 Narxi: {$book->price} so'm\n";
            $text .= "📦 Qoldiq: {$book->stock} dona\n";

            $bot->sendMessage(
                text: $text,
                parse_mode: 'Markdown',
                reply_markup: InlineKeyboardMarkup::make()
                    ->addRow(InlineKeyboardButton::make("Sotib olish", callback_data: "buy_{$book->id}"))
            );
        }
    }
}
