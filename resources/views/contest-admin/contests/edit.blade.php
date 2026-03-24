@extends('contest-admin.layout')

@section('title', $contest->title . ' - Tahrirlash')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <a href="{{ route('contest-admin.bots.contests.index', $bot) }}" class="text-slate-400 hover:text-violet-400 transition text-sm">
            <i class="fas fa-arrow-left mr-1"></i> {{ $bot->name }} konkurslariga qaytish
        </a>
        <h2 class="text-3xl font-bold text-white mt-3">✏️ {{ $contest->title }}</h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Asosiy forma (left 2/3) -->
        <div class="lg:col-span-2 space-y-6">
            <form action="{{ route('contest-admin.bots.contests.update', [$bot, $contest]) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="glass-card rounded-2xl p-8">
                    <h3 class="text-lg font-bold text-violet-400 mb-6"><i class="fas fa-info-circle mr-2"></i>Asosiy ma'lumotlar</h3>
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Konkurs nomi *</label>
                            <input type="text" name="title" value="{{ old('title', $contest->title) }}" required
                                class="input-dark w-full rounded-xl px-4 py-3">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Tavsif</label>
                            <textarea name="description" rows="3" class="input-dark w-full rounded-xl px-4 py-3">{{ old('description', $contest->description) }}</textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Boshlanish</label>
                                <input type="datetime-local" name="start_date"
                                    value="{{ old('start_date', $contest->start_date?->format('Y-m-d\TH:i')) }}"
                                    class="input-dark w-full rounded-xl px-4 py-3">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Tugash</label>
                                <input type="datetime-local" name="end_date"
                                    value="{{ old('end_date', $contest->end_date?->format('Y-m-d\TH:i')) }}"
                                    class="input-dark w-full rounded-xl px-4 py-3">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass-card rounded-2xl p-8">
                    <h3 class="text-lg font-bold text-violet-400 mb-6"><i class="fas fa-comment-dots mr-2"></i>Bot xabarlari</h3>
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Start xabari</label>
                            <textarea name="start_text" rows="4" class="input-dark w-full rounded-xl px-4 py-3">{{ old('start_text', $contest->start_text) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-300 mb-2">Qoidalar matni</label>
                            <textarea name="rules_text" rows="4" class="input-dark w-full rounded-xl px-4 py-3">{{ old('rules_text', $contest->rules_text) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="glass-card rounded-2xl p-8">
                    <h3 class="text-lg font-bold text-violet-400 mb-6"><i class="fas fa-gear mr-2"></i>Sozlamalar</h3>
                    <div class="space-y-4">
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ $contest->is_active ? 'checked' : '' }}
                                class="w-5 h-5 rounded-lg border-2 border-emerald-500 bg-transparent text-emerald-500 focus:ring-emerald-500">
                            <span class="text-sm font-bold text-emerald-400">🟢 Konkurs faol</span>
                        </label>
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="checkbox" name="require_phone" value="1" {{ $contest->require_phone ? 'checked' : '' }}
                                class="w-5 h-5 rounded-lg border-2 border-violet-500 bg-transparent text-violet-500 focus:ring-violet-500">
                            <span class="text-sm font-bold text-slate-200">📱 Telefon raqam so'rash</span>
                        </label>
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="checkbox" name="require_channel_join" value="1" {{ $contest->require_channel_join ? 'checked' : '' }}
                                class="w-5 h-5 rounded-lg border-2 border-violet-500 bg-transparent text-violet-500 focus:ring-violet-500">
                            <span class="text-sm font-bold text-slate-200">📢 Kanal obunasini tekshirish</span>
                        </label>
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="checkbox" name="require_referral" value="1" {{ $contest->require_referral ? 'checked' : '' }}
                                class="w-5 h-5 rounded-lg border-2 border-violet-500 bg-transparent text-violet-500 focus:ring-violet-500">
                            <span class="text-sm font-bold text-slate-200">👥 Referral tizimi</span>
                        </label>
                        <div class="pl-8 space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Har bir referral uchun ball</label>
                                <input type="number" name="referral_points" value="{{ old('referral_points', $contest->referral_points) }}" min="0"
                                    class="input-dark w-32 rounded-xl px-4 py-3">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2">Referral taklif xabari</label>
                                <textarea name="referral_text" rows="3" class="input-dark w-full rounded-xl px-4 py-3"
                                    placeholder="Do'stlarni taklif qilish uchun xabar matni...">{{ old('referral_text', $contest->referral_text) }}</textarea>
                                <p class="text-[10px] text-slate-500 mt-1">
                                    Mavjud taglar: <span class="text-violet-400">{link}</span> (referral havola), 
                                    <span class="text-violet-400">{points}</span> (ball), 
                                    <span class="text-violet-400">{name}</span> (foydalanuvchi ismi)
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <button type="submit" class="btn-primary px-8 py-3 rounded-xl font-bold text-white">
                        <i class="fas fa-save mr-2"></i> Saqlash
                    </button>
                    <form action="{{ route('contest-admin.bots.contests.destroy', [$bot, $contest]) }}" method="POST" class="inline"
                        onsubmit="return confirm('Rostdan ham o\'chirmoqchimisiz?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-danger px-6 py-3 rounded-xl font-bold text-white">
                            <i class="fas fa-trash mr-2"></i> O'chirish
                        </button>
                    </form>
                </div>
            </form>
        </div>

        <!-- Right sidebar: Channels & Keywords -->
        <div class="space-y-6">
            <!-- Kanallar -->
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-sm font-bold text-violet-400 mb-4"><i class="fas fa-bullhorn mr-1"></i> Majburiy Kanallar</h3>

                @foreach($contest->channels as $channel)
                    <div class="flex items-center justify-between bg-white/5 rounded-xl px-3 py-2 mb-2">
                        <div>
                            <p class="text-sm font-bold text-white">{{ $channel->channel_name }}</p>
                            <p class="text-xs text-slate-400">{{ $channel->channel_id }}</p>
                        </div>
                        <form action="{{ route('contest-admin.bots.contests.channels.destroy', [$bot, $contest, $channel]) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300 text-sm">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </div>
                @endforeach

                <form action="{{ route('contest-admin.bots.contests.channels.store', [$bot, $contest]) }}" method="POST"
                    class="mt-4 space-y-3">
                    @csrf
                    <input type="text" name="channel_name" placeholder="Kanal nomi" required
                        class="input-dark w-full rounded-lg px-3 py-2 text-sm">
                    <input type="text" name="channel_id" placeholder="@channel_username" required
                        class="input-dark w-full rounded-lg px-3 py-2 text-sm">
                    <input type="text" name="channel_url" placeholder="https://t.me/channel (ixtiyoriy)"
                        class="input-dark w-full rounded-lg px-3 py-2 text-sm">
                    <button type="submit" class="w-full btn-primary px-4 py-2 rounded-lg text-sm font-bold text-white">
                        <i class="fas fa-plus mr-1"></i> Kanal qo'shish
                    </button>
                </form>
            </div>

            <!-- Kalit so'zlar -->
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-sm font-bold text-amber-400 mb-4"><i class="fas fa-key mr-1"></i> Kalit So'zlar</h3>

                @foreach($contest->keywords as $keyword)
                    <div class="flex items-center justify-between bg-white/5 rounded-xl px-3 py-2 mb-2">
                        <div>
                            <p class="text-sm font-bold text-white">"{{ $keyword->keyword }}"</p>
                            <p class="text-xs text-slate-400 truncate" style="max-width:140px">{{ Str::limit($keyword->response_text, 40) }}</p>
                        </div>
                        <form action="{{ route('contest-admin.bots.contests.keywords.destroy', [$bot, $contest, $keyword]) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300 text-sm">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </div>
                @endforeach

                <form action="{{ route('contest-admin.bots.contests.keywords.store', [$bot, $contest]) }}" method="POST"
                    class="mt-4 space-y-3">
                    @csrf
                    <input type="text" name="keyword" placeholder="Tugma matni / Kalit so'z" required
                        class="input-dark w-full rounded-lg px-3 py-2 text-sm">
                    <textarea name="response_text" placeholder="Javob matni (oddiy tugma bo'lsa)" rows="3"
                        class="input-dark w-full rounded-lg px-3 py-2 text-sm"></textarea>
                    
                    <div class="grid grid-cols-2 gap-2">
                        <select name="action" class="input-dark w-full rounded-lg px-3 py-2 text-sm">
                            <option value="">-- Amal tanlang (ixtiyoriy) --</option>
                            <option value="profile">👤 Profil / Natijalar</option>
                            <option value="leaderboard">🏆 Reyting (TOP 20)</option>
                            <option value="referral">🔗 Referral havola</option>
                            <option value="rules">📋 Qoidalar</option>
                        </select>
                        <input type="number" name="sort_order" placeholder="Tartib (Sort)" value="0"
                            class="input-dark w-full rounded-lg px-3 py-2 text-sm">
                    </div>

                    <input type="text" name="response_photo" placeholder="Rasm URL yoki File ID (ixtiyoriy)"
                        class="input-dark w-full rounded-lg px-3 py-2 text-sm">

                    <label class="flex items-center space-x-2 cursor-pointer bg-white/5 p-2 rounded-lg border border-white/10 hover:border-violet-500/50 transition">
                        <input type="checkbox" name="is_menu_button" value="1" 
                            class="w-4 h-4 rounded border-violet-500 bg-transparent text-violet-500 focus:ring-violet-500">
                        <span class="text-xs font-bold text-slate-300">Asosiy menyuda tugma sifatida ko'rinsin</span>
                    </label>

                    <button type="submit" class="w-full btn-primary px-4 py-2 rounded-lg text-sm font-bold text-white">
                        <i class="fas fa-plus mr-1"></i> Qo'shish
                    </button>
                </form>
            </div>

            <!-- Prizes (Sovg'alar) -->
            <div class="glass-card rounded-2xl p-6 border border-white/10 mt-6">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest flex items-center">
                    <i class="fas fa-gift mr-2 text-pink-500"></i> Sovg'alar (Prizes)
                </h3>
                
                <div class="mt-4 space-y-3">
                    @foreach($contest->prizes as $prize)
                        <div class="flex items-center justify-between p-3 bg-white/5 rounded-xl border border-white/5 group hover:border-pink-500/30 transition">
                            <div class="flex items-center space-x-3">
                                @if($prize->image)
                                    <img src="{{ $prize->image }}" class="w-10 h-10 rounded-lg object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-lg bg-pink-500/10 flex items-center justify-center text-pink-400">
                                        <i class="fas fa-gift"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="text-xs font-bold text-white">{{ $prize->title }}</div>
                                    <div class="text-[10px] text-slate-400">⭐ {{ $prize->points_required }} ball</div>
                                </div>
                            </div>
                            <form action="{{ route('contest-admin.bots.contests.prizes.destroy', [$bot, $contest, $prize]) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-slate-500 hover:text-red-400 transition" onclick="return confirm('O\'chirilsinmi?')">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>

                <form action="{{ route('contest-admin.bots.contests.prizes.store', [$bot, $contest]) }}" method="POST"
                    class="mt-6 space-y-3 pt-6 border-t border-white/5">
                    @csrf
                    <input type="text" name="title" placeholder="Sovg'a nomi" required
                        class="input-dark w-full rounded-lg px-3 py-2 text-sm">
                    <input type="number" name="points_required" placeholder="Kerakli ball" required
                        class="input-dark w-full rounded-lg px-3 py-2 text-sm">
                    <input type="text" name="image" placeholder="Rasm URL (ixtiyoriy)"
                        class="input-dark w-full rounded-lg px-3 py-2 text-sm">
                    <input type="number" name="sort_order" placeholder="Tartib" value="0"
                        class="input-dark w-full rounded-lg px-3 py-2 text-sm">
                    
                    <button type="submit" class="w-full bg-pink-600 hover:bg-pink-500 py-2 rounded-lg text-sm font-bold text-white transition">
                        <i class="fas fa-plus mr-1"></i> Sovg'a qo'shish
                    </button>
                </form>
            </div>

            <!-- Statistika -->
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-sm font-bold text-emerald-400 mb-4"><i class="fas fa-chart-bar mr-1"></i> Statistika</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-slate-400 text-sm">Jami ishtirokchilar</span>
                        <span class="font-bold text-white">{{ $contest->participants()->where('is_registered', true)->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400 text-sm">Bugungi yangi</span>
                        <span class="font-bold text-emerald-400">{{ $contest->participants()->where('is_registered', true)->whereDate('created_at', today())->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400 text-sm">Kanallar</span>
                        <span class="font-bold text-blue-400">{{ $contest->channels->count() }}</span>
                    </div>
                </div>
                <a href="{{ route('contest-admin.bots.contests.participants', [$bot, $contest]) }}"
                    class="block mt-4 text-center btn-primary px-4 py-2 rounded-xl text-sm font-bold text-white">
                    <i class="fas fa-users mr-1"></i> Ishtirokchilarni ko'rish
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
