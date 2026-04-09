@extends('layouts.app')

@section('content')
    @php
        $displayTitle = $listingTitle;
    @endphp

    <section class="listing-shell">
        <h1 class="listing-title">{{ $displayTitle }}</h1>

        <div class="content-columns listing-layout">
            <div class="content-main listing-results">
                <ul>
                    @foreach ($listing['folders'] as $folder)
                        <li>
                            <article>
                                <h3><a href="{{ $folder['url'] }}">{{ $folder['name'] }}</a></h3>
                            </article>
                        </li>
                    @endforeach

                    @foreach ($pages as $item)
                        <li>
                            <article>
                                <h3><a href="{{ $item['url'] }}">{{ $item['title'] }}</a></h3>
                                @if ($item['summary'] !== '')
                                    <p class="listing-summary">{{ $item['summary'] }}</p>
                                @endif
                            </article>
                        </li>
                    @endforeach
                </ul>

                @if (count($listing['folders']) === 0 && count($pages) === 0)
                    <p>No results found.</p>
                @endif
            </div>

            <aside class="content-side listing-sidebar">
                <div class="search-panel">
                    <h2>Search</h2>
                    <form action="{{ route('heritages.search') }}" method="get">
                        <label for="search-q">Search keyword</label>
                        <input id="search-q" name="q" type="text" value="{{ $searchKeywords ?? '' }}" placeholder="KEYWORD">
                        <button type="submit">Search</button>
                    </form>
                </div>
            </aside>
        </div>
    </section>
@endsection
