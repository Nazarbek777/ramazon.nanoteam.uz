<?php

namespace App\Modules\Parvoz\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ParvozTelegramService
{
    protected string $token;
    protected string $apiUrl;

    public function __construct(string $token)
    {
        $this->token = $token;
        $this->apiUrl = "https://api.telegram.org/bot{$token}";
    }

    public function sendMessage(int|string $chatId, string $text, array $options = []): array
    {
        return $this->request('sendMessage', array_merge([
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'HTML',
        ], $options));
    }

    /** Pastdagi doimiy menyu tugmalari */
    public function sendWithMenu(int|string $chatId, string $text, array $keyboard): array
    {
        return $this->sendMessage($chatId, $text, [
            'reply_markup' => json_encode([
                'keyboard'        => $keyboard,
                'resize_keyboard' => true,
            ]),
        ]);
    }

    /** Xabar ichidagi tanlov tugmalari */
    public function sendWithInline(int|string $chatId, string $text, array $keyboard): array
    {
        return $this->sendMessage($chatId, $text, [
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
        ]);
    }

    public function sendContactRequest(int|string $chatId, string $text): array
    {
        return $this->sendMessage($chatId, $text, [
            'reply_markup' => json_encode([
                'keyboard' => [
                    [['text' => '📱 Raqamni yuborish', 'request_contact' => true]],
                ],
                'resize_keyboard'   => true,
                'one_time_keyboard' => true,
            ]),
        ]);
    }

    public function answerCallbackQuery(string $callbackId, string $text = ''): array
    {
        return $this->request('answerCallbackQuery', [
            'callback_query_id' => $callbackId,
            'text'              => $text,
        ]);
    }

    public function getMe(): array
    {
        return $this->request('getMe', []);
    }

    public function setWebhook(string $url): array
    {
        return $this->request('setWebhook', [
            'url'             => $url,
            'allowed_updates' => json_encode(['message', 'callback_query']),
        ]);
    }

    public function deleteWebhook(): array
    {
        return $this->request('deleteWebhook', []);
    }

    protected function request(string $method, array $params): array
    {
        try {
            $response = Http::timeout(15)->asForm()->post("{$this->apiUrl}/{$method}", $params);
            $result = $response->json() ?? [];

            if (!($result['ok'] ?? false)) {
                Log::warning('[ParvozBot] Telegram API error', [
                    'method'   => $method,
                    'response' => $result,
                ]);
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error('[ParvozBot] Telegram request failed', [
                'method' => $method,
                'error'  => $e->getMessage(),
            ]);

            return ['ok' => false, 'description' => $e->getMessage()];
        }
    }
}
