@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <h2>
        <i class="ri-hand-heart-line"></i>
        Assalomu alaykum, {{ auth()->user()->name }}!
    </h2>
    <p>{{ $today->format('d.m.Y') }} — Ramazon kunlik amallaringiz</p>
</div>

{{-- Stats --}}
<div class="stats-grid">
    <div class="card stat-card">
        <div class="stat-icon"><i class="ri-fire-fill"></i></div>
        <div class="stat-value">{{ $streak }}</div>
        <div class="stat-label">Kunlik streak</div>
        @if($streak > 0)
            <div class="streak-badge" style="margin-top:8px;">
                <i class="ri-fire-fill"></i> {{ $streak }} kun
            </div>
        @endif
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
                            $value = $item ? $item->value : null;
                        @endphp
                        <li class="checklist-item">
                            <div class="habit-icon"><i class="{{ $habit->icon }}"></i></div>
                            <span class="habit-name">{{ $habit->name }}</span>
                            <span class="habit-input">
                                @if($habit->type === 'checkbox')
                                    @if($isCompleted)
                                        <span style="color:var(--success);font-weight:700;"><i class="ri-check-line"></i></span>
                                    @else
                                        <span style="color:var(--text-muted);">—</span>
                                    @endif
                                @else
                                    <span style="color:var(--gold);font-weight:600;">{{ $value ?? 0 }}</span>
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>

                <div style="text-align:center;margin-top:16px;">
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: {{ $todayLog->completion_percent }}%"></div>
                    </div>
                    <span class="text-muted" style="font-size:0.8rem;">{{ $todayLog->completed_count }}/{{ $todayLog->total_count }} bajarildi ({{ $todayLog->completion_percent }}%)</span>
                </div>
            @else
                <div class="text-center" style="padding:24px;">
                    <div class="stat-icon" style="margin:0 auto 12px;"><i class="ri-file-list-3-line"></i></div>
                    <p class="text-muted">Bugun hali belgilanmagan</p>
                    <a href="{{ route('daily.show') }}" class="btn btn-primary btn-sm" style="margin-top:12px;">
                        <i class="ri-play-line"></i> Belgilashni boshlash
                    </a>
                </div>
            @endif

            <div style="text-align:center;margin-top:12px;">
                <a href="{{ route('daily.show') }}" class="btn btn-outline btn-sm">
                    Kunlik sahifaga o'tish <i class="ri-arrow-right-line"></i>
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
                            <div class="goal-title">
                                <i class="ri-flag-line"></i> {{ $goal->title }}
                            </div>
                            <div class="goal-meta">{{ $goal->current_value }}/{{ $goal->target_value }} {{ $goal->unit }}</div>
                        </div>
                        <span class="text-gold" style="font-weight:700;">{{ $goal->progress_percent }}%</span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: {{ $goal->progress_percent }}%"></div>
                    </div>
                </div>
            @endforeach
            @if($goals->count() > 3)
                <a href="{{ route('goals.index') }}" class="btn btn-outline btn-sm" style="margin-top:8px;">
                    Barcha maqsadlar <i class="ri-arrow-right-line"></i>
                </a>
            @endif
        @else
            <div class="card text-center" style="padding:20px;">
                <div class="stat-icon" style="margin:0 auto 8px;"><i class="ri-focus-3-line"></i></div>
                <p class="text-muted">Hali maqsad qo'yilmagan</p>
                <a href="{{ route('goals.index') }}" class="btn btn-primary btn-sm" style="margin-top:8px;">
                    <i class="ri-add-line"></i> Maqsad qo'shish
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
