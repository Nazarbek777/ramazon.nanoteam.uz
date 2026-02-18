<?php

namespace App\Helpers;

use Carbon\Carbon;

class RamadanHelper
{
    // Ramazon 2026 sanalari (1447 hijriy)
    const RAMADAN_START = '2026-02-19';
    const RAMADAN_END = '2026-03-20'; // 30 kun

    /**
     * Bugungi sana Ramazon oyi ichidami?
     */
    public static function isRamadan(?Carbon $date = null): bool
    {
        $date = $date ?? Carbon::today();
        $start = Carbon::parse(self::RAMADAN_START);
        $end = Carbon::parse(self::RAMADAN_END);

        return $date->between($start, $end);
    }

    /**
     * Ramazon nechichi kuni (1-30)
     */
    public static function dayNumber(?Carbon $date = null): ?int
    {
        $date = $date ?? Carbon::today();
        $start = Carbon::parse(self::RAMADAN_START);

        if (!self::isRamadan($date)) {
            return null;
        }

        return $start->diffInDays($date) + 1;
    }

    /**
     * Ramazongacha necha kun qoldi
     */
    public static function daysUntilRamadan(?Carbon $date = null): ?int
    {
        $date = $date ?? Carbon::today();
        $start = Carbon::parse(self::RAMADAN_START);

        if ($date->lt($start)) {
            return $date->diffInDays($start);
        }

        return null;
    }

    /**
     * Ramazon boshlanish va tugash sanalari
     */
    public static function dates(): array
    {
        return [
            'start' => Carbon::parse(self::RAMADAN_START),
            'end' => Carbon::parse(self::RAMADAN_END),
        ];
    }

    /**
     * Ramazon qolgan kunlar soni
     */
    public static function remainingDays(?Carbon $date = null): ?int
    {
        $date = $date ?? Carbon::today();
        $end = Carbon::parse(self::RAMADAN_END);

        if (self::isRamadan($date)) {
            return $date->diffInDays($end);
        }

        return null;
    }
}
