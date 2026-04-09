@extends('layouts.app')

@section('content')
    <article class="detail-shell">
        <section class="detail-hero">
            <div class="cover-spotlight" data-cover-spotlight>
                <img src="{{ $page['cover_url'] }}" alt="{{ $page['title'] }} cover" loading="lazy">
            </div>
            <h1 class="detail-title">{{ $page['title'] }}</h1>
        </section>

        <section class="detail-card">
            <div class="content-columns">
                <section class="content-main heritage-content">
                    {!! $page['rendered_html'] !!}
                </section>

                <aside class="content-side detail-meta">
                    <p><strong>Date:</strong> {{ $page['date_label'] }}</p>
                    <p>
                        <strong>tags:</strong>
                        @if (empty($page['tags']))
                            -
                        @else
                            @foreach ($page['tags'] as $tag)
                                <a href="{{ route('heritages.tag', ['tag' => $tag]) }}">{{ $tag }}</a>@if (!$loop->last), @endif
                            @endforeach
                        @endif
                    </p>
                    <p><strong>Draft:</strong> {{ $page['draft'] ? 'true' : 'false' }}</p>
                </aside>
            </div>
        </section>
    </article>

    <div class="image-lightbox" data-image-lightbox hidden>
        <img src="" alt="Big image">
    </div>
@endsection
