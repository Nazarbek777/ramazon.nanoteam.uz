<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Book\Services\TelegramService;

class SetBookBotWebhook extends Command
{
    protected $signature = 'book-bot:set-webhook {url}';
    protected $description = 'Set the Telegram Bot Webhook URL';

    public function handle(): void
    {
        $url = $this->argument('url');

        if (!str_starts_with($url, 'https://')) {
            $this->error('Webhook URL must be HTTPS!');
            return;
        }

        $telegram = new TelegramService();
        $result = $telegram->setWebhook($url);

        if ($result['ok'] ?? false) {
            $this->info("✅ Webhook muvaffaqiyatli sozlandi: {$url}");
        } else {
            $this->error('❌ Webhook sozlashda xato: ' . ($result['description'] ?? 'Unknown error'));
        }
    }
}
