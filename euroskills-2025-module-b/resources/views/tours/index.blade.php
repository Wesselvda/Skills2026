@extends('layouts.layout')

@section('content')
    <section class="text-centered">
        <h1>Visit the Offshore Wind Farm</h1>
    </section>
    <section class="section-half-image">
        <div>
            <img src="assets/images/kristijan-arsov-aackMr-c12s-unsplash.jpg" alt="Image of boat with people">
        </div>
        <div>
            <h2>Experience the Power of the Wind</h2>
            <p>Once operational, the Offshore Wind Farm will open its doors to the public—inviting you to step behind the scenes of one of the most exciting renewable energy projects in the world.</p>
            <p>Our guided visitor tours offer a rare opportunity to see cutting-edge offshore wind technology in action.</p>
            <p>Learn how clean electricity is generated, explore the engineering behind the turbines, and discover the environmental benefits of offshore energy.</p>
        </div>
    </section>
    <section class="whattoexpect">
        <h2>What to Expect on the Tour:</h2>
        <ul>
            <li>Guided boat trip to the wind farm location</li>
            <li>Up-close view of turbines in operation</li>
            <li>Insightful presentations on wind energy technology</li>
            <li>Live Q&A with our engineers and project team</li>
            <li>Safety gear and briefings provided</li>
        </ul>
        <p>Tours last approximately 3 hours and depart from Thyboron Havn.</p>
    </section>
    <section class="tours">
        <h2>Book Your Tour</h2>
        <div class="tour-grid">
            @foreach ($tours as $tour)
                <div class="item">
                    <h3>{{ $tour->name }}</h3>
                    <p>Available Seats: {{ $tour->total_seats - $tour->bookings->sum('seats') }}</p>
                    <p>{{ \Carbon\Carbon::parse($tour->tour_date)->format('l jS F Y H:i') }}</p>
                    <a href="{{ route('tours.show', $tour) }}" class="button">Book Now</a>
                </div>
            @endforeach
        </div>
    </section>
    <section class="section-half-image">
        <div>
            <h2>Important locations</h2>
            <p>Start of the visitor tours: Thyboron</p>
            <p>Location of the offshore wind farm: 30 km off the West coast</p>
        </div>
        <div>
            <img src="assets/map/style6.png" alt="Image of map">
            <img src="assets/icons/iconoir-regular/map-pin.svg" alt="Map pin" class="map-pin">
            <img src="assets/icons/turbines/turbine-1.svg" alt="Map pin" class="map-pin top">
        </div>
    </section>
@endsection