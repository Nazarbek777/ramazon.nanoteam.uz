@extends('layouts.app')
@section('title', 'Kunlik Amallar')

@section('content')
@if($ramadan['is_ramadan'])
    <div style="text-align:center;margin-bottom:14px;">
        <span style="background:linear-gradient(135deg,rgba(212,168,67,0.15),rgba(212,168,67,0.05));border:1px solid rgba(212,168,67,0.25);padding:5px 16px;border-radius:50px;font-size:0.82rem;font-weight:600;color:var(--gold);display:inline-flex;align-items:center;gap:5px;">
            <i class="ri-moon-clear-fill"></i> Ramazon {{ $ramadan['day'] }}-kuni
        </span>
    </div>
@endif

<div class="page-header" style="margin-bottom:12px;">
    <h2><i class="ri-checkbox-circle-line"></i> Kunlik Amallar</h2>
</div>

<div class="date-nav">
    <a href="{{ route('daily.show', ['date' => $prevDate]) }}"><i class="ri-arrow-left-s-line"></i></a>
    <div class="current-date">
        {{ $currentDate->format('d.m.Y') }}
        @if($isToday) <span class="today-badge">Bugun</span> @endif
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
    {{-- Progress bar --}}
    <div id="progressSection" style="margin-bottom:16px;">
        <div class="progress-bar-container">
            <div class="progress-bar" id="progressBar" style="width: 0%; transition: width 0.5s ease;"></div>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px;">
            <span class="text-muted" style="font-size:0.75rem;" id="progressText">0/0</span>
            <span style="font-size:0.75rem;font-weight:700;color:var(--gold);" id="progressPercent">0%</span>
        </div>
    </div>

    {{-- Checklist --}}
    <div class="card mb-24">
        <ul class="checklist" id="habitList">
            @foreach($habits as $habit)
                <li class="checklist-item" id="item-{{ $habit->id }}" data-habit-id="{{ $habit->id }}" data-type="{{ $habit->type }}">
                    <div class="habit-icon"><i class="{{ $habit->icon }}"></i></div>
                    <span class="habit-name">{{ $habit->name }}</span>
                    <span class="habit-input">
                        @if($habit->type === 'checkbox')
                            <div class="custom-check">
                                <input type="checkbox"
                                       id="habit_{{ $habit->id }}"
                                       data-habit-id="{{ $habit->id }}"
                                       {{ ($completedMap[$habit->id] ?? false) ? 'checked' : '' }}
                                       onchange="toggleHabit({{ $habit->id }}, this.checked)">
                                <span class="checkmark"></span>
                            </div>
                        @else
                            <input type="number"
                                   id="value_{{ $habit->id }}"
                                   data-habit-id="{{ $habit->id }}"
                                   class="number-input"
                                   value="{{ $valuesMap[$habit->id] ?? '' }}"
                                   min="0" max="999"
                                   placeholder="0"
                                   onchange="toggleHabit({{ $habit->id }}, null, this.value)">
                        @endif
                    </span>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Izoh (oddiy form) --}}
    <form method="POST" action="{{ route('daily.store') }}">
        @csrf
        <input type="hidden" name="date" value="{{ $currentDate->format('Y-m-d') }}">
        @foreach($habits as $habit)
            @if($habit->type === 'checkbox')
                <input type="hidden" name="habit_{{ $habit->id }}" id="form_habit_{{ $habit->id }}" {{ ($completedMap[$habit->id] ?? false) ? 'value=on' : 'disabled' }}>
            @else
                <input type="hidden" name="value_{{ $habit->id }}" id="form_value_{{ $habit->id }}" value="{{ $valuesMap[$habit->id] ?? '' }}">
            @endif
        @endforeach
        <div class="card mb-24">
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label"><i class="ri-edit-line"></i> Izoh (ixtiyoriy)</label>
                <textarea name="notes" class="form-input" rows="2" placeholder="Bugungi kun haqida..." style="resize:vertical;">{{ $log->notes ?? '' }}</textarea>
            </div>
        </div>
        <div class="sticky-save">
            <button type="submit" class="btn btn-gold" style="width:100%;">
                <i class="ri-save-line"></i> Izohni saqlash
            </button>
        </div>
    </form>

    {{-- Yangi amal --}}
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

{{-- Status toast --}}
<div id="toast" style="position:fixed;bottom:76px;left:50%;transform:translateX(-50%) translateY(20px);background:var(--success);color:#fff;padding:8px 20px;border-radius:30px;font-size:0.8rem;font-weight:600;opacity:0;transition:all 0.3s;z-index:999;pointer-events:none;display:flex;align-items:center;gap:6px;">
    <i class="ri-check-line"></i> <span id="toastText">Saqlandi!</span>
</div>
@endsection

@section('scripts')
<script>
    const TOGGLE_URL = '{{ route("daily.toggle") }}';
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const DATE = '{{ $currentDate->format("Y-m-d") }}';

    // Boshlang'ich holatni hisoblash
    let totalHabits = {{ $habits->count() }};
    let completedCount = {{ isset($log) && $log ? $log->items->where('is_completed', true)->count() : 0 }};
    updateProgress(completedCount, totalHabits);

    function toggleHabit(habitId, isChecked, value) {
        const item = document.getElementById('item-' + habitId);
        const isCheckbox = item.dataset.type === 'checkbox';
        const completed = isCheckbox ? isChecked : (parseInt(value) > 0);

        // Darhol vizual yangilash (optimistic UI)
        if (completed) {
            item.style.background = 'var(--accent-bg)';
            item.style.borderColor = 'var(--accent)';
            if (isCheckbox) completedCount++;
            showToast('✓ Bajarildi!');
        } else {
            item.style.background = '';
            item.style.borderColor = '';
            if (isCheckbox) completedCount--;
        }

        if (isCheckbox) {
            updateProgress(completedCount, totalHabits);
        }

        // AJAX so'rov (background)
        const body = {
            habit_id: habitId,
            date: DATE,
            is_completed: completed
        };
        if (!isCheckbox) body.value = value;

        fetch(TOGGLE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json'
            },
            body: JSON.stringify(body)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                completedCount = data.completed;
                totalHabits = data.total > 0 ? data.total : totalHabits;
                updateProgress(data.completed, data.total);
            }
        })
        .catch(err => {
            console.error('Toggle xatosi:', err);
            showToast('Xatolik! Qaytadan urinib ko\'ring', true);
        });
    }

    function updateProgress(completed, total) {
        const percent = total > 0 ? Math.round((completed / total) * 100) : 0;
        document.getElementById('progressBar').style.width = percent + '%';
        document.getElementById('progressText').textContent = completed + '/' + total;
        document.getElementById('progressPercent').textContent = percent + '%';
    }

    function showToast(text, isError) {
        const toast = document.getElementById('toast');
        const toastText = document.getElementById('toastText');
        toastText.textContent = text;
        toast.style.background = isError ? 'var(--danger)' : 'var(--success)';
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(-50%) translateY(0)';
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(-50%) translateY(20px)';
        }, 1500);
    }
</script>
@endsection
