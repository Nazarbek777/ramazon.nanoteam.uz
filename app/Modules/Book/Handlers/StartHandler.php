<?php

namespace App\Modules\Book\Handlers;

use App\Modules\Book\Services\BookService;
use SergiX44\Nutgram\Nutgram;

class StartHandler
{
    public function __construct(
        protected BookService $bookService
    ) {
    }

    public function __invoke(Nutgram $bot): void
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

        $referralLink = "https://t.me/" . $bot->getMe()->username . "?start=" . $user->telegram_id;

        $bot->sendMessage(
            text: "Do'stlaringizni taklif qiling va har bir do'stingiz uchun 1 ballga ega bo'ling! \n\nSizning havolangiz: \n`{$referralLink}`",
            parse_mode: 'Markdown'
        );

        // Show main menu (could also be a separate class or method in a helper)
        (new MenuHandler())($bot);
    }
}
