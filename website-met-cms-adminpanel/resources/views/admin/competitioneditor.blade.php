@extends('layouts.admin')

@section('title')
    @if(isset($competition))
        Edit Competition
    @else
        Add Competition
    @endif
@endsection

@section('main')
    <div class="container">
        <h1>
            @if(isset($competition))
                Edit Competition
            @else
                Add Competition
            @endif
        </h1>

        @error('limit')
            <p class="error">{{ $message }}</p>
        @enderror

        <form action="/admin/competitions/{{ isset($competition) ? $competition->id : 'add' }}" method="POST" class="big-form" enctype="multipart/form-data">
            @csrf
            <div class="input-wrapper">
                <label for="title">Title</label>
                <input type="text" name="title" id="title" maxlength="40" value="{{ old('title', isset($competition) ? $competition->title : '') }}" required>
                @error('title')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="input-wrapper">
                <label for="text">Text</label>
                <textarea id="text" name="text" rows="4" maxlength="150" required>{{ old('text', isset($competition) ? $competition->text : '') }}</textarea>
                @error('text')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="input-wrapper">
                <label for="position">Position</label>
                <select name="position" id="position" required>
                    @for($position = 1; $position <= 3; $position++)
                        <option value="{{ $position }}" {{ (int) old('position', isset($competition) ? $competition->position : 1) === $position ? 'selected' : '' }}>{{ $position }}</option>
                    @endfor
                </select>
                @error('position')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="input-wrapper">
                <label>
                    <input type="checkbox" name="active" {{ old('active', isset($competition) && $competition->active ? 'on' : null) === 'on' ? 'checked' : '' }}>
                    Active
                </label>
                @error('active')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="input-wrapper">
                <label for="image_filename">Image</label>
                <input type="file" name="image_filename" id="image_filename" accept="image/jpeg,image/png" {{ isset($competition) ? '' : 'required' }}>
                @error('image_filename')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit">
                @if(isset($competition))
                    Edit Competition
                @else
                    Add Competition
                @endif
            </button>
        </form>

        @isset($competition)
            <form action="/admin/competitions/{{ $competition->id }}" method="POST" class="big-form">
                @csrf
                @method('DELETE')
                <button type="submit" class="delete-btn">Delete Competition</button>
            </form>
        @endisset
    </div>
@endsection
