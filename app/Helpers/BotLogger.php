<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class BotLogger
{
    // Admin Telegram chat ID to receive error alerts
    private static string $adminChatId = '5601028714';
    private static string $token = '8147881295:AAE9Zb2zBWmQw7iP_hasy_5Pn0rgLiT1YCA';

    /**
     * Log info message to bot log channel.
     */
    public static function info(string $message, array $context = []): void
    {
        Log::channel('bot')->info($message, $context);
    }

    /**
     * Log error and send Telegram alert to admin.
     */
    public static function error(string $message, array $context = [], ?string $userId = null): void
    {
        Log::channel('bot')->error($message, $context);

        self::notifyAdmin($message, $userId);
    }

    /**
     * Log warning.
     */
    public static function warning(string $message, array $context = []): void
    {
        Log::channel('bot')->warning($message, $context);
    }

    /**
     * Send Telegram message to admin about an error.
     */
    private static function notifyAdmin(string $message, ?string $userId = null): void
    {
        try {
            $now = now()->format('d.m.Y H:i:s');
            $text = "🚨 <b>BOT XATOSI</b>\n\n";
            $text .= "🕐 <b>Vaqt:</b> {$now}\n";
            if ($userId) {
                $text .= "👤 <b>User ID:</b> {$userId}\n";
            }
            $text .= "❌ <b>Xato:</b> " . htmlspecialchars(mb_substr($message, 0, 500));

            $ch = curl_init("https://api.telegram.org/bot" . self::$token . "/sendMessage");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'chat_id'    => self::$adminChatId,
                'text'       => $text,
                'parse_mode' => 'HTML',
            ]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Throwable $e) {
            // Fail silently — don't break the app if alert fails
            Log::channel('bot')->warning('Admin alert failed: ' . $e->getMessage());
        }
    }
}
