@extends('layouts.layout')

@section('content')
    <section class="text-centered">
        <h1>Admin Dashboard</h1>
        <p>See pending investments</p>
        @if (session('status'))
            <div class="status-message">
                {{ session('status') }}
            </div>
        @endif
    </section>
    <section class="investments-section">
        @foreach ($investments as $investment)
            <div class="investment-item">
                <h3>{{ $investment->investor_name }} - {{ ucfirst($investment->investment_type) }}</h3>
                <p>Email: {{ $investment->investor_email }}</p>
                <p>Address: {{ $investment->investor_address }}</p>
                <p>Phone: {{ $investment->investor_phone }}</p>
                @if ($investment->investment_type === 'turbine' && $investment->turbine)
                    <p>Turbine Option: {{ $investment->turbine->name }}</p>
                    <p>Turbine Text: {{ $investment->turbine->displayed_text }}</p>
                    @if ($investment->turbine->logo_filename)
                        <img src="{{ asset('storage/sponsorImages/' . $investment->turbine->logo_filename) }}" alt="Turbine Logo" class="admin-turbine-logo">
                    @endif
                @elseif ($investment->investment_type === 'presenting' && $investment->presentingSponsor)
                    <img src="{{ asset('storage/sponsorImages/' . $investment->presentingSponsor->logo_filename) }}" alt="Sponsor Logo" class="admin-sponsor-logo">
                @elseif ($investment->investment_type === 'support' && $investment->supports)
                    <p>Support Amount: {{ number_format($investment->supports->amount, 2) }} DKK</p>
                @endif
                <p>Status: {{ ucfirst($investment->status) }}</p>
                @if ($investment->status === 'pending')
                    <div class="buttons">
                        <a href="/admin/investments/{{ $investment->id }}/approve" class="button approve">Approve</a>
                        <a href="/admin/investments/{{ $investment->id }}/reject" class="button reject">Reject</a>
                    </div>
                @endif
            </div>
        @endforeach
    </section>
@endsection