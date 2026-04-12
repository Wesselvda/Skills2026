@extends('layouts.app')

@section('content')
    <section class="card">
        <h2>Edit product</h2>

        <form method="POST" enctype="multipart/form-data" action="{{ route('products.update', $product) }}" class="form">
            @csrf
            @method('PUT')

            <label>Company</label>
            <select name="company_id" required>
                <option value="">Select company</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}" @selected((string) old('company_id', $product->company_id) === (string) $company->id)>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>

            <label>GTIN</label>
            <input name="gtin" value="{{ old('gtin', $product->gtin) }}" required>

            <label>Name (English)</label>
            <input name="name_en" value="{{ old('name_en', $product->name_en) }}" required>

            <label>Name (French)</label>
            <input name="name_fr" value="{{ old('name_fr', $product->name_fr) }}" required>

            <label>Description (English)</label>
            <textarea name="description_en" rows="4" required>{{ old('description_en', $product->description_en) }}</textarea>

            <label>Description (French)</label>
            <textarea name="description_fr" rows="4" required>{{ old('description_fr', $product->description_fr) }}</textarea>

            <label>Brand name</label>
            <input name="brand" value="{{ old('brand', $product->brand) }}" required>

            <label>Country of origin</label>
            <input name="country_of_origin" value="{{ old('country_of_origin', $product->country_of_origin) }}" required>

            <label>Gross weight (with packaging)</label>
            <input name="gross_weight" type="number" step="0.01" value="{{ old('gross_weight', $product->gross_weight) }}" required>

            <label>Net content weight</label>
            <input name="net_weight" type="number" step="0.01" value="{{ old('net_weight', $product->net_weight) }}" required>

            <label>Weight unit</label>
            <input name="weight_unit" value="{{ old('weight_unit', $product->weight_unit) }}" required>

            <label>Image</label>
            <input type="file" name="image" accept="image/*">

            <button type="submit">Save changes</button>
        </form>
    </section>

    <section class="card">
        <h3>Product state</h3>

        <p><strong>Hidden:</strong> {{ $product->is_hidden ? 'Yes' : 'No' }}</p>
        <p><strong>Current image:</strong></p>

        <img src="{{ $product->image_path ? asset('storage/' . $product->image_path) : asset('images/placeholder.jpg') }}" alt="Product image" class="preview-image">

        <div class="actions">
            @if (! $product->is_hidden)
                <form method="POST" action="{{ route('products.hide', $product) }}" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit">Hide product</button>
                </form>
            @endif

            @if ($product->image_path)
                <form method="POST" action="{{ route('products.image.remove', $product) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Remove image</button>
                </form>
            @endif

            @if ($product->is_hidden)
                <form method="POST" action="{{ route('products.destroy', $product) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Delete hidden product</button>
                </form>
            @endif
        </div>
    </section>
@endsection
