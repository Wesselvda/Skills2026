@extends('layouts.layout')

@section('content')
    <div class="container">
        <div class="page-header">
            <div>
                <h1>Add bicycle</h1>
                <p>{{ $category->name }}</p>
            </div>
            <a class="btn secondary" href="{{ route('bicycles.index', $category) }}">Back</a>
        </div>

        <form method="POST" class="box-form" action="{{ route('bicycles.store', $category) }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" maxlength="100" required>
                @error('name')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description">{{ old('description') }}</textarea>
                @error('description')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="wear">Wear:</label>
                <input type="number" id="wear" name="wear" value="{{ old('wear', 0) }}" min="0" max="100">
                @error('wear')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" min="1" max="20" required>
                @error('quantity')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="image">Image:</label>
                <input type="file" id="image" name="image" accept="image/*">
                @error('image')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit">Save bicycle</button>
        </form>
    </div>
@endsection
