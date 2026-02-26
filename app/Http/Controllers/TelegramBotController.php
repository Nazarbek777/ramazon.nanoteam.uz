<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramBotController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $update = $request->all();
            Log::channel('single')->info('--- Telegram Update Start ---');
            Log::channel('single')->info(json_encode($update, JSON_PRETTY_PRINT));

            if (isset($update['message'])) {
                $chatId = $update['message']['chat']['id'];
                $text = $update['message']['text'] ?? '';

                if ($text === '/start') {
                    $this->sendStartMessage($chatId);
                }
            }
            
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::channel('single')->error('Telegram Error: ' . $e->getMessage());
            Log::channel('single')->error($e->getTraceAsString());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function sendStartMessage($chatId)
    {
        $token = '8147881295:AAE9Zb2zBWmQw7iP_hasy_5Pn0rgLiT1YCA';
        $webAppUrl = 'https://test.nanoteam.uz/webapp';
        
        $message = "Assalomu alaykum! Test botiga xush kelibsiz.\n\nTestni boshlash uchun quyidagi tugmani bosing:";
        
        $keyboard = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => '🚀 Testni boshlash', 'web_app' => ['url' => $webAppUrl]]
                ]
            ]
        ]);

        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        
        $response = $this->callTelegramApi($url, [
            'chat_id' => $chatId,
            'text' => $message,
            'reply_markup' => $keyboard
        ]);
        
        Log::channel('single')->info('Telegram Send Response: ' . $response);
    }

    private function callTelegramApi($url, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For compatibility
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            Log::channel('single')->error('CURL Error: ' . curl_error($ch));
        }
        curl_close($ch);
        return $response;
    }
}
