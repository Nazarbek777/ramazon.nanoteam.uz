@extends('layouts.app')
@section('title', 'Hisobotlar')

@section('content')
<div class="page-header" style="text-align:center;margin-bottom:20px;">
    <h2 style="font-size:1.5rem;color:var(--gold);"><i class="ri-bar-chart-box-line"></i> Hisobotlar</h2>
    <p class="text-muted">{{ $startDate->format('d.m') }} — {{ $endDate->format('d.m.Y') }} davri uchun tahlil</p>
</div>

{{-- Tab switcher --}}
<div style="text-align:center;margin-bottom:24px;">
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
</div>

{{-- Premium Stats Row --}}
<div class="report-grid mb-24">
    <div class="card stat-card stat-card-accent">
        <div class="stat-icon"><i class="ri-pie-chart-2-fill"></i></div>
        <div class="stat-value">{{ $overallPercent }}<span class="stat-value-unit">%</span></div>
        <div class="stat-label">Umumiy natija</div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="color:var(--success);"><i class="ri-fire-fill"></i></div>
        <div class="stat-value">{{ $streak }}</div>
        <div class="stat-label">Kunlik Streak</div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="color:var(--gold);"><i class="ri-award-fill"></i></div>
        <div class="stat-value">{{ $bestDay['percent'] ?? 0 }}<span class="stat-value-unit">%</span></div>
        <div class="stat-label">Eng yaxshi natija</div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="color:var(--accent-light);"><i class="ri-checkbox-multiple-fill"></i></div>
        <div class="stat-value">{{ $totalCompleted }}</div>
        <div class="stat-label">Jami amallar</div>
    </div>
</div>

{{-- Category Progress --}}
<div class="report-categories">
    <div class="category-card">
        <div class="category-header">
            <span class="category-title">Ibodatlar</span>
            <span class="category-percent">{{ $ibodatPercent }}%</span>
        </div>
        <div class="category-progress">
            <div class="category-progress-fill" style="width: {{ $ibodatPercent }}%; background: var(--gold);"></div>
        </div>
    </div>
    <div class="category-card">
        <div class="category-header">
            <span class="category-title">Vazifalar</span>
            <span class="category-percent">{{ $vazifaPercent }}%</span>
        </div>
        <div class="category-progress">
            <div class="category-progress-fill" style="width: {{ $vazifaPercent }}%; background: var(--accent);"></div>
        </div>
    </div>
</div>

<div class="grid-2">
    {{-- Chart --}}
    <div>
        <h3 class="section-title"><i class="ri-bar-chart-2-line"></i> Kunlik dinamika</h3>
        <div class="card" style="padding:20px 10px;">
            <div class="chart-bar-group" style="height: {{ $period === 'monthly' ? '120px' : '150px' }}; align-items: flex-end;">
                @foreach($dailyChart as $day)
                    <div class="chart-bar-wrapper">
                        <div class="chart-bar-track" style="background:var(--white-5); border-radius:4px 4px 0 0;">
                            <div class="chart-bar-fill"
                                 style="height: {{ max(4, $day['percent']) }}%; 
                                        background: {{ $day['percent'] > 80 ? 'var(--gold)' : ($day['percent'] > 50 ? 'var(--accent)' : 'rgba(255,255,255,0.1)') }};
                                        border-radius:4px 4px 0 0;"
                                 data-percent="{{ $day['percent'] }}"></div>
                        </div>
                        <div class="chart-bar-label" style="font-size:0.6rem; margin-top:8px; opacity:0.7;">{{ $day['date'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Top habits --}}
    <div>
        <h3 class="section-title"><i class="ri-medal-fill"></i> Eng faol amallar</h3>
        <div class="card">
            @if($topHabits->count() > 0)
                @foreach($topHabits as $index => $item)
                    <div class="top-habit" style="padding:12px 0;">
                        <div class="rank {{ $index < 3 ? 'gold' : '' }}" style="border-radius:10px;">{{ $index + 1 }}</div>
                        <div class="habit-info">
                            <div style="font-weight:600; font-size:0.9rem;">{{ $item->habit->name ?? 'Noma\'lum' }}</div>
                            <span class="top-habit-frequency">{{ $item->frequency }}% davomiylik</span>
                        </div>
                        <div class="habit-count">
                            {{ $item->count }}<span style="font-size:0.7rem; color:var(--text-muted); font-weight:500; margin-left:2px;">marta</span>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center" style="padding:40px 20px;">
                    <i class="ri-inbox-line" style="font-size:2rem; color:var(--text-muted); opacity:0.3;"></i>
                    <p class="text-muted" style="margin-top:10px;">Hali ma'lumotlar yetarli emas</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Missed days --}}
@if(count($missedDates) > 0)
    <div class="mt-24">
        <h3 class="section-title"><i class="ri-calendar-close-line"></i> Siz to'ldirmagan kunlar</h3>
        <div class="card" style="padding:10px;">
            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap:8px;">
                @foreach($missedDates as $missed)
                    <a href="{{ route('daily.show', ['date' => $missed]) }}" 
                       style="background:var(--white-5); padding:8px; border-radius:10px; text-align:center; font-size:0.75rem; border:1px solid var(--border-color); color:var(--text-muted); text-decoration:none; transition: var(--transition-fast);"
                       onmouseover="this.style.borderColor='var(--gold)'; this.style.color='var(--gold)';"
                       onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-muted)';"
                    >
                        {{ \Carbon\Carbon::parse($missed)->format('d.m.Y') }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@else
    <div class="mt-24 card stat-card-accent" style="padding:30px; border-style:dashed;">
        <div class="stat-icon" style="margin:0 auto 12px; font-size:2.5rem;"><i class="ri-sparkling-fill"></i></div>
        <h4 style="color:var(--gold); font-size:1.1rem; font-weight:700;">Ajoyib natija!</h4>
        <p class="text-muted" style="font-size:0.85rem; margin-top:5px;">Siz birorta ham kunni o'tkazib yubormadingiz. Davom eting!</p>
    </div>
@endif
@endsection
