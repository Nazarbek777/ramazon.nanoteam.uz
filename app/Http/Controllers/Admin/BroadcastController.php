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
        ]);

        $users = User::whereNotNull('telegram_id')->get();
        $token = '8147881295:AAE9Zb2zBWmQw7iP_hasy_5Pn0rgLiT1YCA';
        $count = 0;

        foreach ($users as $user) {
            try {
                $this->sendTelegramMessage($token, $user->telegram_id, $request->message);
                $count++;
            } catch (\Exception $e) {
                Log::error("Broadcast failed for user {$user->telegram_id}: " . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', "Xabar {$count} ta foydalanuvchiga yuborildi.");
    }

    private function sendTelegramMessage($token, $chatId, $text)
    {
        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        $params = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        curl_close($ch);
    }
}
