@extends('layouts.app')
@section('title', 'Fikrlar Boshqaruvi')

@section('content')
<div class="page-header" style="margin-bottom:24px; display:flex; align-items:center; justify-content:space-between;">
    <div>
        <h2 style="font-size:1.5rem;color:var(--gold);"><i class="ri-feedback-line"></i> Fikrlar Boshqaruvi</h2>
        <p class="text-muted">Foydalanuvchilar tomonidan bildirilgan anonim fikrlar</p>
    </div>
    <a href="{{ route('admin.index') }}" class="btn btn-outline btn-sm">
        <i class="ri-arrow-left-line"></i> Orqaga
    </a>
</div>

<div class="card" style="padding:0; overflow:hidden;">
    <table style="width:100%; border-collapse:collapse; font-size:0.9rem;">
        <thead style="background:var(--white-5); color:var(--text-muted); border-bottom:1px solid var(--white-10);">
            <tr>
                <th style="padding:15px; text-align:left;">Sana</th>
                <th style="padding:15px; text-align:left;">Fikr mazmuni</th>
                <th style="padding:15px; text-align:left;">Turi</th>
                <th style="padding:15px; text-align:left;">Status</th>
                <th style="padding:15px; text-align:right;">Amallar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($feedbacks as $feedback)
            <tr style="border-bottom:1px solid var(--white-5);">
                <td style="padding:15px; color:var(--text-muted); font-size:0.8rem; vertical-align:top;">
                    {{ $feedback->created_at->format('d.m.Y H:i') }}
                    <span style="display:block; opacity:0.6;">{{ $feedback->ip_address }}</span>
                </td>
                <td style="padding:15px; vertical-align:top; max-width:400px;">
                    <div style="color:var(--text-primary); line-height:1.5;">{{ $feedback->content }}</div>
                </td>
                <td style="padding:15px; vertical-align:top;">
                    @if($feedback->is_public)
                        <span class="badge" style="background:var(--accent-bg); color:var(--accent); padding:4px 8px; border-radius:4px; font-size:0.7rem;">OMMAVIY</span>
                    @else
                        <span class="badge" style="background:var(--white-10); color:var(--text-muted); padding:4px 8px; border-radius:4px; font-size:0.7rem;">SHAXSIY</span>
                    @endif
                </td>
                <td style="padding:15px; vertical-align:top;">
                    @if($feedback->is_approved)
                        <span style="color:var(--success); font-size:0.8rem;"><i class="ri-checkbox-circle-line"></i> Tasdiqlangan</span>
                    @elseif($feedback->is_public)
                        <span style="color:var(--gold); font-size:0.8rem;"><i class="ri-time-line"></i> Kutilmoqda</span>
                    @else
                        <span style="color:var(--text-muted); font-size:0.8rem;"><i class="ri-eye-off-line"></i> Faqat admin uchun</span>
                    @endif
                </td>
                <td style="padding:15px; text-align:right; vertical-align:top;">
                    <div style="display:flex; gap:8px; justify-content:flex-end;">
                        @if(!$feedback->is_approved && $feedback->is_public)
                        <form action="{{ route('admin.feedback.approve', $feedback->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm" style="background:var(--success); color:#fff; padding:5px 10px;">
                                <i class="ri-check-line"></i>
                            </button>
                        </form>
                        @endif
                        
                        <form action="{{ route('admin.feedback.delete', $feedback->id) }}" method="POST" onsubmit="return confirm('O\'chirmoqchimisiz?')">
                            @csrf
                            <button type="submit" class="btn btn-sm" style="background:var(--danger); color:#fff; padding:5px 10px;">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if($feedbacks->isEmpty())
    <div style="text-align:center; padding:40px; color:var(--text-muted);">
        Hozircha hech qanday fikr yo'q.
    </div>
@endif

<div class="pagination-wrapper" style="margin-top:20px;">
    {{ $feedbacks->links() }}
</div>
@endsection
