@extends('layouts.layout')

@section('content')
    <div class="container">
        <div class="page-header">
            <div>
                <h1>Applications</h1>
                <p>Moderate requests for your bicycle categories.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="message">{{ session('success') }}</div>
        @endif

        @if ($applications->count())
            <div class="table-box">
                <table>
                    <thead>
                        <tr>
                            <th>Applicant</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($applications as $application)
                            <tr class="{{ $application->status !== 'pending' ? 'archived-row' : '' }}">
                                <td>
                                    <strong>{{ $application->user->name ?? 'Unknown' }}</strong><br>
                                    <span>{{ $application->user->phone ?? '' }}</span><br>
                                    <span>{{ $application->user->email ?? '' }}</span>
                                </td>
                                <td>{{ $application->category->name ?? 'Unknown' }}</td>
                                <td>{{ ucfirst($application->status) }}</td>
                                <td class="actions">
                                    @if ($application->status === 'pending')
                                        <form method="POST" action="{{ route('applications.approve', $application) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="small" type="submit">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('applications.reject', $application) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="danger small" type="submit">Reject</button>
                                        </form>
                                    @else
                                        No actions
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-box">
                <p>No applications found.</p>
            </div>
        @endif
    </div>
@endsection
