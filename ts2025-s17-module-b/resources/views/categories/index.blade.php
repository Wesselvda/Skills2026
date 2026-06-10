@extends('layouts.layout')

@section('content')
    <div class="container">
        <div class="page-header">
            <div>
                <h1>Categories</h1>
                <p>Manage your bicycle categories.</p>
            </div>
            <a class="btn" href="{{ route('categories.create') }}">Add category</a>
        </div>

        @if (session('success'))
            <div class="message">{{ session('success') }}</div>
        @endif

        @error('category')
            <div class="error box-error">{{ $message }}</div>
        @enderror

        @if ($categories->count())
            <div class="table-box">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Bicycles</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $category)
                            <tr>
                                <td>
                                    <a class="table-link" href="{{ route('bicycles.index', $category) }}">{{ $category->name }}</a>
                                </td>
                                <td>{{ $category->bicycles_count }}</td>
                                <td class="actions">
                                    <a class="btn small" href="{{ route('categories.edit', $category) }}">Edit</a>
                                    <form method="POST" action="{{ route('categories.destroy', $category) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="danger small" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-box">
                <p>No categories yet.</p>
                <a class="btn" href="{{ route('categories.create') }}">Create your first category</a>
            </div>
        @endif
    </div>
@endsection
