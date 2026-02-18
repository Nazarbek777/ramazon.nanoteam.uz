@extends('layouts.app')
@section('title', 'Bosh sahifa')

@section('content')
@php
    $treePercent = 0;
    $completedToday = 0;
    $totalToday = count($habits);
    if ($todayLog && $todayLog->items->count() > 0) {
        $completedToday = $todayLog->items->where('is_completed', true)->count();
    }
    $treePercent = $totalToday > 0 ? round(($completedToday / $totalToday) * 100) : 0;
    $stage = 0;
    if ($treePercent >= 90) $stage = 5;
    elseif ($treePercent >= 70) $stage = 4;
    elseif ($treePercent >= 50) $stage = 3;
    elseif ($treePercent >= 30) $stage = 2;
    elseif ($treePercent >= 10) $stage = 1;

    $stageNames = ['Urug\'', 'Niholcha', 'Ko\'chat', 'Yosh daraxt', 'Katta daraxt', 'Jannat daraxti'];
    $stageEmojis = ['🌰', '🌱', '🌿', '🌳', '🌲', '🌸'];
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
        <div class="tree-detail">{{ $completedToday }}/{{ $totalToday }} amal bajarildi</div>
        <div class="tree-motivation">
            @if($treePercent === 0)
                <p>✨ Bugungi amallarni belgilang va daraxtingiz o'sishini kuzating!</p>
            @elseif($treePercent < 20)
                <p>🌱 Yaxshi boshlang'ich! Davom eting — har bir amal muhim!</p>
            @elseif($treePercent < 40)
                <p>💪 Ajoyib! Yarim yo'lga yetmadingiz, lekin yaqinsiz!</p>
            @elseif($treePercent < 60)
                <p>🔥 Zo'r! Yarmidan ko'pi bajarildi. Davom eting!</p>
            @elseif($treePercent < 80)
                <p>🌟 Mashaalloh! Juda yaxshi natija — ozgina qoldi!</p>
            @elseif($treePercent < 100)
                <p>🏆 Deyarli tamom! Jannat daraxtiga ozgina qoldi!</p>
            @else
                <p>🌸 SubhanAlloh! Barcha amallar bajarildi! Alloh qabul qilsin!</p>
            @endif
        </div>
    </div>
    <a href="{{ route('daily.show') }}" class="btn btn-gold" style="width:100%;margin-top:10px;">
        @if($treePercent === 0)
            <i class="ri-play-fill"></i> Boshlash — bugungi amallar
        @elseif($treePercent < 100)
            <i class="ri-edit-line"></i> Davom etish ({{ $totalToday - $completedToday }} ta qoldi)
        @else
            <i class="ri-check-double-line"></i> Bugungi amallar ✓
        @endif
    </a>
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
            <p class="dua-arabic">نَوَيتُ أَن أَصُومَ غَداً مِن شَهرِ رَمَضَانَ المُبَارَكِ فَرضاً لَكَ يَا اللّهُ فَتَقَبَّلْ مِنِّي إِنَّكَ أَنتَ السَّمِيعُ العَلِيمُ</p>
            <p class="dua-translit"><strong>Navaytu an asuma g'adan min shahri Ramazonal mubaraki fardan laka ya Allohu fataqabbal minni innaka antas-sami'ul 'alim.</strong></p>
            <p class="dua-meaning">Ertangi Ramazon kunining ro'zasini tutmoqni niyat qildim. Ya Alloh, qabul ayla, albatta Sen Eshituvchi va Biluvchisan.</p>
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
            <p class="dua-arabic">اللّهُمَّ لَكَ صُمتُ وَبِكَ آمَنتُ وَعَلَيكَ تَوَكَّلتُ وَعَلَى رِزقِكَ أَفطَرتُ فَاغفِر لِي مَا قَدَّمتُ وَمَا أَخَّرتُ</p>
            <p class="dua-translit"><strong>Allohumma laka sumtu va bika amantu va 'alayka tavakkaltu va 'ala rizqika aftartu, fag'fir li ma qaddamtu va ma axxartu.</strong></p>
            <p class="dua-meaning">Yo Alloh, Sen uchun ro'za tutdim, Senga iymon keltirdim, Senga tavakkul qildim va bergan rizqing bilan og'iz ochdim. Oldingi va keyingi gunohlarimni mag'firat ayla.</p>
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
            <p class="dua-meaning">Tashnalik ketdi, tomir-lar namlandi va ajr sobit bo'ldi, inshaAlloh.</p>
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

<div style="text-align:center;padding:16px 10px;margin-top:20px;border-top:1px solid var(--white-5);">
    <p style="font-size:0.7rem;color:var(--text-muted);margin:0;">Namoz vaqtlari islom.uz saytidan olindi</p>
</div>
@endsection

@section('scripts')
<script>
    // Smooth scroll
    document.querySelectorAll('.quick-nav-btn[href^="#"]').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            const target = document.querySelector(btn.getAttribute('href'));
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
</script>
@endsection
