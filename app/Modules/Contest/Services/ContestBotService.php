<?php

namespace App\Modules\Contest\Services;

use App\Modules\Contest\Models\ContestBot;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ContestBotService
{
    /**
     * Create or update a bot and set up its webhook.
     */
    public function setupBot(ContestBot $bot): array
    {
        $telegram = new ContestTelegramService($bot->token);

        // Get bot info
        $me = $telegram->getMe();
        if ($me['ok'] ?? false) {
            $bot->update([
                'username' => $me['result']['username'] ?? $bot->username,
            ]);
        }

        // Generate webhook secret if not exists
        if (!$bot->webhook_secret) {
            $bot->update(['webhook_secret' => Str::random(32)]);
        }

        // Set webhook
        $webhookUrl = url("/telegram/contest-webhook/{$bot->id}?secret={$bot->webhook_secret}");
        $result = $telegram->setWebhook($webhookUrl);

        Log::info('[ContestBot] Webhook set', [
            'bot_id' => $bot->id,
            'url' => $webhookUrl,
            'result' => $result,
        ]);

        return $result;
    }

    /**
     * Remove webhook from a bot.
     */
    public function deleteWebhook(ContestBot $bot): array
    {
        $telegram = new ContestTelegramService($bot->token);
        return $telegram->deleteWebhook();
    }

    /**
     * Get bot info from Telegram API.
     */
    public function getBotInfo(ContestBot $bot): array
    {
        $telegram = new ContestTelegramService($bot->token);
        return $telegram->getMe();
    }
}
