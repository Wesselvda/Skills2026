@extends('layouts.app')

@section('content')
    <section class="container">
        <h1>Welcome, {{ Auth::user()->name }}</h1>

        <p>There are currently <strong>{{ $userCount }}</strong> users.</p>

        <h2>Adverts: </h2>
        <div class="stats">
            <div class="stat">
                <div class="value">{{ $publishedCount }}</div>
                <div class="label">Published</div>
            </div>
            <div class="stat">
                <div class="value">{{ $moderationCount }}</div>
                <div class="label">Moderation</div>
            </div>
            <div class="stat">
                <div class="value">{{ $declinedCount }}</div>
                <div class="label">Declined</div>
            </div>
            <div class="stat">
                <div class="value">{{ $draftCount }}</div>
                <div class="label">Drafts</div>
            </div>
        </div>

        <h2>Top 10 adverts:</h2>
        <div class="adverts-list">
            @foreach ($topTenAdverts as $advert)
                <div>
                    <h3>{{ $advert->title }}</h3>
                    <p>Views: {{ $advert->views_count }}</p>
                </div>
            @endforeach
        </div>
    </section>
@endsection