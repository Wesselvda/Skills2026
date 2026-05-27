<?php

namespace App\Http\Controllers;

use App\Models\Mentor;
use App\Models\Audit;
use Illuminate\Support\Facades\Auth;

class MentorController extends Controller
{
    public function index()
    {
        $mentors = Mentor::with('user')
            ->orderByRaw("case approval_status when 'pending' then 0 when 'approved' then 1 else 2 end")
            ->orderByDesc('approval_date')
            ->orderBy('id')
            ->get();

        return view('mentors.index', [
            'title' => 'Mentor Management',
            'mentors' => $mentors,
        ]);
    }

    public function approve(Mentor $mentor)
    {
        $mentor->update([
            'approval_status' => 'approved',
            'approval_date' => now(),
        ]);

        if ($mentor->user) {
            $mentor->user->update([
                'status' => 'active',
            ]);
        }

        // Audit log
        try {
            Audit::create([
                'admin_user_id' => Auth::id(),
                'action' => 'approve_mentor',
                'target_type' => 'mentor',
                'target_id' => $mentor->id,
                'description' => 'Approved mentor: ' . ($mentor->user?->name ?? $mentor->id),
            ]);
        } catch (\Throwable $e) {
            // do nothing
        }

        return back()->with('status', 'Mentor approved.');
    }
}
