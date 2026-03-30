<?php

namespace App\Modules\Book\Services;

use App\Modules\Book\Models\BookUser;
use App\Modules\Book\Models\Referral;
use Illuminate\Support\Facades\DB;
use App\Modules\Book\Services\TelegramService;
use App\Modules\Bookstore\Models\Book as BookstoreBook;

class BookService
{
    protected TelegramService $telegram;

    public function __construct()
    {
        $this->telegram = new TelegramService();
    }

    public function getOrCreateUser(array $tgUser, ?int $referrerTgId = null): BookUser
    {
        return DB::transaction(function () use ($tgUser, $referrerTgId) {
            $user = BookUser::where('telegram_id', $tgUser['id'])->first();

            if (!$user) {
                $referrerId = null;
                if ($referrerTgId && $referrerTgId != $tgUser['id']) {
                    $referrer = BookUser::where('telegram_id', $referrerTgId)->first();
                    if ($referrer) {
                        $referrerId = $referrer->id;
                    }
                }

                $user = BookUser::create([
                    'telegram_id' => $tgUser['id'],
                    'username' => $tgUser['username'] ?? null,
                    'first_name' => $tgUser['first_name'] ?? null,
                    'last_name' => $tgUser['last_name'] ?? null,
                    'referrer_id' => $referrerId,
                ]);

                // Referrerni saqlaymiz, lekin ballni telefon kiritilganda beramiz
            }

            return $user;
        });
    }

    public function processReferral(int $referrerId, int $referredId): void
    {
        // Oldinroq ball berilganligini tekshiramiz
        $exists = Referral::where('referred_id', $referredId)->exists();
        if ($exists) {
            return;
        }

        $points = 1;

        Referral::create([
            'referrer_id' => $referrerId,
            'referred_id' => $referredId,
            'points_earned' => $points,
        ]);

        BookUser::where('id', $referrerId)->increment('points', $points);

        $referrer = BookUser::find($referrerId);
        $referred = BookUser::find($referredId);

        if ($referrer && $referred && !empty($referrer->telegram_id)) {
            $name = $referred->first_name ?: 'Foydalanuvchi';
            $msg = "🎉 <b>Tabriklaymiz!</b>\n\nSizning havolangiz orqali <b>{$name}</b> konkursga qo'shildi va sizga <b>1 ball</b> taqdim etildi! 👏\n\nKo'proq do'stlaringizni taklif qilib, g'alaba qozonish imkoniyatingizni oshiring!";
            $this->telegram->sendMessage($referrer->telegram_id, $msg);
        }
    }

    public function getLeaderboard(int $limit = 10)
    {
        return BookUser::orderByDesc('points')->limit($limit)->get();
    }

    public function searchBooks(string $query)
    {
        return BookstoreBook::where('title', 'like', "%{$query}%")
            ->orWhere('author', 'like', "%{$query}%")
            ->limit(10)
            ->get();
    }
}
