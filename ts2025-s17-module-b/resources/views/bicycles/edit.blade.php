@extends('layouts.layout')

@section('content')
    <div class="container">
        <div class="page-header">
            <div>
                <h1>Edit bicycle</h1>
                <p>{{ $bicycle->category->name }}</p>
            </div>
            <a class="btn secondary" href="{{ route('bicycles.index', $bicycle->category) }}">Back</a>
        </div>

        <form method="POST" class="box-form" action="{{ route('bicycles.update', $bicycle) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @if ($bicycle->pathToImage)
                <img class="bike-preview" src="{{ asset('storage/bicycle-images/' . $bicycle->pathToImage) }}" alt="{{ $bicycle->name }}">
            @endif

            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="{{ old('name', $bicycle->name) }}" maxlength="100" required>
                @error('name')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description">{{ old('description', $bicycle->description) }}</textarea>
                @error('description')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="wear">Wear:</label>
                <input type="number" id="wear" name="wear" value="{{ old('wear', $bicycle->wear) }}" min="0" max="100" required>
                @error('wear')
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

            <button type="submit">Update bicycle</button>
        </form>
    </div>
@endsection
