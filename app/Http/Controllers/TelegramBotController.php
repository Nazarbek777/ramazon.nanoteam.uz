<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TelegramBotController extends Controller
{
    private $token;

    public function __construct()
    {
        $this->token = '8147881295:AAE9Zb2zBWmQw7iP_hasy_5Pn0rgLiT1YCA';
    }

    public function handle(Request $request)
    {
        try {
            $update = $request->all();
            Log::channel('single')->info('--- Telegram Update ---');
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
                } elseif ($text === '/yoriqnoma' || $text === '📋 Yoriqnoma') {
                    $this->sendYoriqnoma($chatId);
                }
            }

            // Handle inline button callbacks
            if (isset($update['callback_query'])) {
                $callbackData = $update['callback_query']['data'] ?? '';
                $chatId = $update['callback_query']['from']['id'];

                if ($callbackData === 'yoriqnoma') {
                    $this->sendYoriqnoma($chatId);
                    $this->callTelegramApi("https://api.telegram.org/bot{$this->token}/answerCallbackQuery", [
                        'callback_query_id' => $update['callback_query']['id'],
                    ]);
                } elseif ($callbackData === 'show_subjects') {
                    $this->sendSubjectsMenu($chatId);
                    $this->callTelegramApi("https://api.telegram.org/bot{$this->token}/answerCallbackQuery", [
                        'callback_query_id' => $update['callback_query']['id'],
                    ]);
                }
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::channel('single')->error('Telegram Error: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }

    private function handleContact($message)
    {
        $contact = $message['contact'];
        $telegramId = $contact['user_id'];
        $phoneNumber = $contact['phone_number'];
        $firstName = $contact['first_name'] ?? 'User';

        User::updateOrCreate(
            ['telegram_id' => $telegramId],
            [
                'name' => $firstName,
                'phone_number' => $phoneNumber,
                'email' => $telegramId . '@telegram.com',
                'password' => Hash::make(Str::random(12)),
            ]
        );

        $this->sendMessage($telegramId, "✅ Rahmat! Ma'lumotlaringiz saqlandi.");
        // Show yoriqnoma first, then subjects
        $this->sendYoriqnoma($telegramId, true);
    }

    private function sendYoriqnoma($chatId, $withSubjectsButton = false)
    {
        $text = "📋 *YORIQNOMA*\n\n";
        $text .= "🌟 Maktabgacha ta'lim tarbiyachilari uchun attestatsiya tayyorgarlik boti!\n\n";
        $text .= "✅ *Test yechib ko'rish — BEPUL!*\n\n";
        $text .= "📌 *Qanday foydalanish:*\n";
        $text .= "1️⃣ Fanlardan birini tanlang\n";
        $text .= "2️⃣ Testni boshlang\n";
        $text .= "3️⃣ Natijangizni ko'ring\n\n";
        $text .= "📢 *Kanal:* @attestatsiya_jamoa\n";
        $text .= "👤 *Admin:* @abdullayevna_jamoa\n\n";
        $text .= "Kursga qo'shilmoqchi bo'lsangiz — adminimizga yozing! 👆";

        $extra = ['parse_mode' => 'Markdown'];

        if ($withSubjectsButton) {
            $extra['reply_markup'] = json_encode([
                'inline_keyboard' => [[[
                    'text' => '📚 Testlarni boshlash',
                    'callback_data' => 'show_subjects'
                ]]]
            ]);
        }

        $this->sendMessage($chatId, $text, $extra);
    }

    private function sendStartMessage($chatId, $from)
    {
        // Check if user exists with phone
        $user = User::where('telegram_id', $chatId)->whereNotNull('phone_number')->first();

        if (!$user) {
            // Ask for phone number first
            $message = "Assalomu alaykum! 👋\nBotdan foydalanish uchun telefon raqamingizni yuboring:";
            $keyboard = json_encode([
                'keyboard' => [
                    [['text' => '📱 Telefon raqamni yuborish', 'request_contact' => true]]
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ]);

            $this->callTelegramApi("https://api.telegram.org/bot{$this->token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'reply_markup' => $keyboard
            ]);
            return;
        }

        // User exists — show yoriqnoma first, then subjects
        $this->sendYoriqnoma($chatId, true);
    }

    private function sendSubjectsMenu($chatId)
    {
        $webAppBase = 'https://test.nanoteam.uz/webapp?telegram_id=' . $chatId;

        // Get all subjects with their quizzes
        $subjects = Subject::with('quizzes')->get();

        if ($subjects->isEmpty()) {
            $this->sendMessage($chatId, "Hozircha fanlar mavjud emas. Iltimos, keyinroq urinib ko'ring.");
            return;
        }

        $message = "📚 *Attestatsiya fanlarini tanlang:*\n\nQuyidagi fanlardan birini tanlang va testni boshlang:";

        // Build inline keyboard with 2 columns
        $rows = [];
        $row = [];
        foreach ($subjects as $index => $subject) {
            $quiz = $subject->quizzes->first();
            if (!$quiz) continue;

            $url = $webAppBase . '&quiz_id=' . $quiz->id;

            $row[] = [
                'text' => $subject->name,
                'web_app' => ['url' => 'https://test.nanoteam.uz/webapp/subject/' . $subject->id . '?telegram_id=' . $chatId]
            ];

            // 1 button per row for better readability
            $rows[] = $row;
            $row = [];
        }

        // Add Yoriqnoma + Barcha testlar buttons
        $rows[] = [[
            'text' => '📌 Yoriqnoma',
            'callback_data' => 'yoriqnoma'
        ]];
        $rows[] = [[
            'text' => '📱 Barcha testlar',
            'web_app' => ['url' => $webAppBase]
        ]];

        $keyboard = json_encode(['inline_keyboard' => $rows]);

        $this->callTelegramApi("https://api.telegram.org/bot{$this->token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ]);
    }

    private function sendMessage($chatId, $text, $extra = [])
    {
        return $this->callTelegramApi("https://api.telegram.org/bot{$this->token}/sendMessage", array_merge([
            'chat_id' => $chatId,
            'text' => $text
        ], $extra));
    }

    private function callTelegramApi($url, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            Log::channel('single')->error('CURL Error: ' . curl_error($ch));
        }
        curl_close($ch);
        return $response;
    }
}
