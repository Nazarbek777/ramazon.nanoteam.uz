@extends('contest-admin.layout')

@section('title', 'Botlar')

@section('content')
<div class="mb-8 flex items-center justify-between">
    <div>
        <h2 class="text-3xl font-bold text-white">🤖 Botlar</h2>
        <p class="text-slate-400 text-sm mt-1">Barcha konkurs botlarni boshqaring</p>
    </div>
    <a href="{{ route('contest-admin.bots.create') }}"
        class="btn-primary px-6 py-3 rounded-xl font-bold text-white flex items-center space-x-2">
        <i class="fas fa-plus"></i>
        <span>Yangi Bot</span>
    </a>
</div>

@if($bots->isEmpty())
    <div class="glass-card rounded-3xl p-16 text-center">
        <div class="w-20 h-20 mx-auto mb-6 bg-violet-500/10 rounded-2xl flex items-center justify-center">
            <i class="fas fa-robot text-4xl text-violet-400"></i>
        </div>
        <h3 class="text-xl font-bold text-white mb-2">Hali bot yo'q</h3>
        <p class="text-slate-400 mb-6">Birinchi konkurs botni yarating!</p>
        <a href="{{ route('contest-admin.bots.create') }}"
            class="btn-primary px-8 py-3 rounded-xl font-bold text-white inline-flex items-center space-x-2">
            <i class="fas fa-plus"></i>
            <span>Bot Yaratish</span>
        </a>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($bots as $bot)
            <div class="glass-card rounded-2xl p-6 hover:border-violet-500/30 transition-all duration-300 group">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center {{ $bot->is_active ? 'bg-emerald-500/20' : 'bg-red-500/20' }}">
                            <i class="fas fa-robot text-xl {{ $bot->is_active ? 'text-emerald-400' : 'text-red-400' }}"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-white">{{ $bot->name }}</h3>
                            <p class="text-sm text-slate-400">@{{ $bot->username ?? '—' }}</p>
                        </div>
                    </div>
                    <form action="{{ route('contest-admin.bots.toggle', $bot) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-10 h-6 rounded-full relative transition-all duration-300 {{ $bot->is_active ? 'bg-emerald-500' : 'bg-slate-600' }}">
                            <span class="absolute top-0.5 w-5 h-5 bg-white rounded-full transition-all duration-300 {{ $bot->is_active ? 'left-4' : 'left-0.5' }}"></span>
                        </button>
                    </form>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-5">
                    <div class="bg-white/5 rounded-xl px-4 py-3 text-center">
                        <p class="text-2xl font-bold text-violet-400">{{ $bot->contests_count }}</p>
                        <p class="text-xs text-slate-400">Konkurslar</p>
                    </div>
                    <div class="bg-white/5 rounded-xl px-4 py-3 text-center">
                        <p class="text-2xl font-bold text-emerald-400">
                            {{ $bot->is_active ? 'Faol' : 'O\'chiq' }}
                        </p>
                        <p class="text-xs text-slate-400">Holat</p>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between border-t border-white/5 pt-4">
                    <div class="flex items-center space-x-2">
                        <span class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Status:</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold {{ $bot->is_active ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-500/20 text-slate-400' }}">
                            {{ $bot->is_active ? 'Faol' : 'Nofaol' }}
                        </span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Webhook:</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold {{ $bot->webhook_set ? 'bg-blue-500/20 text-blue-400' : 'bg-amber-500/20 text-amber-400' }}">
                            {{ $bot->webhook_set ? 'Ulangan' : 'Uzilgan' }}
                        </span>
                    </div>
                </div>

                <div class="mt-6 flex items-center space-x-2">
                    <form action="{{ route('contest-admin.bots.toggle-webhook', $bot) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full text-center px-4 py-2.5 rounded-xl border {{ $bot->webhook_set ? 'border-amber-500/30 text-amber-400 hover:bg-amber-500/10' : 'border-blue-500/30 text-blue-400 hover:bg-blue-500/10' }} transition-all text-xs font-bold">
                            <i class="fas {{ $bot->webhook_set ? 'fa-plug-circle-xmark' : 'fa-plug-circle-check' }} mr-1"></i>
                            {{ $bot->webhook_set ? 'Webhook o\'chirish' : 'Webhook yoqish' }}
                        </button>
                    </form>
                    <a href="{{ route('contest-admin.bots.edit', $bot) }}"
                        class="w-10 h-10 flex items-center justify-center rounded-xl bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white transition">
                        <i class="fas fa-cog"></i>
                    </a>
                </div>

                <div class="flex items-center space-x-2 mt-4">
                    <a href="{{ route('contest-admin.bots.contests.index', $bot) }}"
                        class="flex-1 btn-primary text-center px-4 py-2.5 rounded-xl text-sm font-bold text-white">
                        <i class="fas fa-trophy mr-1"></i> Konkurslar
                    </a>
                    <a href="{{ route('contest-admin.bots.edit', $bot) }}"
                        class="w-10 h-10 flex items-center justify-center rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 transition">
                        <i class="fas fa-pen-to-square"></i>
                    </a>
                    <form action="{{ route('contest-admin.bots.reset-webhook', $bot) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="w-10 h-10 flex items-center justify-center rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 transition"
                            title="Webhookni qayta o'rnatish">
                            <i class="fas fa-rotate"></i>
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
