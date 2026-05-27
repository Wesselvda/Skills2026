<?php

namespace App\Http\Controllers;

use App\Models\Mentor;

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

        return back()->with('status', 'Mentor approved.');
    }
}
