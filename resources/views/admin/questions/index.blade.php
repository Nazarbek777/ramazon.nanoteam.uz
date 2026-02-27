@extends('layouts.admin')

@section('title', 'Savollar')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h3 class="text-2xl font-bold text-gray-800">Savollar</h3>
        <p class="text-gray-500 text-sm mt-1">Fanlar bo'yicha savollar</p>
    </div>
    <a href="{{ route('admin.questions.create', request('subject_id') ? ['subject_id' => request('subject_id')] : []) }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-5 rounded-xl transition shadow flex items-center gap-2">
        <i class="fas fa-plus"></i> Yangi savol
    </a>
</div>

{{-- Subject Cards Grid --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
    @forelse($subjects as $subject)
    @php $isSelected = $selectedSubject?->id == $subject->id; @endphp
    <a href="{{ route('admin.questions.index', ['subject_id' => $isSelected ? '' : $subject->id]) }}"
       class="group relative bg-white rounded-2xl border-2 transition-all duration-200 p-5 text-center
              hover:border-indigo-400 hover:shadow-lg hover:-translate-y-0.5
              {{ $isSelected ? 'border-indigo-500 bg-indigo-50 shadow-md' : 'border-gray-100 shadow-sm' }}">

        <div class="w-12 h-12 rounded-xl mx-auto mb-3 flex items-center justify-center text-2xl
                    {{ $isSelected ? 'bg-indigo-100' : 'bg-gray-100 group-hover:bg-indigo-50' }}">
            @if($subject->icon && str_starts_with($subject->icon, 'fa'))
                <i class="{{ $subject->icon }} {{ $isSelected ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-500' }}"></i>
            @else
                {{ $subject->icon ?? '📚' }}
            @endif
        </div>

        <p class="font-bold text-sm leading-tight {{ $isSelected ? 'text-indigo-700' : 'text-gray-800' }}">
            {{ $subject->name }}
        </p>
        <p class="text-xs mt-1 font-semibold {{ $isSelected ? 'text-indigo-400' : 'text-gray-400' }}">
            {{ $subject->questions_count }} ta savol
        </p>

        @if($isSelected)
        <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-indigo-500"></span>
        @endif
    </a>
    @empty
    <div class="col-span-4 text-center text-gray-400 py-8">Fanlar mavjud emas</div>
    @endforelse
</div>

{{-- Selected subject questions --}}
@if($selectedSubject)
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-indigo-50/50">
        <div class="flex items-center gap-2">
            @if($selectedSubject->icon && str_starts_with($selectedSubject->icon, 'fa'))
                <i class="{{ $selectedSubject->icon }} text-indigo-500"></i>
            @else
                <span>{{ $selectedSubject->icon ?? '📚' }}</span>
            @endif
            <h4 class="font-bold text-gray-800">{{ $selectedSubject->name }} — savollar</h4>
        </div>
        <a href="{{ route('admin.questions.create', ['subject_id' => $selectedSubject->id]) }}"
           class="text-xs font-bold bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition">
            <i class="fas fa-plus mr-1"></i> Savol qo'shish
        </a>
    </div>

    @forelse($questions as $question)
    <div class="flex items-start justify-between px-5 py-3.5 border-b border-gray-50 last:border-0 hover:bg-gray-50/50">
        <div class="flex-1 min-w-0 mr-4">
            <p class="text-sm font-medium text-gray-800 line-clamp-2 leading-snug">{{ $question->content }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $question->options_count }} ta variant</p>
        </div>
        <div class="flex items-center gap-3 shrink-0 mt-0.5">
            <a href="{{ route('admin.questions.edit', $question) }}" class="text-indigo-400 hover:text-indigo-700 text-sm">
                <i class="fas fa-edit"></i>
            </a>
            <form action="{{ route('admin.questions.destroy', $question) }}" method="POST" class="inline"
                  onsubmit="return confirm('O\'chirilsinmi?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-red-400 hover:text-red-600 text-sm">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="px-5 py-8 text-center text-gray-400 text-sm italic">Bu fanda hali savol yo'q.</div>
    @endforelse
</div>
@else
<div class="bg-white rounded-2xl border border-dashed border-gray-200 py-10 text-center text-gray-400 text-sm">
    <i class="fas fa-hand-pointer text-2xl mb-2 block text-gray-300"></i>
    Fan kartochkasini bosing — uning savollarini ko'ring
</div>
@endif
@endsection
