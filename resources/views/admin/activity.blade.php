@extends('layouts.app')
@section('title', 'Faollik Tarixi')

@section('content')
<div class="page-header" style="margin-bottom:24px; display:flex; align-items:center; justify-content:space-between;">
    <div>
        <h2 style="font-size:1.5rem;color:var(--gold);"><i class="ri-pulse-line"></i> Faollik Tarixi</h2>
        <p class="text-muted">Foydalanuvchilar tomonidan bajarilgan barcha amallar</p>
    </div>
    <a href="{{ route('admin.index') }}" class="btn btn-outline btn-sm">
        <i class="ri-arrow-left-line"></i> Orqaga
    </a>
</div>

<div class="card" style="padding:0; overflow:hidden; margin-bottom:20px;">
    <table style="width:100%; border-collapse:collapse; font-size:0.9rem;">
        <thead style="background:var(--white-5); color:var(--text-muted); border-bottom:1px solid var(--white-10);">
            <tr>
                <th style="padding:15px; text-align:left;">Vaqt</th>
                <th style="padding:15px; text-align:left;">Foydalanuvchi</th>
                <th style="padding:15px; text-align:left;">Bajarilgan amal</th>
                <th style="padding:15px; text-align:left;">Qiymat</th>
            </tr>
        </thead>
        <tbody>
            @foreach($activities as $activity)
            <tr style="border-bottom:1px solid var(--white-5);">
                <td style="padding:15px; color:var(--text-muted); font-size:0.8rem;">
                    {{ $activity->created_at->format('d.m.Y H:i') }}
                    <span style="display:block; font-size:0.7rem; opacity:0.6;">{{ $activity->created_at->diffForHumans() }}</span>
                </td>
                <td style="padding:15px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div class="sidebar-avatar" style="width:30px; height:30px; font-size:0.8rem;">
                            {{ strtoupper(substr($activity->dailyLog->user->name ?? '?', 0, 1)) }}
                        </div>
                        <span style="font-weight:600;">{{ $activity->dailyLog->user->name ?? 'Noma\'lum' }}</span>
                    </div>
                </td>
                <td style="padding:15px;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <i class="{{ $activity->habit->icon ?? 'ri-checkbox-circle-line' }}" style="color:var(--accent);"></i>
                        <span>{{ $activity->habit->name ?? 'Amal' }}</span>
                    </div>
                </td>
                <td style="padding:15px;">
                    @if($activity->habit && $activity->habit->type === 'number')
                        <span class="badge" style="background:var(--accent-bg); color:var(--accent); padding:4px 8px; border-radius:6px; font-weight:700;">
                            {{ $activity->value }}
                        </span>
                    @else
                        <span style="color:var(--success);"><i class="ri-check-double-line"></i> Bajarildi</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="pagination-wrapper" style="margin-top:20px;">
    {{ $activities->links() }}
</div>

<style>
    .pagination {
        display: flex;
        gap: 8px;
        list-style: none;
        justify-content: center;
    }
    .page-item .page-link {
        padding: 8px 16px;
        border-radius: 8px;
        background: var(--bg-card);
        border: 1px solid var(--white-10);
        color: var(--text-primary);
        text-decoration: none;
        transition: var(--transition);
    }
    .page-item.active .page-link {
        background: var(--gold);
        color: #fff;
        border-color: var(--gold);
    }
    .page-item .page-link:hover:not(.active) {
        background: var(--white-10);
    }
    .page-item.disabled .page-link {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>
@endsection
