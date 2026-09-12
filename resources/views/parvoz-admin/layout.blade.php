<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Parvoz') - O'quv markazi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #082f49 50%, #0f172a 100%);
            min-height: 100vh;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-link.active {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.3), rgba(6, 182, 212, 0.2));
            border-right: 3px solid #0ea5e9;
            color: #7dd3fc;
        }

        .sidebar-link:hover { background: rgba(14, 165, 233, 0.15); }
        .glow-sm { box-shadow: 0 0 15px rgba(14, 165, 233, 0.15); }

        .btn-primary {
            background: linear-gradient(135deg, #0ea5e9, #06b6d4);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #0284c7, #0891b2);
            transform: translateY(-1px);
            box-shadow: 0 4px 20px rgba(14, 165, 233, 0.4);
        }

        .input-dark {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
            transition: all 0.2s ease;
        }

        .input-dark:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.2);
            outline: none;
        }

        .input-dark::placeholder { color: rgba(255, 255, 255, 0.3); }
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="antialiased text-slate-200">
    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: true }">
        <aside class="relative w-72 glass-card border-r border-white/10 transition-all duration-300 flex flex-col"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0 lg:w-20'">

            <div class="h-20 flex items-center px-6 border-b border-white/10">
                <div class="w-10 h-10 bg-gradient-to-br from-sky-500 to-cyan-600 rounded-xl flex items-center justify-center shrink-0 glow-sm">
                    <i class="fas fa-graduation-cap text-white"></i>
                </div>
                <span class="ml-3 font-bold text-xl tracking-tight" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 lg:hidden'">
                    PARVOZ<span class="text-sky-400">EDU</span>
                </span>
            </div>

            <nav class="flex-1 overflow-y-auto p-4 space-y-1">
                @php
                    $links = [
                        ['parvoz-admin.dashboard', 'fa-chart-line',      'Bosh sahifa'],
                        ['parvoz-admin.groups',    'fa-users',           'Guruhlar'],
                        ['parvoz-admin.students',  'fa-user-graduate',   'O\'quvchilar'],
                        ['parvoz-admin.teachers',  'fa-chalkboard-user', 'O\'qituvchilar'],
                        ['parvoz-admin.subjects',  'fa-book',            'Fanlar'],
                        ['parvoz-admin.grades',    'fa-star',            'Baholar'],
                        ['parvoz-admin.bot',       'fa-robot',           'Bot sozlamalari'],
                    ];
                @endphp

                @foreach($links as [$route, $icon, $label])
                    <a href="{{ route($route) }}"
                        class="sidebar-link flex items-center px-4 py-3 rounded-xl transition duration-200 {{ request()->routeIs($route) ? 'active' : '' }}">
                        <i class="fas {{ $icon }} w-6 text-sky-400"></i>
                        <span class="ml-3 font-semibold" :class="!sidebarOpen && 'lg:hidden'">{{ $label }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="p-4 border-t border-white/10">
                <a href="{{ route('admin.dashboard') }}"
                    class="w-full flex items-center px-4 py-3 rounded-xl text-slate-400 hover:bg-white/5 transition duration-200 font-bold">
                    <i class="fas fa-arrow-left w-6"></i>
                    <span class="ml-3" :class="!sidebarOpen && 'lg:hidden'">Asosiy Admin</span>
                </a>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="h-20 glass-card border-b border-white/10 flex items-center justify-between px-8 shrink-0">
                <button @click="sidebarOpen = !sidebarOpen" class="text-slate-400 hover:text-sky-400 transition">
                    <i class="fas fa-bars-staggered text-xl"></i>
                </button>

                <div class="flex items-center space-x-4">
                    <div class="flex-col text-right hidden sm:flex">
                        <p class="text-sm font-bold text-slate-200">{{ auth()->user()->name ?? 'Admin' }}</p>
                        <p class="text-[10px] text-sky-400 font-bold uppercase tracking-widest">Parvoz Edu</p>
                    </div>
                    <div class="w-10 h-10 bg-gradient-to-br from-sky-500 to-cyan-600 rounded-xl flex items-center justify-center glow-sm">
                        <i class="fas fa-user text-white text-sm"></i>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-8">
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                        class="mb-6 bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 px-6 py-4 rounded-2xl flex justify-between items-center">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-3 text-emerald-400"></i>
                            <span class="font-semibold">{{ session('success') }}</span>
                        </div>
                        <button @click="show = false" class="text-emerald-400 hover:text-emerald-300">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 bg-red-500/10 border border-red-500/30 text-red-300 px-6 py-4 rounded-2xl">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-exclamation-triangle mr-3 text-red-400"></i>
                            <span class="font-semibold">Xatolik!</span>
                        </div>
                        <ul class="list-disc list-inside text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>

</html>
