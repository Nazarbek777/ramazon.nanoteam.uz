<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ball qo'yish - Parvoz</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
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
    </style>
</head>

<body class="pb-16" x-data="{ search: '' }">
    <!-- Header -->
    <div class="sticky top-0 z-10 glass-card border-b border-white/10 px-4 py-3 flex items-center justify-between"
        style="background: rgba(15, 23, 42, 0.85);">
        <div>
            <p class="text-white font-bold leading-tight">👨‍🏫 {{ $teacher->full_name }}</p>
            <p class="text-slate-400 text-xs">Parvoz o'quv markazi</p>
        </div>
        <form method="POST" action="{{ route('parvoz.logout') }}">
            @csrf
            <button class="text-slate-400 text-sm bg-white/5 px-3 py-2 rounded-xl hover:text-white">Chiqish</button>
        </form>
    </div>

    <div class="max-w-lg mx-auto px-4 mt-4 space-y-4">

        @if(session('success'))
            <div class="rounded-2xl px-4 py-3 bg-emerald-500/15 border border-emerald-400/30 text-emerald-300 text-sm font-semibold">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-2xl px-4 py-3 bg-rose-500/15 border border-rose-400/30 text-rose-300 text-sm font-semibold">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Qidiruv -->
        <input type="text" x-model="search" placeholder="🔍 O'quvchini qidirish..."
            class="input-dark w-full px-4 py-3 rounded-2xl text-sm">

        @php
            $sections = $groups->isEmpty()
                ? collect([(object) ['name' => "O'quvchilar", 'students' => $ungrouped]])
                : $groups;
        @endphp

        @forelse($sections as $section)
            @if($section->students->isNotEmpty())
                <div class="glass-card rounded-2xl overflow-hidden">
                    <div class="px-4 py-3 border-b border-white/10">
                        <h3 class="font-bold text-sky-300 text-sm">👥 {{ $section->name }}</h3>
                    </div>

                    @foreach($section->students as $student)
                        <div class="border-b border-white/5 last:border-b-0"
                            x-data="{ open: false }"
                            x-show="search === '' || '{{ mb_strtolower($student->full_name) }}'.includes(search.toLowerCase())">
                            <form method="POST" action="{{ route('parvoz.grade.store') }}" class="px-4 py-3">
                                @csrf
                                <input type="hidden" name="student_id" value="{{ $student->id }}">

                                <div class="flex items-center gap-2">
                                    <button type="button" @click="open = !open" class="flex-1 text-left min-w-0">
                                        <p class="text-white font-semibold text-sm truncate">{{ $student->full_name }}</p>
                                        <p class="text-slate-500 text-xs">
                                            {{ $student->telegram_id ? '✅ botga ulangan' : '⏳ botga ulanmagan' }}
                                        </p>
                                    </button>

                                    <input type="text" name="score" inputmode="decimal" placeholder="9/10"
                                        class="input-dark w-20 px-2 py-2.5 rounded-xl text-center text-sm font-bold shrink-0">

                                    <button class="btn-primary px-4 py-2.5 rounded-xl font-bold text-white text-sm shrink-0">✓</button>
                                </div>

                                <!-- Qo'shimcha (ixtiyoriy): fan + izoh -->
                                <div x-show="open" x-cloak class="mt-3 space-y-2">
                                    @if($subjects->isNotEmpty())
                                        <select name="subject_id" class="input-dark w-full px-3 py-2.5 rounded-xl text-sm">
                                            <option value="">📚 Fan (ixtiyoriy)</option>
                                            @foreach($subjects as $subject)
                                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                    <input type="text" name="comment" maxlength="500" placeholder="💬 Izoh (ixtiyoriy)"
                                        class="input-dark w-full px-3 py-2.5 rounded-xl text-sm">
                                </div>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        @empty
        @endforelse

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
</body>

</html>
