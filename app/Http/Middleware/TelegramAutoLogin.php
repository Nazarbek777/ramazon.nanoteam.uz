<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TelegramAutoLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        $telegramId = $request->query('telegram_id');

        if ($telegramId && (!Auth::check() || Auth::user()->telegram_id != $telegramId)) {
            $user = User::where('telegram_id', $telegramId)->first();
            if ($user) {
                Auth::login($user, true);
            }
        }

        return $next($request);
    }
}
