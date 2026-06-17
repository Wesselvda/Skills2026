@extends('layouts.layout')

@section('content')
    <section class="section container-inner">
        <div class="page-header">
            <div>
                <h1>Review queue</h1>
            </div>
            <a class="button" href="{{ route('reviews.history') }}">Review history</a>
        </div>

        @if($submissions->isEmpty())
            <div class="simple-panel">
                <p>No submissions are available for review right now.</p>
            </div>
        @else
            <div class="item-list">
                @foreach($submissions as $submission)
                    <a class="list-item" href="{{ route('reviews.create', $submission) }}">
                        <span>
                            <strong>{{ $submission->title }}</strong>
                            <small>{{ $submission->user->full_name }}</small>
                        </span>
                        <span>{{ $submission->category->name }}</span>
                        <span>{{ $submission->valid_reviews_count }} reviews</span>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
@endsection
