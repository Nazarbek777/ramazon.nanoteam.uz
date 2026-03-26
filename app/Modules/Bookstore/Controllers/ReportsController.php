<?php

namespace App\Modules\Bookstore\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Modules\Bookstore\Models\Sale;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Modules\Bookstore\Models\Arrival;
use App\Modules\Bookstore\Models\Book;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $from    = $request->input('from') ? Carbon::parse($request->input('from'))->startOfDay() : Carbon::now()->startOfMonth();
        $to      = $request->input('to')   ? Carbon::parse($request->input('to'))->endOfDay()     : Carbon::now()->endOfDay();
        $payment = $request->input('payment');

        $query = Sale::with(['user', 'items.book'])
            ->whereBetween('created_at', [$from, $to]);

        if ($payment) {
            $query->where('payment_method', $payment);
        }

        $sales = $query->latest()->paginate(25)->withQueryString();

        // Summary for date range
        $summaryQuery = Sale::whereBetween('created_at', [$from, $to]);
        if ($payment) $summaryQuery->where('payment_method', $payment);

        $summary = [
            'total_revenue' => (float) (clone $summaryQuery)->sum('total_amount'),
            'total_count'   => (clone $summaryQuery)->count(),
            'avg_sale'      => (float) (clone $summaryQuery)->avg('total_amount'),
            'total_discount'=> (float) (clone $summaryQuery)->sum('discount'),
        ];

        // Payment breakdown for this period
        $payBreakdown = Sale::whereBetween('created_at', [$from, $to])
            ->select('payment_method', DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        // Stock Valuation (Real-time)
        $stockStats = Arrival::where('remaining_stock', '>', 0)
            ->join('bookstore_books', 'bookstore_arrivals.book_id', '=', 'bookstore_books.id')
            ->select(
                DB::raw('SUM(remaining_stock) as total_quantity'),
                DB::raw('SUM(remaining_stock * bookstore_arrivals.cost_price) as total_cost_value'),
                DB::raw('SUM(remaining_stock * bookstore_books.price) as total_sale_value')
            )
            ->first();

        return Inertia::render('Bookstore/Reports', [
            'stockStats'   => [
                'total_quantity'   => (int) $stockStats->total_quantity,
                'total_cost_value' => (float) $stockStats->total_cost_value,
                'total_sale_value' => (float) $stockStats->total_sale_value,
                'potential_profit' => (float) ($stockStats->total_sale_value - $stockStats->total_cost_value),
            ],
            'sales'        => $sales->through(fn($s) => [
                'id'             => $s->id,
                'total_amount'   => (float) $s->total_amount,
                'discount'       => (float) $s->discount,
                'payment_method' => $s->payment_method,
                'created_at'     => $s->created_at->format('d.m.Y H:i'),
                'user'           => $s->user ? ['name' => $s->user->name] : null,
                'items'          => $s->items->map(fn($i) => [
                    'title'    => $i->book ? $i->book->title : '?',
                    'quantity' => $i->quantity,
                    'price'    => (float) $i->unit_price,
                    'total'    => (float) $i->total_price,
                ]),
            ]),
            'summary'      => $summary,
            'payBreakdown' => $payBreakdown,
            'filters'      => [
                'from'    => $request->input('from', $from->format('Y-m-d')),
                'to'      => $request->input('to', $to->format('Y-m-d')),
                'payment' => $payment,
            ],
        ]);
    }

    public function export(Request $request)
    {
        $from    = $request->input('from') ? Carbon::parse($request->input('from'))->startOfDay() : Carbon::now()->startOfMonth();
        $to      = $request->input('to')   ? Carbon::parse($request->input('to'))->endOfDay()     : Carbon::now()->endOfDay();
        $payment = $request->input('payment');

        $query = Sale::with(['user', 'items.book'])->whereBetween('created_at', [$from, $to]);
        if ($payment) $query->where('payment_method', $payment);
        $sales = $query->latest()->get();

        $csv = "ID,Sana,Xodim,To'lov,Chegirma,Jami\n";
        foreach ($sales as $s) {
            $csv .= "{$s->id},{$s->created_at->format('d.m.Y H:i')},".
                    ($s->user ? $s->user->name : '?').",".
                    "{$s->payment_method},{$s->discount},{$s->total_amount}\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="hisobot-'.$from->format('Y-m-d').'-'.$to->format('Y-m-d').'.csv"',
        ]);
    }
}
