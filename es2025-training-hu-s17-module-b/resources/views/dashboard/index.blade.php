@extends('layouts.admin')

@section('content')
    <div class="page-wrap">
        @if (session('status'))
            <div class="flash-message">
                {{ session('status') }}
            </div>
        @endif

        <section class="page-head">
            <div>
                <h1>Dashboard</h1>
            </div>
        </section>

        <section class="stat-grid">
            @foreach ($stats as $stat)
                <article class="stat-card">
                    <p class="stat-card__label">{{ $stat['label'] }}</p>
                    <strong class="stat-card__value">{{ $stat['value'] }}</strong>

                    @if ($stat['change'] !== null)
                        <span class="stat-card__delta">+ {{ number_format($stat['change']) }} in the last 7 days</span>
                    @endif
                </article>
            @endforeach
        </section>

        <section class="content-grid">
            <div class="table-shell">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Activity</th>
                            <th>Mentor/Learner</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activities as $activity)
                            <tr>
                                <td>{{ $activity['activity'] }}</td>
                                <td>{{ $activity['name'] }}</td>
                                <td>{{ $activity['time']->diffForHumans(short: true) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="empty-state">No recent activity found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <aside class="quick-links">
                <a class="quick-link" href="{{ route('learners.index') }}">Learner management »</a>
                <a class="quick-link" href="{{ route('mentors.index') }}">Mentor management »</a>
                <a class="quick-link" href="{{ route('courses.index') }}">Course management »</a>
            </aside>
        </section>
    </div>
@endsection