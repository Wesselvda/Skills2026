@extends('emails.layout')

@section('content')
<h2>Hi {{ $name }}!</h2>

<p>Thank you for your investment request.</p>
<p>We have received your request and it is now pending approval.</p>

<p><strong>Investment reference:</strong> {{ $investmentReference }}</p>
<p><strong>Investment Type:</strong> {{ $investmentType }}</p>
@if ($turbineData)
<p><strong>Turbine Spot:</strong> {{ $turbineData['turbine']->name }}</p>
<p><strong>Logo/Text:</strong></p>
@if ($turbineData['logo_filename']) 
<img src="{{ asset('storage/sponsorImages/' . $turbineData['logo_filename']) }}" alt="{{ $turbineData['displayed_text'] ?? 'Turbine Logo' }}" style="max-width: 200px; max-height: 100px;">
@else
{{ $turbineData['displayed_text'] }}
@endif
@endif
@if ($sponsorLogoData)
<p><strong>Logo/Text:</strong></p>
<img src="{{ asset('storage/sponsorImages/' . $sponsorLogoData['logo_filename']) }}" alt="Sponsor Logo" style="max-width: 200px; max-height: 100px;">
@endif
@if ($supportAmount)
<p><strong>Support Amount:</strong> {{ $supportAmount }}</p>
@endif

<p>We will shortly review your request and provide you with details on the next steps (such as payment details).</p>

<p>Stay breezy and sustainable!</p>
@endsection