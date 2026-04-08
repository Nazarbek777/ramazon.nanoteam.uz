<?php

namespace App\Modules\Bookstore\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Modules\Bookstore\Models\Book;
use App\Modules\Bookstore\Models\Sale;
use App\Modules\Bookstore\Models\Arrival;
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
            'is_delivery' => 'nullable|boolean',
            'status' => 'nullable|string|in:paid,pending',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'delivery_fee' => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated) {
            $totalAmount = 0;
            $items = [];

            foreach ($validated['items'] as $itemData) {
                $book = Book::lockForUpdate()->find($itemData['id']);
                
                if ($book->stock < $itemData['quantity']) {
                    throw new \Exception("Kitob zaxirasi yetarli emas: {$book->title}");
                }

                $remainingToDeduct = $itemData['quantity'];
                
                // FIFO: Deduct from oldest arrivals first
                $arrivals = Arrival::where('book_id', $book->id)
                    ->where('remaining_stock', '>', 0)
                    ->orderBy('arrived_at', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

                foreach ($arrivals as $arrival) {
                    if ($remainingToDeduct <= 0) break;

                    $deduct = min($remainingToDeduct, $arrival->remaining_stock);
                    $arrival->decrement('remaining_stock', $deduct);
                    $remainingToDeduct -= $deduct;

                    $items[] = [
                        'book_id'    => $book->id,
                        'quantity'   => $deduct,
                        'unit_price' => $book->price,
                        'cost_price' => $arrival->cost_price,
                        'total_price' => $book->price * $deduct,
                    ];
                }

                // Fallback for missing arrival records
                if ($remainingToDeduct > 0) {
                    $items[] = [
                        'book_id'    => $book->id,
                        'quantity'   => $remainingToDeduct,
                        'unit_price' => $book->price,
                        'cost_price' => $book->cost_price,
                        'total_price' => $book->price * $remainingToDeduct,
                    ];
                }

                $book->decrement('stock', $itemData['quantity']);
                $totalAmount += $book->price * $itemData['quantity'];
            }

            $sale = Sale::create([
                'bookstore_user_id' => Auth::guard('bookstore')->id(),
                'total_amount' => ($totalAmount + ($validated['delivery_fee'] ?? 0)) - ($validated['discount'] ?? 0),
                'discount' => $validated['discount'] ?? 0,
                'payment_method' => $validated['payment_method'],
                'is_delivery' => $validated['is_delivery'] ?? false,
                'status' => $validated['status'] ?? 'paid',
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'delivery_fee' => $validated['delivery_fee'] ?? 0,
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
            'is_delivery' => 'nullable|boolean',
            'status' => 'nullable|string|in:paid,pending',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'delivery_fee' => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated) {
            $totalAmount = 0;
            $items = [];

            foreach ($validated['items'] as $itemData) {
                $book = Book::lockForUpdate()->find($itemData['id']);
                if ($book->stock < $itemData['quantity']) {
                    return response()->json(['error' => "Zaxira yetarli emas: {$book->title}"], 422);
                }

                $remainingToDeduct = $itemData['quantity'];
                $arrivals = Arrival::where('book_id', $book->id)
                    ->where('remaining_stock', '>', 0)
                    ->orderBy('arrived_at', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

                foreach ($arrivals as $arrival) {
                    if ($remainingToDeduct <= 0) break;
                    $deduct = min($remainingToDeduct, $arrival->remaining_stock);
                    $arrival->decrement('remaining_stock', $deduct);
                    $remainingToDeduct -= $deduct;

                    $items[] = [
                        'book_id'    => $book->id,
                        'quantity'   => $deduct,
                        'unit_price' => $book->price,
                        'cost_price' => $arrival->cost_price,
                        'total_price' => $book->price * $deduct,
                    ];
                }

                if ($remainingToDeduct > 0) {
                    $items[] = [
                        'book_id'    => $book->id,
                        'quantity'   => $remainingToDeduct,
                        'unit_price' => $book->price,
                        'cost_price' => $book->cost_price,
                        'total_price' => $book->price * $remainingToDeduct,
                    ];
                }

                $book->decrement('stock', $itemData['quantity']);
                $totalAmount += $book->price * $itemData['quantity'];
            }

            $sale = Sale::create([
                'bookstore_user_id' => Auth::guard('bookstore')->id(),
                'total_amount' => ($totalAmount + ($validated['delivery_fee'] ?? 0)) - ($validated['discount'] ?? 0),
                'discount' => $validated['discount'] ?? 0,
                'payment_method' => $validated['payment_method'],
                'is_delivery' => $validated['is_delivery'] ?? false,
                'status' => $validated['status'] ?? 'paid',
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'delivery_fee' => $validated['delivery_fee'] ?? 0,
            ]);
            $sale->items()->createMany($items);

            return response()->json(['success' => true, 'sale_id' => $sale->id]);
        });
    }

    public function searchBooks(Request $request)
    {
        $query = $request->get('q');
        if (strlen($query) < 2) return response()->json([]);

        $books = Book::where('title', 'like', "%{$query}%")
            ->orWhere('author', 'like', "%{$query}%")
            ->orWhere('barcode', 'like', "%{$query}%")
            ->select('id', 'title', 'author', 'barcode', 'price', 'stock')
            ->limit(15)
            ->get()
            ->map(fn($b) => [
                'id' => $b->id,
                'title' => $b->title,
                'author' => $b->author,
                'barcode' => $b->barcode,
                'price' => (float) $b->price,
                'stock' => $b->stock,
            ]);

        return response()->json($books);
    }
}
