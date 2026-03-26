<?php

namespace App\Modules\Bookstore\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Modules\Bookstore\Models\Sale;
use App\Modules\Bookstore\Models\Book;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();
        $yearStart = Carbon::now()->startOfYear();

        // Core stats
        $todaySales  = (float) Sale::whereDate('created_at', $today)->sum('total_amount');
        $todayCount  = Sale::whereDate('created_at', $today)->count();
        $weekSales   = (float) Sale::where('created_at', '>=', $weekStart)->sum('total_amount');
        $monthSales  = (float) Sale::where('created_at', '>=', $monthStart)->sum('total_amount');
        $yearSales   = (float) Sale::where('created_at', '>=', $yearStart)->sum('total_amount');
        $totalBooks  = Book::count();
        $lowStock    = Book::where('stock', '<=', 5)->where('stock', '>', 0)->count();
        $outOfStock  = Book::where('stock', 0)->count();

        // Last 30 days daily revenue (for line chart)
        $dailyRevenue = Sale::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(29))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Fill in missing days with 0
        $chartDays   = [];
        $chartTotals = [];
        $chartCounts = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = Carbon::now()->subDays($i)->format('Y-m-d');
            $chartDays[]   = Carbon::now()->subDays($i)->format('d/m');
            $chartTotals[] = $dailyRevenue->has($d) ? (float) $dailyRevenue[$d]->total : 0;
            $chartCounts[] = $dailyRevenue->has($d) ? (int) $dailyRevenue[$d]->count : 0;
        }

        // Payment method breakdown
        $paymentStats = Sale::select('payment_method', DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        // Recent sales
        $recentSales = Sale::with(['user', 'items.book'])->latest()->take(15)->get()
            ->map(fn($s) => [
                'id'             => $s->id,
                'total_amount'   => (float) $s->total_amount,
                'discount'       => (float) $s->discount,
                'payment_method' => $s->payment_method,
                'created_at'     => $s->created_at,
                'user'           => $s->user ? ['name' => $s->user->name] : null,
                'items_count'    => $s->items->count(),
                'items'          => $s->items->map(fn($i) => [
                    'title'    => $i->book ? $i->book->title : '?',
                    'quantity' => $i->quantity,
                    'price'    => (float) $i->unit_price,
                ]),
            ]);

        return Inertia::render('Bookstore/Dashboard', [
            'todaySales'   => $todaySales,
            'todayCount'   => $todayCount,
            'weekSales'    => $weekSales,
            'monthSales'   => $monthSales,
            'yearSales'    => $yearSales,
            'totalBooks'   => $totalBooks,
            'lowStock'     => $lowStock,
            'outOfStock'   => $outOfStock,
            'chartDays'    => $chartDays,
            'chartTotals'  => $chartTotals,
            'chartCounts'  => $chartCounts,
            'paymentStats' => $paymentStats,
            'recentSales'  => $recentSales,
        ]);
    }
}
