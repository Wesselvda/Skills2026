<?php

namespace App\Http\Controllers;

use App\Models\LogEntry;
use App\Models\Turbine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TurbineController extends Controller
{
    private function updateTurbineList() {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer SECRET_TOKEN_123'
        ])->get("http://localhost:4000/turbines");

        if ($response->ok()) {
            $json = $response->json();
            $data = $json['data'];

            foreach ($data as $turbine) {
                Turbine::updateOrCreate([
                    'id' => $turbine['id'],
                ], [
                    'name' => $turbine['name'],
                    'lat' => $turbine['location']['lat'],
                    'lng' => $turbine['location']['lng'],
                    'rpm' => $turbine['rpm'],
                    'powerMw' => $turbine['powerMw'],
                    'yaw' => $turbine['yaw'],
                    'pitch' => $turbine['pitch'],
                    'temperature' => $turbine['temperature'],
                    'status' => $turbine['status'],
                ]);
            }
        }

        return $response;
    }

    private function formatValue($value, $freshness, $lastUpdated) {
        return [
            "freshness" => $value === null ? "missing" : $freshness,
            "lastUpdated" => $lastUpdated,
            "value" => $value
        ];
    }

    public function turbines(Request $request) {
        $this->updateTurbineList();

        $turbines = Turbine::select([
            'id',
            'name',
            'lat',
            'lng',
            'status'
        ])->get()->map(function ($turbine) {
            return [
                'id' => $turbine->id,
                'name' => $turbine->name,
                'location' => [
                    'lat' => $turbine->lat,
                    'lng' => $turbine->lng,
                ],
                'status' => $turbine->status
            ];
        });

        return response()->json($turbines, 200);
    }

    public function status(Request $request, int $id) {
        $response = $this->updateTurbineList();

        $turbine = Turbine::where('id', $id)->first();

        if (!$turbine) {
            return response()->json(
                [
                    "type" => "http://localhost:8080/problemtype/unauthorized",
                    "title" => "Resource not found",
                    "status" => 599,
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

        $freshness = $response->ok() ? "live" : "cached";
        
        return response()->json([
            "freshness" => $freshness,
            "lastUpdated" => $turbine->updated_at,
            "id" => $turbine->id,
            "name" => $turbine->name,
            "rpm" => $this->formatValue($turbine->rpm, $freshness, $turbine->updated_at),
            "powerMw" => $this->formatValue($turbine->powerMw, $freshness, $turbine->updated_at),
            "yaw" => $this->formatValue($turbine->yaw, $freshness, $turbine->updated_at),
            "pitch" => $this->formatValue($turbine->pitch, $freshness, $turbine->updated_at),
            "temperature" => $this->formatValue($turbine->temperature, $freshness, $turbine->updated_at),
            "status" => $this->formatValue($turbine->status, $freshness, $turbine->updated_at),
        ], 200);
    }
    

    public function actions(Request $request, int $id) {
        return response()->json(
            [
                "type" => "http://localhost:8080/problemtype/unauthorized",
                "title" => "Resource not found",
                "status" => 599,
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

    

    public function control(Request $request, int $id) {
        if (!in_array($request->attributes->get('user')->role, ['operator', 'admin'])) {
            return response()->json(
                [
                    "type" => "http://localhost:8080/problemtype/unauthorized",
                    "title" => "Insufficient privileges",
                    "status" => 403,
                    "detail" => "Insufficient privileges",
                    "instance" => "no idea",
                    "errors" => [
                        "additionalProp1" => []
                    ]
                ],
                403,
                [
                    "Content-Type" => "application/problem+json"
                ]
            );
        }

        $validator = Validator::make($request->all(), [
            'pitch' => "numeric|min:-90|max:90|required",
            'yaw' => "numeric|min:0|max:360|required",
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "type" => "http://localhost:8080/problemtype/unauthorized",
                    "title" => "Request failed validation",
                    "status" => 400,
                    "detail" => "Request failed validation",
                    "instance" => "no idea",
                    "errors" => [
                        "additionalProp1" => []
                    ]
                ],
                400,
                [
                    "Content-Type" => "application/problem+json"
                ]
            );
        }

        $this->updateTurbineList();
        
        $turbine = Turbine::where('id', $id)->first();

        if (!$turbine) {
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

        if ($turbine->status !== "started") {
            return response()->json(
                [
                    "type" => "http://localhost:8080/problemtype/unauthorized",
                    "title" => "Request conflicts with current resource state",
                    "status" => 409,
                    "detail" => "Request conflicts with current resource state",
                    "instance" => "no idea",
                    "errors" => [
                        "additionalProp1" => []
                    ]
                ],
                409,
                [
                    "Content-Type" => "application/problem+json"
                ]
            );
        }

        $turbine->yaw = $request->input('yaw');
        $turbine->pitch = $request->input('pitch');

        $turbine->save();

        return response()->json([
            'status' => 'success'
        ], 200);
    }

    public function start(Request $request, int $id) {
        if (!in_array($request->attributes->get('user')->role, ['operator', 'admin'])) {
            return response()->json(
                [
                    "type" => "http://localhost:8080/problemtype/unauthorized",
                    "title" => "Insufficient privileges",
                    "status" => 403,
                    "detail" => "Insufficient privileges",
                    "instance" => "no idea",
                    "errors" => [
                        "additionalProp1" => []
                    ]
                ],
                403,
                [
                    "Content-Type" => "application/problem+json"
                ]
            );
        }

        $this->updateTurbineList();
        
        $turbine = Turbine::where('id', $id)->first();

        if (!$turbine) {
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

        if ($turbine->status === "started") {
            return response()->json(
                [
                    "type" => "http://localhost:8080/problemtype/unauthorized",
                    "title" => "Request conflicts with current resource state",
                    "status" => 409,
                    "detail" => "Request conflicts with current resource state",
                    "instance" => "no idea",
                    "errors" => [
                        "additionalProp1" => []
                    ]
                ],
                409,
                [
                    "Content-Type" => "application/problem+json"
                ]
            );
        }

        $postRequest = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer SECRET_TOKEN_123'
        ])->post("http://localhost:4000/turbines/{$turbine->id}/status", [
            "status" => "started"
        ]);

        if (!$postRequest->ok()) {
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

        return response()->json([
            'status' => 'success'
        ], 200);
    }
    

    public function shutdown(Request $request, int $id) {
        if (!in_array($request->attributes->get('user')->role, ['operator', 'admin'])) {
            return response()->json(
                [
                    "type" => "http://localhost:8080/problemtype/unauthorized",
                    "title" => "Insufficient privileges",
                    "status" => 403,
                    "detail" => "Insufficient privileges",
                    "instance" => "no idea",
                    "errors" => [
                        "additionalProp1" => []
                    ]
                ],
                403,
                [
                    "Content-Type" => "application/problem+json"
                ]
            );
        }

        $this->updateTurbineList();
        
        $turbine = Turbine::where('id', $id)->first();

        if (!$turbine) {
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

        if ($turbine->status === "shutdown") {
            return response()->json(
                [
                    "type" => "http://localhost:8080/problemtype/unauthorized",
                    "title" => "Request conflicts with current resource state",
                    "status" => 409,
                    "detail" => "Request conflicts with current resource state",
                    "instance" => "no idea",
                    "errors" => [
                        "additionalProp1" => []
                    ]
                ],
                409,
                [
                    "Content-Type" => "application/problem+json"
                ]
            );
        }

        $postRequest = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer SECRET_TOKEN_123'
        ])->post("http://localhost:4000/turbines/{$turbine->id}/status", [
            "status" => "shutdown"
        ]);

        if (!$postRequest->ok()) {
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

        return response()->json([
            'status' => 'success'
        ], 200);
    }

    

    public function maintenance(Request $request, int $id) {
        if (!in_array($request->attributes->get('user')->role, ['operator', 'admin'])) {
            return response()->json(
                [
                    "type" => "http://localhost:8080/problemtype/unauthorized",
                    "title" => "Insufficient privileges",
                    "status" => 403,
                    "detail" => "Insufficient privileges",
                    "instance" => "no idea",
                    "errors" => [
                        "additionalProp1" => []
                    ]
                ],
                403,
                [
                    "Content-Type" => "application/problem+json"
                ]
            );
        }

        $this->updateTurbineList();
        
        $turbine = Turbine::where('id', $id)->first();

        if (!$turbine) {
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

        if ($turbine->status === "maintenance" || $turbine->status === "started") {
            return response()->json(
                [
                    "type" => "http://localhost:8080/problemtype/unauthorized",
                    "title" => "Request conflicts with current resource state",
                    "status" => 409,
                    "detail" => "Request conflicts with current resource state",
                    "instance" => "no idea",
                    "errors" => [
                        "additionalProp1" => []
                    ]
                ],
                409,
                [
                    "Content-Type" => "application/problem+json"
                ]
            );
        }

        $postRequest = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer SECRET_TOKEN_123'
        ])->post("http://localhost:4000/turbines/{$turbine->id}/status", [
            "status" => "maintenance"
        ]);

        if (!$postRequest->ok()) {
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

        return response()->json([
            'status' => 'success'
        ], 200);
    }

    

    public function logs(Request $request, int $id) {
        if (!in_array($request->attributes->get('user')->role, ['operator', 'admin'])) {
            return response()->json(
                [
                    "type" => "http://localhost:8080/problemtype/unauthorized",
                    "title" => "Insufficient privileges",
                    "status" => 403,
                    "detail" => "Insufficient privileges",
                    "instance" => "no idea",
                    "errors" => [
                        "additionalProp1" => []
                    ]
                ],
                403,
                [
                    "Content-Type" => "application/problem+json"
                ]
            );
        }

        $this->updateTurbineList();
        
        $turbine = Turbine::where('id', $id)->first();

        if (!$turbine) {
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

        $logRequest = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer SECRET_TOKEN_123'
        ])->get("http://localhost:4000/turbines/{$turbine->id}/logs");

        if (!$logRequest->ok()) {
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

        $lines = explode("\n", $logRequest->body());

        $multilineLogs = [];

        foreach ($lines as $line) {
            if (str_contains($line, "[") && str_contains($line, "]")) {
                array_push($multilineLogs, $line);
            } else {
                $multilineLogs[count($multilineLogs) - 1] .= "\n".$line;
            }
        }

        foreach ($multilineLogs as $logMessage) {
            $timestamp = explode(" ", $logMessage)[0];
            $level = explode("]", explode("[", $logMessage)[1])[0];
            $message = explode("] ", $logMessage)[1];

            LogEntry::updateOrCreate([
                'timestamp' => $timestamp,
                'level' => $level,
                'message' => $message,
                'turbineId' => $turbine->id,
            ]);
        }

        return response()->json([
            'turbineId' => $turbine->id,
            'entries' => LogEntry::where('turbineId', $turbine->id)->orderBy('timestamp', 'asc')->get()->map(function ($entry) {
                return [
                    'timestamp' => $entry->timestamp,
                    'level' => $entry->level,
                    'message' => $entry->message,
                ];
            })
        ], 200);
    }
}
