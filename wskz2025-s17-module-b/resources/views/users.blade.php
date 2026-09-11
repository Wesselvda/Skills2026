@extends('layouts.app')

@section('content')
    <section class="container">
        <h1>Users</h1>
        <form method="GET" action="/users">
            <div class="input-wrapper">
                <label for="search">Search:</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}">
            </div>  
            <button type="submit">Search</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endsection
