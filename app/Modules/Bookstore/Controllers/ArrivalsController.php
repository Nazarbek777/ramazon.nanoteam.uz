<?php

namespace App\Modules\Bookstore\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Modules\Bookstore\Models\Arrival;
use App\Modules\Bookstore\Models\Book;
use App\Modules\Bookstore\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ArrivalsController extends Controller
{
    public function index(Request $request)
    {
        $from    = $request->input('from') ? Carbon::parse($request->input('from'))->startOfDay() : Carbon::now()->startOfMonth();
        $to      = $request->input('to')   ? Carbon::parse($request->input('to'))->endOfDay()     : Carbon::now()->endOfDay();

        $arrivals = Arrival::with('book:id,title,author,barcode')
            ->whereBetween('arrived_at', [$from->toDateString(), $to->toDateString()])
            ->latest('arrived_at')
            ->paginate(25)
            ->withQueryString()
            ->through(fn($a) => [
                'id'         => $a->id,
                'book'       => $a->book ? ['title' => $a->book->title, 'author' => $a->book->author, 'barcode' => $a->book->barcode] : null,
                'quantity'   => $a->quantity,
                'cost_price' => $a->cost_price,
                'total_cost' => $a->total_cost,
                'supplier'   => $a->supplier,
                'note'       => $a->note,
                'arrived_at' => Carbon::parse($a->arrived_at)->format('d.m.Y'),
            ]);

        // Revenue for same period
        $periodRevenue = (float) Sale::whereBetween('created_at', [$from, $to])->sum('total_amount');
        $periodCost    = (float) Arrival::whereBetween('arrived_at', [$from->toDateString(), $to->toDateString()])->sum('total_cost');

        // Monthly P&L for current year
        $months = ['Yan','Fev','Mar','Apr','May','Iyn','Iyl','Avg','Sen','Okt','Noy','Dek'];
        $monthlyCosts = Arrival::select(
                DB::raw('MONTH(arrived_at) as month'),
                DB::raw('SUM(total_cost) as total')
            )
            ->whereYear('arrived_at', Carbon::now()->year)
            ->groupBy('month')->get()->keyBy('month');

        $monthlyRevenues = Sale::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_amount) as total')
            )
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy('month')->get()->keyBy('month');

        $plData = [];
        for ($m = 1; $m <= 12; $m++) {
            $rev  = $monthlyRevenues->has($m) ? (float) $monthlyRevenues[$m]->total : 0;
            $cost = $monthlyCosts->has($m)    ? (float) $monthlyCosts[$m]->total    : 0;
            $plData[] = [
                'month'   => $months[$m - 1],
                'revenue' => $rev,
                'cost'    => $cost,
                'profit'  => $rev - $cost,
            ];
        }

        return Inertia::render('Bookstore/Arrivals', [
            'arrivals'      => $arrivals,
            'books'         => Book::select('id', 'title', 'barcode', 'cost_price')->orderBy('title')->get(),
            'periodRevenue' => $periodRevenue,
            'periodCost'    => $periodCost,
            'plData'        => $plData,
            'filters'       => [
                'from' => $request->input('from', $from->format('Y-m-d')),
                'to'   => $request->input('to',   $to->format('Y-m-d')),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'book_id'    => 'nullable|exists:bookstore_books,id',
            'quantity'   => 'required|integer|min:1',
            'cost_price' => 'required|numeric|min:0',
            'supplier'   => 'nullable|string|max:200',
            'note'       => 'nullable|string|max:500',
            'arrived_at' => 'required|date',
        ]);

        $data['total_cost'] = $data['quantity'] * $data['cost_price'];

        $arrival = Arrival::create($data);

        // Update book stock and cost_price
        if ($data['book_id']) {
            $book = Book::find($data['book_id']);
            $book->increment('stock', $data['quantity']);
            $book->update(['cost_price' => $data['cost_price']]);
        }

        return redirect()->route('bookstore.arrivals')->with('success', 'Keldi qayd etildi!');
    }

    public function destroy(Arrival $arrival)
    {
        // Reverse the stock increment ONLY if book_id is present
        if ($arrival->book_id) {
            $arrival->book->decrement('stock', $arrival->quantity);
        }
        $arrival->delete();
        return redirect()->route('bookstore.arrivals')->with('success', 'O\'chirildi');
    }

    public function export(Request $request)
    {
        $from = $request->input('from') ? Carbon::parse($request->input('from'))->startOfDay() : Carbon::now()->startOfMonth();
        $to   = $request->input('to')   ? Carbon::parse($request->input('to'))->endOfDay()     : Carbon::now()->endOfDay();

        $arrivals = Arrival::with('book')
            ->whereBetween('arrived_at', [$from->toDateString(), $to->toDateString()])
            ->latest('arrived_at')->get();

        $csv = "Sana,Kitob,Barcode,Miqdor,Narxi,Jami,Yetkazuvchi,Izoh\n";
        foreach ($arrivals as $a) {
            $title    = $a->book ? $a->book->title   : '?';
            $barcode  = $a->book ? $a->book->barcode : '';
            $supplier = $a->supplier ?? '';
            $note     = $a->note ?? '';
            $date     = Carbon::parse($a->arrived_at)->format('d.m.Y');
            $csv .= "{$date},\"{$title}\",{$barcode},{$a->quantity},{$a->cost_price},{$a->total_cost},\"{$supplier}\",\"{$note}\"\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="keldi-'.$from->format('Y-m-d').'-'.$to->format('Y-m-d').'.csv"',
        ]);
    }
}
