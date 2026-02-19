@extends('layouts.app')
@section('title', 'Bosh sahifa')

@section('content')
@php
    $totalToday = count($habits) + 6;
    $completedToday = 0;
    if ($todayLog) {
        $completedToday = $todayLog->items->where('is_completed', true)->count();
        $nD = $todayLog->data['namoz'] ?? [];
        foreach (['fajr', 'dhuhr', 'asr', 'maghrib', 'isha', 'roza'] as $k) {
            if ($nD[$k] ?? false) $completedToday++;
        }
    }
    $treePercent = $totalToday > 0 ? round(($completedToday / $totalToday) * 100) : 0;
    
    // Stages: 0: Seed, 1: Sprout, 2: Young, 3: Medium, 4: Big, 5: Paradise
    $stage = 0;
    if ($treePercent >= 100) $stage = 5;
    elseif ($treePercent >= 80) $stage = 4;
    elseif ($treePercent >= 50) $stage = 3;
    elseif ($treePercent >= 25) $stage = 2;
    elseif ($treePercent >= 5) $stage = 1;

    $stageNames = ['Urug\'', 'Nihol', 'Yosh daraxt', 'O\'rta daraxt', 'Katta daraxt', 'Jannat daraxti'];
    $stageEmojis = ['🌱', '🌿', '🌳', '🌲', '🌴', '🌸'];
@endphp

{{-- Ramazon banner --}}
@if($ramadan['is_ramadan'])
    <div style="text-align:center;margin-bottom:14px;">
        <span class="ramadan-badge">
            <i class="ri-moon-clear-fill"></i> Ramazon {{ $ramadan['day'] }}-kuni · Qoldi {{ $ramadan['remaining'] }} kun
        </span>
    </div>
@elseif($ramadan['days_until'])
    <div style="text-align:center;margin-bottom:14px;">
        <span class="ramadan-badge">
            <i class="ri-moon-clear-line"></i> Ramazongacha {{ $ramadan['days_until'] }} kun
        </span>
    </div>
@endif

{{-- 🔔 REMINDERS & ENCOURAGEMENT --}}
<div id="reminderContainer" style="margin-bottom:20px; display:none;">
    <div class="card reminder-banner" id="reminderBanner" onclick="handleReminderClick()" style="display:flex; align-items:flex-start; gap:16px; padding:20px; background:linear-gradient(135deg, rgba(192,132,252,0.1), rgba(212,168,67,0.05)); border:1px solid var(--accent-glow); position:relative; overflow:hidden; cursor:pointer;">
        <div class="reminder-glow"></div>
        <div class="reminder-icon" style="font-size:1.6rem; color:var(--gold); display:flex; align-items:center; justify-content:center; width:48px; height:48px; min-width:48px; background:var(--gold-bg); border-radius:14px; box-shadow:0 8px 16px rgba(0,0,0,0.2);">
            <i class="ri-notification-3-line"></i>
        </div>
        <div style="flex:1; padding-top:4px;">
            <div id="reminderTitle" style="font-size:1.05rem; font-weight:800; color:var(--text-primary); margin-bottom:6px; line-height:1.2;">Bugungi amalni bajardingizmi?</div>
            <div id="reminderText" style="font-size:0.85rem; color:var(--text-secondary); line-height:1.5;">Namoz vaqtlarini o'tkazib yubormang, har bir amalni o'z vaqtida bajaring!</div>
        </div>
        <i class="ri-close-line close-btn" onclick="event.stopPropagation(); closeReminder()" style="cursor:pointer; color:var(--text-muted); position:absolute; top:12px; right:12px; font-size:1.2rem; z-index:10;"></i>
    </div>
</div>

