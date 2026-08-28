@extends('layouts.layout')

@section('title')
    404 - Niet beschikbaar
@endsection

@section('main')
    <div class="container">
        <h1>404</h1>
        <p>Deze pagina is helaas niet beschikbaar.</p>
    </div>
@endsection

@php
    $navigation = \App\Models\Page::where('show_in_navigation', true)->get();
@endphp

@section('navigation')
    @foreach ($navigation as $navitem)
        <a href="/{{ $navitem->slug }}">{{ $navitem->title }}</a>
    @endforeach
@endsection