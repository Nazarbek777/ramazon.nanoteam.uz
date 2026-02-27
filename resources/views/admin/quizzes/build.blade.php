@extends('layouts.admin')

@section('title', $quiz->title . ' — Bazalar')

@section('content')
<div class="max-w-xl mx-auto mb-8">
    {{-- Step indicator --}}
    <div class="flex items-center gap-3">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center text-sm font-black">
                <i class="fas fa-check text-xs"></i>
            </div>
            <span class="text-sm font-semibold text-gray-400 line-through">Test ma'lumotlari</span>
        </div>
        <div class="flex-1 h-0.5 bg-indigo-600 rounded-full"></div>
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-black">2</div>
            <span class="text-sm font-bold text-gray-800">Bazalar tanlash</span>
        </div>
    </div>
</div>

<div class="flex items-center gap-2 text-sm text-gray-400 mb-5">
    <a href="{{ route('admin.quizzes.subject', $quiz->subject) }}" class="hover:text-indigo-600">{{ $quiz->subject->name }}</a>
    <i class="fas fa-chevron-right text-[10px]"></i>
    <span class="font-semibold text-gray-700">{{ $quiz->title }}</span>
</div>

<div class="flex items-center justify-between mb-6">
    <div>
        <h3 class="text-xl font-bold text-gray-800">{{ $quiz->title }}</h3>
        <p class="text-sm text-gray-400">Qaysi bazadan nechta savol olinishini belgilang</p>
    </div>
    <a href="{{ route('admin.quizzes.subject', $quiz->subject) }}"
       class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 transition">
        <i class="fas fa-check mr-1"></i> Tayyor
    </a>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 mb-4 text-sm flex items-center gap-2">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-4 text-sm">❌ {{ session('error') }}</div>
@endif

@if($bazalar->isEmpty())
<div class="bg-white rounded-2xl border border-dashed border-gray-200 py-14 text-center text-gray-400">
    <i class="fas fa-database text-4xl block mb-3 text-gray-200"></i>
    <p class="font-semibold text-sm">{{ $quiz->subject->name }} fanida hali baza yaratilmagan</p>
    <a href="{{ route('admin.subjects.show', $quiz->subject) }}"
       class="text-indigo-500 font-semibold hover:underline text-sm mt-2 inline-block">
        → Baza qo'shish
    </a>
</div>
@else

{{-- Summary bar if sources exist --}}
@if($quiz->sources->isNotEmpty())
<div class="bg-indigo-50 rounded-2xl px-5 py-3 mb-5 flex items-center gap-6">
    <div>
        <p class="text-xs text-indigo-400 font-bold uppercase">Tanlangan bazalar</p>
        <p class="text-2xl font-black text-indigo-700">{{ $quiz->sources->count() }}</p>
    </div>
    <div>
        <p class="text-xs text-indigo-400 font-bold uppercase">Jami savollar</p>
        <p class="text-2xl font-black text-indigo-700">{{ $quiz->sources->sum('count') }}</p>
    </div>
</div>
@endif

{{-- Baza cards --}}
<div class="space-y-3">
    @foreach($bazalar as $baza)
    @php $source = $quiz->sources->where('baza_id', $baza->id)->first(); @endphp
    <div class="bg-white rounded-2xl border {{ $source ? 'border-indigo-200 bg-indigo-50/30' : 'border-gray-100' }} shadow-sm px-5 py-4 flex items-center gap-4">
        {{-- Icon + name --}}
        <div class="w-10 h-10 rounded-xl {{ $source ? 'bg-indigo-100' : 'bg-gray-50' }} flex items-center justify-center shrink-0">
            <i class="fas fa-database {{ $source ? 'text-indigo-500' : 'text-gray-300' }}"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-bold text-sm text-gray-800">{{ $baza->name }}</p>
            <p class="text-xs text-gray-400">{{ $baza->questions_count }} ta savol mavjud</p>
        </div>

        {{-- If already added: show count + remove --}}
        @if($source)
        <div class="flex items-center gap-3 shrink-0">
            <span class="bg-indigo-100 text-indigo-700 text-xs font-black px-3 py-1.5 rounded-lg">
                {{ $source->count }} ta savol
            </span>
            <form method="POST" action="{{ route('admin.quizzes.source.delete', [$quiz, $source]) }}"
                  onsubmit="return confirm('O\'chirilsinmi?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="w-8 h-8 rounded-lg text-red-400 hover:text-red-600 hover:bg-red-50 flex items-center justify-center text-xs transition">
                    <i class="fas fa-times"></i>
                </button>
            </form>
        </div>
        @else
        {{-- Not added: inline form --}}
        <form method="POST" action="{{ route('admin.quizzes.source.store', $quiz) }}"
              class="flex items-center gap-2 shrink-0">
            @csrf
            <input type="hidden" name="baza_id" value="{{ $baza->id }}">
            <input type="number" name="count" value="{{ min(10, $baza->questions_count) }}"
                   min="1" max="{{ $baza->questions_count }}" required
                   class="w-20 text-center px-2 py-1.5 border border-gray-200 rounded-lg text-sm outline-none focus:border-indigo-400"
                   placeholder="soni">
            <button type="submit"
                    class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition {{ $baza->questions_count == 0 ? 'opacity-40 pointer-events-none' : '' }}">
                Qo'shish
            </button>
        </form>
        @endif
    </div>
    @endforeach
</div>
@endif
@endsection
