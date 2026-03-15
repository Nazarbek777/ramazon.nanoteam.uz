<?php

namespace App\Modules\Book\Handlers;

use App\Modules\Book\Services\BookService;
use SergiX44\Nutgram\Nutgram;

class ProfileHandler
{
    public function __construct(
        protected BookService $bookService
    ) {
    }

    public function __invoke(Nutgram $bot): void
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

        $bot->sendMessage(text: $text, parse_mode: 'Markdown');
    }
}
