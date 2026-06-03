@extends('layouts.layout')

@section('content')
    <section class="text-centered">
        <h1>Invest in the Future of Energy</h1>
    </section>
    <section class="section-half-image">
        <div>
            <img src="/assets/images/jannik-qbW9KLqWDiQ-unsplash.jpg" alt="Image of inside of building">
        </div>
        <div>
            <h2>Join us in driving the shift to renewable energy.</h2>
            <p>The Offshore Wind Farm invites companies and private individuals to become part of a groundbreaking initiative that will power homes, protect the planet, and generate long-term value.</p>
            <p>Whether you're looking to make a symbolic contribution or a strategic investment, we offer flexible and meaningful ways to get involved.</p>
        </div>
    </section>
    <section>
        <form class="investor-form" novalidate>
            <fieldset class="investor-type-fieldset">
                <legend>What type of investor are you?</legend>
                <div class="input-wrapper">
                    <input type="radio" id="company" name="investor-type" value="company" required>
                    <label for="company">Company Investor</label>
                </div>
                <div class="input-wrapper">
                    <input type="radio" id="individual" name="investor-type" value="individual" required>
                    <label for="individual">Private Individual Investor</label>
                </div>
            </fieldset>
            <div class="section-half-image">
                <fieldset class="contact-fieldset">
                    <legend>Contact details</legend>
                    <div class="input-row">
                        <label for="fullname">Full name / Company name</label>
                        <input id="fullname" name="fullname" type="text" required maxlength="100" placeholder="Your full name or company name">
                    </div>
                    <div class="input-row">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" required placeholder="name@example.com">
                    </div>
                    <div class="input-row">
                        <label for="address">Address</label>
                        <input id="address" name="address" type="text" required placeholder="Address">
                    </div>
                    <div class="input-row">
                        <label for="phone">Phone</label>
                        <input id="phone" name="phone" type="tel" required placeholder="+31 6 12345678">
                    </div>
                </fieldset>

                <fieldset class="investment-options-fieldset">
                    <legend>Investment options</legend>

                    <div class="input-wrapper">
                        <input type="checkbox" id="opt-turbine" name="investment-option" value="turbine">
                        <label for="opt-turbine">Fund one or more wind turbines</label>
                        <div class="option-details" aria-hidden="true">
                            <div class="input-row">
                                <label for="turbine-count">Number of turbines</label>
                                <input id="turbine-count" name="turbine_count" type="number" min="1" max="100" value="1">
                            </div>
                            <div class="input-row">
                                <label for="turbine-text">Engraving text (max 25 characters)</label>
                                <input id="turbine-text" name="turbine_text" type="text" maxlength="25" placeholder="Short text (max 25)">
                            </div>
                            <div class="input-row">
                                <label for="turbine-logo">Company logo (optional)</label>
                                <input id="turbine-logo" name="turbine_logo" type="file" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <div class="input-wrapper">
                        <input type="checkbox" id="opt-sponsor" name="investment-option" value="sponsor">
                        <label for="opt-sponsor">Become a presenting sponsor</label>
                        <div class="option-details" aria-hidden="true">
                            <div class="input-row">
                                <label for="sponsor-logo">Sponsor logo (PNG/JPG/SVG)</label>
                                <input id="sponsor-logo" name="sponsor_logo" type="file" accept="image/png,image/jpeg,image/svg+xml">
                            </div>
                            <div class="input-row">
                                <label for="sponsor-name">Organisation / Brand name</label>
                                <input id="sponsor-name" name="sponsor_name" type="text" maxlength="100">
                            </div>
                        </div>
                    </div>

                    <div class="input-wrapper">
                        <input type="checkbox" id="opt-donation" name="investment-option" value="donation">
                        <label for="opt-donation">Support the project with a monetary amount</label>
                        <div class="option-details" aria-hidden="true">
                            <div class="input-row">
                                <label for="donation-amount">Amount</label>
                                <input id="donation-amount" name="donation_amount" type="number" min="1" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class="input-row">
                <button type="submit">Send interest</button>
            </div>
        </form>
    </section>
@endsection