<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Book\Services\TelegramService;

class DeleteBookBotWebhook extends Command
{
    protected $signature = 'book-bot:delete-webhook';
    protected $description = 'Delete the Telegram Bot Webhook';

    public function handle(): void
    {
        $telegram = new TelegramService();
        $result = $telegram->deleteWebhook();

        if ($result['ok'] ?? false) {
            $this->info("✅ Webhook muvaffaqiyatli o'chirildi!");
        } else {
            $this->error('❌ Xatolik: ' . ($result['description'] ?? 'Unknown error'));
        }
    }
}
