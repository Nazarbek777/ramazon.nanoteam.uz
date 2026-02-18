@extends('layouts.app')
@section('title', 'Hisobotlar')

@section('content')
<div class="page-header">
    <h2><i class="ri-bar-chart-box-line"></i> Hisobotlar</h2>
    <p>{{ $startDate->format('d.m') }} — {{ $endDate->format('d.m.Y') }} davri uchun tahlil</p>
</div>

{{-- Tab switcher --}}
<div class="tabs">
    <a href="{{ route('reports', ['period' => 'weekly']) }}"
       class="tab-link {{ $period === 'weekly' ? 'active' : '' }}">
        <i class="ri-calendar-line"></i> Haftalik
    </a>
    <a href="{{ route('reports', ['period' => 'monthly']) }}"
       class="tab-link {{ $period === 'monthly' ? 'active' : '' }}">
        <i class="ri-calendar-2-line"></i> Oylik
    </a>
</div>

{{-- Stats --}}
<div class="report-grid mb-24">
    <div class="card stat-card">
        <div class="stat-icon"><i class="ri-checkbox-circle-fill"></i></div>
        <div class="stat-value">{{ $totalCompleted }}</div>
        <div class="stat-label">Bajarilgan amallar</div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon"><i class="ri-pie-chart-fill"></i></div>
        <div class="stat-value">{{ $overallPercent }}%</div>
        <div class="stat-label">Umumiy bajarilish</div>
    </div>
</div>

<div class="grid-2">
    {{-- Chart --}}
    <div>
        <h3 class="section-title"><i class="ri-bar-chart-2-line"></i> Kunlik bajarilish</h3>
        <div class="card">
            <div class="chart-bar-group" style="height: {{ $period === 'monthly' ? '100px' : '130px' }};">
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

    {{-- Top habits --}}
    <div>
        <h3 class="section-title"><i class="ri-trophy-line"></i> Eng ko'p bajarilgan</h3>
        <div class="card">
            @if($topHabits->count() > 0)
                @foreach($topHabits as $index => $item)
                    <div class="top-habit">
                        <div class="rank {{ $index < 3 ? 'gold' : '' }}">{{ $index + 1 }}</div>
                        <div class="habit-info">
                            {{ $item->habit->name ?? 'Noma\'lum' }}
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

{{-- Missed days --}}
<div class="mt-24">
    <h3 class="section-title"><i class="ri-close-circle-line"></i> O'tkazib yuborilgan kunlar</h3>
    <div class="card">
        @if(count($missedDates) > 0)
            <ul class="missed-list">
                @foreach($missedDates as $missed)
                    <li><i class="ri-close-line"></i> {{ \Carbon\Carbon::parse($missed)->format('d.m.Y') }}</li>
                @endforeach
            </ul>
        @else
            <div class="text-center" style="padding:20px;">
                <div class="stat-icon" style="margin:0 auto 8px;"><i class="ri-emotion-happy-line"></i></div>
                <span class="text-success" style="font-weight:600;">Hech qanday kun o'tkazib yuborilmagan!</span>
            </div>
        @endif
    </div>
</div>
@endsection
