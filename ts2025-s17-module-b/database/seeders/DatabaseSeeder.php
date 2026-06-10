<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Bicycle;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Tariff;
use App\Models\User;
use Carbon\Carbon;
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
        foreach ($this->csvRows('users.csv') as $row) {
            User::create([
                'user_key' => $row['user_key'],
                'name' => $row['name'],
                'email' => $row['email'],
                'phone' => $row['phone'],
                'password' => $this->passwordFromHashPlaceholder($row['password_hash']),
            ]);
        }

        foreach ($this->csvRows('categories.csv') as $row) {
            Category::create($row);
        }

        foreach ($this->csvRows('tariffs.csv') as $row) {
            Tariff::create($row);
        }

        foreach ($this->csvRows('bicycles.csv') as $row) {
            Bicycle::create($row);
        }

        foreach ($this->csvRows('applications.csv') as $row) {
            Application::create($row);
        }

        foreach ($this->csvRows('bookings.csv') as $row) {
            Booking::create([
                ...$row,
                'startedAt' => $this->dateExpression($row['startedAt']),
                'endedAt' => $this->dateExpression($row['endedAt']),
                'photos' => $row['photos'] ? json_decode($row['photos'], true) : null,
            ]);
        }
    }

    private function csvRows(string $filename)
    {
        $path = public_path("assets/data/{$filename}");
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return [];
        }

        $headers = fgetcsv($handle);
        $rows = [];

        while ($values = fgetcsv($handle)) {
            $row = [];

            foreach ($headers as $index => $header) {
                $value = trim($values[$index] ?? '');

                if ($value === '') {
                    $value = null;
                }

                $row[$header] = $value;
            }

            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    private function dateExpression(?string $value)
    {
        if ($value === null) {
            return null;
        }

        if ($value === 'NOW') {
            return now();
        }

        if (str_starts_with($value, 'NOW+') && str_ends_with($value, 'm')) {
            $minutes = str_replace('NOW+', '', $value);
            $minutes = str_replace('m', '', $minutes);

            return now()->addMinutes((int) $minutes);
        }

        return Carbon::parse($value);
    }

    private function passwordFromHashPlaceholder(string $value)
    {
        if (str_starts_with($value, '<hash(') && str_ends_with($value, ')>')) {
            $value = str_replace('<hash(', '', $value);
            $value = str_replace(')>', '', $value);
        }

        return Hash::make($value);
    }
}
