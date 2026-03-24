@extends('contest-admin.layout')

@section('title', 'Yangi Bot')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-8">
        <a href="{{ route('contest-admin.bots.index') }}" class="text-slate-400 hover:text-violet-400 transition text-sm">
            <i class="fas fa-arrow-left mr-1"></i> Botlarga qaytish
        </a>
        <h2 class="text-3xl font-bold text-white mt-3">🤖 Yangi Bot Qo'shish</h2>
        <p class="text-slate-400 text-sm mt-1">BotFather'dan olingan tokenni kiriting</p>
    </div>

    <div class="glass-card rounded-2xl p-8">
        <form action="{{ route('contest-admin.bots.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-bold text-slate-300 mb-2">Bot nomi</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="input-dark w-full rounded-xl px-4 py-3"
                    placeholder="Masalan: Mega Konkurs Bot">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-300 mb-2">Bot Token</label>
                <input type="text" name="token" value="{{ old('token') }}" required
                    class="input-dark w-full rounded-xl px-4 py-3 font-mono text-sm"
                    placeholder="123456789:AABBCCDD...">
                <p class="text-xs text-slate-500 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Token BotFather'dan olinadi. Token kiritilgach webhook avtomatik o'rnatiladi.
                </p>
            </div>

            <div class="flex items-center space-x-4 pt-4">
                <button type="submit" class="btn-primary px-8 py-3 rounded-xl font-bold text-white">
                    <i class="fas fa-plus mr-2"></i> Bot Yaratish
                </button>
                <a href="{{ route('contest-admin.bots.index') }}" class="text-slate-400 hover:text-white transition font-semibold">
                    Bekor qilish
                </a>
            </div>
        </form>
    </div>

    <div class="glass-card rounded-2xl p-6 mt-6">
        <h3 class="text-sm font-bold text-violet-400 mb-3">📋 Qanday bot yaratish kerak?</h3>
        <ol class="text-sm text-slate-400 space-y-2 list-decimal list-inside">
            <li>Telegram'da <a href="https://t.me/BotFather" target="_blank" class="text-violet-400 hover:underline">@BotFather</a>'ga boring</li>
            <li><code class="bg-white/10 px-2 py-0.5 rounded">/newbot</code> buyrug'ini yuboring</li>
            <li>Bot uchun nom va username kiriting</li>
            <li>BotFather bergan token'ni nusxalang</li>
            <li>Yuqoridagi formaga token'ni kiriting va "Bot Yaratish" tugmasini bosing</li>
        </ol>
    </div>
</div>
@endsection
