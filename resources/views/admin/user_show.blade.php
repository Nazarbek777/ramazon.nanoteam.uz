@extends('layouts.app')
@section('title', 'Foydalanuvchi Faolligi')

@section('content')
<div class="page-header" style="margin-bottom:24px; display:flex; align-items:center; justify-content:space-between;">
    <div style="display:flex; align-items:center; gap:16px;">
        <div class="sidebar-avatar" style="width:60px; height:60px; font-size:1.5rem;">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div>
            <h2 style="font-size:1.5rem;color:var(--gold); margin:0;">{{ $user->name }}</h2>
            <p class="text-muted" style="margin:4px 0 0 0;">{{ $user->phone ?? $user->email }} · Ro'yxatdan o'tgan: {{ $user->created_at->format('d.m.Y') }}</p>
        </div>
    </div>
    <a href="{{ route('admin.index') }}" class="btn btn-outline btn-sm">
        <i class="ri-arrow-left-line"></i> Orqaga
    </a>
</div>

<div class="admin-content-grid" style="display:grid; grid-template-columns: 1fr 2fr; gap:20px;">
    {{-- User Info Summary --}}
    <div class="info-sidebar">
        <div class="card" style="padding:20px; margin-bottom:20px;">
            <h4 style="margin-top:0; color:var(--text-primary); border-bottom:1px solid var(--white-10); padding-bottom:10px;">Ma'lumotlar</h4>
            <div style="display:flex; flex-direction:column; gap:12px; margin-top:15px;">
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:var(--text-muted);">Jinsi:</span>
                    <span style="color:var(--text-primary); font-weight:600;">{{ $user->gender === 'male' ? 'Erkak' : 'Ayol' }}</span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:var(--text-muted);">ID:</span>
                    <span style="color:var(--text-primary);">#{{ $user->id }}</span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:var(--text-muted);">Oxirgi faollik:</span>
                    <span style="color:var(--text-primary);">{{ $user->updated_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>
        
        <div class="card" style="padding:20px; background:rgba(212,168,67,0.05); border-color:var(--gold-border);">
            <h4 style="margin-top:0; color:var(--gold);">Statistika</h4>
            <div style="text-align:center; padding:10px 0;">
                <div style="font-size:2rem; font-weight:800; color:var(--gold);">{{ $activities->where('action', '!=', 'page_visit')->count() }}</div>
                <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase;">Jami amallar</div>
            </div>
        </div>
    </div>

    {{-- Activity Timeline --}}
    <div class="activity-history">
        <h3 class="section-title"><i class="ri-history-line"></i> Faollik xronologiyasi</h3>
        <div class="card" style="padding:0; overflow:hidden;">
            <div style="padding:20px;">
                @foreach($activities as $activity)
                <div style="display:flex; gap:15px; padding-bottom:20px; border-left:2px solid var(--white-10); margin-left:10px; padding-left:20px; position:relative;">
                    <div style="position:absolute; left:-7px; top:0; width:12px; height:12px; border-radius:50%; background:{{ $activity->action === 'page_visit' ? 'var(--text-muted)' : 'var(--gold)' }}; border:2px solid var(--bg-card);"></div>
                    
                    <div style="flex:1;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                            <div style="font-size:0.9rem; font-weight:700; color:var(--text-primary);">
                                @if($activity->action === 'page_visit')
                                    Sahifaga kirdi: <span style="font-weight:400; opacity:0.8;">/{{ $activity->path }}</span>
                                @elseif($activity->action === 'toggle_deed')
                                    Amalni o'zgartirdi: <span style="color:var(--gold);">{{ $activity->data['key'] ?? '' }}</span>
                                @elseif($activity->action === 'toggle_habit')
                                    Odatni o'zgartirdi: <span style="color:var(--success);">{{ $activity->data['habit_name'] ?? '' }}</span>
                                @endif
                            </div>
                            <span style="font-size:0.75rem; color:var(--text-muted);">{{ $activity->created_at->format('H:i') }}</span>
                        </div>
                        
                        <div style="font-size:0.8rem; color:var(--text-secondary); margin-top:4px;">
                            @if($activity->action === 'toggle_deed' || $activity->action === 'toggle_habit')
                                Status: <span style="font-weight:700;">{{ ($activity->data['value'] ?? $activity->data['is_completed'] ?? false) ? 'Bajarildi' : 'Bekor qilindi' }}</span>
                            @else
                                Brauzer: <span style="font-size:0.7rem; opacity:0.7;">{{ Str::limit($activity->user_agent, 50) }}</span>
                            @endif
                        </div>
                        
                        @if($loop->first || $activity->created_at->format('Y-m-d') !== $activities[$loop->index-1]->created_at->format('Y-m-d'))
                            <div style="font-size:0.7rem; color:var(--gold); font-weight:800; text-transform:uppercase; margin-top:10px; background:var(--gold-bg); display:inline-block; padding:2px 8px; border-radius:4px;">
                                {{ $activity->created_at->translatedFormat('d F, Y') }}
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach
                
                @if($activities->isEmpty())
                    <div style="text-align:center; padding:40px; color:var(--text-muted);">
                        <i class="ri-inbox-line" style="font-size:3rem; opacity:0.3; display:block; margin-bottom:10px;"></i>
                        Hali hech qanday faollik yo'q
                    </div>
                @endif
            </div>
        </div>
        
        <div class="pagination-wrapper" style="margin-top:20px;">
            {{ $activities->links() }}
        </div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .admin-content-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection
