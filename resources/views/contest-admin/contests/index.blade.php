@extends('contest-admin.layout')

@section('title', $bot->name . ' - Konkurslar')

@section('content')
<div class="mb-8 flex items-center justify-between">
    <div>
        <a href="{{ route('contest-admin.bots.index') }}" class="text-slate-400 hover:text-violet-400 transition text-sm">
            <i class="fas fa-arrow-left mr-1"></i> Botlarga qaytish
        </a>
        <h2 class="text-3xl font-bold text-white mt-2">🏆 {{ $bot->name }}</h2>
        <p class="text-slate-400 text-sm mt-1">@{{ $bot->username }} — Konkurslarni boshqarish</p>
    </div>
    <a href="{{ route('contest-admin.bots.contests.create', $bot) }}"
        class="btn-primary px-6 py-3 rounded-xl font-bold text-white flex items-center space-x-2">
        <i class="fas fa-plus"></i>
        <span>Yangi Konkurs</span>
    </a>
</div>

@if($contests->isEmpty())
    <div class="glass-card rounded-3xl p-16 text-center">
        <div class="w-20 h-20 mx-auto mb-6 bg-violet-500/10 rounded-2xl flex items-center justify-center">
            <i class="fas fa-trophy text-4xl text-violet-400"></i>
        </div>
        <h3 class="text-xl font-bold text-white mb-2">Hali konkurs yo'q</h3>
        <p class="text-slate-400 mb-6">Bu bot uchun birinchi konkursni yarating!</p>
        <a href="{{ route('contest-admin.bots.contests.create', $bot) }}"
            class="btn-primary px-8 py-3 rounded-xl font-bold text-white inline-flex items-center space-x-2">
            <i class="fas fa-plus"></i>
            <span>Konkurs Yaratish</span>
        </a>
    </div>
@else
    <div class="space-y-4">
        @foreach($contests as $contest)
            <div class="glass-card rounded-2xl p-6 hover:border-violet-500/30 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center {{ $contest->is_active ? 'bg-emerald-500/20' : 'bg-slate-500/20' }}">
                            <i class="fas fa-trophy text-xl {{ $contest->is_active ? 'text-emerald-400' : 'text-slate-400' }}"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-white text-lg">{{ $contest->title }}</h3>
                            <p class="text-sm text-slate-400">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold {{ $contest->is_active ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-500/20 text-slate-400' }}">
                                    {{ $contest->is_active ? '🟢 Faol' : '⚫ Nofaol' }}
                                </span>
                                @if($contest->start_date)
                                    <span class="ml-2">📅 {{ $contest->start_date->format('d.m.Y') }}</span>
                                @endif
                                @if($contest->end_date)
                                    <span>→ {{ $contest->end_date->format('d.m.Y') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-6">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-violet-400">{{ $contest->participants_count }}</p>
                            <p class="text-xs text-slate-400">Ishtirokchilar</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-blue-400">{{ $contest->channels_count }}</p>
                            <p class="text-xs text-slate-400">Kanallar</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-amber-400">{{ $contest->keywords_count }}</p>
                            <p class="text-xs text-slate-400">Kalit so'zlar</p>
                        </div>

                        <div class="flex items-center space-x-2">
                            <a href="{{ route('contest-admin.bots.contests.participants', [$bot, $contest]) }}"
                                class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-sm font-bold text-slate-300 transition">
                                <i class="fas fa-users mr-1"></i> Ishtirokchilar
                            </a>
                            <a href="{{ route('contest-admin.bots.contests.edit', [$bot, $contest]) }}"
                                class="btn-primary px-4 py-2 rounded-xl text-sm font-bold text-white">
                                <i class="fas fa-pen mr-1"></i> Tahrirlash
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
