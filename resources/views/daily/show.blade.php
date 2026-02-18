@extends('layouts.app')
@section('title', 'Kunlik Amallar')

@section('content')
<div class="page-header">
    <h2>✅ Kunlik Amallar</h2>
    <p>Bugungi ibodatlaringizni belgilab boring</p>
</div>

{{-- Sana navigatsiyasi --}}
<div class="date-nav">
    <a href="{{ route('daily.show', ['date' => $prevDate]) }}">←</a>
    <div class="current-date">
        {{ $currentDate->format('d.m.Y') }}
        @if($isToday)
            <span class="today-badge">Bugun</span>
        @endif
    </div>
    @if(!$isToday)
        <a href="{{ route('daily.show', ['date' => $nextDate]) }}">→</a>
    @else
        <span style="width:40px;"></span>
    @endif
</div>

@if($isFuture)
    <div class="card text-center" style="padding:40px;">
        <span style="font-size:3rem;display:block;margin-bottom:12px;">⏳</span>
        <p class="text-muted">Bu sana hali kelmagan.</p>
        <a href="{{ route('daily.show') }}" class="btn btn-primary btn-sm" style="margin-top:12px;">Bugunga qaytish</a>
    </div>
@else
    <form method="POST" action="{{ route('daily.store') }}">
        @csrf
        <input type="hidden" name="date" value="{{ $currentDate->format('Y-m-d') }}">

        <div class="card mb-24">
            <ul class="checklist">
                @foreach($habits as $habit)
                    <li class="checklist-item">
                        <span class="habit-icon">{{ $habit->icon }}</span>
                        <span class="habit-name">{{ $habit->name }}</span>
                        <span class="habit-input">
                            @if($habit->type === 'checkbox')
                                <div class="custom-check">
                                    <input type="checkbox"
                                           name="habit_{{ $habit->id }}"
                                           id="habit_{{ $habit->id }}"
                                           {{ ($completedMap[$habit->id] ?? false) ? 'checked' : '' }}>
                                    <span class="checkmark"></span>
                                </div>
                            @else
                                <input type="number"
                                       name="value_{{ $habit->id }}"
                                       class="number-input"
                                       value="{{ $valuesMap[$habit->id] ?? '' }}"
                                       min="0"
                                       max="999"
                                       placeholder="0">
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Izoh --}}
        <div class="card mb-24">
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">📝 Izoh (ixtiyoriy)</label>
                <textarea name="notes"
                          class="form-input"
                          rows="2"
                          placeholder="Bugungi kun haqida izoh..."
                          style="resize:vertical;">{{ $log->notes ?? '' }}</textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-gold" style="width:100%;">
            💾 Saqlash
        </button>
    </form>

    {{-- Qo'shimcha amal qo'shish --}}
    <div class="mt-24">
        <h3 class="section-title">⭐ Qo'shimcha amal qo'shish</h3>
        <div class="card add-form-card">
            <form method="POST" action="{{ route('daily.custom-habit') }}" class="inline-form">
                @csrf
                <div class="form-group">
                    <label class="form-label">Amal nomi</label>
                    <input type="text" name="name" class="form-input" placeholder="Masalan: Tahajjud" required>
                </div>
                <div class="form-group" style="min-width:120px;">
                    <label class="form-label">Turi</label>
                    <select name="type" class="form-select">
                        <option value="checkbox">Belgilash ✓</option>
                        <option value="number">Raqam 🔢</option>
                    </select>
                </div>
                <div class="form-group" style="flex:0;">
                    <button type="submit" class="btn btn-primary btn-sm">+ Qo'shish</button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection
