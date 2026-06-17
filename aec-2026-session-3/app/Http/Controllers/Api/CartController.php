<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Cart;
use App\Services\ExternalApi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'expires_in' => ['nullable', 'integer', 'min:0', 'max:300'],
        ]);

        $expiresIn = $data['expires_in'] ?? 300;
        $cart = Cart::create([
            'expires_in' => $expiresIn,
            'expires_at' => now()->addSeconds($expiresIn),
        ]);

        return response()->json([
            'id' => $cart->id,
            'expires_at' => $cart->expires_at->toISOString(),
        ]);
    }

    public function show(Request $request, string $id)
    {
        $cart = Cart::with('items.book')->find($id);

        if (! $cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        return response()->json($this->cartResponse($cart, $request));
    }

    public function storeItem(Request $request, string $id)
    {
        $cart = Cart::find($id);

        if (! $cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        if ($cart->expires_at->isPast()) {
            return response()->json(['message' => 'Cart is expired'], 409);
        }

        $data = $request->validate([
            'book_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1'],
            'expires_in' => ['nullable', 'integer', 'min:0', 'max:300'],
        ]);

        $book = Book::find($data['book_id']);
        if (! $book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $existing = $cart->items()->where('book_id', $book->id)->first();
        $quantity = $data['quantity'] + ($existing?->quantity ?? 0);

        if ($quantity > $this->availableStock($book) + ($existing?->quantity ?? 0)) {
            return response()->json(['message' => 'Validation error'], 422);
        }

        $item = $cart->items()->updateOrCreate(
            ['book_id' => $book->id],
            [
                'quantity' => $quantity,
                'unit_price' => $book->price,
            ],
        );

        $this->refreshCart($cart, $data['expires_in'] ?? null);

        return response()->json($this->itemResponse($item->refresh(), $cart));
    }

    public function updateItem(Request $request, string $id, string $itemId)
    {
        $cart = Cart::with('items')->find($id);

        if (! $cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        if ($cart->expires_at->isPast()) {
            return response()->json(['message' => 'Cart is expired'], 409);
        }

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'expires_in' => ['nullable', 'integer', 'min:0', 'max:300'],
        ]);

        $item = $cart->items()->findOrFail($itemId);
        if ($data['quantity'] > $this->availableStock($item->book) + $item->quantity) {
            return response()->json(['message' => 'Validation error'], 422);
        }
        $item->update(['quantity' => $data['quantity']]);
        $this->refreshCart($cart, $data['expires_in'] ?? null);

        return response()->json($this->itemResponse($item->refresh(), $cart));
    }

    public function destroyItem(Request $request, string $id, string $itemId)
    {
        $cart = Cart::with('items')->find($id);

        if (! $cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        if ($cart->expires_at->isPast()) {
            return response()->json(['message' => 'Cart is expired'], 409);
        }

        $request->validate([
            'expires_in' => ['nullable', 'integer', 'min:0', 'max:300'],
        ]);

        $item = $cart->items()->findOrFail($itemId);
        $bookId = $item->book_id;
        $item->delete();
        $this->refreshCart($cart, $request->integer('expires_in') ?: null);

        return response()->json([
            'id' => $itemId,
            'cart_id' => $cart->id,
            'book_id' => $bookId,
            'cart_expires_at' => $cart->expires_at->toISOString(),
        ]);
    }

    public function startCheckout(Request $request, string $id)
    {
        $cart = Cart::with('items.book')->find($id);

        if (! $cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        if ($cart->expires_at->isPast()) {
            return response()->json(['message' => 'Cart is expired'], 409);
        }

        $data = $request->validate([
            'callbackUrl' => ['required', 'url'],
        ]);

        if ($cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 409);
        }

        $payload = $this->cartResponse($cart, $request);
        $cartHash = $this->cartHash($cart, $payload['discounted']);
        $externalApi = app(ExternalApi::class);

        if ($cart->payment_session_id && $cart->payment_cart_hash === $cartHash) {
            $session = $externalApi->paymentSession($cart->payment_session_id);
            $status = $session['status'] ?? null;

            if (in_array($status, ['pending', 'successful'])) {
                return response()->json([
                    'cart_id' => $cart->id,
                    'payment_url' => $cart->payment_url,
                ]);
            }
        }

        $session = $externalApi->createPaymentSession(
            (float) $payload['discounted'],
            $cart->id,
            $cart->expires_at->toISOString(),
            $data['callbackUrl'],
        );

        if (! $session) {
            return response()->json(['message' => 'Could not create payment session'], 422);
        }

        $cart->update([
            'checkout_status' => $session['status'] ?? 'pending',
            'payment_session_id' => $session['sessionId'],
            'payment_url' => url('/api/payment/' . $session['sessionId']),
            'payment_callback_url' => $data['callbackUrl'],
            'payment_cart_hash' => $cartHash,
        ]);

        return response()->json([
            'cart_id' => $cart->id,
            'payment_url' => $cart->payment_url,
        ]);
    }

    public function completeCheckout(string $id)
    {
        $cart = Cart::with('items.book')->find($id);

        if (! $cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        if (! $cart->payment_session_id) {
            return response()->json([
                'message' => 'Checkout has not been started',
                'error' => 'NO_SESSION',
            ], 409);
        }

        if ($cart->expires_at->isPast()) {
            return response()->json([
                'message' => 'Cart is expired',
                'error' => 'EXPIRED',
            ], 409);
        }

        $session = app(ExternalApi::class)->paymentSession($cart->payment_session_id);
        $status = $session['status'] ?? null;

        if ($status === 'failed') {
            return response()->json([
                'message' => 'Payment failed',
                'error' => $session['errorCode'] ?? 'FAILED',
            ], 409);
        }

        if ($status !== 'successful') {
            return response()->json([
                'message' => 'Payment has not been completed',
                'error' => 'PENDING',
            ], 409);
        }

        foreach ($cart->items as $item) {
            $item->book->decrement('stock', $item->quantity);
        }

        $paidAt = $session['paidAt'] ?? Carbon::now()->toISOString();

        $cart->items()->delete();
        $cart->update([
            'checkout_status' => 'successful',
            'paid_at' => $paidAt,
        ]);

        return response()->json([
            'paid_at' => $paidAt,
            'cart_id' => $id,
        ]);
    }

    private function cartResponse(Cart $cart, ?Request $request = null)
    {
        $targetLanguage = $request ? $this->targetLanguage($request, null) : null;
        $items = $cart->items->map(fn($item) => [
            'id' => $item->id,
            'book_id' => $item->book_id,
            'title' => $this->localized($item->book->title, $item->book->original_language, $targetLanguage === $item->book->original_language ? null : $targetLanguage),
            'cover' => asset('assets/data/book-covers/' . $item->book->cover_image),
            'unit_price' => (float) $item->unit_price,
            'quantity' => $item->quantity,
            'total_price' => round((float) $item->unit_price * $item->quantity, 2),
            'stock' => $this->availableStock($item->book),
            'original_language' => $item->book->original_language,
            'translated_language' => $targetLanguage === $item->book->original_language ? null : $targetLanguage,
        ])->values();

        $total = round($items->sum('total_price'), 2);
        $discountResult = $this->applyDiscounts($total, (int) $cart->items->sum('quantity'));

        return [
            'id' => $cart->id,
            'expires_at' => $cart->expires_at->toISOString(),
            'expired' => $cart->expires_at->isPast(),
            'items' => $items,
            'total' => $total,
            'discounted' => $discountResult['discounted'],
            'total_items' => $cart->items->sum('quantity'),
            'applied_discounts' => $discountResult['rules'],
        ];
    }

    private function applyDiscounts(float $total, int $quantity)
    {
        $discounted = $total;
        $rules = [];

        foreach (app(ExternalApi::class)->discountRules() as $rule) {
            $applied = $this->discountApplies($rule, $total, $quantity);

            if ($applied) {
                $discount = $rule['discount'] ?? [];

                if (($discount['type'] ?? null) === 'percentage') {
                    $discounted = $discounted - ($discounted * ((float) $discount['value'] / 100));
                }

                if (($discount['type'] ?? null) === 'fixed') {
                    $discounted = $discounted - (float) $discount['value'];
                }

                if ($discounted < 0) {
                    $discounted = 0;
                }
            }

            if ($applied) {
                $rules[] = [
                    'id' => $rule['id'] ?? null,
                    'name' => $rule['name'] ?? null,
                ];
            }
        }

        return [
            'discounted' => round($discounted, 2),
            'rules' => $rules,
        ];
    }

    private function discountApplies(array $rule, float $total, int $quantity)
    {
        $conditions = $rule['conditions'] ?? [];

        if (isset($conditions['minQuantity']) && $conditions['minQuantity'] !== null && $quantity < $conditions['minQuantity']) {
            return false;
        }

        if (isset($conditions['minTotalPrice']) && $conditions['minTotalPrice'] !== null && $total < $conditions['minTotalPrice']) {
            return false;
        }

        if (isset($conditions['timespan']) && is_array($conditions['timespan'])) {
            $now = now();
            $start = Carbon::parse($conditions['timespan']['start']);
            $end = Carbon::parse($conditions['timespan']['end']);

            if ($now->lt($start) || $now->gt($end)) {
                return false;
            }
        }

        return true;
    }

    private function cartHash(Cart $cart, float $discounted)
    {
        $parts = [$discounted];

        foreach ($cart->items as $item) {
            $parts[] = $item->book_id . ':' . $item->quantity . ':' . $item->unit_price;
        }

        return hash('sha256', implode('|', $parts));
    }

    private function refreshCart(Cart $cart, ?int $expiresIn)
    {
        $seconds = $expiresIn ?? 300;

        if ($cart->payment_session_id) {
            app(ExternalApi::class)->deletePaymentSession($cart->payment_session_id);
        }

        $cart->update([
            'expires_in' => $seconds,
            'expires_at' => now()->addSeconds($seconds),
            'payment_session_id' => null,
            'payment_url' => null,
            'payment_callback_url' => null,
            'payment_cart_hash' => null,
        ]);
    }

    private function itemResponse($item, Cart $cart)
    {
        return [
            'id' => $item->id,
            'cart_id' => $cart->id,
            'book_id' => $item->book_id,
            'quantity' => $item->quantity,
            'cart_expires_at' => $cart->expires_at->toISOString(),
        ];
    }

    private function availableStock(Book $book)
    {
        $reserved = \App\Models\CartItem::query()
            ->where('book_id', $book->id)
            ->whereHas('cart', fn($query) => $query->where('expires_at', '>', now()))
            ->sum('quantity');

        return max(0, $book->stock - $reserved);
    }

    private function targetLanguage(Request $request, ?string $originalLanguage)
    {
        $header = $request->header('Accept-Language');
        $supported = ['en', 'de', 'nl', 'hu'];

        if (! $header || $header === '*') {
            return null;
        }

        $best = null;
        $bestQuality = -1;

        foreach (explode(',', $header) as $part) {
            $pieces = array_map('trim', explode(';', $part));
            $language = strtolower(substr($pieces[0] ?? '', 0, 2));
            $quality = 1.0;

            foreach (array_slice($pieces, 1) as $piece) {
                if (str_starts_with($piece, 'q=')) {
                    $quality = (float) substr($piece, 2);
                }
            }

            if (in_array($language, $supported) && $quality > $bestQuality) {
                $best = $language;
                $bestQuality = $quality;
            }
        }

        if (! $best || $best === $originalLanguage) {
            return null;
        }

        return $best;
    }

    private function localized(string $value, string $sourceLanguage, ?string $targetLanguage)
    {
        if (! $targetLanguage) {
            return [
                'value' => $value,
                'fallback' => false,
            ];
        }

        $translation = app(ExternalApi::class)->translateBatch([[
            'text' => $value,
            'sourceLanguage' => $sourceLanguage,
            'targetLanguage' => $targetLanguage,
        ]])[0] ?? null;

        $success = ($translation['success'] ?? false) && ($translation['translation'] ?? null);

        return [
            'value' => $success ? $translation['translation'] : $value,
            'fallback' => ! $success,
        ];
    }
}
