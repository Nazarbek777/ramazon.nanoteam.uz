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

    {{-- Ibodatlar (Namoz & Quran) --}}
    <div style="margin-top:8px;margin-bottom:8px;display:flex;align-items:center;gap:10px;">
        <span style="background:var(--gold-bg);color:var(--gold);padding:3px 10px;border-radius:50px;font-size:0.65rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;border:1px solid var(--gold-border);">Asosiy Ibodatlar</span>
        <div style="flex:1;height:1px;background:linear-gradient(90deg, var(--gold-border), transparent);"></div>
    </div>
    
    @php
        $prayers = [
            ['id' => 'fajr', 'label' => 'Bomdod', 'icon' => 'ri-sun-foggy-line'],
            ['id' => 'dhuhr', 'label' => 'Peshin', 'icon' => 'ri-sun-line'],
            ['id' => 'asr', 'label' => 'Asr', 'icon' => 'ri-sun-cloudy-line'],
            ['id' => 'maghrib', 'label' => 'Shom', 'icon' => 'ri-moon-line'],
            ['id' => 'isha', 'label' => 'Xufton', 'icon' => 'ri-moon-clear-line'],
            ['id' => 'qazo', 'label' => 'Qazo', 'icon' => 'ri-history-line'],
        ];
        $data = $log->data ?? [];
        $namozData = $data['namoz'] ?? [];
    @endphp

    <h3 class="section-title"><i class="ri-heart-pulse-line"></i> Namozlarim</h3>
    <div class="deeds-grid">
        @foreach($prayers as $p)
            <div class="deed-card {{ ($namozData[$p['id']] ?? false) ? 'active' : '' }}" 
                 onclick="toggleDataField('namoz.{{ $p['id'] }}', this)">
                <i class="ri-checkbox-circle-fill check-icon"></i>
                <i class="{{ $p['icon'] }} main-icon"></i>
                <span class="deed-label">{{ $p['label'] }}</span>
            </div>
        @endforeach
    </div>

        <div style="text-align:center;margin-top:4px;margin-bottom:20px;">
            <span class="deed-label" style="color:var(--text-muted);display:block;margin-bottom:6px;font-size:0.75rem;">Tarovih namozi</span>
            <div class="rakat-selector">
                @foreach([8, 10, 20] as $rakat)
                    <div class="rakat-btn {{ ($data['taroweh_rakat'] ?? 0) == $rakat ? 'active' : '' }}" 
                         onclick="setDataField('taroweh_rakat', {{ $rakat }}, this, 'rakat-btn')">
                        {{ $rakat }}
                    </div>
                @endforeach
            </div>
        </div>

    {{-- Quran Tracker --}}
    <h3 class="section-title"><i class="ri-book-open-line"></i> Qur'on</h3>
    <div class="card mb-24 quran-progress-card">
        <table class="quran-table">
            <thead>
                <tr>
                    <th>Sura</th>
                    <th>Oyat</th>
                    <th>Sahifa</th>
                </tr>
            </thead>
            <tbody>
                @php $quran = $data['quran'] ?? []; @endphp
                <tr>
                    <td><input type="text" class="quran-input" placeholder="..." value="{{ $quran['sura'] ?? '' }}" onchange="setDataField('quran.sura', this.value)"></td>
                    <td><input type="text" class="quran-input" placeholder="..." value="{{ $quran['oyat'] ?? '' }}" onchange="setDataField('quran.oyat', this.value)"></td>
                    <td><input type="text" class="quran-input" placeholder="..." value="{{ $quran['sahifa'] ?? '' }}" onchange="setDataField('quran.sahifa', this.value)"></td>
                </tr>
            </tbody>
        </table>
        <div class="quran-actions">
            <div class="quran-action-btn {{ ($quran['yodladim'] ?? false) ? 'active' : '' }}" onclick="toggleDataField('quran.yodladim', this)" style="position:relative;">
                <i class="ri-checkbox-circle-fill check-icon"></i>
                <i class="ri-medal-line"></i>
                <span>Yodladim</span>
            </div>
            <div class="quran-action-btn {{ ($quran['qiroat'] ?? false) ? 'active' : '' }}" onclick="toggleDataField('quran.qiroat', this)" style="position:relative;">
                <i class="ri-checkbox-circle-fill check-icon"></i>
                <i class="ri-mic-line"></i>
                <span>Qiroat</span>
            </div>
            <div class="quran-action-btn {{ ($quran['takrorladim'] ?? false) ? 'active' : '' }}" onclick="toggleDataField('quran.takrorladim', this)" style="position:relative;">
                <i class="ri-checkbox-circle-fill check-icon"></i>
                <i class="ri-repeat-2-line"></i>
                <span>Takrorladim</span>
            </div>
        </div>
    </div>

    {{-- Priorities --}}
    <h3 class="section-title"><i class="ri-list-ordered"></i> Eng muhim</h3>
    <div class="card mb-24 priority-card">
        @php $priorities = $data['priorities'] ?? ['', '', '']; @endphp
        @foreach([0, 1, 2] as $idx)
            <div class="priority-row">
                <div class="priority-bullet">{{ $idx + 1 }}</div>
                <input type="text" class="priority-field" placeholder="Bugungi muhim reja..." 
                       value="{{ $priorities[$idx] ?? '' }}"
                       onchange="updatePriority({{ $idx }}, this.value)">
            </div>
        @endforeach
    </div>


    {{-- Vazifalar Section Header --}}
    <div style="margin-top:16px;margin-bottom:8px;display:flex;align-items:center;gap:10px;">
        <span style="background:var(--accent-bg);color:var(--accent);padding:3px 10px;border-radius:50px;font-size:0.65rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;border:1px solid var(--accent-glow);">Kunlik Vazifalar</span>
        <div style="flex:1;height:1px;background:linear-gradient(90deg, var(--accent-glow), transparent);"></div>
    </div>

    <h3 class="section-title"><i class="ri-checkbox-multiple-line"></i> Vazifalar</h3>
    <div class="deeds-grid">
        @foreach($habits as $habit)
            @php $isDone = $completedMap[$habit->id] ?? false; @endphp
            <div class="deed-card {{ $habit->type === 'number' ? 'type-number' : 'type-checkbox' }} {{ $isDone ? 'active' : '' }}" 
                 id="item-{{ $habit->id }}"
                 data-type="{{ $habit->type }}"
                 @if($habit->type === 'checkbox') onclick="toggleHabit({{ $habit->id }}, !this.classList.contains('active'))" @endif>
                
                <i class="ri-checkbox-circle-fill check-icon"></i>
                <i class="{{ $habit->icon }} main-icon"></i>
                <span class="deed-label">{{ $habit->name }}</span>
                
                @if($habit->type === 'number')
                    <input type="number"
                           id="value_{{ $habit->id }}"
                           class="deed-num-input"
                           value="{{ $valuesMap[$habit->id] ?? '' }}"
                           min="0" max="999"
                           placeholder="0"
                           onclick="event.stopPropagation()"
                           onchange="toggleHabit({{ $habit->id }}, null, this.value)">
                @endif
            </div>
        @endforeach
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
                <label class="form-label"><i class="ri-edit-line"></i> Bugungi kun haqida izohingiz</label>
                <textarea name="notes" class="form-input" rows="2" placeholder="Xotiralar, hislar..." style="resize:vertical;">{{ $log->notes ?? '' }}</textarea>
            </div>
        </div>
        <div class="sticky-save">
            <button type="submit" class="btn btn-gold" style="width:100%;">
                <i class="ri-save-line"></i> Kunlik hisobotni saqlash
            </button>
        </div>
    </form>

    {{-- Yangi amal --}}
    <div class="mt-24" style="padding-bottom:80px;">
        <h3 class="section-title"><i class="ri-add-circle-line"></i> Maxsus amal qo'shish</h3>
        <div class="card add-form-card">
            <form method="POST" action="{{ route('daily.custom-habit') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Amal nomi</label>
                    <input type="text" name="name" class="form-input" placeholder="Masalan: Tahajjud namozi" required>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div class="form-group">
                        <label class="form-label">Turi</label>
                        <select name="type" class="form-select">
                            <option value="checkbox">Belgilash (Checkbox)</option>
                            <option value="number">Soni (Raqam)</option>
                        </select>
                    </div>
                    <div class="form-group" style="display:flex;align-items:flex-end;">
                        <button type="submit" class="btn btn-primary" style="width:100%;"><i class="ri-add-line"></i> Ro'yxatga qo'shish</button>
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

    // Data keys used in state
    let logData = @json($log->data ?? []);

    // Boshlang'ich holatni hisoblash
    let totalHabits = {{ $habits->count() + 6 }};
    @php
        $extraDone = 0;
        if(isset($log) && $log) {
            $nD = $log->data['namoz'] ?? [];
            foreach (['fajr', 'dhuhr', 'asr', 'maghrib', 'isha', 'roza'] as $k) {
                if ($nD[$k] ?? false) $extraDone++;
            }
        }
    @endphp
    let completedCount = {{ (isset($log) && $log ? $log->items->where('is_completed', true)->count() : 0) + $extraDone }};
    updateProgress(completedCount, totalHabits);

    function toggleHabit(habitId, isChecked, value) {
        const item = document.getElementById('item-' + habitId);
        const isCheckbox = item.dataset.type === 'checkbox';
        const completed = isCheckbox ? isChecked : (parseInt(value) > 0);

        // Darhol vizual yangilash (optimistic UI)
        if (completed) {
            item.classList.add('active');
            if (isCheckbox) completedCount++;
            showMotivation(completedCount, totalHabits);
        } else {
            item.classList.remove('active');
            if (isCheckbox) completedCount--;
        }

        if (isCheckbox) updateProgress(completedCount, totalHabits);

        saveHabitRaw(habitId, completed, value);
    }

    function saveHabitRaw(habitId, isCompleted, value) {
        const body = { habit_id: habitId, date: DATE, is_completed: isCompleted };
        if (value !== undefined) body.value = value;
        
        fetch(TOGGLE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify(body)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                completedCount = data.completed;
                totalHabits = data.total > 0 ? data.total : totalHabits;
                updateProgress(data.completed, data.total);
            }
        });
    }

    // New Data Field Management
    function toggleDataField(key, el) {
        const isActive = el.classList.contains('active');
        const newValue = !isActive;
        
        if (newValue) el.classList.add('active');
        else el.classList.remove('active');

        setDataField(key, newValue);
    }

    function setDataField(key, value, el, activeClass) {
        if (el && activeClass) {
            const parent = el.parentElement;
            parent.querySelectorAll('.' + activeClass).forEach(b => b.classList.remove('active'));
            el.classList.add('active');
        }

        fetch(TOGGLE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ key, value, date: DATE, type: 'data_field' })
        })
        .then(r => r.json())
        .then(data => { if (data.success) showToast('Saqlandi'); });
    }

    function updatePriority(index, value) {
        fetch(TOGGLE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ key: 'priorities', index, value, date: DATE, type: 'priority' })
        })
        .then(r => r.json())
        .then(data => { if (data.success) showToast('Reja saqlandi'); });
    }

    function updateProgress(completed, total) {
        const percent = total > 0 ? Math.round((completed / total) * 100) : 0;
        const bar = document.getElementById('progressBar');
        if (bar) bar.style.width = percent + '%';
        const txt = document.getElementById('progressText');
        if (txt) txt.textContent = completed + '/' + total;
        const pct = document.getElementById('progressPercent');
        if (pct) pct.textContent = percent + '%';

        if (bar) {
            if (percent >= 90) bar.style.background = 'linear-gradient(90deg, var(--gold), #f7c948)';
            else if (percent >= 50) bar.style.background = 'linear-gradient(90deg, var(--accent), var(--gold))';
        }
    }

    function showMotivation(completed, total) {
        const percent = total > 0 ? Math.round((completed / total) * 100) : 0;
        const messages = [
            { min: 0,  text: '🌱 Yaxshi boshlang\'ich! Davom eting!' },
            { min: 15, text: '💪 Zo\'r! Har bir amal muhim!' },
            { min: 30, text: '🌿 Ajoyib! Daraxtingiz o\'sib bormoqda!' },
            { min: 50, text: '🔥 Yarmi bajarildi! Siz zo\'rsiz!' },
            { min: 70, text: '🌟 Ajoyib! Deyarli tayyor!' },
            { min: 85, text: '🏆 Ozgina qoldi! Hammasi bo\'ladi!' },
            { min: 100, text: '🌸 Barcha amallar bajarildi!' }
        ];
        let msg = messages[0].text;
        for (const m of messages) { if (percent >= m.min) msg = m.text; }
        showToast(msg);
    }

    function showToast(text, isError) {
        const toast = document.getElementById('toast');
        const toastText = document.getElementById('toastText');
        if (!toast) return;
        toastText.textContent = text;
        toast.style.background = isError ? 'var(--danger)' : 'linear-gradient(135deg, var(--accent), #2d8a4f)';
        if (!isError) {
            const percent = totalHabits > 0 ? Math.round((completedCount / totalHabits) * 100) : 0;
            if (percent >= 90) toast.style.background = 'linear-gradient(135deg, var(--gold), #e6a817)';
        }
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(-50%) translateY(0)';
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(-50%) translateY(20px)';
        }, 2000);
    }
</script>
@endsection
