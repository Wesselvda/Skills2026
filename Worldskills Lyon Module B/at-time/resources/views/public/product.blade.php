<!DOCTYPE html>
<html lang="{{ $language }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $language === 'fr' ? $product->name_fr : $product->name_en }}</title>
    <link rel="stylesheet" href="{{ asset('scss/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body>
    <main class="page public-page">
        <section class="card">
            <div class="actions">
                <a href="{{ route('public.product', ['gtin' => $product->gtin, 'lang' => 'en']) }}">English</a>
                <a href="{{ route('public.product', ['gtin' => $product->gtin, 'lang' => 'fr']) }}">Francais</a>
            </div>

            <h1>{{ $language === 'fr' ? $product->name_fr : $product->name_en }}</h1>
            <p><strong>Company:</strong> {{ $product->company->name }}</p>
            <p><strong>GTIN:</strong> {{ $product->gtin }}</p>

            <img src="{{ $product->image_path ? asset('storage/' . $product->image_path) : asset('images/placeholder.jpg') }}" alt="Product image" class="public-image">

            <p>{{ $language === 'fr' ? $product->description_fr : $product->description_en }}</p>
            <p><strong>Gross:</strong> {{ $product->gross_weight }} {{ $product->weight_unit }}</p>
            <p><strong>Net:</strong> {{ $product->net_weight }} {{ $product->weight_unit }}</p>
        </section>
    </main>
</body>
</html>
