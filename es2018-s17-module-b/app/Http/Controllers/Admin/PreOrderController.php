<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class PreOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('orderItems.productColor.product', 'orderItems.design')
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('admin.pre-orders', [
            'orders' => $orders,
            'filters' => $request->only(['status', 'date_from', 'date_to']),
        ]);
    }

    public function show(Order $order)
    {
        $order->load('orderItems.productColor.product', 'orderItems.design', 'statusTransitions');

        return view('admin.pre-order-detail', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:prepared,closed',
            'note' => 'nullable|string|max:1000',
        ]);

        $allowedTransitions = [
            'open' => 'prepared',
            'prepared' => 'closed',
        ];

        if (($allowedTransitions[$order->status] ?? null) !== $validated['status']) {
            return redirect()->route('admin.pre-orders.show', $order)->with('error', 'Invalid status transition.');
        }

        $order->statusTransitions()->create([
            'old_status' => $order->status,
            'new_status' => $validated['status'],
            'note' => $validated['note'] ?? null,
        ]);

        $order->update(['status' => $validated['status']]);

        return redirect()->route('admin.pre-orders.show', $order)->with('success', 'Order status updated.');
    }
}
