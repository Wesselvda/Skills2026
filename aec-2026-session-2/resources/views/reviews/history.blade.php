@extends('layouts.layout')

@section('content')
    <section class="section container-inner">
        <div class="page-header">
            <div>
                <h1>Review history</h1>
            </div>
            <a class="button" href="{{ route('reviews.index') }}">Review queue</a>
        </div>

        @if($reviews->isEmpty())
            <div class="simple-panel">
                <p>You have not written any reviews yet.</p>
            </div>
        @else
            <div class="item-list">
                @foreach($reviews as $review)
                    <div class="list-item">
                        <span>
                            <strong>{{ $review->submission->title }}</strong>
                            <small>{{ $review->created_at->format('d-m-Y H:i') }}</small>
                        </span>
                        <span>{{ $review->is_positive ? 'Positive' : 'Negative' }}</span>
                        <span>{{ $review->history_status }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection
