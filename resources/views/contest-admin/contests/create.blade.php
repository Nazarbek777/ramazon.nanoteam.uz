@extends('contest-admin.layout')

@section('title', 'Yangi Konkurs')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-8">
        <a href="{{ route('contest-admin.bots.contests.index', $bot) }}" class="text-slate-400 hover:text-violet-400 transition text-sm">
            <i class="fas fa-arrow-left mr-1"></i> {{ $bot->name }} konkurslariga qaytish
        </a>
        <h2 class="text-3xl font-bold text-white mt-3">🏆 Yangi Konkurs</h2>
        <p class="text-slate-400 text-sm mt-1">{{ $bot->name }} (@{{ $bot->username }}) uchun konkurs yaratish</p>
    </div>

    <form action="{{ route('contest-admin.bots.contests.store', $bot) }}" method="POST" class="space-y-6">
        @csrf

        <!-- Asosiy ma'lumotlar -->
        <div class="glass-card rounded-2xl p-8">
            <h3 class="text-lg font-bold text-violet-400 mb-6"><i class="fas fa-info-circle mr-2"></i>Asosiy ma'lumotlar</h3>

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Konkurs nomi *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                        class="input-dark w-full rounded-xl px-4 py-3"
                        placeholder="Masalan: Ramazon Konkurs 2026">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Tavsif</label>
                    <textarea name="description" rows="3" class="input-dark w-full rounded-xl px-4 py-3"
                        placeholder="Konkurs haqida qisqacha...">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2">Boshlanish sanasi</label>
                        <input type="datetime-local" name="start_date" value="{{ old('start_date') }}"
                            class="input-dark w-full rounded-xl px-4 py-3">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2">Tugash sanasi</label>
                        <input type="datetime-local" name="end_date" value="{{ old('end_date') }}"
                            class="input-dark w-full rounded-xl px-4 py-3">
                    </div>
                </div>
            </div>
        </div>

        <!-- Bot xabarlari -->
        <div class="glass-card rounded-2xl p-8">
            <h3 class="text-lg font-bold text-violet-400 mb-6"><i class="fas fa-comment-dots mr-2"></i>Bot xabarlari</h3>

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Start xabari</label>
                    <textarea name="start_text" rows="4" class="input-dark w-full rounded-xl px-4 py-3"
                        placeholder="Foydalanuvchi /start bosganda yuboriladigan xabar...">{{ old('start_text', "👋 Assalomu alaykum!\n\n🏆 Konkursimizga xush kelibsiz!\n\n📱 Ro'yxatdan o'tish uchun telefon raqamingizni yuboring:") }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Qoidalar matni</label>
                    <textarea name="rules_text" rows="4" class="input-dark w-full rounded-xl px-4 py-3"
                        placeholder="Konkurs qoidalari...">{{ old('rules_text') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Sozlamalar -->
        <div class="glass-card rounded-2xl p-8">
            <h3 class="text-lg font-bold text-violet-400 mb-6"><i class="fas fa-gear mr-2"></i>Sozlamalar</h3>

            <div class="space-y-4">
                <label class="flex items-center space-x-3 cursor-pointer group">
                    <input type="checkbox" name="require_phone" value="1" {{ old('require_phone', true) ? 'checked' : '' }}
                        class="w-5 h-5 rounded-lg border-2 border-violet-500 bg-transparent text-violet-500 focus:ring-violet-500">
                    <div>
                        <span class="text-sm font-bold text-slate-200 group-hover:text-white">📱 Telefon raqam so'rash</span>
                        <p class="text-xs text-slate-400">Foydalanuvchi kontaktini yuborishi majburiy</p>
                    </div>
                </label>

                <label class="flex items-center space-x-3 cursor-pointer group">
                    <input type="checkbox" name="require_channel_join" value="1" {{ old('require_channel_join', true) ? 'checked' : '' }}
                        class="w-5 h-5 rounded-lg border-2 border-violet-500 bg-transparent text-violet-500 focus:ring-violet-500">
                    <div>
                        <span class="text-sm font-bold text-slate-200 group-hover:text-white">📢 Kanal obunasini tekshirish</span>
                        <p class="text-xs text-slate-400">Foydalanuvchi majburiy kanallarga a'zo bo'lishi kerak</p>
                    </div>
                </label>

                <label class="flex items-center space-x-3 cursor-pointer group">
                    <input type="checkbox" name="require_referral" value="1" {{ old('require_referral') ? 'checked' : '' }}
                        class="w-5 h-5 rounded-lg border-2 border-violet-500 bg-transparent text-violet-500 focus:ring-violet-500">
                    <div>
                        <span class="text-sm font-bold text-slate-200 group-hover:text-white">👥 Referral tizimi</span>
                        <p class="text-xs text-slate-400">Do'stlarni taklif qilish uchun ball berish</p>
                    </div>
                </label>

                <div class="pl-8">
                    <label class="block text-sm font-bold text-slate-300 mb-2">Har bir referral uchun ball</label>
                    <input type="number" name="referral_points" value="{{ old('referral_points', 1) }}" min="0"
                        class="input-dark w-32 rounded-xl px-4 py-3">
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-4">
            <button type="submit" class="btn-primary px-8 py-3 rounded-xl font-bold text-white">
                <i class="fas fa-plus mr-2"></i> Konkurs Yaratish
            </button>
            <a href="{{ route('contest-admin.bots.contests.index', $bot) }}" class="text-slate-400 hover:text-white transition font-semibold">
                Bekor qilish
            </a>
        </div>
    </form>
</div>
@endsection
