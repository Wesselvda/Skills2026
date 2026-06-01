<?php

namespace App\Http\Controllers;

use App\Models\MentorSession;
use Illuminate\Http\Request;

class MentorController extends Controller
{
    public function index()
    {
        $mentorSessions = MentorSession::all();
        
        return response()->json(['sessions' => $mentorSessions->map(function ($session) {
            return [
                'id' => $session->id,
                'mentorName' => $session->mentor_name,
                'expertise' => $session->expertise,
                'experienceLevel' => $session->experience_level,
                'sessionDate' => $session->session_date->toIso8601String(),
                'durationMinutes' => $session->duration_minutes,
                'creditCost' => $session->credit_cost,
                'isAvailable' => $session->is_available,
            ];
        })]);
    }

    public function book($id)
    {
        $session = MentorSession::find($id);

        if ($session) {
            if (!$session->is_available) {
                return response()->json(['message' => 'Session not available'], 409);
            }

            $user = request()->user();

            if ($session->credit_cost > $user->credit_balance) {
                return response()->json(['message' => 'Insufficient credits'], 403);
            }

            $sessionBooking = $user->sessionBookings()->create([
                'mentor_session_id' => $session->id,
                'credits_paid' => $session->credit_cost,
            ]);

            $sessionBooking->refresh();

            $user->credit_balance -= $session->credit_cost;
            $user->save();

            $session->is_available = false;
            $session->save();

            return response()->json([
                'message' => 'Session booked successfully',
                'booking' => [
                    'id' => $sessionBooking->id,
                    'sessionId' => $session->id,
                    'status' => $sessionBooking->status,
                    'creditsPaid' => $sessionBooking->credits_paid,
                    'bookedAt' => $sessionBooking->booked_at->toIso8601String(),
                ]
            ], 200);
        }

        return response()->json(['message' => 'Session not available'], 404);
    }
}
