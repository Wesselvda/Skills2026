@extends('layouts.layout')

@section('content')
    <div class="centered-container">
        <h1 class="text-center">Login</h1>
        <form method="POST" class="box-form small" action="{{ route('login') }}">
            @csrf
            {{-- Email or phone --}}
            <div class="form-group">
                <label for="email_or_phone">Email or Phone:</label>
                <input type="text" id="email_or_phone" name="email_or_phone" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            @error('email_or_phone')
                <div class="error">{{ $message }}</div>
            @enderror
            <button type="submit">Login</button>
        </form>
    </div>
@endsection