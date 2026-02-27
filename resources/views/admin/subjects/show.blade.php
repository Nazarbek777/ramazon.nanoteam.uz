@extends('layouts.admin')

@section('title', $subject->name . ' — Bazalar')

@section('content')

{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-sm text-gray-400 mb-5">
    <a href="{{ route('admin.subjects.index') }}" class="hover:text-indigo-600">Fanlar</a>
    <i class="fas fa-chevron-right text-[10px]"></i>
    <span class="text-gray-700 font-semibold">{{ $subject->name }}</span>
</div>

{{-- Header --}}
<div class="flex items-center gap-4 mb-6">
    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-indigo-50 shrink-0">
        @if($subject->icon && str_starts_with($subject->icon, 'fa'))
            <i class="{{ $subject->icon }} text-indigo-500 text-xl"></i>
        @else
            <span class="text-xl">{{ $subject->icon ?? '📚' }}</span>
        @endif
    </div>
    <div>
        <h3 class="text-xl font-bold text-gray-800">{{ $subject->name }}</h3>
        <p class="text-sm text-gray-400">{{ $bazalar->count() }} ta baza &nbsp;·&nbsp; {{ $bazalar->sum('questions_count') }} ta savol</p>
    </div>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 mb-4 text-sm flex items-center gap-2">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Add Baza Form --}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 sticky top-4">
            <h4 class="font-bold text-gray-700 mb-4 text-sm flex items-center gap-2">
                <i class="fas fa-plus-circle text-indigo-500"></i> Yangi baza qo'shish
            </h4>
            <form method="POST" action="{{ route('admin.bazalar.store', $subject) }}" class="space-y-3">
                @csrf
                <input type="hidden" name="parent_id" value="">
                <input type="text" name="name" placeholder="Baza nomi..." required autofocus
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400">
                <button type="submit"
                        class="w-full bg-indigo-600 text-white py-3 rounded-xl font-bold text-sm hover:bg-indigo-700 transition">
                    <i class="fas fa-plus mr-1"></i> Qo'shish
                </button>
            </form>
        </div>
    </div>

    {{-- Bazalar list --}}
    <div class="lg:col-span-2">
        @forelse($bazalar as $baza)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm mb-3 flex items-center justify-between px-5 py-4 hover:border-indigo-200 hover:shadow-md transition group">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
                    <i class="fas fa-database text-indigo-400"></i>
                </div>
                <div>
                    <p class="font-bold text-gray-800 group-hover:text-indigo-700 transition text-sm">{{ $baza->name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $baza->questions_count }} ta savol</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.questions.baza', [$subject, $baza]) }}"
                   class="inline-flex items-center gap-1.5 text-sm font-bold px-4 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition">
                    <i class="fas fa-list text-xs"></i> Savollar
                </a>
                <form method="POST" action="{{ route('admin.bazalar.destroy', [$subject, $baza]) }}"
                      onsubmit="return confirm('\"{{ $baza->name }}\" bazasini o\'chirilsinmi?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="w-9 h-9 rounded-xl border border-gray-200 text-red-400 hover:text-red-600 hover:border-red-200 hover:bg-red-50 transition flex items-center justify-center">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-dashed border-gray-200 py-14 text-center text-gray-400">
            <i class="fas fa-database text-4xl block mb-3 text-gray-200"></i>
            <p class="font-semibold text-sm">Hali baza yaratilmagan</p>
            <p class="text-xs mt-1 text-gray-300">Chap tomondagi formadan baza qo'shing</p>
        </div>
        @endforelse
    </div>

</div>
@endsection
