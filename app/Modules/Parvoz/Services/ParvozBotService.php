<?php

namespace App\Modules\Parvoz\Services;

use App\Modules\Parvoz\Models\ParvozBot;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ParvozBotService
{
    public function setupBot(ParvozBot $bot): array
    {
        $telegram = new ParvozTelegramService($bot->token);

        $me = $telegram->getMe();
        if ($me['ok'] ?? false) {
            $bot->update(['username' => $me['result']['username'] ?? $bot->username]);
        }

        if (!$bot->webhook_secret) {
            $bot->update(['webhook_secret' => Str::random(32)]);
        }

        $webhookUrl = url("/telegram/parvoz-webhook/{$bot->id}?secret={$bot->webhook_secret}");
        $result = $telegram->setWebhook($webhookUrl);

        $bot->update(['webhook_set' => (bool) ($result['ok'] ?? false)]);

        Log::info('[ParvozBot] Webhook set', [
            'bot_id' => $bot->id,
            'url'    => $webhookUrl,
            'result' => $result,
        ]);

        return $result;
    }

    public function deleteWebhook(ParvozBot $bot): array
    {
        $result = (new ParvozTelegramService($bot->token))->deleteWebhook();
        $bot->update(['webhook_set' => false]);

        return $result;
    }
}
