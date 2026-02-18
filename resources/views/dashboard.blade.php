@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php
    // Daraxt bosqichi: bugungi bajarilish foiziga qarab
    $treePercent = 0;
    if ($todayLog && $todayLog->items->count() > 0) {
        $treePercent = round($todayLog->items->where('is_completed', true)->count() / $todayLog->items->count() * 100);
    }
    // 0-19: urug', 20-39: niholcha, 40-59: kichik daraxt, 60-79: katta daraxt, 80-100: gullagan daraxt
    $treeStage = 0;
    if ($treePercent >= 80) $treeStage = 4;
    elseif ($treePercent >= 60) $treeStage = 3;
    elseif ($treePercent >= 40) $treeStage = 2;
    elseif ($treePercent >= 20) $treeStage = 1;
@endphp

{{-- Ramazon banner --}}
@if($ramadan['is_ramadan'])
    <div class="card" style="text-align:center;padding:14px 20px;margin-bottom:20px;border-color:rgba(212,168,67,0.3);background:linear-gradient(135deg,rgba(212,168,67,0.08),rgba(212,168,67,0.02));">
        <div style="font-size:1.3rem;color:var(--gold);margin-bottom:2px;">
            <i class="ri-moon-clear-fill"></i>
        </div>
        <div style="font-size:1rem;font-weight:700;color:var(--gold);">Ramazon {{ $ramadan['day'] }}-kuni</div>
        <div style="font-size:0.75rem;color:var(--text-muted);margin-top:2px;">Qoldi: {{ $ramadan['remaining'] }} kun</div>
    </div>
@elseif($ramadan['days_until'])
    <div class="card" style="text-align:center;padding:14px;margin-bottom:20px;border-color:rgba(212,168,67,0.3);">
        <i class="ri-moon-clear-line" style="color:var(--gold);font-size:1.2rem;"></i>
        <div style="font-size:0.85rem;color:var(--gold);font-weight:600;margin-top:4px;">Ramazongacha {{ $ramadan['days_until'] }} kun qoldi</div>
    </div>
@endif

