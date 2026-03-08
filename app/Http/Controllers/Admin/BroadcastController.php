<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\BotLogger;
use App\Models\User;
use App\Jobs\BroadcastJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BroadcastController extends Controller
{
    private string $token = '8756417207:AAFyg2vohZbQFrECi6q1qKlN1ep_uGwf-LM';

    public function index()
    {
        $userCount = User::whereNotNull('telegram_id')->count();
        return view('admin.broadcast.index', compact('userCount'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required_without:message_link|string|nullable',
            'message_link' => 'required_without:message|string|nullable',
            'image' => 'nullable|image|max:5120',
            'button_text' => 'nullable|string|max:50',
            'button_url' => 'nullable|url',
        ]);

        // Store image if provided
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('broadcasts', 'public');
        }

        $users = User::whereNotNull('telegram_id')->get();

        $isCopy = !empty($request->message_link);
        $broadcastData = [
            'message' => $request->message,
            'button_text' => $request->button_text,
            'button_url' => $request->button_url,
            'image_path' => $imagePath,
            'message_link' => $request->message_link,
        ];

        Log::info("Broadcast initiation debug", [
            'is_copy' => $isCopy,
            'image_path' => $imagePath,
            'link' => $request->message_link,
            'message_length' => strlen($request->message ?? '')
        ]);

        if ($isCopy) {
            $link = $request->message_link;
            Log::info("Attempting to parse Telegram link", ['link' => $link]);
            // More robust regex for Telegram links
            if (preg_match('/t\.me\/(?:c\/)?([^\/]+)\/(\d+)/', $link, $matches)) {
                $fromChatIdRaw = $matches[1];
                $messageId = $matches[2];

                if (is_numeric($fromChatIdRaw)) {
                    // Numerical ID for private channels must start with -100
                    $fromChatId = (str_starts_with($fromChatIdRaw, '-100')) ? $fromChatIdRaw : ('-100' . $fromChatIdRaw);
                } else {
                    $fromChatId = '@' . $fromChatIdRaw;
                }

                $broadcastData['from_chat_id'] = $fromChatId;
                $broadcastData['message_id'] = $messageId;

                Log::info("Successfully parsed Telegram link", [
                    'raw_chat' => $fromChatIdRaw,
                    'final_chat' => $fromChatId,
                    'message_id' => $messageId
                ]);
            } else {
                Log::error("Regex failed for Telegram link", ['link' => $link]);
                return redirect()->back()->with('error', 'Telegram xabar linki noto\'g\'ri formatda (Regex failed).');
            }
        }

        foreach ($users as $user) {
            BroadcastJob::dispatch($user->telegram_id, $broadcastData);
        }

        $msg = "✅ Broadcast navbatga qo'shildi ({$users->count()} ta foydalanuvchi).";
        return redirect()->back()->with('success', $msg);
    }

    private function copyTelegramMessage(string $chatId, string $fromChatId, string $messageId): string
    {
        $url = "https://api.telegram.org/bot{$this->token}/copyMessage";

        $params = [
            'chat_id' => $chatId,
            'from_chat_id' => $fromChatId,
            'message_id' => $messageId,
        ];

        return $this->executeCurl($url, $params);
    }

    private function sendTelegramMessage(string $chatId, string $text, ?string $imagePath = null, ?string $buttonText = null, ?string $buttonUrl = null): string
    {
        $method = $imagePath ? 'sendPhoto' : 'sendMessage';
        $url = "https://api.telegram.org/bot{$this->token}/{$method}";

        $params = [
            'chat_id' => $chatId,
            'parse_mode' => 'HTML',
        ];

        if ($imagePath) {
            $params['photo'] = new \CURLFile($imagePath);
            $params['caption'] = $text;
        } else {
            $params['text'] = $text;
        }

        if ($buttonText && $buttonUrl) {
            $params['reply_markup'] = json_encode([
                'inline_keyboard' => [[['text' => $buttonText, 'url' => $buttonUrl]]]
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
        if (curl_errno($ch)) {
            throw new \Exception('CURL error: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }
}
