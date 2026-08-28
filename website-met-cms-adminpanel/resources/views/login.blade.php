@extends('layouts.layout')

@section('title')
    Login
@endsection

@section('main')
    <div class="container">
        <h1>Login</h1>
        <form method="POST" action="/login">
            <div class="input-wrapper">
                <label for="email">Email</label>
                <input type="email" value="{{ old('email') }}" name="email" id="email" required>
                @error('email')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="input-wrapper">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
                @error('password')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit">Login</button>
        </form>
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
