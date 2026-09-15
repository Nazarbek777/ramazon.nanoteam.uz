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
        body { font-family: 'Outfit', sans-serif; background: #0f172a; min-height: 100vh; }

        .card { background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1); }
        .inp { background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.15); color: #fff; }
        .inp:focus { outline: none; border-color: #0ea5e9; }
        .inp::placeholder { color: rgba(148,163,184,.6); }
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

    <!-- ══ BO'LIMLAR ══ -->
    <div class="bg-slate-900 border-b border-white/10 px-4 py-2 sticky top-0 z-20">
        <div class="max-w-5xl mx-auto grid grid-cols-3 gap-2">
            <button type="button" id="tab-guruh" onclick="showTab('guruh')" class="tab-on  py-2.5 rounded-xl text-sm font-bold">👥 Guruhlar</button>
            <button type="button" id="tab-oquv"  onclick="showTab('oquv')"  class="tab-off py-2.5 rounded-xl text-sm font-bold">🎓 O'quvchilar</button>
            <button type="button" id="tab-oqit"  onclick="showTab('oqit')"  class="tab-off py-2.5 rounded-xl text-sm font-bold">🧑‍🏫 Ustozlar</button>
        </div>
    </div>

    <div id="toast" class="fixed top-4 left-1/2 -translate-x-1/2 z-50 px-4 py-3 rounded-2xl text-sm font-bold opacity-0 pointer-events-none max-w-[90vw] text-center"></div>

    <div class="max-w-5xl mx-auto px-4 py-4">

        {{-- ════════════ GURUHLAR ════════════ --}}
        <section id="pane-guruh">
            <p class="text-slate-400 text-xs px-1 mb-3">Ball qo'yish uchun guruhni oching.</p>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">

                <div class="card rounded-2xl p-4 space-y-3 border-dashed border-sky-400/30">
                    <p class="font-bold text-sky-300 text-sm">➕ Yangi guruh</p>
                    <input type="text" id="ng-name" maxlength="100" placeholder="Guruh nomi" class="inp w-full px-4 py-2.5 rounded-xl text-sm">
                    <select id="ng-teacher" class="inp w-full px-4 py-2.5 rounded-xl text-sm">
                        <option value="">🧑‍🏫 O'qituvchini tanlang</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}" @selected($t->id === $teacher->id)>{{ $t->full_name }}</option>
                        @endforeach
                    </select>
                    <button type="button" onclick="createGroup()" class="btn w-full py-2.5 rounded-xl font-bold text-sm">Yaratish</button>
                </div>

                @foreach($groups as $g)
                    <div class="card rounded-2xl p-4 space-y-3">
                        <a href="{{ route('parvoz.group.show', $g) }}" class="block">
                            <div class="flex items-center justify-between gap-2">
                                <p class="font-bold text-white truncate">👥 {{ $g->name }}</p>
                                <span class="text-xs text-sky-300 bg-sky-500/15 px-2 py-0.5 rounded-lg shrink-0">{{ $g->students_count }}</span>
                            </div>
                            <p class="text-slate-500 text-xs truncate mt-1">
                                🧑‍🏫 {{ $g->teachers->isNotEmpty() ? $g->teachers->pluck('full_name')->join(', ') : 'tanlanmagan' }}
                            </p>
                        </a>

                        <a href="{{ route('parvoz.group.show', $g) }}"
                            class="btn block text-center py-2.5 rounded-xl font-bold text-sm">⭐ Ochish va ball qo'yish</a>

                        <details>
                            <summary class="text-slate-400 text-xs cursor-pointer select-none py-1">⚙️ Sozlamalari</summary>
                            <div class="space-y-2 mt-2">
                                <input type="text" id="gn-{{ $g->id }}" value="{{ $g->name }}" maxlength="100" class="inp w-full px-4 py-2.5 rounded-xl text-sm">
                                <select id="gt-{{ $g->id }}" class="inp w-full px-4 py-2.5 rounded-xl text-sm">
                                    <option value="">🧑‍🏫 Tanlanmagan</option>
                                    @foreach($teachers as $t)
                                        <option value="{{ $t->id }}" @selected($g->teachers->contains('id', $t->id))>{{ $t->full_name }}</option>
                                    @endforeach
                                </select>
                                <div class="flex gap-2">
                                    <button type="button" onclick="saveGroup({{ $g->id }})" class="btn flex-1 py-2.5 rounded-xl font-bold text-sm">💾 Saqlash</button>
                                    <button type="button" onclick="deleteGroup({{ $g->id }}, @js($g->name))"
                                        class="text-rose-300 bg-rose-500/15 border border-rose-400/30 px-3.5 py-2.5 rounded-xl font-bold text-sm shrink-0">🗑</button>
                                </div>
                            </div>
                        </details>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ════════════ O'QUVCHILAR ════════════ --}}
        <section id="pane-oquv" class="space-y-3" x-hide>

            <div class="card rounded-2xl p-4 space-y-3">
                <p class="font-bold text-sky-300 text-sm">➕ Yangi o'quvchi</p>
                <div class="grid gap-3 sm:grid-cols-3">
                    <input type="text" id="ns-name" maxlength="100" placeholder="Ism familiya" class="inp w-full px-4 py-2.5 rounded-xl text-sm">
                    <input type="text" id="ns-phone" inputmode="tel" maxlength="30" placeholder="+998 90 123 45 67" class="inp w-full px-4 py-2.5 rounded-xl text-sm">
                    <select id="ns-group" class="inp w-full px-4 py-2.5 rounded-xl text-sm">
                        <option value="">👥 Guruhsiz</option>
                        @foreach($groups as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" onclick="createStudent()" class="btn w-full py-2.5 rounded-xl font-bold text-sm">Qo'shish</button>
            </div>

            <input type="text" id="ssearch" placeholder="🔍 Qidirish" autocomplete="off" class="inp w-full px-4 py-3 rounded-2xl text-sm" oninput="filterStudents()">

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($allStudents as $st)
                    <div class="card rounded-2xl p-4 space-y-3 js-scard" data-search="{{ mb_strtolower($st->full_name) }} {{ $st->phone }}">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-bold text-white truncate">🎓 {{ $st->full_name }}</p>
                            <span class="text-xs shrink-0">{{ $st->telegram_id ? '✅' : '⏳' }}</span>
                        </div>

                        <div>
                            <label class="block text-slate-500 text-xs mb-1">Ism familiya</label>
                            <input type="text" id="sn-{{ $st->id }}" value="{{ $st->full_name }}" maxlength="100" class="inp w-full px-4 py-2.5 rounded-xl text-sm">
                        </div>

                        <div>
                            <label class="block text-slate-500 text-xs mb-1">Telefon</label>
                            <input type="text" id="sp-{{ $st->id }}" value="{{ $st->phone }}" maxlength="30" placeholder="—" class="inp w-full px-4 py-2.5 rounded-xl text-sm">
                        </div>

                        <div>
                            <label class="block text-slate-500 text-xs mb-1">Guruhlari (bir nechta bo'lishi mumkin)</label>
                            @if($groups->isEmpty())
                                <p class="text-slate-500 text-xs">Hali guruh yo'q.</p>
                            @else
                                <div class="space-y-1.5">
                                    @foreach($groups as $g)
                                        @php $in = $st->groups->contains('id', $g->id); @endphp
                                        <label class="flex items-center gap-2 text-sm {{ $in ? 'text-sky-300' : 'text-slate-400' }}">
                                            <input type="checkbox" {{ $in ? 'checked' : '' }}
                                                onchange="toggleGroup({{ $st->id }}, {{ $g->id }}, this)"
                                                class="w-4 h-4 rounded accent-sky-500">
                                            <span class="truncate">{{ $g->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="flex gap-2">
                            <button type="button" onclick="saveStudent({{ $st->id }})" class="btn flex-1 py-2.5 rounded-xl font-bold text-sm">💾 Saqlash</button>
                            <button type="button" onclick="blockStudentById({{ $st->id }}, @js($st->full_name))"
                                class="text-rose-300 bg-rose-500/15 border border-rose-400/30 px-3.5 py-2.5 rounded-xl font-bold text-sm shrink-0">🚫</button>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($allStudents->isEmpty())
                <p class="text-slate-500 text-sm text-center mt-4">Hali o'quvchi yo'q — yuqoridan qo'shing yoki ular botdan ro'yxatdan o'tadi.</p>
            @endif
        </section>

        {{-- ════════════ USTOZLAR ════════════ --}}
        <section id="pane-oqit" x-hide>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">

                <div class="card rounded-2xl p-4 space-y-3 border-dashed border-sky-400/30">
                    <p class="font-bold text-sky-300 text-sm">➕ Yangi o'qituvchi</p>
                    <input type="text" id="nt-name" maxlength="100" placeholder="F.I.O" class="inp w-full px-4 py-2.5 rounded-xl text-sm">
                    <input type="text" id="nt-phone" maxlength="30" placeholder="Telefon (ixtiyoriy)" class="inp w-full px-4 py-2.5 rounded-xl text-sm">
                    <button type="button" onclick="createTeacher()" class="btn w-full py-2.5 rounded-xl font-bold text-sm">Qo'shish</button>
                    <p class="text-slate-500 text-xs">Kirish kodi avtomatik beriladi.</p>
                </div>

                @foreach($teachers as $t)
                    <div class="card rounded-2xl p-4 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-bold text-white truncate">
                                🧑‍🏫 {{ $t->full_name }}
                                @if($t->id === $teacher->id)<span class="text-emerald-300 text-xs">(siz)</span>@endif
                            </p>
                            <span class="text-xs text-slate-400 shrink-0">{{ $t->grades_count }} ball</span>
                        </div>

                        <div>
                            <label class="block text-slate-500 text-xs mb-1">F.I.O</label>
                            <input type="text" id="tn-{{ $t->id }}" value="{{ $t->full_name }}" maxlength="100" class="inp w-full px-4 py-2.5 rounded-xl text-sm">
                        </div>

                        <div>
                            <label class="block text-slate-500 text-xs mb-1">Telefon</label>
                            <input type="text" id="tp-{{ $t->id }}" value="{{ $t->phone }}" maxlength="30" placeholder="—" class="inp w-full px-4 py-2.5 rounded-xl text-sm">
                        </div>

                        <div class="bg-amber-500/10 border border-amber-400/20 rounded-xl px-3 py-2 flex items-center justify-between gap-2">
                            <span class="text-xs text-slate-400">Kirish kodi</span>
                            <span class="flex items-center gap-2">
                                <b class="font-mono text-amber-300 tracking-widest text-sm">{{ $t->access_code }}</b>
                                <button type="button" onclick="resetCode({{ $t->id }}, @js($t->full_name))" class="text-amber-300/70 text-xs" title="Yangi kod">🔄</button>
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

        function showTab(name) {
            ['guruh', 'oquv', 'oqit'].forEach(k => {
                document.getElementById('pane-' + k).toggleAttribute('x-hide', k !== name);
                const b = document.getElementById('tab-' + k);
                b.className = b.className.replace(/tab-(on|off)/, k === name ? 'tab-on' : 'tab-off');
            });
            sessionStorage.setItem('parvozTab', name);
            window.scrollTo(0, 0);
        }

        function filterStudents() {
            const q = document.getElementById('ssearch').value.trim().toLowerCase();
            document.querySelectorAll('.js-scard').forEach(c => {
                c.style.display = (!q || c.dataset.search.includes(q)) ? '' : 'none';
            });
        }

        // ── O'quvchilar ───────────────────────────────────────
        async function createStudent() {
            const full_name = document.getElementById('ns-name').value.trim();
            if (full_name.length < 3) { toast("Ism familiyani to'liq yozing.", false); return; }
            try {
                const d = await api(BASE + '/student', {
                    full_name,
                    phone: document.getElementById('ns-phone').value.trim() || null,
                    group_id: document.getElementById('ns-group').value || null,
                });
                reload(d.message);
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        async function saveStudent(id) {
            const full_name = document.getElementById('sn-' + id).value.trim();
            if (full_name.length < 3) { toast("Ism juda qisqa.", false); return; }
            try {
                const d = await api(BASE + '/student/' + id + '/rename', {
                    full_name,
                    phone: document.getElementById('sp-' + id).value.trim() || null,
                });
                toast(d.message);
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        /** Guruh belgisini bosganda — qo'shadi yoki chiqaradi */
        async function toggleGroup(studentId, groupId, el) {
            const attach = el.checked;
            el.disabled = true;
            try {
                const d = await api(BASE + '/student/' + studentId + '/group', {
                    group_id: groupId,
                    action: attach ? 'attach' : 'detach',
                });
                el.parentElement.classList.toggle('text-sky-300', attach);
                el.parentElement.classList.toggle('text-slate-400', !attach);
                toast(d.message);
            } catch (e) {
                el.checked = !attach;
                if (e.message !== 'session') toast(e.message, false);
            } finally { el.disabled = false; }
        }

        async function blockStudentById(id, name) {
            if (!confirm(name + " bloklansinmi? Ro'yxatdan yo'qoladi.")) return;
            try {
                const d = await api(BASE + '/student/' + id + '/block', {});
                document.getElementById('sn-' + id)?.closest('.js-scard')?.remove();
                toast(d.message);
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        // ── Guruhlar ──────────────────────────────────────────
        async function createGroup() {
            const name = document.getElementById('ng-name').value.trim();
            if (name.length < 2) { toast("Guruh nomini yozing.", false); return; }
            try {
                const d = await api(BASE + '/group', { name, teacher_id: document.getElementById('ng-teacher').value || null });
                reload(d.message);
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        async function saveGroup(id) {
            const name = document.getElementById('gn-' + id).value.trim();
            if (name.length < 2) { toast("Guruh nomini yozing.", false); return; }
            try {
                const d = await api(BASE + '/group/' + id + '/rename', { name, teacher_id: document.getElementById('gt-' + id).value || null });
                reload(d.message);
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        async function deleteGroup(id, name) {
            if (!confirm('"' + name + '" o\'chirilsinmi?\n\nO\'quvchilari o\'chmaydi.')) return;
            try {
                const d = await api(BASE + '/group/' + id + '/delete', {});
                reload(d.message);
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        // ── Ustozlar ──────────────────────────────────────────
        async function createTeacher() {
            const full_name = document.getElementById('nt-name').value.trim();
            if (full_name.length < 3) { toast("F.I.O ni to'liq yozing.", false); return; }
            try {
                const d = await api(BASE + '/teacher', { full_name, phone: document.getElementById('nt-phone').value.trim() || null });
                reload(d.message);
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        async function saveTeacher(id) {
            const full_name = document.getElementById('tn-' + id).value.trim();
            if (full_name.length < 3) { toast("F.I.O ni to'liq yozing.", false); return; }
            try {
                const d = await api(BASE + '/teacher/' + id + '/update', { full_name, phone: document.getElementById('tp-' + id).value.trim() || null });
                reload(d.message);
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        async function resetCode(id, name) {
            if (!confirm(name + " uchun yangi kirish kodi berilsinmi?\n\nEski kod ishlamay qoladi.")) return;
            try {
                const d = await api(BASE + '/teacher/' + id + '/code', {});
                reload(d.message);
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        async function deleteTeacher(id, name) {
            if (!confirm(name + " o'chirilsinmi?")) return;
            try {
                const d = await api(BASE + '/teacher/' + id + '/delete', {});
                reload(d.message);
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        // ── Yuklanganda tiklash ───────────────────────────────
        (function restore() {
            const msg = sessionStorage.getItem('parvozToast');
            if (msg) { sessionStorage.removeItem('parvozToast'); toast(msg); }

            const tab = sessionStorage.getItem('parvozTab');
            if (tab && tab !== 'guruh' && document.getElementById('pane-' + tab)) showTab(tab);
        })();
    </script>
</body>

</html>
