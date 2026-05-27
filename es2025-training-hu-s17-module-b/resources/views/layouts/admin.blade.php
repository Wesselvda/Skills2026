<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'SkillShare Academy Admin' }}</title>
    <link rel="stylesheet" href="{{ asset('assets/style/style.css') }}">
</head>
<body>
    <div class="app-shell">
        <header class="main-header">
            <div class="main-header-inner">
                <a class="logo-wrapper" href="{{ route('dashboard') }}">
                    <img src="{{ asset('assets/logos/skillshare-academy-logo-horizontal-purple.svg') }}" alt="SkillShare Academy" class="logo">
                </a>

                <nav class="main-nav">
                    <a href="{{ route('dashboard') }}" class="main-nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">Dashboard</a>
                    <a href="{{ route('learners.index') }}" class="main-nav-link {{ request()->routeIs('learners.*') ? 'is-active' : '' }}">Learners</a>
                    <a href="{{ route('mentors.index') }}" class="main-nav-link {{ request()->routeIs('mentors.*') ? 'is-active' : '' }}">Mentors</a>
                    <a href="{{ route('courses.index') }}" class="main-nav-link {{ request()->routeIs('courses.*') ? 'is-active' : '' }}">Courses</a>
                </nav>

                <div class="userbar">
                    <div class="userbar-name">{{ auth()->user()?->name ?? 'Admin User' }}</div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-outline" type="submit">Log out</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="main-content">
            @yield('content')
        </main>
    </div>
</body>
</html>