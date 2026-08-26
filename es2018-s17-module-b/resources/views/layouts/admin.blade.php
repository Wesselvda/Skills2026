<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') &middot; ES2018 Module B</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    @yield('head')
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="{{ route('admin.main-nav') }}">Main Nav</a></li>
                <li><a href="{{ route('admin.design-symbols') }}">Design Symbols</a></li>
                <li><a href="{{ route('admin.pre-orders') }}">Pre-Orders</a></li>
                <li>
                    <form method="POST" action="/logout" class="logout-form">
                        @csrf
                        <button type="submit" class="link-button">Logout</button>
                    </form>
                </li>
            </ul>
        </nav>
    </header>
    <main>
        @if (session('success'))
            <div class="container">
                <div class="success-message">
                    {{ session('success') }}
                </div>
            </div>
        @endif
        @if (session('error'))
            <div class="container">
                <div class="error-message">
                    {{ session('error') }}
                </div>
            </div>
        @endif
        @yield('content')
    </main>
    <footer>
        <div class="inner">
            <p>&copy; {{ date('Y') }} ES2018 Module B.</p>
        </div>
    </footer>
</body>
</html>
