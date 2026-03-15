<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SergiX44\Nutgram\Nutgram;
use App\Modules\Book\Controllers\BotController;

class RunBookBot extends Command
{
    protected $signature = 'book-bot:run';
    protected $description = 'Run the Book Competition Telegram Bot';

    public function handle(Nutgram $bot, \App\Modules\Book\BotManager $botManager): void
    {
        $this->info('Book Bot is starting in long-polling mode...');

        $botManager->registerHandlers($bot);

        $bot->run();
    }
}
