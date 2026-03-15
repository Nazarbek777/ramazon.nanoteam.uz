<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SergiX44\Nutgram\Nutgram;
use App\Modules\Book\Controllers\BotController;

class SetBookBotWebhook extends Command
{
    protected $signature = 'book-bot:set-webhook {url}';
    protected $description = 'Set the Telegram Bot Webhook URL';

    public function handle(Nutgram $bot): void
    {
        $url = $this->argument('url');

        if (!str_starts_with($url, 'https://')) {
            $this->error('Webhook URL must be HTTPS!');
            return;
        }

        $result = $bot->setWebhook($url);

        if ($result) {
            $this->info("Webhook successfully set to: $url");
        } else {
            $this->error('Failed to set webhook.');
        }
    }
}
