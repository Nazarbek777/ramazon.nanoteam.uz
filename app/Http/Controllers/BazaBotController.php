<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BazaBotController extends Controller
{
    private string $token = '8504067021:AAFJgY8gXh8xlvQxNdIfbn2h4xk6bDE3dpM';

    public function handle(Request $request)
    {
        try {
            $update = $request->all();
            Log::channel('single')->info('[BazaBot] Update: ' . json_encode($update));

            if (isset($update['message'])) {
                $chatId   = $update['message']['chat']['id'];
                $text     = $update['message']['text'] ?? '';
                $from     = $update['message']['from'];

                if ($text === '/start') {
                    $this->registerUser($chatId, $from);
                    $this->sendWelcome($chatId, $from);
                }
            }

            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            Log::channel('single')->error('[BazaBot] Error: ' . $e->getMessage());
            return response()->json(['ok' => false], 500);
        }
    }

    // ─── Register user to JSON storage ───────────────────────────────────────

    private function registerUser(int $chatId, array $from): void
    {
        $users = $this->loadUsers();

        $users[$chatId] = [
            'chat_id'    => $chatId,
            'first_name' => $from['first_name'] ?? '',
            'username'   => $from['username'] ?? '',
            'registered' => now()->toDateTimeString(),
        ];

        Storage::put('baza_users.json', json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public static function loadUsers(): array
    {
        if (!Storage::exists('baza_users.json')) {
            return [];
        }
        return json_decode(Storage::get('baza_users.json'), true) ?? [];
    }

    // ─── Welcome message ──────────────────────────────────────────────────────

    private function sendWelcome(int $chatId, array $from): void
    {
        $name = $from['first_name'] ?? 'Foydalanuvchi';

        $text = "👋 Assalomu alaykum, <b>{$name}</b>!\n\n";
        $text .= "✅ Siz muvaffaqiyatli ro'yxatdan o'tdingiz.\n\n";
        $text .= "📦 Ushbu bot har kuni <b>soat 08:00</b> da serverdan so'nggi <b>ma'lumotlar bazasini</b> yuborib turadi.\n\n";
        $text .= "⏳ Birinchi baza ertaga yuboriladi. Sabr bilan kuting!";

        $this->callApi('sendMessage', [
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'HTML',
        ]);
    }

    // ─── Telegram API helper ──────────────────────────────────────────────────

    public function callApi(string $method, array $params): ?array
    {
        $url = "https://api.telegram.org/bot{$this->token}/{$method}";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $params,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 30,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}
