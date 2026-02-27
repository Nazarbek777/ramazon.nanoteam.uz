@extends('layouts.admin')

@section('title', $quiz->title . ' — Baza sozlash')

@section('content')
{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-sm text-gray-400 mb-5">
    <a href="{{ route('admin.quizzes.subject', $quiz->subject) }}" class="hover:text-indigo-600">{{ $quiz->subject->name }}</a>
    <i class="fas fa-chevron-right text-[10px]"></i>
    <span class="font-semibold text-gray-700">{{ $quiz->title }}</span>
    <i class="fas fa-chevron-right text-[10px]"></i>
    <span class="text-gray-500">Bazalar</span>
</div>

<div class="flex items-center justify-between mb-6">
    <div>
        <h3 class="text-xl font-bold text-gray-800">{{ $quiz->title }}</h3>
        <p class="text-sm text-gray-400">Test uchun savollar bazasini sozlang</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.quizzes.edit', $quiz) }}"
           class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50">
            <i class="fas fa-edit mr-1"></i> Test tahrirlash
        </a>
        <a href="{{ route('admin.quizzes.subject', $quiz->subject) }}"
           class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
            <i class="fas fa-check mr-1"></i> Tayyor
        </a>
    </div>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 mb-4 text-sm font-medium">
    ✅ {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-4 text-sm font-medium">
    ❌ {{ session('error') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- LEFT: Add baza --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h4 class="font-bold text-gray-700 mb-4 flex items-center gap-2">
            <i class="fas fa-plus-circle text-indigo-500"></i> Baza qo'shish
        </h4>

        @if($bazalar->isEmpty())
        <p class="text-gray-400 text-sm italic">Bu fanda hali baza (ichki fan) yo'q. Avval fanlar bo'limida ichki fan yarating.</p>
        @else
        <form method="POST" action="{{ route('admin.quizzes.source.store', $quiz) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Baza (ichki fan)</label>
                <select name="subject_id" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400">
                    <option value="">— Tanlang —</option>
                    @foreach($bazalar as $baza)
                    <option value="{{ $baza->id }}">
                        {{ $baza->name }} ({{ $baza->questions_count }} ta savol)
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Savollar soni</label>
                <input type="number" name="count" min="1" max="500" value="10" required
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400">
                <p class="text-xs text-gray-400 mt-1">Bu bazadan nechta random savol olinadi</p>
            </div>
            <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2.5 rounded-xl font-bold text-sm hover:bg-indigo-700 transition">
                <i class="fas fa-plus mr-1"></i> Qo'shish
            </button>
        </form>
        @endif
    </div>

    {{-- RIGHT: Current sources --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h4 class="font-bold text-gray-700 mb-4 flex items-center gap-2">
            <i class="fas fa-database text-indigo-500"></i> Qo'shilgan bazalar
        </h4>

        @forelse($quiz->sources as $source)
        <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
            <div>
                <p class="font-semibold text-sm text-gray-800">{{ $source->subject->name }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $source->count }} ta random savol</p>
            </div>
            <form method="POST" action="{{ route('admin.quizzes.source.delete', [$quiz, $source]) }}"
                  onsubmit="return confirm('O\'chirilsinmi?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-red-400 hover:text-red-600 text-sm px-2 py-1">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
        @empty
        <div class="text-center py-8 text-gray-400">
            <i class="fas fa-database text-3xl block mb-2 text-gray-200"></i>
            <p class="text-sm">Hali baza qo'shilmagan.</p>
            <p class="text-xs mt-1">Baza qo'shing — test o'tilganda shu bazalardan savollar olinadi.</p>
        </div>
        @endforelse

        @if($quiz->sources->isNotEmpty())
        <div class="mt-4 bg-indigo-50 rounded-xl px-4 py-3 flex items-center justify-between">
            <span class="text-sm font-semibold text-indigo-700">Jami savollar:</span>
            <span class="text-lg font-black text-indigo-600">{{ $quiz->sources->sum('count') }} ta</span>
        </div>
        @endif
    </div>
</div>
@endsection
