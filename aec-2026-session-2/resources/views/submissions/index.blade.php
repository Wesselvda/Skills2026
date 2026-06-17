@extends('layouts.layout')

@section('content')
    <section class="section container-inner">
        <div class="page-header">
            <div>
                <p class="small-title">Peer review</p>
                <h1>My submissions</h1>
            </div>
            <a class="button" href="{{ route('submissions.create') }}">Create submission</a>
        </div>

        @if($submissions->isEmpty())
            <div class="simple-panel">
                <p>You have not created any submissions yet.</p>
                <a class="button" href="{{ route('submissions.create') }}">Create submission</a>
            </div>
        @else
            <div class="item-list">
                @foreach($submissions as $submission)
                    <a class="list-item" href="{{ route('submissions.show', $submission) }}">
                        <span>
                            <strong>{{ $submission->title }}</strong>
                            <small>Updated {{ $submission->updated_at->format('d-m-Y H:i') }}</small>
                        </span>
                        <span>{{ $submission->valid_reviews_count }} reviews</span>
                        <span>{{ str_replace('_', ' ', $submission->status) }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
@endsection
