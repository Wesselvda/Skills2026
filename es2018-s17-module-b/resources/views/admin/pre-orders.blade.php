@extends('layouts.admin')

@section('title', 'Pre-Orders')

@section('content')
    <div class="container">
        <h1>Pre-Orders</h1>

        <div class="pre-orders-layout">
            <main class="pre-order-list">
                @forelse ($orders as $order)
                    <a href="{{ route('admin.pre-orders.show', $order) }}" class="pre-order-row">
                        <div class="pre-order-row-header">
                            <strong>#ORDER{{ $order->id }}</strong>
                            <span class="status status-{{ $order->status }}">
                                {{ $order->status === 'closed' ? 'Closed/Delivered' : ucfirst($order->status) }}
                            </span>
                        </div>
                        <ul class="product-list">
                            @foreach ($order->orderItems as $item)
                                <li>
                                    <div class="preview-image-stack">
                                        <img class="preview-product-image"
                                            src="/storage/product_images/{{ $item->productColor->image_filename }}"
                                            alt="{{ $item->productColor->product->name }}">
                                        <img class="preview-design-image"
                                            src="/storage/design_symbols/{{ $item->design->image_filename }}"
                                            alt="{{ $item->design->name }}">
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </a>
                @empty
                    <p>No pre-orders found.</p>
                @endforelse

                {{ $orders->links() }}
            </main>

            <aside class="pre-order-filters">
                <h2>Filters</h2>
                <form method="GET" action="{{ route('admin.pre-orders') }}">
                    <div class="input-wrapper">
                        <label for="date_from">From</label>
                        <input type="date" id="date_from" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div class="input-wrapper">
                        <label for="date_to">To</label>
                        <input type="date" id="date_to" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <div class="input-wrapper">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="">All</option>
                            <option value="open" @selected(($filters['status'] ?? '') === 'open')>Open</option>
                            <option value="prepared" @selected(($filters['status'] ?? '') === 'prepared')>Prepared</option>
                            <option value="closed" @selected(($filters['status'] ?? '') === 'closed')>Closed/Delivered</option>
                        </select>
                    </div>
                    <button type="submit">Apply filters</button>
                </form>
            </aside>
        </div>
    </div>
@endsection
