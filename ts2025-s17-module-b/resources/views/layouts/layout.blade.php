<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>VelOrda</title>
    <link rel="stylesheet" href="{{ asset('assets/style/style.css') }}">
    @stack('head')
</head>
<body>
    <header class="main-header">
        <div class="inner">
            <a class="logo" href="/">VelOrda</a>
            <nav>
                @auth
                    <a href="{{ route('categories.index') }}">Categories</a>
                    <a href="{{ route('history.index') }}">History</a>
                    <a href="{{ route('applications.index') }}">Applications</a>
                    <a href="{{ route('logout') }}">Logout</a>
                @else
                    <a href="{{ route('login') }}">Login</a>
                @endauth
            </nav>
        </div>
    </header>
    <main class="main-content">
        @yield('content')
    </main>
</body>
</html>
