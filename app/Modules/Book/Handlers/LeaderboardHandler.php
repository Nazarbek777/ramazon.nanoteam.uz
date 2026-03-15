<?php

namespace App\Modules\Book\Handlers;

use App\Modules\Book\Services\BookService;
use SergiX44\Nutgram\Nutgram;

class LeaderboardHandler
{
    public function __construct(
        protected BookService $bookService
    ) {
    }

    public function __invoke(Nutgram $bot): void
    {
        $leaders = $this->bookService->getLeaderboard(10);

        $text = "🏆 *Top 10 Ishtirokchilar*\n\n";
        foreach ($leaders as $index => $leader) {
            $name = $leader->first_name ?: "Foydalanuvchi";
            $text .= ($index + 1) . ". {$name} - *{$leader->points}* ball\n";
        }

        $bot->sendMessage(text: $text, parse_mode: 'Markdown');
    }
}
