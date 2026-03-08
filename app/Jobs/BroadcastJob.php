<?php

namespace App\Jobs;

use App\Helpers\BotLogger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class BroadcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $telegramId;
    protected $data;
    protected $token = '8756417207:AAFyg2vohZbQFrECi6q1qKlN1ep_uGwf-LM';

    public function __construct($telegramId, array $data)
    {
        $this->telegramId = $telegramId;
        $this->data = $data;
    }

    public function handle()
    {
        try {
            \Illuminate\Support\Facades\Log::info("BroadcastJob handling", [
                'user' => $this->telegramId,
                'is_copy' => !empty($this->data['message_link']),
                'data' => $this->data
            ]);

            if (!empty($this->data['message_link'])) {
                $result = $this->copyTelegramMessage();
            } else {
                $result = $this->sendTelegramMessage();
            }

            $decoded = json_decode($result, true);
            if (!($decoded['ok'] ?? false)) {
                BotLogger::warning("BroadcastJob failed for {$this->telegramId}: " . ($decoded['description'] ?? 'unknown'));
                \Illuminate\Support\Facades\Log::warning("Telegram API Error details", [
                    'user' => $this->telegramId,
                    'response' => $decoded,
                    'params' => [
                        'from_chat_id' => $this->data['from_chat_id'] ?? null,
                        'message_id' => $this->data['message_id'] ?? null
                    ]
                ]);
            }
        } catch (\Exception $e) {
            BotLogger::error("BroadcastJob exception for {$this->telegramId}: " . $e->getMessage());
        }
    }

    private function copyTelegramMessage(): string
    {
        $url = "https://api.telegram.org/bot{$this->token}/copyMessage";
        $params = [
            'chat_id' => $this->telegramId,
            'from_chat_id' => $this->data['from_chat_id'],
            'message_id' => $this->data['message_id'],
        ];

        return $this->executeCurl($url, $params);
    }

    private function sendTelegramMessage(): string
    {
        $method = !empty($this->data['image_path']) ? 'sendPhoto' : 'sendMessage';
        $url = "https://api.telegram.org/bot{$this->token}/{$method}";

        $params = [
            'chat_id' => $this->telegramId,
            'parse_mode' => 'HTML',
        ];

        if (!empty($this->data['image_path'])) {
            $absPath = Storage::disk('public')->path($this->data['image_path']);
            if (file_exists($absPath)) {
                $params['photo'] = new \CURLFile($absPath);
                $params['caption'] = $this->data['message'] ?? '';
            } else {
                // Flash back to message if image missing
                $url = "https://api.telegram.org/bot{$this->token}/sendMessage";
                $params['text'] = $this->data['message'] ?? '';
            }
        } else {
            $params['text'] = $this->data['message'] ?? '';
        }

        if (!empty($this->data['button_text']) && !empty($this->data['button_url'])) {
            $params['reply_markup'] = json_encode([
                'inline_keyboard' => [[['text' => $this->data['button_text'], 'url' => $this->data['button_url']]]]
            ]);
        }

        return $this->executeCurl($url, $params);
    }

    private function executeCurl(string $url, array $params): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
