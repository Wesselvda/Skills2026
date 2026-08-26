@extends('layouts.layout')

@section('head')
    <script src="/assets/js/homepage.js" defer></script>
@endsection

@section('content')
    <div class="container">
        <h1>Product customizer</h1>
        <form class="design-selection-form" method="POST" action="/add-to-cart">
            @csrf
            <fieldset class="design-symbols-fieldset">
                <legend>Choose a design!</legend>
                <div class="design-symbols" id="design-symbols">
                    <p class="loading-message">Loading designs...</p>
                </div>
            </fieldset>
            <fieldset class="products-fieldset">
                <legend>Choose a product!</legend>
                <div class="products" id="products">
                    <p class="loading-message">Loading products...</p>
                </div>
            </fieldset>
            <fieldset class="color-selection-fieldset">
                <legend>Choose a colour!</legend>
                <div class="color-selection">
                    <div class="preview" id="preview">
                        <div class="preview-image-stack">
                            <img src="" alt="" class="preview-product-image" id="preview-product-image">
                            <img src="" alt="" class="preview-design-image" id="preview-design-image">
                        </div>
                    </div>
                    <div class="color-options-wrapper">
                        <div class="color-options" id="color-options"></div>
                        <input type="submit" value="Add to Cart" class="add-to-cart-button">
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
@endsection
