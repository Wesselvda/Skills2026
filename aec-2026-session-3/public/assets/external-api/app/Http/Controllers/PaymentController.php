<?php

namespace App\Http\Controllers;

use App\Models\PaymentSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Payment', description: 'Payment processing services for checkout transactions')]
class PaymentController extends Controller
{
    #[Endpoint(
        title: 'Create payment session',
        description: 'Creates a new payment session for processing a transaction. Returns a payment URL that the customer can use to complete the payment.',
    )]
    #[BodyParam(name: 'amount', required: true, description: 'The payment amount (min: 0.01, max: 999999.99)', example: 99.99, type: 'number')]
    #[BodyParam(name: 'orderId', required: true, description: 'The order identifier (max: 255 characters)', example: 'ORD-12345', type: 'string')]
    #[BodyParam(name: 'expiresAt', required: true, description: 'The expiry timestamp (must be in the future)', example: '2026-05-14T10:05:26.958Z', type: 'string')]
    #[BodyParam(name: 'callbackUrl', required: true, description: 'URL to redirect after payment completion (must be a valid URL)', example: 'https://storefront.example.com/payment/callback', type: 'string')]
    #[Response(status: 201, description: 'Created', content: [
        'sessionId' => 'c132e968-0901-40a1-9038-c1ed48b724e0',
        'paymentUrl' => 'https://example.com/payment/c132e968-0901-40a1-9038-c1ed48b724e0',
        'status' => 'pending',
        'expiresAt' => '2026-05-14T10:05:26.958Z',
    ])]
    #[Response(status: 422, description: 'Validation error', content: [
        'message' => 'The amount field is required.',
        'errors' => [
            'amount' => ['The amount field is required.'],
        ],
    ])]
    public function createSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'orderId' => ['required', 'string', 'max:255'],
            'expiresAt' => ['required', 'date', 'after:now'],
            'callbackUrl' => ['required', 'url', 'max:255'],
        ]);

        $paymentSession = PaymentSession::create([
            'amount' => $validated['amount'],
            'order_id' => $validated['orderId'],
            'expires_at' => $validated['expiresAt'],
            'callback_url' => $validated['callbackUrl'],
            'status' => PaymentSession::STATUS_PENDING,
        ]);

        return response()->json([
            'sessionId' => $paymentSession->id,
            'paymentUrl' => config('app.url') . '/payment/' . $paymentSession->id,
            'status' => $paymentSession->status,
        ], 201);
    }

    #[Endpoint(
        title: 'Get payment session',
        description: 'Retrieves the current status and details of a payment session.',
    )]
    #[UrlParam(name: 'sessionId', type: 'string', required: true, description: 'The payment session UUID', example: 'c132e968-0901-40a1-9038-c1ed48b724e0')]
    #[Response(status: 200, description: 'Pending session', content: [
        'sessionId' => 'c132e968-0901-40a1-9038-c1ed48b724e0',
        'amount' => 99.99,
        'status' => 'pending',
    ])]
    #[Response(status: 200, description: 'Successful session', content: [
        'sessionId' => 'c132e968-0901-40a1-9038-c1ed48b724e0',
        'amount' => 99.99,
        'status' => 'successful',
        'paidAt' => '2024-01-15T11:30:00Z',
    ])]
    #[Response(status: 200, description: 'Failed session', content: [
        'sessionId' => 'c132e968-0901-40a1-9038-c1ed48b724e0',
        'amount' => 99.99,
        'status' => 'failed',
        'errorCode' => 'CANCELLED',
    ])]
    #[Response(status: 404, description: 'Not found', content: [
        'message' => 'Payment session not found',
    ])]
    public function getSession(string $sessionId): JsonResponse
    {
        $paymentSession = PaymentSession::where('id', $sessionId)->first();

        if (!$paymentSession) {
            return response()->json([
                'message' => 'Payment session not found',
            ], 404);
        }

        $response = [
            'sessionId' => $paymentSession->id,
            'amount' => (float)$paymentSession->amount,
            'status' => $paymentSession->status,
        ];

        if ($paymentSession->isSuccessful()) {
            $response['paidAt'] = $paymentSession->paid_at->toIso8601String();
        }

        if ($paymentSession->isFailed()) {
            $response['errorCode'] = $paymentSession->error();
        }

        return response()->json($response);
    }

    #[Endpoint(
        title: 'Cancel payment session',
        description: 'Cancels an existing payment session. Only pending sessions can be cancelled.',
    )]
    #[UrlParam(name: 'sessionId', required: true, description: 'The payment session UUID', example: 'c132e968-0901-40a1-9038-c1ed48b724e0', type: 'string')]
    #[Response(status: 200, description: 'Cancelled', content: [
        'sessionId' => 'c132e968-0901-40a1-9038-c1ed48b724e0',
        'status' => 'failed',
    ])]
    #[Response(status: 404, description: 'Not found', content: [
        'message' => 'Payment session not found',
    ])]
    #[Response(status: 409, description: 'Cannot cancel (not pending)', content: [
        'message' => 'Only pending payment sessions can be cancelled',
    ])]
    public function deleteSession(string $sessionId): JsonResponse
    {
        $paymentSession = PaymentSession::where('id', $sessionId)->first();

        if (!$paymentSession) {
            return response()->json([
                'message' => 'Payment session not found',
            ], 404);
        }

        if (!$paymentSession->isPending()) {
            return response()->json([
                'message' => 'Only pending payment sessions can be cancelled',
            ], 409);
        }

        $paymentSession->markAsCancelled();

        return response()->json([
            'sessionId' => $paymentSession->id,
            'status' => $paymentSession->status,
        ]);
    }
}
