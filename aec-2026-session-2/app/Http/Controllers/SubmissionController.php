<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class SubmissionController extends Controller
{
    public function index()
    {
        $user = $this->currentUser();

        $submissions = Submission::query()
            ->whereBelongsTo($user, 'user')
            ->withCount(['reviews as valid_reviews_count' => fn ($query) => $query->where('is_valid', true)])
            ->latest('updated_at')
            ->get();

        return view('submissions.index', [
            'submissions' => $submissions,
        ]);
    }

    public function create()
    {
        return view('submissions.create', [
            'categories' => Category::query()->orderBy('name')->get(),
            'user' => $this->currentUser(),
        ]);
    }

    public function store(Request $request)
    {
        $user = $this->currentUser();

        if ($user->credits < 5) {
            return back()->withErrors(['credits' => 'You need 5 credits to create a submission.'])->withInput();
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'draft' => ['required', 'file', 'mimes:pdf'],
        ]);

        $path = $request->file('draft')->store('submissions');

        $submission = DB::transaction(function () use ($user, $validated, $path): Submission {
            $user->decrement('credits', 5);

            return Submission::create([
                'user_id' => $user->id,
                'category_id' => $validated['category_id'],
                'title' => $validated['title'],
                'file_path' => $path,
                'status' => 'under_review',
            ]);
        });

        return redirect()->route('submissions.show', $submission)->with('success', 'Submission created.');
    }

    public function show(Submission $submission)
    {
        $this->authorizeSubmissionOwner($submission);

        $submission->load([
            'category',
            'reviews' => fn ($query) => $query->where('is_valid', true)->latest(),
        ]);

        return view('submissions.show', [
            'submission' => $submission,
        ]);
    }

    public function file(Submission $submission): Response
    {
        $this->authorizeSubmissionOwner($submission);

        if (! Storage::exists($submission->file_path)) {
            abort(404);
        }

        return response()->file(Storage::path($submission->file_path), [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function edit(Submission $submission)
    {
        $this->authorizeSubmissionOwner($submission);

        if ($submission->status !== 'under_review') {
            return redirect()->route('submissions.show', $submission)
                ->withErrors(['submission' => 'Only submissions under review can be updated.']);
        }

        return view('submissions.edit', [
            'categories' => Category::query()->orderBy('name')->get(),
            'submission' => $submission,
            'user' => $this->currentUser(),
        ]);
    }

    public function update(Request $request, Submission $submission)
    {
        $user = $this->currentUser();
        $this->authorizeSubmissionOwner($submission);

        if ($submission->status !== 'under_review') {
            return redirect()->route('submissions.show', $submission)
                ->withErrors(['submission' => 'Only submissions under review can be updated.']);
        }

        if ($user->credits < 3) {
            return back()->withErrors(['credits' => 'You need 3 credits to update a submission.'])->withInput();
        }

        $validated = $request->validate([
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'draft' => ['required', 'file', 'mimes:pdf'],
        ]);

        $oldPath = $submission->file_path;
        $path = $request->file('draft')->store('submissions');

        DB::transaction(function () use ($submission, $user, $validated, $path): void {
            $submission->reviews()->where('is_valid', true)->update(['is_valid' => false]);
            $submission->update([
                'category_id' => $validated['category_id'],
                'file_path' => $path,
                'status' => 'under_review',
                'submitted_at' => null,
            ]);

            $user->decrement('credits', 3);
        });

        Storage::delete($oldPath);

        return redirect()->route('submissions.show', $submission)->with('success', 'Submission updated.');
    }

    public function submitPublisher(Submission $submission)
    {
        $this->authorizeSubmissionOwner($submission);

        if ($submission->status !== 'approved') {
            return redirect()->route('submissions.show', $submission)
                ->withErrors(['submission' => 'Only approved submissions can be submitted to the publisher.']);
        }

        $submission->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return redirect()->route('submissions.show', $submission)->with('success', 'Submission sent to the publisher.');
    }

    private function authorizeSubmissionOwner(Submission $submission): void
    {
        if ($submission->user_id !== session('user_id')) {
            abort(403);
        }
    }
}
