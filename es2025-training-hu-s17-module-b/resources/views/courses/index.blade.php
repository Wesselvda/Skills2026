@extends('layouts.admin')

@section('content')
    <div class="page-wrap">
        @if (session('status'))
            <div class="flash-message">
                {{ session('status') }}
            </div>
        @endif

        <section class="toolbar-row">
            <h1>Course management</h1>
            <a class="btn btn-primary" href="{{ route('courses.create') }}">Add new course</a>
        </section>

        <div class="table-shell table-shell-compact">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Difficulty</th>
                        <th>Chapters</th>
                        <th>Credits</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($courses as $course)
                        <tr>
                            <td>{{ $course->title }}</td>
                            <td>{{ $course->difficulty_level ?? 'n/a' }}</td>
                            <td>{{ number_format((int) $course->chapters_count) }} chapters</td>
                            <td>{{ number_format((int) ($course->total_credits ?? 0)) }} credits</td>
                            <td class="data-table__actions">
                                <div class="action-stack">
                                    <a class="action-link" href="{{ route('courses.edit', $course) }}">Update</a>
                                    <a class="action-link action-link-danger" href="{{ route('courses.index', ['delete' => $course->id]) }}">Delete</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">No courses found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($deleteCourse)
        <div class="modal-overlay">
            <div class="modal-card">
                <h2>Are you sure?</h2>
                <p>This action cannot be undone.</p>

                <div class="modal-actions">
                    <a class="btn btn-outline" href="{{ route('courses.index') }}">Cancel</a>

                    <form method="POST" action="{{ route('courses.destroy', $deleteCourse) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection