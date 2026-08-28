@extends('layouts.admin')

@section('title')
    Builds
@endsection

@section('main')
    <div class="container">
        <h1>Builds</h1>
        <div class="filters">
            <form action="/admin/builds" method="GET">
                <div class="input-wrapper">
                    <label for="search">Search</label>
                    <input value="{{ $search }}" type="search" name="search" id="search">
                </div>
                <div class="input-wrapper">
                    <label for="status">Status</label>
                    <select name="status" id="status">
                        <option>All</option>
                        <option {{ $status === "active" ? "selected" : "" }} value="active">Active</option>
                        <option {{ $status === "inactive" ? "selected" : "" }} value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="input-wrapper">
                    <label for="sort">Sort</label>
                    <select name="sort" id="sort">
                        <option {{ $sort === "title_asc" ? "selected" : "" }} value="title_asc">Title A > Z</option>
                        <option {{ $sort === "title_desc" ? "selected" : "" }} value="title_desc">Title Z > A</option>
                        <option {{ $sort === "creation_desc" ? "selected" : "" }} value="creation_desc">Newest</option>
                        <option {{ $sort === "creation_asc" ? "selected" : "" }} value="creation_asc">Oldest</option>
                    </select>
                </div>
                <button type="submit">Search</button>
            </form>
            <div class="add-build">
                <a class="btn" href="/admin/builds/add">Add build</a>
            </div>
        </div>
        @if(count($builds) > 0)
            <div class="builds-list">
                @foreach ($builds as $build)
                    <div class="{{ $build->active ? "" : "inactive"}} build">
                        <img src="/storage/{{ ltrim($build->thumbnail_filename, '/') }}" alt="Thumbnail">
                        <h2>{{ $build->title }}</h2>
                        <a class="btn" href="/admin/builds/{{ $build->id }}">Edit</a>
                    </div>
                @endforeach
            </div>
            @if($builds->hasPages())
                {{ $builds->links() }}
            @endif
        @else
            <p>No builds found</p>
        @endif
    </div>
@endsection
