@extends('layouts.app')
@section('title', 'Jamoatchilik Fikri')

@section('content')
<div class="page-header" style="text-align:center; margin-bottom:30px;">
    <h2 style="font-size:1.8rem; color:var(--gold);"><i class="ri-chat-voice-line"></i> Jamoatchilik Fikri</h2>
    <p class="text-muted">Foydalanuvchilarimiz tomonidan bildirilgan anonim fikr va mulohazalar</p>
</div>

<div class="feedback-grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap:20px;">
    @foreach($feedbacks as $feedback)
    <div class="card feedback-card" style="padding:20px; position:relative; overflow:hidden; border:1px solid var(--white-10); transition:var(--transition);">
        <div class="quote-icon" style="position:absolute; top:-10px; right:10px; font-size:4rem; opacity:0.05; color:var(--gold);">
            <i class="ri-double-quotes-r"></i>
        </div>
        <div style="font-size:0.95rem; line-height:1.6; color:var(--text-primary); margin-bottom:15px; font-style:italic;">
            "{{ $feedback->content }}"
        </div>
        <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--white-5); padding-top:10px;">
            <span style="font-size:0.75rem; color:var(--text-muted);">
                <i class="ri-user-smile-line"></i> Anonim foydalanuvchi
            </span>
            <span style="font-size:0.7rem; color:var(--text-muted);">
                {{ $feedback->created_at->translatedFormat('d F, Y') }}
            </span>
        </div>
    </div>
    @endforeach
</div>

@if($feedbacks->isEmpty())
    <div style="text-align:center; padding:60px 0;">
        <i class="ri-chat-history-line" style="font-size:4rem; color:var(--white-10); display:block; margin-bottom:20px;"></i>
        <p class="text-muted">Hozircha ommaviy fikrlar yo'q. Birinchilardan bo'lib yozing! Mashallah!</p>
        <a href="{{ route('dashboard') }}" class="btn btn-gold" style="margin-top:20px;">Fikr bildirish</a>
    </div>
@endif

<div class="pagination-wrapper" style="margin-top:40px; display:flex; justify-content:center;">
    {{ $feedbacks->links() }}
</div>

<style>
    .feedback-card:hover {
        transform: translateY(-5px);
        border-color: var(--gold-border);
        background: var(--bg-card-hover);
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
</style>
@endsection
