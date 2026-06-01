<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $dumpPath = public_path('assets/db/skillshare_academy.sql');

        if (! is_file($dumpPath)) {
            throw new RuntimeException("SQL dump not found at {$dumpPath}");
        }

        $sql = file_get_contents($dumpPath);

        if ($sql === false) {
            throw new RuntimeException("Unable to read SQL dump at {$dumpPath}");
        }

        $sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;
        $sql = preg_replace('/\/\*!\d+.*?\*\//s', '', $sql) ?? $sql;

        $statements = array_values(array_filter(array_map(
            static fn (string $statement): string => trim($statement),
            preg_split('/;\s*(?:\r?\n|$)/', $sql) ?: []
        ), static fn (string $statement): bool => $statement !== ''));

        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            try {
                foreach ([
                    'session_bookings',
                    'mentor_sessions',
                    'enrollments',
                    'chapter_completions',
                    'api_tokens',
                    'users',
                ] as $table) {
                    DB::table($table)->delete();
                }

                foreach ($statements as $statement) {
                    if (preg_match('/^INSERT\s+INTO/i', $statement) !== 1) {
                        continue;
                    }

                    DB::unprepared($statement);
                }
            } finally {
                DB::statement('PRAGMA foreign_keys = ON');
            }

            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ([
                'session_bookings',
                'mentor_sessions',
                'enrollments',
                'chapter_completions',
                'api_tokens',
                'users',
            ] as $table) {
                Schema::dropIfExists($table);
            }

            foreach ($statements as $statement) {
                DB::unprepared($statement);
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}
