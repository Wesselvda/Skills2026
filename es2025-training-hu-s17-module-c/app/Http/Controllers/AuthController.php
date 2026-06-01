<?php

namespace App\Http\Controllers;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'name' => 'required|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['message' => "Failed to register user"], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($user) {
            return response()->json([
                'message' => 'User created successfully',
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                    'credits' => (int) $user->credit_balance,
                ]
            ], 201);
        } else {
            return response()->json(['message' => "Failed to register user"], 400);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->passes()) {
            $user = User::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                $token = ApiToken::create([
                    'user_id' => $user->id,
                    'token' => bin2hex(random_bytes(40)),
                ]);
                
                return response()->json([
                    'message' => 'Login successful',
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                        'name' => $user->name,
                        'credits' => (int) $user->credit_balance,
                    ],
                    "token" => $token->token
                ], 200);
            }
        }

        return response()->json(['message' => 'Invalid email or password'], 401);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        $apiToken = $request->header('X-API-TOKEN');

        ApiToken::where('token', $apiToken)->where('user_id', $user->id)->update(['revoked_at' => now()]);

        return response()->json(['message' => 'Logout successful'], 200);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load(['enrollments', 'chapterCompletions', 'sessionBookings']);

        $recentActivity = collect()
            ->concat($user->chapterCompletions->map(function ($completion) {
                return [
                    'type' => 'chapter_completed',
                    'description' => "Completed chapter {$completion->chapter_id}",
                    'creditsEarned' => $completion->credits_earned,
                    'timestamp' => $completion->completed_at->toIso8601String(),
                ];
            }))
            ->concat($user->sessionBookings->map(function ($booking) {
                return [
                    'type' => 'session_booked',
                    'description' => isset($booking->mentor_name)
                        ? "Booked session with {$booking->mentor_name}"
                        : 'Booked mentoring session',
                    'creditsPaid' => $booking->credits_paid,
                    'timestamp' => $booking->booked_at->toIso8601String(),
                ];
            }))
            ->sortByDesc('timestamp')
            ->take(5)
            ->values()
            ->all();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'creditBalance' => (int) $user->credit_balance,
            ],
            'stats' => [
                'enrolledCourses' => $user->enrollments->count(),
                'completedChapters' => $user->chapterCompletions->count(),
                'totalCreditsEarned' => (int) $user->chapterCompletions->sum('credits_earned'),
                'upcomingBookings' => $user->sessionBookings->where('session_time', '>', now())->count(),
            ],
            'recentActivity' => $recentActivity,
        ], 200);
    }
}
