@extends('layouts.admin')

@section('title', $baza->name . ' — Savollar')

@section('content')
{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-sm text-gray-400 mb-5 flex-wrap">
    <a href="{{ route('admin.subjects.index') }}" class="hover:text-indigo-600">Fanlar</a>
    <i class="fas fa-chevron-right text-[10px]"></i>
    <a href="{{ route('admin.subjects.show', $subject) }}" class="hover:text-indigo-600">{{ $subject->name }}</a>
    <i class="fas fa-chevron-right text-[10px]"></i>
    <span class="text-gray-700 font-semibold">{{ $baza->name }}</span>
</div>

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center">
            <i class="fas fa-database text-indigo-500"></i>
        </div>
        <div>
            <h3 class="text-xl font-bold text-gray-800">{{ $baza->name }}</h3>
            <p class="text-sm text-gray-400 flex items-center gap-2">
                <span>{{ $questions->count() }} ta savol</span>
                <span class="text-gray-200">|</span>
                <span class="text-indigo-500 font-semibold">{{ $subject->name }}</span>
            </p>
        </div>
    </div>
    @if(auth()->user()->hasPermission('questions.create'))
    <a href="{{ route('admin.questions.create', ['subject_id' => $subject->id, 'baza_id' => $baza->id]) }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-5 rounded-xl transition shadow flex items-center gap-2">
        <i class="fas fa-plus"></i> Savol qo'shish
    </a>
    @endif
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 mb-4 text-sm flex items-center gap-2">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

{{-- All bazalar of this subject (for move dropdown) --}}
@php
    $otherBazalar = \App\Models\Baza::where('subject_id', $subject->id)
        ->where('id', '!=', $baza->id)
        ->orderBy('name')
        ->get();
@endphp

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
        <div class="flex items-center gap-2 ml-4 shrink-0 mt-0.5">
            {{-- Move to another baza --}}
            @if($otherBazalar->isNotEmpty() && auth()->user()->hasPermission('questions.edit'))
            <form method="POST" action="{{ route('admin.bazalar.moveQuestion', [$subject, $baza]) }}">
                @csrf
                <input type="hidden" name="question_id" value="{{ $question->id }}">
                <select name="target_baza_id" onchange="this.form.submit()" title="Boshqa bazaga ko'chirish"
                        class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 text-gray-500 outline-none focus:border-indigo-400 cursor-pointer bg-white max-w-[140px]">
                    <option value="">↪ Ko'chirish</option>
                    @foreach($otherBazalar as $target)
                    <option value="{{ $target->id }}">{{ $target->name }}</option>
                    @endforeach
                </select>
            </form>
            @endif

            @if(auth()->user()->hasPermission('questions.edit'))
            <a href="{{ route('admin.questions.edit', $question) }}"
               class="w-8 h-8 rounded-lg flex items-center justify-center text-indigo-400 hover:text-indigo-700 hover:bg-indigo-50 transition text-sm" title="Tahrirlash">
                <i class="fas fa-edit"></i>
            </a>
            @endif
            @if(auth()->user()->hasPermission('questions.delete'))
            <form action="{{ route('admin.questions.destroy', $question) }}" method="POST" class="inline"
                  onsubmit="return confirm('Savolni o\'chirilsinmi?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="w-8 h-8 rounded-lg flex items-center justify-center text-red-400 hover:text-red-600 hover:bg-red-50 transition text-sm" title="O'chirish">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
            @endif
        </div>
    </div>
    @empty
    <div class="py-14 text-center text-gray-400">
        <i class="fas fa-question-circle text-4xl block mb-3 text-gray-200"></i>
        <p class="font-semibold text-sm">Bu bazada hali savol yo'q</p>
        @if(auth()->user()->hasPermission('questions.create'))
        <a href="{{ route('admin.questions.create', ['subject_id' => $subject->id, 'baza_id' => $baza->id]) }}"
           class="text-indigo-500 font-semibold hover:underline text-sm mt-3 inline-block">
            + Birinchi savolni qo'shing
        </a>
        @endif
    </div>
    @endforelse
</div>
@endsection
