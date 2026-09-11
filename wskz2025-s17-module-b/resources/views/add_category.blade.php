@extends('layouts.app')

@section('content')
    <section class="container">
        <h1>Add category</h1>
        
        <form method="POST" action="{{ route('categories.add') }}">
            @csrf
            @method('POST')
            <div class="input-wrapper">
                <label for="name">Name:</label>
                <input type="text" name="name" id="name" required>
            </div>

            <button type="submit">Add</button>
        </form>
    </section>
@endsection
