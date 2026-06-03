@extends('layouts.layout')

@section('content')
    <section class="text-centered">
        <h1>Mock Emails</h1>
    </section>
    <section>
        <table>
            <thead>
                <tr>
                    <th>Recipient</th>
                    <th>Subject</th>
                    <th>Body</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($mockEmails as $email)
                    <tr>
                        <td>{{ $email->recipient }}</td>
                        <td>{{ $email->subject }}</td>
                        {{-- Escape the body content but show things like header, main, p, a etc. --}}
                        <td>{{ $email->body }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endsection