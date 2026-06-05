<?php

namespace Database\Seeders;

use App\Models\ApplicationUser;
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
        ApplicationUser::create([
            'username' => 'user',
            'password' => Hash::make('user12345')
        ]);
        
        ApplicationUser::create([
            'username' => 'bob',
            'password' => Hash::make('bob12345'),
            'role' => 'operator'
        ]);
        
        ApplicationUser::create([
            'username' => 'alice',
            'password' => Hash::make('alice12345'),
            'role' => 'admin'
        ]);
    }
}
