@extends('layouts.admin')

@section('title', $subject->name . ' — Bazalar')

@section('content')
<div class="flex items-center gap-2 text-sm text-gray-400 mb-5">
    <a href="{{ route('admin.subjects.index') }}" class="hover:text-indigo-600">Fanlar</a>
    <i class="fas fa-chevron-right text-[10px]"></i>
    <span class="text-gray-700 font-semibold">{{ $subject->name }}</span>
</div>

<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-indigo-50">
            @if($subject->icon && str_starts_with($subject->icon, 'fa'))
                <i class="{{ $subject->icon }} text-indigo-500 text-xl"></i>
            @else
                <span class="text-xl">{{ $subject->icon ?? '📚' }}</span>
            @endif
        </div>
        <div>
            <h3 class="text-xl font-bold text-gray-800">{{ $subject->name }}</h3>
            <p class="text-sm text-gray-400">Savollar bazalarini boshqaring</p>
        </div>
    </div>
    <a href="{{ route('admin.subjects.edit', $subject) }}"
       class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50">
        <i class="fas fa-edit mr-1"></i> Fanni tahrirlash
    </a>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 mb-4 text-sm">✅ {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-4 text-sm">❌ {{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT: Add baza + tree --}}
    <div class="lg:col-span-1 space-y-4">

        {{-- Add baza form --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h4 class="font-bold text-gray-700 mb-3 text-sm flex items-center gap-2">
                <i class="fas fa-plus-circle text-indigo-500"></i> Yangi baza qo'shish
            </h4>
            <form method="POST" action="{{ route('admin.bazalar.store', $subject) }}" class="space-y-3">
                @csrf
                <div>
                    <input type="text" name="name" placeholder="Baza nomi..." required
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400">
                </div>
                <div>
                    <select name="parent_id"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400">
                        <option value="">— Asosiy baza (yuqori daraja) —</option>
                        @foreach($bazalar as $b)
                        <option value="{{ $b->id }}">{{ str_repeat('　', $b->depth) }}{{ $b->depth > 0 ? '└─ ' : '' }}{{ $b->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-gray-400 mt-1">Ichki baza bo'lsa yuqoridagi dropdown dan tanlang</p>
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white py-2.5 rounded-xl font-bold text-sm hover:bg-indigo-700">
                    Qo'shish
                </button>
            </form>
        </div>

        {{-- Stats --}}
        <div class="bg-indigo-50 rounded-2xl p-4 grid grid-cols-2 gap-3">
            <div class="text-center">
                <p class="text-2xl font-black text-indigo-700">{{ $bazalar->count() }}</p>
                <p class="text-xs text-indigo-400 font-semibold">Bazalar</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-black text-indigo-700">{{ $bazalar->sum('questions_count') }}</p>
                <p class="text-xs text-indigo-400 font-semibold">Jami savollar</p>
            </div>
        </div>
    </div>

    {{-- RIGHT: Baza tree --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 flex items-center gap-2">
            <i class="fas fa-sitemap text-indigo-400"></i>
            <h4 class="font-bold text-gray-700 text-sm">Bazalar daraxti</h4>
        </div>

        @forelse($bazalar as $baza)
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-50 last:border-0 hover:bg-gray-50/60 transition"
             style="padding-left: {{ ($baza->depth * 20) + 20 }}px">
            <div class="flex items-center gap-3">
                <i class="fas fa-{{ $baza->depth == 0 ? 'database' : 'folder' }}
                   text-{{ $baza->depth == 0 ? 'indigo' : 'amber' }}-400 text-sm w-4"></i>
                <div>
                    <p class="text-sm font-semibold text-gray-800">{{ $baza->name }}</p>
                    <p class="text-xs text-gray-400">{{ $baza->questions_count }} ta savol</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('admin.questions.baza', [$subject, $baza]) }}"
                   class="text-xs px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 font-semibold">
                    <i class="fas fa-eye mr-1"></i>Savollar
                </a>
                <form method="POST" action="{{ route('admin.bazalar.destroy', [$subject, $baza]) }}"
                      onsubmit="return confirm('Bazani o\'chirilsinmi? Ichki bazalar ham o\'chadi!')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-400 hover:text-red-600 px-2 py-1.5 text-sm rounded-lg hover:bg-red-50" title="O'chirish">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="py-12 text-center text-gray-400">
            <i class="fas fa-inbox text-3xl block mb-2 text-gray-200"></i>
            <p class="text-sm">Hali baza qo'shilmagan.</p>
            <p class="text-xs mt-1">Chap tomondagi formadan baza yarating.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
