@extends('layouts.layout')

@section('body')
    <header class="main-header">
        <div class="inner">
            <nav>
                <a href="/">Home</a>
                <a href="/categories">Categories</a>
                <a href="/users">Users</a>
                <a href="/adverts">Adverts</a>
            </nav>
            <nav>
                <a href="/logout">Logout</a>
            </nav>
        </div>
    </header>
    <main class="main-content">
        @yield('content')
    </main>
@endsection