<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="Ramazon oyida kunlik ibodatlaringizni belgilab boring — namoz, ro'za, Qur'on, zikr, sadaqa">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0a0e27">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>@yield('title', 'Ramazon Tracker') — Kunlik Ibodat</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="theme-{{ auth()->user()->gender ?? 'male' }}">

    <div class="app-layout">
        {{-- Desktop Sidebar --}}
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
            {{-- Mobile Top Bar --}}
            <div class="mobile-topbar">
                <span class="mobile-topbar-moon">🌙</span>
                <span class="mobile-topbar-title">@yield('title', 'Ramazon Tracker')</span>
                <div class="mobile-topbar-user">
                    <div class="sidebar-avatar" style="width:32px;height:32px;font-size:0.85rem;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>

    {{-- Mobile Bottom Navbar --}}
    <nav class="bottom-nav">
        <a href="{{ route('dashboard') }}" class="bottom-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="bottom-nav-icon">📊</span>
            <span class="bottom-nav-label">Bosh sahifa</span>
        </a>
        <a href="{{ route('daily.show') }}" class="bottom-nav-item {{ request()->routeIs('daily.*') ? 'active' : '' }}">
            <span class="bottom-nav-icon">✅</span>
            <span class="bottom-nav-label">Kunlik</span>
        </a>
        <a href="{{ route('goals.index') }}" class="bottom-nav-item {{ request()->routeIs('goals.*') ? 'active' : '' }}">
            <span class="bottom-nav-icon">🎯</span>
            <span class="bottom-nav-label">Maqsadlar</span>
        </a>
        <a href="{{ route('reports') }}" class="bottom-nav-item {{ request()->routeIs('reports') ? 'active' : '' }}">
            <span class="bottom-nav-icon">📈</span>
            <span class="bottom-nav-label">Hisobot</span>
        </a>
        <button class="bottom-nav-item" onclick="document.getElementById('logoutMenu').classList.toggle('show')">
            <span class="bottom-nav-icon">
                <span class="sidebar-avatar" style="width:24px;height:24px;font-size:0.65rem;border-width:1.5px;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </span>
            </span>
            <span class="bottom-nav-label">Profil</span>
        </button>
    </nav>

    {{-- Mobile Profile/Logout Popup --}}
    <div class="mobile-logout-menu" id="logoutMenu">
        <div class="mobile-logout-card">
            <div class="sidebar-user-info" style="padding:0 0 12px;">
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
            <button class="btn btn-outline btn-sm" style="width:100%;margin-top:8px;" onclick="document.getElementById('logoutMenu').classList.remove('show')">Yopish</button>
        </div>
    </div>

    @yield('scripts')
</body>
</html>
