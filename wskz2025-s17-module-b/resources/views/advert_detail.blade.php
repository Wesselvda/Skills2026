@extends('layouts.app')

{{-- Advert Details Page

The page contains the following data:

    ID, title, text, price, view count, category name, status, photo list (as a tile grid)
    ID, name, phone, and email of the author
    Interface for changing status
    Data on connected paid services, including expired ones (type, activation date, validity period)
    Ability to enable or disable a paid service for the advert

Allowed status transitions:

    moderation → published
    moderation → declined
    published → declined --}}


@section('content')
    <section class="container">
        <h1>{{ $advert->title }} ({{ $advert->id }})</h1>
        <p>{{ $advert->text }}</p>
        <p>Price: {{ $advert->price }}</p>
        <p>Views: {{ $advert->views_count }}</p>
        <p>Category: {{ $advert->category->name }}</p>
        <p>Status: {{ $advert->status }}</p>

        <h2>Photos:</h2>
        <div class="photo-grid">
            @foreach ($advert->photos as $photo)
                <div class="photo-tile">
                    <img src="{{ asset('storage/images/' . $photo) }}" alt="Photo {{ $photo }}">
                </div>
            @endforeach
        </div>

        <h2>Author: ({{ $advert->author->id }})</h2>
        <p>Name: {{ $advert->author->name }}</p>
        <p>Phone: {{ $advert->author->phone }}</p>
        <p>Email: {{ $advert->author->email }}</p>

        <h2>Change Status:</h2>
        

    </section>
@endsection
