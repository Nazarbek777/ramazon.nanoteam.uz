<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $student->full_name }} - Parvoz</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * { -webkit-tap-highlight-color: transparent; }
        html, body { overflow-x: hidden; }
        body { font-family: 'Outfit', sans-serif; background: #0f172a; min-height: 100vh; }
        .card { background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1); }
        #toast { transition: opacity .25s ease; }
    </style>
</head>

<body class="pb-24">

    <div class="bg-slate-900 border-b border-white/10 px-4 py-3 sticky top-0 z-20">
        <div class="max-w-3xl mx-auto flex items-center gap-3">
            <a href="{{ $focusGroup ? route('parvoz.group.show', $focusGroup) : route('parvoz.panel') }}"
                class="text-slate-300 bg-white/10 px-3 py-2 rounded-xl text-sm shrink-0">←</a>
            <div class="min-w-0 flex-1">
                <p class="text-white font-bold truncate">🎓 {{ $student->full_name }}</p>
                <p class="text-slate-400 text-xs truncate">
                    📞 {{ $student->phone ?: '—' }} · {{ $student->telegram_id ? '✅ botda' : '⏳ botsiz' }}
                </p>
            </div>
        </div>
    </div>

    <div id="toast" class="fixed top-20 left-1/2 -translate-x-1/2 z-50 px-4 py-3 rounded-2xl text-sm font-bold opacity-0 pointer-events-none max-w-[90vw] text-center"></div>

    <div class="max-w-3xl mx-auto px-4 py-4 space-y-4">

        @if($grades->isEmpty())
            <div class="card rounded-2xl p-8 text-center">
                <p class="text-slate-400 text-sm">Bu o'quvchiga hali ball qo'yilmagan.</p>
            </div>
        @endif

        @foreach($stats->sortByDesc(fn ($s, $gid) => $gid === $focusGroup) as $gid => $st)
            <div class="card rounded-2xl overflow-hidden {{ $gid === $focusGroup ? 'ring-1 ring-sky-400/50' : '' }}">

                <div class="px-4 py-3 bg-white/5">
                    <p class="font-bold text-sky-300">👥 {{ $st['group']?->name ?? 'Guruhsiz' }}</p>
                </div>

                <!-- Statistika -->
                <div class="grid grid-cols-3 gap-2 p-4">
                    <div class="bg-white/5 rounded-xl p-3 text-center">
                        <p class="text-2xl font-bold text-white">{{ $st['avg'] }}</p>
                        <p class="text-slate-400 text-xs">📈 O'rtacha</p>
                    </div>
                    <div class="bg-white/5 rounded-xl p-3 text-center">
                        <p class="text-2xl font-bold text-white">{{ $st['count'] }}</p>
                        <p class="text-slate-400 text-xs">⭐ Ballar soni</p>
                    </div>
                    <div class="bg-white/5 rounded-xl p-3 text-center">
                        <p class="text-2xl font-bold text-white">{{ $st['days'] }}</p>
                        <p class="text-slate-400 text-xs">📅 Qatnashgan kun</p>
                    </div>
                </div>

                <!-- Hisob-kitob -->
                <div class="px-4 pb-4">
                    <div class="bg-sky-500/10 border border-sky-400/20 rounded-xl px-4 py-3 text-sm">
                        <p class="text-slate-400 text-xs mb-1">O'rtacha qanday hisoblandi:</p>
                        <p class="text-white font-mono break-words">
                            {{ rtrim(rtrim(number_format($st['sum'], 2, '.', ''), '0'), '.') }}
                            ÷ {{ $st['count'] }}
                            = <b class="text-sky-300">{{ $st['avg'] }}</b>
                        </p>
                        <p class="text-slate-500 text-xs mt-1">
                            (barcha ballar yig'indisi ÷ ballar soni)
                            @if($st['percent'] !== null) · foizda: <b class="text-sky-300">{{ $st['percent'] }}%</b> @endif
                        </p>
                    </div>
                </div>

                <!-- Tarix -->
                <div class="border-t border-white/10">
                    <p class="px-4 pt-3 pb-1 text-slate-400 text-xs font-semibold">🕐 To'liq tarix</p>
                    @foreach($st['rows'] as $g)
                        <div class="js-grade px-4 py-2.5 border-t border-white/5 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-white text-sm">
                                    {{ $g->graded_at?->format('d.m.Y H:i') }}
                                    <span class="text-slate-500 text-xs">· {{ $g->subject?->name ?? 'Umumiy' }}</span>
                                </p>
                                <p class="text-slate-500 text-xs truncate">
                                    🧑‍🏫 {{ $g->teacher?->full_name ?? '—' }}
                                    @if($g->comment) · 💬 {{ $g->comment }} @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-sky-300 font-bold text-sm">⭐ {{ $g->scoreLabel() }}</span>
                                <button type="button" onclick="deleteGrade({{ $g->id }}, this)"
                                    class="text-rose-300/70 bg-rose-500/10 px-2 py-1 rounded-lg text-xs">🗑</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        const BASE = @json(url('/parvoz'));

        function toast(msg, ok = true) {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.style.opacity = 1;
            t.style.background = ok ? '#059669' : '#e11d48';
            clearTimeout(t._h);
            t._h = setTimeout(() => t.style.opacity = 0, 2800);
        }

        async function deleteGrade(id, btn) {
            if (!confirm("Bu ball o'chirilsinmi?")) return;
            const r = await fetch(BASE + '/grade/' + id + '/delete', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            });
            if (!r.ok) { toast("O'chirib bo'lmadi — sahifani yangilang.", false); return; }
            sessionStorage.setItem('parvozToast', "🗑 Ball o'chirildi.");
            location.reload();
        }

        (function () {
            const msg = sessionStorage.getItem('parvozToast');
            if (msg) { sessionStorage.removeItem('parvozToast'); toast(msg); }
        })();
    </script>
</body>

</html>
