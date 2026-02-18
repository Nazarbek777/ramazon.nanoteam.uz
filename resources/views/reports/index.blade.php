@extends('layouts.app')
@section('title', 'Hisobotlar')

@section('content')
<div class="page-header">
    <h2>📈 Hisobotlar</h2>
    <p>{{ $startDate->format('d.m') }} — {{ $endDate->format('d.m.Y') }} davri uchun tahlil</p>
</div>

{{-- Tab switcher --}}
<div class="tabs">
    <a href="{{ route('reports', ['period' => 'weekly']) }}"
       class="tab-link {{ $period === 'weekly' ? 'active' : '' }}">
        📅 Haftalik
    </a>
    <a href="{{ route('reports', ['period' => 'monthly']) }}"
       class="tab-link {{ $period === 'monthly' ? 'active' : '' }}">
        📆 Oylik
    </a>
</div>

{{-- Umumiy statistika --}}
<div class="report-grid mb-24">
    <div class="card stat-card">
        <span class="stat-icon">✅</span>
        <div class="stat-value">{{ $totalCompleted }}</div>
        <div class="stat-label">Bajarilgan amallar</div>
    </div>
    <div class="card stat-card">
        <span class="stat-icon">📊</span>
        <div class="stat-value">{{ $overallPercent }}%</div>
        <div class="stat-label">Umumiy bajarilish</div>
    </div>
</div>

<div class="grid-2">
    {{-- Kunlik diagramma --}}
    <div>
        <h3 class="section-title">📊 Kunlik bajarilish</h3>
        <div class="card">
            <div class="chart-bar-group" style="height: {{ $period === 'monthly' ? '120px' : '140px' }};">
                @foreach($dailyChart as $day)
                    <div class="chart-bar-wrapper">
                        <div class="chart-bar-track">
                            <div class="chart-bar-fill"
                                 style="height: {{ max(3, $day['percent']) }}%"
                                 data-percent="{{ $day['percent'] }}"></div>
                        </div>
                        <div class="chart-bar-label">{{ $day['date'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Eng ko'p bajarilgan --}}
    <div>
        <h3 class="section-title">🏆 Eng ko'p bajarilgan</h3>
        <div class="card">
            @if($topHabits->count() > 0)
                @foreach($topHabits as $index => $item)
                    <div class="top-habit">
                        <div class="rank {{ $index < 3 ? 'gold' : '' }}">{{ $index + 1 }}</div>
                        <div class="habit-info">
                            <span>{{ $item->habit->icon ?? '⭐' }} {{ $item->habit->name ?? 'Noma\'lum' }}</span>
                        </div>
                        <div class="habit-count">{{ $item->count }} marta</div>
                    </div>
                @endforeach
            @else
                <div class="text-center" style="padding:20px;">
                    <span class="text-muted">Hali ma'lumot yo'q</span>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- O'tkazib yuborilgan kunlar --}}
<div class="mt-24">
    <h3 class="section-title">❌ O'tkazib yuborilgan kunlar</h3>
    <div class="card">
        @if(count($missedDates) > 0)
            <ul class="missed-list">
                @foreach($missedDates as $missed)
                    <li>{{ \Carbon\Carbon::parse($missed)->format('d.m.Y') }}</li>
                @endforeach
            </ul>
        @else
            <div class="text-center" style="padding:20px;">
                <span style="font-size:2rem;display:block;margin-bottom:8px;">🎉</span>
                <span class="text-success" style="font-weight:600;">Hech qanday kun o'tkazib yuborilmagan!</span>
            </div>
        @endif
    </div>
</div>
@endsection
