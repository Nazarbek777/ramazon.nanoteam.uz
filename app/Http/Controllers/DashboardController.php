<?php

namespace App\Http\Controllers;

use App\Helpers\RamadanHelper;
use App\Models\DailyLog;
use App\Models\Goal;
use App\Models\Habit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // Ramazon ma'lumotlari
        $ramadan = [
            'is_ramadan' => RamadanHelper::isRamadan(),
            'day' => RamadanHelper::dayNumber(),
            'remaining' => RamadanHelper::remainingDays(),
            'days_until' => RamadanHelper::daysUntilRamadan(),
        ];

        // Bugungi log
        $todayLog = DailyLog::with('items.habit')
            ->where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        // Barcha habitlar
        $habits = Cache::remember("habits_user_{$user->id}", 3600, function () use ($user) {
            return Habit::forUser($user->id)->orderBy('sort_order')->get();
        });

        // Streak
        $streak = $this->calculateStreak($user->id);

        // Statistika
        $stats = Cache::remember("stats_user_{$user->id}", 300, function () use ($user) {
            $totalLogs = DailyLog::where('user_id', $user->id)->count();
            $totalCompleted = DailyLog::where('user_id', $user->id)
                ->withCount(['items as completed_count' => function ($q) {
                    $q->where('is_completed', true);
                }])
                ->get()
                ->sum('completed_count');
            $totalItems = DailyLog::where('user_id', $user->id)
                ->withCount('items')
                ->get()
                ->sum('items_count');

            return [
                'total_days' => $totalLogs,
                'total_completed' => $totalCompleted,
                'total_items' => $totalItems,
                'completion_rate' => $totalItems > 0 ? round(($totalCompleted / $totalItems) * 100, 1) : 0,
            ];
        });

        // Maqsadlar
        $goals = Goal::where('user_id', $user->id)->with('habit')->get();

        // Haftalik progress
        $weeklyProgress = $this->getWeeklyProgress($user->id);

        // Namoz ma'lumotlari (Quick Check-in uchun)
        $namozData = ($todayLog && isset($todayLog->data['namoz'])) ? $todayLog->data['namoz'] : [];

        return view('dashboard', compact(
            'todayLog', 'habits', 'streak', 'stats', 'goals', 'weeklyProgress', 'today', 'ramadan', 'namozData'
        ));
    }

    private function calculateStreak(int $userId): int
    {
        $streak = 0;
        $date = Carbon::today();

        while (true) {
            $log = DailyLog::where('user_id', $userId)
                ->where('date', $date)
                ->withCount(['items as completed_count' => function ($q) {
                    $q->where('is_completed', true);
                }])
                ->first();

            if (!$log || $log->completed_count === 0) {
                break;
            }

            $streak++;
            $date = $date->subDay();
        }

        return $streak;
    }

    private function getWeeklyProgress(int $userId): array
    {
        // Weekly progress
        $weeklyProgress = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $log = DailyLog::where('user_id', $userId)->where('date', $date)->first();
            
            $habitsTotal = Habit::forUser($userId)->count() + 6;
            $habitsDone = 0;
            if ($log) {
                $habitsDone = $log->items()->where('is_completed', true)->count();
                $namozData = $log->data['namoz'] ?? [];
                foreach (['fajr', 'dhuhr', 'asr', 'maghrib', 'isha', 'roza'] as $k) {
                    if ($namozData[$k] ?? false) $habitsDone++;
                }
            }

            $weeklyProgress[] = [
                'date' => $date->format('d.m'),
                'day' => $this->getUzbekDay($date->dayOfWeek),
                'ramadan_day' => RamadanHelper::dayNumber($date),
                'completed' => $habitsDone,
                'total' => $habitsTotal,
                'percent' => $habitsTotal > 0 ? round(($habitsDone / $habitsTotal) * 100) : 0,
            ];
        }

        return $weeklyProgress;
    }

    private function getUzbekDay(int $dayOfWeek): string
    {
        $days = [
            0 => 'Yak',
            1 => 'Dush',
            2 => 'Sesh',
            3 => 'Chor',
            4 => 'Pay',
            5 => 'Jum',
            6 => 'Shan',
        ];
        return $days[$dayOfWeek] ?? '';
    }
}
