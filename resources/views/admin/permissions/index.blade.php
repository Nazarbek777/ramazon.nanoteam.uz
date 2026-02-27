@extends('layouts.admin')

@section('title', 'Admin Ruxsatlari')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <h3 class="text-2xl font-bold text-gray-800">Admin Ruxsatlari</h3>
        <p class="text-gray-500 text-sm mt-1">Adminlar va ularning amal ruxsatlarini boshqaring</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-4 text-sm font-medium">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-4 text-sm font-medium">
            ❌ {{ session('error') }}
        </div>
    @endif

    {{-- Create new admin --}}
    <div x-data="{ open: false }" class="bg-white rounded-2xl border border-gray-100 shadow-sm mb-6">
        <button @click="open = !open" class="w-full flex items-center justify-between px-5 py-4 font-bold text-gray-700 hover:bg-gray-50 rounded-2xl">
            <span>➕ Yangi admin yaratish</span>
            <i class="fas fa-chevron-down transition-transform" :class="open && 'rotate-180'"></i>
        </button>
        <div x-show="open" x-transition class="border-t border-gray-100 p-5">
            <form method="POST" action="{{ route('admin.permissions.create-admin') }}" class="space-y-3">
                @csrf
                @if($errors->any())
                    <div class="bg-red-50 text-red-600 text-sm px-4 py-2 rounded-xl">{{ $errors->first() }}</div>
                @endif
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <input type="text" name="name" placeholder="Ism" value="{{ old('name') }}"
                           class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400" required>
                    <input type="email" name="email" placeholder="Email" value="{{ old('email') }}"
                           class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400" required>
                    <input type="password" name="password" placeholder="Parol (min 6 belgi)"
                           class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400" required>
                </div>
                <div>
                    <p class="text-xs font-black text-gray-500 uppercase tracking-wide mb-2">Ruxsatlar:</p>
                    @php
                        $grouped = collect($pages)->groupBy(fn($v,$k) => explode('.', $k)[0], true);
                    @endphp
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($grouped as $section => $actions)
                        <div class="bg-gray-50 rounded-xl p-3">
                            <p class="text-xs font-black text-gray-500 uppercase mb-2">{{ ucfirst($section) }}</p>
                            @foreach($actions as $key => $label)
                            <label class="flex items-center gap-2 py-1 cursor-pointer hover:text-indigo-600">
                                <input type="checkbox" name="permissions[]" value="{{ $key }}" class="accent-indigo-600 w-3.5 h-3.5">
                                <span class="text-xs text-gray-700">{{ str_replace("$section — ", '', str_replace(['👁 ','➕ ','✏️ ','🗑 ','📤 ','🚫 '], '', $label)) }}</span>
                            </label>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white py-2.5 rounded-xl text-sm font-bold hover:bg-indigo-700 transition-all">
                    ✅ Admin yaratish
                </button>
            </form>
        </div>
    </div>

    {{-- Admins list --}}
    <div class="space-y-4">
        @forelse($admins as $admin)
        <div x-data="{ editOpen: false }" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-50">
                <div>
                    <h4 class="font-bold text-gray-800">{{ $admin->name }}</h4>
                    <p class="text-xs text-gray-400">{{ $admin->email }} | ID: {{ $admin->id }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="editOpen = !editOpen" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100">
                        ✏️ Tahrirlash
                    </button>
                    <form method="POST" action="{{ route('admin.permissions.remove', $admin) }}"
                          onsubmit="return confirm('Admin huquqini olish?')">
                        @csrf
                        <button type="submit" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-red-50 text-red-500 hover:bg-red-100">
                            Admin huquqini olish
                        </button>
                    </form>
                </div>
            </div>

            {{-- Edit admin info --}}
            <div x-show="editOpen" x-transition class="px-5 py-4 bg-gray-50 border-b border-gray-100">
                <form method="POST" action="{{ route('admin.permissions.update', $admin) }}" class="flex flex-wrap gap-2">
                    @csrf
                    <input type="text" name="name" value="{{ $admin->name }}" placeholder="Ism"
                           class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500/20 min-w-[140px]" required>
                    <input type="email" name="email" value="{{ $admin->email }}" placeholder="Email"
                           class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500/20 min-w-[140px]" required>
                    <input type="password" name="password" placeholder="Yangi parol (ixtiyoriy)"
                           class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500/20 min-w-[140px]">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-indigo-700">
                        Saqlash
                    </button>
                </form>
            </div>

            {{-- Permissions checkboxes --}}
            <form method="POST" action="{{ route('admin.permissions.store') }}" class="p-5">
                @csrf
                <input type="hidden" name="admin_id" value="{{ $admin->id }}">
                <p class="text-xs font-black text-gray-500 uppercase tracking-wide mb-3">Amal ruxsatlari:</p>
                @php
                    $grouped = collect($pages)->groupBy(fn($v,$k) => explode('.', $k)[0], true);
                    $adminPerms = $admin->permissions->pluck('page');
                @endphp
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-4">
                    @foreach($grouped as $section => $actions)
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs font-black text-gray-400 uppercase mb-2">{{ ucfirst($section) }}</p>
                        @foreach($actions as $key => $label)
                        <label class="flex items-center gap-2 py-1 cursor-pointer hover:text-indigo-600">
                            <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                   {{ $adminPerms->contains($key) ? 'checked' : '' }}
                                   class="accent-indigo-600 w-3.5 h-3.5">
                            <span class="text-xs text-gray-700">{{ str_replace("$section — ", '', str_replace(['👁 ','➕ ','✏️ ','🗑 ','📤 ','🚫 '], '', $label)) }}</span>
                        </label>
                        @endforeach
                    </div>
                    @endforeach
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-xl text-sm font-bold hover:bg-indigo-700 transition-all">
                    💾 Ruxsatlarni saqlash
                </button>
            </form>
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-dashed border-gray-200 p-10 text-center text-gray-400">
            <i class="fas fa-user-shield text-3xl mb-2 block"></i>
            Hali adminlar yo'q.
        </div>
        @endforelse
    </div>
</div>
@endsection
