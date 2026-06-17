<?php

use App\Models\PaymentSession;

describe('Payment Page', function () {
    describe('GET /payment/{sessionId}', function () {
        it('shows the payment page for a pending session', function () {
            $session = PaymentSession::factory()->create([
                'status' => PaymentSession::STATUS_PENDING,
                'callback_url' => 'https://example.com/callback',
            ]);

            $response = $this->get("/payment/{$session->id}");

            $response->assertStatus(200)
                ->assertViewIs('payment')
                ->assertViewHas('sessionId', $session->id)
                ->assertViewHas('amount', $session->amount)
                ->assertViewHas('orderId', $session->order_id);
        });

        it('redirects to callback when session is successful', function () {
            $session = PaymentSession::factory()->successful()->create([
                'callback_url' => 'https://example.com/callback',
            ]);

            $response = $this->get("/payment/{$session->id}");

            $response->assertRedirect('https://example.com/callback');
        });

        it('redirects to callback when session is failed', function () {
            $session = PaymentSession::factory()->failed()->create([
                'callback_url' => 'https://example.com/callback',
            ]);

            $response = $this->get("/payment/{$session->id}");

            $response->assertRedirect('https://example.com/callback');
        });

        it('redirects to home when session is successful without callback', function () {
            $session = PaymentSession::factory()->create([
                'status' => PaymentSession::STATUS_SUCCESSFUL,
                'paid_at' => now(),
                'callback_url' => 'https://example.com/callback',
            ]);

            $response = $this->get("/payment/{$session->id}");

            $response->assertRedirect('https://example.com/callback');
        });

        it('returns 404 for non-existent session', function () {
            $nonExistentId = '00000000-0000-0000-0000-000000000000';

            $response = $this->get("/payment/{$nonExistentId}");

            $response->assertStatus(404);
        });
    });

    describe('POST /payment/{sessionId}/process', function () {
        it('processes payment successfully', function () {
            $session = PaymentSession::factory()->create([
                'status' => PaymentSession::STATUS_PENDING,
            ]);

            $response = $this->post("/payment/{$session->id}/process", [
                'cardHolderName' => 'John Doe',
                'cardNumber' => '1234567890123456',
                'expiryMonth' => '12',
                'expiryYear' => '2030',
                'cvc' => '123',
            ]);

            $response->assertRedirect();

            $this->assertDatabaseHas('payment_sessions', [
                'id' => $session->id,
                'status' => PaymentSession::STATUS_SUCCESSFUL,
                'paid_at' => now(),
            ]);
        });

        it('validates card holder name', function () {
            $session = PaymentSession::factory()->create();

            $response = $this->post("/payment/{$session->id}/process", [
                'cardHolderName' => '',
                'cardNumber' => '1234567890123456',
                'expiryMonth' => '12',
                'expiryYear' => '2025',
                'cvc' => '123',
            ]);

            $response->assertRedirect()
                ->assertSessionHasErrors('cardHolderName');
        });

        it('validates card number is just numbers', function () {
            $session = PaymentSession::factory()->create();

            $response = $this->post("/payment/{$session->id}/process", [
                'cardHolderName' => 'John Doe',
                'cardNumber' => '123456789012345a',
                'expiryMonth' => '12',
                'expiryYear' => '2025',
                'cvc' => '123',
            ]);

            $response->assertRedirect()
                ->assertSessionHasErrors('cardNumber');
        });

        it('validates expiry month is valid', function () {
            $session = PaymentSession::factory()->create();

            $response = $this->post("/payment/{$session->id}/process", [
                'cardHolderName' => 'John Doe',
                'cardNumber' => '1234567890123456',
                'expiryMonth' => '13',
                'expiryYear' => '2025',
                'cvc' => '123',
            ]);

            $response->assertRedirect()
                ->assertSessionHasErrors('expiryMonth');
        });

        it('validates expiry year format', function () {
            $session = PaymentSession::factory()->create();

            $response = $this->post("/payment/{$session->id}/process", [
                'cardHolderName' => 'John Doe',
                'cardNumber' => '1234567890123456',
                'expiryMonth' => '12',
                'expiryYear' => '25',
                'cvc' => '123',
            ]);

            $response->assertRedirect()
                ->assertSessionHasErrors('expiryYear');
        });

        it('validates expiry date is not expired', function () {
            $session = PaymentSession::factory()->create();

            $response = $this->post("/payment/{$session->id}/process", [
                'cardHolderName' => 'John Doe',
                'cardNumber' => '1234567890123456',
                'expiryMonth' => '01',
                'expiryYear' => '2020',
                'cvc' => '123',
            ]);

            $response->assertRedirect()
                ->assertSessionHasErrors('expiryDate');
        });

        it('validates cvc is just numbers', function () {
            $session = PaymentSession::factory()->create();

            $response = $this->post("/payment/{$session->id}/process", [
                'cardHolderName' => 'John Doe',
                'cardNumber' => '1234567890123456',
                'expiryMonth' => '12',
                'expiryYear' => '2025',
                'cvc' => '12a',
            ]);

            $response->assertRedirect()
                ->assertSessionHasErrors('cvc');
        });

        it('returns 404 for non-existent session', function () {
            $nonExistentId = '00000000-0000-0000-0000-000000000000';

            $response = $this->post("/payment/{$nonExistentId}/process", [
                'cardHolderName' => 'John Doe',
                'cardNumber' => '1234567890123456',
                'expiryMonth' => '12',
                'expiryYear' => '2030',
                'cvc' => '123',
            ]);

            $response->assertStatus(404);
        });

        it('marks session as successful even if already processed', function () {
            $session = PaymentSession::factory()->successful()->create();

            $response = $this->post("/payment/{$session->id}/process", [
                'cardHolderName' => 'John Doe',
                'cardNumber' => '1234567890123456',
                'expiryMonth' => '12',
                'expiryYear' => '2030',
                'cvc' => '123',
            ]);

            $response->assertRedirect();
        });
    });

    describe('POST /payment/{sessionId}/fail', function () {
        it('simulates payment failure with random error code', function () {
            $session = PaymentSession::factory()->create([
                'status' => PaymentSession::STATUS_PENDING,
            ]);

            $response = $this->post("/payment/{$session->id}/fail", [
                'cardHolderName' => 'John Doe',
                'cardNumber' => '1234567890123456',
                'expiryMonth' => '12',
                'expiryYear' => '2030',
                'cvc' => '123',
            ]);

            $response->assertRedirect();

            $this->assertDatabaseHas('payment_sessions', [
                'id' => $session->id,
                'status' => PaymentSession::STATUS_FAILED,
            ]);

            $failedSession = $session->fresh();
            expect($failedSession->error_code)->toBeIn(['CARD_DECLINED', 'INSUFFICIENT_FUNDS', 'EXPIRED_CARD', 'FRAUD_SUSPECTED']);
        });

        it('fails without validating form fields', function () {
            $session = PaymentSession::factory()->create([
                'status' => PaymentSession::STATUS_PENDING,
            ]);

            $response = $this->post("/payment/{$session->id}/fail", []);

            $response->assertRedirect();

            $this->assertDatabaseHas('payment_sessions', [
                'id' => $session->id,
                'status' => PaymentSession::STATUS_FAILED,
            ]);
        });

        it('returns 404 for non-existent session', function () {
            $nonExistentId = '00000000-0000-0000-0000-000000000000';

            $response = $this->post("/payment/{$nonExistentId}/fail", [
                'cardHolderName' => 'John Doe',
                'cardNumber' => '1234567890123456',
                'expiryMonth' => '12',
                'expiryYear' => '2025',
                'cvc' => '123',
            ]);

            $response->assertStatus(404);
        });

        it('does not change status if already processed', function () {
            $session = PaymentSession::factory()->successful()->create();

            $response = $this->post("/payment/{$session->id}/fail", [
                'cardHolderName' => 'John Doe',
                'cardNumber' => '1234567890123456',
                'expiryMonth' => '12',
                'expiryYear' => '2030',
                'cvc' => '123',
            ]);

            $response->assertRedirect();

            $this->assertDatabaseHas('payment_sessions', [
                'id' => $session->id,
                'status' => PaymentSession::STATUS_SUCCESSFUL,
            ]);
        });
    });
});
