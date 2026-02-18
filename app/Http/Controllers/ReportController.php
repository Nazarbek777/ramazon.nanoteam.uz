<?php

namespace App\Http\Controllers;

use App\Models\DailyLog;
use App\Models\DailyLogItem;
use App\Models\Habit;
use App\Helpers\RamadanHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $period = $request->get('period', 'weekly');

        if ($period === 'weekly') {
            $startDate = Carbon::today()->subDays(6);
            $endDate = Carbon::today();
        } else {
            $startDate = Carbon::today()->subDays(29);
            $endDate = Carbon::today();
        }

        // Kunlik loglar
        $logs = DailyLog::with('items.habit')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        // Eng ko'p bajarilgan amallar
        $topHabits = DailyLogItem::whereHas('dailyLog', fn($q) => $q->where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate]))
            ->where('is_completed', true)
            ->selectRaw('habit_id, COUNT(*) as count')
            ->groupBy('habit_id')
            ->orderByDesc('count')
            ->with('habit')
            ->limit(10)
            ->get();

        // O'tkazib yuborilgan kunlar
        $allDates = [];
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $allDates[] = $current->format('Y-m-d');
            $current->addDay();
        }
        $loggedDates = $logs->pluck('date')->map(fn($d) => $d->format('Y-m-d'))->toArray();
        
        $userCreatedAt = $user->created_at->startOfDay();
        $missedDates = collect($allDates)
            ->filter(fn($date) => Carbon::parse($date)->gte($userCreatedAt))
            ->diff($loggedDates)
            ->toArray();

        // Kunlik bajarilish diagrammasi
        $dailyChart = [];
        foreach ($allDates as $dateStr) {
            $log = $logs->firstWhere('date', Carbon::parse($dateStr));
            $completed = 0;
            $total = 0;
            if ($log) {
                $completed = $log->items->where('is_completed', true)->count();
                $total = $log->items->count();
            }
            $dailyChart[] = [
                'date' => Carbon::parse($dateStr)->format('d.m'),
                'completed' => $completed,
                'total' => $total,
                'percent' => $total > 0 ? round(($completed / $total) * 100) : 0,
            ];
        }

        // Umumiy statistika
        $totalCompleted = $logs->sum(fn($l) => $l->items->where('is_completed', true)->count());
        $totalItems = $logs->sum(fn($l) => $l->items->count());
        $overallPercent = $totalItems > 0 ? round(($totalCompleted / $totalItems) * 100, 1) : 0;

        // Streak (Kunlik ketma-ketlik)
        $streak = $this->calculateStreak($user->id);

        // Category Split
        $ibodatSlugs = ['bomdod', 'peshin', 'asr', 'shom', 'xufton', 'qazo', 'taroveh', 'sura', 'oyat', 'sahifa', 'yodladim', 'qiroat', 'takrorladim'];
        
        $ibodatCompleted = 0;
        $ibodatTotal = 0;
        $vazifaCompleted = 0;
        $vazifaTotal = 0;

        foreach ($logs as $log) {
            foreach ($log->items as $item) {
                if (in_array($item->habit->slug, $ibodatSlugs)) {
                    $ibodatTotal++;
                    if ($item->is_completed) $ibodatCompleted++;
                } else {
                    $vazifaTotal++;
                    if ($item->is_completed) $vazifaCompleted++;
                }
            }
        }

        $ibodatPercent = $ibodatTotal > 0 ? round(($ibodatCompleted / $ibodatTotal) * 100) : 0;
        $vazifaPercent = $vazifaTotal > 0 ? round(($vazifaCompleted / $vazifaTotal) * 100) : 0;

        // Best Day
        $bestDay = collect($dailyChart)->sortByDesc('percent')->first();

        // Habit Frequency (necha marta bajarilganligi %)
        foreach ($topHabits as $th) {
            $th->frequency = round(($th->count / count($allDates)) * 100);
        }

        return view('reports.index', compact(
            'period', 'logs', 'topHabits', 'missedDates',
            'dailyChart', 'totalCompleted', 'totalItems', 'overallPercent',
            'startDate', 'endDate', 'streak', 'ibodatPercent', 'vazifaPercent', 'bestDay'
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

            // Streak counts if ANY completion exists for the day
            if (!$log || $log->completed_count === 0) {
                break;
            }

            $streak++;
            $date = $date->subDay();
        }

        return $streak;
    }
}
