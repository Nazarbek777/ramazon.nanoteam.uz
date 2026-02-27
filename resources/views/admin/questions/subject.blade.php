@extends('layouts.admin')

@section('title', $subject->name . ' — Savollar')

@section('content')
{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-sm text-gray-400 mb-5">
    <a href="{{ route('admin.questions.index') }}" class="hover:text-indigo-600 transition">Savollar</a>
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
            <p class="text-sm text-gray-400">{{ $questions->count() }} ta savol</p>
        </div>
    </div>
    @if(auth()->user()->hasPermission('questions.create'))
    <a href="{{ route('admin.questions.create', ['subject_id' => $subject->id]) }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-5 rounded-xl transition shadow flex items-center gap-2">
        <i class="fas fa-plus"></i> Yangi savol qo'shish
    </a>
    @endif
</div>

{{-- Questions list --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    @forelse($questions as $i => $question)
    <div class="flex items-start justify-between px-5 py-4 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition">
        <div class="flex gap-3 flex-1 min-w-0">
            <span class="text-xs font-bold text-gray-300 mt-0.5 w-6 shrink-0">{{ $i+1 }}</span>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800 leading-snug">{{ $question->content }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $question->options_count }} ta variant</p>
            </div>
        </div>
        <div class="flex items-center gap-3 ml-4 shrink-0 mt-0.5">
            @if(auth()->user()->hasPermission('questions.edit'))
            <a href="{{ route('admin.questions.edit', $question) }}" class="text-indigo-400 hover:text-indigo-700 text-sm" title="Tahrirlash">
                <i class="fas fa-edit"></i>
            </a>
            @endif
            @if(auth()->user()->hasPermission('questions.delete'))
            <form action="{{ route('admin.questions.destroy', $question) }}" method="POST" class="inline"
                  onsubmit="return confirm('Savolni o\'chirilsinmi?')">
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
        <i class="fas fa-question-circle text-3xl mb-2 block text-gray-200"></i>
        Bu fanda hali savol yaratilmagan.
        @if(auth()->user()->hasPermission('questions.create'))
        <div class="mt-3">
            <a href="{{ route('admin.questions.create', ['subject_id' => $subject->id]) }}"
               class="text-indigo-500 font-semibold hover:underline text-sm">+ Birinchi savolni yarating</a>
        </div>
        @endif
    </div>
    @endforelse
</div>
@endsection
