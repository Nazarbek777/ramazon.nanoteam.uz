{{-- Bitta o'quvchi qatori: ism, telefon, ball katagi, saqlash tugmasi --}}
<div class="js-row border-t border-white/5 px-4 py-3"
    data-id="{{ $st->id }}"
    data-phone="{{ $st->phone }}"
    data-search="{{ mb_strtolower($st->full_name) }} {{ $st->phone }}">

    <div class="flex items-center justify-between gap-2 mb-2">
        <div class="min-w-0">
            <p class="js-name text-white font-semibold text-sm truncate">{{ $st->full_name }}</p>
            <p class="text-slate-500 text-xs truncate">
                📞 {{ $st->phone ?: '—' }}
                {{ $st->telegram_id ? '· ✅ botda' : '· ⏳ botsiz' }}
            </p>
        </div>
        <button type="button" class="js-open text-slate-300 bg-white/10 px-3 py-1.5 rounded-lg text-xs font-semibold shrink-0">
            ⚙️ Boshqarish
        </button>
    </div>

    <div class="flex items-center gap-2">
        <input type="text" inputmode="decimal" placeholder="Ball"
            class="js-score inp flex-1 px-3 py-2.5 rounded-xl text-sm font-bold min-w-0">
        <button type="button" class="js-save btn px-5 py-2.5 rounded-xl font-bold text-sm shrink-0">Saqlash</button>
    </div>
</div>
