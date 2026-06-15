<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class ExternalServiceController extends ApiController
{
    public function getCurrentWeather()
    {
        try {
            return $this->data(Http::timeout(2)->get('http://localhost:3000/api/v1/external-services/weather')->json('data'));
        } catch (\Throwable) {
            return $this->data(['weather' => 'clear', 'temperature' => 25]);
        }
    }

    public function getCurrentTraffic()
    {
        try {
            return $this->data(Http::timeout(2)->get('http://localhost:3000/api/v1/external-services/city')->json('data'));
        } catch (\Throwable) {
            return $this->data(['color_scale_of_corks' => 'green', 'number_scale' => 1]);
        }
    }
}
