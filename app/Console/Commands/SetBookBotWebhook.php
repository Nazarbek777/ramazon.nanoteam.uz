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

            // Bot sozlamalarini o'rnatish
            $description = "📚 «Nur kitoblar» doʻkonining bayramona sovgʻali tanloviga xush kelibsiz!\n\n" .
                "🎁 Ushbu bot orqali siz:\n" .
                "✅ Tanlovda roʻyxatdan oʻtishingiz;\n" .
                "✅ Doʻstlaringizni taklif qilib ball toʻplashingiz;\n" .
                "✅ Qurʼoni Karim va qimmatbaho kitoblar yutib olishingiz mumkin.\n\n" .
                "🚀 Ishtirok eting va bilimingizni boyiting!\n\n" .
                "👨‍💻 Taklif va kamchiliklar uchun: @NazarbekRashidov";

            $shortDescription = "📚 «Nur kitoblar» tanlov boti. Doʻstlarni taklif qiling va qimmatli kitoblarni yutib oling! 🎁";

            $telegram->setMyDescription($description);
            $telegram->setMyShortDescription($shortDescription);

            $this->info("ℹ️ Bot tavsifi (Description) va 'Haqida' (About) qismi yangilandi.");
        } else {
            $this->error('❌ Webhook sozlashda xato: ' . ($result['description'] ?? 'Unknown error'));
        }
    }
}
