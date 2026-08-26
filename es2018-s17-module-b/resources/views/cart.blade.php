@extends('layouts.layout')

@section('content')
    <div class="container">
        <h1>Your Cart</h1>
        @if ($cartDetails->isEmpty())
            <p>Your cart is empty.</p>
        @else
            <div class="cart-wrapper">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cartDetails as $item)
                            <tr>
                                <td class="cart-description">
                                    <div class="cart-item-preview">
                                        <div class="preview-image-stack">
                                            <img class="preview-product-image" src="/storage/product_images/{{ $item['color']->image_filename }}" alt="{{ $item['color']->product->name }}" class="cart-product-image">
                                            <img class="preview-design-image" src="/storage/design_symbols/{{ $item['design']->image_filename }}" alt="{{ $item['design']->name }}" class="cart-design-image">
                                        </div>
                                    </div>
                                    <span class="cart-product-name">{{ $item['color']->product->name }}</span>
                                </td>
                                <td>${{ number_format($item['color']->product->price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="checkout-box">
                    <form>
                        <p class="total-price">Total: ${{ number_format($totalPrice, 2) }}</p>
                        <button type="submit">Proceed to Checkout</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection