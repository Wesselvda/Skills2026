@extends('layouts.layout')

@section('content')
    <div class="container">
        <div class="page-header">
            <div>
                <h1>Edit category</h1>
                <p>Change the category name.</p>
            </div>
            <a class="btn secondary" href="{{ route('categories.index') }}">Back</a>
        </div>

        <form method="POST" class="box-form" action="{{ route('categories.update', $category) }}">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}" required>
                @error('name')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit">Update category</button>
        </form>
    </div>
@endsection
