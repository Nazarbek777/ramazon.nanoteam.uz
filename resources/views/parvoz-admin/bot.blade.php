@extends('parvoz-admin.layout')

@section('title', 'Bot sozlamalari')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-white">🤖 Bot sozlamalari</h2>
    <p class="text-slate-400 text-sm mt-1">Telegram botni ulang — webhook avtomatik o'rnatiladi</p>
</div>

<div class="max-w-2xl space-y-6">
    @if($bot)
        <div class="glass-card rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center {{ $bot->is_active ? 'bg-emerald-500/20' : 'bg-red-500/20' }}">
                        <i class="fas fa-robot text-xl {{ $bot->is_active ? 'text-emerald-400' : 'text-red-400' }}"></i>
                    </div>
                    <div>
                        <p class="font-bold text-white">{{ $bot->name }}</p>
                        @if($bot->username)
                            <a href="https://t.me/{{ $bot->username }}" target="_blank" class="text-sky-400 text-sm hover:underline">
                                {{ '@' . $bot->username }}
                            </a>
                        @endif
                    </div>
                </div>

                <form method="POST" action="{{ route('parvoz-admin.bot.toggle') }}">
                    @csrf
                    <button class="px-4 py-2 rounded-xl font-bold text-sm {{ $bot->is_active ? 'bg-red-500/20 text-red-300' : 'bg-emerald-500/20 text-emerald-300' }}">
                        {{ $bot->is_active ? 'O\'chirish' : 'Yoqish' }}
                    </button>
                </form>
            </div>

            <div class="flex items-center gap-2 text-sm">
                <span class="text-slate-400">Webhook:</span>
                @if($bot->webhook_set)
                    <span class="text-emerald-400 font-semibold"><i class="fas fa-check-circle"></i> O'rnatilgan</span>
                @else
                    <span class="text-amber-400 font-semibold"><i class="fas fa-triangle-exclamation"></i> O'rnatilmagan</span>
                @endif
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('parvoz-admin.bot.save') }}" class="glass-card rounded-2xl p-6 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">Bot nomi</label>
            <input type="text" name="name" value="{{ old('name', $bot->name ?? 'Parvoz Edu') }}" required
                class="input-dark w-full px-4 py-3 rounded-xl">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">Bot tokeni</label>
            <input type="text" name="token" value="{{ old('token', $bot->token ?? '') }}" required
                placeholder="123456789:AAH..." class="input-dark w-full px-4 py-3 rounded-xl font-mono text-sm">
            <p class="text-xs text-slate-500 mt-2">@BotFather'dan olingan token. Saqlanganda webhook avtomatik o'rnatiladi.</p>
        </div>

        <button class="btn-primary w-full px-6 py-3 rounded-xl font-bold text-white">
            <i class="fas fa-save mr-2"></i>Saqlash va ulash
        </button>
    </form>
</div>
@endsection
