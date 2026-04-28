@extends('layouts.app')

@section('content')
    <section class="card">
        <h2>{{ $isEdit ? 'Edit company' : 'Create company' }}</h2>

        <form method="POST" action="{{ $isEdit ? route('companies.update', $company) : route('companies.store') }}" class="form">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <label>Company name</label>
            <input name="name" value="{{ old('name', $company->name) }}" required>

            <label>Company address</label>
            <input name="address" value="{{ old('address', $company->address) }}" required>

            <label>Company telephone number</label>
            <input name="telephone" value="{{ old('telephone', $company->telephone) }}" required>

            <label>Company email address</label>
            <input name="email" type="email" value="{{ old('email', $company->email) }}" required>

            <label>Owner name</label>
            <input name="owner_name" value="{{ old('owner_name', $company->owner_name) }}" required>

            <label>Owner mobile number</label>
            <input name="owner_mobile" value="{{ old('owner_mobile', $company->owner_mobile) }}" required>

            <label>Owner email address</label>
            <input name="owner_email" type="email" value="{{ old('owner_email', $company->owner_email) }}" required>

            <label>Contact name</label>
            <input name="contact_name" value="{{ old('contact_name', $company->contact_name) }}" required>

            <label>Contact mobile number</label>
            <input name="contact_mobile" value="{{ old('contact_mobile', $company->contact_mobile) }}" required>

            <label>Contact email address</label>
            <input name="contact_email" type="email" value="{{ old('contact_email', $company->contact_email) }}" required>

            <button type="submit">{{ $isEdit ? 'Save changes' : 'Create company' }}</button>
        </form>
    </section>
@endsection
