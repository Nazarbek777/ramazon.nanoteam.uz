@extends('parvoz-admin.layout')

@section('title', 'Bosh sahifa')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-white">📊 Parvoz o'quv markazi</h2>
    <p class="text-slate-400 text-sm mt-1">Umumiy ko'rsatkichlar</p>
</div>

@php
    $cards = [
        ['Guruhlar',      $stats['groups'],   'fa-users',           'sky'],
        ["O'quvchilar",   $stats['students'], 'fa-user-graduate',   'cyan'],
        ["O'qituvchilar", $stats['teachers'], 'fa-chalkboard-user', 'emerald'],
        ['Fanlar',        $stats['subjects'], 'fa-book',            'amber'],
        ['Baholar',       $stats['grades'],   'fa-star',            'violet'],
        ['Botga ulangan', $stats['linked'],   'fa-link',            'rose'],
    ];
@endphp

<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
    @foreach($cards as [$label, $value, $icon, $color])
        <div class="glass-card rounded-2xl p-5">
            <div class="w-10 h-10 rounded-xl bg-{{ $color }}-500/20 flex items-center justify-center mb-3">
                <i class="fas {{ $icon }} text-{{ $color }}-400"></i>
            </div>
            <p class="text-2xl font-bold text-white">{{ $value }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $label }}</p>
        </div>
    @endforeach
</div>

@if(!$bot)
    <div class="glass-card rounded-2xl p-8 mb-8 border-amber-500/30">
        <div class="flex items-center gap-4">
            <i class="fas fa-robot text-3xl text-amber-400"></i>
            <div class="flex-1">
                <h3 class="font-bold text-white text-lg">Bot hali ulanmagan</h3>
                <p class="text-slate-400 text-sm">Telegram botni ulash uchun sozlamalarga o'ting.</p>
            </div>
            <a href="{{ route('parvoz-admin.bot') }}" class="btn-primary px-6 py-3 rounded-xl font-bold text-white">
                Botni ulash
            </a>
        </div>
    </div>
@endif

<div class="glass-card rounded-2xl p-6">
    <h3 class="font-bold text-white text-lg mb-4">🕘 Oxirgi baholar</h3>

    @if($recentGrades->isEmpty())
        <p class="text-slate-400 text-sm py-8 text-center">Hali baho qo'yilmagan.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-slate-400 border-b border-white/10">
                    <tr>
                        <th class="text-left py-3 px-2">O'quvchi</th>
                        <th class="text-left py-3 px-2">Fan</th>
                        <th class="text-left py-3 px-2">Ball</th>
                        <th class="text-left py-3 px-2">O'qituvchi</th>
                        <th class="text-left py-3 px-2">Sana</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentGrades as $g)
                        <tr class="border-b border-white/5">
                            <td class="py-3 px-2 text-white font-semibold">{{ $g->student?->full_name }}</td>
                            <td class="py-3 px-2 text-slate-300">{{ $g->subject?->name ?? '—' }}</td>
                            <td class="py-3 px-2"><span class="text-sky-400 font-bold">{{ $g->scoreLabel() }}</span></td>
                            <td class="py-3 px-2 text-slate-400">{{ $g->teacher?->full_name ?? '—' }}</td>
                            <td class="py-3 px-2 text-slate-400">{{ $g->graded_at?->format('d.m.Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
