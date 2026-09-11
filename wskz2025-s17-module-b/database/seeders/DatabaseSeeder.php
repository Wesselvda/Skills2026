<?php

namespace Database\Seeders;

use App\Models\Advert;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $usersFilePath = storage_path('app/private/data/users.csv');
        $usersFile = fopen($usersFilePath, 'r');

        while (($row = fgetcsv($usersFile)) !== false) {
            if ($row[0] === 'name') {
                continue;
            }

            User::create([
                'name' => $row[0],
                'phone' => $row[1],
                'email' => $row[2],
                'password' => $this->parsePassword($row[3]),
                'role' => $row[4],
            ]);
        }

        fclose($usersFile);


        $categoriesFilePath = storage_path('app/private/data/categories.csv');
        $categoriesFile = fopen($categoriesFilePath, 'r');

        while (($row = fgetcsv($categoriesFile)) !== false) {
            if ($row[0] === 'id') {
                continue;
            }

            Category::create([
                'id' => $row[0],
                'name' => $row[1],
            ]);
        }

        fclose($categoriesFile);

        $advertsFilePath = storage_path('app/private/data/adverts.csv');
        $advertsFile = fopen($advertsFilePath, 'r');

        while (($row = fgetcsv($advertsFile)) !== false) {
            if ($row[0] === 'title') {
                continue;
            }

            Advert::create([
                'title' => $row[0],
                'text' => $row[1],
                'status' => $row[2],
                'price' => $row[3],
                'views_count' => $row[4],
                'category_id' => $row[5],
                'author_email' => $row[6],
                'photos' => json_decode($row[7], true),
                'paid_services' => json_decode($row[8], true),
            ]);
        }

        fclose($advertsFile);
    }

    private function parsePassword(string $password): string
    {
        if (str_starts_with($password, '<hash(') && str_ends_with($password, ')>')) {
            $password = substr($password, 6, -2);
            return Hash::make($password);
        }

        return Hash::make($password);
    }
}
