<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'SkillShare Academy Login' }}</title>
    <link rel="stylesheet" href="{{ asset('assets/style/style.css') }}">
</head>
<body class="body-guest">
    <main class="guest-main">
        @yield('content')
    </main>
</body>
</html>