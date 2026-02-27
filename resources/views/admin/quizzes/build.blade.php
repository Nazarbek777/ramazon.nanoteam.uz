@extends('layouts.admin')

@section('title', $quiz->title . ' — Baza sozlash')

@section('content')
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
        <p class="text-sm text-gray-400">Qaysi bazadan nechta savol olinishini belgilang</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.quizzes.edit', $quiz) }}"
           class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50">
            <i class="fas fa-edit mr-1"></i> Tahrirlash
        </a>
        <a href="{{ route('admin.quizzes.subject', $quiz->subject) }}"
           class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
            <i class="fas fa-check mr-1"></i> Tayyor
        </a>
    </div>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 mb-4 text-sm">✅ {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-4 text-sm">❌ {{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- LEFT: Add baza --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h4 class="font-bold text-gray-700 mb-4 flex items-center gap-2">
            <i class="fas fa-plus-circle text-indigo-500"></i> Baza qo'shish
        </h4>

        @if($bazalar->isEmpty())
        <div class="text-center py-6 text-gray-400 text-sm">
            <i class="fas fa-exclamation-circle text-2xl block mb-2 text-amber-300"></i>
            <strong>{{ $quiz->subject->name }}</strong> fanida hali baza yaratilmagan.<br>
            <a href="{{ route('admin.bazalar.index', $quiz->subject) }}"
               class="text-indigo-500 font-semibold hover:underline mt-2 inline-block">
                → Bazalar sahifasiga o'ting va baza qo'shing
            </a>
        </div>
        @else
        <form method="POST" action="{{ route('admin.quizzes.source.store', $quiz) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Baza tanlang</label>
                <select name="baza_id" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400">
                    <option value="">— Tanlang —</option>
                    @foreach($bazalar as $baza)
                    @php $already = $quiz->sources->where('baza_id', $baza->id)->count() > 0; @endphp
                    <option value="{{ $baza->id }}" {{ $already ? 'disabled' : '' }}>
                        {{ str_repeat('　', $baza->depth) }}{{ $baza->depth > 0 ? '└─ ' : '' }}{{ $baza->name }}
                        ({{ $baza->questions_count }} savol){{ $already ? ' ✓' : '' }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Savollar soni</label>
                <input type="number" name="count" min="1" max="999" value="10" required
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400">
            </div>
            <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2.5 rounded-xl font-bold text-sm hover:bg-indigo-700">
                <i class="fas fa-plus mr-1"></i> Qo'shish
            </button>
        </form>

        <div class="mt-5 pt-4 border-t border-gray-100">
            <p class="text-xs font-bold text-gray-400 uppercase mb-2">Mavjud bazalar</p>
            @foreach($bazalar as $baza)
            <div class="flex items-center gap-1.5 py-0.5" style="padding-left: {{ $baza->depth * 14 }}px">
                <i class="fas fa-{{ $baza->depth == 0 ? 'database' : 'folder' }} text-{{ $baza->depth == 0 ? 'indigo' : 'amber' }}-300 text-xs"></i>
                <span class="text-xs text-gray-600">{{ $baza->name }}</span>
                <span class="text-[10px] text-gray-400">({{ $baza->questions_count }})</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- RIGHT: Current sources --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h4 class="font-bold text-gray-700 mb-4 flex items-center gap-2">
            <i class="fas fa-list text-indigo-500"></i> Qo'shilgan bazalar
        </h4>

        @forelse($quiz->sources as $source)
        <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
            <div>
                <p class="font-semibold text-sm text-gray-800">{{ $source->baza->name ?? '—' }}</p>
                <p class="text-xs text-gray-400">{{ $source->count }} ta random savol</p>
            </div>
            <form method="POST" action="{{ route('admin.quizzes.source.delete', [$quiz, $source]) }}"
                  onsubmit="return confirm('O\'chirilsinmi?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-red-400 hover:text-red-600 px-2 py-1 text-sm">
                    <i class="fas fa-times"></i>
                </button>
            </form>
        </div>
        @empty
        <div class="text-center py-10 text-gray-400">
            <i class="fas fa-inbox text-3xl block mb-2 text-gray-200"></i>
            <p class="text-sm">Hali baza qo'shilmagan.</p>
        </div>
        @endforelse

        @if($quiz->sources->isNotEmpty())
        <div class="mt-4 bg-indigo-50 rounded-xl px-4 py-3 grid grid-cols-2 gap-2">
            <div>
                <p class="text-xs text-indigo-400 font-bold uppercase">Bazalar</p>
                <p class="text-xl font-black text-indigo-700">{{ $quiz->sources->count() }}</p>
            </div>
            <div>
                <p class="text-xs text-indigo-400 font-bold uppercase">Jami savollar</p>
                <p class="text-xl font-black text-indigo-700">{{ $quiz->sources->sum('count') }}</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
