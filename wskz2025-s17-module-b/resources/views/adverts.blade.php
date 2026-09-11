@extends('layouts.app')

@section('content')
    <section class="container">
        <h1>Adverts</h1>
        <form method="GET" action="/adverts" class="filter-form">
            <div class="input-wrapper">
                <label for="status">Status:</label>
                <select name="status" id="status">
                    <option value="">All</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                    <option value="moderation" {{ request('status') == 'moderation' ? 'selected' : '' }}>Moderation</option>
                    <option value="declined" {{ request('status') == 'declined' ? 'selected' : '' }}>Declined</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
            </div>

            <div class="input-wrapper">
                <label for="category">Category:</label>
                <select name="category" id="category">
                    <option value="">All</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="input-wrapper">
                <label for="search">Search:</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}">
            </div>

            <button type="submit">Filter</button>
            <button type="submit" formaction="{{ route('adverts.export') }}">Export</button>
        </form>
        <div class="advert-wrapper">
            @foreach ($adverts as $advert)
                <a href="/adverts/{{ $advert->id }}" class="advert">
                    <h2>{{ $advert->title }}</h2>
                    <p>{{ $advert->text }}</p>
                    <p>Category: {{ $advert->category->name }}</p>
                    <p>Author: {{ $advert->author->name }}</p>
                    @if ($advert->paidServices->isNotEmpty())
                        <p>Paid Services: 
                            @foreach ($advert->paidServices as $service)
                                <span class="paid-service">{{ $service->type }}</span>
                            @endforeach
                        </p>
                    @endif
                </a>
            @endforeach
        </div>
    </section>
@endsection
