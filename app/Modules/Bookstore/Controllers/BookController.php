<?php

namespace App\Modules\Bookstore\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Modules\Bookstore\Models\Book;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::orderByDesc('created_at')->get();
        return Inertia::render('Bookstore/Books', ['books' => $books]);
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

        Book::create($data);

        return redirect('/bookstore/books')->with('success', 'Kitob qo\'shildi!');
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

        $book->update($data);

        return redirect('/bookstore/books')->with('success', 'Kitob yangilandi!');
    }

    public function destroy(Book $book)
    {
        $book->delete();
        return redirect('/bookstore/books')->with('success', 'Kitob o\'chirildi!');
    }
}
