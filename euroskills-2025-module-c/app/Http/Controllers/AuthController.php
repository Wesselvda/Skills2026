<?php

namespace App\Http\Controllers;

use App\Models\ApplicationUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'username' => "string|max:255|required",
            'password' => "string|max:255|required"
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "type" => "http://localhost:8080/problemtype/unauthorized",
                    "title" => "Validator failed",
                    "status" => 422,
                    "detail" => "Validation failed",
                    "instance" => "no idea",
                    "errors" => [
                        "additionalProp1" => []
                    ]
                ],
                422,
                [
                    "Content-Type" => "application/problem+json"
                ]
            );
        }

        $user = ApplicationUser::where('username', $request->input('username'))->first();

        if ($user) {
            if (Hash::check($request->input('password'), $user->password)) {  
                $token = null;

                while ($token === null) {
                    $generatedToken = Str::random(40);

                    if (!ApplicationUser::where('token', $generatedToken)->exists()) {
                        $token = $generatedToken;
                    }
                }

                $user->token = $token;
                $user->save();

                return response()->json([
                    "token" => $token,
                    "role" => $user->role
                ], 200);
            }
        }

        return response()->json(
            [
                "type" => "http://localhost:8080/problemtype/unauthorized",
                "title" => "Invalid credentials",
                "status" => 401,
                "detail" => "Invalid credentials",
                "instance" => "no idea",
                "errors" => [
                    "additionalProp1" => []
                ]
            ],
            401,
            [
                "Content-Type" => "application/problem+json"
            ]
        );
    }

    public function assignRole(Request $request) {
        if ($request->attributes->get('user')->role !== "admin") {
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
            'username' => "string|max:255|required",
            'role' => "string|in:user,operator,admin|required"
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "type" => "http://localhost:8080/problemtype/unauthorized",
                    "title" => "Validator failed",
                    "status" => 400,
                    "detail" => "Validation failed",
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

        if ($request->input('username') === $request->attributes->get('user')->username) {
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

        $user = ApplicationUser::where('username', $request->input('username'))->first();

        if (!$user) {
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

        $user->role = $request->input('role');
        $user->save();

        return response(null, 204);
    }
}
