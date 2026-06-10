@extends('layouts.layout')

@section('content')
    <div class="container">
        <div class="page-header">
            <div>
                <h1>Booking history</h1>
                <p>View rentals for your bicycles.</p>
            </div>
            <a class="btn" href="{{ route('history.export', request()->query()) }}">Export</a>
        </div>

        <form method="GET" class="filter-form" action="{{ route('history.index') }}">
            <div class="form-group">
                <label for="from">From:</label>
                <input type="date" id="from" name="from" value="{{ request('from') }}">
            </div>
            <div class="form-group">
                <label for="to">To:</label>
                <input type="date" id="to" name="to" value="{{ request('to') }}">
            </div>
            <button type="submit">Filter</button>
            <a class="btn secondary" href="{{ route('history.index') }}">Clear</a>
        </form>

        @if ($bookings->count())
            <div class="table-box">
                <table>
                    <thead>
                        <tr>
                            <th>Renter</th>
                            <th>Started</th>
                            <th>Ended</th>
                            <th>Wear</th>
                            <th>Photos</th>
                            <th>Price</th>
                            <th>Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bookings as $booking)
                            <tr>
                                <td>
                                    <strong>{{ $booking->user->name ?? 'Unknown' }}</strong><br>
                                    <span>{{ $booking->user->phone ?? '' }}</span>
                                </td>
                                <td>{{ $booking->startedAt ? $booking->startedAt->format('Y-m-d H:i') : '-' }}</td>
                                <td>{{ $booking->endedAt ? $booking->endedAt->format('Y-m-d H:i') : '-' }}</td>
                                <td>
                                    @if ($booking->percentageOfWear !== null)
                                        {{ $booking->percentageOfWear }}%
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <div class="photo-list">
                                        @foreach ($booking->photos ?? [] as $photo)
                                            <img class="bike-image" src="{{ asset('storage/bicycle-images/' . $photo) }}" alt="Booking photo">
                                        @endforeach
                                    </div>
                                </td>
                                <td>{{ $booking->price ?? '-' }}</td>
                                <td>{{ $booking->rating ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                @if ($bookings->onFirstPage())
                    <span>Previous</span>
                @else
                    <a href="{{ $bookings->previousPageUrl() }}">Previous</a>
                @endif

                @for ($page = 1; $page <= $bookings->lastPage(); $page++)
                    @if ($page === 1 || $page === $bookings->lastPage() || abs($page - $bookings->currentPage()) <= 1)
                        @if ($page === $bookings->currentPage())
                            <span class="active">{{ $page }}</span>
                        @else
                            <a href="{{ $bookings->url($page) }}">{{ $page }}</a>
                        @endif
                    @elseif ($page === 2 || $page === $bookings->lastPage() - 1)
                        <span>...</span>
                    @endif
                @endfor

                @if ($bookings->hasMorePages())
                    <a href="{{ $bookings->nextPageUrl() }}">Next</a>
                @else
                    <span>Next</span>
                @endif
            </div>
        @else
            <div class="empty-box">
                <p>No booking history found.</p>
            </div>
        @endif
    </div>
@endsection
