<?php

namespace Database\Seeders;

use App\Models\ApplicationUser;
use App\Models\InvestmentRequest;
use App\Models\PresentingSponsor;
use App\Models\Tour;
use App\Models\Turbine;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        
        // - **2 users**:
        //     - Email: `admin@localhost` / Role: `admin`
        //     - Email: `user@localhost` / Role: `user`
        // - **10 turbines** (unique names, e.g. `Turbine 1`, `Turbine 2`, etc.)
        // - **2 investments: Fund turbine**: Turbine, text
        // - **2 investments: Fund turbine**: Turbine, logo
        // - **2 investments: Presenting sponsor**: Logo
        // - **5 tours**: unique date + time, capacity 10
        // - **2 bookings**: on same tour, 2 seats each

        ApplicationUser::create([
            'email' => 'admin@localhost',
            'role' => 'admin',
        ]);

        $normalUser = ApplicationUser::create([
            'email' => 'user@localhost',
            'role' => 'user',
        ]);

        $investmentRequestOne = InvestmentRequest::create([
            'status' => 'approved',
            'investor_name' => 'John Doe',
            'investor_email' => 'john.doe@example.com',
            'investor_address' => 'Applestreet 1',
            'investor_phone' => '+31 1234567890',
            'investment_type' => 'turbine',
        ]);

        Turbine::create([
            'name' => "Turbine 1",
            'investment_id' => $investmentRequestOne->id,
            'status' => 'approved',
            'logo_filename' => 'lumen-flux.png',
        ]);

        $investmentRequestTwo = InvestmentRequest::create([
            'status' => 'approved',
            'investor_name' => 'John Doe',
            'investor_email' => 'john.doe@example.com',
            'investor_address' => 'Applestreet 1',
            'investor_phone' => '+31 1234567890',
            'investment_type' => 'turbine',
        ]);

        Turbine::create([
            'name' => "Turbine 2",
            'investment_id' => $investmentRequestTwo->id,
            'status' => 'approved',
            'logo_filename' => 'nova-power.png',
        ]);

        $investmentRequestThree = InvestmentRequest::create([
            'status' => 'approved',
            'investor_name' => 'John Doe',
            'investor_email' => 'john.doe@example.com',
            'investor_address' => 'Applestreet 1',
            'investor_phone' => '+31 1234567890',
            'investment_type' => 'turbine',
        ]);

        Turbine::create([
            'name' => "Turbine 3",
            'investment_id' => $investmentRequestThree->id,
            'status' => 'approved',
            'displayed_text' => 'Funded by John Doe',
        ]);

        $investmentRequestFour = InvestmentRequest::create([
            'status' => 'approved',
            'investor_name' => 'Jane Doe',
            'investor_email' => 'jane.doe@example.com',
            'investor_address' => 'Applestreet 1',
            'investor_phone' => '+31 1234567890',
            'investment_type' => 'turbine',
        ]);

        Turbine::create([
            'name' => "Turbine 4",
            'investment_id' => $investmentRequestFour->id,
            'status' => 'approved',
            'displayed_text' => 'Funded by Jane Doe',
        ]);

        for ($i = 5; $i <= 10; $i++) {
            Turbine::create([
                'name' => "Turbine $i",
            ]);
        }

        $investmentRequestFive = InvestmentRequest::create([
            'status' => 'approved',
            'investor_name' => 'John Doe',
            'investor_email' => 'john.doe@example.com',
            'investor_address' => 'Applestreet 1',
            'investor_phone' => '+31 1234567890',
            'investment_type' => 'presenting',
        ]);

        PresentingSponsor::create([
            'investment_id' => $investmentRequestFive->id,
            'status' => 'approved',
            'logo_filename' => 'terravolt.png',
        ]);

        $investmentRequestSix = InvestmentRequest::create([
            'status' => 'approved',
            'investor_name' => 'John Doe',
            'investor_email' => 'john.doe@example.com',
            'investor_address' => 'Applestreet 1',
            'investor_phone' => '+31 1234567890',
            'investment_type' => 'presenting',
        ]);

        PresentingSponsor::create([
            'investment_id' => $investmentRequestSix->id,
            'status' => 'approved',
            'logo_filename' => 'veridian-spark.png',
        ]);

        $tourOne = Tour::create([
            'tour_date' => '2026-08-01 10:00:00',
            'total_seats' => 10,
        ]);

        $tourOne->bookings()->create([
            'user_id' => $normalUser->id,
            'name' => 'John Doe',
            'address' => 'Applestreet 1',
            'phone' => '+31 1234567890',
            'seats' => 2,
        ]);
        
        $tourOne->bookings()->create([
            'user_id' => $normalUser->id,
            'name' => 'John Doe',
            'address' => 'Applestreet 1',
            'phone' => '+31 1234567890',
            'seats' => 2,
        ]);

        for ($i = 2; $i <= 5; $i++) {
            Tour::create([
                'tour_date' => "2026-08-0$i 1$i:00:00",
                'total_seats' => 10,
            ]);
        }
    }
}
