@extends('layouts.layout')

@section('content')
    <div class="container">
        <div class="page-header">
            <div>
                <h1>Bicycles</h1>
                <p>{{ $category->name }}</p>
            </div>
            <div class="actions">
                <a class="btn secondary" href="{{ route('categories.index') }}">Back</a>
                <a class="btn" href="{{ route('bicycles.create', $category) }}">Add bicycle</a>
            </div>
        </div>

        @if (session('success'))
            <div class="message">{{ session('success') }}</div>
        @endif

        @error('bicycle')
            <div class="error box-error">{{ $message }}</div>
        @enderror

        @if ($bicycles->count())
            <div class="bike-map">
                <div class="bike-map-inner">
                    @foreach ($bicycles as $bicycle)
                        <a
                            class="map-marker"
                            href="{{ route('bicycles.edit', $bicycle) }}"
                            title="{{ $bicycle->name }}"
                            style="left: {{ ($bicycle->locationX / 5000) * 100 }}%; top: {{ ($bicycle->locationY / 5000) * 100 }}%;"
                        >
                            @if ($bicycle->pathToImage)
                                <img src="{{ asset('storage/bicycle-images/' . $bicycle->pathToImage) }}" alt="{{ $bicycle->name }}">
                            @else
                                <span>{{ strtoupper(substr($bicycle->name, 0, 1)) }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="table-box">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Wear</th>
                            <th>Map</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bicycles as $bicycle)
                            <tr>
                                <td>
                                    @if ($bicycle->pathToImage)
                                        <img class="bike-image" src="{{ asset('storage/bicycle-images/' . $bicycle->pathToImage) }}" alt="{{ $bicycle->name }}">
                                    @else
                                        No image
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $bicycle->name }}</strong><br>
                                    <span>{{ $bicycle->slug }}</span>
                                </td>
                                <td>{{ $bicycle->status }}</td>
                                <td>{{ $bicycle->wear }}%</td>
                                <td>{{ $bicycle->locationX }}, {{ $bicycle->locationY }}</td>
                                <td class="actions">
                                    <a class="btn small" href="{{ route('bicycles.edit', $bicycle) }}">Edit</a>

                                    <form method="POST" action="{{ route('bicycles.status', $bicycle) }}">
                                        @csrf
                                        @method('PATCH')
                                        @if ($bicycle->status === 'available')
                                            <input type="hidden" name="status" value="unavailable">
                                            <button class="secondary small" type="submit">Unavailable</button>
                                        @else
                                            <input type="hidden" name="status" value="available">
                                            <button class="small" type="submit">Available</button>
                                        @endif
                                    </form>

                                    <form method="POST" action="{{ route('bicycles.destroy', $bicycle) }}">
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
                <p>No bicycles yet.</p>
                <a class="btn" href="{{ route('bicycles.create', $category) }}">Create your first bicycle</a>
            </div>
        @endif
    </div>
@endsection
