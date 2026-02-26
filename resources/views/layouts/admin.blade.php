<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - @yield('title')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-indigo-900 text-white flex-shrink-0">
            <div class="p-6">
                <h1 class="text-2xl font-bold tracking-wider">TEST ADMIN</h1>
            </div>
            <nav class="mt-6 px-4">
                <a href="#" class="flex items-center py-3 px-4 rounded-lg bg-indigo-800 text-white mb-2">
                    <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                </a>
                <a href="{{ route('admin.subjects.index') }}" class="flex items-center py-3 px-4 rounded-lg hover:bg-indigo-800 text-indigo-100 transition mb-2">
                    <i class="fas fa-book mr-3"></i> Fanlar
                </a>
                <a href="{{ route('admin.questions.index') }}" class="flex items-center py-3 px-4 rounded-lg hover:bg-indigo-800 text-indigo-100 transition mb-2">
                    <i class="fas fa-question-circle mr-3"></i> Savollar
                </a>
                <a href="{{ route('admin.quizzes.index') }}" class="flex items-center py-3 px-4 rounded-lg hover:bg-indigo-800 text-indigo-100 transition mb-2">
                    <i class="fas fa-tasks mr-3"></i> Testlar
                </a>
                <form action="{{ route('admin.logout') }}" method="POST" class="mt-10 px-4">
                    @csrf
                    <button type="submit" class="w-full flex items-center py-3 px-4 rounded-lg hover:bg-red-800 text-indigo-100 transition whitespace-nowrap">
                        <i class="fas fa-sign-out-alt mr-3"></i> Chiqish
                    </button>
                </form>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <header class="bg-white shadow-sm py-4 px-8 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">@yield('title')</h2>
                <div class="flex items-center">
                    <span class="text-gray-600 mr-4">Admin</span>
                    <img class="h-8 w-8 rounded-full border" src="https://ui-avatars.com/api/?name=Admin" alt="Admin">
                </div>
            </header>

            <main class="p-8">
                @if(session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
