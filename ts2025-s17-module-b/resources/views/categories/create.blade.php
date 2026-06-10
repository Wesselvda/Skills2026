@extends('layouts.layout')

@section('content')
    <div class="container">
        <div class="page-header">
            <div>
                <h1>Add category</h1>
                <p>Create a new bicycle category.</p>
            </div>
            <a class="btn secondary" href="{{ route('categories.index') }}">Back</a>
        </div>

        <form method="POST" class="box-form" action="{{ route('categories.store') }}">
            @csrf
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                @error('name')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit">Save category</button>
        </form>
    </div>
@endsection
