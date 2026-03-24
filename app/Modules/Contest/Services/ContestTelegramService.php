<?php

namespace App\Modules\Contest\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContestTelegramService
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
        $params = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ], $options);

        return $this->request('sendMessage', $params);
    }

    public function deleteMessage(int|string $chatId, int $messageId): array
    {
        return $this->request('deleteMessage', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]);
    }

    public function sendMessageWithKeyboard(int|string $chatId, string $text, array $keyboard): array
    {
        return $this->sendMessage($chatId, $text, [
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard,
            ]),
        ]);
    }

    public function sendMessageWithReplyKeyboard(int|string $chatId, string $text, array $keyboard, bool $resize = true): array
    {
        return $this->sendMessage($chatId, $text, [
            'reply_markup' => json_encode([
                'keyboard' => $keyboard,
                'resize_keyboard' => $resize,
                'one_time_keyboard' => false,
            ]),
        ]);
    }

    public function sendContactRequest(int|string $chatId, string $text): array
    {
        return $this->sendMessage($chatId, $text, [
            'reply_markup' => json_encode([
                'keyboard' => [
                    [['text' => '📱 Raqamni yuborish', 'request_contact' => true]],
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]),
        ]);
    }

    public function sendPhoto(int|string $chatId, string $photo, string $caption = '', array $options = []): array
    {
        $params = array_merge([
            'chat_id' => $chatId,
            'photo' => $photo,
            'caption' => $caption,
            'parse_mode' => 'HTML',
        ], $options);

        return $this->request('sendPhoto', $params);
    }

    public function answerCallbackQuery(string $callbackQueryId, string $text = '', bool $showAlert = false): array
    {
        return $this->request('answerCallbackQuery', [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
            'show_alert' => $showAlert,
        ]);
    }

    public function getMe(): array
    {
        return $this->request('getMe');
    }

    public function setWebhook(string $url): array
    {
        return $this->request('setWebhook', ['url' => $url]);
    }

    public function deleteWebhook(): array
    {
        return $this->request('deleteWebhook');
    }

    public function getChatMember(string $chatId, int $userId): array
    {
        return $this->request('getChatMember', [
            'chat_id' => $chatId,
            'user_id' => $userId,
        ]);
    }

    public function isUserInChannel(string $channelUsername, int $userId): bool
    {
        $result = $this->getChatMember($channelUsername, $userId);

        if (!($result['ok'] ?? false)) {
            return false;
        }

        $status = $result['result']['status'] ?? 'left';

        return in_array($status, ['member', 'administrator', 'creator']);
    }

    protected function request(string $method, array $params = []): array
    {
        Log::info("[ContestBot] Request: {$method}", ['params' => $params]);
        try {
            $response = Http::post("{$this->apiUrl}/{$method}", $params);
            $json = $response->json() ?? [];
            Log::info("[ContestBot] Response: {$method}", ['json' => $json]);
            return $json;
        } catch (\Exception $e) {
            Log::error("[ContestBot] API error [{$method}]: " . $e->getMessage());
            return ['ok' => false, 'description' => $e->getMessage()];
        }
    }
}
