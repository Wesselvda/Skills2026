@extends('layouts.layout')

@section('content')
    <section class="text-centered">
        <h1>Invest in the Future of Energy</h1>
        <p>Total amount invested: {{ $totalInvestment }} DKK</p>
    </section>
    <section class="turbine-grid">
        @foreach ($turbines as $turbine)
            <div class="turbine-item">
                <h3>{{ $turbine->name }}</h3>
                @if ($turbine->status === 'approved')
                    <img src="{{ asset('storage/sponsorImages/' . $turbine->logo_filename) }}" alt="{{ $turbine->displayed_text }}" class="turbine-logo">
                @else
                    <p>{{ ucfirst($turbine->status) }}</p>
                @endif
            </div>
        @endforeach
    </section>
    <section>
        <form class="investor-form" action="/investors" method="POST" enctype="multipart/form-data">
            <div class="section-half-image">
                <fieldset class="contact-fieldset">
                    <legend>Contact details</legend>
                    <div class="input-row">
                        <label for="fullname">Full name / Company name</label>
                        <input id="fullname" name="fullname" type="text" required maxlength="100" value="{{ old('fullname') }}" placeholder="Your full name or company name">
                        @error('fullname')
                            <p class="error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="input-row">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" required value="{{ old('email') }}" placeholder="name@example.com">
                        @error('email')
                            <p class="error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="input-row">
                        <label for="address">Address</label>
                        <input id="address" name="address" type="text" required value="{{ old('address') }}" placeholder="Address">
                        @error('address')
                            <p class="error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="input-row">
                        <label for="phone">Phone</label>
                        <input id="phone" name="phone" type="tel" required value="{{ old('phone') }}" placeholder="+31 6 12345678">
                        @error('phone')
                            <p class="error">{{ $message }}</p>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="investment_options-fieldset">
                    <legend>Investment options</legend>

                    @error('investment_option')
                        <p class="error">{{ $message }}</p>
                    @enderror

                    <div class="input-wrapper">
                        <input type="radio" {{ old('investment_option') === 'turbine' ? 'checked' : '' }} id="opt-turbine" name="investment_option" value="turbine">
                        <label for="opt-turbine">Fund one or more wind turbines</label>
                        <div class="option-details" aria-hidden="true">
                            <div class="input-row">
                                <label for="turbine-select">What turbine would you like to fund?</label>
                                <select id="turbine-select" name="turbine_option">
                                    <option value="">Select an option</option>
                                    @foreach ($turbines->where('status', 'available') as $turbine)
                                        <option {{ old('turbine_option') == $turbine->id ? 'selected' : '' }} value="{{ $turbine->id }}">{{ $turbine->name }}</option>
                                    @endforeach
                                </select>
                                @error('turbine_option')
                                    <p class="error">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="input-row">
                                <label for="turbine-text">Engraving text (max 25 characters)</label>
                                <input id="turbine-text" name="turbine_text" type="text" maxlength="25" value="{{ old('turbine_text') }}" placeholder="Short text (max 25)">
                                @error('turbine_text')
                                    <p class="error">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="input-row">
                                <label for="turbine-logo">Company logo (optional)</label>
                                <input id="turbine-logo" name="turbine_logo" type="file" maxsize="1MB" accept="image/*">
                                @error('turbine_logo')
                                    <p class="error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="input-wrapper">
                        <input type="radio" {{ old('investment_option') === 'presenting' ? 'checked' : '' }} id="opt-sponsor" name="investment_option" value="presenting">
                        <label for="opt-sponsor">Become a presenting sponsor</label>
                        <div class="option-details" aria-hidden="true">
                            <div class="input-row">
                                <label for="sponsor-logo">Sponsor logo (PNG/JPG/SVG)</label>
                                <input id="sponsor-logo" name="sponsor_logo" type="file" maxsize="1MB" accept="image/png,image/jpeg,image/svg+xml">
                                @error('sponsor_logo')
                                    <p class="error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="input-wrapper">
                        <input type="radio" {{ old('investment_option') === 'support' ? 'checked' : '' }} id="opt-donation" name="investment_option" value="support">
                        <label for="opt-donation">Support the project with a monetary amount</label>
                        <div class="option-details" aria-hidden="true">
                            <div class="input-row">
                                <label for="donation-amount">Amount (DKK)</label>
                                <input id="donation-amount" name="donation_amount" type="number" min="1" step="0.01" value="{{ old('donation_amount') }}" placeholder="0.00">
                                @error('donation_amount')
                                    <p class="error">{{ $message }}</p>
                                @enderror
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