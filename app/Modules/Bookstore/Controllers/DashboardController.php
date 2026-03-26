<?php

namespace App\Modules\Bookstore\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Modules\Bookstore\Models\Sale;
use App\Modules\Bookstore\Models\Book;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $todaySales = Sale::whereDate('created_at', Carbon::today())->sum('total_amount');
        $todayCount = Sale::whereDate('created_at', Carbon::today())->count();
        $totalBooks = Book::count();
        $recentSales = Sale::with('user')->latest()->take(10)->get();

        return Inertia::render('Bookstore/Dashboard', [
            'todaySales' => (float) $todaySales,
            'todayCount' => $todayCount,
            'totalBooks' => $totalBooks,
            'recentSales' => $recentSales,
        ]);
    }
}
