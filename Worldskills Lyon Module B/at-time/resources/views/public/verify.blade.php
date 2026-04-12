@extends('layouts.app')

@section('content')
    <section class="card">
        <h2>Public GTIN bulk verification</h2>

        <form method="POST" action="{{ route('public.verify.submit') }}" class="form">
            @csrf
            <label>GTIN values (one per line)</label>
            <textarea name="gtins" rows="10" required>{{ old('gtins', $input) }}</textarea>
            <button type="submit">Verify</button>
        </form>
    </section>

    @if (count($results) > 0)
        <section class="card">
            @if ($allValid)
                <p class="all-valid" aria-label="All valid">
                    <img src="{{ asset('images/green-tick.png') }}" alt="All valid" class="icon"> All valid
                </p>
            @endif

            <table>
                <thead>
                    <tr>
                        <th>GTIN</th>
                        <th>Valid</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($results as $result)
                        <tr>
                            <td>{{ $result['gtin'] }}</td>
                            <td>
                                @if ($result['valid'])
                                    <img src="{{ asset('images/green-tick.png') }}" alt="Valid" class="icon"> Valid
                                @else
                                    Not valid
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endif
@endsection
