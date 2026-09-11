@extends('layouts.layout')

@section('body')
    <form class="centered-form" method="POST" action="/login">
        <h1>Login</h1>

        <div class="input-wrapper">
            <label for="phoneoremail">Email or phone number:</label>
            <input type="text" name="phoneoremail" required id="phoneoremail">
            @error('phoneoremail')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="input-wrapper">
            <label for="password">Password:</label>
            <input type="password" name="password" required id="password">
            @error('password')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit">Login</button>
    </form>
@endsection
