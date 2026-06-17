<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class ExternalApi
{
    public function translate(string $text, string $sourceLanguage, string $targetLanguage): ?string
    {
        $result = $this->translateBatch([[
            'text' => $text,
            'sourceLanguage' => $sourceLanguage,
            'targetLanguage' => $targetLanguage,
        ]])[0] ?? null;

        if (($result['success'] ?? false) && isset($result['translation'])) {
            return $result['translation'];
        }

        return null;
    }

    public function translateBatch(array $texts): array
    {
        try {
            $response = $this->http()->post('/translation/batch', [
                'texts' => $texts,
            ]);
        } catch (Throwable) {
            return array_map(fn () => ['success' => false], $texts);
        }

        if (! $response->successful()) {
            return array_map(fn () => ['success' => false], $texts);
        }

        return $response->json() ?? array_map(fn () => ['success' => false], $texts);
    }

    public function storesForBook(int $bookId): array
    {
        try {
            $response = $this->http()->get("/inventory/stores/for-book/{$bookId}");
        } catch (Throwable) {
            return [];
        }

        if (! $response->successful()) {
            return [];
        }

        return $response->json() ?? [];
    }

    public function storeAvailability(int $storeId, int $bookId): ?array
    {
        try {
            $response = $this->http()->get("/inventory/stores/{$storeId}/books/{$bookId}");
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        return $response->json();
    }

    public function moderate(string $text): array
    {
        try {
            $response = $this->http()->post('/moderation/check', [
                'text' => $text,
            ]);
        } catch (Throwable) {
            return [];
        }

        if (! $response->successful()) {
            return [];
        }

        return $response->json('flaggedWords') ?? [];
    }

    public function discountRules(): array
    {
        try {
            $response = $this->http()->get('/discounts');
        } catch (Throwable) {
            return [];
        }

        if (! $response->successful()) {
            return [];
        }

        return $response->json() ?? [];
    }

    public function createPaymentSession(float $amount, string $orderId, string $expiresAt, string $callbackUrl): ?array
    {
        try {
            $response = $this->http()->post('/payment/session', [
                'amount' => $amount,
                'orderId' => $orderId,
                'expiresAt' => $expiresAt,
                'callbackUrl' => $callbackUrl,
            ]);
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        return $response->json();
    }

    public function paymentSession(string $sessionId): ?array
    {
        try {
            $response = $this->http()->get("/payment/session/{$sessionId}");
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        return $response->json();
    }

    public function deletePaymentSession(string $sessionId): void
    {
        try {
            $this->http()->delete("/payment/session/{$sessionId}");
        } catch (Throwable) {
        }
    }

    private function http()
    {
        return Http::baseUrl($this->baseUrl())
            ->connectTimeout(1)
            ->timeout(12);
    }

    private function baseUrl(): string
    {
        return "http://localhost:3000/api";
    }

}
