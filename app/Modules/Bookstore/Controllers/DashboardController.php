<?php

namespace App\Modules\Bookstore\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Modules\Bookstore\Models\Sale;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $todaySales = Sale::whereDate('created_at', Carbon::today())->sum('total_amount');
        $recentSales = Sale::with('user')->latest()->take(5)->get();

        return Inertia::render('Bookstore/Dashboard', [
            'todaySales' => $todaySales,
            'recentSales' => $recentSales,
        ]);
    }
}
