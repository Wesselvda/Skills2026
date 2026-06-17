<?php

namespace App\Http\Controllers;

use App\Traits\DeterministicRandom;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Inventory', description: 'Physical store inventory and availability services')]
class InventoryController extends Controller
{
    use DeterministicRandom;

    private const array STORES = [
        ['storeId' => 1, 'name' => 'McPencil', 'address' => 'Marktplatz 1', 'city' => 'Neubrandenburg'],
        ['storeId' => 2, 'name' => 'Tholeo', 'address' => 'Juri-Gagarin-Ring 12', 'city' => 'Neubrandenburg'],
        ['storeId' => 3, 'name' => 'Paperleaf', 'address' => 'Ihlenfelder Straße 45', 'city' => 'Neubrandenburg'],
        ['storeId' => 4, 'name' => 'Scriptus', 'address' => 'Lange Straße 28', 'city' => 'Waren (Müritz)'],
        ['storeId' => 5, 'name' => 'InkSpine', 'address' => 'Strelitzer Straße 10', 'city' => 'Neustrelitz'],
    ];

    private function getAvailabilityStatus(int $storeId, int $bookId): array
    {
        $seed = 'availability:'.$storeId.':'.$bookId;
        $rand = $this->deterministicChance($seed);
        if ($rand < 0.15) {
            $status = 'out_of_stock';
            $quantity = 0;
        } elseif ($rand < 0.40) {
            $status = 'low';
            $quantity = ($this->deterministicRandom($seed.':low_qty') % 10) + 1;
        } else {
            $status = 'high';
            $quantity = ($this->deterministicRandom($seed.':high_qty') % 100) + 20;
        }

        $shouldShowQuantity = $this->deterministicChance($seed.':show_qty') < 0.6;

        return [
            'status' => $status,
            'quantity' => $shouldShowQuantity ? $quantity : null,
        ];
    }

    private function getErrorResponse(int $storeId, int $bookId): ?array
    {
        $rand = $this->deterministicChance('error:'.$storeId.':'.$bookId);

        if ($rand < 0.08) {
            return ['message' => 'Store not found'];
        }

        if ($rand >= 0.08 && $rand < 0.12) {
            return ['message' => 'Book not found'];
        }

        if ($rand >= 0.12 && $rand < 0.15) {
            return ['message' => 'Service temporarily unavailable'];
        }

        return null;
    }

    private function getDelayMs(int $storeId, int $bookId): int
    {
        $rand = $this->deterministicChance('delay:'.$storeId.':'.$bookId);

        return (int) ($rand * 3000) + 500;
    }

    #[Endpoint(
        title: 'Get stores for book',
        description: 'Returns a list of physical store locations that carry the specified book.'
    )]
    #[UrlParam('bookId', required: true, description: 'The ID of the book to check store availability for', example: 1)]
    #[Response([
        ['storeId' => 1, 'name' => 'Downtown', 'address' => '123 Main Street', 'city' => 'New York'],
        ['storeId' => 2, 'name' => 'Uptown', 'address' => '456 Oak Avenue', 'city' => 'Brooklyn'],
        ['storeId' => 3, 'name' => 'Midtown', 'address' => '789 Park Lane', 'city' => 'Manhattan'],
    ], 200)]
    public function getStores(int $bookId): JsonResponse
    {
        $stores = array_filter(self::STORES, fn ($store) => $this->deterministicChance('has:'.$store['storeId'].':'.$bookId) < 0.8);

        return response()->json(array_values($stores));
    }

    #[Endpoint(
        title: 'Get book availability at store',
        description: 'Returns the availability status and inventory count for a specific book at a specific store location.'
    )]
    #[UrlParam('storeId', required: true, description: 'The ID of the store', example: 1)]
    #[UrlParam('bookId', required: true, description: 'The ID of the book', example: 1)]
    #[Response([
        'storeId' => 1,
        'bookId' => 1,
        'availability' => 'high',
        'inventory' => 50,
    ], 200, description: 'High availability')]
    #[Response([
        'storeId' => 1,
        'bookId' => 1,
        'availability' => 'low',
        'inventory' => 5,
    ], 200, description: 'Low availability')]
    #[Response([
        'storeId' => 1,
        'bookId' => 1,
        'availability' => 'out_of_stock',
        'inventory' => 0,
    ], 200, description: 'Out of stock')]
    #[Response([
        'storeId' => 1,
        'bookId' => 1,
        'availability' => 'high',
        'inventory' => null,
    ], 200, description: 'Quantity hidden')]
    #[Response([
        'message' => 'Store not found',
    ], 404, description: 'Store not found')]
    #[Response([
        'message' => 'Book not found',
    ], 404, description: 'Book not found')]
    #[Response([
        'message' => 'Service temporarily unavailable',
    ], 503, description: 'Service unavailable')]
    public function getAvailability(int $storeId, int $bookId): JsonResponse
    {
        $errorResponse = $this->getErrorResponse($storeId, $bookId);

        if ($errorResponse !== null) {
            $statusCode = str_contains($errorResponse['message'], 'Service') ? 503 : 404;

            return response()->json($errorResponse, $statusCode);
        }

        $delayMs = $this->getDelayMs($storeId, $bookId);
        usleep($delayMs * 1000);

        $availabilityData = $this->getAvailabilityStatus($storeId, $bookId);

        return response()->json([
            'storeId' => $storeId,
            'bookId' => $bookId,
            'availability' => $availabilityData['status'],
            'inventory' => $availabilityData['quantity'],
        ]);
    }
}
