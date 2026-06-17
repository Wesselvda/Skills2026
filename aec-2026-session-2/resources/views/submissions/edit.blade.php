@extends('layouts.layout')

@section('content')
    <section class="section container-inner">
        <div class="form-panel">
            <p class="small-title">3 credits</p>
            <h1>Update submission</h1>
            <p><strong>{{ $submission->title }}</strong></p>

            @if($user->credits < 3)
                <div class="error-box">You need 3 credits to update a submission. Current balance: {{ $user->credits }}.</div>
            @endif

            <form method="POST" action="{{ route('submissions.update', $submission) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <label class="contact-field">
                    <span>Category</span>
                    <select name="category_id" required>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) old('category_id', $submission->category_id) === $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </label>
                @error('category_id')
                    <div class="error-message">{{ $message }}</div>
                @enderror

                <label class="contact-field">
                    <span>Replacement PDF</span>
                    <input type="file" name="draft" accept="application/pdf" required>
                </label>
                @error('draft')
                    <div class="error-message">{{ $message }}</div>
                @enderror

                <button class="button" type="submit" @disabled($user->credits < 3)>Update submission</button>
            </form>
        </div>
    </section>
@endsection
