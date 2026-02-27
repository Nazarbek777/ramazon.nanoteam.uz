@extends('layouts.admin')

@section('title', $subject->name . ' — Testlar')

@section('content')
{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-sm text-gray-400 mb-5">
    <a href="{{ route('admin.quizzes.index') }}" class="hover:text-indigo-600 transition">Testlar</a>
    <i class="fas fa-chevron-right text-[10px]"></i>
    <span class="text-gray-700 font-semibold">{{ $subject->name }}</span>
</div>

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-xl">
            @if($subject->icon && str_starts_with($subject->icon, 'fa'))
                <i class="{{ $subject->icon }} text-indigo-500"></i>
            @else
                {{ $subject->icon ?? '📚' }}
            @endif
        </div>
        <div>
            <h3 class="text-xl font-bold text-gray-800">{{ $subject->name }}</h3>
            <p class="text-sm text-gray-400">{{ $quizzes->count() }} ta test</p>
        </div>
    </div>
    @can('quizzes.create')
    @endcan
    @if(auth()->user()->hasPermission('quizzes.create'))
    <a href="{{ route('admin.quizzes.create', ['subject_id' => $subject->id]) }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-5 rounded-xl transition shadow flex items-center gap-2">
        <i class="fas fa-plus"></i> Yangi test qo'shish
    </a>
    @endif
</div>

{{-- Quizzes list --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    @forelse($quizzes as $quiz)
    @php
        $now = now();
        $isExpired   = $quiz->ends_at && $quiz->ends_at < $now;
        $isNotStarted = $quiz->starts_at && $quiz->starts_at > $now;
    @endphp
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition {{ $isExpired ? 'opacity-60' : '' }}">
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <p class="font-semibold text-gray-800">{{ $quiz->title }}</p>
                @if($quiz->access_code)
                    <span class="font-mono text-[11px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded">{{ $quiz->access_code }}</span>
                @endif
                @if($isExpired)
                    <span class="text-[10px] font-bold bg-red-100 text-red-500 px-2 py-0.5 rounded-full uppercase">Tugagan</span>
                @elseif($isNotStarted)
                    <span class="text-[10px] font-bold bg-amber-100 text-amber-600 px-2 py-0.5 rounded-full uppercase">Boshlanmagan</span>
                @else
                    <span class="text-[10px] font-bold bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded-full uppercase">Faol</span>
                @endif
            </div>
            <p class="text-xs text-gray-400 mt-1">
                {{ $quiz->time_limit }} daqiqa &bull; O'tish: {{ $quiz->pass_score }}%
                @if($quiz->starts_at) &bull; <i class="fas fa-play-circle text-emerald-400"></i> {{ $quiz->starts_at->format('d.m.Y H:i') }} @endif
                @if($quiz->ends_at)   &bull; <i class="fas fa-stop-circle text-red-400"></i> {{ $quiz->ends_at->format('d.m.Y H:i') }}     @endif
            </p>
        </div>
        <div class="flex items-center gap-3 ml-4 shrink-0">
            @if(auth()->user()->hasPermission('quizzes.edit'))
            <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="text-indigo-400 hover:text-indigo-700 text-sm" title="Tahrirlash">
                <i class="fas fa-edit"></i>
            </a>
            @endif
            @if(auth()->user()->hasPermission('quizzes.delete'))
            <form action="{{ route('admin.quizzes.destroy', $quiz) }}" method="POST" class="inline"
                  onsubmit="return confirm('Testni o\'chirmoqchimisiz?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-red-400 hover:text-red-600 text-sm" title="O'chirish">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
            @endif
        </div>
    </div>
    @empty
    <div class="py-12 text-center text-gray-400">
        <i class="fas fa-clipboard text-3xl mb-2 block text-gray-200"></i>
        Bu fanda hali test yaratilmagan.
        @if(auth()->user()->hasPermission('quizzes.create'))
        <div class="mt-3">
            <a href="{{ route('admin.quizzes.create', ['subject_id' => $subject->id]) }}"
               class="text-indigo-500 font-semibold hover:underline text-sm">+ Birinchi testni yarating</a>
        </div>
        @endif
    </div>
    @endforelse
</div>
@endsection
