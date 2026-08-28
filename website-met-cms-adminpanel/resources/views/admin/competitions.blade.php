@extends('layouts.admin')

@section('title')
    Competitions
@endsection

@section('main')
    <div class="container">
        <h1>Competitions</h1>

        @if(count($competitions) < 10)
            <p><a class="btn" href="/admin/competitions/add">Add competition</a></p>
        @else
            <p>A maximum of 10 competitions has been reached.</p>
        @endif

        @if(count($competitions) > 0)
            <div class="competitions-list">
                @foreach ($competitions as $competition)
                    <div class="{{ $competition->active ? '' : 'inactive' }} competition">
                        <img src="/storage/{{ ltrim($competition->image_filename, '/') }}" alt="Competition image">
                        <div>
                            <h2>{{ $competition->title }}</h2>
                            <p>Position: {{ $competition->position }}</p>
                            <a class="btn" href="/admin/competitions/{{ $competition->id }}">Edit</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p>No competitions found</p>
        @endif
    </div>
@endsection
