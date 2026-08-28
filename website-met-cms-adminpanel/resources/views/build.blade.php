@extends('layouts.layout')

@section('title')
    {{ $build->title }}
@endsection

@section('main')
    <div class="container project">
        <h1>{{ $build->title }}</h1>
        <p class="intro">{{ $build->intro }}</p>
        <p class="description">{{ $build->description }}</p>
        <p>Background image:</p>
        <img class="max-size" src="/storage/{{ ltrim($build->background_filename, '/') }}" alt="Background image">
        <p>Thumbnail image:</p>
        <img class="max-size" src="/storage/{{ ltrim($build->thumbnail_filename, '/') }}" alt="Thumbnail image">
        <p>Signatue image:</p>
        <img class="max-size" src="/storage/{{ ltrim($build->signature_filename, '/') }}" alt="Signatue image">
    </div>
@endsection

@section('navigation')
    @foreach ($navigation as $navitem)
        <a href="/{{ $navitem->slug }}">{{ $navitem->title }}</a>
    @endforeach
@endsection
