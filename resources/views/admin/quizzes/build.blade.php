@extends('layouts.admin')

@section('title', $quiz->title . ' — Bazalar')

@section('content')

{{-- Step indicator --}}
<div class="max-w-lg mx-auto mb-8">
    <div class="flex items-center gap-3">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center text-sm font-black shrink-0">
                <i class="fas fa-check text-xs"></i>
            </div>
            <span class="text-sm font-semibold text-gray-400 line-through">Test ma'lumotlari</span>
        </div>
        <div class="flex-1 h-0.5 bg-indigo-600 rounded-full"></div>
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-black shrink-0">2</div>
            <span class="text-sm font-bold text-gray-800">Bazalar tanlash</span>
        </div>
    </div>
</div>

{{-- Card wrapper --}}
<div class="max-w-lg mx-auto">

    {{-- Quiz info card --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-4 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
            <i class="fas fa-file-alt text-indigo-500"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-bold text-gray-800 truncate">{{ $quiz->title }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $quiz->subject->name }} · {{ $quiz->time_limit }} daqiqa</p>
        </div>
        <div class="text-right shrink-0">
            <p class="text-[10px] text-gray-400 font-bold uppercase mb-1">Maqsad</p>
            <p class="text-lg font-black text-gray-800 leading-none">{{ $quiz->random_questions_count ?? 0 }}</p>
        </div>
    </div>

    {{-- Sources total status --}}
    @php 
        $totalDefined = $quiz->sources->sum('count'); 
        $isInsufficient = $totalDefined < ($quiz->random_questions_count ?? 0);
    @endphp

    <div class="mb-6 p-5 rounded-2xl border {{ $isInsufficient ? 'bg-amber-50 border-amber-200' : 'bg-green-50 border-green-200' }} transition-all">
        <div class="flex items-center justify-between mb-3">
            <h4 class="text-sm font-bold {{ $isInsufficient ? 'text-amber-800' : 'text-green-800' }}">
                Tanlangan savollar yig'indisi:
            </h4>
            <span class="text-lg font-black {{ $isInsufficient ? 'text-amber-600' : 'text-green-600' }}">
                {{ $totalDefined }}
            </span>
        </div>
        
        <div class="w-full bg-gray-200/50 rounded-full h-2 mb-2">
            <div class="h-2 rounded-full {{ $isInsufficient ? 'bg-amber-400' : 'bg-green-500' }} transition-all" 
                 style="width: {{ min(100, ($totalDefined / ($quiz->random_questions_count ?: 1)) * 100) }}%"></div>
        </div>

        @if($isInsufficient)
            <p class="text-[11px] text-amber-600 font-medium flex items-center gap-1">
                <i class="fas fa-exclamation-triangle"></i> 
                Hali {{ ($quiz->random_questions_count ?? 0) - $totalDefined }} ta savol qo'shishingiz kerak
            </p>
        @else
            <p class="text-[11px] text-green-600 font-medium flex items-center gap-1">
                <i class="fas fa-check-circle"></i> 
                Savollar soni yetarli
            </p>
        @endif
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-2.5 mb-4 text-sm flex items-center gap-2">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-2.5 mb-4 text-sm">❌ {{ session('error') }}</div>
    @endif

    {{-- Section label --}}
    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 px-1">
        Bazalar ({{ $bazalar->count() }} ta)
    </p>

    @if($bazalar->isEmpty())
    <div class="bg-white rounded-2xl border border-dashed border-gray-200 py-12 text-center text-gray-400">
        <i class="fas fa-database text-4xl block mb-3 text-gray-200"></i>
        <p class="font-semibold text-sm">{{ $quiz->subject->name }} fanida hali baza yo'q</p>
        <a href="{{ route('admin.subjects.show', $quiz->subject) }}"
           class="text-indigo-500 font-semibold hover:underline text-sm mt-2 inline-block">
            → Baza qo'shish
        </a>
    </div>
    @else

    <div class="space-y-2 mb-6">
        @foreach($bazalar as $baza)
        @php $source = $quiz->sources->where('baza_id', $baza->id)->first(); @endphp

        <div class="bg-white rounded-2xl border {{ $source ? 'border-indigo-200' : 'border-gray-100' }} shadow-sm px-4 py-3.5 flex items-center gap-3
                    {{ $baza->questions_count == 0 && !$source ? 'opacity-50' : '' }}">

            {{-- Status dot --}}
            <div class="w-2 h-2 rounded-full shrink-0 {{ $source ? 'bg-indigo-500' : 'bg-gray-200' }}"></div>

            {{-- Name + count --}}
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-gray-800 truncate">{{ $baza->name }}</p>
                <p class="text-xs text-gray-400">{{ $baza->questions_count }} ta savol bor</p>
            </div>

            {{-- Action --}}
            @if($source)
            <div class="flex items-center gap-2 shrink-0">
                <span class="text-xs font-black text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-lg">
                    {{ $source->count }} ta
                </span>
                <form method="POST" action="{{ route('admin.quizzes.source.delete', [$quiz, $source]) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-8 h-8 rounded-lg text-red-400 hover:text-red-600 hover:bg-red-50 flex items-center justify-center text-xs transition" title="Olib tashlash">
                        <i class="fas fa-times"></i>
                    </button>
                </form>
            </div>
            @else
            <form method="POST" action="{{ route('admin.quizzes.source.store', $quiz) }}" class="flex items-center gap-2 shrink-0">
                @csrf
                <input type="hidden" name="baza_id" value="{{ $baza->id }}">
                <input type="number" name="count"
                       value="{{ min(20, max(1, $quiz->random_questions_count - $totalDefined)) }}"
                       min="1" required
                       class="w-16 text-center px-2 py-1.5 border border-gray-200 rounded-lg text-sm outline-none focus:border-indigo-400">
                <button type="submit"
                        class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition whitespace-nowrap">
                    + Qo'shish
                </button>
            </form>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Done button --}}
    <div class="space-y-3">
        @if($isInsufficient)
            <div class="p-3 rounded-xl bg-amber-50 border border-amber-100 text-[11px] text-amber-700 text-center">
                <i class="fas fa-info-circle mr-1"></i>
                Testni boshlash uchun jami {{ $quiz->random_questions_count }} ta savol belgilashingiz kerak.
            </div>
        @endif
        
        <a href="{{ route('admin.quizzes.subject', $quiz->subject) }}"
           class="w-full flex items-center justify-center gap-2 font-bold py-3.5 rounded-2xl transition shadow text-sm
                  {{ $isInsufficient ? 'bg-gray-100 text-gray-400 cursor-not-allowed pointer-events-none' : 'bg-indigo-600 hover:bg-indigo-700 text-white' }}">
            <i class="fas fa-check"></i>
            Tayyor — testni saqlash
        </a>
    </div>
    @endif
</div>
@endsection
