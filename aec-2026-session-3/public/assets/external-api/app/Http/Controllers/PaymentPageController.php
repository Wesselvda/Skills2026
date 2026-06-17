<?php

namespace App\Http\Controllers;

use App\Models\PaymentSession;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentPageController extends Controller
{
    public function show(string $sessionId)
    {
        $paymentSession = PaymentSession::where('id', $sessionId)->firstOrFail();

        if (!$paymentSession->isPending()) {
            return redirect($paymentSession->callback_url ?? '/');
        }

        return view('payment', [
            'sessionId' => $paymentSession->id,
            'amount' => $paymentSession->amount,
            'orderId' => $paymentSession->order_id,
        ]);
    }

    public function process(Request $request, string $sessionId)
    {
        $validator = Validator::make($request->all(), [
            'cardHolderName' => ['required', 'string', 'max:255'],
            'cardNumber' => ['required', 'regex:/^\d{16}$/'],
            'expiryMonth' => ['required', 'string', 'regex:/^(0[1-9]|1[0-2])$/'],
            'expiryYear' => ['required', 'string', 'regex:/^\d{4}$/'],
            'cvc' => ['required', 'regex:/^\d{3}$/'],
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->filled(['expiryMonth', 'expiryYear'])) {
                $month = (int) $request->expiryMonth;
                $year = (int) $request->expiryYear;

                try {
                    $expiryDate = Carbon::create($year, $month, 1)->endOfMonth();

                    if ($expiryDate < now()) {
                        $validator->errors()->add('expiryDate', 'The card has expired.');
                    }
                } catch (Exception $e) {
                    // Optional: handle invalid date edge cases
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withInput()
                ->withErrors($validator);
        }

        $paymentSession = PaymentSession::where('id', $sessionId)->firstOrFail();

        if ($paymentSession->isPending()) {
            $paymentSession->markAsSuccessful();
        }

        return redirect($paymentSession->callback_url ?? '/');
    }

    public function fail(Request $request, string $sessionId)
    {
        $paymentSession = PaymentSession::where('id', $sessionId)->firstOrFail();

        if ($paymentSession->isPending()) {
            $errorCodes = ['CARD_DECLINED', 'INSUFFICIENT_FUNDS', 'EXPIRED_CARD', 'FRAUD_SUSPECTED'];
            $randomErrorCode = $errorCodes[array_rand($errorCodes)];
            $paymentSession->markAsFailed($request->query('code', $randomErrorCode));
        }

        return redirect($paymentSession->callback_url ?? '/');
    }
}
