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

        .card { background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1); }

        .inp {
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.15);
            color: #fff;
        }
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
        <div class="max-w-5xl mx-auto grid grid-cols-4 gap-2">
            <button type="button" id="tab-ball"  onclick="showTab('ball')"  class="tab-on  py-2.5 rounded-xl text-xs sm:text-sm font-bold">⭐ Ball</button>
            <button type="button" id="tab-guruh" onclick="showTab('guruh')" class="tab-off py-2.5 rounded-xl text-xs sm:text-sm font-bold">👥 Guruh</button>
            <button type="button" id="tab-oquv"  onclick="showTab('oquv')"  class="tab-off py-2.5 rounded-xl text-xs sm:text-sm font-bold">🎓 O'quvchi</button>
            <button type="button" id="tab-oqit"  onclick="showTab('oqit')"  class="tab-off py-2.5 rounded-xl text-xs sm:text-sm font-bold">🧑‍🏫 Ustoz</button>
        </div>
    </div>

    <div id="toast" class="fixed top-4 left-1/2 -translate-x-1/2 z-50 px-4 py-3 rounded-2xl text-sm font-bold opacity-0 pointer-events-none max-w-[90vw] text-center"></div>

    <div class="max-w-5xl mx-auto px-4 py-4">

        {{-- ════════════ 1-BO'LIM: BALL (faqat guruh ichida) ════════════ --}}
        <section id="pane-ball" class="space-y-3">

            @if($groups->isEmpty())
                <div class="card rounded-2xl p-8 text-center">
                    <p class="text-slate-300 font-semibold mb-1">Avval guruh yarating</p>
                    <p class="text-slate-500 text-sm">Ball faqat guruh ichidagi o'quvchiga qo'yiladi.</p>
                    <button type="button" onclick="showTab('guruh')" class="btn mt-4 px-5 py-2.5 rounded-xl font-bold text-sm">👥 Guruhlar bo'limi</button>
                </div>
            @else
                <p class="text-slate-400 text-xs px-1">Guruhni tanlang, ball yozing va <b class="text-sky-300">Saqlash</b> bosing. O'quvchiga darhol xabar boradi.</p>

                <select id="gfilter" onchange="applyFilter()" class="inp w-full px-4 py-3 rounded-2xl text-sm font-semibold">
                    @foreach($groups as $g)
                        <option value="g{{ $g->id }}" @selected(in_array($g->id, $myGroupIds) && $loop->first)>
                            👥 {{ $g->name }} ({{ $g->students->count() }} ta)
                        </option>
                    @endforeach
                </select>

                <input type="text" id="search" placeholder="🔍 Ism yoki telefon bo'yicha qidirish" autocomplete="off"
                    class="inp w-full px-4 py-3 rounded-2xl text-sm">

                @foreach($groups as $g)
                    <div class="card rounded-2xl overflow-hidden js-sec" data-gkey="g{{ $g->id }}">
                        <div class="px-4 py-3 bg-white/5 flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <p class="font-bold text-sky-300 text-sm truncate">👥 {{ $g->name }}</p>
                                <p class="text-slate-500 text-xs truncate">
                                    🧑‍🏫 {{ $g->teachers->isNotEmpty() ? $g->teachers->pluck('full_name')->join(', ') : 'o\'qituvchi tanlanmagan' }}
                                </p>
                            </div>
                            <button type="button" onclick="openMembers({{ $g->id }})"
                                class="text-sky-300 bg-sky-500/15 border border-sky-400/30 px-3 py-1.5 rounded-lg text-xs font-bold shrink-0">
                                ➕ O'quvchi
                            </button>
                        </div>

                        @forelse($g->students as $st)
                            @include('parvoz._row', ['st' => $st])
                        @empty
                            <div class="js-empty px-4 py-6 text-center">
                                <p class="text-slate-500 text-xs mb-3">Bu guruhda o'quvchi yo'q.</p>
                                <button type="button" onclick="openMembers({{ $g->id }})" class="btn px-4 py-2 rounded-xl font-bold text-xs">➕ O'quvchi qo'shish</button>
                            </div>
                        @endforelse
                    </div>
                @endforeach

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
            @endif
        </section>

        {{-- ════════════ 2-BO'LIM: GURUHLAR ════════════ --}}
        <section id="pane-guruh" x-hide>
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
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-bold text-white truncate">👥 {{ $g->name }}</p>
                            <span class="text-xs text-sky-300 bg-sky-500/15 px-2 py-0.5 rounded-lg shrink-0">{{ $g->students->count() }}</span>
                        </div>

                        <div>
                            <label class="block text-slate-500 text-xs mb-1">Guruh nomi</label>
                            <input type="text" id="gn-{{ $g->id }}" value="{{ $g->name }}" maxlength="100" class="inp w-full px-4 py-2.5 rounded-xl text-sm">
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

                        <button type="button" onclick="openMembers({{ $g->id }})"
                            class="w-full py-2.5 rounded-xl font-bold text-sm text-emerald-300 bg-emerald-500/15 border border-emerald-400/30">
                            👥 O'quvchilarini boshqarish
                        </button>

                        <div class="flex gap-2">
                            <button type="button" onclick="saveGroup({{ $g->id }})" class="btn flex-1 py-2.5 rounded-xl font-bold text-sm">💾 Saqlash</button>
                            <button type="button" onclick="deleteGroup({{ $g->id }}, @js($g->name))"
                                class="text-rose-300 bg-rose-500/15 border border-rose-400/30 px-3.5 py-2.5 rounded-xl font-bold text-sm shrink-0">🗑</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ════════════ 3-BO'LIM: O'QUVCHILAR (umumiy ro'yxat) ════════════ --}}
        <section id="pane-oquv" class="space-y-3" x-hide>

            <div class="card rounded-2xl p-4 space-y-3">
                <p class="font-bold text-sky-300 text-sm">➕ Yangi o'quvchi</p>
                <div class="grid gap-3 sm:grid-cols-3">
                    <input type="text" id="ns-name" maxlength="100" placeholder="Ism familiya" class="inp w-full px-4 py-2.5 rounded-xl text-sm sm:col-span-1">
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

            <input type="text" id="ssearch" placeholder="🔍 Qidirish" autocomplete="off" class="inp w-full px-4 py-3 rounded-2xl text-sm"
                oninput="filterStudents()">

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($allStudents as $st)
                    <div class="card rounded-2xl p-4 space-y-3 js-scard"
                        data-search="{{ mb_strtolower($st->full_name) }} {{ $st->phone }}">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-bold text-white truncate">🎓 {{ $st->full_name }}</p>
                            @if($st->telegram_id)
                                <span class="text-xs text-emerald-300 shrink-0" title="Botga ulangan">✅</span>
                            @else
                                <span class="text-xs text-slate-500 shrink-0" title="Botga ulanmagan">⏳</span>
                            @endif
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
                            <label class="block text-slate-500 text-xs mb-1">Guruhi</label>
                            <select id="sg-{{ $st->id }}" onchange="setGroup({{ $st->id }})" class="inp w-full px-4 py-2.5 rounded-xl text-sm">
                                <option value="">👥 Guruhsiz</option>
                                @foreach($groups as $g)
                                    <option value="{{ $g->id }}" @selected($st->parvoz_group_id === $g->id)>{{ $g->name }}</option>
                                @endforeach
                            </select>
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

        {{-- ════════════ 4-BO'LIM: O'QITUVCHILAR ════════════ --}}
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

    {{-- ════════════ GURUH A'ZOLARI OYNASI ════════════ --}}
    <div id="members" class="fixed inset-0 z-40 bg-black/70 p-4 flex items-end sm:items-center justify-center" x-hide
        onclick="if(event.target===this) closeMembers()">
        <div class="bg-slate-900 border border-white/10 rounded-3xl p-5 w-full max-w-md space-y-3 max-h-[90vh] overflow-y-auto">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-white font-bold">👥 O'quvchilar</p>
                    <p id="mb-group" class="text-sky-300 text-sm truncate"></p>
                </div>
                <button type="button" onclick="closeMembers()" class="text-slate-400 bg-white/10 px-3 py-1.5 rounded-xl text-sm shrink-0">✕</button>
            </div>

            <!-- Mavjud o'quvchidan tanlash -->
            <div class="border-t border-white/10 pt-3 space-y-2">
                <p class="text-slate-400 text-xs font-semibold">Mavjud o'quvchini qo'shish</p>
                <select id="mb-pick" class="inp w-full px-4 py-2.5 rounded-xl text-sm"></select>
                <button type="button" onclick="addExisting()" class="w-full py-2.5 rounded-xl font-bold text-sm text-emerald-300 bg-emerald-500/15 border border-emerald-400/30">Guruhga qo'shish</button>
            </div>

            <!-- Yangi yaratish -->
            <div class="border-t border-white/10 pt-3 space-y-2">
                <p class="text-slate-400 text-xs font-semibold">Yoki yangi o'quvchi yaratish</p>
                <input type="text" id="mb-name" maxlength="100" placeholder="Ism familiya" class="inp w-full px-4 py-2.5 rounded-xl text-sm">
                <input type="text" id="mb-phone" inputmode="tel" maxlength="30" placeholder="+998 90 123 45 67" class="inp w-full px-4 py-2.5 rounded-xl text-sm">
                <button type="button" onclick="addNew()" class="btn w-full py-2.5 rounded-xl font-bold text-sm">Yaratib qo'shish</button>
            </div>

            <!-- Guruhdagilar -->
            <div class="border-t border-white/10 pt-3">
                <p class="text-slate-400 text-xs font-semibold mb-2">Guruhdagilar</p>
                <div id="mb-list" class="space-y-2"></div>
            </div>
        </div>
    </div>

    {{-- ════════════ O'QUVCHI (ball tafsiloti) OYNASI ════════════ --}}
    <div id="modal" class="fixed inset-0 z-40 bg-black/70 p-4 flex items-end sm:items-center justify-center" x-hide
        onclick="if(event.target===this) closeModal()">
        <div class="bg-slate-900 border border-white/10 rounded-3xl p-5 w-full max-w-md space-y-3 max-h-[90vh] overflow-y-auto">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p id="m-name" class="text-white font-bold truncate"></p>
                    <p id="m-phone" class="text-slate-400 text-sm font-mono"></p>
                </div>
                <button type="button" onclick="closeModal()" class="text-slate-400 bg-white/10 px-3 py-1.5 rounded-xl text-sm shrink-0">✕</button>
            </div>

            <div class="border-t border-white/10 pt-3 space-y-2">
                <p class="text-slate-400 text-xs font-semibold">⭐ Ball (fan va izoh bilan)</p>
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
        </div>
    </div>

    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        const BASE = @json(url('/parvoz'));
        const STUDENTS = @json($allStudents->map(fn ($s) => ['id' => $s->id, 'name' => $s->full_name, 'phone' => $s->phone, 'group' => $s->parvoz_group_id])->values());
        const GROUPS = @json($groups->map(fn ($g) => ['id' => $g->id, 'name' => $g->name])->values());

        let current = null;
        let mbGroup = null;

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

        function showTab(name) {
            ['ball', 'guruh', 'oquv', 'oqit'].forEach(k => {
                document.getElementById('pane-' + k).toggleAttribute('x-hide', k !== name);
                const b = document.getElementById('tab-' + k);
                b.className = b.className.replace(/tab-(on|off)/, k === name ? 'tab-on' : 'tab-off');
            });
            window.scrollTo(0, 0);
        }

        // ── Ball bo'limi filtri ───────────────────────────────
        function applyFilter() {
            const sel = document.getElementById('gfilter');
            if (!sel) return;
            const f = sel.value;
            const q = (document.getElementById('search')?.value || '').trim().toLowerCase();

            document.querySelectorAll('.js-sec').forEach(sec => {
                let any = false;
                sec.querySelectorAll('.js-row').forEach(row => {
                    const ok = !q || row.dataset.search.includes(q);
                    row.style.display = ok ? '' : 'none';
                    if (ok) any = true;
                });
                const ph = sec.querySelector('.js-empty');
                if (ph) ph.style.display = q ? 'none' : '';
                sec.style.display = (sec.dataset.gkey === f && (any || (!q && ph))) ? '' : 'none';
            });
        }
        document.getElementById('search')?.addEventListener('input', applyFilter);

        // ── O'quvchilar bo'limi qidiruvi ──────────────────────
        function filterStudents() {
            const q = document.getElementById('ssearch').value.trim().toLowerCase();
            document.querySelectorAll('.js-scard').forEach(c => {
                c.style.display = (!q || c.dataset.search.includes(q)) ? '' : 'none';
            });
        }

        // ── Ball qo'yish ──────────────────────────────────────
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

        function openModal(row) {
            current = row;
            document.getElementById('m-name').textContent = row.querySelector('.js-name').textContent;
            document.getElementById('m-phone').textContent = '📞 ' + (row.dataset.phone || '—');
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

        // ── Guruh a'zolari oynasi ─────────────────────────────
        function openMembers(groupId) {
            mbGroup = groupId;
            const g = GROUPS.find(x => x.id === groupId);
            document.getElementById('mb-group').textContent = g ? g.name : '';
            document.getElementById('mb-name').value = '';
            document.getElementById('mb-phone').value = '';

            // Boshqa guruhdagi/guruhsiz o'quvchilar ro'yxati
            const pick = document.getElementById('mb-pick');
            const free = STUDENTS.filter(s => s.group !== groupId);
            pick.innerHTML = free.length
                ? '<option value="">O\'quvchini tanlang...</option>' + free.map(s => {
                    const gg = s.group ? (GROUPS.find(x => x.id === s.group)?.name || '') : 'guruhsiz';
                    return `<option value="${s.id}">${s.name} — ${gg}</option>`;
                }).join('')
                : '<option value="">Qo\'shiladigan o\'quvchi yo\'q</option>';

            // Guruhdagilar
            const list = document.getElementById('mb-list');
            const mine = STUDENTS.filter(s => s.group === groupId);
            list.innerHTML = mine.length
                ? mine.map(s => `
                    <div class="flex items-center justify-between gap-2 bg-white/5 rounded-xl px-3 py-2">
                        <div class="min-w-0">
                            <p class="text-white text-sm truncate">${s.name}</p>
                            <p class="text-slate-500 text-xs truncate">${s.phone || '—'}</p>
                        </div>
                        <button type="button" onclick="removeFromGroup(${s.id})"
                            class="text-rose-300 bg-rose-500/15 px-2.5 py-1.5 rounded-lg text-xs shrink-0">Chiqarish</button>
                    </div>`).join('')
                : '<p class="text-slate-500 text-xs text-center py-2">Hali hech kim yo\'q.</p>';

            document.getElementById('members').removeAttribute('x-hide');
        }

        function closeMembers() { document.getElementById('members').setAttribute('x-hide', ''); }

        async function addExisting() {
            const id = document.getElementById('mb-pick').value;
            if (!id) { toast("O'quvchini tanlang.", false); return; }
            try {
                const d = await api(BASE + '/student/' + id + '/group', { group_id: mbGroup });
                toast(d.message); reload();
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        async function addNew() {
            const full_name = document.getElementById('mb-name').value.trim();
            if (full_name.length < 3) { toast("Ism familiyani to'liq yozing.", false); return; }
            try {
                const d = await api(BASE + '/student', {
                    full_name,
                    phone: document.getElementById('mb-phone').value.trim() || null,
                    group_id: mbGroup,
                });
                toast(d.message); reload();
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        async function removeFromGroup(id) {
            try {
                const d = await api(BASE + '/student/' + id + '/group', { group_id: null });
                toast(d.message); reload();
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        // ── O'quvchilar bo'limi ───────────────────────────────
        async function createStudent() {
            const full_name = document.getElementById('ns-name').value.trim();
            if (full_name.length < 3) { toast("Ism familiyani to'liq yozing.", false); return; }
            try {
                const d = await api(BASE + '/student', {
                    full_name,
                    phone: document.getElementById('ns-phone').value.trim() || null,
                    group_id: document.getElementById('ns-group').value || null,
                });
                toast(d.message); reload();
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

        async function setGroup(id) {
            try {
                const d = await api(BASE + '/student/' + id + '/group', { group_id: document.getElementById('sg-' + id).value || null });
                toast(d.message); reload();
            } catch (e) { if (e.message !== 'session') toast(e.message, false); }
        }

        async function blockStudentById(id, name) {
            if (!confirm(name + " bloklansinmi? Ro'yxatdan yo'qoladi.")) return;
            try {
                const d = await api(BASE + '/student/' + id + '/block', {});
                toast(d.message); reload();
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
                const d = await api(BASE + '/teacher/' + id + '/update', { full_name, phone: document.getElementById('tp-' + id).value.trim() || null });
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
            if (e.key === 'Escape') { closeModal(); closeMembers(); }
        });

        applyFilter();
    </script>
</body>

</html>