{{-- ⚡ TEZKOR NAVIGATSIYA --}}
<div class="quick-nav">
    <a href="#saharlik-iftorlik" class="quick-nav-btn">
        <i class="ri-restaurant-line"></i>
        <span>Saharlik / Iftorlik</span>
    </a>
    <a href="#duas-section" class="quick-nav-btn">
        <i class="ri-book-read-line"></i>
        <span>Duolar</span>
    </a>
    <a href="#prayer-section" class="quick-nav-btn">
        <i class="ri-time-line"></i>
        <span>Namoz</span>
    </a>
    <a href="{{ route('daily.show') }}" class="quick-nav-btn qn-active">
        <i class="ri-checkbox-circle-line"></i>
        <span>Amallar</span>
    </a>
</div>

{{-- 🌳 DARAXT --}}
<div class="tree-card">
    <div class="tree-scene">
        {{-- ... tree scene content as before ... --}}
        <div class="tree-stars">
            <span style="top:8%;left:12%;animation-delay:0s;"></span>
            <span style="top:15%;right:18%;animation-delay:0.7s;"></span>
            <span style="top:5%;left:45%;animation-delay:1.4s;"></span>
            <span style="top:22%;left:75%;animation-delay:2.1s;"></span>
            <span style="top:10%;right:8%;animation-delay:0.3s;"></span>
        </div>
        <div class="tree-wrapper">
            @if($stage === 0)
                <div class="tree-seed"><div class="seed-body"></div><div class="seed-shine"></div></div>
            @elseif($stage === 1)
                <div class="tree-sprout"><div class="sprout-stem"></div><div class="sprout-leaf sprout-leaf-l"></div><div class="sprout-leaf sprout-leaf-r"></div></div>
            @elseif($stage === 2)
                <div class="tree-young"><div class="young-trunk"></div><div class="young-crown"></div><div class="young-crown-light"></div></div>
            @elseif($stage === 3)
                <div class="tree-medium"><div class="med-trunk"></div><div class="med-branch med-branch-l"></div><div class="med-branch med-branch-r"></div><div class="med-crown"></div><div class="med-crown-light"></div><div class="med-crown-top"></div></div>
            @elseif($stage === 4)
                <div class="tree-big"><div class="big-trunk"></div><div class="big-branch big-branch-l"></div><div class="big-branch big-branch-r"></div><div class="big-crown"></div><div class="big-crown-2"></div><div class="big-crown-3"></div><div class="big-crown-top"></div></div>
            @else
                <div class="tree-paradise">
                    <div class="para-trunk"></div><div class="para-branch para-branch-l"></div><div class="para-branch para-branch-r"></div>
                    <div class="para-crown"></div><div class="para-crown-2"></div><div class="para-crown-3"></div><div class="para-crown-top"></div><div class="para-glow"></div>
                    <span class="para-flower" style="top:18%;left:22%;"></span>
                    <span class="para-flower" style="top:10%;left:48%;animation-delay:0.5s;"></span>
                    <span class="para-flower" style="top:22%;right:20%;animation-delay:1s;"></span>
                    <span class="para-flower" style="top:35%;left:30%;animation-delay:1.5s;"></span>
                    <span class="para-flower" style="top:28%;right:30%;animation-delay:0.8s;"></span>
                    <span class="para-particle" style="top:5%;left:20%;animation-delay:0s;"></span>
                    <span class="para-particle" style="top:0%;left:55%;animation-delay:1s;"></span>
                    <span class="para-particle" style="top:10%;right:15%;animation-delay:2s;"></span>
                </div>
            @endif
        </div>
        <div class="tree-ground"></div>
    </div>
    <div class="tree-info">
        <div class="tree-stage-name">{{ $stageEmojis[$stage] }} {{ $stageNames[$stage] }}</div>
        <div class="tree-progress-row">
            <div class="tree-progress-bar"><div class="tree-progress-fill" style="width:{{ $treePercent }}%"></div></div>
            <span class="tree-percent">{{ $treePercent }}%</span>
        </div>
        <div id="quick-focus-anchor"></div>
        {{-- QUICK FOCUS SECTION --}}
        <div class="today-focus" id="todayFocusArea" style="margin-top:20px; text-align:center;">
            {{-- Fasting Status --}}
            <div class="fasting-status {{ ($namozData['roza'] ?? false) ? 'active' : '' }}" 
                 onclick="toggleDashboardNamoz('roza', this)"
                 style="display:inline-flex; align-items:center; gap:8px; padding:6px 16px; border-radius:50px; background:rgba(212,168,67,0.1); border:1px solid rgba(212,168,67,0.3); color:var(--gold); font-size:0.85rem; font-weight:700; cursor:pointer; transition:all 0.3s; margin-bottom:15px;">
                <i class="ri-moon-cloudy-line" style="font-size:1.1rem;"></i>
                <span>{{ ($namozData['roza'] ?? false) ? "Bugun ro'zadorman" : "Bugun ro'za tutdingizmi?" }}</span>
            </div>

            <div style="font-size:0.8rem; color:var(--gold); opacity:0.8; margin-top:15px; margin-bottom:10px; font-weight:700;">
                Bugun o'qigan namozlaringizni belgilang
            </div>
            <div style="display:flex; justify-content:center; gap:8px; padding:10px 0; margin-top:5px;">
                @php
                    $prayers = [
                        'fajr' => ['name' => 'Bomdod', 'icon' => 'ri-sun-foggy-line'],
                        'dhuhr' => ['name' => 'Peshin', 'icon' => 'ri-sun-line'],
                        'asr' => ['name' => 'Asr', 'icon' => 'ri-sun-cloudy-line'],
                        'maghrib' => ['name' => 'Shom', 'icon' => 'ri-moon-line'],
                        'isha' => ['name' => 'Xufton', 'icon' => 'ri-moon-clear-line']
                    ];
                @endphp
                @foreach($prayers as $id => $info)
                <div class="prayer-lantern {{ ($namozData[$id] ?? false) ? 'active' : '' }}" 
                     onclick="toggleDashboardNamoz('{{ $id }}', this)">
                    <div class="lantern-wire"></div>
                    <div class="lantern-box">
                        <span class="lantern-light"></span>
                        <div class="lantern-content">
                            <i class="{{ $info['icon'] }}"></i>
                            <div class="lantern-name">{{ $info['name'] }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="tree-motivation" style="margin-top:15px;">
            @if($treePercent === 0)
                <p>✨ Bugun birinchi amaldan boshlang, {{ explode(' ', Auth::user()->name)[0] }}!</p>
            @elseif($treePercent < 100)
                <p>🔥 Zo'r! Daraxtingiz o'sib bormoqda, {{ explode(' ', Auth::user()->name)[0] }}!</p>
            @else
                <p>🌸 Barcha amallar bajarildi!</p>
            @endif
        </div>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-top:10px;">
        <a href="{{ route('daily.show') }}" class="btn btn-gold" style="width:100%;">
            <i class="ri-list-check"></i> Amallar
        </a>
        <button onclick="toggleHelp()" class="btn btn-outline" style="width:100%; border-color:var(--text-muted); color:var(--text-muted);">
            <i class="ri-question-line"></i> Yo'riqnoma
        </button>
    </div>
</div>

{{-- 💡 HOW TO USE GUIDE (Help Card) --}}
<div id="helpCard" class="card" style="display:none; margin-bottom:24px; background:linear-gradient(135deg, rgba(212,168,67,0.1), rgba(0,0,0,0.5)); border:1px solid var(--gold-border);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <h4 style="color:var(--gold); margin:0;"><i class="ri-lightbulb-line"></i> Qanday foydalanish kerak?</h4>
        <i class="ri-close-line" onclick="toggleHelp()" style="cursor:pointer; color:var(--text-muted);"></i>
    </div>
    <div style="font-size:0.85rem; line-height:1.6; color:var(--text-secondary);">
        <p>1. <b>Namozlarni belgilang</b> — Daraxt ostidagi harflarni bosing. Har bir namoz daraxtingizni o'stiradi.</p>
        <p>2. <b>Amallar bo'limiga o'ting</b> — U yerda Qur'on o'qish, zikr va boshqa foydali amallar bor.</p>
        <p>3. <b>Maqsad qo'ying</b> — "Maqsadlar" bo'limida Ramazon uchun o'z rejalaringizni belgilang.</p>
        <p>4. <b>Daraxtni kuzating</b> — Kunlik amallaringizga qarab daraxtingiz niholdan katta jannat daraxtigacha o'sadi.</p>
    </div>
</div>

{{-- 🕐 SAHARLIK / IFTORLIK --}}
<div id="saharlik-iftorlik" style="margin-bottom:24px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;padding:0 8px;">
        <button onclick="PrayerTimes.changeDate(-1)" style="background:var(--white-5);border:none;color:var(--gold);width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;"><i class="ri-arrow-left-s-line"></i></button>
        <div id="topDateDisplay" style="font-size:0.85rem;font-weight:700;color:var(--text-primary);text-align:center;">
            {{ now()->translatedFormat('j-F, l') }}
        </div>
        <button onclick="PrayerTimes.changeDate(1)" style="background:var(--white-5);border:none;color:var(--gold);width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;"><i class="ri-arrow-right-s-line"></i></button>
    </div>
    <div class="suhoor-iftar-grid" id="suhoorIftarWidget">
        <div class="si-card si-suhoor">
            <div class="si-icon"><i class="ri-sun-line"></i></div>
            <div class="si-label">Saharlik</div>
            <div class="si-time" id="suhoorTime">--:--</div>
            <div class="si-sub">Og'iz yopish</div>
        </div>
        <div class="si-card si-iftar">
            <div class="si-icon"><i class="ri-moon-line"></i></div>
            <div class="si-label">Iftorlik</div>
            <div class="si-time" id="iftarTime">--:--</div>
            <div class="si-sub">Og'iz ochish</div>
        </div>
    </div>
</div>

{{-- 📖 RO'ZA DUOLARI --}}
<div id="duas-section">
    <h3 class="section-title"><i class="ri-book-read-line"></i> Ro'za duolari</h3>

    {{-- Og'iz yopish duosi --}}
    <div class="card dua-card mb-24">
        <div class="dua-header">
            <div class="dua-header-icon" style="background:linear-gradient(135deg,rgba(91,140,255,0.15),rgba(91,140,255,0.05));">
                <i class="ri-sun-line" style="color:#7da1ff;"></i>
            </div>
            <div>
                <div class="dua-title">Saharlik duosi</div>
                <div class="dua-subtitle">Og'iz yopish (niyat) — tongda</div>
            </div>
        </div>
        <div class="dua-body">
            <p class="dua-arabic">نَوَيْتُ أَنْ أَصُومَ صَوْمَ شَهْرِ رَمَضَانَ مِنَ الْفَجْرِ إِلَى الْمَغْرِبِ، خَالِصًا لِلَّهِ تَعَالَى أَللهُ أَكْبَرُ</p>
            <p class="dua-translit"><strong>Navaytu an asuvma sovma shahri ramazona minal fajri ilal mag'ribi, xolisan lillaxi ta'aalaa Alloxu akbar.</strong></p>
            <p class="dua-meaning">Ramazon oyining ro'zasini subxdan to kun botguncha tutmoqni niyat qildim. Xolis Alloh uchun Alloh buyukdir.</p>
        </div>
    </div>

    {{-- Og'iz ochish duosi --}}
    <div class="card dua-card mb-24">
        <div class="dua-header">
            <div class="dua-header-icon" style="background:linear-gradient(135deg,rgba(212,168,67,0.15),rgba(212,168,67,0.05));">
                <i class="ri-moon-line" style="color:var(--gold);"></i>
            </div>
            <div>
                <div class="dua-title">Iftorlik duosi</div>
                <div class="dua-subtitle">Og'iz ochish — kechqurun</div>
            </div>
        </div>
        <div class="dua-body">
            <p class="dua-arabic">اللَّهُمَّ لَكَ صُمْتُ وَ بِكَ آمَنْتُ وَ عَلَيْكَ تَوَكَّلْتُ وَ عَلَى رِزْقِكَ أَفْطَرْتُ، فَاغْفِرْلِي مَا قَدَّمْتُ وَ مَا أَخَّرْتُ بِرَحْمَتِكَ يَا أَرْحَمَ الرَّاحِمِينَ</p>
            <p class="dua-translit"><strong>Allohumma laka sumtu va bika aamantu va a'layka tavakkaltu va a'laa rizqika aftartu, fag'firliy ma qoddamtu va maa axxortu biroxmatika yaa arhamar roohimiyn.</strong></p>
            <p class="dua-meaning">Ey Alloh, ushbu Ro'zamni Sen uchun tutdim va Senga iymon keltirdim va Senga tavakkal qildim va bergan rizqing bilan iftor qildim. Ey mehribonlarning eng mehriboni, mening avvalgi va keyingi gunohlarimni mag'firat qilgil.</p>
        </div>
    </div>

    {{-- Iftorlik uchun qo'shimcha duo --}}
    <div class="card dua-card mb-24">
        <div class="dua-header">
            <div class="dua-header-icon" style="background:linear-gradient(135deg,rgba(110,200,120,0.15),rgba(110,200,120,0.05));">
                <i class="ri-heart-pulse-line" style="color:var(--accent-light);"></i>
            </div>
            <div>
                <div class="dua-title">Og'iz ochish vaqtidagi duo</div>
                <div class="dua-subtitle">Tashnalik ketgach aytiladi</div>
            </div>
        </div>
        <div class="dua-body">
            <p class="dua-arabic">ذَهَبَ الظَّمَأُ وَابتَلَّتِ العُرُوقُ وَثَبَتَ الأَجرُ إِن شَاءَ اللّهُ</p>
            <p class="dua-translit"><strong>Zahabaz zama'u vabtallatil 'uruqu va sabatal ajru insha Alloh.</strong></p>
            <p class="dua-meaning">Tashnalik ketdi, tomirlar namlandi va savob sobit bo'ldi, inshaAlloh.</p>
        </div>
    </div>
</div>

{{-- 🕌 NAMOZ VAQTLARI --}}
<div id="prayer-section">
    <h3 class="section-title"><i class="ri-time-line"></i> Namoz vaqtlari</h3>
    <div class="card mb-24">
        <div id="prayerTimesWidget">
            <div style="text-align:center;padding:14px;">
                <i class="ri-loader-4-line" style="font-size:1.2rem;color:var(--gold);animation:spin 1s linear infinite;"></i>
                <div style="font-size:0.78rem;color:var(--text-muted);margin-top:4px;">Yuklanmoqda...</div>
            </div>
        </div>
    </div>
</div>

    {{-- 📢 FEEDBACK SECTION --}}
    <div class="feedback-section" style="margin-top:40px; margin-bottom:100px; padding:0 10px;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:15px;">
            <h3 style="font-size:1.1rem; color:var(--gold); margin:0;"><i class="ri-message-3-line"></i> Fikringizni yozing</h3>
            <a href="{{ route('feedback.index') }}" style="font-size:0.8rem; color:var(--accent); text-decoration:none;">
                Jamoatchilik fikri <i class="ri-arrow-right-s-line"></i>
            </a>
        </div>
        <div class="card" style="padding:20px; border:1px dashed var(--white-15); background:rgba(255,255,255,0.02);">
            <form action="{{ route('feedback.store') }}" method="POST">
                @csrf
                <textarea name="content" placeholder="Ilova haqida fikringiz yoki taklifingiz..." style="width:100%; min-height:80px; background:rgba(255,255,255,0.03); border:1px solid var(--white-10); border-radius:12px; padding:12px; color:var(--text-primary); font-family:inherit; resize:none; margin-bottom:12px;" required></textarea>
                <div style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:0.8rem; color:var(--text-muted);">
                        <input type="checkbox" name="is_public" value="1" style="width:16px; height:16px; accent-color:var(--gold);">
                        Ommaga ko'rsatilsin (anonim)
                    </label>
                    <button type="submit" class="btn btn-gold btn-sm">
                        <i class="ri-send-plane-fill"></i> Yuborish
                    </button>
                </div>
            </form>
        </div>
        <p style="text-align:center; font-size:0.75rem; color:var(--text-muted); margin-top:12px; opacity:0.8;">
            <i class="ri-shield-user-line"></i> Bildirilgan fikrlar mutlaqo anonim – kim yozganini hech kim ko'ra olmaydi.
        </p>
    </div>

<div style="text-align:center;padding:16px 10px;margin-top:20px;border-top:1px solid var(--white-5);">
    <p style="font-size:0.7rem;color:var(--text-muted);margin:0;">Namoz vaqtlari islom.uz saytidan olindi</p>
</div>
@endsection

@section('scripts')
<script>
    const TOGGLE_URL = "{{ route('daily.toggle') }}";
    const CSRF = "{{ csrf_token() }}";
    const DATE = "{{ $today->format('Y-m-d') }}";
    const USER_NAME = "{{ explode(' ', Auth::user()->name)[0] }}";
    let namozData = {!! json_encode($namozData) !!};

    // Smooth scroll
    document.querySelectorAll('.quick-nav-btn[href^="#"]').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            const target = document.querySelector(btn.getAttribute('href'));
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    function toggleHelp() {
        const card = document.getElementById('helpCard');
        card.style.display = card.style.display === 'none' ? 'block' : 'none';
        if (card.style.display === 'block') card.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function toggleDashboardNamoz(id, el) {
        if (el.classList.contains('loading')) return;
        const isDone = !el.classList.contains('active');
        el.classList.add('loading');
        
        fetch(TOGGLE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ key: 'namoz.' + id, value: isDone, date: DATE, type: 'data_field' })
        })
        .then(r => r.json())
        .then(data => {
            el.classList.remove('loading');
            if (data.success) {
                el.classList.toggle('active', isDone);
                namozData[id] = isDone;
                
                // Update Ro'za text if applicable
                if (id === 'roza') {
                    const span = el.querySelector('span');
                    if (span) span.textContent = isDone ? "Bugun ro'zadorman" : "Bugun ro'za tutdingizmi?";
                }
                
                if (typeof checkReminders === 'function') checkReminders();
                
                // Update tree progress
                const fill = document.querySelector('.tree-progress-fill');
                const pText = document.querySelector('.tree-percent');
                const pNav = document.querySelector('.tree-percent-nav');
                const motivation = document.querySelector('.tree-motivation p');
                
                if (fill && data.percent !== undefined) fill.style.width = data.percent + '%';
                if (pText && data.percent !== undefined) pText.textContent = data.percent + '%';
                if (pNav && data.percent !== undefined) pNav.textContent = data.percent + '%';
                
                if (motivation && data.percent !== undefined) {
                    if (data.percent === 100) motivation.innerHTML = `🌸 Barcha amallar bajarildi!`;
                    else if (data.percent > 0) motivation.innerHTML = `🔥 Zo'r! Daraxtingiz o'sib bormoqda, ${USER_NAME}!`;
                    else motivation.innerHTML = `✨ Bugun birinchi amaldan boshlang, ${USER_NAME}!`;
                }

                showToast(data.percent === 100 ? `🌸 Barcha amallar bajarildi!` : 'Saqlandi');
            }
        });
    }

    function checkReminders() {
        const container = document.getElementById('reminderContainer');
        const title = document.getElementById('reminderTitle');
        const text = document.getElementById('reminderText');
        
        if (typeof PrayerTimes === 'undefined') return;

        PrayerTimes.getLocation().then(async loc => {
            const times = await PrayerTimes.getTimesForDate(loc.apiRegion, new Date());
            if (!times) return;

            const now = new Date();
            const nowMin = now.getHours() * 60 + now.getMinutes();

            const prayerOrder = [
                {id: 'fajr', name: 'Bomdod', time: times.Fajr},
                {id: null, name: 'Quyosh', time: times.Sunrise},
                {id: 'dhuhr', name: 'Peshin', time: times.Dhuhr},
                {id: 'asr', name: 'Asr', time: times.Asr},
                {id: 'maghrib', name: 'Shom', time: times.Maghrib},
                {id: 'isha', name: 'Xufton', time: times.Isha}
            ];

            let activePrayer = null;
            for (let i = 0; i < prayerOrder.length; i++) {
                const [h, m] = prayerOrder[i].time.split(':').map(Number);
                if (nowMin >= (h * 60 + m)) activePrayer = prayerOrder[i];
            }

            // Priorities: 1. Unchecked Prayer (only if it's the current one), 2. Unchecked Deeds, 3. Success
            if (activePrayer && activePrayer.id && !namozData[activePrayer.id]) {
                container.style.display = 'block';
                const prompts = [
                    `${USER_NAME}, ${activePrayer.name} namozini o'qidingizmi?`,
                    `Vaqt g'animat, ${USER_NAME}. ${activePrayer.name}ni ado etdingizmi?`,
                    `${activePrayer.name} vaqti kirdi, ${USER_NAME}. Amalingizni belgilang.`
                ];
                title.innerHTML = `✨ ${prompts[Math.floor(Math.random() * prompts.length)]}`;
                text.innerHTML = `Ibodat — qalb oromi. Uni belgilashni unutmang, har bir amalni o'z vaqtida ado eting!`;
            } else {
                const randomDeeds = [
                    "Bugun Qur'on o'qidingizmi? O'qigan bo'lsangiz, belgilab qo'ying.",
                    "Zikr aytishni unutmadingizmi?",
                    "Istig'for aytib, qalbni pokladingizmi?",
                    "Bugundagi yaxshiliklarni belgiladingizmi?"
                ];
                const deed = randomDeeds[Math.floor(Math.random() * randomDeeds.length)];
                
                container.style.display = 'block';
                title.innerHTML = `🌟 ${USER_NAME}, ${deed}`;
                text.innerHTML = `Har bir kichik amal Ramazon oyida siz uchun foydali bo'ladi.`;
            }

            // Track target for click
            container.dataset.target = activePrayer ? 'prayer' : 'deeds';
        });
    }

    function handleReminderClick() {
        const container = document.getElementById('reminderContainer');
        const target = container.dataset.target;
        
        if (target === 'prayer') {
            const anchor = document.getElementById('quick-focus-anchor');
            if (anchor) anchor.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            const area = document.getElementById('todayFocusArea');
            if (area) {
                area.style.boxShadow = '0 0 30px rgba(212,168,67,0.3)';
                setTimeout(() => area.style.boxShadow = '', 2000);
            }
        } else {
            window.location.href = "{{ route('daily.show') }}";
        }
    }

    function closeReminder() {
        document.getElementById('reminderContainer').style.display = 'none';
        sessionStorage.setItem('reminder_closed_today', new Date().toDateString());
    }

    function showToast(msg) {
        let t = document.getElementById('dash-toast');
        if (!t) {
            t = document.createElement('div');
            t.id = 'dash-toast';
            t.style = 'position:fixed;bottom:80px;left:50%;transform:translateX(-50%);background:var(--gold);color:white;padding:12px 24px;border-radius:50px;z-index:1000;box-shadow:0 10px 30px rgba(0,0,0,0.3);font-size:0.9rem;font-weight:700;pointer-events:none;transition:all 0.4s;opacity:0;';
            document.body.appendChild(t);
        }
        t.textContent = msg;
        t.style.opacity = '1';
        t.style.transform = 'translateX(-50%) translateY(-10px)';
        setTimeout(() => { t.style.opacity = '0'; t.style.transform = 'translateX(-50%) translateY(0)'; }, 3000);
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (sessionStorage.getItem('reminder_closed_today') !== new Date().toDateString()) {
            setTimeout(checkReminders, 2000);
        }
    });
</script>
@endsection

@section('styles')
<style>
    .today-focus { 
        background: rgba(255,255,255,0.03); 
        padding: 20px 10px; 
        border-radius: 20px; 
        border: 1px solid rgba(255,255,255,0.08); 
        margin: 15px 0;
        position: relative;
    }
    .prayer-lantern {
        width: 18%;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        cursor: pointer;
        transition: var(--transition);
    }
    .lantern-wire {
        width: 1px;
        height: 12px;
        background: linear-gradient(to bottom, transparent, rgba(212,168,67,0.4));
        margin-bottom: -2px;
        z-index: 1;
    }
    .lantern-box {
        width: 100%;
        max-width: 65px;
        padding: 12px 2px;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 12px 12px 28px 28px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }
    .lantern-content { 
        display: flex; 
        flex-direction: column; 
        align-items: center; 
        gap: 4px; 
        z-index: 2; 
    }
    .lantern-content i { 
        font-size: 1.25rem; 
        color: var(--text-muted); 
        transition: all 0.4s ease; 
    }
    .lantern-name { 
        font-size: 0.6rem; 
        font-weight: 800; 
        color: var(--text-muted); 
        text-transform: uppercase; 
        letter-spacing: 0.3px; 
    }
    .lantern-light { 
        position: absolute; 
        top: 20%; 
        width: 25px; 
        height: 25px; 
        background: var(--gold); 
        border-radius: 50%; 
        opacity: 0; 
        transition: all 0.4s ease; 
        filter: blur(8px); 
    }
    .prayer-lantern.active .lantern-box { 
        background: linear-gradient(to bottom, rgba(212,168,67,0.25), rgba(212,168,67,0.05)); 
        border-color: var(--gold); 
        transform: translateY(2px);
        box-shadow: 0 5px 20px rgba(212,168,67,0.3);
    }
    .prayer-lantern.active .lantern-wire {
        background: var(--gold);
        opacity: 0.6;
    }
    .prayer-lantern.active .lantern-light { 
        opacity: 0.8; 
        transform: scale(1.6); 
    }
    .prayer-lantern.active .lantern-content i, 
    .prayer-lantern.active .lantern-name { 
        color: var(--gold); 
        text-shadow: 0 0 10px rgba(212,168,67,0.5); 
    }
    .prayer-lantern.loading { opacity: 0.5; pointer-events: none; }
    
    .fasting-status { position: relative; overflow: hidden; }
    .fasting-status.active { 
        background: linear-gradient(135deg, var(--gold), #f7c948) !important; 
        color: #1a1a1a !important; 
        border-color: #fff !important; 
        transform: scale(1.05); 
        box-shadow: 0 10px 20px rgba(212,168,67,0.3); 
    }
    .reminder-banner { overflow: hidden; border-radius: 16px; transition: transform 0.3s ease; }
    .reminder-glow { position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(192,132,252,0.1) 0%, transparent 70%); animation: rotateGlow 10s linear infinite; pointer-events: none; }
    @keyframes rotateGlow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    #reminderContainer { animation: slideInTop 0.6s cubic-bezier(0.23, 1, 0.32, 1); }
    @keyframes slideInTop { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection
