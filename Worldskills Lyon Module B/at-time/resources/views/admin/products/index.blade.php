@extends('layouts.app')

@section('content')
    <section class="card">
        <h2>Products</h2>

        <div class="actions">
            <a href="{{ route('products.create') }}">Create product</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>GTIN</th>
                    <th>Name (EN)</th>
                    <th>Company</th>
                    <th>Hidden</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td><a href="{{ route('products.show', $product) }}">{{ $product->gtin }}</a></td>
                        <td>{{ $product->name_en }}</td>
                        <td>{{ $product->company?->name }}</td>
                        <td>{{ $product->is_hidden ? 'Yes' : 'No' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No products found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">
            {{ $products->links() }}
        </div>
    </section>
@endsection
