@extends('layouts.admin')

@section('title', 'Testlar')

@section('content')
<div class="mb-6">
    <h3 class="text-2xl font-bold text-gray-800">Testlar</h3>
    <p class="text-gray-500 text-sm mt-1">Fanni tanlang — uning testlarini boshqaring</p>
</div>

<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
    @forelse($subjects as $subject)
    <a href="{{ route('admin.quizzes.subject', $subject) }}"
       class="group bg-white rounded-2xl border-2 border-gray-100 p-5 text-center
              hover:border-indigo-400 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 shadow-sm">

        <div class="w-12 h-12 rounded-xl mx-auto mb-3 flex items-center justify-center text-2xl bg-gray-100 group-hover:bg-indigo-50 transition">
            @if($subject->icon && str_starts_with($subject->icon, 'fa'))
                <i class="{{ $subject->icon }} text-gray-500 group-hover:text-indigo-500"></i>
            @else
                {{ $subject->icon ?? '📚' }}
            @endif
        </div>

        <p class="font-bold text-sm leading-tight text-gray-800 group-hover:text-indigo-700">{{ $subject->name }}</p>
        <p class="text-xs mt-1.5 font-semibold text-gray-400">
            {{ $subject->quizzes_count }} ta test
        </p>
        <div class="mt-3 text-xs font-bold text-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity">
            Ko'rish <i class="fas fa-arrow-right ml-1 text-[10px]"></i>
        </div>
    </a>
    @empty
    <div class="col-span-4 text-center text-gray-400 py-12">
        <i class="fas fa-folder-open text-3xl mb-2 block text-gray-200"></i>
        Fanlar mavjud emas.
    </div>
    @endforelse
</div>
@endsection
