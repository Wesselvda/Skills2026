<?php

namespace App\Http\Controllers;

class HealthController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->utc()->format('Y-m-d\TH:i:s.v\Z'),
            'version' => '1.0.0',
        ]);
    }
}
