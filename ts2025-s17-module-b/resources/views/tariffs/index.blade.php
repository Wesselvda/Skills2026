@extends('layouts.layout')

@section('content')
    <div class="container">
        <div class="page-header">
            <div>
                <h1>Tariffs</h1>
                <p>{{ $category->name }}</p>
            </div>
            <div class="actions">
                <a class="btn secondary" href="{{ route('categories.index') }}">Back</a>
                <a class="btn" href="{{ route('tariffs.create', $category) }}">Add tariff</a>
            </div>
        </div>

        @if (session('success'))
            <div class="message">{{ session('success') }}</div>
        @endif

        @if ($tariffs->count())
            <div class="table-box">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tariffs as $tariff)
                            <tr class="{{ $tariff->archived ? 'archived-row' : '' }}">
                                <td>{{ $tariff->name }}</td>
                                <td>{{ $tariff->type }}</td>
                                <td>
                                    {{ $tariff->basePrice }} tenge / minute
                                    @if ($tariff->type === 'DYNAMIC')
                                        <br>
                                        <span>{{ $tariff->minPrice }} - {{ $tariff->maxPrice }} tenge</span>
                                    @endif
                                </td>
                                <td>{{ $tariff->archived ? 'Archived' : 'Active' }}</td>
                                <td class="actions">
                                    <a class="btn small" href="{{ route('tariffs.edit', $tariff) }}">Edit</a>
                                    @if (! $tariff->archived)
                                        <form method="POST" action="{{ route('tariffs.archive', $tariff) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="secondary small" type="submit">Archive</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-box">
                <p>No tariffs yet.</p>
                <a class="btn" href="{{ route('tariffs.create', $category) }}">Create your first tariff</a>
            </div>
        @endif
    </div>
@endsection
