@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <h2>
        @if(auth()->user()->isFemale())
            👩 Assalomu alaykum, {{ auth()->user()->name }}!
        @else
            👨 Assalomu alaykum, {{ auth()->user()->name }}!
        @endif
    </h2>
    <p>Bugungi kun: {{ $today->format('d.m.Y') }} — Ramazon kunlik amallaringiz</p>
</div>

{{-- Streak va umumiy statistika --}}
<div class="stats-grid">
    <div class="card stat-card">
        <span class="stat-icon">🔥</span>
        <div class="stat-value">{{ $streak }}</div>
        <div class="stat-label">Kunlik streak</div>
        @if($streak > 0)
            <div class="streak-badge" style="margin-top:8px;font-size:0.85rem;">
                <span class="fire">🔥</span> {{ $streak }} kun ketma-ket
            </div>
        @endif
    </div>
    <div class="card stat-card">
        <span class="stat-icon">✅</span>
        <div class="stat-value">{{ $stats['total_completed'] }}</div>
        <div class="stat-label">Bajarilgan amallar</div>
    </div>
    <div class="card stat-card">
        <span class="stat-icon">📅</span>
        <div class="stat-value">{{ $stats['total_days'] }}</div>
        <div class="stat-label">Faol kunlar</div>
    </div>
    <div class="card stat-card">
        <span class="stat-icon">📊</span>
        <div class="stat-value">{{ $stats['completion_rate'] }}%</div>
        <div class="stat-label">Bajarilish darajasi</div>
    </div>
</div>

<div class="grid-2">
    {{-- Bugungi checklist --}}
    <div>
        <h3 class="section-title">✅ Bugungi amallar</h3>
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
                            <span class="habit-icon">{{ $habit->icon }}</span>
                            <span class="habit-name">{{ $habit->name }}</span>
                            <span class="habit-input">
                                @if($habit->type === 'checkbox')
                                    @if($isCompleted)
                                        <span style="color:var(--success);font-weight:700;font-size:1.2rem;">✓</span>
                                    @else
                                        <span style="color:var(--text-muted);">—</span>
                                    @endif
                                @else
                                    <span style="color:var(--gold);font-weight:600;">{{ $value ?? 0 }}</span>
                                    <span class="text-muted" style="font-size:0.8rem;"> sahifa</span>
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>

                <div style="text-align:center;margin-top:16px;">
                    <div class="progress-bar-container" style="margin-bottom:8px;">
                        <div class="progress-bar" style="width: {{ $todayLog->completion_percent }}%"></div>
                    </div>
                    <span class="text-muted" style="font-size:0.85rem;">{{ $todayLog->completed_count }}/{{ $todayLog->total_count }} bajarildi ({{ $todayLog->completion_percent }}%)</span>
                </div>
            @else
                <div class="text-center" style="padding:24px;">
                    <span style="font-size:3rem;display:block;margin-bottom:12px;">📋</span>
                    <p class="text-muted">Bugun hali belgilanmagan</p>
                    <a href="{{ route('daily.show') }}" class="btn btn-primary btn-sm" style="margin-top:12px;">Belgilashni boshlash</a>
                </div>
            @endif

            <div style="text-align:center;margin-top:12px;">
                <a href="{{ route('daily.show') }}" class="btn btn-outline btn-sm">Kunlik sahifaga o'tish →</a>
            </div>
        </div>
    </div>

    {{-- Haftalik progress --}}
    <div>
        <h3 class="section-title">📊 Haftalik progress</h3>
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
        <h3 class="section-title mt-24">🎯 Maqsadlar</h3>
        @if($goals->count() > 0)
            @foreach($goals->take(3) as $goal)
                <div class="card goal-card">
                    <div class="goal-header">
                        <div>
                            <div class="goal-title">{{ $goal->title }}</div>
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
                <a href="{{ route('goals.index') }}" class="btn btn-outline btn-sm" style="margin-top:8px;">Barcha maqsadlar →</a>
            @endif
        @else
            <div class="card text-center" style="padding:20px;">
                <span style="font-size:2rem;display:block;margin-bottom:8px;">🎯</span>
                <p class="text-muted">Hali maqsad qo'yilmagan</p>
                <a href="{{ route('goals.index') }}" class="btn btn-primary btn-sm" style="margin-top:8px;">Maqsad qo'shish</a>
            </div>
        @endif
    </div>
</div>
@endsection
