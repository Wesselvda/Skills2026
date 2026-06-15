<?php

namespace App\Http\Controllers;

use App\Models\Bicycle;
use App\Models\Tariff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TariffController extends ApiController
{
    public function getBicycleTariffs(Request $request, string $bicycleId)
    {
        $user = $this->requireUser($request);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        $bicycle = Bicycle::find($bicycleId);

        if (! $bicycle) {
            return $this->fail('Bicycle not found', 404, 'Not Found');
        }

        return $this->data(Tariff::query()
            ->where('category_id', $bicycle->category_id)
            ->whereNull('deleted_at')
            ->get()
            ->map(fn (Tariff $tariff) => $this->formatTariff($tariff)));
    }

    public function getCurrentPrice(Request $request, string $bicycleId, string $tariffId)
    {
        $user = $this->requireUser($request);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        $bicycle = Bicycle::find($bicycleId);

        if (! $bicycle) {
            return $this->fail('Bicycle not found', 404, 'Not Found');
        }

        $tariff = Tariff::where('id', $tariffId)->where('category_id', $bicycle->category_id)->first();

        if (! $tariff) {
            return $this->fail('Tariff not found', 404, 'Not Found');
        }

        return $this->data(['price' => $this->currentPrice($tariff)]);
    }

    private function formatTariff(Tariff $tariff)
    {
        $data = [
            'id' => $tariff->id,
            'name' => $tariff->name,
            'type' => $tariff->type,
            'price' => $tariff->base_price,
        ];

        if ($tariff->type === 'DYNAMIC') {
            $data['additionalPrices'] = [
                'min' => $tariff->min_price,
                'max' => $tariff->max_price,
            ];
        }

        return $data;
    }

    private function currentPrice(Tariff $tariff)
    {
        if ($tariff->type !== 'DYNAMIC') {
            return $tariff->base_price;
        }

        try {
            $traffic = Http::timeout(2)->get('http://localhost:3000/api/v1/external-services/city')->json('data');
        } catch (\Throwable) {
            $traffic = null;
        }

        $multipliers = ['green' => 0.9, 'yellow' => 1, 'red' => 1.2, 'black' => 1.5];
        $numberScale = (int) ($traffic['number_scale'] ?? 0);
        $colorMultiplier = $multipliers[$traffic['color_scale_of_corks'] ?? 'yellow'] ?? 1;
        $price = (int) round($tariff->base_price * (1 + $numberScale / 100) * $colorMultiplier);

        return max($tariff->min_price, min($price, $tariff->max_price));
    }
}
