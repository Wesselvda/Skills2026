<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = base_path('public/assets/data/books.json');
        $books = json_decode(file_get_contents($path), true);

        foreach ($books as $book) {
            Book::updateOrCreate(
                ['id' => $book['id']],
                [
                    'author_id' => $book['author_id'],
                    'released_at' => $book['released_at'],
                    'price' => $book['price'],
                    'stock' => $book['stock'],
                    'cover_image' => $book['cover_image'],
                    'original_language' => $book['original_language'],
                    'title' => $book['title'],
                    'category' => $book['category'],
                    'abstract' => $book['abstract'],
                ],
            );
        }
    }
}
