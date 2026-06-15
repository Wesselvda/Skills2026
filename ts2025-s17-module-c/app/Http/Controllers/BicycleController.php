<?php

namespace App\Http\Controllers;

use App\Models\BalanceHistory;
use App\Models\Bicycle;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BicycleController extends ApiController
{
    public function getBicycles(Request $request)
    {
        $user = $this->requireUser($request);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        $busyBicycles = Booking::whereNull('ended_at')->pluck('bicycle_id')->all();

        return $this->data(Bicycle::get()->map(fn (Bicycle $bicycle) => [
            'id' => $bicycle->id,
            'slug' => $bicycle->slug,
            'locationX' => $bicycle->location_x,
            'locationY' => $bicycle->location_y,
            'status' => in_array($bicycle->id, $busyBicycles, true) ? 'BUSY' : $bicycle->status,
        ]));
    }

    public function getBicycle(Request $request, string $bicycleId)
    {
        $user = $this->requireUser($request);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        $bicycle = Bicycle::find($bicycleId);

        if (! $bicycle) {
            return $this->fail('Bicycle not found', 404, 'Not Found');
        }

        return $this->data([
            'name' => $bicycle->name,
            'description' => $bicycle->description,
            'percentageOfWear' => $bicycle->percentage_of_wear,
            'pathToImage' => $bicycle->path_to_image,
            'rating' => $this->bicycleRating($bicycle->id),
        ]);
    }

    public function repairBicycle(Request $request, string $bicycleId)
    {
        $user = $this->requireUser($request);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        $validated = $request->validate(['type' => ['required', 'in:wash,repair,tires,chain']]);
        $bicycle = Bicycle::with('category')->find($bicycleId);

        if (! $bicycle || $bicycle->category->user_id !== $user->id) {
            return $this->fail('Bicycle not found', 404, 'Not Found');
        }

        $services = [
            'wash' => ['price' => 2000, 'wear' => 10],
            'repair' => ['price' => 10000, 'wear' => 25],
            'tires' => ['price' => 6500, 'wear' => 35],
            'chain' => ['price' => 15000, 'wear' => 30],
        ];
        $service = $services[$validated['type']];

        if ($user->balance < $service['price']) {
            return $this->fail("You don't have enough funds", 409, 'Conflict');
        }

        $user->decrement('balance', $service['price']);
        $bicycle->update(['percentage_of_wear' => max(0, $bicycle->percentage_of_wear - $service['wear'])]);
        BalanceHistory::create([
            'id' => (string) Str::uuid(),
            'type' => 'WITHDRAWAL',
            'value' => $service['price'],
            'user_id' => $user->id,
            'created_at' => now(),
        ]);

        return $this->empty();
    }

    public function getBicycleBookings(Request $request, string $bicycleId)
    {
        $user = $this->requireUser($request);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        $bicycle = Bicycle::find($bicycleId);

        if (! $bicycle) {
            return $this->fail('Bicycle not found', 404, 'Not Found');
        }

        return $this->data($bicycle->bookings()
            ->whereNotNull('ended_at')
            ->orderByDesc('ended_at')
            ->get()
            ->map(fn (Booking $booking) => [
                'id' => $booking->id,
                'percentageOfWear' => $booking->percentage_of_wear,
                'photos' => $booking->photos ?? [],
            ]));
    }

    public function rateBooking(Request $request, string $bicycleId, string $rentalId)
    {
        $user = $this->requireUser($request);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        $validated = $request->validate(['rating' => ['required', 'integer', 'min:1', 'max:5']]);
        $bicycle = Bicycle::with('category')->find($bicycleId);

        if (! $bicycle) {
            return $this->fail('Bicycle not found', 404, 'Not Found');
        }

        $booking = Booking::where('id', $rentalId)->where('bicycle_id', $bicycleId)->first();

        if (! $booking) {
            return $this->fail('Rental not found', 404, 'Not Found');
        }

        if ($booking->userRating !== null) {
            return $this->fail('The rent has already been assessed', 403, 'Forbidden');
        }

        $reward = (int) floor(($booking->full_price ?? 0) * 0.05);

        if ($reward > 0) {
            $bicycle->category->user->decrement('balance', $reward);
            $user->increment('balance', $reward);
            BalanceHistory::create([
                'id' => (string) Str::uuid(),
                'type' => 'MODERATION_REWARD',
                'value' => $reward,
                'user_id' => $user->id,
                'created_at' => now(),
            ]);
        }

        $booking->update(['userRating' => $validated['rating']]);

        return $this->empty();
    }
}
