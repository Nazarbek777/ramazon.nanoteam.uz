<?php

namespace App\Modules\Book\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $token;
    protected string $apiUrl;

    public function __construct()
    {
        $this->token = '7294865765:AAFrcw4uNAmK-QOuZDW1hhDzzrUY3NXU9cs';
        $this->apiUrl = "https://api.telegram.org/bot{$this->token}";
    }

    /**
     * Send a text message to a chat.
     */
    public function sendMessage(int|string $chatId, string $text, array $options = []): array
    {
        $params = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ], $options);

        return $this->request('sendMessage', $params);
    }

    /**
     * Delete a message.
     */
    public function deleteMessage(int|string $chatId, int $messageId): array
    {
        return $this->request('deleteMessage', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]);
    }

    /**
     * Send a message with an inline keyboard.
     */
    public function sendMessageWithKeyboard(int|string $chatId, string $text, array $keyboard): array
    {
        return $this->sendMessage($chatId, $text, [
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard,
            ]),
        ]);
    }

    /**
     * Send a message with a persistent ReplyKeyboard (bottom buttons).
     */
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

    /**
     * Send a contact request button.
     */
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

    /**
     * Answer a callback query (removes the "loading" state on the button).
     */
    public function answerCallbackQuery(string $callbackQueryId, string $text = '', bool $showAlert = false): array
    {
        return $this->request('answerCallbackQuery', [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
            'show_alert' => $showAlert,
        ]);
    }

    /**
     * Get bot info.
     */
    public function getMe(): array
    {
        return $this->request('getMe');
    }

    /**
     * Set webhook URL.
     */
    public function setWebhook(string $url): array
    {
        return $this->request('setWebhook', ['url' => $url]);
    }

    /**
     * Check if a user is a member of a channel.
     */
    public function getChatMember(string $chatId, int $userId): array
    {
        return $this->request('getChatMember', [
            'chat_id' => $chatId,
            'user_id' => $userId,
        ]);
    }

    /**
     * Check if user is a member of the channel (not 'left' or 'kicked').
     */
    public function isUserInChannel(string $channelUsername, int $userId): bool
    {
        $result = $this->getChatMember($channelUsername, $userId);

        if (!($result['ok'] ?? false)) {
            return false;
        }

        $status = $result['result']['status'] ?? 'left';

        return in_array($status, ['member', 'administrator', 'creator']);
    }

    /**
     * Delete webhook.
     */
    public function deleteWebhook(): array
    {
        return $this->request('deleteWebhook');
    }

    /**
     * Make a request to the Telegram Bot API.
     */
    protected function request(string $method, array $params = []): array
    {
        Log::info("[Telegram] Request: " . $method, ['params' => $params]);
        try {
            $response = Http::post("{$this->apiUrl}/{$method}", $params);
            $json = $response->json() ?? [];
            Log::info("[Telegram] Response: " . $method, ['json' => $json]);
            return $json;
        } catch (\Exception $e) {
            Log::error("Telegram API error [{$method}]: " . $e->getMessage());
            return ['ok' => false, 'description' => $e->getMessage()];
        }
    }
}
