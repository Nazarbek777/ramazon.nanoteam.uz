<?php

namespace App\Modules\Bookstore\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Modules\Bookstore\Models\Book;
use App\Modules\Bookstore\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class POSController extends Controller
{
    public function index()
    {
        return Inertia::render('Bookstore/POS');
    }

    public function findBook($barcode)
    {
        \Log::debug('[Bookstore] findBook called', [
            'barcode_raw'    => $barcode,
            'barcode_length' => strlen($barcode),
            'request_url'    => request()->fullUrl(),
            'request_method' => request()->method(),
        ]);

        $book = Book::where('barcode', $barcode)->first();

        if (!$book) {
            \Log::warning('[Bookstore] Book NOT found', [
                'barcode'     => $barcode,
                'total_books' => Book::count(),
                'all_barcodes' => Book::pluck('barcode')->toArray(),
            ]);
            return response()->json(['error' => 'Kitob topilmadi', 'barcode' => $barcode], 404);
        }

        \Log::debug('[Bookstore] Book FOUND', [
            'id'     => $book->id,
            'title'  => $book->title,
            'price'  => $book->price,
            'stock'  => $book->stock,
            'barcode' => $book->barcode,
        ]);

        return response()->json([
            'id'     => $book->id,
            'title'  => $book->title,
            'author' => $book->author,
            'barcode' => $book->barcode,
            'price'  => (float) $book->price,
            'stock'  => $book->stock,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:bookstore_books,id',
            'items.*.quantity' => 'required|integer|min:1',
            'discount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $totalAmount = 0;
            $items = [];

            foreach ($validated['items'] as $itemData) {
                $book = Book::lockForUpdate()->find($itemData['id']);
                
                if ($book->stock < $itemData['quantity']) {
                    throw new \Exception("Kitob zaxirasi yetarli emas: {$book->title}");
                }

                $book->decrement('stock', $itemData['quantity']);

                $subtotal = $book->price * $itemData['quantity'];
                $totalAmount += $subtotal;

                $items[] = [
                    'book_id' => $book->id,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $book->price,
                    'total_price' => $subtotal,
                ];
            }

            $sale = Sale::create([
                'bookstore_user_id' => Auth::guard('bookstore')->id(),
                'total_amount' => $totalAmount - ($validated['discount'] ?? 0),
                'discount' => $validated['discount'] ?? 0,
                'payment_method' => $validated['payment_method'],
            ]);

            $sale->items()->createMany($items);

            $saleWithItems = $sale->load('items.book', 'user');

            return redirect('/bookstore/pos')->with('saleReceipt', [
                'id' => $sale->id,
                'total_amount' => $sale->total_amount,
                'discount' => $sale->discount,
                'payment_method' => $sale->payment_method,
                'created_at' => $sale->created_at->format('d.m.Y H:i:s'),
                'user' => $saleWithItems->user ? $saleWithItems->user->name : 'Xodim',
                'items' => $saleWithItems->items->map(fn($i) => [
                    'title' => $i->book->title,
                    'quantity' => $i->quantity,
                    'unit_price' => (float) $i->unit_price,
                    'total_price' => (float) $i->total_price,
                ])->toArray(),
            ]);
        });
    }

    // Returns all books for offline caching
    public function booksCache()
    {
        return response()->json(
            Book::select('id', 'title', 'author', 'barcode', 'price', 'stock')
                ->get()
                ->map(fn($b) => [
                    ...$b->toArray(),
                    'price' => (float) $b->price,
                ])
        );
    }

    // Sync a sale that was saved while offline
    public function offlineSync(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:bookstore_books,id',
            'items.*.quantity' => 'required|integer|min:1',
            'discount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $totalAmount = 0;
            $items = [];

            foreach ($validated['items'] as $itemData) {
                $book = Book::lockForUpdate()->find($itemData['id']);
                if ($book->stock < $itemData['quantity']) {
                    return response()->json(['error' => "Zaxira yetarli emas: {$book->title}"], 422);
                }
                $book->decrement('stock', $itemData['quantity']);
                $subtotal = $book->price * $itemData['quantity'];
                $totalAmount += $subtotal;
                $items[] = [
                    'book_id' => $book->id,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $book->price,
                    'total_price' => $subtotal,
                ];
            }

            $sale = Sale::create([
                'bookstore_user_id' => Auth::guard('bookstore')->id(),
                'total_amount' => $totalAmount - ($validated['discount'] ?? 0),
                'discount' => $validated['discount'] ?? 0,
                'payment_method' => $validated['payment_method'],
            ]);
            $sale->items()->createMany($items);

            return response()->json(['success' => true, 'sale_id' => $sale->id]);
        });
    }
}
