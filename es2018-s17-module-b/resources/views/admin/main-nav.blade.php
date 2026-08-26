@extends('layouts.admin')

@section('title', 'Main Nav')

@section('head')
    <script src="/assets/js/admin-main-nav.js" defer></script>
@endsection

@section('content')
    <div class="container">
        <h1>Main Nav</h1>
        <p>Drag items to reorder the site's main navigation, then save.</p>

        <form method="POST" action="{{ route('admin.main-nav.reorder') }}" id="main-nav-form">
            @csrf
            <ul class="sortable-list" id="main-nav-list">
                @foreach ($navigationItems as $item)
                    <li class="sortable-item" draggable="true" data-id="{{ $item->id }}">
                        <span class="sortable-label">{{ $item->name }}</span>
                        <input type="hidden" name="order[]" value="{{ $item->id }}">
                    </li>
                @endforeach
            </ul>
            <button type="submit" class="save-order-button">Save order</button>
        </form>
    </div>
@endsection
