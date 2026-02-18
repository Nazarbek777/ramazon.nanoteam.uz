@extends('layouts.app')
@section('title', 'Maqsadlar')

@section('content')
<div class="page-header">
    <h2><i class="ri-focus-3-line"></i> Maqsadlar</h2>
    <p>Ramazon uchun maqsad qo'ying va taraqqiyotingizni kuzating</p>
</div>

@if($goals->count() > 0)
    @foreach($goals as $goal)
        <div class="card goal-card">
            <div class="goal-header">
                <div>
                    <div class="goal-title">
                        @if($goal->habit)
                            <i class="{{ $goal->habit->icon }}"></i>
                        @else
                            <i class="ri-flag-line"></i>
                        @endif
                        {{ $goal->title }}
                    </div>
                    <div class="goal-meta">
                        {{ number_format($goal->current_value) }} / {{ number_format($goal->target_value) }} {{ $goal->unit }}
                        @if($goal->remaining > 0)
                            — <span class="text-gold">{{ number_format($goal->remaining) }} {{ $goal->unit }} qoldi</span>
                        @else
                            — <span class="text-success"><i class="ri-check-double-line"></i> Maqsadga erishildi!</span>
                        @endif
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:12px;">
                    <span style="font-weight:700;font-size:1.05rem;color:var(--gold);">{{ $goal->progress_percent }}%</span>
                    <form method="POST" action="{{ route('goals.destroy', $goal) }}" onsubmit="return confirm('Rostdan o\'chirmoqchimisiz?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" style="padding:6px 10px;">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar" style="width: {{ $goal->progress_percent }}%"></div>
            </div>
        </div>
    @endforeach
@else
    <div class="card text-center" style="padding:40px;">
        <div class="stat-icon" style="margin:0 auto 12px;"><i class="ri-focus-3-line"></i></div>
        <p class="text-muted" style="margin-bottom:4px;">Hali hech qanday maqsad qo'yilmagan</p>
        <p class="text-muted" style="font-size:0.8rem;">Quyidagi formadan birinchi maqsadingizni qo'shing!</p>
    </div>
@endif

{{-- Add goal form --}}
<div class="mt-24">
    <h3 class="section-title"><i class="ri-add-circle-line"></i> Yangi maqsad qo'shish</h3>
    <div class="card">
        <form method="POST" action="{{ route('goals.store') }}">
            @csrf

            @if($errors->any())
                <div class="alert alert-error"><i class="ri-error-warning-fill"></i> {{ $errors->first() }}</div>
            @endif

            <div class="form-group">
                    <label class="form-label">Maqsad nomi</label>
                    <input type="text" name="title" class="form-input" placeholder="Masalan: 30 kun ro'za tutish" required>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div class="form-group">
                        <label class="form-label">Maqsad soni</label>
                        <input type="number" name="target_value" class="form-input" placeholder="30" min="1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Birlik</label>
                        <select name="unit" class="form-select">
                            <option value="kun">kun</option>
                            <option value="marta">marta</option>
                            <option value="sahifa">sahifa</option>
                            <option value="so'm">so'm</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Bog'liq amal</label>
                    <select name="habit_id" class="form-select">
                        <option value="">Umumiy (streak)</option>
                        @foreach($habits as $habit)
                            <option value="{{ $habit->id }}">{{ $habit->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-gold" style="width:100%;"><i class="ri-focus-3-line"></i> Qo'shish</button>
        </form>
    </div>
</div>

{{-- Suggestions --}}
<div class="mt-24">
    <h3 class="section-title"><i class="ri-lightbulb-line"></i> Tavsiya etiladigan maqsadlar</h3>
    <div class="stats-grid">
        <div class="card stat-card" style="cursor:default;">
            <div class="stat-icon"><i class="ri-restaurant-line"></i></div>
            <div style="font-weight:600;font-size:0.88rem;">30 kun ro'za</div>
            <div class="stat-label">Butun Ramazon</div>
        </div>
        <div class="card stat-card" style="cursor:default;">
            <div class="stat-icon"><i class="ri-book-open-line"></i></div>
            <div style="font-weight:600;font-size:0.88rem;">1 Qur'on xatm</div>
            <div class="stat-label">604 sahifa</div>
        </div>
        <div class="card stat-card" style="cursor:default;">
            <div class="stat-icon"><i class="ri-building-4-line"></i></div>
            <div style="font-weight:600;font-size:0.88rem;">30 kun namoz</div>
            <div class="stat-label">5 mahal to'liq</div>
        </div>
        <div class="card stat-card" style="cursor:default;">
            <div class="stat-icon"><i class="ri-fire-line"></i></div>
            <div style="font-weight:600;font-size:0.88rem;">30 kunlik streak</div>
            <div class="stat-label">Ketma-ket bajarish</div>
        </div>
    </div>
</div>
@endsection
