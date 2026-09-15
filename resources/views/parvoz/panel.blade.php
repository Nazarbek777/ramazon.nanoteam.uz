<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Parvoz - O'qituvchi paneli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * { -webkit-tap-highlight-color: transparent; }

        html, body { overflow-x: hidden; }

        body {
            font-family: 'Outfit', sans-serif;
            background: #0f172a;
            min-height: 100vh;
        }

        .card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .inp {
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #fff;
        }

        .inp:focus { outline: none; border-color: #0ea5e9; }
        .inp::placeholder { color: rgba(148, 163, 184, 0.6); }

        .btn { background: #0ea5e9; color: #fff; }
        .btn:active { background: #0284c7; }

        .tab-on { background: #0ea5e9; color: #fff; }
        .tab-off { background: rgba(255,255,255,.06); color: #94a3b8; }

        #toast { transition: opacity .25s ease; }
        [x-hide] { display: none !important; }
    </style>
</head>

<body class="pb-24">

    <!-- ══ SARLAVHA ══ -->
    <div class="bg-slate-900 border-b border-white/10 px-4 py-3">
        <div class="max-w-5xl mx-auto flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-white font-bold truncate">👨‍🏫 {{ $teacher->full_name }}</p>
                <p class="text-slate-400 text-xs">Parvoz o'quv markazi</p>
            </div>
            <form method="POST" action="{{ route('parvoz.logout') }}" class="shrink-0">
                @csrf
                <button class="text-slate-300 text-sm bg-white/10 px-3 py-2 rounded-xl">Chiqish</button>
            </form>
        </div>
    </div>

    <!-- ══ 3 TA BO'LIM ══ -->
    <div class="bg-slate-900 border-b border-white/10 px-4 py-2 sticky top-0 z-20">
        <div class="max-w-5xl mx-auto grid grid-cols-3 gap-2">
            <button type="button" id="tab-ball" onclick="showTab('ball')" class="tab-on py-2.5 rounded-xl text-sm font-bold">⭐ Ball</button>
            <button type="button" id="tab-guruh" onclick="showTab('guruh')" class="tab-off py-2.5 rounded-xl text-sm font-bold">👥 Guruhlar</button>
            <button type="button" id="tab-oqit" onclick="showTab('oqit')" class="tab-off py-2.5 rounded-xl text-sm font-bold">🧑‍🏫 O'qituvchi</button>
        </div>
    </div>

    <div id="toast" class="fixed top-4 left-1/2 -translate-x-1/2 z-50 px-4 py-3 rounded-2xl text-sm font-bold opacity-0 pointer-events-none max-w-[90vw] text-center"></div>

    <div class="max-w-5xl mx-auto px-4 py-4">

        {{-- ═══════════════ 1-BO'LIM: BALL QO'YISH ═══════════════ --}}
        <section id="pane-ball" class="space-y-3">

            <p class="text-slate-400 text-xs px-1">
                Ball katagiga yozing va <b class="text-sky-300">Saqlash</b> bosing. O'quvchiga darhol xabar boradi.
            </p>

            <!-- Guruh tanlash -->
            <select id="gfilter" onchange="applyFilter()" class="inp w-full px-4 py-3 rounded-2xl text-sm font-semibold">
                @if(count($myGroupIds))
                    <option value="mine">⭐ Mening guruhlarim</option>
                @endif
                <option value="all" @if(!count($myGroupIds)) selected @endif>📋 Barcha o'quvchilar</option>
                @foreach($groups as $g)
                    <option value="g{{ $g->id }}">👥 {{ $g->name }} ({{ $g->students->count() }} ta)</option>
                @endforeach
                @if($ungrouped->isNotEmpty())
                    <option value="none">🆕 Guruhsiz ({{ $ungrouped->count() }} ta)</option>
                @endif
            </select>

            <input type="text" id="search" placeholder="🔍 Ism yoki telefon bo'yicha qidirish" autocomplete="off"
                class="inp w-full px-4 py-3 rounded-2xl text-sm">

            @foreach($groups as $g)
                <div class="card rounded-2xl overflow-hidden js-sec" data-gkey="g{{ $g->id }}"
                    data-mine="{{ in_array($g->id, $myGroupIds) ? '1' : '0' }}">
                    <div class="px-4 py-3 bg-white/5 flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <p class="font-bold text-sky-300 text-sm truncate">👥 {{ $g->name }}</p>
                            <p class="text-slate-500 text-xs truncate">
                                🧑‍🏫 {{ $g->teachers->isNotEmpty() ? $g->teachers->pluck('full_name')->join(', ') : 'o\'qituvchi tanlanmagan' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-slate-500 text-xs">{{ $g->students->count() }} ta</span>
                            <button type="button" onclick="openAdd({{ $g->id }}, @js($g->name))"
                                class="text-sky-300 bg-sky-500/15 border border-sky-400/30 px-3 py-1.5 rounded-lg text-xs font-bold">
                                ➕ O'quvchi
                            </button>
                        </div>
                    </div>

                    @forelse($g->students as $st)
                        @include('parvoz._row', ['st' => $st])
                    @empty
                        <p class="js-empty px-4 py-5 text-center text-slate-500 text-xs">Bu guruhda o'quvchi yo'q.</p>
                    @endforelse
                </div>
            @endforeach

            @if($ungrouped->isNotEmpty())
                <div class="card rounded-2xl overflow-hidden js-sec" data-gkey="none" data-mine="0">
                    <div class="px-4 py-3 bg-white/5 flex items-center justify-between gap-2">
                        <p class="font-bold text-amber-300 text-sm truncate">🆕 Guruhsiz o'quvchilar</p>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-slate-500 text-xs">{{ $ungrouped->count() }} ta</span>
                            <button type="button" onclick="openAdd('', 'Guruhsiz')"
                                class="text-sky-300 bg-sky-500/15 border border-sky-400/30 px-3 py-1.5 rounded-lg text-xs font-bold">
                                ➕ O'quvchi
                            </button>
                        </div>
                    </div>
                    @foreach($ungrouped as $st)
                        @include('parvoz._row', ['st' => $st])
                    @endforeach
                </div>
            @endif

            @if($groups->isEmpty() && $ungrouped->isEmpty())
                <div class="card rounded-2xl p-8 text-center">
                    <p class="text-slate-400 text-sm">Hali o'quvchi yo'q.<br>Ular botdan ro'yxatdan o'tadi.</p>
                </div>
            @endif

            @if($lastGrades->isNotEmpty())
                <div class="card rounded-2xl overflow-hidden">
                    <p class="px-4 py-3 bg-white/5 font-bold text-slate-300 text-sm">🕐 Oxirgi qo'ygan ballaringiz</p>
                    @foreach($lastGrades as $gr)
                        <div class="px-4 py-2.5 border-t border-white/5 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-white text-sm truncate">{{ $gr->student?->full_name }}</p>
                                <p class="text-slate-500 text-xs">{{ $gr->graded_at?->format('d.m H:i') }}</p>
                            </div>
                            <span class="text-sky-300 font-bold text-sm shrink-0">⭐ {{ $gr->scoreLabel() }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ═══════════════ 2-BO'LIM: GURUHLAR ═══════════════ --}}
        <section id="pane-guruh" x-hide>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">

                <!-- Yangi guruh kartasi -->
                <div class="card rounded-2xl p-4 space-y-3 border-dashed border-sky-400/30">
                    <p class="font-bold text-sky-300 text-sm">➕ Yangi guruh</p>
                    <input type="text" id="ng-name" maxlength="100" placeholder="Guruh nomi"
                        class="inp w-full px-4 py-2.5 rounded-xl text-sm">
                    <select id="ng-teacher" class="inp w-full px-4 py-2.5 rounded-xl text-sm">
                        <option value="">🧑‍🏫 O'qituvchini tanlang</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}" @selected($t->id === $teacher->id)>{{ $t->full_name }}</option>
                        @endforeach
                    </select>
                    <button type="button" onclick="createGroup()" class="btn w-full py-2.5 rounded-xl font-bold text-sm">Yaratish</button>
                </div>

                <!-- Guruh kartalari -->
                @foreach($groups as $g)
                    <div class="card rounded-2xl p-4 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-bold text-white truncate">👥 {{ $g->name }}</p>
                            <span class="text-xs text-sky-300 bg-sky-500/15 px-2 py-0.5 rounded-lg shrink-0">{{ $g->students->count() }}</span>
                        </div>

                        <div>
                            <label class="block text-slate-500 text-xs mb-1">Guruh nomi</label>
                            <input type="text" id="gn-{{ $g->id }}" value="{{ $g->name }}" maxlength="100"
                                class="inp w-full px-4 py-2.5 rounded-xl text-sm">
                        </div>

                        <div>
                            <label class="block text-slate-500 text-xs mb-1">O'qituvchi</label>
                            <select id="gt-{{ $g->id }}" class="inp w-full px-4 py-2.5 rounded-xl text-sm">
                                <option value="">🧑‍🏫 Tanlanmagan</option>
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}" @selected($g->teachers->contains('id', $t->id))>{{ $t->full_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button type="button" onclick="saveGroup({{ $g->id }})" class="btn flex-1 py-2.5 rounded-xl font-bold text-sm">💾 Saqlash</button>
                            <button type="button" onclick="deleteGroup({{ $g->id }}, @js($g->name))"
                                class="text-rose-300 bg-rose-500/15 border border-rose-400/30 px-3.5 py-2.5 rounded-xl font-bold text-sm shrink-0">🗑</button>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($groups->isEmpty())
                <p class="text-slate-500 text-sm text-center mt-4">Hali guruh yo'q — chapdagi kartadan yarating.</p>
            @endif
        </section>

        {{-- ═══════════════ 3-BO'LIM: O'QITUVCHILAR ═══════════════ --}}
        <section id="pane-oqit" x-hide>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">

                <!-- Yangi o'qituvchi kartasi -->
                <div class="card rounded-2xl p-4 space-y-3 border-dashed border-sky-400/30">
                    <p class="font-bold text-sky-300 text-sm">➕ Yangi o'qituvchi</p>
                    <input type="text" id="nt-name" maxlength="100" placeholder="F.I.O"
                        class="inp w-full px-4 py-2.5 rounded-xl text-sm">
                    <input type="text" id="nt-phone" maxlength="30" placeholder="Telefon (ixtiyoriy)"
                        class="inp w-full px-4 py-2.5 rounded-xl text-sm">
                    <button type="button" onclick="createTeacher()" class="btn w-full py-2.5 rounded-xl font-bold text-sm">Qo'shish</button>
                    <p class="text-slate-500 text-xs">Kirish kodi avtomatik beriladi.</p>
                </div>

                <!-- O'qituvchi kartalari -->
                @foreach($teachers as $t)
                    <div class="card rounded-2xl p-4 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-bold text-white truncate">
                                🧑‍🏫 {{ $t->full_name }}
                                @if($t->id === $teacher->id)
                                    <span class="text-emerald-300 text-xs">(siz)</span>
                                @endif
                            </p>
                            <span class="text-xs text-slate-400 shrink-0">{{ $t->grades_count }} ball</span>
                        </div>

                        <div>
                            <label class="block text-slate-500 text-xs mb-1">F.I.O</label>
                            <input type="text" id="tn-{{ $t->id }}" value="{{ $t->full_name }}" maxlength="100"
                                class="inp w-full px-4 py-2.5 rounded-xl text-sm">
                        </div>

                        <div>
                            <label class="block text-slate-500 text-xs mb-1">Telefon</label>
                            <input type="text" id="tp-{{ $t->id }}" value="{{ $t->phone }}" maxlength="30" placeholder="—"
                                class="inp w-full px-4 py-2.5 rounded-xl text-sm">
                        </div>

                        <div class="bg-amber-500/10 border border-amber-400/20 rounded-xl px-3 py-2 flex items-center justify-between gap-2">
                            <span class="text-xs text-slate-400">Kirish kodi</span>
                            <span class="flex items-center gap-2">
                                <b class="font-mono text-amber-300 tracking-widest text-sm">{{ $t->access_code }}</b>
                                <button type="button" onclick="resetCode({{ $t->id }}, @js($t->full_name))"
                                    class="text-amber-300/70 hover:text-amber-300 text-xs" title="Yangi kod berish">🔄</button>
                            </span>
                        </div>

                        <div class="flex gap-2">
                            <button type="button" onclick="saveTeacher({{ $t->id }})" class="btn flex-1 py-2.5 rounded-xl font-bold text-sm">💾 Saqlash</button>
                            @if($t->id !== $teacher->id)
                                <button type="button" onclick="deleteTeacher({{ $t->id }}, @js($t->full_name))"
                                    class="text-rose-300 bg-rose-500/15 border border-rose-400/30 px-3.5 py-2.5 rounded-xl font-bold text-sm shrink-0">🗑</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    {{-- ═══════════════ YANGI O'QUVCHI QO'SHISH OYNASI ═══════════════ --}}
    <div id="addmodal" class="fixed inset-0 z-40 bg-black/70 p-4 flex items-end sm:items-center justify-center" x-hide
        onclick="if(event.target===this) closeAdd()">
        <div class="bg-slate-900 border border-white/10 rounded-3xl p-5 w-full max-w-md space-y-3">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-white font-bold">➕ Yangi o'quvchi</p>
                    <p id="a-group" class="text-sky-300 text-sm truncate"></p>
                </div>
                <button type="button" onclick="closeAdd()" class="text-slate-400 bg-white/10 px-3 py-1.5 rounded-xl text-sm shrink-0">✕</button>
            </div>

            <div>
                <label class="block text-slate-500 text-xs mb-1">Ism familiya</label>
                <input type="text" id="a-name" maxlength="100" placeholder="Masalan: Aliyev Alisher"
                    class="inp w-full px-4 py-3 rounded-xl text-sm">
            </div>

            <div>
                <label class="block text-slate-500 text-xs mb-1">Telefon raqami</label>
                <input type="text" id="a-phone" inputmode="tel" maxlength="30" placeholder="+998 90 123 45 67"
                    class="inp w-full px-4 py-3 rounded-xl text-sm">
                <p class="text-slate-500 text-xs mt-1">
                    O'quvchi botga shu raqam bilan kirsa, kabineti avtomatik ochiladi.
                </p>
            </div>

            <button type="button" onclick="addStudent()" class="btn w-full py-3 rounded-xl font-bold text-sm">Qo'shish</button>
        </div>
    </div>

    {{-- ═══════════════ O'QUVCHI OYNASI ═══════════════ --}}
    <div id="modal" class="fixed inset-0 z-40 bg-black/70 p-4 flex items-end sm:items-center justify-center" x-hide onclick="if(event.target===this) closeModal()">
        <div class="bg-slate-900 border border-white/10 rounded-3xl p-5 w-full max-w-md space-y-3 max-h-[90vh] overflow-y-auto">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p id="m-name" class="text-white font-bold truncate"></p>
                    <p id="m-phone" class="text-slate-400 text-sm font-mono"></p>
                </div>
                <button type="button" onclick="closeModal()" class="text-slate-400 bg-white/10 px-3 py-1.5 rounded-xl text-sm shrink-0">✕</button>
            </div>

            <div class="border-t border-white/10 pt-3 space-y-2">
                <p class="text-slate-400 text-xs font-semibold">⭐ Ball qo'yish (fan va izoh bilan)</p>
                <input type="text" id="m-score" inputmode="decimal" placeholder="Ball: 9 yoki 9/10" class="inp w-full px-4 py-3 rounded-xl text-sm font-bold">
                @if($subjects->isNotEmpty())
                    <select id="m-subject" class="inp w-full px-4 py-3 rounded-xl text-sm">
                        <option value="">📚 Fan (ixtiyoriy)</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                @endif
                <input type="text" id="m-comment" maxlength="500" placeholder="💬 Izoh (ixtiyoriy)" class="inp w-full px-4 py-3 rounded-xl text-sm">
                <button type="button" onclick="modalSave()" class="btn w-full py-3 rounded-xl font-bold text-sm">Ballni saqlash</button>
            </div>

            @if($allGroups->isNotEmpty())
                <div class="border-t border-white/10 pt-3 space-y-2">
                    <p class="text-slate-400 text-xs font-semibold">👥 Guruhga biriktirish</p>
                    <select id="m-group" class="inp w-full px-4 py-3 rounded-xl text-sm">
                        <option value="">Guruhni tanlang...</option>
                        @foreach($allGroups as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" onclick="assignGroup()" class="w-full py-2.5 rounded-xl font-bold text-sm text-emerald-300 bg-emerald-500/15 border border-emerald-400/30">Biriktirish</button>
                </div>
            @endif

            <div class="border-t border-white/10 pt-3 space-y-2">
                <p class="text-slate-400 text-xs font-semibold">✏️ Ismini tuzatish</p>
                <input type="text" id="m-rename" minlength="3" maxlength="100" class="inp w-full px-4 py-3 rounded-xl text-sm">
                <button type="button" onclick="renameStudent()" class="w-full py-2.5 rounded-xl font-bold text-sm text-sky-300 bg-sky-500/15 border border-sky-400/30">Ismni saqlash</button>
            </div>

            <div class="border-t border-white/10 pt-3">
                <button type="button" onclick="blockStudent()" class="w-full py-2.5 rounded-xl font-bold text-sm text-rose-300 bg-rose-500/15 border border-rose-400/30">🚫 O'quvchini bloklash</button>
            </div>
        </div>
    </div>

    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        const BASE = @json(url('/parvoz'));
        let current = null;

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

        function reload() { setTimeout(() => location.reload(), 800); }

        // ── Bo'limlar ─────────────────────────────────────────
        function showTab(name) {
            ['ball', 'guruh', 'oqit'].forEach(k => {
                document.getElementById('pane-' + k).toggleAttribute('x-hide', k !== name);
                const b = document.getElementById('tab-' + (k === 'oqit' ? 'oqit' : k));
                b.className = b.className.replace(/tab-(on|off)/, k === name ? 'tab-on' : 'tab-off');
            });
            window.scrollTo(0, 0);
        }

        // ── Filtr + qidiruv ───────────────────────────────────
        function applyFilter() {
            const f = document.getElementById('gfilter').value;
            const q = document.getElementById('search').value.trim().toLowerCase();

            document.querySelectorAll('.js-sec').forEach(sec => {
                let any = false;
                sec.querySelectorAll('.js-row').forEach(row => {
                    const ok = !q || row.dataset.search.includes(q);
                    row.style.display = ok ? '' : 'none';
                    if (ok) any = true;
                });

                // "Mening guruhlarim" — o'z guruhlari + hali guruhga qo'shilmagan yangi o'quvchilar
                let pass = f === 'all'
                    || sec.dataset.gkey === f
                    || (f === 'mine' && (sec.dataset.mine === '1' || sec.dataset.gkey === 'none'));
                const emptyPh = sec.querySelector('.js-empty');
                if (emptyPh) emptyPh.style.display = q ? 'none' : '';
                sec.style.display = (pass && (any || (!q && emptyPh))) ? '' : 'none';
            });
        }

        document.getElementById('search').addEventListener('input', applyFilter);

        // ── Ball (tez) ────────────────────────────────────────
        async function quickSave(row) {
            const inp = row.querySelector('.js-score');
            const btn = row.querySelector('.js-save');
            const score = inp.value.trim();
            if (!score) { inp.focus(); return; }

            btn.disabled = true;
            const old = btn.textContent;
            btn.textContent = '…';
            try {
                const d = await api(BASE + '/grade', { student_id: row.dataset.id, score });
                toast(d.message);
                inp.value = '';
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
            finally { btn.textContent = old; btn.disabled = false; }
        }

        // ── Yangi o'quvchi qo'shish ───────────────────────────
        let addGroupId = '';

        function openAdd(groupId, groupName) {
            addGroupId = groupId || '';
            document.getElementById('a-group').textContent = '👥 ' + groupName;
            document.getElementById('a-name').value = '';
            document.getElementById('a-phone').value = '';
            document.getElementById('addmodal').removeAttribute('x-hide');
            setTimeout(() => document.getElementById('a-name').focus(), 100);
        }

        function closeAdd() {
            document.getElementById('addmodal').setAttribute('x-hide', '');
        }

        async function addStudent() {
            const full_name = document.getElementById('a-name').value.trim();
            if (full_name.length < 3) { toast("Ism familiyani to'liq yozing.", false); return; }

            try {
                const d = await api(BASE + '/student', {
                    full_name,
                    phone: document.getElementById('a-phone').value.trim() || null,
                    group_id: addGroupId || null,
                });
                toast(d.message);
                closeAdd();
                reload();
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        // ── O'quvchi oynasi ───────────────────────────────────
        function openModal(row) {
            current = row;
            document.getElementById('m-name').textContent = row.querySelector('.js-name').textContent;
            document.getElementById('m-phone').textContent = '📞 ' + (row.dataset.phone || '—');
            document.getElementById('m-rename').value = row.querySelector('.js-name').textContent;
            document.getElementById('m-score').value = '';
            document.getElementById('m-comment').value = '';
            document.getElementById('modal').removeAttribute('x-hide');
        }

        function closeModal() {
            document.getElementById('modal').setAttribute('x-hide', '');
            current = null;
        }

        async function modalSave() {
            if (!current) return;
            const score = document.getElementById('m-score').value.trim();
            if (!score) { document.getElementById('m-score').focus(); return; }
            const sub = document.getElementById('m-subject');
            try {
                const d = await api(BASE + '/grade', {
                    student_id: current.dataset.id,
                    score,
                    subject_id: sub ? (sub.value || null) : null,
                    comment: document.getElementById('m-comment').value.trim() || null,
                });
                toast(d.message);
                closeModal();
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        async function assignGroup() {
            if (!current) return;
            const sel = document.getElementById('m-group');
            if (!sel.value) { toast("Guruhni tanlang.", false); return; }
            try {
                const d = await api(BASE + '/student/' + current.dataset.id + '/group', { group_id: sel.value });
                toast(d.message); closeModal(); reload();
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        async function renameStudent() {
            if (!current) return;
            const name = document.getElementById('m-rename').value.trim();
            if (name.length < 3) { toast("Ism juda qisqa.", false); return; }
            try {
                const d = await api(BASE + '/student/' + current.dataset.id + '/rename', { full_name: name });
                current.querySelector('.js-name').textContent = d.full_name;
                document.getElementById('m-name').textContent = d.full_name;
                toast(d.message);
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        async function blockStudent() {
            if (!current) return;
            const name = current.querySelector('.js-name').textContent;
            if (!confirm(name + " bloklansinmi?")) return;
            try {
                const d = await api(BASE + '/student/' + current.dataset.id + '/block', {});
                current.remove(); closeModal(); toast(d.message);
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        // ── Guruhlar ──────────────────────────────────────────
        async function createGroup() {
            const name = document.getElementById('ng-name').value.trim();
            if (name.length < 2) { toast("Guruh nomini yozing.", false); return; }
            try {
                const d = await api(BASE + '/group', { name, teacher_id: document.getElementById('ng-teacher').value || null });
                toast(d.message); reload();
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        async function saveGroup(id) {
            const name = document.getElementById('gn-' + id).value.trim();
            if (name.length < 2) { toast("Guruh nomini yozing.", false); return; }
            try {
                const d = await api(BASE + '/group/' + id + '/rename', { name, teacher_id: document.getElementById('gt-' + id).value || null });
                toast(d.message); reload();
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        async function deleteGroup(id, name) {
            if (!confirm('"' + name + '" o\'chirilsinmi?\n\nO\'quvchilari o\'chmaydi — guruhsiz bo\'lib qoladi.')) return;
            try {
                const d = await api(BASE + '/group/' + id + '/delete', {});
                toast(d.message); reload();
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        // ── O'qituvchilar ─────────────────────────────────────
        async function createTeacher() {
            const full_name = document.getElementById('nt-name').value.trim();
            if (full_name.length < 3) { toast("F.I.O ni to'liq yozing.", false); return; }
            try {
                const d = await api(BASE + '/teacher', { full_name, phone: document.getElementById('nt-phone').value.trim() || null });
                toast(d.message); reload();
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        async function saveTeacher(id) {
            const full_name = document.getElementById('tn-' + id).value.trim();
            if (full_name.length < 3) { toast("F.I.O ni to'liq yozing.", false); return; }
            try {
                const d = await api(BASE + '/teacher/' + id + '/update', {
                    full_name,
                    phone: document.getElementById('tp-' + id).value.trim() || null,
                });
                toast(d.message); reload();
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        async function resetCode(id, name) {
            if (!confirm(name + " uchun yangi kirish kodi berilsinmi?\n\nEski kod ishlamay qoladi.")) return;
            try {
                const d = await api(BASE + '/teacher/' + id + '/code', {});
                toast(d.message); reload();
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        async function deleteTeacher(id, name) {
            if (!confirm(name + " o'chirilsinmi?")) return;
            try {
                const d = await api(BASE + '/teacher/' + id + '/delete', {});
                toast(d.message); reload();
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        // ── Hodisalar ─────────────────────────────────────────
        document.addEventListener('click', e => {
            const row = e.target.closest('.js-row');
            if (!row) return;
            if (e.target.closest('.js-save')) quickSave(row);
            else if (e.target.closest('.js-open')) openModal(row);
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Enter' && e.target.classList.contains('js-score')) quickSave(e.target.closest('.js-row'));
            if (e.key === 'Enter' && (e.target.id === 'a-name' || e.target.id === 'a-phone')) addStudent();
            if (e.key === 'Escape') { closeModal(); closeAdd(); }
        });

        applyFilter();
    </script>
</body>

</html>
