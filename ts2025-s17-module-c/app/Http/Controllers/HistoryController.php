<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HistoryController extends ApiController
{
    public function getHistories(Request $request)
    {
        $user = $this->requireUser($request);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        return $this->data(Booking::query()
            ->with('bicycle')
            ->where('user_id', $user->id)
            ->orderByDesc('started_at')
            ->get()
            ->map(fn (Booking $booking) => [
                'id' => $booking->id,
                'pricePerMin' => $booking->price_per_min,
                'fullPrice' => $booking->full_price,
                'startedAt' => $booking->started_at,
                'endedAt' => $booking->ended_at,
                'bicycle' => ['name' => $booking->bicycle->name],
            ]));
    }
}
