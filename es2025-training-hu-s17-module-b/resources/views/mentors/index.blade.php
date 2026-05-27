@extends('layouts.admin')

@section('content')
    <div class="page-wrap">
        @if (session('status'))
            <div class="flash-message">
                {{ session('status') }}
            </div>
        @endif

        <section class="toolbar-row">
            <h1>Mentor management</h1>
        </section>

        <div class="table-shell table-shell-compact">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Hourly rate</th>
                        <th>Experience</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mentors as $mentor)
                        @php
                            $mentorName = trim(($mentor->user?->first_name ?? '') . ' ' . ($mentor->user?->last_name ?? '')) ?: $mentor->user?->name;
                        @endphp
                        <tr>
                            <td>
                                {{ $mentorName }}
                                @if ($mentor->approval_status === 'pending')
                                    <span class="user-status">Pending approval</span>
                                @endif
                            </td>
                            <td>{{ number_format((int) $mentor->hourly_credit_rate) }} credits / h</td>
                            <td>{{ number_format((int) ($mentor->years_experience ?? 0)) }} years</td>
                            <td>{{ rtrim(rtrim(number_format((float) ($mentor->average_rating ?? 0), 1), '0'), '.') }}</td>
                            <td>
                                @if ($mentor->approval_status === 'approved')
                                    <span class="badge badge-success">Approved</span>
                                @elseif ($mentor->approval_status === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @else
                                    <span class="badge badge-danger">Rejected</span>
                                @endif
                            </td>
                            <td class="data-table__actions">
                                @if ($mentor->approval_status === 'pending')
                                    <form method="POST" action="{{ route('mentors.approve', $mentor) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-small btn-outline">Approve</button>
                                    </form>
                                @else
                                    <span class="action-link action-link-muted">Approved</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">No mentors found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection