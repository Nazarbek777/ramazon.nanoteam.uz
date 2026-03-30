<?php

namespace App\Modules\Book\Commands;

use Illuminate\Console\Command;
use App\Modules\Book\Models\BookUser;
use App\Modules\Book\Services\TelegramService;
use Illuminate\Support\Facades\Log;

class BroadcastCommand extends Command
{
    protected $signature = 'book:broadcast {message?}';
    protected $description = 'Send a broadcast message to all Book bot users';

    public function handle()
    {
        $message = $this->argument('message');

        if (!$message) {
            $message = "📚 <b>\"Nur kitoblar\" botida yangilik!</b>\n\nEndi botimiz orqali kitoblarni nafaqat izlash, balki to'g'ridan-to'g'ri <b>buyurtma qilish</b> imkoniyati yaratildi! 🛍\n\n🔍 Kerakli kitobni topish uchun uning nomini yozing va <b>\"🛍 Buyurtma qilish\"</b> tugmasini bosing.\n\nBotni qayta ishga tushirish uchun: /start";
        }

        $users = BookUser::whereNotNull('telegram_id')->get();
        $total = $users->count();
        $this->info("Broadcasting to {$total} users...");

        $success = 0;
        $failed = 0;

        $telegram = new TelegramService();

        foreach ($users as $user) {
            try {
                $response = $telegram->sendMessage($user->telegram_id, $message);
                if ($response['ok'] ?? false) {
                    $success++;
                } else {
                    $failed++;
                    Log::warning("[BookBroadcast] Failed for {$user->telegram_id}: " . ($response['description'] ?? 'Unknown error'));
                }
            } catch (\Exception $e) {
                $failed++;
                Log::error("[BookBroadcast] Error for {$user->telegram_id}: " . $e->getMessage());
            }

            // Telegram rate limitga tushmaslik uchun ozgina kutamiz
            if ($success % 20 == 0) {
                usleep(500000); // 0.5 soniya
            }
        }

        $this->info("Broadcast finished! Success: {$success}, Failed: {$failed}");
    }
}
