@extends('layouts.admin')

@section('title', 'Admin Ruxsatlari')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <h3 class="text-2xl font-bold text-gray-800">Admin Ruxsatlari</h3>
        <p class="text-gray-500 text-sm mt-1">Adminlarga sahifa kirish ruxsatlarini boshqaring</p>
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

    {{-- Make someone admin --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-6">
        <h4 class="font-bold text-gray-700 mb-3">Foydalanuvchini admin qilish</h4>
        <form method="POST" action="{{ route('admin.permissions.make-admin') }}" class="flex gap-2">
            @csrf
            <input type="number" name="user_id" placeholder="User ID kiriting"
                   class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400">
            <button type="submit" class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-indigo-700">
                Admin qilish
            </button>
        </form>
    </div>

    {{-- Admins list with permissions --}}
    <div class="space-y-4">
        @forelse($admins as $admin)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="font-bold text-gray-800">{{ $admin->name }}</h4>
                    <p class="text-xs text-gray-400">{{ $admin->email }} | ID: {{ $admin->id }}</p>
                </div>
                <form method="POST" action="{{ route('admin.permissions.remove', $admin) }}"
                      onsubmit="return confirm('Admin huquqini olish?')">
                    @csrf
                    <button type="submit" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-red-50 text-red-500 hover:bg-red-100">
                        Admin huquqini olish
                    </button>
                </form>
            </div>

            <form method="POST" action="{{ route('admin.permissions.store') }}">
                @csrf
                <input type="hidden" name="admin_id" value="{{ $admin->id }}">
                <p class="text-xs font-black text-gray-500 uppercase tracking-wide mb-2">Sahifa ruxsatlari:</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-3">
                    @foreach($pages as $key => $label)
                    <label class="flex items-center gap-2 cursor-pointer bg-gray-50 px-3 py-2 rounded-xl hover:bg-indigo-50 transition-all">
                        <input type="checkbox" name="permissions[]" value="{{ $key }}"
                               {{ $admin->permissions->pluck('page')->contains($key) ? 'checked' : '' }}
                               class="accent-indigo-600 w-4 h-4">
                        <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                    </label>
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
            Hali adminlar yo'q. Yuqoridan foydalanuvchini admin qiling.
        </div>
        @endforelse
    </div>
</div>
@endsection
