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
        $book = Book::where('barcode', $barcode)->firstOrFail();
        return response()->json([
            'id' => $book->id,
            'title' => $book->title,
            'author' => $book->author,
            'barcode' => $book->barcode,
            'price' => (float) $book->price,
            'stock' => $book->stock,
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

            return redirect()->back()->with('success', 'Sotuv muvaffaqiyatli amalga oshirildi!');
        });
    }
}
