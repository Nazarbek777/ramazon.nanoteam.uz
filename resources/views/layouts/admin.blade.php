<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - Premium Test System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <!-- KaTeX -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>

    <!-- CKEditor -->
    <script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>

    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #f8fafc; }
        .sidebar-link.active {
            background-color: rgba(99, 102, 241, 0.1);
            color: #4f46e5;
            border-right: 4px solid #4f46e5;
        }
        [x-cloak] { display: none !important; }
        .ck-editor__editable {
            min-height: 200px;
            border-bottom-left-radius: 1rem !important;
            border-bottom-right-radius: 1rem !important;
        }
    </style>
</head>
<body class="antialiased text-slate-800">
    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: true }">
        <!-- Sidebar -->
        <aside class="relative w-72 bg-white border-r border-slate-200 transition-all duration-300 flex flex-col"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0 lg:w-20'">
            
            <!-- Logo area -->
            <div class="h-20 flex items-center px-6 border-b border-slate-100">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shrink-0">
                    <i class="fas fa-microchip text-white"></i>
                </div>
                <span class="ml-3 font-bold text-xl tracking-tight transition-opacity duration-300"
                      :class="sidebarOpen ? 'opacity-100' : 'opacity-0 lg:hidden'">ADMIN<span class="text-indigo-600">PRO</span></span>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto p-4 space-y-2">
                <p class="text-[10px] uppercase font-bold text-slate-400 px-3 mb-2" :class="!sidebarOpen && 'hidden'">Asosiy</p>
                
                <a href="{{ route('admin.stats.index') }}" 
                   class="sidebar-link group flex items-center px-4 py-3 rounded-xl transition duration-200 hover:bg-slate-50 {{ request()->routeIs('admin.stats.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line w-6 text-indigo-500"></i>
                    <span class="ml-3 font-semibold" :class="!sidebarOpen && 'lg:hidden'">Statistika</span>
                </a>
                
                <a href="{{ route('admin.subjects.index') }}" 
                   class="sidebar-link group flex items-center px-4 py-3 rounded-xl transition duration-200 hover:bg-slate-50 {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}">
                    <i class="fas fa-layer-group w-6 text-indigo-500"></i>
                    <span class="ml-3 font-semibold" :class="!sidebarOpen && 'lg:hidden'">Fanlar</span>
                </a>

                <a href="{{ route('admin.questions.index') }}" 
                   class="sidebar-link group flex items-center px-4 py-3 rounded-xl transition duration-200 hover:bg-slate-50 {{ request()->routeIs('admin.questions.*') ? 'active' : '' }}">
                    <i class="fas fa-question-circle w-6 text-indigo-500"></i>
                    <span class="ml-3 font-semibold" :class="!sidebarOpen && 'lg:hidden'">Savollar</span>
                </a>

                <a href="{{ route('admin.quizzes.index') }}" 
                   class="sidebar-link group flex items-center px-4 py-3 rounded-xl transition duration-200 hover:bg-slate-50 {{ request()->routeIs('admin.quizzes.*') ? 'active' : '' }}">
                    <i class="fas fa-tasks w-6 text-indigo-500"></i>
                    <span class="ml-3 font-semibold" :class="!sidebarOpen && 'lg:hidden'">Testlar</span>
                </a>

                <a href="{{ route('admin.broadcast.index') }}" 
                   class="sidebar-link group flex items-center px-4 py-3 rounded-xl transition duration-200 hover:bg-slate-50 {{ request()->routeIs('admin.broadcast.*') ? 'active' : '' }}">
                    <i class="fas fa-bullhorn w-6 text-indigo-500"></i>
                    <span class="ml-3 font-semibold" :class="!sidebarOpen && 'lg:hidden'">Broadcast</span>
                </a>
            </nav>

            <!-- Logout -->
            <div class="p-4 border-t border-slate-100">
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-4 py-3 rounded-xl text-red-500 hover:bg-red-50 transition duration-200 font-bold">
                        <i class="fas fa-sign-out-alt w-6"></i>
                        <span class="ml-3" :class="!sidebarOpen && 'lg:hidden'">Chiqish</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-8 shrink-0">
                <button @click="sidebarOpen = !sidebarOpen" class="text-slate-500 hover:text-indigo-600 transition">
                    <i class="fas fa-bars-staggered text-xl"></i>
                </button>

                <div class="flex items-center space-x-4">
                    <div class="flex flex-col text-right hidden sm:block">
                        <p class="text-sm font-bold text-slate-800">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-indigo-600 font-bold uppercase tracking-widest">Administrator</p>
                    </div>
                    <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name }}&background=6366f1&color=fff" 
                         alt="Avatar" class="w-10 h-10 rounded-xl shadow-lg shadow-indigo-100">
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-8">
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                         class="mb-6 bg-emerald-50 border-emerald-500 text-emerald-700 px-6 py-4 rounded-2xl flex justify-between items-center shadow-sm">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-3"></i>
                            <span class="font-semibold">{{ session('success') }}</span>
                        </div>
                        <button @click="show = false"><i class="fas fa-times"></i></button>
                    </div>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
