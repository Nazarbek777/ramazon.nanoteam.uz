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

        $arrivals = Arrival::with(['book' => fn($q) => $q->withTrashed()->select('id', 'title', 'author', 'barcode')])
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
            'is_new_book'=> 'nullable|boolean',
            'title'      => 'nullable|string|max:255|required_if:is_new_book,true',
            'author'     => 'nullable|string|max:255',
            'barcode'    => [
                'nullable', 'string', 'max:255',
                $request->is_new_book ? 'unique:bookstore_books,barcode' : ''
            ],
            'price'      => 'nullable|numeric|min:0|required_if:is_new_book,true',
            'quantity'   => 'required|integer|min:1',
            'cost_price' => 'required|numeric|min:0',
            'supplier'   => 'nullable|string|max:200',
            'note'       => 'nullable|string|max:500',
            'arrived_at' => 'required|date',
        ]);

        return DB::transaction(function () use ($data) {
            $bookId = $data['book_id'];

            if (!empty($data['is_new_book'])) {
                $barcode = $data['barcode'];
                
                if (empty($barcode)) {
                    do {
                        $barcode = 'BK-' . strtoupper(Str::random(9));
                    } while (Book::where('barcode', $barcode)->exists());
                }

                $book = Book::create([
                    'title'      => $data['title'],
                    'author'     => $data['author'],
                    'barcode'    => $barcode,
                    'price'      => $data['price'],
                    'cost_price' => $data['cost_price'],
                    'stock'      => 0,
                ]);
                $bookId = $book->id;
            }

            $arrival = Arrival::create([
                'book_id'    => $bookId,
                'quantity'   => $data['quantity'],
                'remaining_stock' => $data['quantity'],
                'cost_price' => $data['cost_price'],
                'total_cost' => $data['quantity'] * $data['cost_price'],
                'supplier'   => $data['supplier'],
                'note'       => $data['note'],
                'arrived_at' => $data['arrived_at'],
            ]);

            if ($bookId) {
                $book = Book::find($bookId);
                $book->increment('stock', $data['quantity']);
                $book->update(['cost_price' => $data['cost_price']]);
            }

            return redirect()->route('bookstore.arrivals')->with('success', 'Muvaffaqiyatli saqlandi!');
        });
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

        $arrivals = Arrival::with(['book' => fn($q) => $q->withTrashed()])
            ->whereBetween('arrived_at', [$from->toDateString(), $to->toDateString()])
            ->latest('arrived_at')->get();

        $csv = "Sana,Kitob,Barcode,Miqdor,Qolgan,Narxi,Jami,Yetkazuvchi,Izoh\n";
        foreach ($arrivals as $a) {
            $title    = $a->book ? $a->book->title   : '?';
            $barcode  = $a->book ? $a->book->barcode : '';
            $supplier = $a->supplier ?? '';
            $note     = $a->note ?? '';
            $date     = Carbon::parse($a->arrived_at)->format('d.m.Y');
            $csv .= "{$date},\"{$title}\",{$barcode},{$a->quantity},{$a->remaining_stock},{$a->cost_price},{$a->total_cost},\"{$supplier}\",\"{$note}\"\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="keldi-'.$from->format('Y-m-d').'-'.$to->format('Y-m-d').'.csv"',
        ]);
    }
}
