@extends('layouts.admin')

@section('title')
    @if(isset($build))
        Edit Build
    @else
        Add Build
    @endif
@endsection

@section('main')
    <div class="container">
        <h1>
            @if(isset($build))
                Edit Build
            @else
                Add Build
            @endif
        </h1>
        <form action="/admin/builds/{{ isset($build) ? $build->id : 'add' }}" method="POST" class="big-form" enctype="multipart/form-data">
            @csrf
            <div class="input-wrapper">
                <label for="title">Title</label>
                <input type="text" name="title" id="title" maxlength="40" value="{{ isset($build) ? $build->title : "" }}" required>
                @error('title')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="input-wrapper">
                <label for="intro">Intro</label>
                <textarea id="intro" name="intro" rows="2" required maxlength="200">{{ isset($build) ? $build->intro : "" }}</textarea>
                @error('intro')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="input-wrapper">
                <label for="intro">Description</label>
                <textarea id="description" name="description" rows="6" required maxlength="65535">{{ isset($build) ? $build->description : "" }}</textarea>
                @error('description')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="input-wrapper">
                <label>
                    <input type="checkbox" name="active" {{ (isset($build) && $build->active) ? "checked" : "" }} />
                    Active
                </label>
                @error('active')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="input-wrapper">
                <label for="thumbnail_filename">Square thumbnail</label>
                <input type="file" name="thumbnail_filename" id="thumbnail_filename" accept="image/jpeg,image/png">
                @error('thumbnail_filename')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="input-wrapper">
                <label for="background_filename">Background image</label>
                <input type="file" name="background_filename" id="background_filename" accept="image/jpeg,image/png">
                @error('background_filename')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="input-wrapper">
                <label for="signature_filename">Signature image</label>
                <input type="file" name="signature_filename" id="signature_filename" accept="image/jpeg,image/png">
                @error('signature_filename')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit">
                @if(isset($build))
                    Edit Build
                @else
                    Add Build
                @endif
            </button>
        </form>
        @isset($build)
            <form action="/admin/builds/{{ $build->id }}" method="POST" class="big-form">
                @csrf
                @method('DELETE')
                <button type="submit" class="delete-btn">Delete Build</button>
            </form>
        @endisset
    </div>
@endsection
