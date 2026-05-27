@extends('layouts.guest')

@section('content')
    <section class="login-card">
        <div class="login-card-header">
            <h1>Log In</h1>
            <p>Use your credentials to access the site</p>
        </div>

        @if ($errors->any())
            <div class="form-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="simple-form">
            @csrf
            <div class="form-group">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" value="{{ old('username') }}" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required>
            </div>

            <label class="form-check">
                <input type="checkbox" name="remember" value="1">
                Remember me
            </label>

            <button type="submit" class="btn btn--primary btn--block">Log In</button>
        </form>
    </section>
@endsection