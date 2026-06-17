<?php

namespace App\Http\Controllers;

use App\Models\ApplicationUser;
use App\Models\Review;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class ReviewController extends Controller
{
    public function index()
    {
        $user = $this->currentUser();

        $submissions = Submission::query()
            ->with(['user', 'category'])
            ->withCount(['reviews as valid_reviews_count' => fn ($query) => $query->where('is_valid', true)])
            ->where('status', 'under_review')
            ->where('user_id', '!=', $user->id)
            ->whereDoesntHave('reviews', fn ($query) => $query
                ->where('user_id', $user->id)
                ->where('is_valid', true))
            ->whereHas('reviews', fn ($query) => $query->where('is_valid', true), '<', 3)
            ->oldest('updated_at')
            ->get();

        return view('reviews.index', [
            'submissions' => $submissions,
        ]);
    }

    public function create(Submission $submission)
    {
        if (! $this->canReview($submission)) {
            return redirect()->route('reviews.index')
                ->withErrors(['review' => 'This submission is not available for review.']);
        }

        $submission->load(['user', 'category']);

        return view('reviews.create', [
            'submission' => $submission,
        ]);
    }

    public function store(Request $request, Submission $submission)
    {
        if (! $this->canReview($submission)) {
            return redirect()->route('reviews.index')
                ->withErrors(['review' => 'This submission is not available for review.']);
        }

        $validated = $request->validate([
            'verdict' => ['required', Rule::in(['positive', 'negative'])],
            'comment' => ['required', 'string', 'min:20'],
        ]);

        DB::transaction(function () use ($submission, $validated): void {
            $submission->refresh();

            if (! $this->canReview($submission)) {
                abort(403);
            }

            Review::create([
                'user_id' => session('user_id'),
                'submission_id' => $submission->id,
                'is_valid' => true,
                'is_positive' => $validated['verdict'] === 'positive',
                'comment' => $validated['comment'],
            ]);

            $this->currentUser()->increment('credits', 2);

            $this->resolveSubmissionIfComplete($submission);
        });

        return redirect()->route('reviews.history')->with('success', 'Review submitted.');
    }

    public function file(Submission $submission): Response
    {
        if (! $this->canReview($submission)) {
            abort(403);
        }

        if (! Storage::exists($submission->file_path)) {
            abort(404);
        }

        return response()->file(Storage::path($submission->file_path), [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function history()
    {
        $reviews = Review::query()
            ->where('user_id', session('user_id'))
            ->with('submission')
            ->latest('created_at')
            ->get()
            ->map(function (Review $review): Review {
                $review->history_status = $this->historyStatus($review);

                return $review;
            });

        return view('reviews.history', [
            'reviews' => $reviews,
        ]);
    }

    private function canReview(Submission $submission): bool
    {
        if ($submission->status !== 'under_review') {
            return false;
        }

        if ($submission->user_id === session('user_id')) {
            return false;
        }

        if ($submission->reviews()->where('is_valid', true)->count() >= 3) {
            return false;
        }

        return ! $submission->reviews()
            ->where('user_id', session('user_id'))
            ->where('is_valid', true)
            ->exists();
    }

    private function resolveSubmissionIfComplete(Submission $submission): void
    {
        $reviews = $submission->reviews()
            ->where('is_valid', true)
            ->get();

        if ($reviews->count() < 3) {
            return;
        }

        $isApproved = $reviews->where('is_positive', true)->count() >= 2;

        $submission->update([
            'status' => $isApproved ? 'approved' : 'rejected',
        ]);

        foreach ($reviews as $review) {
            $review->is_positive === $isApproved
                ? ApplicationUser::whereKey($review->user_id)->increment('reputation')
                : ApplicationUser::whereKey($review->user_id)->decrement('reputation');
        }
    }

    private function historyStatus(Review $review): string
    {
        if (! $review->is_valid) {
            return 'outdated';
        }

        if ($review->submission->status === 'submitted') {
            return 'approved';
        }

        return str_replace('_', ' ', $review->submission->status);
    }
}
