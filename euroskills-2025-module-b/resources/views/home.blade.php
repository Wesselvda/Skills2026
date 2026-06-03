@extends('layouts.layout')

@section('content')
    <section class="home-hero">
        <video autoplay muted loop>
            <source src="/assets/videos/day.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="overlay">
            <h1>The Offshore Wind Farm is a <b>bold</b> step forward in our journey toward a sustainable and low-carbon future.</h1>
        </div>
    </section>
    <section class="section-half-image">
        <div>
            <h2>Project overview</h2>
            <p>Positioned off the West Sea coast of Denmark, this state-of-the-art renewable energy project will harness the power of offshore wind to deliver clean, reliable electricity to thousands of homes and businesses.</p>
            <p>Backed by a vision of innovation and environmental stewardship, we are leading the development of this project to help meet growing energy demands while drastically reducing greenhouse gas emissions.</p>
            <p>We are currently seeking forward-thinking investors and partners to join us in shaping tomorrow's energy landscape.</p>
        </div>
        <div>
            <img src="/assets/images/jesse-de-meulenaere--IaTiYqRTL8-unsplash.jpg" alt="Image of wind turbines at sea">
        </div>
    </section>
    <section class="key-facts">
        <h2>Key facts</h2>
        <div class="fact-wrapper">
            <div class="fact">
                <div class="label">
                    Location
                </div>
                <div class="value">
                    30 km off the West coast of Denmark
                </div>
            </div>
            <div class="fact">
                <div class="label">
                    Total Capacity
                </div>
                <div class="value">
                    1.2 GW (enough to power 1 million homes)
                </div>
            </div>
            <div class="fact">
                <div class="label">
                    Number of Turbines
                </div>
                <div class="value">
                    80+ high-efficiency offshore wind units
                </div>
            </div>
            <div class="fact">
                <div class="label">
                    Operational Date
                </div>
                <div class="value">
                    Targeted for Q4 2026
                </div>
            </div>
            <div class="fact">
                <div class="label">
                    Estimated CO₂ Reduction
                </div>
                <div class="value">
                    Over 2 million tons per year
                </div>
            </div>
        </div>
    </section>
    <section class="section-half-image">
        <div>
            <img src="/assets/map/style6.png" alt="Image of map">
            <img src="/assets/icons/iconoir-regular/map-pin.svg" alt="Map pin" class="map-pin">
            <img src="/assets/icons/turbines/turbine-1.svg" alt="Map pin" class="map-pin top">
        </div>
        <div>
            <h2>Powering Progress, Responsibly</h2>
            <p>This isn't just about building a wind farm—it's about building a legacy of clean energy, economic growth, and environmental protection.</p>
            <p>With a focus on innovation, safety, and long-term return, this project represents an exceptional investment opportunity in one of the world's fastest-growing energy sectors.</p>
            <h2>Important locations</h2>
            <p>Start of the visitor tours: Thyboron</p>
            <p>Location of the offshore wind farm: 30 km off the West coast</p>
        </div>
    </section>
@endsection