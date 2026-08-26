@extends('layouts.admin')

@section('title', 'Order #' . $order->id)

@section('content')
    <div class="container">
        <h1>Order Detail for #ORDER{{ $order->id }}</h1>

        <ol class="status-bar">
            <li class="@if ($order->status === 'open') active @endif">Open</li>
            <li class="@if ($order->status === 'prepared') active @endif">Prepared</li>
            <li class="@if ($order->status === 'closed') active @endif">Closed/Delivered</li>
        </ol>

        <p class="contact-information">
            {{ $order->first_name }} {{ $order->last_name }}<br>
            {{ $order->email }}
        </p>

        <ul class="product-list product-list-detail">
            @foreach ($order->orderItems as $item)
                <li>
                    <div class="preview-image-stack">
                        <img class="preview-product-image" src="/storage/product_images/{{ $item->productColor->image_filename }}" alt="{{ $item->productColor->product->name }}">
                        <img class="preview-design-image" src="/storage/design_symbols/{{ $item->design->image_filename }}" alt="{{ $item->design->name }}">
                    </div>
                    <p>
                        Design symbol ID: {{ $item->design->id }}<br>
                        Product type: {{ $item->productColor->product->name }}<br>
                        Colour option: {{ $item->productColor->name }}
                    </p>
                </li>
            @endforeach
        </ul>

        @if ($order->statusTransitions->isNotEmpty())
            <ul class="status-note-list">
                @foreach ($order->statusTransitions as $transition)
                    <li>
                        <strong>{{ ucfirst($transition->old_status) }} &rarr; {{ ucfirst($transition->new_status) }}</strong>
                        @if ($transition->note)
                            <p>{{ $transition->note }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('admin.pre-orders.status', $order) }}">
            @csrf
            <label>
                Remarks<br>
                <textarea name="note" rows="3"></textarea>
            </label>

            <p>
                Status
                <span class="status-radio-group">
                    <label>
                        <input type="radio" name="status" value="open" @checked($order->status === 'open') disabled>Open
                    </label>
                    <label>
                        <input type="radio" name="status" value="prepared" @checked($order->status === 'prepared') @disabled($order->status !== 'open')>Prepared
                    </label>
                    <label>
                        <input type="radio" name="status" value="closed" @checked($order->status === 'closed') @disabled($order->status !== 'prepared')>Closed/Delivered
                    </label>
                </span>
            </p>

            <p><input type="submit" value="Update status" @disabled($order->status === 'closed')></p>
        </form>
    </div>
@endsection
