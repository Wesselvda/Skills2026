<?php

namespace Database\Factories;

use App\Models\PaymentSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentSession>
 */
class PaymentSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => fake()->randomFloat(2, 1, 999),
            'order_id' => 'ORD-'.fake()->unique()->numerify('######'),
            'callback_url' => fake()->url(),
            'status' => PaymentSession::STATUS_PENDING,
            'expires_at' => now()->addDay(),
        ];
    }

    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentSession::STATUS_SUCCESSFUL,
            'paid_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentSession::STATUS_FAILED,
            'error_code' => fake()->randomElement(['CANCELLED', 'CARD_DECLINED', 'INSUFFICIENT_FUNDS', 'EXPIRED_CARD', 'FRAUD_SUSPECTED']),
        ]);
    }
}
