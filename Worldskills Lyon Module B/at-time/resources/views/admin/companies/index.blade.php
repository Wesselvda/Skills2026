@extends('layouts.app')

@section('content')
    <section class="card">
        <h2>{{ $title }}</h2>

        <div class="actions">
            <a href="{{ route('companies.create') }}">Create company</a>
            <a href="{{ route('companies.index') }}">Active list</a>
            <a href="{{ route('companies.deactivated') }}">Deactivated list</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Telephone</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($companies as $company)
                    <tr>
                        <td><a href="{{ route('companies.show', $company) }}">{{ $company->name }}</a></td>
                        <td>{{ $company->email }}</td>
                        <td>{{ $company->telephone }}</td>
                        <td>{{ $company->is_deactivated ? 'Deactivated' : 'Active' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No companies found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
