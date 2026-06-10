@extends('layouts.layout')

@section('content')
    <div class="container">
        <div class="page-header">
            <div>
                <h1>Edit tariff</h1>
                <p>{{ $tariff->category->name }}</p>
            </div>
            <a class="btn secondary" href="{{ route('tariffs.index', $tariff->category) }}">Back</a>
        </div>

        <form method="POST" class="box-form" action="{{ route('tariffs.update', $tariff) }}">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="{{ old('name', $tariff->name) }}" maxlength="100" required>
                @error('name')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="type">Type:</label>
                <select id="type" name="type" required>
                    <option value="STATIC" @selected(old('type', $tariff->type) === 'STATIC')>Fixed</option>
                    <option value="DYNAMIC" @selected(old('type', $tariff->type) === 'DYNAMIC')>Dynamic</option>
                </select>
                @error('type')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="basePrice">Base price:</label>
                <input type="number" id="basePrice" name="basePrice" value="{{ old('basePrice', $tariff->basePrice) }}" min="0" required>
                @error('basePrice')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="minPrice">Minimum price:</label>
                <input type="number" id="minPrice" name="minPrice" value="{{ old('minPrice', $tariff->minPrice) }}" min="0">
                @error('minPrice')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="maxPrice">Maximum price:</label>
                <input type="number" id="maxPrice" name="maxPrice" value="{{ old('maxPrice', $tariff->maxPrice) }}" min="0">
                @error('maxPrice')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit">Update tariff</button>
        </form>
    </div>
@endsection
