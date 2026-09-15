<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ball qo'yish - Parvoz</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #082f49 50%, #0f172a 100%);
            min-height: 100vh;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .input-dark {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #fff;
        }

        .input-dark:focus {
            outline: none;
            border-color: #0ea5e9;
        }

        .input-dark::placeholder {
            color: rgba(148, 163, 184, 0.5);
        }

        .btn-primary {
            background: linear-gradient(135deg, #0ea5e9, #06b6d4);
        }

        #sheet {
            transition: transform .25s ease;
            transform: translateY(110%);
        }

        #sheet.open {
            transform: translateY(0);
        }

        #toast {
            transition: opacity .3s ease;
        }
    </style>
</head>

<body class="pb-40">
    <!-- Header -->
    <div class="sticky top-0 z-20 glass-card border-b border-white/10 px-4 py-3 flex items-center justify-between"
        style="background: rgba(15, 23, 42, 0.9);">
        <div>
            <p class="text-white font-bold leading-tight">👨‍🏫 {{ $teacher->full_name }}</p>
            <p class="text-slate-400 text-xs">Parvoz o'quv markazi</p>
        </div>
        <form method="POST" action="{{ route('parvoz.logout') }}">
            @csrf
            <button class="text-slate-400 text-sm bg-white/5 px-3 py-2 rounded-xl hover:text-white">Chiqish</button>
        </form>
    </div>

    <!-- Toast -->
    <div id="toast" class="fixed top-16 left-1/2 -translate-x-1/2 z-40 px-4 py-3 rounded-2xl text-sm font-semibold opacity-0 pointer-events-none max-w-[90vw] text-center"></div>

    <div class="max-w-lg mx-auto px-4 mt-4 space-y-4">

        @if(session('success'))
            <div class="rounded-2xl px-4 py-3 bg-emerald-500/15 border border-emerald-400/30 text-emerald-300 text-sm font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <!-- Qidiruv -->
        <input type="text" id="search" placeholder="🔍 O'quvchini qidirish..." autocomplete="off"
            class="input-dark w-full px-4 py-3 rounded-2xl text-sm">

        @php
            $sections = $groups->isEmpty() ? collect() : $groups->collect();
            if ($ungrouped->isNotEmpty()) {
                $sections->push((object) [
                    'name'     => $groups->isEmpty() ? "O'quvchilar" : "🆕 Yangi / guruhsiz",
                    'students' => $ungrouped,
                ]);
            }
        @endphp

        @foreach($sections as $section)
            @if($section->students->isNotEmpty())
                <div class="glass-card rounded-2xl overflow-hidden js-section">
                    <div class="px-4 py-3 border-b border-white/10 flex items-center justify-between">
                        <h3 class="font-bold text-sky-300 text-sm">👥 {{ $section->name }}</h3>
                        <span class="text-slate-500 text-xs font-semibold">{{ $section->students->count() }} ta</span>
                    </div>

                    @foreach($section->students as $student)
                        <div class="js-row border-b border-white/5 last:border-b-0 px-4 py-2.5 flex items-center gap-2"
                            data-id="{{ $student->id }}" data-phone="{{ $student->phone }}">
                            <button type="button" class="js-open flex-1 text-left min-w-0">
                                <p class="js-name text-white font-semibold text-sm truncate">{{ $student->full_name }}</p>
                                <p class="text-slate-500 text-xs truncate">📞 {{ $student->phone ?? '—' }} {{ $student->telegram_id ? '· ✅' : '· ⏳' }}</p>
                            </button>
                            <input type="text" inputmode="decimal" placeholder="9/10"
                                class="js-score input-dark w-20 px-2 py-2 rounded-xl text-center text-sm font-bold shrink-0">
                            <button type="button" class="js-save btn-primary px-4 py-2 rounded-xl font-bold text-white text-sm shrink-0">✓</button>
                        </div>
                    @endforeach
                </div>
            @endif
        @endforeach

        @if($sections->every(fn ($s) => $s->students->isEmpty()))
            <div class="glass-card rounded-2xl p-10 text-center">
                <p class="text-slate-400 text-sm">Sizga hali o'quvchi biriktirilmagan.<br>Administratorga murojaat qiling.</p>
            </div>
        @endif

        <!-- Oxirgi qo'yilgan ballar -->
        @if($lastGrades->isNotEmpty())
            <div class="glass-card rounded-2xl overflow-hidden">
                <div class="px-4 py-3 border-b border-white/10">
                    <h3 class="font-bold text-slate-300 text-sm">🕐 Oxirgi ballaringiz</h3>
                </div>
                @foreach($lastGrades as $grade)
                    <div class="px-4 py-2.5 border-b border-white/5 last:border-b-0 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-white text-sm truncate">{{ $grade->student?->full_name }}</p>
                            <p class="text-slate-500 text-xs">
                                {{ $grade->subject?->name ?? 'Umumiy' }} · {{ $grade->graded_at?->format('d.m H:i') }}
                            </p>
                        </div>
                        <span class="text-sky-300 font-bold text-sm shrink-0">⭐ {{ $grade->scoreLabel() }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Tanlangan o'quvchi oynasi (bitta umumiy) -->
    <div id="sheet" class="fixed bottom-0 left-1/2 -translate-x-1/2 z-30 w-full max-w-lg glass-card rounded-t-3xl p-5 space-y-3"
        style="background: rgba(15, 23, 42, 0.97);">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p id="sh-name" class="text-white font-bold truncate"></p>
                <p id="sh-phone" class="text-slate-400 text-sm font-mono"></p>
            </div>
            <button type="button" onclick="closeSheet()" class="text-slate-400 bg-white/5 px-3 py-1.5 rounded-xl text-sm shrink-0">✕</button>
        </div>

        <!-- Ball (fan/izoh bilan) -->
        <div class="flex items-center gap-2">
            <input type="text" id="sh-score" inputmode="decimal" placeholder="Ball: 9 yoki 9/10"
                class="input-dark flex-1 px-3 py-2.5 rounded-xl text-sm font-bold">
            <button type="button" onclick="sheetSave()" class="btn-primary px-5 py-2.5 rounded-xl font-bold text-white text-sm shrink-0">Saqlash</button>
        </div>
        @if($subjects->isNotEmpty())
            <select id="sh-subject" class="input-dark w-full px-3 py-2.5 rounded-xl text-sm">
                <option value="">📚 Fan (ixtiyoriy)</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                @endforeach
            </select>
        @endif
        <input type="text" id="sh-comment" maxlength="500" placeholder="💬 Izoh (ixtiyoriy)"
            class="input-dark w-full px-3 py-2.5 rounded-xl text-sm">

        <!-- Guruhga biriktirish -->
        @if($allGroups->isNotEmpty())
            <div class="flex items-center gap-2 pt-1 border-t border-white/10">
                <select id="sh-group" class="input-dark flex-1 px-3 py-2.5 rounded-xl text-sm">
                    <option value="">👥 Guruh tanlang...</option>
                    @foreach($allGroups as $g)
                        <option value="{{ $g->id }}">{{ $g->name }}{{ $g->teachers->isNotEmpty() ? ' — ' . $g->teachers->pluck('full_name')->join(', ') : '' }}</option>
                    @endforeach
                </select>
                <button type="button" onclick="assignGroup()"
                    class="text-xs font-semibold text-emerald-300 bg-emerald-500/10 border border-emerald-400/20 px-3 py-2.5 rounded-xl shrink-0">👥 Biriktirish</button>
            </div>
        @endif

        <!-- Ism tahrirlash / bloklash -->
        <div class="flex items-center gap-2 pt-1 border-t border-white/10">
            <input type="text" id="sh-rename" minlength="3" maxlength="100" placeholder="Yangi ism"
                class="input-dark flex-1 px-3 py-2.5 rounded-xl text-sm">
            <button type="button" onclick="renameStudent()"
                class="text-xs font-semibold text-sky-300 bg-sky-500/10 border border-sky-400/20 px-3 py-2.5 rounded-xl shrink-0">✏️ Saqlash</button>
            <button type="button" onclick="blockStudent()"
                class="text-xs font-semibold text-rose-300 bg-rose-500/10 border border-rose-400/20 px-3 py-2.5 rounded-xl shrink-0">🚫 Bloklash</button>
        </div>
    </div>

    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        const URLS = {
            grade: @json(route('parvoz.grade.store')),
            rename: id => @json(url('/parvoz/student')) + '/' + id + '/rename',
            block: id => @json(url('/parvoz/student')) + '/' + id + '/block',
            group: id => @json(url('/parvoz/student')) + '/' + id + '/group',
        };

        let current = null; // tanlangan qator (element)

        // ── Yordamchilar ──────────────────────────────────────────
        function toast(msg, ok = true) {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.className = t.className.replace(/opacity-\d+/, '');
            t.style.opacity = 1;
            t.style.background = ok ? 'rgba(16,185,129,.95)' : 'rgba(244,63,94,.95)';
            t.style.color = '#fff';
            clearTimeout(t._h);
            t._h = setTimeout(() => t.style.opacity = 0, 2600);
        }

        async function api(url, body) {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                },
                body: JSON.stringify(body),
            });

            if (res.redirected || res.status === 419 || res.status === 401) {
                toast("Sessiya tugadi — sahifani yangilang.", false);
                throw new Error('session');
            }

            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                throw new Error(data.message || "Xatolik yuz berdi.");
            }
            return data;
        }

        // ── Tez ball saqlash (qatordan) ──────────────────────────
        async function quickSave(row) {
            const input = row.querySelector('.js-score');
            const btn = row.querySelector('.js-save');
            const score = input.value.trim();
            if (!score) { input.focus(); return; }

            btn.disabled = true;
            btn.textContent = '…';
            try {
                const d = await api(URLS.grade, { student_id: row.dataset.id, score });
                toast(d.message);
                input.value = '';
                btn.textContent = '✓';
            } catch (e) {
                if (e.message !== 'session') toast(e.message, false);
                btn.textContent = '✓';
            } finally {
                btn.disabled = false;
            }
        }

        // ── Tanlangan o'quvchi oynasi ─────────────────────────────
        function openSheet(row) {
            current = row;
            document.getElementById('sh-name').textContent = row.querySelector('.js-name').textContent;
            document.getElementById('sh-phone').textContent = '📞 ' + (row.dataset.phone || '—');
            document.getElementById('sh-rename').value = row.querySelector('.js-name').textContent;
            document.getElementById('sh-score').value = '';
            document.getElementById('sh-comment').value = '';
            document.getElementById('sheet').classList.add('open');
        }

        function closeSheet() {
            document.getElementById('sheet').classList.remove('open');
            current = null;
        }

        async function sheetSave() {
            if (!current) return;
            const score = document.getElementById('sh-score').value.trim();
            if (!score) { document.getElementById('sh-score').focus(); return; }

            const subjectEl = document.getElementById('sh-subject');
            try {
                const d = await api(URLS.grade, {
                    student_id: current.dataset.id,
                    score,
                    subject_id: subjectEl ? (subjectEl.value || null) : null,
                    comment: document.getElementById('sh-comment').value.trim() || null,
                });
                toast(d.message);
                closeSheet();
            } catch (e) {
                if (e.message !== 'session') toast(e.message, false);
            }
        }

        async function renameStudent() {
            if (!current) return;
            const name = document.getElementById('sh-rename').value.trim();
            if (name.length < 3) { toast("Ism juda qisqa.", false); return; }

            try {
                const d = await api(URLS.rename(current.dataset.id), { full_name: name });
                current.querySelector('.js-name').textContent = d.full_name;
                document.getElementById('sh-name').textContent = d.full_name;
                toast(d.message);
            } catch (e) {
                if (e.message !== 'session') toast(e.message, false);
            }
        }

        async function assignGroup() {
            if (!current) return;
            const sel = document.getElementById('sh-group');
            if (!sel || !sel.value) { toast("Avval guruhni tanlang.", false); return; }

            try {
                const d = await api(URLS.group(current.dataset.id), { group_id: sel.value });
                toast(d.message);
            } catch (e) {
                if (e.message !== 'session') toast(e.message, false);
            }
        }

        async function blockStudent() {
            if (!current) return;
            const name = current.querySelector('.js-name').textContent;
            if (!confirm(name + " bloklansinmi? U panelda ko'rinmay qoladi.")) return;

            try {
                const d = await api(URLS.block(current.dataset.id), {});
                current.remove();
                closeSheet();
                toast(d.message);
            } catch (e) {
                if (e.message !== 'session') toast(e.message, false);
            }
        }

        // ── Qidiruv (klientda, engil) ─────────────────────────────
        document.getElementById('search').addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            document.querySelectorAll('.js-row').forEach(row => {
                const name = row.querySelector('.js-name').textContent.toLowerCase();
                const phone = (row.dataset.phone || '');
                row.style.display = (!q || name.includes(q) || phone.includes(q)) ? '' : 'none';
            });
            // Bo'sh bo'limlarni yashirish
            document.querySelectorAll('.js-section').forEach(sec => {
                const visible = [...sec.querySelectorAll('.js-row')].some(r => r.style.display !== 'none');
                sec.style.display = visible ? '' : 'none';
            });
        });

        // ── Hodisalar (delegatsiya — minglab tinglovchi o'rniga bitta) ──
        document.addEventListener('click', e => {
            const row = e.target.closest('.js-row');
            if (!row) return;
            if (e.target.closest('.js-save')) quickSave(row);
            else if (e.target.closest('.js-open')) openSheet(row);
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Enter' && e.target.classList.contains('js-score')) {
                quickSave(e.target.closest('.js-row'));
            }
            if (e.key === 'Escape') closeSheet();
        });
    </script>
</body>

</html>
