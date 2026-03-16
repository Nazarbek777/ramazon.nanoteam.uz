<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Quiz;
use App\Models\User;
use App\Helpers\BotLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TelegramBotController extends Controller
{
    private $token;

    public function __construct()
    {
        $this->token = '8756417207:AAFyg2vohZbQFrECi6q1qKlN1ep_uGwf-LM';
    }

    public function handle(Request $request)
    {
        try {
            $update = $request->all();
            BotLogger::info('--- Telegram Update ---');
            BotLogger::info(json_encode($update, JSON_PRETTY_PRINT));

            if (isset($update['message'])) {
                $chatId = $update['message']['chat']['id'];
                $userId = $update['message']['from']['id'] ?? $chatId;

                if (!$this->checkChannelMembership($userId)) {
                    $this->sendSubscriptionRequest($chatId);
                    return response()->json(['status' => 'success']);
                }

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
                $chatId = $update['callback_query']['message']['chat']['id'] ?? $update['callback_query']['from']['id'];
                $userId = $update['callback_query']['from']['id'];

                if ($callbackData === 'check_subscription') {
                    if ($this->checkChannelMembership($userId)) {
                        $this->callTelegramApi("https://api.telegram.org/bot{$this->token}/answerCallbackQuery", [
                            'callback_query_id' => $update['callback_query']['id'],
                            'text' => "✅ Tabriklaymiz, guruhlarga a'zo bo'lganingiz tasdiqlandi!",
                            'show_alert' => true,
                        ]);
                        $this->callTelegramApi("https://api.telegram.org/bot{$this->token}/deleteMessage", [
                            'chat_id' => $chatId,
                            'message_id' => $update['callback_query']['message']['message_id']
                        ]);
                        $this->sendStartMessage($chatId, $update['callback_query']['from']);
                    } else {
                        $this->callTelegramApi("https://api.telegram.org/bot{$this->token}/answerCallbackQuery", [
                            'callback_query_id' => $update['callback_query']['id'],
                            'text' => "❌ Iltimos, barcha guruhlarga a'zo bo'ling!",
                            'show_alert' => true,
                        ]);
                    }
                    return response()->json(['status' => 'success']);
                }

                if (!$this->checkChannelMembership($userId)) {
                    $this->sendSubscriptionRequest($chatId);
                    return response()->json(['status' => 'success']);
                }

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
            $chatId = $update['message']['chat']['id'] ?? $update['callback_query']['from']['id'] ?? null;
            BotLogger::error('Telegram Error: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ':' . $e->getLine(), [], $chatId);
            return response()->json(['status' => 'error'], 500);
        }
    }

    private function handleContact($message)
    {
        $contact = $message['contact'];
        $telegramId = $contact['user_id'] ?? $message['from']['id'];
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
        $text = "📋 <b>YORIQNOMA</b>\n\n";
        $text .= "📚 <b>Nur kitoblari</b> konkursida ishtirok etish qoidalari:\n\n";
        $text .= "📌 <b>Qanday qatnashasiz:</b>\n";
        $text .= "1️⃣ Ko'rsatilgan guruhlarga a'zo bo'ling\n";
        $text .= "2️⃣ Do'stlaringizni taklif qiling\n";
        $text .= "3️⃣ Faol bo'ling va g'olibga aylaning!\n\n";
        $text .= "📞 <b>Murojaat uchun:</b> Guruhlardagi adminlar orqali bog'lanishingiz mumkin.";

        $extra = ['parse_mode' => 'HTML'];

        $this->sendMessage($chatId, $text, $extra);
    }

    private function sendStartMessage($chatId, $from)
    {
        // Check if user exists with phone
        $user = User::where('telegram_id', $chatId)->whereNotNull('phone_number')->first();

        if (!$user) {
            // Ask for phone number first
            $message = "Assalomu alaykum! 📚 <b>Nur kitoblari</b> konkursiga xush kelibsiz!\n\n";
            $message .= "Konkursda ishtirok etish va ajoyib sovg'alarga ega bo'lish uchun ro'yxatdan o'tishingiz kerak. 🎁\n\n";
            $message .= "Boshlash uchun pastdagi tugmani bosib, telefon raqamingizni yuboring 👇";

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
                'parse_mode' => 'HTML',
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

        $message = "📚 *Fanlarni tanlang:*\n\nQuyidagi fanlardan birini tanlang va testni boshlang:";

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

    private function checkChannelMembership($userId)
    {
        $channels = ['@Nurkitoblari_m1', '@NurArt_uz'];
        foreach ($channels as $channel) {
            $response = $this->callTelegramApi("https://api.telegram.org/bot{$this->token}/getChatMember", [
                'chat_id' => $channel,
                'user_id' => $userId
            ]);
            $data = json_decode($response, true);
            if (!isset($data['result']['status']) || in_array($data['result']['status'], ['left', 'kicked'])) {
                return false;
            }
        }
        return true;
    }

    private function sendSubscriptionRequest($chatId)
    {
        $text = "⚠️ <b>Diqqat!</b>\n\nBotdan to'liq foydalanish va konkursda qatnashish uchun quyidagi guruhlarga a'zo bo'lishingiz shart:\n\n";
        $text .= "1️⃣ @Nurkitoblari_m1\n";
        $text .= "2️⃣ @NurArt_uz\n\n";
        $text .= "<i>Guruhlarga a'zo bo'lgach «A'zolikni tekshirish» tugmasini bosing.</i>";

        $keyboard = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => "1-guruhga qo'shilish", 'url' => 'https://t.me/Nurkitoblari_m1']
                ],
                [
                    ['text' => "2-guruhga qo'shilish", 'url' => 'https://t.me/NurArt_uz']
                ],
                [
                    ['text' => "✅ A'zolikni tekshirish", 'callback_data' => 'check_subscription']
                ]
            ]
        ]);

        $this->callTelegramApi("https://api.telegram.org/bot{$this->token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
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
