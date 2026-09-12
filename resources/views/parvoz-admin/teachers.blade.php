@extends('parvoz-admin.layout')

@section('title', 'O\'qituvchilar')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-white">👨‍🏫 O'qituvchilar</h2>
    <p class="text-slate-400 text-sm mt-1">O'qituvchi botga shu telefon raqami bilan kiradi</p>
</div>

<form method="POST" action="{{ route('parvoz-admin.teachers.store') }}" class="glass-card rounded-2xl p-6 mb-6">
    @csrf
    <div class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-semibold text-slate-300 mb-2">F.I.O</label>
            <input type="text" name="full_name" required placeholder="Aliyev Alisher" class="input-dark w-full px-4 py-3 rounded-xl">
        </div>
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-semibold text-slate-300 mb-2">Telefon</label>
            <input type="text" name="phone" required placeholder="+998901234567" class="input-dark w-full px-4 py-3 rounded-xl">
        </div>
        <button class="btn-primary px-6 py-3 rounded-xl font-bold text-white"><i class="fas fa-plus mr-2"></i>Qo'shish</button>
    </div>

    @if($groups->isNotEmpty())
        <div class="mt-4">
            <label class="block text-sm font-semibold text-slate-300 mb-2">Guruhlari</label>
            <div class="flex flex-wrap gap-3">
                @foreach($groups as $g)
                    <label class="flex items-center gap-2 text-sm bg-white/5 px-3 py-2 rounded-xl">
                        <input type="checkbox" name="groups[]" value="{{ $g->id }}" class="rounded">
                        <span>{{ $g->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endif
</form>

@if($teachers->isEmpty())
    <div class="glass-card rounded-2xl p-16 text-center">
        <i class="fas fa-chalkboard-user text-4xl text-sky-400/50 mb-4"></i>
        <p class="text-slate-400">Hali o'qituvchi yo'q.</p>
    </div>
@else
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @foreach($teachers as $teacher)
            <div class="glass-card rounded-2xl p-6" x-data="{ edit: false }">
                <div x-show="!edit">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-bold text-white text-lg">{{ $teacher->full_name }}</h3>
                            <p class="text-slate-400 text-sm font-mono">{{ $teacher->phone }}</p>
                        </div>
                        @if($teacher->telegram_id)
                            <span class="px-3 py-1 rounded-lg text-xs font-bold bg-emerald-500/20 text-emerald-300">
                                <i class="fab fa-telegram"></i> Ulangan
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-lg text-xs font-bold bg-slate-500/20 text-slate-400">Ulanmagan</span>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-2 text-sm mb-4">
                        @forelse($teacher->groups as $g)
                            <span class="px-3 py-1 bg-sky-500/10 text-sky-300 rounded-lg">{{ $g->name }}</span>
                        @empty
                            <span class="px-3 py-1 bg-amber-500/10 text-amber-300 rounded-lg">Guruh biriktirilmagan</span>
                        @endforelse
                    </div>

                    <div class="flex gap-2">
                        <button @click="edit = true" class="px-4 py-2 bg-white/5 hover:bg-white/10 rounded-xl text-sm font-semibold">
                            <i class="fas fa-pen mr-1"></i>Tahrirlash
                        </button>
                        <form method="POST" action="{{ route('parvoz-admin.teachers.destroy', $teacher) }}"
                            onsubmit="return confirm('O\'chirilsinmi?')">
                            @csrf @method('DELETE')
                            <button class="px-4 py-2 bg-red-500/20 text-red-300 rounded-xl text-sm font-semibold"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>

                <form x-show="edit" x-cloak method="POST" action="{{ route('parvoz-admin.teachers.update', $teacher) }}" class="space-y-4">
                    @csrf @method('PUT')
                    <input type="text" name="full_name" value="{{ $teacher->full_name }}" required class="input-dark w-full px-4 py-3 rounded-xl">
                    <input type="text" name="phone" value="{{ $teacher->phone }}" required class="input-dark w-full px-4 py-3 rounded-xl">

                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-2">Guruhlari</label>
                        <div class="space-y-2 max-h-40 overflow-y-auto">
                            @foreach($groups as $g)
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" name="groups[]" value="{{ $g->id }}"
                                        {{ $teacher->groups->contains($g->id) ? 'checked' : '' }} class="rounded">
                                    <span>{{ $g->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_active" value="1" {{ $teacher->is_active ? 'checked' : '' }} class="rounded">
                        <span>Faol</span>
                    </label>

                    <div class="flex gap-2">
                        <button class="btn-primary px-6 py-2 rounded-xl font-bold text-white text-sm">Saqlash</button>
                        <button type="button" @click="edit = false" class="px-6 py-2 bg-white/5 rounded-xl text-sm font-semibold">Bekor</button>
                    </div>
                </form>
            </div>
        @endforeach
    </div>
@endif
@endsection
