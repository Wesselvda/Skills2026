<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AlertController extends Controller
{
    public function alerts(Request $request) {
        return response()->json(
            [
                "type" => "http://localhost:8080/problemtype/unauthorized",
                "title" => "Resource not found",
                "status" => 404,
                "detail" => "Resource not found",
                "instance" => "no idea",
                "errors" => [
                    "additionalProp1" => []
                ]
            ],
            404,
            [
                "Content-Type" => "application/problem+json"
            ]
        );
    }

    

    public function ack(Request $request, $id) {
        return response()->json(
            [
                "type" => "http://localhost:8080/problemtype/unauthorized",
                "title" => "Resource not found",
                "status" => 404,
                "detail" => "Resource not found",
                "instance" => "no idea",
                "errors" => [
                    "additionalProp1" => []
                ]
            ],
            404,
            [
                "Content-Type" => "application/problem+json"
            ]
        );
    }
}
