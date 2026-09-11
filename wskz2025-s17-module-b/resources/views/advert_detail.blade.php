@extends('layouts.app')

@section('content')
    <section class="container">
        <a href="{{ route('adverts.index') }}">&larr; Back to adverts</a>
        <h1>{{ $advert->title }} <small>#{{ $advert->id }}</small></h1>

        <div class="advert-details">
            <p><span class="detail-label">Text:</span> {{ $advert->text }}</p>
            <p><span class="detail-label">Price:</span> {{ number_format((float) $advert->price, 2) }}</p>
            <p><span class="detail-label">Views:</span> {{ $advert->views_count }}</p>
            <p><span class="detail-label">Category:</span> {{ $advert->category->name }}</p>
            <p><span class="detail-label">Status:</span> {{ ucfirst($advert->status) }}</p>
        </div>

        <h2>Photos</h2>
        <div class="photo-grid">
            @forelse ($advert->photos ?? [] as $photo)
                <div class="photo-tile">
                    <img src="{{ asset('storage/images/' . $photo) }}" alt="{{ $advert->title }}">
                </div>
            @empty
                <p>No photos are attached to this advert.</p>
            @endforelse
        </div>

        <h2>Author #{{ $advert->author->id }}</h2>
        <div class="advert-details">
            <p><span class="detail-label">Name:</span> {{ $advert->author->name }}</p>
            <p><span class="detail-label">Phone:</span> {{ $advert->author->phone }}</p>
            <p><span class="detail-label">Email:</span> <a href="mailto:{{ $advert->author->email }}">{{ $advert->author->email }}</a></p>
        </div>

        <h2>Change status</h2>
        @php($transitions = ['moderation' => ['published', 'declined'], 'published' => ['declined']])
        @if (! empty($transitions[$advert->status]))
            <form action="{{ route('adverts.status.update', $advert) }}" method="POST" class="inline-form">
                @csrf
                @method('PATCH')
                <label for="status">New status</label>
                <select id="status" name="status">
                    @foreach ($transitions[$advert->status] as $status)
                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <button type="submit">Update status</button>
            </form>
        @else
            <p>No status transitions are available.</p>
        @endif

        <h2>Paid services</h2>
        <table>
            <thead>
                <tr><th>Type</th><th>Activated</th><th>Validity</th><th>Expires</th><th>State</th><th>Action</th></tr>
            </thead>
            <tbody>
                @forelse ($advert->paidServices as $service)
                    <tr>
                        <td>{{ ucfirst($service->type) }}</td>
                        <td>{{ $service->activated_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $service->validity_days }} days</td>
                        <td>{{ $service->expires_at->format('Y-m-d H:i') }}</td>
                        <td>
                            {{ $service->is_enabled ? 'Enabled' : 'Disabled' }}
                            @if ($service->is_expired) (Expired) @endif
                        </td>
                        <td>
                            <form action="{{ route('adverts.paid-services.toggle', [$advert, $service]) }}" method="POST" class="inline-form">
                                @csrf
                                @method('PATCH')
                                <button type="submit">{{ $service->is_enabled ? 'Disable' : 'Enable' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">No paid services are connected to this advert.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
