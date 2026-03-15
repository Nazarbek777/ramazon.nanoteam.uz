<?php

namespace App\Modules\Book\Services;

use App\Modules\Book\Models\BookUser;
use App\Modules\Book\Models\Referral;
use Illuminate\Support\Facades\DB;

class BookService
{
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

                if ($referrerId) {
                    $this->processReferral($referrerId, $user->id);
                }
            }

            return $user;
        });
    }

    protected function processReferral(int $referrerId, int $referredId): void
    {
        $points = 1; // Default points for referral

        Referral::create([
            'referrer_id' => $referrerId,
            'referred_id' => $referredId,
            'points_earned' => $points,
        ]);

        BookUser::where('id', $referrerId)->increment('points', $points);
    }

    public function getLeaderboard(int $limit = 10)
    {
        return BookUser::orderByDesc('points')->limit($limit)->get();
    }
}
