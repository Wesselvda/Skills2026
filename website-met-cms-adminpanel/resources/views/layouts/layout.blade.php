<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    @stack('head')
</head>
<body>
    <header>
        <nav>
            @yield('navigation')
        </nav>
    </header>
    <main>
        @yield('main')
    </main>
</body>
</html>