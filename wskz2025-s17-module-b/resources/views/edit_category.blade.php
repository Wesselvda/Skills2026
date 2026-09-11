@extends('layouts.app')

@section('content')
    <section class="container">
        <h1>Edit category</h1>
        
        <form method="POST" action="{{ route('categories.update', $category->id) }}">
            @csrf
            @method('PUT')
            <div class="input-wrapper">
                <label for="name">Name:</label>
                <input type="text" name="name" id="name" value="{{ $category->name }}" required>
            </div>

            <button type="submit">Update</button>
        </form>
    </section>
@endsection
