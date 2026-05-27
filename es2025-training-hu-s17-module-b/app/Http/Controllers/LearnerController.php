<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class LearnerController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $status = (string) $request->input('status', 'all');

        $learners = User::query()
            ->where('role', 'user')
            ->withSum('enrollments as completed_chapters_total', 'completed_chapters');

        if ($search !== '') {
            $learners->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%');
            });
        }

        if (in_array($status, ['active', 'suspended'], true)) {
            $learners->where('status', $status);
        }

        $learners = $learners
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate(10)
            ->withQueryString();

        return view('learners.index', [
            'title' => 'Learner Management',
            'learners' => $learners,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function suspend(User $user)
    {
        if ($user->role !== 'user') {
            abort(404);
        }

        $user->update([
            'status' => 'suspended',
        ]);

        return back()->with('status', 'Learner suspended.');
    }

    public function enable(User $user)
    {
        if ($user->role !== 'user') {
            abort(404);
        }

        $user->update([
            'status' => 'active',
        ]);

        return back()->with('status', 'Learner enabled.');
    }
}
