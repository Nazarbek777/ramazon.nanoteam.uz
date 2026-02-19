<?php

namespace App\Http\Controllers;

use App\Helpers\HusnaHelper;
use App\Helpers\RamadanHelper;
use App\Models\DailyLog;
use App\Models\DailyLogItem;
use App\Models\Goal;
use App\Models\Habit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class DailyLogController extends Controller
{
    public function show(Request $request, ?string $date = null)
    {
        $user = Auth::user();
        $currentDate = $date ? Carbon::parse($date) : Carbon::today();

        $habits = Habit::forUser($user->id)
            ->whereNotIn('slug', ['bomdod', 'peshin', 'asr', 'shom', 'xufton', 'taroveh'])
            ->orderBy('sort_order')
            ->get();

        $log = DailyLog::with('items')
            ->where('user_id', $user->id)
            ->where('date', $currentDate)
            ->first();

        $completedMap = [];
        $valuesMap = [];
        if ($log) {
            foreach ($log->items as $item) {
                $completedMap[$item->habit_id] = $item->is_completed;
                $valuesMap[$item->habit_id] = $item->value;
            }
        }

        $prevDate = $currentDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $currentDate->copy()->addDay()->format('Y-m-d');
        $isToday = $currentDate->isToday();
        $isFuture = $currentDate->isFuture();

        $ramadan = [
            'is_ramadan' => RamadanHelper::isRamadan($currentDate),
            'day' => RamadanHelper::dayNumber($currentDate),
            'remaining' => RamadanHelper::remainingDays($currentDate),
        ];

        // 99 ism, 30 kun -> kuniga ~3 tadan (oxirgi kunlari 4 ta)
        $husnaNames = [];
        if ($ramadan['is_ramadan']) {
            $day = $ramadan['day'];
            $allNames = HusnaHelper::getNames();
            $perDay = 3;
            $startIndex = ($day - 1) * $perDay;
            
            // Oxirgi kunlarda qolganlarini ham chiqarish uchun
            if ($day == 30) {
                $husnaNames = array_slice($allNames, $startIndex);
            } else {
                $husnaNames = array_slice($allNames, $startIndex, $perDay);
            }
        } else {
            // Ramazon bo'lmasa, tasodifiy 3 tasi
            $allNames = HusnaHelper::getNames();
            $husnaNames = array_slice($allNames, 0, 3);
        }

        return view('daily.show', compact(
            'habits', 'log', 'completedMap', 'valuesMap',
            'currentDate', 'prevDate', 'nextDate', 'isToday', 'isFuture', 'ramadan', 'husnaNames'
        ));
    }

    /**
     * AJAX — bitta habitni belgilash/bekor qilish (reload yo'q)
     */
    public function toggle(Request $request)
    {
        $user = Auth::user();
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $type = $request->input('type', 'habit'); // 'habit', 'data_field', 'priority'

        $log = DailyLog::firstOrCreate(
            ['user_id' => $user->id, 'date' => $date],
            ['notes' => '', 'data' => []]
        );

        if ($type === 'data_field' || $type === 'priority') {
            $key = $request->input('key');
            $value = $request->input('value');
            
            $data = $log->data ?? [];
            if ($type === 'priority') {
                $index = $request->input('index');
                $priorities = $data['priorities'] ?? ['', '', ''];
                $priorities[$index] = $value;
                $data['priorities'] = $priorities;
            } else {
                data_set($data, $key, $value);
            }
            
            $log->update(['data' => $data]);
            
            $log->refresh();
            $data = $log->data ?? [];
            $namozData = $data['namoz'] ?? [];
            $extraCompleted = 0;
            foreach (['fajr', 'dhuhr', 'asr', 'maghrib', 'isha', 'roza'] as $nKey) {
                if ($namozData[$nKey] ?? false) $extraCompleted++;
            }

            $allHabits = Habit::forUser($user->id)->count() + 6;
            $completedCount = $log->items->where('is_completed', true)->count() + $extraCompleted;
            $percent = $allHabits > 0 ? round(($completedCount / $allHabits) * 100) : 0;

            return response()->json([
                'success' => true,
                'completed' => $completedCount,
                'total' => $allHabits,
                'percent' => $percent,
            ]);
        }

        // Default 'habit' toggle
        $habitId = $request->input('habit_id');
        $isCompleted = $request->boolean('is_completed');
        $value = $request->input('value');

        $habit = Habit::findOrFail($habitId);

        if ($habit->type === 'number') {
            $isCompleted = ($value > 0);
        }

        DailyLogItem::updateOrCreate(
            ['daily_log_id' => $log->id, 'habit_id' => $habitId],
            ['is_completed' => $isCompleted, 'value' => $value]
        );

        $log->refresh();
        $data = $log->data ?? [];
        $namozData = $data['namoz'] ?? [];
        $extraCompleted = 0;
        foreach (['fajr', 'dhuhr', 'asr', 'maghrib', 'isha', 'roza'] as $nKey) {
            if ($namozData[$nKey] ?? false) $extraCompleted++;
        }

        $allHabits = Habit::forUser($user->id)->count() + 6;
        $completedCount = $log->items->where('is_completed', true)->count() + $extraCompleted;
        $percent = $allHabits > 0 ? round(($completedCount / $allHabits) * 100) : 0;

        $streak = $this->calculateStreak($user->id);
        Cache::forget("stats_user_{$user->id}");
        $this->updateGoals($user->id);

        return response()->json([
            'success' => true,
            'completed' => $completedCount,
            'total' => $allHabits,
            'percent' => $percent,
            'streak' => $streak,
        ]);
    }

    /**
     * Oddiy form POST (fallback)
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));

        $log = DailyLog::firstOrCreate(
            ['user_id' => $user->id, 'date' => $date],
            ['notes' => $request->input('notes', '')]
        );

        if ($request->has('notes')) {
            $log->update(['notes' => $request->input('notes')]);
        }

        $habits = Habit::forUser($user->id)->get();

        foreach ($habits as $habit) {
            $isCompleted = $request->has("habit_{$habit->id}");
            $value = $request->input("value_{$habit->id}");

            if ($habit->type === 'number') {
                $isCompleted = $value > 0;
            }

            DailyLogItem::updateOrCreate(
                ['daily_log_id' => $log->id, 'habit_id' => $habit->id],
                ['is_completed' => $isCompleted, 'value' => $value]
            );
        }

        $this->updateGoals($user->id);
        Cache::forget("stats_user_{$user->id}");

        return redirect()->route('daily.show', ['date' => $date])
            ->with('success', 'Saqlandi!');
    }

    public function addCustomHabit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:checkbox,number',
        ]);

        $user = Auth::user();
        $maxSort = Habit::forUser($user->id)->max('sort_order') ?? 0;

        Habit::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'type' => $request->type,
            'icon' => 'ri-star-line',
            'is_default' => false,
            'sort_order' => $maxSort + 1,
        ]);

        Cache::forget("habits_user_{$user->id}");

        return redirect()->back()->with('success', 'Yangi amal qo\'shildi!');
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

    private function updateGoals(int $userId): void
    {
        $goals = Goal::where('user_id', $userId)->get();

        foreach ($goals as $goal) {
            if ($goal->habit_id) {
                $completed = DailyLogItem::whereHas('dailyLog', fn($q) => $q->where('user_id', $userId))
                    ->where('habit_id', $goal->habit_id)
                    ->where('is_completed', true)
                    ->count();
                $goal->update(['current_value' => $completed]);
            } else {
                $totalDays = DailyLog::where('user_id', $userId)
                    ->whereHas('items', fn($q) => $q->where('is_completed', true))
                    ->count();
                $goal->update(['current_value' => $totalDays]);
            }
        }
    }
}
