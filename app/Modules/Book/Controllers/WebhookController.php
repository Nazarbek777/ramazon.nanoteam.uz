<?php

namespace App\Modules\Book\Controllers;

use SergiX44\Nutgram\Nutgram;
use App\Modules\Book\BotManager;
use Illuminate\Http\Request;

class WebhookController
{
    public function handle(Nutgram $bot, BotManager $botManager)
    {
        $botManager->registerHandlers($bot);
        $bot->run();
    }
}
