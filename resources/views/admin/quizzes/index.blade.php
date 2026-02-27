@extends('layouts.admin')

@section('title', 'Testlar')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h3 class="text-2xl font-bold text-gray-800">Testlar</h3>
        <p class="text-gray-500 text-sm mt-1">Fanlar bo'yicha testlar</p>
    </div>
    <a href="{{ route('admin.quizzes.create') }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-5 rounded-xl transition shadow-md flex items-center gap-2">
        <i class="fas fa-plus"></i> Yangi test
    </a>
</div>

<div class="space-y-4">
    @forelse($subjects as $subject)
    <div x-data="{ open: {{ request('subject') == $subject->id ? 'true' : 'false' }} }"
         class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- Subject card header --}}
        <button @click="open = !open"
                class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl bg-indigo-50">
                    {{ $subject->icon ?? '📚' }}
                </div>
                <div class="text-left">
                    <p class="font-bold text-gray-800">{{ $subject->name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $subject->quizzes_count }} ta test</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.quizzes.create', ['subject_id' => $subject->id]) }}"
                   @click.stop
                   class="text-xs font-bold bg-indigo-50 text-indigo-600 px-3 py-1.5 rounded-lg hover:bg-indigo-100 transition">
                    <i class="fas fa-plus mr-1"></i> Qo'shish
                </a>
                <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200" :class="open && 'rotate-180'"></i>
            </div>
        </button>

        {{-- Quiz list --}}
        <div x-show="open" x-transition class="border-t border-gray-50">
            @forelse($subject->quizzes as $quiz)
            @php
                $now = now();
                $isExpired = $quiz->ends_at && $quiz->ends_at < $now;
                $isNotStarted = $quiz->starts_at && $quiz->starts_at > $now;
            @endphp
            <div class="flex items-center justify-between px-6 py-3.5 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 {{ $isExpired ? 'opacity-60' : '' }}">
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <div>
                        <p class="font-semibold text-gray-800 text-sm truncate">{{ $quiz->title }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $quiz->time_limit }} daq &bull; O'tish: {{ $quiz->pass_score }}%
                            @if($quiz->access_code)
                                &bull; <span class="font-mono bg-gray-100 px-1 rounded">{{ $quiz->access_code }}</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3 shrink-0 ml-4">
                    @if($isExpired)
                        <span class="text-[10px] font-bold bg-red-100 text-red-500 px-2 py-0.5 rounded-full uppercase">Tugagan</span>
                    @elseif($isNotStarted)
                        <span class="text-[10px] font-bold bg-amber-100 text-amber-600 px-2 py-0.5 rounded-full uppercase">Boshlanmagan</span>
                    @else
                        <span class="text-[10px] font-bold bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded-full uppercase">Faol</span>
                    @endif
                    <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="text-indigo-500 hover:text-indigo-700 text-sm">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('admin.quizzes.destroy', $quiz) }}" method="POST" class="inline"
                          onsubmit="return confirm('O\'chirmoqchimisiz?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-600 text-sm">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="px-6 py-4 text-sm text-gray-400 italic">Bu fanda hali test yo'q.</div>
            @endforelse
        </div>
    </div>
    @empty
    <div class="bg-white rounded-2xl border border-dashed border-gray-200 py-16 text-center text-gray-400">
        <i class="fas fa-folder-open text-4xl mb-2 block"></i>
        Fanlar mavjud emas.
    </div>
    @endforelse
</div>
@endsection
