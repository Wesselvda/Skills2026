@extends('layouts.layout')

@section('navigation')
    <a href="/admin">Dashboard</a>
    <a href="/admin/builds">Builds</a>
    <a href="/admin/pages">Pages</a>
    <a href="/admin/competitions">Competitions</a>
    <form action="/logout" method="POST">
        @csrf
        <button type="submit">Logout</button>
    </form>
@endsection