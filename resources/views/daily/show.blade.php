@extends('layouts.app')
@section('title', 'Kunlik Amallar')

@section('content')
{{-- Ramazon kuni --}}
@if($ramadan['is_ramadan'])
    <div style="text-align:center;margin-bottom:16px;">
        <span style="background:linear-gradient(135deg,rgba(212,168,67,0.15),rgba(212,168,67,0.05));border:1px solid rgba(212,168,67,0.25);padding:6px 18px;border-radius:50px;font-size:0.85rem;font-weight:600;color:var(--gold);display:inline-flex;align-items:center;gap:6px;">
            <i class="ri-moon-clear-fill"></i> Ramazon {{ $ramadan['day'] }}-kuni
        </span>
    </div>
@endif

<div class="page-header" style="margin-bottom:16px;">
    <h2><i class="ri-checkbox-circle-line"></i> Kunlik Amallar</h2>
</div>

{{-- Sana navigatsiyasi --}}
<div class="date-nav">
    <a href="{{ route('daily.show', ['date' => $prevDate]) }}"><i class="ri-arrow-left-s-line"></i></a>
    <div class="current-date">
        {{ $currentDate->format('d.m.Y') }}
        @if($isToday)
            <span class="today-badge">Bugun</span>
        @endif
    </div>
    @if(!$isToday)
        <a href="{{ route('daily.show', ['date' => $nextDate]) }}"><i class="ri-arrow-right-s-line"></i></a>
    @else
        <span style="width:36px;"></span>
    @endif
</div>

@if($isFuture)
    <div class="card text-center" style="padding:40px;">
        <i class="ri-time-line" style="font-size:2.5rem;color:var(--text-muted);"></i>
        <p class="text-muted" style="margin-top:12px;">Bu sana hali kelmagan</p>
        <a href="{{ route('daily.show') }}" class="btn btn-primary" style="margin-top:12px;">
            <i class="ri-arrow-left-line"></i> Bugunga qaytish
        </a>
    </div>
@else
    <form method="POST" action="{{ route('daily.store') }}" id="dailyForm">
        @csrf
        <input type="hidden" name="date" value="{{ $currentDate->format('Y-m-d') }}">

        <div class="card mb-24">
            <ul class="checklist">
                @foreach($habits as $habit)
                    <li class="checklist-item">
                        <div class="habit-icon"><i class="{{ $habit->icon }}"></i></div>
                        <span class="habit-name">{{ $habit->name }}</span>
                        <span class="habit-input">
                            @if($habit->type === 'checkbox')
                                <div class="custom-check">
                                    <input type="checkbox"
                                           name="habit_{{ $habit->id }}"
                                           id="habit_{{ $habit->id }}"
                                           {{ ($completedMap[$habit->id] ?? false) ? 'checked' : '' }}
                                           onchange="autoSave()">
                                    <span class="checkmark"></span>
                                </div>
                            @else
                                <input type="number"
                                       name="value_{{ $habit->id }}"
                                       class="number-input"
                                       value="{{ $valuesMap[$habit->id] ?? '' }}"
                                       min="0" max="999"
                                       placeholder="0"
                                       onchange="autoSave()">
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Izoh --}}
        <div class="card mb-24">
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label"><i class="ri-edit-line"></i> Izoh (ixtiyoriy)</label>
                <textarea name="notes" class="form-input" rows="2" placeholder="Bugungi kun haqida..." style="resize:vertical;">{{ $log->notes ?? '' }}</textarea>
            </div>
        </div>

        {{-- Saqlash tugmasi — doim ko'rinib turadi --}}
        <div class="sticky-save">
            <button type="submit" class="btn btn-gold" id="saveBtn" style="width:100%;">
                <i class="ri-save-line"></i> <span id="saveBtnText">Saqlash</span>
            </button>
        </div>
    </form>

    {{-- Qo'shimcha amal --}}
    <div class="mt-24" style="padding-bottom:80px;">
        <h3 class="section-title"><i class="ri-add-circle-line"></i> Yangi amal qo'shish</h3>
        <div class="card add-form-card">
            <form method="POST" action="{{ route('daily.custom-habit') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Amal nomi</label>
                    <input type="text" name="name" class="form-input" placeholder="Masalan: Tahajjud" required>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div class="form-group">
                        <label class="form-label">Turi</label>
                        <select name="type" class="form-select">
                            <option value="checkbox">Belgilash</option>
                            <option value="number">Raqam</option>
                        </select>
                    </div>
                    <div class="form-group" style="display:flex;align-items:flex-end;">
                        <button type="submit" class="btn btn-primary" style="width:100%;"><i class="ri-add-line"></i> Qo'shish</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection

@section('scripts')
<script>
    let saveTimeout;

    function autoSave() {
        clearTimeout(saveTimeout);
        // Tugma animatsiyasi
        const btn = document.getElementById('saveBtn');
        const txt = document.getElementById('saveBtnText');
        txt.textContent = 'Saqlanmoqda...';
        btn.style.opacity = '0.7';

        saveTimeout = setTimeout(() => {
            document.getElementById('dailyForm').submit();
        }, 800);
    }
</script>
@endsection
