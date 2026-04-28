@extends('layouts.app')

@section('content')
    <section class="card">
        <h2>Admin login</h2>

        <form action="{{ route('admin.login.submit') }}" method="POST" class="form">
            @csrf
            <label for="passphrase">Passphrase</label>
            <input id="passphrase" name="passphrase" type="password" required>
            <button type="submit">Login</button>
        </form>
    </section>
@endsection
