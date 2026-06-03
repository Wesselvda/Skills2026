@extends('layouts.layout')

@section('content')
    <section class="text-centered">
        <h1>Log in to your account</h1>
    </section>
    <section>
        <form class="investor-form" method="POST" action="/login">
            @csrf
            <fieldset class="contact-fieldset">
                <legend style="display: none;">Login fields</legend>
                <div class="input-row">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" required placeholder="name@example.com">
                </div>
            </fieldset>

            <div class="input-row">
                <button type="submit">Login</button>
            </div>
        </form>
    </section>
@endsection
