@extends('layouts.admin')

@section('title', $subject->name . ' — Bazalar')

@section('content')
<div class="flex items-center gap-2 text-sm text-gray-400 mb-5">
    <a href="{{ route('admin.subjects.index') }}" class="hover:text-indigo-600">Fanlar</a>
    <i class="fas fa-chevron-right text-[10px]"></i>
    <span class="font-semibold text-gray-700">{{ $subject->name }} — Bazalar</span>
</div>

<div class="flex items-center justify-between mb-6">
    <div>
        <h3 class="text-xl font-bold text-gray-800">{{ $subject->name }}</h3>
        <p class="text-sm text-gray-400">Savollar bazalarini boshqaring</p>
    </div>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 mb-4 text-sm">✅ {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-4 text-sm">❌ {{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Add baza form --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h4 class="font-bold text-gray-700 mb-4">➕ Yangi baza qo'shish</h4>
        <form method="POST" action="{{ route('admin.bazalar.store', $subject) }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Baza nomi</label>
                <input type="text" name="name" required placeholder="Masalan: Kadrlar bo'yicha buyruqlar"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Ichki baza (ixtiyoriy)</label>
                <select name="parent_id"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400">
                    <option value="">— Yuqori daraja (asosiy baza) —</option>
                    @foreach($bazalar as $b)
                    <option value="{{ $b->id }}">{{ str_repeat('　', $b->depth) }}{{ $b->depth > 0 ? '└─ ': '' }}{{ $b->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">Biror bazaning ichiga kiruvchi baza bo'lsa tanlang</p>
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white py-2.5 rounded-xl font-bold text-sm hover:bg-indigo-700">
                Qo'shish
            </button>
        </form>
    </div>

    {{-- Baza tree --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h4 class="font-bold text-gray-700 mb-4">📂 Bazalar daraxti</h4>

        @forelse($bazalar as $baza)
        <div class="flex items-center justify-between py-2.5 border-b border-gray-50 last:border-0"
             style="padding-left: {{ $baza->depth * 20 + 4 }}px">
            <div class="flex items-center gap-2">
                <i class="fas fa-{{ $baza->depth == 0 ? 'database' : 'folder' }} text-{{ $baza->depth == 0 ? 'indigo' : 'amber' }}-400 text-xs"></i>
                <div>
                    <p class="text-sm font-semibold text-gray-800">{{ $baza->name }}</p>
                    <p class="text-xs text-gray-400">{{ $baza->questions_count }} ta savol</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('admin.questions.subject', ['subject' => $subject->id, 'baza_id' => $baza->id]) }}"
                   class="text-xs px-2 py-1 rounded-lg bg-gray-100 text-gray-600 hover:bg-indigo-50 hover:text-indigo-600">
                    Savollar
                </a>
                <form method="POST" action="{{ route('admin.bazalar.destroy', [$subject, $baza]) }}"
                      onsubmit="return confirm('O\'chirilsinmi?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-400 hover:text-red-600 text-xs px-1">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="text-center py-8 text-gray-400 text-sm">
            <i class="fas fa-database text-3xl block mb-2 text-gray-200"></i>
            Hali baza qo'shilmagan.
        </div>
        @endforelse
    </div>
</div>
@endsection
