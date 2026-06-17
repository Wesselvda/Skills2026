<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $categoryFile = storage_path('app/private/categories.txt');

        if (File::exists($categoryFile)) {
            collect(File::lines($categoryFile))
                ->map(fn (string $category): string => trim($category))
                ->filter()
                ->each(fn (string $category): Category => Category::firstOrCreate(['name' => $category]));
        }
    }
}
