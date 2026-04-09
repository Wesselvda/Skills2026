<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle ?? 'Lyon Heritage Sites' }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'Lyon heritage content website.' }}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $pageTitle ?? 'Lyon Heritage Sites' }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'Lyon heritage content website.' }}">
    @if (!empty($metaImage))
        <meta property="og:image" content="{{ $metaImage }}">
    @endif

    <link rel="stylesheet" href="/css/app.css">
    <script src="/js/app.js" defer></script>
</head>
<body>
    @hasSection('header_title')
    <header class="site-header">
        <h1><a href="{{ route('heritages.index') }}">@yield('header_title')</a></h1>
    </header>
    @endif
    <main>
        @yield('content')
    </main>
</body>
</html>
