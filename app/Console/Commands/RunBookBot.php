<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SergiX44\Nutgram\Nutgram;
use App\Modules\Book\Controllers\BotController;

class RunBookBot extends Command
{
    protected $signature = 'book-bot:run';
    protected $description = 'Run the Book Competition Telegram Bot';

    public function handle(Nutgram $bot, BotController $controller): void
    {
        $this->info('Book Bot is starting...');

        $bot->onCommand('start', [$controller, 'start']);
        $bot->onCommand('profile', [$controller, 'profile']);
        $bot->onCommand('leaderboard', [$controller, 'leaderboard']);
        $bot->onCommand('referral', [$controller, 'referral']);

        $bot->onCallbackQueryData('profile', [$controller, 'profile']);
        $bot->onCallbackQueryData('leaderboard', [$controller, 'leaderboard']);
        $bot->onCallbackQueryData('referral', [$controller, 'referral']);
        $bot->onCallbackQueryData('books', [$controller, 'books']);

        $bot->run();
    }
}
