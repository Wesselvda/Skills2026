<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Mentor;
use App\Models\Transaction;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $learnerCount = User::where('role', 'user')->count();
        $learnerChange = User::where('role', 'user')
            ->where('registration_date', '>=', now()->subDays(7))
            ->count();

        $courseCount = Course::count();

        $mentorCount = Mentor::where('approval_status', 'approved')->count();
        $mentorChange = Mentor::where('approval_status', 'approved')
            ->whereNotNull('approval_date')
            ->where('approval_date', '>=', now()->subDays(7))
            ->count();

        $creditCount = (int) Transaction::where('amount', '>', 0)->sum('amount');
        $creditChange = (int) Transaction::where('amount', '>', 0)
            ->where('created_at', '>=', now()->subDays(7))
            ->sum('amount');

        $activities = collect();

        User::where('role', 'user')
            ->whereNotNull('registration_date')
            ->orderByDesc('registration_date')
            ->limit(3)
            ->get()
            ->each(function ($user) use (&$activities) {
                $activities->push([
                    'activity' => 'Learner registration',
                    'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->name,
                    'time' => $user->registration_date,
                ]);
            });

        Mentor::with('user')
            ->whereNotNull('approval_date')
            ->orderByDesc('approval_date')
            ->limit(3)
            ->get()
            ->each(function ($mentor) use (&$activities) {
                $name = trim(($mentor->user?->first_name ?? '') . ' ' . ($mentor->user?->last_name ?? '')) ?: $mentor->user?->name;

                $activities->push([
                    'activity' => 'Mentor registration',
                    'name' => $name,
                    'time' => $mentor->approval_date,
                ]);
            });

        Transaction::with('user')
            ->where('transaction_type', 'credit_earned')
            ->where('related_entity_type', 'chapter')
            ->where('amount', '>', 0)
            ->orderByDesc('created_at')
            ->limit(3)
            ->get()
            ->each(function ($transaction) use (&$activities) {
                $name = trim(($transaction->user?->first_name ?? '') . ' ' . ($transaction->user?->last_name ?? '')) ?: $transaction->user?->name;

                $activities->push([
                    'activity' => 'Chapter completed',
                    'name' => $name,
                    'time' => $transaction->created_at,
                ]);
            });

        $activities = $activities
            ->sortByDesc('time')
            ->take(6)
            ->values();

        return view('dashboard.index', [
            'title' => 'Dashboard',
            'stats' => [
                [
                    'label' => 'Total learners',
                    'value' => number_format($learnerCount, 0, '', ' '),
                    'change' => $learnerChange,
                ],
                [
                    'label' => 'Active mentors',
                    'value' => number_format($mentorCount, 0, '', ' '),
                    'change' => $mentorChange,
                ],
                [
                    'label' => 'Total credits earned',
                    'value' => number_format($creditCount, 0, '', ' '),
                    'change' => $creditChange,
                ],
                [
                    'label' => 'Total courses',
                    'value' => number_format($courseCount, 0, '', ' '),
                    'change' => null,
                ],
            ],
            'activities' => $activities,
        ]);
    }
}
