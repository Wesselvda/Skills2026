<?php

namespace App\Http\Controllers;

use App\Models\ApplicationUser;
use App\Models\Booking;
use App\Models\UserToken;
use Illuminate\Http\Request;

abstract class ApiController extends Controller
{
    public function data(mixed $data, int $status = 200)
    {
        return response()->json(['data' => $data], $status);
    }

    public function empty(int $status = 204)
    {
        return response()->json(null, $status);
    }

    public function fail(mixed $message, int $status, ?string $error = null)
    {
        $payload = ['message' => $message, 'statusCode' => $status];

        if ($error !== null) {
            $payload['error'] = $error;
        }

        return response()->json($payload, $status);
    }

    public function user(Request $request)
    {
        $token = $request->bearerToken();

        if (! $token) {
            return null;
        }

        return UserToken::query()
            ->where('hash', hash('sha256', $token))
            ->first()
            ?->user;
    }

    public function requireUser(Request $request)
    {
        $user = $this->user($request);

        if (! $user) {
            return $this->fail('Unauthorized', 401);
        }

        return $user;
    }

    public function bicycleRating(string $bicycleId)
    {
        $ratings = Booking::query()
            ->where('bicycle_id', $bicycleId)
            ->whereNotNull('rating')
            ->pluck('rating');

        return round((5 * 10 + $ratings->sum()) / (10 + $ratings->count()), 1);
    }

    public function userRating(ApplicationUser|string $user)
    {
        $userId = $user instanceof ApplicationUser ? $user->id : $user;
        $ratings = Booking::query()
            ->where('user_id', $userId)
            ->whereNotNull('userRating')
            ->pluck('userRating');

        return round((5 * 10 + $ratings->sum()) / (10 + $ratings->count()), 1);
    }
}
