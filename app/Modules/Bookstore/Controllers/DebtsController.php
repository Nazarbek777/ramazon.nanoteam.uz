<?php

namespace App\Modules\Bookstore\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Bookstore\Models\Sale;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DebtsController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('from') ? Carbon::parse($request->input('from'))->startOfDay() : Carbon::now()->subMonths(3)->startOfDay();
        $to   = $request->input('to')   ? Carbon::parse($request->input('to'))->endOfDay()     : Carbon::now()->endOfDay();

        $debts = Sale::with(['user', 'items.book'])
            ->where('status', 'pending')
            ->whereBetween('created_at', [$from, $to])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Bookstore/Debts', [
            'debts'   => $debts,
            'filters' => [
                'from' => $from->format('Y-m-d'),
                'to'   => $to->format('Y-m-d'),
            ],
            'total_pending_amount' => Sale::where('status', 'pending')->sum('total_amount'),
        ]);
    }

    public function markAsPaid(Sale $sale)
    {
        if ($sale->status === 'pending') {
            $sale->update(['status' => 'paid']);
            return redirect()->back()->with('success', 'To\'landi deb belgilandi!');
        }
        return redirect()->back()->with('error', 'Bu buyurtma allaqachon to\'langan.');
    }

    public function destroy(Sale $sale)
    {
        // Permission check can be added here
        $sale->items()->delete();
        $sale->delete();
        return redirect()->back()->with('success', 'Buyurtma o\'chirildi');
    }
}
