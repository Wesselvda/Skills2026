<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offshore wind farm</title>
    <link rel="stylesheet" href="/assets/style/style.css">
    @yield('head')
</head>
<body>
    <header class="main-header">
        <div class="inner-wrapper">
            <a href="/" class="logo">Offshore Wind Farm</a>
            <nav>
                <a href="/" <?php if (Request::is('/')): ?>class="active"<?php endif; ?>>Home</a>
                <a href="/investors" <?php if (Request::is('/investors')): ?>class="active"<?php endif; ?>>Investors</a>
                <a href="/sponsors" <?php if (Request::is('/sponsors')): ?>class="active"<?php endif; ?>>Sponsors</a>
                <a href="/visitor-tours" <?php if (Request::is('/visitor-tours')): ?>class="active"<?php endif; ?>>Visitor Tours</a>
                @if (session('user_id'))
                    @if (\App\Models\ApplicationUser::find(session('user_id'))->role === 'admin')
                        <a href="/admin" <?php if (Request::is('/admin')): ?>class="active"<?php endif; ?>>Admin</a>
                    @endif
                    <a href="/logout">Logout</a>
                @endif
            </nav>
        </div>
    </header>
    <main class="main-content">
        @yield('content')
    </main>
</body>
</html>