@extends('layouts.admin')

@section('title')
    Edit page
@endsection

@section('main')
    <div class="container">
        <h1>Edit page</h1>
        <form action="/admin/pages/{{ $page->id }}" method="POST" class="big-form" enctype="multipart/form-data">
            @csrf
            <div class="input-wrapper">
                <label for="title">Title</label>
                <input type="text" name="title" id="title" maxlength="40" value="{{ $page->title }}" required>
                @error('title')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="input-wrapper">
                <label for="intro">Intro</label>
                <textarea id="intro" name="intro" rows="2" required maxlength="200">{{$page->intro}}</textarea>
                @error('intro')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="input-wrapper">
                <label for="intro">Description</label>
                <textarea id="description" name="description" rows="6" required maxlength="65535">{{$page->description}}</textarea>
                @error('description')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="input-wrapper">
                <label>
                    <input type="checkbox" name="show_in_navigation" {{ $page->show_in_navigation ? "checked" : "" }} />
                    Show in navigation
                </label>
                @error('show_in_navigation')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="input-wrapper">
                <label for="image_filename">Landscape image</label>
                <input type="file" name="image_filename" id="image_filename" accept="image/jpeg,image/png">
                @error('image_filename')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit">Save page</button>
        </form>
    </div>
@endsection
