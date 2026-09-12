<?php

namespace App\Modules\Parvoz\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Bot dialog holati — ball kiritish sehrgari (guruh → o'quvchi → fan → ball) uchun.
 */
class ParvozState extends Model
{
    protected $table = 'parvoz_states';

    protected $fillable = ['telegram_id', 'state', 'payload'];

    protected $casts = ['payload' => 'array'];

    public static function for(string|int $telegramId): self
    {
        return static::firstOrCreate(['telegram_id' => (string) $telegramId]);
    }

    public function set(?string $state, array $payload = []): void
    {
        $this->update(['state' => $state, 'payload' => $payload]);
    }

    public function clear(): void
    {
        $this->update(['state' => null, 'payload' => null]);
    }
}