{{-- DARAXT — asosiy gamifikatsiya --}}
<div class="card" style="text-align:center;padding:20px 16px;margin-bottom:20px;overflow:hidden;position:relative;">
    <div style="position:relative;height:200px;display:flex;align-items:flex-end;justify-content:center;">
        {{-- Tuproq --}}
        <div style="position:absolute;bottom:0;left:0;right:0;height:30px;background:linear-gradient(180deg,rgba(101,67,33,0.3),rgba(101,67,33,0.15));border-radius:50%;"></div>

        {{-- Daraxt SVG --}}
        <div id="treeContainer" style="position:relative;z-index:2;transition:all 1s ease;">
            @if($treeStage === 0)
                {{-- Urug' --}}
                <svg width="60" height="50" viewBox="0 0 60 50">
                    <ellipse cx="30" cy="40" rx="8" ry="5" fill="#8B6914" opacity="0.6"/>
                    <circle cx="30" cy="35" r="6" fill="#D4A843" opacity="0.8"/>
                    <path d="M28 35 Q30 30 32 35" stroke="#4a7c59" stroke-width="1.5" fill="none"/>
                </svg>
                <div style="font-size:0.7rem;color:var(--text-muted);margin-top:4px;">Urug' ekildi...</div>
            @elseif($treeStage === 1)
                {{-- Niholcha --}}
                <svg width="80" height="90" viewBox="0 0 80 90">
                    <line x1="40" y1="85" x2="40" y2="45" stroke="#6B4226" stroke-width="3"/>
                    <ellipse cx="40" cy="42" rx="16" ry="20" fill="#4a7c59" opacity="0.85"/>
                    <ellipse cx="40" cy="38" rx="12" ry="14" fill="#5a9c69" opacity="0.7"/>
                </svg>
                <div style="font-size:0.7rem;color:var(--accent-light);margin-top:2px;">Niholcha o'smoqda 🌱</div>
            @elseif($treeStage === 2)
                {{-- Kichik daraxt --}}
                <svg width="120" height="140" viewBox="0 0 120 140">
                    <rect x="55" y="90" width="10" height="45" rx="3" fill="#6B4226"/>
                    <line x1="60" y1="100" x2="40" y2="85" stroke="#6B4226" stroke-width="3" stroke-linecap="round"/>
                    <line x1="60" y1="105" x2="80" y2="90" stroke="#6B4226" stroke-width="3" stroke-linecap="round"/>
                    <ellipse cx="60" cy="65" rx="35" ry="35" fill="#3d7a4a" opacity="0.9"/>
                    <ellipse cx="45" cy="58" rx="22" ry="22" fill="#4a9c5a" opacity="0.6"/>
                    <ellipse cx="75" cy="62" rx="18" ry="18" fill="#55a865" opacity="0.5"/>
                    <ellipse cx="60" cy="50" rx="20" ry="18" fill="#5cb868" opacity="0.4"/>
                </svg>
                <div style="font-size:0.75rem;color:var(--success);margin-top:2px;">Daraxt o'sib bormoqda 🌿</div>
            @elseif($treeStage === 3)
                {{-- Katta daraxt --}}
                <svg width="160" height="170" viewBox="0 0 160 170">
                    <rect x="73" y="108" width="14" height="55" rx="4" fill="#5a3a1a"/>
                    <line x1="80" y1="125" x2="50" y2="105" stroke="#5a3a1a" stroke-width="4" stroke-linecap="round"/>
                    <line x1="80" y1="120" x2="110" y2="100" stroke="#5a3a1a" stroke-width="4" stroke-linecap="round"/>
                    <line x1="80" y1="130" x2="45" y2="120" stroke="#5a3a1a" stroke-width="3" stroke-linecap="round"/>
                    <ellipse cx="80" cy="70" rx="50" ry="50" fill="#2d6e3f" opacity="0.95"/>
                    <ellipse cx="55" cy="60" rx="30" ry="30" fill="#3d8a4f" opacity="0.6"/>
                    <ellipse cx="105" cy="65" rx="25" ry="25" fill="#4a9c5a" opacity="0.5"/>
                    <ellipse cx="80" cy="45" rx="30" ry="25" fill="#4aaa5a" opacity="0.4"/>
                    <ellipse cx="65" cy="80" rx="20" ry="18" fill="#55b565" opacity="0.3"/>
                </svg>
                <div style="font-size:0.8rem;color:var(--success);margin-top:2px;">Ulkan daraxt! 🌳</div>
            @else
                {{-- Gullagan daraxt (80-100%) --}}
                <svg width="180" height="180" viewBox="0 0 180 180">
                    <rect x="83" y="115" width="14" height="55" rx="4" fill="#5a3a1a"/>
                    <line x1="90" y1="130" x2="55" y2="110" stroke="#5a3a1a" stroke-width="4" stroke-linecap="round"/>
                    <line x1="90" y1="125" x2="125" y2="105" stroke="#5a3a1a" stroke-width="4" stroke-linecap="round"/>
                    <line x1="90" y1="135" x2="50" y2="125" stroke="#5a3a1a" stroke-width="3" stroke-linecap="round"/>
                    <line x1="90" y1="140" x2="130" y2="130" stroke="#5a3a1a" stroke-width="3" stroke-linecap="round"/>
                    <ellipse cx="90" cy="72" rx="55" ry="55" fill="#2d6e3f"/>
                    <ellipse cx="60" cy="60" rx="32" ry="30" fill="#3d8a4f" opacity="0.6"/>
                    <ellipse cx="120" cy="65" rx="28" ry="26" fill="#4a9c5a" opacity="0.5"/>
                    <ellipse cx="90" cy="42" rx="32" ry="25" fill="#4aaa5a" opacity="0.4"/>
                    {{-- Gullar --}}
                    <circle cx="55" cy="48" r="5" fill="#f7c948" opacity="0.9"/>
                    <circle cx="75" cy="35" r="4" fill="#ff8fab" opacity="0.85"/>
                    <circle cx="110" cy="42" r="5" fill="#f7c948" opacity="0.9"/>
                    <circle cx="125" cy="60" r="4" fill="#ff8fab" opacity="0.85"/>
                    <circle cx="65" cy="70" r="3.5" fill="#f7c948" opacity="0.8"/>
                    <circle cx="100" cy="30" r="4" fill="#ff8fab" opacity="0.85"/>
                    <circle cx="90" cy="55" r="4" fill="#f7c948" opacity="0.9"/>
                    <circle cx="45" cy="65" r="3" fill="#ff8fab" opacity="0.7"/>
                    <circle cx="135" cy="72" r="3.5" fill="#f7c948" opacity="0.8"/>
                    {{-- Yulduzchalar --}}
                    <circle cx="30" cy="25" r="2" fill="var(--gold)" opacity="0.6" class="sparkle"/>
                    <circle cx="150" cy="30" r="2" fill="var(--gold)" opacity="0.6" class="sparkle"/>
                    <circle cx="90" cy="15" r="2.5" fill="var(--gold)" opacity="0.7" class="sparkle"/>
                </svg>
                <div style="font-size:0.85rem;color:var(--gold);font-weight:600;margin-top:2px;">Daraxt gulladi! 🌸✨</div>
            @endif
        </div>
    </div>

    <div style="margin-top:12px;">
        <div style="font-size:0.8rem;color:var(--text-muted);">Bugun: {{ $treePercent }}% bajarildi</div>
        <div class="progress-bar-container" style="margin-top:6px;">
            <div class="progress-bar" style="width: {{ $treePercent }}%"></div>
        </div>
    </div>

    <a href="{{ route('daily.show') }}" class="btn btn-gold" style="width:100%;margin-top:12px;">
        <i class="ri-edit-line"></i> Amallarni belgilash
    </a>
</div>

{{-- Namoz vaqtlari --}}
<div class="card mb-24">
    <h3 class="section-title" style="margin-bottom:10px;">
        <i class="ri-time-line"></i> Namoz vaqtlari
    </h3>
    <div id="prayerTimesWidget">
        <div style="text-align:center;padding:14px;">
            <i class="ri-loader-4-line" style="font-size:1.2rem;color:var(--gold);animation:spin 1s linear infinite;"></i>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:4px;">Yuklanmoqda...</div>
        </div>
    </div>
</div>

{{-- Stats --}}
<div class="stats-grid">
    <div class="card stat-card">
        <div class="stat-icon"><i class="ri-fire-fill"></i></div>
        <div class="stat-value">{{ $streak }}</div>
        <div class="stat-label">Streak</div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon"><i class="ri-checkbox-circle-fill"></i></div>
        <div class="stat-value">{{ $stats['total_completed'] }}</div>
        <div class="stat-label">Bajarilgan</div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon"><i class="ri-calendar-check-fill"></i></div>
        <div class="stat-value">{{ $stats['total_days'] }}</div>
        <div class="stat-label">Faol kunlar</div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon"><i class="ri-pie-chart-fill"></i></div>
        <div class="stat-value">{{ $stats['completion_rate'] }}%</div>
        <div class="stat-label">Bajarilish</div>
    </div>
</div>

{{-- Haftalik progress --}}
<h3 class="section-title mt-24"><i class="ri-bar-chart-2-line"></i> Haftalik progress</h3>
<div class="card mb-24">
    <div class="chart-bar-group">
        @foreach($weeklyProgress as $day)
            <div class="chart-bar-wrapper">
                <div class="chart-bar-track">
                    <div class="chart-bar-fill" style="height: {{ max(3, $day['percent']) }}%" data-percent="{{ $day['percent'] }}"></div>
                </div>
                <div class="chart-bar-label">{{ $day['day'] }}</div>
            </div>
        @endforeach
    </div>
</div>

{{-- Maqsadlar --}}
<h3 class="section-title"><i class="ri-focus-3-line"></i> Maqsadlar</h3>
@if($goals->count() > 0)
    @foreach($goals->take(3) as $goal)
        <div class="card goal-card">
            <div class="goal-header">
                <div>
                    <div class="goal-title"><i class="ri-flag-line"></i> {{ $goal->title }}</div>
                    <div class="goal-meta">{{ $goal->current_value }}/{{ $goal->target_value }} {{ $goal->unit }}</div>
                </div>
                <span class="text-gold" style="font-weight:700;">{{ $goal->progress_percent }}%</span>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar" style="width: {{ $goal->progress_percent }}%"></div>
            </div>
        </div>
    @endforeach
@else
    <div class="card text-center" style="padding:16px;">
        <p class="text-muted">Hali maqsad yo'q</p>
        <a href="{{ route('goals.index') }}" class="btn btn-outline btn-sm" style="margin-top:8px;">
            <i class="ri-add-line"></i> Maqsad qo'shish
        </a>
    </div>
@endif

{{-- Duolar --}}
<h3 class="section-title mt-24"><i class="ri-book-read-line"></i> Ro'za duolari</h3>
<div class="card" style="margin-bottom:24px;">
    <div id="duasWidget">
        <div style="text-align:center;padding:12px;">
            <i class="ri-loader-4-line" style="font-size:1.2rem;color:var(--gold);animation:spin 1s linear infinite;"></i>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/prayer-times.js') }}"></script>
<style>
    .sparkle { animation: sparkleAnim 2s ease-in-out infinite alternate; }
    @keyframes sparkleAnim {
        0% { opacity: 0.3; r: 1.5; }
        100% { opacity: 1; r: 3; }
    }
</style>
@endsection
