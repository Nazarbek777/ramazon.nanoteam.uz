<?php

namespace App\Modules\Bookstore\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Modules\Bookstore\Models\Sale;
use App\Modules\Bookstore\Models\Book;
use App\Modules\Bookstore\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index()
    {
        // TOP 20 best-selling books (all time)
        $topBooks = SaleItem::select('book_id',
                DB::raw('SUM(quantity) as total_sold'),
                DB::raw('SUM(total_price) as total_revenue')
            )
            ->with('book:id,title,author,barcode,price,stock')
            ->groupBy('book_id')
            ->orderByDesc('total_sold')
            ->take(20)
            ->get()
            ->map(fn($i) => [
                'id'            => $i->book_id,
                'title'         => $i->book ? $i->book->title  : '?',
                'author'        => $i->book ? $i->book->author : '',
                'barcode'       => $i->book ? $i->book->barcode: '',
                'price'         => $i->book ? (float) $i->book->price : 0,
                'stock'         => $i->book ? $i->book->stock  : 0,
                'total_sold'    => (int) $i->total_sold,
                'total_revenue' => (float) $i->total_revenue,
            ]);

        // Low stock books (stock <= 10)
        $lowStock = Book::where('stock', '<=', 10)
            ->orderBy('stock')
            ->get(['id', 'title', 'author', 'barcode', 'price', 'stock'])
            ->map(fn($b) => [
                ...$b->toArray(),
                'price' => (float) $b->price,
            ]);

        // Monthly revenue for current year (bar chart)
        $monthlyRevenue = Sale::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $months = ['Yan','Fev','Mar','Apr','May','Iyn','Iyl','Avg','Sen','Okt','Noy','Dek'];
        $monthlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyData[] = [
                'month' => $months[$m - 1],
                'total' => $monthlyRevenue->has($m) ? (float) $monthlyRevenue[$m]->total : 0,
                'count' => $monthlyRevenue->has($m) ? (int) $monthlyRevenue[$m]->count  : 0,
            ];
        }

        // Books never sold
        $neverSold = Book::whereNotIn('id', SaleItem::select('book_id')->distinct())
            ->count();

        return Inertia::render('Bookstore/Analytics', [
            'topBooks'      => $topBooks,
            'lowStock'      => $lowStock,
            'monthlyData'   => $monthlyData,
            'neverSold'     => $neverSold,
            'totalBooks'    => Book::count(),
        ]);
    }
}
