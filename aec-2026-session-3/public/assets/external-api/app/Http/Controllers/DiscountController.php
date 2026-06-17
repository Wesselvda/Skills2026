<?php

namespace App\Http\Controllers;

use App\Traits\DeterministicRandom;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Discounts', description: 'Discount rules and calculations for shopping carts')]
class DiscountController extends Controller
{
    use DeterministicRandom;

    private const array BASE_DISCOUNT_RULES = [
        [
            'id' => 'BULK001',
            'name' => 'Bulk Purchase Discount',
            'conditions' => [
                'minQuantity' => 3,
                'minTotalPrice' => null,
                'timespan' => 'expired',
            ],
            'discount' => [
                'type' => 'percentage',
                'value' => 10.0,
            ],
        ],
        [
            'id' => 'SEASONAL001',
            'name' => 'Seasonal Promotion',
            'conditions' => [
                'minQuantity' => 1,
                'minTotalPrice' => 50.00,
                'timespan' => 'soon',
            ],
            'discount' => [
                'type' => 'percentage',
                'value' => 15.0,
            ],
        ],
        [
            'id' => 'SHIPPING001',
            'name' => 'Shipping Discount',
            'conditions' => [
                'minQuantity' => 1,
                'minTotalPrice' => 25.00,
                'timespan' => null,
            ],
            'discount' => [
                'type' => 'fixed',
                'value' => 5.00,
            ],
        ],
        [
            'id' => 'FLASH001',
            'name' => 'Flash Sale',
            'conditions' => [
                'minQuantity' => 2,
                'minTotalPrice' => 100.00,
                'timespan' => 'current',
            ],
            'discount' => [
                'type' => 'percentage',
                'value' => 25.0,
            ],
        ],
        [
            'id' => 'SAVE001',
            'name' => 'Special Savings',
            'conditions' => [
                'minQuantity' => null,
                'minTotalPrice' => 75.00,
                'timespan' => 'expired',
            ],
            'discount' => [
                'type' => 'fixed',
                'value' => 10.00,
            ],
        ],
        [
            'id' => 'VOLUME001',
            'name' => 'Volume Discount Tier 1',
            'conditions' => [
                'minQuantity' => 5,
                'minTotalPrice' => null,
                'timespan' => null,
            ],
            'discount' => [
                'type' => 'percentage',
                'value' => 12.0,
            ],
        ],
        [
            'id' => 'VOLUME002',
            'name' => 'Volume Discount Tier 2',
            'conditions' => [
                'minQuantity' => 10,
                'minTotalPrice' => null,
                'timespan' => null,
            ],
            'discount' => [
                'type' => 'percentage',
                'value' => 20.0,
            ],
        ],
        [
            'id' => 'COMBO001',
            'name' => 'Combo Deal',
            'conditions' => [
                'minQuantity' => 3,
                'minTotalPrice' => 60.00,
                'timespan' => null,
            ],
            'discount' => [
                'type' => 'fixed',
                'value' => 15.00,
            ],
        ],
    ];

    private function assignTimeRange(array $rule): array
    {
        $type = $rule['conditions']['timespan'];
        $now = Carbon::now();
        $rand1 = $this->deterministicRandom($rule['id'].':from') % 120;
        $rand2 = $this->deterministicRandom($rule['id'].':to') % 120;
        if ($type == 'expired') {
            $range = [
                'start' => $now->copy()->subMinutes($rand1 + $rand2 + 15)->toIso8601ZuluString(),
                'end' => $now->copy()->subMinutes($rand1 + 15)->toIso8601ZuluString(),
            ];
        } elseif ($type == 'current') {
            $range = [
                'start' => $now->copy()->subMinutes($rand1 + 15)->toIso8601ZuluString(),
                'end' => $now->copy()->addMinutes($rand2 + 15)->toIso8601ZuluString(),
            ];
        } elseif ($type == 'soon') {
            $range = [
                'start' => $now->copy()->addMinutes($rand1 + 15)->toIso8601ZuluString(),
                'end' => $now->copy()->addMinutes($rand1 + $rand2 + 15)->toIso8601ZuluString(),
            ];
        } else {
            $range = null;
        }

        $copy = $rule;
        $copy['conditions']['timespan'] = $range;

        return $copy;
    }

    #[Endpoint(
        title: 'Get discount rules',
        description: 'Retrieves all discount rules that can be applied to shopping carts. Each rule defines conditions for application and the discount calculation method.'
    )]
    #[Response([
        [
            'id' => 'BULK001',
            'name' => 'Bulk Purchase Discount',
            'conditions' => [
                'minQuantity' => 3,
                'minTotalPrice' => null,
                'timespan' => null,
            ],
            'discount' => [
                'type' => 'fixed',
                'value' => 10.0,
            ],
        ],
        [
            'id' => 'SEASONAL001',
            'name' => 'Seasonal Promotion',
            'conditions' => [
                'minQuantity' => 1,
                'minTotalPrice' => 50.00,
                'timespan' => [
                    'start' => '2024-12-01T00:00:00Z',
                    'end' => '2024-12-31T23:59:59Z',
                ],
            ],
            'discount' => [
                'type' => 'percentage',
                'value' => 15.0,
            ],
        ],
    ], 200)]
    public function getRules(): JsonResponse
    {
        $ruleSetIndices = [
            [0, 1],
            [0, 3],
            [4, 2],
            [0, 4],
            [5, 6, 7],
            [2, 3],
            [4, 7],
            [1, 5],
        ];

        $selectedIndices = $ruleSetIndices[$this->deterministicRandom('ruleset') % count($ruleSetIndices)];

        $ruleSet = [];
        foreach ($selectedIndices as $index) {
            $ruleSet[] = $this->assignTimeRange(self::BASE_DISCOUNT_RULES[$index]);
        }

        return response()->json($ruleSet);
    }
}
