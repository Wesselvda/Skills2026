<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ES2018 Module B')</title>
    <script src="/assets/js/navigation.js"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
    @yield('head')
</head>
<body>
    <header>
        <nav>
            <ul id="navigation-items">
                <!-- Navigation items will be dynamically populated here -->
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