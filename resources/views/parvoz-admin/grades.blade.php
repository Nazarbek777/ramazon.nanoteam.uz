@extends('parvoz-admin.layout')

@section('title', 'Baholar')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-white">⭐ Baholar</h2>
    <p class="text-slate-400 text-sm mt-1">O'qituvchilar bot orqali kiritgan barcha ballar</p>
</div>

<form method="GET" class="flex flex-wrap gap-3 mb-6">
    <select name="group" class="input-dark px-4 py-3 rounded-xl">
        <option value="">Barcha guruhlar</option>
        @foreach($groups as $g)
            <option value="{{ $g->id }}" @selected(request('group') == $g->id)>{{ $g->name }}</option>
        @endforeach
    </select>
    <select name="subject" class="input-dark px-4 py-3 rounded-xl">
        <option value="">Barcha fanlar</option>
        @foreach($subjects as $s)
            <option value="{{ $s->id }}" @selected(request('subject') == $s->id)>{{ $s->name }}</option>
        @endforeach
    </select>
    <button class="btn-primary px-6 py-3 rounded-xl font-bold text-white"><i class="fas fa-filter mr-2"></i>Filtr</button>
    @if(request()->hasAny(['group', 'subject']))
        <a href="{{ route('parvoz-admin.grades') }}" class="px-6 py-3 bg-white/5 rounded-xl font-semibold">Tozalash</a>
    @endif
</form>

<div class="glass-card rounded-2xl p-6">
    @if($grades->isEmpty())
        <p class="text-slate-400 text-center py-12">Baho topilmadi.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-slate-400 border-b border-white/10">
                    <tr>
                        <th class="text-left py-3 px-2">O'quvchi</th>
                        <th class="text-left py-3 px-2">Guruh</th>
                        <th class="text-left py-3 px-2">Fan</th>
                        <th class="text-left py-3 px-2">Ball</th>
                        <th class="text-left py-3 px-2">Izoh</th>
                        <th class="text-left py-3 px-2">O'qituvchi</th>
                        <th class="text-left py-3 px-2">Sana</th>
                        <th class="text-right py-3 px-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($grades as $g)
                        <tr class="border-b border-white/5">
                            <td class="py-3 px-2 text-white font-semibold">{{ $g->student?->full_name }}</td>
                            <td class="py-3 px-2 text-slate-300">{{ $g->student?->group?->name ?? '—' }}</td>
                            <td class="py-3 px-2 text-slate-300">{{ $g->subject?->name ?? '—' }}</td>
                            <td class="py-3 px-2"><span class="text-sky-400 font-bold">{{ $g->scoreLabel() }}</span></td>
                            <td class="py-3 px-2 text-slate-400">{{ $g->comment ?? '—' }}</td>
                            <td class="py-3 px-2 text-slate-400">{{ $g->teacher?->full_name ?? '—' }}</td>
                            <td class="py-3 px-2 text-slate-400">{{ $g->graded_at?->format('d.m.Y H:i') }}</td>
                            <td class="py-3 px-2 text-right">
                                <form method="POST" action="{{ route('parvoz-admin.grades.destroy', $g) }}"
                                    onsubmit="return confirm('Baho o\'chirilsinmi?')">
                                    @csrf @method('DELETE')
                                    <button class="px-3 py-1 bg-red-500/20 text-red-300 rounded-lg"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $grades->links() }}</div>
    @endif
</div>
@endsection
