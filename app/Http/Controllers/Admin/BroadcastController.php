<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\BotLogger;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'message'     => 'required|string',
            'image'       => 'nullable|image|max:5120',
            'button_text' => 'nullable|string|max:50',
            'button_url'  => 'nullable|url',
        ]);

        // Store image if provided
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('broadcasts', 'public');
        }

        $users = User::whereNotNull('telegram_id')->get();
        $count = 0;
        $failed = 0;

        foreach ($users as $user) {
            try {
                $result = $this->sendTelegramMessage(
                    $user->telegram_id,
                    $request->message,
                    $imagePath ? Storage::disk('public')->path($imagePath) : null,
                    $request->button_text,
                    $request->button_url
                );

                $decoded = json_decode($result, true);
                if ($decoded['ok'] ?? false) {
                    $count++;
                } else {
                    $failed++;
                    BotLogger::warning("Broadcast failed for {$user->telegram_id}: " . ($decoded['description'] ?? 'unknown'));
                }
            } catch (\Exception $e) {
                $failed++;
                BotLogger::error("Broadcast exception for {$user->telegram_id}: " . $e->getMessage());
            }
        }

        // Clean up stored image
        if ($imagePath) {
            Storage::disk('public')->delete($imagePath);
        }

        $msg = "✅ Yuborildi: {$count} ta";
        if ($failed > 0) $msg .= " | ❌ Xato: {$failed} ta";
        return redirect()->back()->with('success', $msg);
    }

    private function sendTelegramMessage(string $chatId, string $text, ?string $imagePath = null, ?string $buttonText = null, ?string $buttonUrl = null): string
    {
        $method = $imagePath ? 'sendPhoto' : 'sendMessage';
        $url    = "https://api.telegram.org/bot{$this->token}/{$method}";

        $params = [
            'chat_id'    => $chatId,
            'parse_mode' => 'HTML',
        ];

        if ($imagePath) {
            $params['photo']   = new \CURLFile($imagePath);
            $params['caption'] = $text;
        } else {
            $params['text'] = $text;
        }

        if ($buttonText && $buttonUrl) {
            $params['reply_markup'] = json_encode([
                'inline_keyboard' => [[['text' => $buttonText, 'url' => $buttonUrl]]]
            ]);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params); // array = multipart auto
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
