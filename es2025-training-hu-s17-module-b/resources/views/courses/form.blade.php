@extends('layouts.admin')

@section('content')
    <div class="form-shell">
        <div class="course-form">
            <div class="course-form__header">
                <h1>{{ $pageTitle }}</h1>
                <a class="action-link course-form__back" href="{{ route('courses.index') }}">Back</a>
            </div>

            @if ($errors->any())
                <div class="form-error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ $isEdit ? route('courses.update', $course) : route('courses.store') }}" class="course-form__stack">
                @csrf
                @if ($isEdit)
                    @method('PATCH')
                @endif

                <div class="form-group">
                    <label for="title">Title</label>
                    <input id="title" name="title" type="text" value="{{ old('title', $course->title) }}" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4">{{ old('description', $course->description) }}</textarea>
                </div>

                <div class="form-group">
                    <label for="difficulty_level">Difficulty</label>
                    <select id="difficulty_level" name="difficulty_level" required>
                        <option value="beginner" @selected(old('difficulty_level', $course->difficulty_level) === 'beginner')>Beginner</option>
                        <option value="intermediate" @selected(old('difficulty_level', $course->difficulty_level) === 'intermediate')>Intermediate</option>
                        <option value="advanced" @selected(old('difficulty_level', $course->difficulty_level) === 'advanced')>Advanced</option>
                    </select>
                </div>

                <div class="course-form__actions">
                    <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update' : 'Create' }}</button>

                    @if ($isEdit)
                        <button type="submit" name="action" value="archive" class="btn btn-danger">Deactivate</button>
                        <button type="button" class="btn btn-muted" disabled>Manage chapters</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection