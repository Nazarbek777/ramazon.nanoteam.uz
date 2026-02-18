@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php
    $treePercent = 0;
    $completedToday = 0;
    $totalToday = count($habits); // BARCHA habitlar soni
    if ($todayLog && $todayLog->items->count() > 0) {
        $completedToday = $todayLog->items->where('is_completed', true)->count();
    }
    $treePercent = $totalToday > 0 ? round(($completedToday / $totalToday) * 100) : 0;
    // 6 bosqich
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
    <div style="text-align:center;margin-bottom:16px;">
        <span class="ramadan-badge">
            <i class="ri-moon-clear-fill"></i> Ramazon {{ $ramadan['day'] }}-kuni · Qoldi {{ $ramadan['remaining'] }} kun
        </span>
    </div>
@elseif($ramadan['days_until'])
    <div style="text-align:center;margin-bottom:16px;">
        <span class="ramadan-badge">
            <i class="ri-moon-clear-line"></i> Ramazongacha {{ $ramadan['days_until'] }} kun
        </span>
    </div>
@endif

{{-- 🌳 DARAXT KARTASI --}}
<div class="tree-card">
    <div class="tree-scene">
        {{-- Yulduzlar --}}
        <div class="tree-stars">
            <span style="top:8%;left:12%;animation-delay:0s;"></span>
            <span style="top:15%;right:18%;animation-delay:0.7s;"></span>
            <span style="top:5%;left:45%;animation-delay:1.4s;"></span>
            <span style="top:22%;left:75%;animation-delay:2.1s;"></span>
            <span style="top:10%;right:8%;animation-delay:0.3s;"></span>
        </div>

        {{-- Daraxt --}}
        <div class="tree-wrapper">
            @if($stage === 0)
                <div class="tree-seed">
                    <div class="seed-body"></div>
                    <div class="seed-shine"></div>
                </div>
            @elseif($stage === 1)
                <div class="tree-sprout">
                    <div class="sprout-stem"></div>
                    <div class="sprout-leaf sprout-leaf-l"></div>
                    <div class="sprout-leaf sprout-leaf-r"></div>
                </div>
            @elseif($stage === 2)
                <div class="tree-young">
                    <div class="young-trunk"></div>
                    <div class="young-crown"></div>
                    <div class="young-crown-light"></div>
                </div>
            @elseif($stage === 3)
                <div class="tree-medium">
                    <div class="med-trunk"></div>
                    <div class="med-branch med-branch-l"></div>
                    <div class="med-branch med-branch-r"></div>
                    <div class="med-crown"></div>
                    <div class="med-crown-light"></div>
                    <div class="med-crown-top"></div>
                </div>
            @elseif($stage === 4)
                <div class="tree-big">
                    <div class="big-trunk"></div>
                    <div class="big-branch big-branch-l"></div>
                    <div class="big-branch big-branch-r"></div>
                    <div class="big-crown"></div>
                    <div class="big-crown-2"></div>
                    <div class="big-crown-3"></div>
                    <div class="big-crown-top"></div>
                </div>
            @else
                <div class="tree-paradise">
                    <div class="para-trunk"></div>
                    <div class="para-branch para-branch-l"></div>
                    <div class="para-branch para-branch-r"></div>
                    <div class="para-crown"></div>
                    <div class="para-crown-2"></div>
                    <div class="para-crown-3"></div>
                    <div class="para-crown-top"></div>
                    <div class="para-glow"></div>
                    {{-- Gullar --}}
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

        {{-- Tuproq --}}
        <div class="tree-ground"></div>
    </div>

    {{-- Info --}}
    <div class="tree-info">
        <div class="tree-stage-name">{{ $stageEmojis[$stage] }} {{ $stageNames[$stage] }}</div>
        <div class="tree-progress-row">
            <div class="tree-progress-bar">
                <div class="tree-progress-fill" style="width:{{ $treePercent }}%"></div>
            </div>
            <span class="tree-percent">{{ $treePercent }}%</span>
        </div>
        <div class="tree-detail">{{ $completedToday }}/{{ $totalToday ?: count($habits) }} amal bajarildi</div>
    </div>

    <a href="{{ route('daily.show') }}" class="btn btn-gold" style="width:100%;margin-top:10px;">
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

{{-- Haftalik --}}
<h3 class="section-title mt-24"><i class="ri-bar-chart-2-line"></i> Haftalik progress</h3>
<div class="card mb-24">
    <div class="chart-bar-group">
        @foreach($weeklyProgress as $day)
            <div class="chart-bar-wrapper">
                <div class="chart-bar-track">
                    <div class="chart-bar-fill" style="height: {{ max(3, $day['percent']) }}%"></div>
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
@endsection
