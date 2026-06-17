<?php

namespace Database\Seeders;

use App\Models\Author;
use Illuminate\Database\Seeder;

class AuthorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = base_path('public/assets/data/authors.json');
        $authors = json_decode(file_get_contents($path), true);

        foreach ($authors as $author) {
            Author::updateOrCreate(
                ['id' => $author['id']],
                [
                    'forename' => $author['forename'],
                    'surname' => $author['surname'],
                    'date_of_birth' => $author['date_of_birth'],
                    'original_language' => $author['original_language'],
                    'location' => $author['location'],
                    'biography' => $author['biography'],
                ],
            );
        }
    }
}
