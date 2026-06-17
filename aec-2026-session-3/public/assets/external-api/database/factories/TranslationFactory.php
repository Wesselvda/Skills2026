<?php

namespace Database\Factories;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Translation>
 */
class TranslationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'message_key' => 'message_'.fake()->unique()->numerify('#####'),
            'language' => fake()->randomElement(['en', 'de', 'fr', 'es']),
            'message' => fake()->sentence(),
        ];
    }

    public function forMessage(string $messageKey, string $language, string $message): static
    {
        return $this->state(fn (array $attributes) => [
            'message_key' => $messageKey,
            'language' => $language,
            'message' => $message,
        ]);
    }
}
