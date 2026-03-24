@extends('contest-admin.layout')

@section('title', 'Botni tahrirlash')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-8">
        <a href="{{ route('contest-admin.bots.index') }}" class="text-slate-400 hover:text-violet-400 transition text-sm">
            <i class="fas fa-arrow-left mr-1"></i> Botlarga qaytish
        </a>
        <h2 class="text-3xl font-bold text-white mt-3">✏️ {{ $bot->name }}</h2>
        <p class="text-slate-400 text-sm mt-1">@{{ $bot->username }}</p>
    </div>

    <div class="glass-card rounded-2xl p-8">
        <form action="{{ route('contest-admin.bots.update', $bot) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-bold text-slate-300 mb-2">Bot nomi</label>
                <input type="text" name="name" value="{{ old('name', $bot->name) }}" required
                    class="input-dark w-full rounded-xl px-4 py-3">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-300 mb-2">Bot Token</label>
                <input type="text" name="token" value="{{ old('token', $bot->token) }}" required
                    class="input-dark w-full rounded-xl px-4 py-3 font-mono text-sm">
                <p class="text-xs text-slate-500 mt-2">
                    <i class="fas fa-warning mr-1 text-amber-400"></i>
                    Token o'zgartirilsa webhook avtomatik qayta o'rnatiladi.
                </p>
            </div>

            <div class="flex items-center space-x-4 pt-4">
                <button type="submit" class="btn-primary px-8 py-3 rounded-xl font-bold text-white">
                    <i class="fas fa-save mr-2"></i> Saqlash
                </button>
                <a href="{{ route('contest-admin.bots.index') }}" class="text-slate-400 hover:text-white transition font-semibold">
                    Bekor qilish
                </a>
            </div>
        </form>
    </div>

    <!-- Danger Zone -->
    <div class="glass-card rounded-2xl p-6 mt-6 border border-red-500/20">
        <h3 class="text-sm font-bold text-red-400 mb-3">⚠️ Xavfli hudud</h3>
        <p class="text-sm text-slate-400 mb-4">Botni o'chirganingizda barcha konkurslar va ishtirokchilar ham o'chib ketadi.</p>
        <form action="{{ route('contest-admin.bots.destroy', $bot) }}" method="POST"
            onsubmit="return confirm('Rostdan ham bu botni o\'chirmoqchimisiz? Bu amalni qaytarib bo\'lmaydi!')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-danger px-6 py-2.5 rounded-xl text-sm font-bold text-white">
                <i class="fas fa-trash mr-2"></i> Botni O'chirish
            </button>
        </form>
    </div>
</div>
@endsection
