@extends('layouts.layout')

@section('content')
    <section class="section container-inner">
        <div class="page-header">
            <div>
                <h1>{{ $submission->title }}</h1>
            </div>
            <a class="button" href="{{ route('submissions.index') }}">Back</a>
        </div>

        <div class="detail-grid">
            <div class="simple-panel">
                <dl class="meta-list">
                    <div>
                        <dt>Status</dt>
                        <dd>{{ str_replace('_', ' ', $submission->status) }}</dd>
                    </div>
                    <div>
                        <dt>Category</dt>
                        <dd>{{ str_replace('_', ' ', $submission->category->name) }}</dd>
                    </div>
                    <div>
                        <dt>Reviews</dt>
                        <dd>{{ $submission->reviews->count() }} of 3</dd>
                    </div>
                    <div>
                        <dt>Last update</dt>
                        <dd>{{ $submission->updated_at->format('d-m-Y H:i') }}</dd>
                    </div>
                </dl>

                <div class="action-row">
                    <a class="button" href="{{ route('submissions.file', $submission) }}" target="_blank">Open file</a>

                    @if($submission->status === 'under_review')
                        <a class="button" href="{{ route('submissions.edit', $submission) }}">Update</a>
                    @endif

                    @if($submission->status === 'approved')
                        <form method="POST" action="{{ route('submissions.publisher', $submission) }}">
                            @csrf
                            <button class="button" type="submit">Submit to publisher</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="simple-panel">
                <h2>Reviews</h2>
                @if($submission->reviews->isEmpty())
                    <p>No reviews yet.</p>
                @else
                    <div class="review-list">
                        @foreach($submission->reviews as $review)
                            <article>
                                <strong>{{ $review->is_positive ? 'Positive' : 'Negative' }}</strong>
                                <p>{{ $review->comment }}</p>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
