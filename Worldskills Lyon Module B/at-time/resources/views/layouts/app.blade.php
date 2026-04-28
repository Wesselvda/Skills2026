<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Products Management' }}</title>
    <link rel="stylesheet" href="{{ asset('scss/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body>
    <main class="page">
        <header class="header">
            <h1>Products Management</h1>

            @if (session('is_admin'))
                <nav class="menu">
                    <a href="{{ route('companies.index') }}">Companies</a>
                    <a href="{{ route('companies.deactivated') }}">Deactivated companies</a>
                    <a href="{{ route('products.index') }}">Products</a>
                    <a href="{{ route('public.verify.form') }}">GTIN verification</a>
                    <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit">Logout</button>
                    </form>
                </nav>
            @endif
        </header>

        @if (session('status'))
            <div class="flash">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
