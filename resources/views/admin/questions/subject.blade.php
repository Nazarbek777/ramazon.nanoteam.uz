@extends('layouts.admin')

@section('title', $subject->name . ' — Bazalar')

@section('content')
<div class="flex items-center gap-2 text-sm text-gray-400 mb-5">
    <a href="{{ route('admin.subjects.index') }}" class="hover:text-indigo-600">Fanlar</a>
    <i class="fas fa-chevron-right text-[10px]"></i>
    <span class="text-gray-700 font-semibold">{{ $subject->name }}</span>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 mb-4 text-sm flex items-center gap-2">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center shrink-0">
            @if($subject->icon && str_starts_with($subject->icon, 'fa'))
                <i class="{{ $subject->icon }} text-indigo-500 text-xl"></i>
            @else
                <span class="text-xl">{{ $subject->icon ?? '📚' }}</span>
            @endif
        </div>
        <div>
            <h3 class="text-xl font-bold text-gray-800">{{ $subject->name }}</h3>
            <p class="text-sm text-gray-400">{{ $bazalar->count() }} ta baza</p>
        </div>
    </div>

    {{-- Inline add baza button --}}
    @if(auth()->user()->hasPermission('questions.create'))
    <button onclick="document.getElementById('addBazaForm').classList.toggle('hidden')"
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-5 rounded-xl transition shadow text-sm">
        <i class="fas fa-plus"></i> Baza qo'shish
    </button>
    @endif
</div>

{{-- Inline add baza form --}}
@if(auth()->user()->hasPermission('questions.create'))
<div id="addBazaForm" class="hidden bg-white rounded-2xl border border-indigo-200 shadow-sm p-5 mb-5">
    <form method="POST" action="{{ route('admin.bazalar.store', $subject) }}" class="flex gap-3">
        @csrf
        <input type="hidden" name="parent_id" value="">
        <input type="text" name="name" placeholder="Baza nomi..." required autofocus
               class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400">
        <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-bold text-sm transition">
            Qo'shish
        </button>
        <button type="button" onclick="document.getElementById('addBazaForm').classList.add('hidden')"
                class="w-10 h-10 rounded-xl border border-gray-200 text-gray-400 hover:bg-gray-50 transition flex items-center justify-center">
            <i class="fas fa-times"></i>
        </button>
    </form>
</div>
@endif

{{-- Bazalar --}}
@if($bazalar->isEmpty())
<div class="bg-white rounded-2xl border border-dashed border-gray-200 py-14 text-center text-gray-400">
    <i class="fas fa-database text-4xl block mb-3 text-gray-200"></i>
    <p class="font-semibold text-sm">Bu fanda hali baza yaratilmagan</p>
    <p class="text-xs mt-1 text-gray-300">Yuqoridagi "Baza qo'shish" tugmasini bosing</p>
</div>
@else
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($bazalar as $baza)
    <a href="{{ route('admin.questions.baza', [$subject, $baza]) }}"
       class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-indigo-200 transition p-5 flex items-center gap-4 group">
        <div class="w-11 h-11 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
            <i class="fas fa-database text-indigo-400"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-bold text-gray-800 group-hover:text-indigo-600 transition text-sm truncate">{{ $baza->name }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $baza->questions_count }} ta savol</p>
        </div>
        <i class="fas fa-chevron-right text-gray-300 group-hover:text-indigo-400 transition text-xs shrink-0"></i>
    </a>
    @endforeach
</div>
@endif
@endsection
