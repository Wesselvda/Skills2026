@extends('layouts.app')

@section('content')
    <section class="container">
        <h1>Categories</h1>
        <a class="btn" href="{{ route('categories.add') }}">Add Category</a>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Adverts Count</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categories as $category)
                    <tr>
                        <td>{{ $category->id }}</td>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->adverts()->where('status', 'published')->count() }}</td>
                        <td>
                            <a href="{{ route('categories.edit', $category->id) }}">Edit</a>
                            @if ($category->adverts()->count() === 0)
                                <form action="{{ route('categories.destroy', $category->id) }}" method="POST" class="delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endsection
