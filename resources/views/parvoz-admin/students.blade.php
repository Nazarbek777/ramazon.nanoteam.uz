@extends('parvoz-admin.layout')

@section('title', 'O\'quvchilar')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-white">🎓 O'quvchilar</h2>
    <p class="text-slate-400 text-sm mt-1">O'quvchi botga shu telefon raqami bilan kiradi</p>
</div>

<div x-data="{ tab: 'one' }" class="glass-card rounded-2xl p-6 mb-6">
    <div class="flex gap-2 mb-4">
        <button @click="tab = 'one'" :class="tab === 'one' ? 'bg-sky-500/20 text-sky-300' : 'bg-white/5'"
            class="px-4 py-2 rounded-xl text-sm font-semibold">Bittalab</button>
        <button @click="tab = 'bulk'" :class="tab === 'bulk' ? 'bg-sky-500/20 text-sky-300' : 'bg-white/5'"
            class="px-4 py-2 rounded-xl text-sm font-semibold">Ko'p qo'shish</button>
    </div>

    <form x-show="tab === 'one'" method="POST" action="{{ route('parvoz-admin.students.store') }}"
        class="flex flex-wrap gap-4 items-end">
        @csrf
        <div class="flex-1 min-w-[180px]">
            <label class="block text-sm font-semibold text-slate-300 mb-2">F.I.O</label>
            <input type="text" name="full_name" required placeholder="Karimov Aziz" class="input-dark w-full px-4 py-3 rounded-xl">
        </div>
        <div class="flex-1 min-w-[180px]">
            <label class="block text-sm font-semibold text-slate-300 mb-2">Telefon</label>
            <input type="text" name="phone" required placeholder="+998901234567" class="input-dark w-full px-4 py-3 rounded-xl">
        </div>
        <div class="flex-1 min-w-[180px]">
            <label class="block text-sm font-semibold text-slate-300 mb-2">Guruh</label>
            <select name="parvoz_group_id" class="input-dark w-full px-4 py-3 rounded-xl">
                <option value="">— tanlanmagan —</option>
                @foreach($groups as $g)
                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn-primary px-6 py-3 rounded-xl font-bold text-white"><i class="fas fa-plus mr-2"></i>Qo'shish</button>
    </form>

    <form x-show="tab === 'bulk'" x-cloak method="POST" action="{{ route('parvoz-admin.students.bulk') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">Guruh</label>
            <select name="parvoz_group_id" required class="input-dark w-full px-4 py-3 rounded-xl">
                @foreach($groups as $g)
                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">Ro'yxat — har qatorda: <code class="text-sky-400">Ism Familiya, telefon</code></label>
            <textarea name="rows" rows="6" required class="input-dark w-full px-4 py-3 rounded-xl font-mono text-sm"
                placeholder="Karimov Aziz, +998901234567&#10;Aliyeva Nilufar, +998912345678"></textarea>
        </div>
        <button class="btn-primary px-6 py-3 rounded-xl font-bold text-white"><i class="fas fa-upload mr-2"></i>Hammasini qo'shish</button>
    </form>
</div>

<form method="GET" class="flex flex-wrap gap-3 mb-6">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Ism yoki telefon bo'yicha qidirish"
        class="input-dark flex-1 min-w-[200px] px-4 py-3 rounded-xl">
    <select name="group" class="input-dark px-4 py-3 rounded-xl">
        <option value="">Barcha guruhlar</option>
        @foreach($groups as $g)
            <option value="{{ $g->id }}" @selected(request('group') == $g->id)>{{ $g->name }}</option>
        @endforeach
    </select>
    <button class="btn-primary px-6 py-3 rounded-xl font-bold text-white"><i class="fas fa-search"></i></button>
</form>

<div class="glass-card rounded-2xl p-6">
    @if($students->isEmpty())
        <p class="text-slate-400 text-center py-12">O'quvchi topilmadi.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-slate-400 border-b border-white/10">
                    <tr>
                        <th class="text-left py-3 px-2">F.I.O</th>
                        <th class="text-left py-3 px-2">Telefon</th>
                        <th class="text-left py-3 px-2">Guruh</th>
                        <th class="text-left py-3 px-2">Baholar</th>
                        <th class="text-left py-3 px-2">Bot</th>
                        <th class="text-right py-3 px-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $s)
                        <tr class="border-b border-white/5" x-data="{ edit: false }">
                            <td colspan="6" class="p-0">
                                <div x-show="!edit" class="flex items-center py-3 px-2 gap-4">
                                    <span class="flex-1 text-white font-semibold">{{ $s->full_name }}</span>
                                    <span class="flex-1 text-slate-400 font-mono">{{ $s->phone }}</span>
                                    <span class="flex-1 text-slate-300">{{ $s->group?->name ?? '—' }}</span>
                                    <span class="flex-1 text-sky-400 font-bold">{{ $s->grades_count }}</span>
                                    <span class="flex-1">
                                        @if($s->telegram_id)
                                            <span class="text-emerald-400"><i class="fab fa-telegram"></i></span>
                                        @else
                                            <span class="text-slate-600">—</span>
                                        @endif
                                    </span>
                                    <span class="flex gap-2">
                                        <button @click="edit = true" class="px-3 py-1 bg-white/5 rounded-lg"><i class="fas fa-pen"></i></button>
                                        <form method="POST" action="{{ route('parvoz-admin.students.destroy', $s) }}"
                                            onsubmit="return confirm('O\'chirilsinmi?')">
                                            @csrf @method('DELETE')
                                            <button class="px-3 py-1 bg-red-500/20 text-red-300 rounded-lg"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </span>
                                </div>

                                <form x-show="edit" x-cloak method="POST" action="{{ route('parvoz-admin.students.update', $s) }}"
                                    class="flex flex-wrap items-center gap-3 py-3 px-2">
                                    @csrf @method('PUT')
                                    <input type="text" name="full_name" value="{{ $s->full_name }}" required class="input-dark flex-1 min-w-[150px] px-3 py-2 rounded-lg">
                                    <input type="text" name="phone" value="{{ $s->phone }}" required class="input-dark flex-1 min-w-[150px] px-3 py-2 rounded-lg">
                                    <select name="parvoz_group_id" class="input-dark px-3 py-2 rounded-lg">
                                        <option value="">— guruhsiz —</option>
                                        @foreach($groups as $g)
                                            <option value="{{ $g->id }}" @selected($s->parvoz_group_id == $g->id)>{{ $g->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="flex items-center gap-2 text-xs">
                                        <input type="checkbox" name="is_active" value="1" {{ $s->is_active ? 'checked' : '' }} class="rounded">
                                        <span>Faol</span>
                                    </label>
                                    <button class="btn-primary px-4 py-2 rounded-lg font-bold text-white text-sm">Saqlash</button>
                                    <button type="button" @click="edit = false" class="px-4 py-2 bg-white/5 rounded-lg text-sm">Bekor</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $students->links() }}</div>
    @endif
</div>
@endsection
