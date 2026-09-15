<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $group->name }} - Parvoz</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * { -webkit-tap-highlight-color: transparent; }
        html, body { overflow-x: hidden; }
        body { font-family: 'Outfit', sans-serif; background: #0f172a; min-height: 100vh; }

        .card { background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1); }
        .inp { background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.15); color: #fff; }
        .inp:focus { outline: none; border-color: #0ea5e9; }
        .inp::placeholder { color: rgba(148,163,184,.6); }
        .btn { background: #0ea5e9; color: #fff; }
        .btn:active { background: #0284c7; }
        #toast { transition: opacity .25s ease; }
        [x-hide] { display: none !important; }
    </style>
</head>

<body class="pb-24">

    <!-- ══ SARLAVHA ══ -->
    <div class="bg-slate-900 border-b border-white/10 px-4 py-3 sticky top-0 z-20">
        <div class="max-w-3xl mx-auto flex items-center gap-3">
            <a href="{{ route('parvoz.panel') }}" class="text-slate-300 bg-white/10 px-3 py-2 rounded-xl text-sm shrink-0">←</a>
            <div class="min-w-0 flex-1">
                <p class="text-white font-bold truncate">👥 {{ $group->name }}</p>
                <p class="text-slate-400 text-xs truncate">
                    🧑‍🏫 {{ $group->teachers->isNotEmpty() ? $group->teachers->pluck('full_name')->join(', ') : 'o\'qituvchi tanlanmagan' }}
                    · {{ $students->count() }} o'quvchi
                </p>
            </div>
        </div>
    </div>

    <div id="toast" class="fixed top-20 left-1/2 -translate-x-1/2 z-50 px-4 py-3 rounded-2xl text-sm font-bold opacity-0 pointer-events-none max-w-[90vw] text-center"></div>

    <div class="max-w-3xl mx-auto px-4 py-4 space-y-4">

        <!-- ══ BALL QO'YISH ══ -->
        <div class="card rounded-2xl overflow-hidden">
            <div class="px-4 py-3 bg-white/5 flex items-center justify-between gap-2">
                <p class="font-bold text-sky-300 text-sm">⭐ Ball qo'yish</p>
                <button type="button" onclick="document.getElementById('addbox').toggleAttribute('x-hide')"
                    class="text-sky-300 bg-sky-500/15 border border-sky-400/30 px-3 py-1.5 rounded-lg text-xs font-bold">➕ O'quvchi</button>
            </div>

            <!-- Umumiy sozlama: fan -->
            @if($subjects->isNotEmpty())
                <div class="px-4 pt-3">
                    <label class="block text-slate-500 text-xs mb-1">Fan (barcha ballar uchun, ixtiyoriy)</label>
                    <select id="subject" class="inp w-full px-4 py-2.5 rounded-xl text-sm">
                        <option value="">📚 Tanlanmagan</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="px-4 py-3">
                <input type="text" id="search" placeholder="🔍 Qidirish" autocomplete="off"
                    class="inp w-full px-4 py-2.5 rounded-xl text-sm" oninput="filterRows()">
            </div>

            @forelse($students as $st)
                <div class="js-row border-t border-white/5 px-4 py-3" data-id="{{ $st->id }}"
                    data-search="{{ mb_strtolower($st->full_name) }} {{ $st->phone }}">

                    <div class="flex items-center justify-between gap-2 mb-2">
                        <div class="min-w-0">
                            <p class="text-white font-semibold text-sm truncate">{{ $st->full_name }}</p>
                            <p class="text-slate-500 text-xs truncate">
                                📞 {{ $st->phone ?: '—' }}
                                {{ $st->telegram_id ? '· ✅ botda' : '· ⏳ botsiz' }}
                                @if($st->group_grades_count)
                                    · 📈 {{ round((float) $st->group_avg, 1) }} ({{ $st->group_grades_count }} ball)
                                @endif
                            </p>
                        </div>
                        <button type="button" onclick="removeStudent({{ $st->id }}, @js($st->full_name))"
                            class="text-rose-300/70 bg-rose-500/10 px-2.5 py-1.5 rounded-lg text-xs shrink-0">Chiqarish</button>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="text" inputmode="decimal" placeholder="Ball (9 yoki 9/10)"
                            class="js-score inp flex-1 px-3 py-2.5 rounded-xl text-sm font-bold min-w-0">
                        <input type="text" maxlength="500" placeholder="💬 Izoh"
                            class="js-comment inp flex-1 px-3 py-2.5 rounded-xl text-sm min-w-0 hidden sm:block">
                        <button type="button" class="js-save btn px-5 py-2.5 rounded-xl font-bold text-sm shrink-0">Saqlash</button>
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center">
                    <p class="text-slate-400 text-sm mb-3">Bu guruhda hali o'quvchi yo'q.</p>
                    <button type="button" onclick="document.getElementById('addbox').removeAttribute('x-hide')"
                        class="btn px-5 py-2.5 rounded-xl font-bold text-sm">➕ O'quvchi qo'shish</button>
                </div>
            @endforelse
        </div>

        <!-- ══ O'QUVCHI QO'SHISH ══ -->
        <div id="addbox" class="card rounded-2xl p-4 space-y-4" x-hide>
            <div class="flex items-center justify-between">
                <p class="font-bold text-white text-sm">➕ Guruhga o'quvchi qo'shish</p>
                <button type="button" onclick="document.getElementById('addbox').setAttribute('x-hide','')"
                    class="text-slate-400 bg-white/10 px-2.5 py-1 rounded-lg text-xs">✕</button>
            </div>

            <div class="space-y-2">
                <label class="block text-slate-500 text-xs">Mavjud o'quvchini qo'shish</label>
                <div class="flex gap-2">
                    <select id="pick" class="inp flex-1 px-4 py-2.5 rounded-xl text-sm min-w-0">
                        <option value="">O'quvchini tanlang...</option>
                        @foreach($available as $a)
                            <option value="{{ $a->id }}">
                                {{ $a->full_name }}{{ $a->groups->isNotEmpty() ? ' — ' . $a->groups->pluck('name')->join(', ') : '' }}
                            </option>
                        @endforeach
                    </select>
                    <button type="button" onclick="addExisting()"
                        class="text-emerald-300 bg-emerald-500/15 border border-emerald-400/30 px-4 py-2.5 rounded-xl font-bold text-sm shrink-0">Qo'shish</button>
                </div>
                <p class="text-slate-500 text-xs">O'quvchi boshqa guruhda bo'lsa ham qo'shiladi — bir nechta guruhda bo'lishi mumkin.</p>
            </div>

            <div class="space-y-2 border-t border-white/10 pt-3">
                <label class="block text-slate-500 text-xs">Yoki yangi o'quvchi yaratish</label>
                <input type="text" id="nname" maxlength="100" placeholder="Ism familiya" class="inp w-full px-4 py-2.5 rounded-xl text-sm">
                <input type="text" id="nphone" inputmode="tel" maxlength="30" placeholder="+998 90 123 45 67" class="inp w-full px-4 py-2.5 rounded-xl text-sm">
                <button type="button" onclick="addNew()" class="btn w-full py-2.5 rounded-xl font-bold text-sm">Yaratib qo'shish</button>
            </div>
        </div>

        <!-- ══ OXIRGI BALLAR ══ -->
        @if($recent->isNotEmpty())
            <div class="card rounded-2xl overflow-hidden">
                <p class="px-4 py-3 bg-white/5 font-bold text-slate-300 text-sm">🕐 Guruhdagi oxirgi ballar</p>
                @foreach($recent as $gr)
                    <div class="px-4 py-2.5 border-t border-white/5 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-white text-sm truncate">{{ $gr->student?->full_name }}</p>
                            <p class="text-slate-500 text-xs truncate">
                                {{ $gr->subject?->name ?? 'Umumiy' }} · {{ $gr->graded_at?->format('d.m H:i') }}
                                @if($gr->teacher) · {{ $gr->teacher->full_name }} @endif
                            </p>
                        </div>
                        <span class="text-sky-300 font-bold text-sm shrink-0">⭐ {{ $gr->scoreLabel() }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        const BASE = @json(url('/parvoz'));
        const GROUP_ID = {{ $group->id }};

        function toast(msg, ok = true) {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.style.opacity = 1;
            t.style.background = ok ? '#059669' : '#e11d48';
            clearTimeout(t._h);
            t._h = setTimeout(() => t.style.opacity = 0, 2800);
        }

        async function api(url, body) {
            const r = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify(body),
            });
            if (r.redirected || r.status === 419 || r.status === 401) {
                toast("Sessiya tugadi — sahifani yangilang.", false);
                throw new Error('session');
            }
            const d = await r.json().catch(() => ({}));
            if (!r.ok) throw new Error(d.message || "Xatolik yuz berdi.");
            return d;
        }

        function reload(msg) {
            if (msg) sessionStorage.setItem('parvozToast', msg);
            location.reload();
        }

        function filterRows() {
            const q = document.getElementById('search').value.trim().toLowerCase();
            document.querySelectorAll('.js-row').forEach(r => {
                r.style.display = (!q || r.dataset.search.includes(q)) ? '' : 'none';
            });
        }

        // ── Ball saqlash ──────────────────────────────────────
        async function saveGrade(row) {
            const scoreEl = row.querySelector('.js-score');
            const commentEl = row.querySelector('.js-comment');
            const btn = row.querySelector('.js-save');
            const score = scoreEl.value.trim();
            if (!score) { scoreEl.focus(); return; }

            const subjectEl = document.getElementById('subject');

            btn.disabled = true;
            const old = btn.textContent;
            btn.textContent = '…';
            try {
                const d = await api(BASE + '/grade', {
                    student_id: row.dataset.id,
                    group_id: GROUP_ID,
                    score,
                    subject_id: subjectEl ? (subjectEl.value || null) : null,
                    comment: commentEl ? (commentEl.value.trim() || null) : null,
                });
                toast(d.message);
                scoreEl.value = '';
                if (commentEl) commentEl.value = '';
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
            finally { btn.textContent = old; btn.disabled = false; }
        }

        // ── A'zolik ───────────────────────────────────────────
        async function addExisting() {
            const id = document.getElementById('pick').value;
            if (!id) { toast("O'quvchini tanlang.", false); return; }
            try {
                const d = await api(BASE + '/student/' + id + '/group', { group_id: GROUP_ID, action: 'attach' });
                reload(d.message);
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        async function addNew() {
            const full_name = document.getElementById('nname').value.trim();
            if (full_name.length < 3) { toast("Ism familiyani to'liq yozing.", false); return; }
            try {
                const d = await api(BASE + '/student', {
                    full_name,
                    phone: document.getElementById('nphone').value.trim() || null,
                    group_id: GROUP_ID,
                });
                reload(d.message);
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        async function removeStudent(id, name) {
            if (!confirm(name + " shu guruhdan chiqarilsinmi?\n\nO'quvchi o'chmaydi, boshqa guruhlarida qoladi.")) return;
            try {
                const d = await api(BASE + '/student/' + id + '/group', { group_id: GROUP_ID, action: 'detach' });
                reload(d.message);
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        // ── Hodisalar ─────────────────────────────────────────
        document.addEventListener('click', e => {
            const row = e.target.closest('.js-row');
            if (row && e.target.closest('.js-save')) saveGrade(row);
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Enter' && (e.target.classList.contains('js-score') || e.target.classList.contains('js-comment'))) {
                saveGrade(e.target.closest('.js-row'));
            }
        });

        (function restore() {
            const msg = sessionStorage.getItem('parvozToast');
            if (msg) { sessionStorage.removeItem('parvozToast'); toast(msg); }
        })();
    </script>
</body>

</html>
