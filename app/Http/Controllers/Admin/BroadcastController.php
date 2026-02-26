<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BroadcastController extends Controller
{
    public function index()
    {
        $userCount = User::whereNotNull('telegram_id')->count();
        return view('admin.broadcast.index', compact('userCount'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'image' => 'nullable|image|max:5120', // Max 5MB
            'button_text' => 'nullable|string|max:50',
            'button_url' => 'nullable|url',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('broadcasts', 'public');
        }

        $users = User::whereNotNull('telegram_id')->get();
        $token = '8147881295:AAE9Zb2zBWmQw7iP_hasy_5Pn0rgLiT1YCA';
        $count = 0;

        foreach ($users as $user) {
            try {
                $this->sendTelegramMessage(
                    $token, 
                    $user->telegram_id, 
                    $request->message, 
                    $imagePath ? public_path('storage/' . $imagePath) : null,
                    $request->button_text, 
                    $request->button_url
                );
                $count++;
            } catch (\Exception $e) {
                Log::error("Broadcast failed for user {$user->telegram_id}: " . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', "Xabar {$count} ta foydalanuvchiga yuborildi.");
    }

    private function sendTelegramMessage($token, $chatId, $text, $imagePath = null, $buttonText = null, $buttonUrl = null)
    {
        $method = $imagePath ? 'sendPhoto' : 'sendMessage';
        $url = "https://api.telegram.org/bot{$token}/{$method}";
        
        $params = [
            'chat_id' => $chatId,
            'parse_mode' => 'HTML',
        ];

        if ($method === 'sendPhoto') {
            $params['photo'] = new \CURLFile($imagePath);
            $params['caption'] = $text;
        } else {
            $params['text'] = $text;
        }

        if ($buttonText && $buttonUrl) {
            $params['reply_markup'] = json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => $buttonText, 'url' => $buttonUrl]
                    ]
                ]
            ]);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
