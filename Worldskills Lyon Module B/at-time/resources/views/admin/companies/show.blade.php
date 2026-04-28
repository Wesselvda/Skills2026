@extends('layouts.app')

@section('content')
    <section class="card">
        <h2>{{ $company->name }}</h2>

        <p><strong>Address:</strong> {{ $company->address }}</p>
        <p><strong>Telephone:</strong> {{ $company->telephone }}</p>
        <p><strong>Email:</strong> {{ $company->email }}</p>

        <p><strong>Owner:</strong> {{ $company->owner_name }} / {{ $company->owner_mobile }} / {{ $company->owner_email }}</p>
        <p><strong>Contact:</strong> {{ $company->contact_name }} / {{ $company->contact_mobile }} / {{ $company->contact_email }}</p>

        <p><strong>Status:</strong> {{ $company->is_deactivated ? 'Deactivated' : 'Active' }}</p>

        <div class="actions">
            <a href="{{ route('companies.edit', $company) }}">Edit company</a>

            @if (! $company->is_deactivated)
                <form method="POST" action="{{ route('companies.deactivate', $company) }}" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit">Deactivate company</button>
                </form>
            @endif
        </div>
    </section>

    <section class="card">
        <h3>Associated products</h3>

        <table>
            <thead>
                <tr>
                    <th>GTIN</th>
                    <th>Name (EN)</th>
                    <th>Hidden</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($company->products as $product)
                    <tr>
                        <td><a href="{{ route('products.show', $product) }}">{{ $product->gtin }}</a></td>
                        <td>{{ $product->name_en }}</td>
                        <td>{{ $product->is_hidden ? 'Yes' : 'No' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No products.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
