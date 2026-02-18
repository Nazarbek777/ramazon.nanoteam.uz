@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
{{-- Ramazon banner --}}
@if($ramadan['is_ramadan'])
    <div class="card" style="text-align:center;padding:16px 20px;margin-bottom:24px;border-color:rgba(212,168,67,0.3);background:linear-gradient(135deg,rgba(212,168,67,0.08),rgba(212,168,67,0.02));">
        <div style="font-size:1.6rem;color:var(--gold);margin-bottom:4px;">
            <i class="ri-moon-clear-fill" style="animation:moonGlow 3s ease-in-out infinite alternate;"></i>
        </div>
        <div style="font-size:1.1rem;font-weight:700;color:var(--gold);">Ramazon {{ $ramadan['day'] }}-kuni</div>
        <div style="font-size:0.8rem;color:var(--text-muted);margin-top:2px;">Qoldi: {{ $ramadan['remaining'] }} kun</div>
    </div>
@elseif($ramadan['days_until'])
    <div class="card" style="text-align:center;padding:16px 20px;margin-bottom:24px;border-color:rgba(212,168,67,0.3);">
        <div style="font-size:1.6rem;margin-bottom:4px;">
            <i class="ri-moon-clear-line" style="color:var(--gold);"></i>
        </div>
        <div style="font-size:0.9rem;color:var(--gold);font-weight:600;">Ramazongacha {{ $ramadan['days_until'] }} kun qoldi</div>
    </div>
@endif

<div class="page-header">
    <h2><i class="ri-hand-heart-line"></i> Assalomu alaykum, {{ auth()->user()->name }}!</h2>
    <p>{{ $today->format('d.m.Y') }}</p>
</div>

{{-- Namoz vaqtlari va Saharlik/Iftorlik --}}
<div class="card mb-24">
    <h3 class="section-title" style="margin-bottom:12px;">
        <i class="ri-time-line"></i> Namoz vaqtlari
    </h3>
    <div id="prayerTimesWidget">
        <div style="text-align:center;padding:16px;">
            <i class="ri-loader-4-line" style="font-size:1.4rem;color:var(--gold);animation:spin 1s linear infinite;"></i>
            <div style="font-size:0.8rem;color:var(--text-muted);margin-top:4px;">Yuklanmoqda...</div>
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

<div class="grid-2">
    {{-- Bugungi checklist --}}
    <div>
        <h3 class="section-title"><i class="ri-checkbox-circle-line"></i> Bugungi amallar</h3>
        <div class="card">
            @if($todayLog && $todayLog->items->count() > 0)
                <ul class="checklist">
                    @foreach($habits as $habit)
                        @php
                            $item = $todayLog->items->firstWhere('habit_id', $habit->id);
                            $isCompleted = $item ? $item->is_completed : false;
                        @endphp
                        <li class="checklist-item">
                            <div class="habit-icon"><i class="{{ $habit->icon }}"></i></div>
                            <span class="habit-name">{{ $habit->name }}</span>
                            @if($isCompleted)
                                <span style="color:var(--success);"><i class="ri-checkbox-circle-fill"></i></span>
                            @else
                                <span style="color:var(--text-muted);"><i class="ri-checkbox-blank-circle-line"></i></span>
                            @endif
                        </li>
                    @endforeach
                </ul>

                <div style="text-align:center;margin-top:14px;">
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: {{ $todayLog->completion_percent }}%"></div>
                    </div>
                    <span class="text-muted" style="font-size:0.78rem;">{{ $todayLog->completed_count }}/{{ $todayLog->total_count }} ({{ $todayLog->completion_percent }}%)</span>
                </div>
            @else
                <div class="text-center" style="padding:24px;">
                    <i class="ri-file-list-3-line" style="font-size:2rem;color:var(--text-muted);"></i>
                    <p class="text-muted" style="margin-top:8px;">Bugun hali belgilanmagan</p>
                </div>
            @endif

            <div style="text-align:center;margin-top:12px;">
                <a href="{{ route('daily.show') }}" class="btn btn-primary" style="width:100%;">
                    <i class="ri-edit-line"></i> Belgilashni boshlash
                </a>
            </div>
        </div>
    </div>

    {{-- Haftalik progress --}}
    <div>
        <h3 class="section-title"><i class="ri-bar-chart-2-line"></i> Haftalik progress</h3>
        <div class="card">
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
        <h3 class="section-title mt-24"><i class="ri-focus-3-line"></i> Maqsadlar</h3>
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
    </div>
</div>

{{-- Duolar --}}
<div class="mt-24">
    <h3 class="section-title"><i class="ri-book-read-line"></i> Ro'za duolari</h3>
    <div class="card">
        <div id="duasWidget">
            <div style="text-align:center;padding:12px;">
                <i class="ri-loader-4-line" style="font-size:1.2rem;color:var(--gold);animation:spin 1s linear infinite;"></i>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/prayer-times.js') }}"></script>
@endsection
