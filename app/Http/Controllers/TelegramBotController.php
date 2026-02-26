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
                
                // Contact message (Phone Number)
                if (isset($update['message']['contact'])) {
                    $this->handleContact($update['message']);
                    return response()->json(['status' => 'success']);
                }

                $text = $update['message']['text'] ?? '';

                if ($text === '/start') {
                    $this->sendStartMessage($chatId, $update['message']['from']);
                }
            }
            
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::channel('single')->error('Telegram Error: ' . $e->getMessage());
            Log::channel('single')->error($e->getTraceAsString());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function handleContact($message)
    {
        $contact = $message['contact'];
        $telegramId = $contact['user_id'];
        $phoneNumber = $contact['phone_number'];
        $firstName = $contact['first_name'] ?? 'User';

        $user = \App\Models\User::updateOrCreate(
            ['telegram_id' => $telegramId],
            [
                'name' => $firstName,
                'phone_number' => $phoneNumber,
                // generate a fake email if needed, or leave it null if migration allows
                'email' => $telegramId . '@telegram.com', 
                'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(12)),
            ]
        );

        $this->sendMessage($telegramId, "Rahmat! Ma'lumotlaringiz saqlandi. Endi testni boshlashingiz mumkin.");
        $this->sendStartMessage($telegramId, $message['from'], false); // Send with WebApp button now
    }

    private function sendStartMessage($chatId, $from, $requestContact = true)
    {
        $token = '8147881295:AAE9Zb2zBWmQw7iP_hasy_5Pn0rgLiT1YCA';
        $webAppUrl = 'https://test.nanoteam.uz/webapp';
        
        // If we don't have the user's phone yet, ask for it
        $user = \App\Models\User::where('telegram_id', $chatId)->whereNotNull('phone_number')->first();

        if (!$user && $requestContact) {
            $message = "Assalomu alaykum! Botdan foydalanish uchun telefon raqamingizni yuboring:";
            $keyboard = json_encode([
                'keyboard' => [
                    [['text' => '📱 Telefon raqamni yuborish', 'request_contact' => true]]
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ]);
        } else {
            $message = "Xush kelibsiz! Testni boshlash uchun quyidagi tugmani bosing:";
            $keyboard = json_encode([
                'inline_keyboard' => [
                    [['text' => '🚀 Testni boshlash', 'web_app' => ['url' => $webAppUrl]]]
                ]
            ]);
        }

        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        
        $response = $this->callTelegramApi($url, [
            'chat_id' => $chatId,
            'text' => $message,
            'reply_markup' => $keyboard
        ]);
        
        Log::channel('single')->info('Telegram Send Response: ' . $response);
    }

    private function sendMessage($chatId, $text)
    {
        $token = '8147881295:AAE9Zb2zBWmQw7iP_hasy_5Pn0rgLiT1YCA';
        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        return $this->callTelegramApi($url, [
            'chat_id' => $chatId,
            'text' => $text
        ]);
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
