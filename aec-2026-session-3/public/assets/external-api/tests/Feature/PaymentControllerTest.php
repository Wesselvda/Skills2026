<?php

use App\Models\PaymentSession;

describe('Payment Service', function () {
    describe('POST /api/payment/session', function () {
        it('creates a payment session successfully', function () {
            $payload = [
                'amount' => 99.99,
                'orderId' => 'ORD-12345',
                'callbackUrl' => 'https://storefront.example.com/payment/callback',
                'expiresAt' => now()->addMinute()->toIso8601String()
            ];

            $response = $this->postJson('/api/payment/session', $payload);

            $response->assertStatus(201)
                ->assertJsonStructure([
                    'sessionId',
                    'paymentUrl',
                    'status',
                ])
                ->assertJson([
                    'status' => 'pending',
                ]);

            $this->assertDatabaseHas('payment_sessions', [
                'amount' => 99.99,
                'order_id' => 'ORD-12345',
                'status' => PaymentSession::STATUS_PENDING,
            ]);
        });

        it('creates a payment session with callback URL', function () {
            $payload = [
                'amount' => 150.50,
                'orderId' => 'ORD-67890',
                'callbackUrl' => 'https://storefront.example.com/payment/callback',
                'expiresAt' => now()->addMinute()->toIso8601String()
            ];

            $response = $this->postJson('/api/payment/session', $payload);

            $response->assertStatus(201)
                ->assertJson([
                    'status' => 'pending',
                ]);

            $this->assertDatabaseHas('payment_sessions', [
                'amount' => 150.50,
                'order_id' => 'ORD-67890',
                'callback_url' => 'https://storefront.example.com/payment/callback',
            ]);
        });

        it('validates required amount field', function () {
            $payload = [
                'orderId' => 'ORD-12345',
            ];

            $response = $this->postJson('/api/payment/session', $payload);

            $response->assertStatus(422)
                ->assertJsonValidationErrors('amount');
        });

        it('validates amount is numeric', function () {
            $payload = [
                'amount' => 'invalid',
                'orderId' => 'ORD-12345',
                'callbackUrl' => 'https://storefront.example.com/payment/callback',
            ];

            $response = $this->postJson('/api/payment/session', $payload);

            $response->assertStatus(422)
                ->assertJsonValidationErrors('amount');
        });

        it('validates amount minimum value', function () {
            $payload = [
                'amount' => 0,
                'orderId' => 'ORD-12345',
                'callbackUrl' => 'https://storefront.example.com/payment/callback',
            ];

            $response = $this->postJson('/api/payment/session', $payload);

            $response->assertStatus(422)
                ->assertJsonValidationErrors('amount');
        });

        it('validates amount maximum value', function () {
            $payload = [
                'amount' => 1000000,
                'orderId' => 'ORD-12345',
                'callbackUrl' => 'https://storefront.example.com/payment/callback',
            ];

            $response = $this->postJson('/api/payment/session', $payload);

            $response->assertStatus(422)
                ->assertJsonValidationErrors('amount');
        });

        it('validates required orderId field', function () {
            $payload = [
                'amount' => 99.99,
                'callbackUrl' => 'https://storefront.example.com/payment/callback',
            ];

            $response = $this->postJson('/api/payment/session', $payload);

            $response->assertStatus(422)
                ->assertJsonValidationErrors('orderId');
        });

        it('validates callbackUrl is a valid URL', function () {
            $payload = [
                'amount' => 99.99,
                'orderId' => 'ORD-12345',
                'callbackUrl' => 'not-a-valid-url',
            ];

            $response = $this->postJson('/api/payment/session', $payload);

            $response->assertStatus(422)
                ->assertJsonValidationErrors('callbackUrl');
        });
    });

    describe('GET /api/payment/session/{sessionId}', function () {
        it('retrieves a pending payment session', function () {
            $session = PaymentSession::factory()->create([
                'status' => PaymentSession::STATUS_PENDING,
            ]);

            $response = $this->getJson("/api/payment/session/{$session->id}");

            $response->assertStatus(200)
                ->assertJson([
                    'sessionId' => $session->id,
                    'amount' => (float) $session->amount,
                    'status' => 'pending',
                ])
                ->assertJsonMissingPath('paidAt')
                ->assertJsonMissingPath('errorCode');
        });

        it('retrieves a successful payment session with paidAt timestamp', function () {
            $session = PaymentSession::factory()->successful()->create();

            $response = $this->getJson("/api/payment/session/{$session->id}");

            $response->assertStatus(200)
                ->assertJson([
                    'sessionId' => $session->id,
                    'amount' => (float) $session->amount,
                    'status' => 'successful',
                ])
                ->assertJsonStructure([
                    'paidAt',
                ]);
        });

        it('retrieves a failed payment session with errorCode', function () {
            $session = PaymentSession::factory()->failed()->create([
                'error_code' => 'CARD_DECLINED',
            ]);

            $response = $this->getJson("/api/payment/session/{$session->id}");

            $response->assertStatus(200)
                ->assertJson([
                    'sessionId' => $session->id,
                    'amount' => (float) $session->amount,
                    'status' => 'failed',
                    'errorCode' => 'CARD_DECLINED',
                ])
                ->assertJsonMissingPath('paidAt');
        });

        it('returns 404 for non-existent session', function () {
            $nonExistentId = '00000000-0000-0000-0000-000000000000';

            $response = $this->getJson("/api/payment/session/{$nonExistentId}");

            $response->assertStatus(404)
                ->assertJson([
                    'message' => 'Payment session not found',
                ]);
        });
    });

    describe('DELETE /api/payment/session/{sessionId}', function () {
        it('cancels a pending payment session', function () {
            $session = PaymentSession::factory()->create([
                'status' => PaymentSession::STATUS_PENDING,
            ]);

            $response = $this->deleteJson("/api/payment/session/{$session->id}");

            $response->assertStatus(200)
                ->assertJson([
                    'sessionId' => $session->id,
                    'status' => 'failed',
                ]);

            $this->assertDatabaseHas('payment_sessions', [
                'id' => $session->id,
                'status' => PaymentSession::STATUS_FAILED,
                'error_code' => 'CANCELLED',
            ]);
        });

        it('returns 409 conflict when cancelling a successful session', function () {
            $session = PaymentSession::factory()->successful()->create();

            $response = $this->deleteJson("/api/payment/session/{$session->id}");

            $response->assertStatus(409)
                ->assertJson([
                    'message' => 'Only pending payment sessions can be cancelled',
                ]);

            $this->assertDatabaseHas('payment_sessions', [
                'id' => $session->id,
                'status' => PaymentSession::STATUS_SUCCESSFUL,
            ]);
        });

        it('returns 409 conflict when cancelling a failed session', function () {
            $session = PaymentSession::factory()->failed()->create();

            $response = $this->deleteJson("/api/payment/session/{$session->id}");

            $response->assertStatus(409)
                ->assertJson([
                    'message' => 'Only pending payment sessions can be cancelled',
                ]);

            $this->assertDatabaseHas('payment_sessions', [
                'id' => $session->id,
                'status' => PaymentSession::STATUS_FAILED,
            ]);
        });

        it('returns 404 for non-existent session', function () {
            $nonExistentId = '00000000-0000-0000-0000-000000000000';

            $response = $this->deleteJson("/api/payment/session/{$nonExistentId}");

            $response->assertStatus(404)
                ->assertJson([
                    'message' => 'Payment session not found',
                ]);
        });
    });
});
