@extends('layouts.admin')

@section('content')
    <div class="page-wrap">
        @if (session('status'))
            <div class="flash-message">
                {{ session('status') }}
            </div>
        @endif

        <section class="toolbar-row">
            <h1>Learner management</h1>
        </section>

        <form method="GET" action="{{ route('learners.index') }}" class="search-bar">
            <div class="search-field search-field-wide">
                <label class="field-label" for="search">Search</label>
                <input id="search" name="search" type="text" value="{{ $search }}" class="search-input" placeholder="Search">
            </div>

            <div class="search-field">
                <label class="field-label" for="status">Status</label>
                <select id="status" name="status" class="search-select">
                    <option value="all" @selected($status === 'all')>All learners</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="suspended" @selected($status === 'suspended')>Suspended</option>
                </select>
            </div>

            <div class="page-actions">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a class="btn btn-outline" href="{{ route('learners.index') }}">Clear filters</a>
            </div>
        </form>

        <div class="table-shell table-shell-compact">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Completed chapters</th>
                        <th>Earned credits</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($learners as $learner)
                        @php
                            $learnerName = trim(($learner->first_name ?? '') . ' ' . ($learner->last_name ?? '')) ?: $learner->name;
                        @endphp
                        <tr>
                            <td>{{ $learner->first_name ?? explode(' ', $learnerName, 2)[0] }}</td>
                            <td>{{ $learner->last_name ?? (explode(' ', $learnerName, 2)[1] ?? '') }}</td>
                            <td>{{ $learner->email }}</td>
                            <td>{{ number_format((int) ($learner->completed_chapters_total ?? 0)) }} chapters</td>
                            <td>{{ number_format((int) $learner->credit_balance) }} credits</td>
                            <td class="data-table__actions">
                                <div class="action-stack">
                                    @if ($learner->status === 'active')
                                        <form method="POST" action="{{ route('learners.suspend', $learner) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-small btn-outline">Suspend</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('learners.enable', $learner) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-small btn-outline">Unsuspend</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">No learners found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($learners->hasPages())
            <nav class="pagination" aria-label="Learner pagination">
                <a class="page-link {{ $learners->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $learners->onFirstPage() ? '#' : $learners->previousPageUrl() }}" @if($learners->onFirstPage()) aria-disabled="true" tabindex="-1" @endif>
                    Previous
                </a>

                @php
                    $startPage = max(1, $learners->currentPage() - 2);
                    $endPage = min($learners->lastPage(), $learners->currentPage() + 2);
                @endphp

                @if ($startPage > 1)
                    <a class="page-link" href="{{ $learners->url(1) }}">1</a>
                    @if ($startPage > 2)
                        <span class="page-link is-disabled">…</span>
                    @endif
                @endif

                @for ($page = $startPage; $page <= $endPage; $page++)
                    <a class="page-link {{ $page === $learners->currentPage() ? 'is-active' : '' }}" href="{{ $learners->url($page) }}">{{ $page }}</a>
                @endfor

                @if ($endPage < $learners->lastPage())
                    @if ($endPage < $learners->lastPage() - 1)
                        <span class="page-link is-disabled">…</span>
                    @endif
                    <a class="page-link" href="{{ $learners->url($learners->lastPage()) }}">{{ $learners->lastPage() }}</a>
                @endif

                <a class="page-link {{ $learners->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $learners->hasMorePages() ? $learners->nextPageUrl() : '#' }}" @if(!$learners->hasMorePages()) aria-disabled="true" tabindex="-1" @endif>
                    Next
                </a>
            </nav>
        @endif
    </div>
@endsection