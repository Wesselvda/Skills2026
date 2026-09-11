<?php

namespace Database\Seeders;

use App\Models\Advert;
use App\Models\Category;
use App\Models\PaidService;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use JsonException;

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

            $advert = Advert::create([
                'title' => $row[0],
                'text' => $row[1],
                'status' => $row[2],
                'price' => $row[3],
                'views_count' => $row[4],
                'category_id' => $row[5],
                'author_email' => $row[6],
                'photos' => $this->decodeJsonArray($row[7], 'photos'),
            ]);

            foreach ($this->decodeJsonArray($row[8], 'paid_services') as $type) {
                PaidService::create([
                    'advert_id' => $advert->id,
                    'type' => $type,
                    'activated_at' => $type === 'top' ? now()->subDays(8) : now()->subDay(),
                    'validity_days' => $type === 'top' ? 7 : 30,
                ]);
            }
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

    /**
     * Decode an array value from the CSV before Eloquent applies its JSON cast.
     */
    private function decodeJsonArray(string $value, string $column): array
    {
        try {
            $decoded = json_decode(str_replace('\\"', '"', $value), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new \RuntimeException("Invalid JSON in adverts CSV column [{$column}].", previous: $exception);
        }

        if (! is_array($decoded)) {
            throw new \RuntimeException("Expected a JSON array in adverts CSV column [{$column}].");
        }

        return $decoded;
    }
}
