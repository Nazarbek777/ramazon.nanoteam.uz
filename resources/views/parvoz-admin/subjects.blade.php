@extends('parvoz-admin.layout')

@section('title', 'Fanlar')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-white">📚 Fanlar</h2>
    <p class="text-slate-400 text-sm mt-1">O'qituvchi ball kiritganda shu fanlardan tanlaydi</p>
</div>

<form method="POST" action="{{ route('parvoz-admin.subjects.store') }}" class="glass-card rounded-2xl p-6 mb-6 flex flex-wrap gap-4 items-end">
    @csrf
    <div class="flex-1 min-w-[220px]">
        <label class="block text-sm font-semibold text-slate-300 mb-2">Fan nomi</label>
        <input type="text" name="name" required placeholder="Masalan: Matematika" class="input-dark w-full px-4 py-3 rounded-xl">
    </div>
    <button class="btn-primary px-6 py-3 rounded-xl font-bold text-white"><i class="fas fa-plus mr-2"></i>Qo'shish</button>
</form>

@if($subjects->isEmpty())
    <div class="glass-card rounded-2xl p-16 text-center">
        <i class="fas fa-book text-4xl text-sky-400/50 mb-4"></i>
        <p class="text-slate-400">Hali fan yo'q. Kamida bittasini qo'shing.</p>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($subjects as $subject)
            <div class="glass-card rounded-2xl p-5 flex items-center justify-between">
                <div>
                    <p class="font-bold text-white">{{ $subject->name }}</p>
                    <p class="text-slate-400 text-xs mt-1">{{ $subject->grades_count }} ta baho</p>
                </div>
                <form method="POST" action="{{ route('parvoz-admin.subjects.destroy', $subject) }}"
                    onsubmit="return confirm('Fan o\'chirilsinmi?')">
                    @csrf @method('DELETE')
                    <button class="px-3 py-2 bg-red-500/20 text-red-300 rounded-xl"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        @endforeach
    </div>
@endif
@endsection
