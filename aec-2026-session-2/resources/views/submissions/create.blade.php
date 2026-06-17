@extends('layouts.layout')

@section('content')
    <section class="section container-inner">
        <div class="form-panel">
            <p class="small-title">5 credits</p>
            <h1>Create submission</h1>

            @if($user->credits < 5)
                <div class="error-box">You need 5 credits to create a submission. Current balance: {{ $user->credits }}.</div>
            @endif

            <form method="POST" action="{{ route('submissions.store') }}" enctype="multipart/form-data">
                @csrf

                <label class="contact-field">
                    <span>Title</span>
                    <input type="text" name="title" value="{{ old('title') }}" required>
                </label>
                @error('title')
                    <div class="error-message">{{ $message }}</div>
                @enderror

                <label class="contact-field">
                    <span>Category</span>
                    <select name="category_id" required>
                        <option value="">Choose a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) old('category_id') === $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </label>
                @error('category_id')
                    <div class="error-message">{{ $message }}</div>
                @enderror

                <label class="contact-field">
                    <span>Draft PDF</span>
                    <input type="file" name="draft" accept="application/pdf" required>
                </label>
                @error('draft')
                    <div class="error-message">{{ $message }}</div>
                @enderror

                <button class="button" type="submit" @disabled($user->credits < 5)>Create submission</button>
            </form>
        </div>
    </section>
@endsection
