<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Your Story Deserves a Home, Not Just a Shelf.">
    <title>Neubrandenbook</title>
    <link rel="stylesheet" href="/assets/styles/style.css">
</head>
<body>
    <a class="skip-link" href="#main">Skip the navigation links</a>
    <header class="main-header">
        <div class="container-inner">
            <a class="brand" href="{{ route('home') }}" aria-label="Neubrandenbook">
                <img src="/assets/logo/logo_black.png" alt="Neubrandenbook" class="logo light-only">
                <img src="/assets/logo/logo_white.png" alt="Neubrandenbook" class="logo dark-only">
            </a>
            <nav>
                <a {{ request()->routeIs('home') ? 'class="active"' : '' }} href="{{ route('home') }}">Home</a>
                @if(session()->has('user_id'))
                    <a {{ request()->routeIs('submissions.*') ? 'class="active"' : '' }} href="{{ route('submissions.index') }}">Submissions</a>
                    <form class="logout-form" method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Logout</button>
                    </form>
                @else
                    <a {{ request()->routeIs('login') ? 'class="active"' : '' }} href="{{ route('login') }}">Login</a>
                    <a {{ request()->routeIs('register') ? 'class="active"' : '' }} href="{{ route('register') }}">Register</a>
                @endif
            </nav>
        </div>
    </header>

    <main class="main-content" id="main">
        @if(session('success'))
            <div class="success-message">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->has('submission') || $errors->has('credits'))
            <div class="error-box">
                {{ $errors->first('submission') ?: $errors->first('credits') }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="main-footer">
        <div class="container-inner">&copy; 2026 Neubrandenbook</div>
    </footer>
</body>
</html>
