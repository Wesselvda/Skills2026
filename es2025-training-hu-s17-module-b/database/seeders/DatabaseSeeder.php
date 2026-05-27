<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $sqlPath = base_path('public/assets/data/skillshare_academy_sample_data.sql');
        $sqlDump = file_get_contents($sqlPath);

        $sqlDump = str_replace(["\r\n", "\r"], "\n", $sqlDump);
        $statement = '';

        foreach (explode("\n", $sqlDump) as $line) {
            $trimmedLine = trim($line);

            if ($trimmedLine === '' || str_starts_with($trimmedLine, '--')) {
                continue;
            }

            $statement .= $line . "\n";

            if (! str_ends_with($trimmedLine, ';')) {
                continue;
            }

            DB::statement(trim($statement));
            $statement = '';
        }

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@ssa.org'],
            [
                'name' => 'Zeus Helmet',
                'password' => Hash::make('skills2025admin1'),
                'role' => 'admin',
                'first_name' => 'Zeus',
                'last_name' => 'Helmet',
                'registration_date' => '2025-08-10 14:23:00',
                'status' => 'active',
                'credit_balance' => 0,
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'alice@example.com'],
            [
                'name' => 'Alice Johnson',
                'password' => Hash::make('WtfiA?'),
                'role' => 'user',
                'first_name' => 'Alice',
                'last_name' => 'Johnson',
                'registration_date' => '2025-08-10 14:23:00',
                'status' => 'active',
                'credit_balance' => 0,
            ]
        );
    }
}
