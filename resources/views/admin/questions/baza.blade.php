@extends('layouts.admin')

@section('title', $baza->name . ' — Savollar')

@section('content')
{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-sm text-gray-400 mb-5 flex-wrap">
    <a href="{{ route('admin.questions.index') }}" class="hover:text-indigo-600">Savollar</a>
    <i class="fas fa-chevron-right text-[10px]"></i>
    <a href="{{ route('admin.questions.subject', $subject) }}" class="hover:text-indigo-600">{{ $subject->name }}</a>
    @if($baza->parent)
    <i class="fas fa-chevron-right text-[10px]"></i>
    <span class="text-gray-500">{{ $baza->parent->name }}</span>
    @endif
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
            <p class="text-sm text-gray-400">{{ $questions->count() }} ta savol</p>
        </div>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.subjects.show', $subject) }}"
           class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50">
            <i class="fas fa-sitemap mr-1"></i> Bazalar
        </a>
        @if(auth()->user()->hasPermission('questions.create'))
        <a href="{{ route('admin.questions.create', ['subject_id' => $subject->id, 'baza_id' => $baza->id]) }}"
           class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-5 rounded-xl transition shadow flex items-center gap-2">
            <i class="fas fa-plus"></i> Savol qo'shish
        </a>
        @endif
    </div>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 mb-4 text-sm">✅ {{ session('success') }}</div>
@endif

{{-- Sub-bazalar (if any) --}}
@if($childBazalar->isNotEmpty())
<div class="mb-5">
    <p class="text-xs font-bold text-gray-400 uppercase mb-2">Ichki bazalar</p>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
        @foreach($childBazalar as $child)
        <a href="{{ route('admin.questions.baza', [$subject, $child]) }}"
           class="bg-white rounded-xl border border-gray-100 hover:border-amber-200 hover:shadow-sm transition p-3 flex items-center gap-2 group">
            <i class="fas fa-folder text-amber-400 text-sm"></i>
            <div>
                <p class="text-xs font-semibold text-gray-700 group-hover:text-amber-600">{{ $child->name }}</p>
                <p class="text-[10px] text-gray-400">{{ $child->questions_count }} savol</p>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif

{{-- All bazalar for move (load all bazalar in this subject except current) --}}
@php
    $allBazalar = \App\Models\Baza::where('subject_id', $subject->id)
        ->where('id', '!=', $baza->id)
        ->orderBy('name')
        ->get();
@endphp

{{-- Questions list --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    @forelse($questions as $i => $question)
    <div class="flex items-start justify-between px-5 py-4 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition group">
        <div class="flex gap-3 flex-1 min-w-0">
            <span class="text-xs font-bold text-gray-300 mt-0.5 w-6 shrink-0">{{ $i+1 }}</span>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800 leading-snug">{{ $question->content }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $question->options_count }} ta variant</p>
            </div>
        </div>
        <div class="flex items-center gap-2 ml-4 shrink-0 mt-0.5">
            {{-- Move to another baza --}}
            @if($allBazalar->isNotEmpty() && auth()->user()->hasPermission('questions.edit'))
            <form method="POST" action="{{ route('admin.bazalar.moveQuestion', [$subject, $baza]) }}" class="flex items-center gap-1">
                @csrf
                <input type="hidden" name="question_id" value="{{ $question->id }}">
                <select name="target_baza_id" onchange="this.form.submit()" title="Ko'chirish"
                        class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 text-gray-500 outline-none focus:border-indigo-400 cursor-pointer bg-white max-w-[130px]">
                    <option value="">↪ Ko'chirish</option>
                    @foreach($allBazalar as $targetBaza)
                    <option value="{{ $targetBaza->id }}">{{ $targetBaza->name }}</option>
                    @endforeach
                </select>
            </form>
            @endif

            @if(auth()->user()->hasPermission('questions.edit'))
            <a href="{{ route('admin.questions.edit', $question) }}" class="text-indigo-400 hover:text-indigo-700 text-sm p-1" title="Tahrirlash">
                <i class="fas fa-edit"></i>
            </a>
            @endif
            @if(auth()->user()->hasPermission('questions.delete'))
            <form action="{{ route('admin.questions.destroy', $question) }}" method="POST" class="inline"
                  onsubmit="return confirm('Savolni o\'chirilsinmi?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-red-400 hover:text-red-600 text-sm p-1" title="O'chirish">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
            @endif
        </div>
    </div>
    @empty
    <div class="py-12 text-center text-gray-400">
        <i class="fas fa-question-circle text-3xl mb-2 block text-gray-200"></i>
        <p class="text-sm">Bu bazada hali savol yo'q.</p>
        @if(auth()->user()->hasPermission('questions.create'))
        <a href="{{ route('admin.questions.create', ['subject_id' => $subject->id, 'baza_id' => $baza->id]) }}"
           class="text-indigo-500 font-semibold hover:underline text-sm mt-2 inline-block">
            + Birinchi savolni qo'shing
        </a>
        @endif
    </div>
    @endforelse
</div>

{{-- New sub-baza form --}}
@if(auth()->user()->hasPermission('questions.create'))
<div class="mt-6 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
    <h4 class="font-bold text-gray-700 mb-3 text-sm">📂 Ichki baza qo'shish</h4>
    <form method="POST" action="{{ route('admin.bazalar.store', $subject) }}" class="flex gap-3">
        @csrf
        <input type="hidden" name="parent_id" value="{{ $baza->id }}">
        <input type="text" name="name" placeholder="Ichki baza nomi..." required
               class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400">
        <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm">
            Qo'shish
        </button>
    </form>
</div>
@endif
@endsection
