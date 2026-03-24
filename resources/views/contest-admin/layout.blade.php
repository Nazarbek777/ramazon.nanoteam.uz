<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Konkurs Bot') - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
            min-height: 100vh;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-link.active {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.3), rgba(99, 102, 241, 0.2));
            border-right: 3px solid #8b5cf6;
            color: #c4b5fd;
        }

        .sidebar-link:hover {
            background: rgba(139, 92, 246, 0.15);
        }

        .glow {
            box-shadow: 0 0 30px rgba(139, 92, 246, 0.2);
        }

        .glow-sm {
            box-shadow: 0 0 15px rgba(139, 92, 246, 0.15);
        }

        [x-cloak] {
            display: none !important;
        }

        .btn-primary {
            background: linear-gradient(135deg, #8b5cf6, #6366f1);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            transform: translateY(-1px);
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 20px rgba(239, 68, 68, 0.4);
        }

        .input-dark {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
            transition: all 0.2s ease;
        }

        .input-dark:focus {
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
            outline: none;
        }

        .input-dark::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }
    </style>
</head>

<body class="antialiased text-slate-200">
    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: true }">
        <!-- Sidebar -->
        <aside class="relative w-72 glass-card border-r border-white/10 transition-all duration-300 flex flex-col"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0 lg:w-20'">

            <!-- Logo -->
            <div class="h-20 flex items-center px-6 border-b border-white/10">
                <div class="w-10 h-10 bg-gradient-to-br from-violet-500 to-indigo-600 rounded-xl flex items-center justify-center shrink-0 glow-sm">
                    <i class="fas fa-trophy text-white"></i>
                </div>
                <span class="ml-3 font-bold text-xl tracking-tight transition-opacity duration-300"
                    :class="sidebarOpen ? 'opacity-100' : 'opacity-0 lg:hidden'">
                    KONKURS<span class="text-violet-400">BOT</span>
                </span>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto p-4 space-y-1">
                <p class="text-[10px] uppercase font-bold text-slate-500 px-3 mb-2" :class="!sidebarOpen && 'hidden'">
                    Boshqaruv</p>

                <a href="{{ route('contest-admin.bots.index') }}"
                    class="sidebar-link group flex items-center px-4 py-3 rounded-xl transition duration-200 {{ request()->routeIs('contest-admin.bots.index') && !request()->routeIs('contest-admin.bots.contests.*') ? 'active' : '' }}">
                    <i class="fas fa-robot w-6 text-violet-400"></i>
                    <span class="ml-3 font-semibold" :class="!sidebarOpen && 'lg:hidden'">Botlar</span>
                </a>

                @php
                    $allBots = \App\Modules\Contest\Models\ContestBot::all();
                @endphp

                @if($allBots->isNotEmpty())
                    <p class="text-[10px] uppercase font-bold text-slate-500 px-3 mt-4 mb-2" :class="!sidebarOpen && 'hidden'">
                        Botlar</p>

                    @foreach($allBots as $navBot)
                        <a href="{{ route('contest-admin.bots.contests.index', $navBot) }}"
                            class="sidebar-link group flex items-center px-4 py-3 rounded-xl transition duration-200 {{ request()->is("contest-admin/bots/{$navBot->id}/*") ? 'active' : '' }}">
                            <i class="fas fa-{{ $navBot->is_active ? 'circle-check text-emerald-400' : 'circle-xmark text-red-400' }} w-6"></i>
                            <span class="ml-3 font-semibold text-sm truncate" :class="!sidebarOpen && 'lg:hidden'">
                                {{ Str::limit($navBot->name, 18) }}
                            </span>
                        </a>
                    @endforeach
                @endif
            </nav>

            <!-- Back to Admin -->
            <div class="p-4 border-t border-white/10">
                <a href="{{ route('admin.dashboard') }}"
                    class="w-full flex items-center px-4 py-3 rounded-xl text-slate-400 hover:bg-white/5 transition duration-200 font-bold">
                    <i class="fas fa-arrow-left w-6"></i>
                    <span class="ml-3" :class="!sidebarOpen && 'lg:hidden'">Asosiy Admin</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="h-20 glass-card border-b border-white/10 flex items-center justify-between px-8 shrink-0">
                <button @click="sidebarOpen = !sidebarOpen" class="text-slate-400 hover:text-violet-400 transition">
                    <i class="fas fa-bars-staggered text-xl"></i>
                </button>

                <div class="flex items-center space-x-4">
                    <div class="flex flex-col text-right hidden sm:block">
                        <p class="text-sm font-bold text-slate-200">{{ auth()->user()->name ?? 'Admin' }}</p>
                        <p class="text-[10px] text-violet-400 font-bold uppercase tracking-widest">Contest Manager</p>
                    </div>
                    <div class="w-10 h-10 bg-gradient-to-br from-violet-500 to-indigo-600 rounded-xl flex items-center justify-center glow-sm">
                        <i class="fas fa-user text-white text-sm"></i>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-8">
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                        class="mb-6 bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 px-6 py-4 rounded-2xl flex justify-between items-center glow-sm">
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
