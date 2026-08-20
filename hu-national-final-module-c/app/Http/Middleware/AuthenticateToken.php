<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateToken
{
    private string $secret = '38344ac35d91bfd0c8f43963b0ca188d2a039504e825ff968b0366855bdbca5b';

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authorization = $request->header('Authorization');

        if (! $authorization || ! str_starts_with($authorization, 'Bearer ')) {
            return $this->unauthorized();
        }

        $token = substr($authorization, 7);
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return $this->unauthorized();
        }

        $headerJson = $this->decodePart($parts[0]);
        $payloadJson = $this->decodePart($parts[1]);
        $signature = $this->decodePart($parts[2]);

        if ($headerJson === false || $payloadJson === false || $signature === false) {
            return $this->unauthorized();
        }

        $header = json_decode($headerJson, true);
        $payload = json_decode($payloadJson, true);

        if (! is_array($header) || ! is_array($payload)) {
            return $this->unauthorized();
        }

        $expectedSignature = hash_hmac('sha256', $parts[0].'.'.$parts[1], $this->secret, true);

        if (! hash_equals($expectedSignature, $signature)) {
            return $this->unauthorized();
        }

        if (! isset($payload['exp']) || time() >= (int) $payload['exp']) {
            return $this->unauthorized();
        }

        if (! isset($payload['sub'])) {
            return $this->unauthorized();
        }

        $request->attributes->set('userId', (int) $payload['sub']);
        $request->attributes->set('token', $token);

        return $next($request);
    }

    private function decodePart(string $part): string|false
    {
        $part = str_replace(['-', '_'], ['+', '/'], $part);
        $remainder = strlen($part) % 4;

        if ($remainder > 0) {
            $part .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode($part, true);
    }

    private function unauthorized()
    {
        return response()->json([
            'error' => 'Unauthorized',
            'code' => 'UNAUTHORIZED',
        ], 401);
    }
}
