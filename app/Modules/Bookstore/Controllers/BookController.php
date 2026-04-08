<?php

namespace App\Modules\Bookstore\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Modules\Bookstore\Models\Book;
use App\Modules\Bookstore\Models\Arrival;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::orderByDesc('created_at')->get();
        return Inertia::render('Bookstore/Books', [
            'books' => $books,
            'isLocked' => false
        ]);
    }

    public function publicIndex()
    {
        if (!session('bookstore_books_unlocked')) {
            return Inertia::render('Bookstore/Stock', ['isLocked' => true, 'books' => []]);
        }
        
        $books = Book::orderByDesc('created_at')->get();
        return Inertia::render('Bookstore/Stock', [
            'books' => $books,
            'isLocked' => false
        ]);
    }

    public function publicUnlock(Request $request)
    {
        $code = $request->input('code');
        if ($code === '7777') {
            session(['bookstore_books_unlocked' => true]);
            return redirect()->back();
        }
        return redirect()->back()->with('error', 'PIN kod noto\'g\'ri!');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'barcode' => 'required|string|unique:bookstore_books,barcode',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        return DB::transaction(function () use ($data) {
            $book = Book::create($data);

            if ($book->stock > 0) {
                Arrival::create([
                    'book_id'    => $book->id,
                    'quantity'   => $book->stock,
                    'remaining_stock' => $book->stock,
                    'cost_price' => $book->cost_price,
                    'total_cost' => $book->stock * $book->cost_price,
                    'note'       => 'Initial stock on creation',
                    'arrived_at' => Carbon::now()->toDateString(),
                ]);
            }

            return redirect('/bookstore/books')->with('success', 'Kitob qo\'shildi va keldi qayd etildi!');
        });
    }

    public function update(Request $request, Book $book)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'barcode' => 'required|string|unique:bookstore_books,barcode,' . $book->id,
            'price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        return DB::transaction(function () use ($request, $book, $data) {
            $oldStock = $book->stock;
            $book->update($data);

            if ($book->stock > $oldStock) {
                $diff = $book->stock - $oldStock;
                Arrival::create([
                    'book_id'    => $book->id,
                    'quantity'   => $diff,
                    'remaining_stock' => $diff,
                    'cost_price' => $book->cost_price,
                    'total_cost' => $diff * $book->cost_price,
                    'note'       => 'Manual stock adjustment (increase)',
                    'arrived_at' => Carbon::now()->toDateString(),
                ]);
            }

            return redirect('/bookstore/books')->with('success', 'Kitob yangilandi!');
        });
    }

    public function destroy(Request $request, Book $book)
    {
        $code = $request->input('code');
        if ($code !== '7777') {
            return redirect()->back()->with('error', 'Xavfsizlik kodi noto\'g\'ri!');
        }

        return DB::transaction(function () use ($book) {
            // 1. Delete all arrivals for this book
            $book->arrivals()->delete();
            
            // 2. Delete all sale items for this book
            $book->saleItems()->delete();
            
            // 3. Delete the book itself
            $book->delete();
            
            return redirect('/bookstore/books')->with('success', 'Kitob va uning barcha tarixi (kirim-chiqim, sotuvlar) butunlay o\'chirildi!');
        });
    }
}
