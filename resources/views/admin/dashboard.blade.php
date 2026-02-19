@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('content')
<div class="page-header" style="text-align:center;margin-bottom:24px;">
    <h2 style="font-size:1.5rem;color:var(--gold);"><i class="ri-dashboard-3-line"></i> Admin Analiz</h2>
    <p class="text-muted">Foydalanuvchilar faolligi va o'sish ko'rsatkichlari</p>
</div>

<div class="admin-stats-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:24px;">
    {{-- Total Users Card --}}
    <div class="card stat-card" style="text-align:center; padding:20px;">
        <div class="stat-icon" style="font-size:1.8rem; color:var(--accent); margin-bottom:8px;">
            <i class="ri-group-line"></i>
        </div>
        <div class="stat-value" style="font-size:1.5rem; font-weight:800; color:var(--text-primary);">{{ $totalUsers }}</div>
        <div class="stat-label" style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px;">Jami foydalanuvchi</div>
    </div>

    {{-- Active Today Card --}}
    <div class="card stat-card" style="text-align:center; padding:20px;">
        <div class="stat-icon" style="font-size:1.8rem; color:var(--success); margin-bottom:8px;">
            <i class="ri-user-follow-line"></i>
        </div>
        <div class="stat-value" style="font-size:1.5rem; font-weight:800; color:var(--text-primary);">{{ $activeUsersToday }}</div>
        <div class="stat-label" style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px;">Bugun faol</div>
    </div>

    {{-- Total Deeds Card --}}
    <div class="card stat-card" style="text-align:center; padding:20px;">
        <div class="stat-icon" style="font-size:1.8rem; color:var(--gold); margin-bottom:8px;">
            <i class="ri-checkbox-circle-line"></i>
        </div>
        <div class="stat-value" style="font-size:1.5rem; font-weight:800; color:var(--text-primary);">{{ $totalDeedsLogged }}</div>
        <div class="stat-label" style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px;">Bajarilgan amallar</div>
    </div>

    {{-- Gender Split Card --}}
    <div class="card stat-card" style="text-align:center; padding:20px;">
        <div class="stat-icon" style="font-size:1.8rem; color:var(--accent-light); margin-bottom:8px;">
            <i class="ri-genderless-line"></i>
        </div>
        <div style="display:flex; justify-content:center; gap:15px; align-items:baseline;">
            <div>
                <span style="display:block; font-size:1.2rem; font-weight:700; color:var(--text-primary);">{{ $maleUsers }}</span>
                <span style="font-size:0.6rem; color:var(--text-muted);">Erkak</span>
            </div>
            <div style="width:1px; height:20px; background:var(--white-10);"></div>
            <div>
                <span style="display:block; font-size:1.2rem; font-weight:700; color:var(--text-primary);">{{ $femaleUsers }}</span>
                <span style="font-size:0.6rem; color:var(--text-muted);">Ayol</span>
            </div>
        </div>
        <div class="stat-label" style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-top:8px;">Jins taqsimoti</div>
    </div>
</div>

<div class="admin-content-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
    {{-- Recent Users --}}
    <div class="admin-section">
        <h3 class="section-title"><i class="ri-user-add-line"></i> Oxirgi kelganlar</h3>
        <div class="card" style="padding:0; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse; font-size:0.85rem;">
                <thead style="background:var(--white-5); color:var(--text-muted);">
                    <tr>
                        <th style="padding:12px; text-align:left;">Foydalanuvchi</th>
                        <th style="padding:12px; text-align:left;">Sana</th>
                        <th style="padding:12px; text-align:left;">Tel/Email</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentUsers as $user)
                    <tr style="border-bottom:1px solid var(--white-5);">
                        <td style="padding:12px; display:flex; align-items:center; gap:10px;">
                            <div class="sidebar-avatar" style="width:28px; height:28px; font-size:0.75rem;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <span>{{ $user->name }}</span>
                        </td>
                        <td style="padding:12px; color:var(--text-muted);">{{ $user->created_at->format('d.m.Y') }}</td>
                        <td style="padding:12px; color:var(--text-secondary); font-size:0.75rem;">
                            {{ $user->phone ?? $user->email }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Live Activity Feed --}}
    <div class="admin-section">
        <h3 class="section-title"><i class="ri-pulse-line"></i> Jonli faollik</h3>
        <div class="card" style="padding:15px; max-height:480px; overflow-y:auto;">
            <div class="activity-timeline">
                @foreach($recentActivity as $activity)
                <div class="activity-item" style="padding-bottom:15px; margin-bottom:15px; border-bottom:1px dashed var(--white-10); display:flex; gap:12px; align-items:flex-start;">
                    <div style="padding:8px; border-radius:10px; background:var(--accent-bg); color:var(--accent);">
                        <i class="{{ $activity->habit->icon ?? 'ri-checkbox-circle-line' }}"></i>
                    </div>
                    <div style="flex:1;">
                        <div style="font-size:0.85rem; font-weight:600; color:var(--text-primary);">
                            {{ $activity->dailyLog->user->name ?? 'Noma\'lum' }}
                        </div>
                        <div style="font-size:0.8rem; color:var(--text-secondary);">
                            {{ $activity->habit->name ?? 'Amal' }} — bajarildi
                        </div>
                        <div style="font-size:0.65rem; color:var(--text-muted); margin-top:4px;">
                            {{ $activity->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
    .stat-card {
        transition: var(--transition);
        border: 1px solid var(--white-10);
    }
    .stat-card:hover {
        transform: translateY(-5px);
        border-color: var(--gold-border);
        background: var(--bg-card-hover);
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    @media (max-width: 768px) {
        .admin-content-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection
