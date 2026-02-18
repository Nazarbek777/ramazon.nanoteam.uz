<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Ramazon oyida kunlik ibodatlaringizni belgilab boring — namoz, ro'za, Qur'on, zikr, sadaqa">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ramazon Tracker') — Kunlik Ibodat</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="theme-{{ auth()->user()->gender ?? 'male' }}">
    <button class="mobile-toggle" onclick="toggleSidebar()">☰</button>
    <div class="mobile-overlay" id="mobileOverlay" onclick="toggleSidebar()"></div>

    <div class="app-layout">
        {{-- Sidebar --}}
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <span class="moon">🌙</span>
                <h1>Ramazon Tracker</h1>
                <div class="arabic">رمضان مبارك</div>
            </div>

            <ul class="sidebar-nav">
                <li>
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span class="nav-icon">📊</span>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('daily.show') }}" class="{{ request()->routeIs('daily.*') ? 'active' : '' }}">
                        <span class="nav-icon">✅</span>
                        Kunlik Amallar
                    </a>
                </li>
                <li>
                    <a href="{{ route('goals.index') }}" class="{{ request()->routeIs('goals.*') ? 'active' : '' }}">
                        <span class="nav-icon">🎯</span>
                        Maqsadlar
                    </a>
                </li>
                <li>
                    <a href="{{ route('reports') }}" class="{{ request()->routeIs('reports') ? 'active' : '' }}">
                        <span class="nav-icon">📈</span>
                        Hisobotlar
                    </a>
                </li>
            </ul>

            <div class="sidebar-user">
                <div class="sidebar-user-info">
                    <div class="sidebar-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
                        <div class="sidebar-user-email">{{ auth()->user()->email }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-logout">Chiqish</button>
                </form>
            </div>
        </aside>

        {{-- Main Content --}}
        <main class="main-content">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('mobileOverlay').classList.toggle('show');
        }
    </script>

    @yield('scripts')
</body>
</html>
