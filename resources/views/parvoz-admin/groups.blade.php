@extends('parvoz-admin.layout')

@section('title', 'Guruhlar')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-white">👥 Guruhlar</h2>
    <p class="text-slate-400 text-sm mt-1">Guruhlar va ularga biriktirilgan o'qituvchilar</p>
</div>

<form method="POST" action="{{ route('parvoz-admin.groups.store') }}" class="glass-card rounded-2xl p-6 mb-6 flex flex-wrap gap-4 items-end">
    @csrf
    <div class="flex-1 min-w-[200px]">
        <label class="block text-sm font-semibold text-slate-300 mb-2">Guruh nomi</label>
        <input type="text" name="name" required placeholder="Masalan: Ingliz tili A" class="input-dark w-full px-4 py-3 rounded-xl">
    </div>
    <div class="flex-1 min-w-[200px]">
        <label class="block text-sm font-semibold text-slate-300 mb-2">Izoh (ixtiyoriy)</label>
        <input type="text" name="description" placeholder="Dush/Chor/Jum 18:00" class="input-dark w-full px-4 py-3 rounded-xl">
    </div>
    <button class="btn-primary px-6 py-3 rounded-xl font-bold text-white"><i class="fas fa-plus mr-2"></i>Qo'shish</button>
</form>

@if($groups->isEmpty())
    <div class="glass-card rounded-2xl p-16 text-center">
        <i class="fas fa-users text-4xl text-sky-400/50 mb-4"></i>
        <p class="text-slate-400">Hali guruh yo'q. Yuqoridan qo'shing.</p>
    </div>
@else
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @foreach($groups as $group)
            <div class="glass-card rounded-2xl p-6" x-data="{ edit: false }">
                <div x-show="!edit">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-bold text-white text-lg">{{ $group->name }}</h3>
                            @if($group->description)
                                <p class="text-slate-400 text-sm">{{ $group->description }}</p>
                            @endif
                        </div>
                        <span class="px-3 py-1 rounded-lg text-xs font-bold {{ $group->is_active ? 'bg-emerald-500/20 text-emerald-300' : 'bg-red-500/20 text-red-300' }}">
                            {{ $group->is_active ? 'Faol' : 'Nofaol' }}
                        </span>
                    </div>

                    <div class="flex flex-wrap gap-2 text-sm mb-4">
                        <span class="px-3 py-1 bg-sky-500/10 text-sky-300 rounded-lg">
                            <i class="fas fa-user-graduate mr-1"></i>{{ $group->students_count }} o'quvchi
                        </span>
                        @forelse($group->teachers as $t)
                            <span class="px-3 py-1 bg-emerald-500/10 text-emerald-300 rounded-lg">
                                <i class="fas fa-chalkboard-user mr-1"></i>{{ $t->full_name }}
                            </span>
                        @empty
                            <span class="px-3 py-1 bg-amber-500/10 text-amber-300 rounded-lg">O'qituvchi biriktirilmagan</span>
                        @endforelse
                    </div>

                    <div class="flex gap-2">
                        <button @click="edit = true" class="px-4 py-2 bg-white/5 hover:bg-white/10 rounded-xl text-sm font-semibold">
                            <i class="fas fa-pen mr-1"></i>Tahrirlash
                        </button>
                        <form method="POST" action="{{ route('parvoz-admin.groups.destroy', $group) }}"
                            onsubmit="return confirm('Guruh o\'chirilsinmi?')">
                            @csrf @method('DELETE')
                            <button class="px-4 py-2 bg-red-500/20 text-red-300 rounded-xl text-sm font-semibold">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <form x-show="edit" x-cloak method="POST" action="{{ route('parvoz-admin.groups.update', $group) }}" class="space-y-4">
                    @csrf @method('PUT')
                    <input type="text" name="name" value="{{ $group->name }}" required class="input-dark w-full px-4 py-3 rounded-xl">
                    <input type="text" name="description" value="{{ $group->description }}" placeholder="Izoh" class="input-dark w-full px-4 py-3 rounded-xl">

                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-2">O'qituvchilar</label>
                        <div class="space-y-2 max-h-40 overflow-y-auto">
                            @forelse($teachers as $t)
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" name="teachers[]" value="{{ $t->id }}"
                                        {{ $group->teachers->contains($t->id) ? 'checked' : '' }} class="rounded">
                                    <span>{{ $t->full_name }}</span>
                                </label>
                            @empty
                                <p class="text-slate-500 text-sm">Avval o'qituvchi qo'shing.</p>
                            @endforelse
                        </div>
                    </div>

                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_active" value="1" {{ $group->is_active ? 'checked' : '' }} class="rounded">
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
