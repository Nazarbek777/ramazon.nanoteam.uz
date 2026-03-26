<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Bookstore\Models\BookstoreUser;
use App\Modules\Bookstore\Models\Book;
use Illuminate\Support\Facades\Hash;

class BookstoreSeeder extends Seeder
{
    public function run(): void
    {
        // Create a test user
        BookstoreUser::updateOrCreate(
            ['email' => 'admin@bookstore.uz'],
            [
                'name' => 'Bookstore Admin',
                'password' => Hash::make('admin123'),
            ]
        );

        // Create some sample books with barcodes
        $books = [
            [
                'title' => 'O\'tkan kunlar',
                'author' => 'Abdulla Qodiriy',
                'barcode' => '9780001',
                'price' => 45000,
                'stock' => 10,
            ],
            [
                'title' => 'Mehrobdan chayon',
                'author' => 'Abdulla Qodiriy',
                'barcode' => '9780002',
                'price' => 42000,
                'stock' => 15,
            ],
            [
                'title' => 'Sariq devni minib',
                'author' => 'Xudoyberdi To\'xtaboyev',
                'barcode' => '9780003',
                'price' => 35000,
                'stock' => 20,
            ],
            [
                'title' => 'Dunyoning ishlari',
                'author' => 'O\'tkir Hoshimov',
                'barcode' => '9780004',
                'price' => 38000,
                'stock' => 12,
            ],
        ];

        foreach ($books as $book) {
            Book::updateOrCreate(['barcode' => $book['barcode']], $book);
        }
    }
}
