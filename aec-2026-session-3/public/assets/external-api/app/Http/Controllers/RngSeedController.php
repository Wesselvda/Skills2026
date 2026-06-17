<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RngSeedController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate(['seed' => 'required|string']);

        cache()->put('rng_global_seed', $validated['seed']);

        $currentSeed = cache()->get('rng_global_seed');

        return response()->json([
            'seed' => $currentSeed,
        ]);
    }
}
