<?php

namespace Database\Seeders;

use App\Models\Design;
use App\Models\NavigationItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'name' => 'shopadmin',
            'email' => 'shopadmin@example.com',
            'password' => Hash::make('asdf'),
        ]);

        $tshirt = Product::create([
            'name' => 'T-Shirt',
            'price' => 25,
        ]);

        $tshirt->productColors()->createMany([
            [
                'name' => 'white',
                'image_filename' => 'tshirt-white.png'
            ],
            [
                'name' => 'blue',
                'image_filename' => 'tshirt-blue.png'
            ],
            [
                'name' => 'yellow',
                'image_filename' => 'tshirt-yellow.png'
            ],
        ]);

        $cup = Product::create([
            'name' => 'Cup',
            'price' => 12,
        ]);

        $cup->productColors()->createMany([
            [
                'name' => 'white',
                'image_filename' => 'cup-white.png'
            ],
            [
                'name' => 'blue',
                'image_filename' => 'cup-blue.png'
            ],
            [
                'name' => 'yellow',
                'image_filename' => 'cup-yellow.png'
            ],
        ]);

        NavigationItem::insert([
            [
                'name' => 'Home',
                'link' => '/',
                'order' => 1,
            ],
            [
                'name' => 'Cart',
                'link' => '/cart',
                'order' => 2,
            ],
            [
                'name' => 'Design Symbols',
                'link' => '#',
                'order' => 3,
            ],
            [
                'name' => 'T-Shirts',
                'link' => '#',
                'order' => 4,
            ],
            [
                'name' => 'Accessories',
                'link' => '#',
                'order' => 5,
            ],
            [
                'name' => 'Admin Area',
                'link' => '/admin',
                'order' => 6,
            ]
        ]);

        $designs = Storage::disk('public')->files('design_symbols');

        Log::info('Designs found: ' . count($designs));

        foreach ($designs as $design) {
            $filename = basename($design);
            $name = ucfirst(pathinfo($filename, PATHINFO_FILENAME));

            Design::create([
                'name' => $name,
                'image_filename' => $filename
            ]);
        }
    }
}
