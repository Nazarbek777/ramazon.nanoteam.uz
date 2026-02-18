<?php

namespace App\Http\Controllers;

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

        $habits = Habit::forUser($user->id)->orderBy('sort_order')->get();

        $log = DailyLog::with('items')
            ->where('user_id', $user->id)
            ->where('date', $currentDate)
            ->first();

        // Prepare items map for view
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

        return view('daily.show', compact(
            'habits', 'log', 'completedMap', 'valuesMap',
            'currentDate', 'prevDate', 'nextDate', 'isToday', 'isFuture'
        ));
    }

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

        // Maqsadlarni yangilash
        $this->updateGoals($user->id);

        // Cache tozalash
        Cache::forget("stats_user_{$user->id}");

        return redirect()->route('daily.show', ['date' => $date])
            ->with('success', 'Bugungi amallar saqlandi! ✅');
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
            'icon' => '⭐',
            'is_default' => false,
            'sort_order' => $maxSort + 1,
        ]);

        // Cache tozalash
        Cache::forget("habits_user_{$user->id}");

        return redirect()->back()->with('success', 'Yangi amal qo\'shildi! ⭐');
    }

    private function updateGoals(int $userId): void
    {
        $goals = Goal::where('user_id', $userId)->get();

        foreach ($goals as $goal) {
            if ($goal->habit_id) {
                // Habit-ga bog'liq maqsad
                $completed = DailyLogItem::whereHas('dailyLog', fn($q) => $q->where('user_id', $userId))
                    ->where('habit_id', $goal->habit_id)
                    ->where('is_completed', true)
                    ->count();
                $goal->update(['current_value' => $completed]);
            } else {
                // Streak turdagi maqsad — kunlar soni
                $totalDays = DailyLog::where('user_id', $userId)
                    ->whereHas('items', fn($q) => $q->where('is_completed', true))
                    ->count();
                $goal->update(['current_value' => $totalDays]);
            }
        }
    }
}
