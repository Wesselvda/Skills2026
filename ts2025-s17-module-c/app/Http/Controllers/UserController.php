<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\BalanceHistory;
use App\Models\Bicycle;
use App\Models\Booking;
use App\Models\PromoCode;
use App\Models\Tariff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends ApiController
{
    public function getUser(Request $request)
    {
        $user = $this->requireUser($request);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        return $this->data([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'balance' => $user->balance,
        ]);
    }

    public function getMyBicycles(Request $request)
    {
        $user = $this->requireUser($request);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        return $this->data(
            Bicycle::query()
                ->whereHas('category', fn ($query) => $query->where('user_id', $user->id))
                ->get()
                ->map(fn (Bicycle $bicycle) => [
                    'id' => $bicycle->id,
                    'name' => $bicycle->name,
                    'percentageOfWear' => $bicycle->percentage_of_wear,
                    'isOwner' => true,
                ]),
        );
    }

    public function getMyPayments(Request $request)
    {
        $user = $this->requireUser($request);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        $query = BalanceHistory::where('user_id', $user->id);

        if ($request->query('from')) {
            $query->where('created_at', '>=', $request->query('from'));
        }

        if ($request->query('to')) {
            $query->where('created_at', '<=', $request->query('to'));
        }

        return response()->json([
            'balance' => $user->balance,
            'payments' => $query->orderByDesc('created_at')->get()->map(fn (BalanceHistory $payment) => [
                'type' => $payment->type,
                'value' => $payment->value,
                'createdAt' => $payment->created_at,
            ]),
        ]);
    }

    public function getCurrentRent(Request $request)
    {
        $user = $this->requireUser($request);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        $booking = Booking::query()
            ->with('bicycle')
            ->where('user_id', $user->id)
            ->whereNull('ended_at')
            ->first();

        if (! $booking) {
            return $this->data(null);
        }

        return $this->data([
            'id' => $booking->id,
            'pricePerMin' => $booking->price_per_min,
            'startedAt' => $booking->started_at,
            'bicycle' => ['name' => $booking->bicycle->name],
        ]);
    }

    public function getWork(Request $request)
    {
        $user = $this->requireUser($request);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        $application = Application::query()
            ->with('category.user')
            ->where('user_id', $user->id)
            ->first();

        if (! $application) {
            return $this->data([]);
        }

        $owner = $application->category->user;

        return $this->data([
            'id' => $owner->id,
            'name' => $owner->name,
            'rating' => $this->userRating($owner),
            'status' => $application->status,
        ]);
    }

    public function rent(Request $request)
    {
        $user = $this->requireUser($request);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        $validated = $request->validate([
            'bicycleId' => ['required', 'string'],
            'tariffId' => ['required', 'string'],
            'promoCode' => ['nullable', 'string'],
        ]);

        if (Booking::where('user_id', $user->id)->whereNull('ended_at')->exists()) {
            return $this->fail("You're already renting a bike", 409, 'Conflict');
        }

        $bicycle = Bicycle::find($validated['bicycleId']);

        if (! $bicycle) {
            return $this->fail('Bicycle not found', 404, 'Not Found');
        }

        if ($bicycle->status !== 'AVAILABLE') {
            return $this->fail('The bike is not available', 409, 'Conflict');
        }

        if ($bicycle->percentage_of_wear >= 50) {
            return $this->fail('The bike is broken', 409, 'Conflict');
        }

        if ($bicycle->bookings()->whereNull('ended_at')->exists()) {
            return $this->fail('The bike is already taken', 409, 'Conflict');
        }

        if ($user->balance < 1000) {
            return $this->fail('There are not enough funds for insurance', 409, 'Conflict');
        }

        $tariff = Tariff::query()
            ->where('id', $validated['tariffId'])
            ->where('category_id', $bicycle->category_id)
            ->whereNull('deleted_at')
            ->first();

        if (! $tariff) {
            return $this->fail('Tariff not found', 404, 'Not Found');
        }

        $promoCodeId = null;

        if (! empty($validated['promoCode'])) {
            $promoCode = PromoCode::where('code', $validated['promoCode'])->first();

            if (! $promoCode) {
                return $this->fail('Promo code not found', 404, 'Not Found');
            }

            if ($promoCode->expires_at->isPast()) {
                return $this->fail('The promo code is not valid', 409, 'Conflict');
            }

            $promoCodeId = $promoCode->id;
        }

        $user->decrement('balance', 1000);
        Booking::create([
            'id' => (string) Str::uuid(),
            'price_per_min' => $tariff->base_price,
            'started_at' => now(),
            'bicycle_id' => $bicycle->id,
            'tariff_id' => $tariff->id,
            'user_id' => $user->id,
            'promo_code_id' => $promoCodeId,
        ]);

        return $this->empty();
    }

    public function finish(Request $request)
    {
        $user = $this->requireUser($request);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        $validated = $request->validate([
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'photos' => ['required', 'array', 'min:2'],
        ]);

        $booking = Booking::where('user_id', $user->id)->whereNull('ended_at')->first();

        if (! $booking) {
            return $this->fail('Rental not found', 404, 'Not Found');
        }

        $minutes = max(1, now()->diffInMinutes($booking->started_at));
        $fullPrice = (int) ($minutes * $booking->price_per_min);
        $wear = (int) floor($minutes * 0.1);

        if ($user->balance + 1000 < $fullPrice) {
            return $this->fail('There are not enough funds', 409, 'Conflict');
        }

        $user->update(['balance' => $user->balance + 1000 - $fullPrice]);
        BalanceHistory::create([
            'id' => (string) Str::uuid(),
            'type' => 'RENTAL',
            'value' => $fullPrice,
            'user_id' => $user->id,
            'created_at' => now(),
        ]);
        $booking->update([
            'full_price' => $fullPrice,
            'rating' => $validated['rating'] ?? null,
            'percentage_of_wear' => $wear,
            'ended_at' => now(),
            'photos' => $validated['photos'],
        ]);
        $booking->bicycle()->increment('percentage_of_wear', $wear);

        return $this->empty();
    }
}
