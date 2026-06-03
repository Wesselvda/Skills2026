@extends('layouts.layout')

@section('content')
    <section class="text-centered">
        <h1>Presenting Sponsors</h1>
    </section>
    <section class="sponsors-section">
        @foreach ($sponsors as $sponsor)
            <div class="sponsor-item">
                @if ($sponsor->status === 'approved')
                    <img src="{{ asset('storage/sponsorImages/' . $sponsor->logo_filename) }}" alt="{{ $sponsor->displayed_text }}" class="sponsor-logo">
                @endif
            </div>
        @endforeach
        @for ($i = count($sponsors); $i < 3; $i++)
            <div class="sponsor-item placeholder">
                <img src="{{ asset('assets/images/sponsor-placeholder.svg') }}" alt="Placeholder Sponsor Logo" class="sponsor-logo">
            </div>
        @endfor
    </section>
@endsection