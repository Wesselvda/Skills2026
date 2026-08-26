@extends('layouts.layout')

@section('content')
    <div class="container">
        <form class="checkout-form" method="POST" action="/login">
            <h1>Admin Login</h1>
            @csrf
            @error('name')
                <div class="error-message">{{ $message }}</div>
            @enderror
            <div class="input-wrapper">
                <label for="name">Username</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
            </div>
            <div class="input-wrapper">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Log in</button>
        </form>
    </div>
@endsection
