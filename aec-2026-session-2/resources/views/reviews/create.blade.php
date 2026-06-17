@extends('layouts.layout')

@section('content')
    <section class="section container-inner">
        <div class="page-header">
            <div>
                <h1>{{ $submission->title }}</h1>
            </div>
            <a class="button" href="{{ route('reviews.index') }}">Back</a>
        </div>

        <div class="review-layout">
            <div class="simple-panel">
                <dl class="meta-list">
                    <div>
                        <dt>Author</dt>
                        <dd>{{ $submission->user->full_name }}</dd>
                    </div>
                    <div>
                        <dt>Category</dt>
                        <dd>{{ $submission->category->name }}</dd>
                    </div>
                    <div>
                        <dt>Updated</dt>
                        <dd>{{ $submission->updated_at->format('d-m-Y H:i') }}</dd>
                    </div>
                </dl>

                <iframe class="pdf-frame" src="{{ route('reviews.file', $submission) }}" title="Submitted draft"></iframe>
            </div>

            <div class="form-panel">
                <p class="small-title">Earn 2 credits</p>
                <h2>Create review</h2>

                <form method="POST" action="{{ route('reviews.store', $submission) }}">
                    @csrf

                    <fieldset class="radio-group">
                        <legend>Verdict</legend>
                        <label>
                            <input type="radio" name="verdict" value="positive" @checked(old('verdict') === 'positive') required>
                            Positive
                        </label>
                        <label>
                            <input type="radio" name="verdict" value="negative" @checked(old('verdict') === 'negative') required>
                            Negative
                        </label>
                    </fieldset>
                    @error('verdict')
                        <div class="error-message">{{ $message }}</div>
                    @enderror

                    <label class="contact-field">
                        <span>Comment</span>
                        <textarea name="comment" rows="7" minlength="20" required>{{ old('comment') }}</textarea>
                    </label>
                    @error('comment')
                        <div class="error-message">{{ $message }}</div>
                    @enderror

                    <button class="button" type="submit">Submit review</button>
                </form>
            </div>
        </div>
    </section>
@endsection
