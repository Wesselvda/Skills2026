<?php

namespace App\Http\Controllers;

use App\Models\BalanceHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PayController extends ApiController
{
    public function replenishment(Request $request)
    {
        $user = $this->requireUser($request);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        $validated = $request->validate([
            'type' => ['required', 'in:top-up,withdraw'],
            'amount' => ['required', 'integer', 'min:1', 'max:100000'],
        ]);

        $balance = $validated['type'] === 'top-up'
            ? $user->balance + $validated['amount']
            : $user->balance - $validated['amount'];

        if ($balance < 0) {
            return $this->fail('The balance cannot be less than zero', 409, 'Conflict');
        }

        $user->update(['balance' => $balance]);
        BalanceHistory::create([
            'id' => (string) Str::uuid(),
            'type' => $validated['type'] === 'top-up' ? 'REPLENISHMENT' : 'WITHDRAWAL',
            'value' => $validated['amount'],
            'user_id' => $user->id,
            'created_at' => now(),
        ]);

        return $this->data(['balance' => $balance], 201);
    }
}
