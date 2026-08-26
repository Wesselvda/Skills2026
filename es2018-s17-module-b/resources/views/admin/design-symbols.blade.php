@extends('layouts.admin')

@section('title', 'Design Symbols')

@section('head')
    <script src="/assets/js/admin-design-symbols.js" defer></script>
@endsection

@section('content')
    <div class="container">
        <h1>Design Symbols</h1>

        @error('design')
            <div class="error-message">
                {{ $message }}
                <div>Make sure it's less than 5mb</div>
            </div>
        @enderror

        <form method="POST" action="{{ route('admin.design-symbols.store') }}" enctype="multipart/form-data" id="design-upload-form">
            @csrf
            <div class="dropzone" id="design-dropzone">
                <p>Drag a PNG file here, or</p>
                <label for="design-file-input" class="btn">Choose file</label>
                <input type="file" id="design-file-input" name="design" accept="image/png" class="hidden" required>
            </div>
        </form>

        <ul class="design-symbol-list">
            @foreach ($designs as $design)
                <li class="design-symbol-row @if (!$design->is_active) design-symbol-row-inactive @endif">
                    <img src="/storage/design_symbols/{{ $design->image_filename }}" alt="{{ $design->name }}">
                    <span class="design-symbol-name">{{ $design->name }}</span>
                    <span class="design-symbol-status">{{ $design->is_active ? 'Active' : 'Inactive' }}</span>
                    <form method="POST" action="{{ route('admin.design-symbols.toggle-active', $design) }}">
                        @csrf
                        <button type="submit">{{ $design->is_active ? 'Deactivate' : 'Activate' }}</button>
                    </form>
                    <form method="POST" action="{{ route('admin.design-symbols.destroy', $design) }}" onsubmit="return confirm('Delete this design symbol? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" @if ($design->is_active) disabled title="Deactivate before deleting" @endif>Delete</button>
                    </form>
                </li>
            @endforeach
        </ul>
    </div>
@endsection
