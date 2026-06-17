<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - {{ $orderId }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card shadow" style="max-width: 480px; width: 100%;">
            <div class="card-body p-4">
                <div class="mb-4">
                    <h1 class="h3 mb-2 text-dark">Complete Your Payment</h1>
                    <p class="text-muted mb-1">Order: {{ $orderId }}</p>
                    <h2 class="h1 text-success mt-2">EUR {{ number_format($amount, 2) }}</h2>
                </div>

                <form action="{{ route('payment.process', $sessionId) }}" method="POST" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="cardHolderName" class="form-label">Card Holder Name</label>
                        <input type="text" class="form-control @error('cardHolderName') is-invalid @enderror"
                            id="cardHolderName" name="cardHolderName" required placeholder="John Doe"
                            value="{{ old('cardHolderName') }}">
                        @error('cardHolderName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="cardNumber" class="form-label">Card Number</label>
                        <input type="text" class="form-control @error('cardNumber') is-invalid @enderror"
                            id="cardNumber" name="cardNumber" required maxlength="16"
                            placeholder="1234567890123456" value="{{ old('cardNumber') }}">
                        @error('cardNumber')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-7">
                            <label for="expiryDate" class="form-label">Expiry Date</label>
                            <div class="input-group @error('expiryMonth') is-invalid @enderror @error('expiryYear') is-invalid @enderror @error('expiryDate') is-invalid @enderror">
                                <input type="text" class="form-control @error('expiryMonth') is-invalid @enderror @error('expiryDate') is-invalid @enderror"
                                    id="expiryMonth" name="expiryMonth" required maxlength="2" placeholder="MM"
                                    value="{{ old('expiryMonth') }}">
                                <input type="text" class="form-control @error('expiryYear') is-invalid @enderror @error('expiryDate') is-invalid @enderror"
                                    id="expiryYear" name="expiryYear" required maxlength="4" placeholder="YYYY"
                                    value="{{ old('expiryYear') }}">
                            </div>
                            <div class="invalid-feedback">
                                @error('expiryMonth')
                                <div>{{ $message }}</div>
                                @enderror
                                @error('expiryYear')
                                <div>{{ $message }}</div>
                                @enderror
                                @error('expiryDate')
                                <div>{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-5">
                            <label for="cvc" class="form-label">CVC</label>
                            <input type="text" class="form-control @error('cvc') is-invalid @enderror"
                                id="cvc" name="cvc" required maxlength="3" placeholder="123"
                                value="{{ old('cvc') }}">
                            @error('cvc')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 mb-2 py-2 fw-semibold">
                        Pay EUR {{ number_format($amount, 2) }}
                    </button>

                    <button type="submit" formaction="{{ route('payment.fail', $sessionId) }}"
                        class="btn btn-danger w-100 py-2 fw-semibold">
                        Simulate Failure
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
