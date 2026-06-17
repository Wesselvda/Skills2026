@extends('layouts.layout')

@section('content')
    <div class="section container-inner">
        <div class="auth-wrapper">
            <h1>Register</h1>
            <form method="POST" action="{{ route('register.post') }}">
                @csrf
                <label class="contact-field">
                    <span>Username</span>
                    <input type="text" name="username" autocomplete="username" required>
                </label>
                @error('username')
                    <div class="error-message">{{ $message }}</div>
                @enderror

                <label class="contact-field">
                    <span>Full Name</span>
                    <input type="text" name="full_name" autocomplete="name" required>
                </label>
                @error('full_name')
                    <div class="error-message">{{ $message }}</div>
                @enderror

                <label class="contact-field">
                    <span>Password</span>
                    <input type="password" name="password" autocomplete="current-password" required>
                </label>
                @error('password')
                    <div class="error-message">{{ $message }}</div>
                @enderror

                <label class="contact-field">
                    <span>Confirm Password</span>
                    <input type="password" name="password_confirmation" autocomplete="current-password" required>
                </label>
                @error('password_confirmation')
                    <div class="error-message">{{ $message }}</div>
                @enderror

                <button class="button" type="submit">Register</button>
            </form>
        </div>
    </div>
@endsection
