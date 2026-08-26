@extends('layouts.layout')

@section('content')
    <div class="container">
        <form class="checkout-form" method="POST" action="/place-order">
            <h1>Checkout</h1>
            @csrf
            <div class="input-wrapper">
                <label for="first-name">First Name</label>
                <input type="text" id="first-name" name="first_name" required>
            </div>
            <div class="input-wrapper">
                <label for="last-name">Last Name</label>
                <input type="text" id="last-name" name="last_name" required>
            </div>
            <div class="input-wrapper">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit">Place pre-order</button>
        </form>
    </div>
@endsection