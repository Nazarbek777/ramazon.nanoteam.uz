<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="Ramazon oyida kunlik ibodatlaringizni belgilab boring — namoz, ro'za, Qur'on, zikr, sadaqa">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#070b1e">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>@yield('title', 'Ramazon Tracker') — Kunlik Ibodat</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Amiri&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ time() }}">
</head>
<body class="theme-{{ auth()->user()->gender ?? 'male' }}">

    <div class="app-layout">
        {{-- Desktop Sidebar --}}
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <span class="brand-icon"><i class="ri-moon-clear-fill"></i></span>
                <h1>Ramazon Tracker</h1>
                <div class="arabic">رمضان مبارك</div>
                <div id="navLocationDesktop" style="font-size:0.7rem;color:var(--text-muted);display:flex;align-items:center;gap:6px;margin-top:8px;opacity:0.8;">
                    <i class="ri-map-pin-2-fill" style="color:var(--accent);font-size:0.8rem;"></i>
                    <span class="loc-text">Yuklanmoqda...</span>
                    <i class="ri-refresh-line refresh-loc-btn" style="cursor:pointer;font-size:0.75rem;margin-left:2px;" onclick="PrayerTimes.refreshLocation()"></i>
                </div>
            </div>

            <ul class="sidebar-nav">
                <li>
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="ri-home-4-line"></i>
                        Bosh sahifa
                    </a>
                </li>
                <li>
                    <a href="{{ route('daily.show') }}" class="{{ request()->routeIs('daily.*') ? 'active' : '' }}">
                        <i class="ri-checkbox-circle-line"></i>
                        Kunlik Amallar
                    </a>
                </li>
                <li>
                    <a href="{{ route('goals.index') }}" class="{{ request()->routeIs('goals.*') ? 'active' : '' }}">
                        <i class="ri-focus-3-line"></i>
                        Maqsadlar
                    </a>
                </li>
                <li>
                    <a href="{{ route('reports') }}" class="{{ request()->routeIs('reports') ? 'active' : '' }}">
                        <i class="ri-bar-chart-box-line"></i>
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
                    <button type="submit" class="btn-logout"><i class="ri-logout-box-r-line"></i> Chiqish</button>
                </form>
            </div>
        </aside>

        {{-- Main Content --}}
        <main class="main-content">
            {{-- Mobile Top Bar --}}
            <div class="mobile-topbar">
                <div class="mobile-topbar-brand">
                    <i class="ri-moon-clear-fill"></i>
                </div>
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;">
                    <span class="mobile-topbar-title" style="margin-bottom:2px;">@yield('title', 'Ramazon Tracker')</span>
                    <div id="navLocationMobile" style="font-size:0.62rem;color:var(--accent-light);display:flex;align-items:center;gap:3px;opacity:0.8;">
                        <i class="ri-map-pin-2-fill" style="font-size:0.7rem;"></i>
                        <span class="loc-text">Yuklanmoqda...</span>
                        <i class="ri-refresh-line refresh-loc-btn" style="cursor:pointer;font-size:0.7rem;margin-left:1px;" onclick="PrayerTimes.refreshLocation()"></i>
                    </div>
                </div>
                <div class="mobile-topbar-user">
                    <div class="sidebar-avatar" style="width:30px;height:30px;font-size:0.78rem;border-width:1.5px;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success"><i class="ri-checkbox-circle-fill"></i> {{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-error"><i class="ri-error-warning-fill"></i> {{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>

    {{-- Mobile Bottom Navbar --}}
    <nav class="bottom-nav">
        <a href="{{ route('dashboard') }}" class="bottom-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="ri-home-4-{{ request()->routeIs('dashboard') ? 'fill' : 'line' }}"></i>
            <span class="bottom-nav-label">Bosh sahifa</span>
        </a>
        <a href="{{ route('daily.show') }}" class="bottom-nav-item {{ request()->routeIs('daily.*') ? 'active' : '' }}">
            <i class="ri-checkbox-circle-{{ request()->routeIs('daily.*') ? 'fill' : 'line' }}"></i>
            <span class="bottom-nav-label">Kunlik</span>
        </a>
        <a href="{{ route('goals.index') }}" class="bottom-nav-item {{ request()->routeIs('goals.*') ? 'active' : '' }}">
            <i class="ri-focus-3-{{ request()->routeIs('goals.*') ? 'fill' : 'line' }}"></i>
            <span class="bottom-nav-label">Maqsadlar</span>
        </a>
        <a href="{{ route('reports') }}" class="bottom-nav-item {{ request()->routeIs('reports') ? 'active' : '' }}">
            <i class="ri-bar-chart-box-{{ request()->routeIs('reports') ? 'fill' : 'line' }}"></i>
            <span class="bottom-nav-label">Hisobot</span>
        </a>
        <button class="bottom-nav-item" onclick="document.getElementById('logoutMenu').classList.toggle('show')">
            <span class="sidebar-avatar" style="width:24px;height:24px;font-size:0.6rem;border-width:1.5px;">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </span>
            <span class="bottom-nav-label">Profil</span>
        </button>
    </nav>

    {{-- Mobile Profile Popup --}}
    <div class="mobile-logout-menu" id="logoutMenu">
        <div class="mobile-logout-card">
            <div class="sidebar-user-info" style="padding-bottom:12px;">
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
                <button type="submit" class="btn-logout"><i class="ri-logout-box-r-line"></i> Chiqish</button>
            </form>
            <button class="btn btn-outline btn-sm" style="width:100%;margin-top:8px;" onclick="document.getElementById('logoutMenu').classList.remove('show')">
                <i class="ri-close-line"></i> Yopish
            </button>
        </div>
    </div>

    @yield('scripts')
    <script src="{{ asset('js/prayer-times.js') }}?v={{ time() }}"></script>
</body>
</html>
