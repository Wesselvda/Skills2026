@extends('layouts.layout')

@section('content')
    <div class="section container-inner">
        <div class="auth-wrapper">
            <h1>Login</h1>
            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <label class="contact-field">
                    <span>Username</span>
                    <input type="text" name="username" autocomplete="username" required>
                </label>
                @error('username')
                    <div class="error-message">{{ $message }}</div>
                @enderror

                <label class="contact-field">
                    <span>Password</span>
                    <input type="password" name="password" autocomplete="current-password" required>
                </label>
                @error('password')
                    <div class="error-message">{{ $message }}</div>
                @enderror

                <button class="button" type="submit">Login</button>
            </form>
        </div>
    </div>
@endsection
