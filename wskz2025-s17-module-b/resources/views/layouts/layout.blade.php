<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>wskz2025-s17-module-b</title>
    <link rel="stylesheet" href="/assets/styles/style.css">
    @stack('head')
</head>
<body>
    @session('success')
        <div class="alert success">
            {{ session('success') }}
        </div>
    @endsession
    @session('error')
        <div class="alert error">
            {{ session('error') }}
        </div>
    @endsession
    @yield('body')
</body>
</html>