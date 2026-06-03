@extends('emails.layout')

@section('content')
<p>Use the following link to log in:</p>
<p><a href="{{ route('logincode', ['email' => $email, 'login_code' => $loginCode]) }}">Log In</a></p>
<p>Or copy and paste the following URL into your browser:</p>
<p>{{ route('logincode', ['email' => $email, 'login_code' => $loginCode]) }}</p>
@endsection