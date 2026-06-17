<?php

use App\Models\Translation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Translation Service', function () {
    beforeEach(function () {
        Translation::create([
            'message_key' => 'greeting_hello',
            'language' => 'en',
            'message' => 'Hello',
        ]);

        Translation::create([
            'message_key' => 'greeting_hello',
            'language' => 'de',
            'message' => 'Hallo',
        ]);

        Translation::create([
            'message_key' => 'greeting_hello',
            'language' => 'fr',
            'message' => 'Bonjour',
        ]);

        Translation::create([
            'message_key' => 'farewell_goodbye',
            'language' => 'en',
            'message' => 'Goodbye',
        ]);

        Translation::create([
            'message_key' => 'farewell_goodbye',
            'language' => 'de',
            'message' => 'Auf Wiedersehen',
        ]);
    });

    describe('POST /api/translation/translate', function () {
        it('translates text successfully', function () {
            $payload = [
                'text' => 'Hello',
                'sourceLanguage' => 'en',
                'targetLanguage' => 'de',
            ];

            $response = $this->postJson('/api/translation/translate', $payload);

            $response->assertStatus(200)
                ->assertJson([
                    'translation' => 'Hallo',
                ]);
        });

        it('returns 404 when message not found in source language', function () {
            $payload = [
                'text' => 'Unknown Text',
                'sourceLanguage' => 'en',
                'targetLanguage' => 'de',
            ];

            $response = $this->postJson('/api/translation/translate', $payload);

            $response->assertStatus(404)
                ->assertJson([
                    'message' => 'Could not translate the text',
                ]);
        });

        it('returns 404 when target language not available', function () {
            $payload = [
                'text' => 'Goodbye',
                'sourceLanguage' => 'en',
                'targetLanguage' => 'fr',
            ];

            $response = $this->postJson('/api/translation/translate', $payload);

            $response->assertStatus(404)
                ->assertJson([
                    'message' => 'Could not translate the text',
                ]);
        });

        it('validates required fields', function () {
            $payload = [
                'sourceLanguage' => 'en',
                'targetLanguage' => 'de',
            ];

            $response = $this->postJson('/api/translation/translate', $payload);

            $response->assertStatus(422)
                ->assertJsonValidationErrors('text');
        });

        it('simulates deterministic failures', function () {
            $payload = [
                'text' => 'Hello',
                'sourceLanguage' => 'en',
                'targetLanguage' => 'de',
            ];

            $response = $this->postJson('/api/translation/translate', $payload);

            $status = $response->status();
            expect(in_array($status, [200, 404]))->toBeTrue();
        });
    });

    describe('POST /api/translation/batch', function () {
        it('translates multiple texts successfully', function () {
            $payload = [
                'texts' => [
                    [
                        'text' => 'Hello',
                        'sourceLanguage' => 'en',
                        'targetLanguage' => 'de',
                    ],
                    [
                        'text' => 'Goodbye',
                        'sourceLanguage' => 'en',
                        'targetLanguage' => 'de',
                    ],
                ],
            ];

            $response = $this->postJson('/api/translation/batch', $payload);

            $response->assertStatus(200)
                ->assertJsonStructure([
                    '*' => ['success', 'translation'],
                ]);

            $results = $response->json();
            expect($results)->toHaveCount(2);
        });

        it('returns mixed success and failure results', function () {
            $payload = [
                'texts' => [
                    [
                        'text' => 'Hello',
                        'sourceLanguage' => 'en',
                        'targetLanguage' => 'de',
                    ],
                    [
                        'text' => 'Unknown Text',
                        'sourceLanguage' => 'en',
                        'targetLanguage' => 'de',
                    ],
                ],
            ];

            $response = $this->postJson('/api/translation/batch', $payload);

            $response->assertStatus(200);

            $results = $response->json();
            expect($results)->toHaveCount(2);

            $firstResult = $results[0];
            $secondResult = $results[1];

            expect($firstResult['success'])->toBeTrue()
                ->and($firstResult['translation'])->toEqual('Hallo')
                ->and($secondResult['success'])->toBeFalse()
                ->and($secondResult['message'])->toEqual('Could not translate the text');
        });

        it('validates texts array is not empty', function () {
            $payload = [
                'texts' => [],
            ];

            $response = $this->postJson('/api/translation/batch', $payload);

            $response->assertStatus(422)
                ->assertJsonValidationErrors('texts');
        });

        it('validates required fields in each text object', function () {
            $payload = [
                'texts' => [
                    [
                        'sourceLanguage' => 'en',
                        'targetLanguage' => 'de',
                    ],
                ],
            ];

            $response = $this->postJson('/api/translation/batch', $payload);

            $response->assertStatus(422)
                ->assertJsonValidationErrors('texts.0.text');
        });
    });
});
