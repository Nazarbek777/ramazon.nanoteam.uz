@extends('layouts.app')
@section('title', 'Profil Sozlamalari')

@section('content')
<div class="page-header" style="text-align:center;margin-bottom:20px;">
    <h2 style="font-size:1.5rem;color:var(--gold);"><i class="ri-user-settings-line"></i> Profil</h2>
    <p class="text-muted">Shaxsiy ma'lumotlar va xavfsizlik</p>
</div>

<div class="profile-layout" style="max-width: 600px; margin: 0 auto;">
    {{-- Profile Chip --}}
    <div class="card mb-24" style="text-align:center; padding: 30px 20px;">
        <div class="sidebar-avatar" style="width:80px; height:80px; font-size:2rem; margin: 0 auto 15px; border-width: 3px;">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <h3 style="margin-bottom:4px;">{{ $user->name }}</h3>
        <p class="text-muted" style="font-size:0.85rem;">{{ $user->email }}</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-24"><i class="ri-checkbox-circle-fill"></i> {{ session('success') }}</div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST">
        @csrf
        
        {{-- Personal Details --}}
        <h3 class="section-title"><i class="ri-profile-line"></i> Asosiy ma'lumotlar</h3>
        <div class="card mb-24">
            <div class="form-group">
                <label class="form-label" style="display:flex; align-items:center; gap:8px;">
                    <i class="ri-user-line" style="color:var(--gold);"></i> Ism
                </label>
                <input type="text" name="name" class="form-input @error('name') is-invalid @enderror"
                       value="{{ old('name', $user->name) }}" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" style="display:flex; align-items:center; gap:8px;">
                    <i class="ri-mail-line" style="color:var(--gold);"></i> Email
                </label>
                <input type="email" name="email" class="form-input @error('email') is-invalid @enderror" 
                       value="{{ old('email', $user->email) }}">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" style="display:flex; align-items:center; gap:8px;">
                    <i class="ri-phone-line" style="color:var(--gold);"></i> Telefon raqam
                </label>
                <input type="text" name="phone" class="form-input @error('phone') is-invalid @enderror" 
                       value="{{ old('phone', $user->phone) }}" placeholder="+998...">
                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <small style="font-size:0.75rem; color:var(--text-muted); margin-top:4px; display:block;">Email yoki telefon — kamida bittasi bo'lishi shart.</small>
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label" style="display:flex; align-items:center; gap:8px;">
                    <i class="ri-genderless-line" style="color:var(--gold);"></i> Jinsingiz (Mavzu uchun)
                </label>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-top:8px;">
                    <label class="gender-option" style="cursor:pointer;">
                        <input type="radio" name="gender" value="male" class="gender-radio" 
                               {{ old('gender', $user->gender) === 'male' ? 'checked' : '' }} style="display:none;">
                        <div class="gender-card {{ old('gender', $user->gender) === 'male' ? 'active' : '' }}" 
                             style="padding:15px; border-radius:12px; border:1px solid var(--border-color); text-align:center; transition:var(--transition-fast);">
                            <i class="ri-user-6-line" style="font-size:1.5rem; display:block; margin-bottom:5px;"></i>
                            <span style="font-size:0.85rem; font-weight:600;">Erkak</span>
                        </div>
                    </label>
                    <label class="gender-option" style="cursor:pointer;">
                        <input type="radio" name="gender" value="female" class="gender-radio" 
                               {{ old('gender', $user->gender) === 'female' ? 'checked' : '' }} style="display:none;">
                        <div class="gender-card {{ old('gender', $user->gender) === 'female' ? 'active' : '' }}" 
                             style="padding:15px; border-radius:12px; border:1px solid var(--border-color); text-align:center; transition:var(--transition-fast);">
                            <i class="ri-user-5-line" style="font-size:1.5rem; display:block; margin-bottom:5px;"></i>
                            <span style="font-size:0.85rem; font-weight:600;">Ayol</span>
                        </div>
                    </label>
                </div>
                @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- Security (collapsible) --}}
        <button type="button" id="togglePasswordBtn" onclick="togglePasswordSection()"
                style="width:100%; display:flex; align-items:center; justify-content:space-between; padding:14px 18px; border-radius:var(--radius); border:1px solid var(--border-color); background:var(--bg-card); color:var(--text-primary); cursor:pointer; margin-bottom:16px; font-size:0.95rem; font-weight:600;">
            <span><i class="ri-shield-keyhole-line" style="color:var(--gold); margin-right:8px;"></i> Parolni o'zgartirish</span>
            <i class="ri-arrow-down-s-line" id="passwordArrow" style="transition:transform 0.3s;"></i>
        </button>

        <div id="passwordSection" style="display:none; overflow:hidden;">
            <div class="card mb-24">
                @error('current_password') <div class="invalid-feedback" style="margin-bottom:10px;">{{ $message }}</div> @enderror
                @error('new_password') <div class="invalid-feedback" style="margin-bottom:10px;">{{ $message }}</div> @enderror

                <div class="form-group">
                    <label class="form-label">Hozirgi parol</label>
                    <input type="password" name="current_password" class="form-input @error('current_password') is-invalid @enderror" 
                           placeholder="Hozirgi parolingiz">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Yangi parol</label>
                    <input type="password" name="new_password" class="form-input @error('new_password') is-invalid @enderror" 
                           placeholder="Yangi parol (kamida 6 belgi)">
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Yangi parolni tasdiqlang</label>
                    <input type="password" name="new_password_confirmation" class="form-input" 
                           placeholder="Parolni qayta kiriting">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-gold" style="width:100%; padding:14px; font-weight:700; font-size:1rem;">
            <i class="ri-check-line"></i> O'zgarishlarni saqlash
        </button>
    </form>
</div>

<style>
    .gender-radio:checked + .gender-card {
        background: var(--accent-bg);
        border-color: var(--accent) !important;
        color: var(--accent);
        box-shadow: 0 0 15px var(--accent-glow);
    }
    
    .gender-card:hover {
        border-color: var(--accent);
        background: rgba(255,255,255,0.02);
    }

    .theme-female .gender-card:hover {
        background: rgba(0,0,0,0.01);
    }

    .is-invalid {
        border-color: var(--danger) !important;
    }

    .invalid-feedback {
        color: var(--danger);
        font-size: 0.75rem;
        margin-top: 5px;
        font-weight: 500;
    }
</style>

<script>
    // Smooth interaction for gender cards
    document.querySelectorAll('.gender-radio').forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.gender-card').forEach(card => card.classList.remove('active'));
            if(radio.checked) {
                radio.closest('.gender-option').querySelector('.gender-card').classList.add('active');
            }
        });
    });

    // Toggle password section
    function togglePasswordSection() {
        const section = document.getElementById('passwordSection');
        const arrow = document.getElementById('passwordArrow');
        const isOpen = section.style.display !== 'none';
        section.style.display = isOpen ? 'none' : 'block';
        arrow.style.transform = isOpen ? '' : 'rotate(180deg)';
    }

    // Auto-open if there are password errors
    @if($errors->has('current_password') || $errors->has('new_password'))
        togglePasswordSection();
    @endif
</script>
@endsection
