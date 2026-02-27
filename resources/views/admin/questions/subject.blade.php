@extends('layouts.admin')

@section('title', $subject->name . ' — Bazalar')

@section('content')
<div class="flex items-center gap-2 text-sm text-gray-400 mb-5">
    <a href="{{ route('admin.subjects.index') }}" class="hover:text-indigo-600">Fanlar</a>
    <i class="fas fa-chevron-right text-[10px]"></i>
    <span class="text-gray-700 font-semibold">{{ $subject->name }}</span>
</div>

<div class="flex items-center gap-4 mb-6">
    <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center shrink-0">
        @if($subject->icon && str_starts_with($subject->icon, 'fa'))
            <i class="{{ $subject->icon }} text-indigo-500 text-xl"></i>
        @else
            <span class="text-xl">{{ $subject->icon ?? '📚' }}</span>
        @endif
    </div>
    <div>
        <h3 class="text-xl font-bold text-gray-800">{{ $subject->name }}</h3>
        <p class="text-sm text-gray-400">Savollar bazalarini tanlang</p>
    </div>
</div>

@if($bazalar->isEmpty())
<div class="bg-white rounded-2xl border border-dashed border-gray-200 py-14 text-center text-gray-400">
    <i class="fas fa-database text-4xl block mb-3 text-gray-200"></i>
    <p class="font-semibold text-sm">Bu fanda hali baza yaratilmagan</p>
    <a href="{{ route('admin.subjects.show', $subject) }}"
       class="text-indigo-500 font-semibold hover:underline text-sm mt-2 inline-block">
        + Baza qo'shish
    </a>
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
