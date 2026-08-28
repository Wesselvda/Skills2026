@extends('layouts.layout')

@section('title')
    {{ $page->title }}
@endsection

@section('main')
    <div class="container">
        <img class="thumbnail" src="/storage/{{ $page->image_filename }}" alt="Thumbnail image">
        <h1>{{ $page->title }}</h1>
        <p class="intro">{{ $page->intro }}</p>
        <p class="description">{{ $page->description }}</p>

        @isset($builds)
            {{-- Builds --}}
            @if(count($builds) > 0)
                <div class="builds-list">
                    @foreach ($builds as $build)
                        <a href="/projects/{{ $build->slug }}" class="build">
                            <img src="/storage/{{ ltrim($build->thumbnail_filename, '/') }}" alt="Thumbnail">
                            <h2>{{ $build->title }}</h2>
                        </a>
                    @endforeach
                </div>
            @else
                <p>No builds found</p>
            @endif
        @endisset

        @isset($competitions)
            <div class="competitions-list">
                @foreach ($competitions as $competition)
                    <div class="competition">
                        <img src="/storage/{{ ltrim($competition->image_filename, '/') }}" alt="Competition image">
                        <div>
                            <h2>{{ $competition->title }}</h2>
                            <p>{{ $competition->text }}</p>
                            <span class="position position-{{ $competition->position }}">Position {{ $competition->position }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endisset
    </div>
@endsection

@section('navigation')
    @foreach ($navigation as $navitem)
        <a href="/{{ $navitem->slug }}">{{ $navitem->title }}</a>
    @endforeach
@endsection
