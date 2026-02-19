<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ro'yxatdan o'tish — Ramazon Tracker</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="theme-guest">
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-icon">
                    <i class="ri-moon-clear-fill"></i>
                </div>
                <h1>Ro'yxatdan o'tish</h1>
                <p>Ramazon Tracker'ga xush kelibsiz</p>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label"><i class="ri-user-line"></i> Ism</label>
                    <input type="text" name="name" class="form-input" value="{{ old('name') }}" placeholder="Ismingiz" required>
                    @error('name') <div class="form-error"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="ri-mail-line"></i> Email</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email') }}" placeholder="email@example.com">
                    @error('email') <div class="form-error"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="ri-phone-line"></i> Telefon raqam</label>
                    <div style="display:flex; align-items:center; gap:0;">
                        <span style="padding:0 12px; height:48px; display:flex; align-items:center; background:var(--input-bg,rgba(255,255,255,0.05)); border:1px solid var(--input-border,rgba(212,168,67,0.2)); border-right:none; border-radius:var(--radius-sm) 0 0 var(--radius-sm); color:var(--text-secondary); font-size:0.95rem; white-space:nowrap;">+998</span>
                        <input type="tel" name="phone" id="phoneInput" class="form-input" 
                               value="{{ old('phone') ? preg_replace('/^\+998/', '', old('phone')) : '' }}"
                               placeholder="90 123 45 67" maxlength="12"
                               style="border-radius:0 var(--radius-sm) var(--radius-sm) 0; border-left:none;">
                    </div>
                    @error('phone') <div class="form-error"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                    <small style="font-size:0.7rem; color:var(--text-muted); margin-top:4px; display:block;">Email yoki telefon — birini kiritsangiz kifoya.</small>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="ri-lock-line"></i> Parol</label>
                    <input type="password" name="password" class="form-input" placeholder="Kamida 8 belgili" required>
                    @error('password') <div class="form-error"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="ri-lock-2-line"></i> Parolni tasdiqlang</label>
                    <input type="password" name="password_confirmation" class="form-input" placeholder="Parolni qaytaring" required>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="ri-genderless-line"></i> Jinsingiz</label>
                    <div class="gender-select">
                        <div class="gender-option">
                            <input type="radio" name="gender" value="male" id="gender_male" {{ old('gender', 'male') === 'male' ? 'checked' : '' }}>
                            <label for="gender_male">
                                <div class="gender-icon"><i class="ri-user-6-line"></i></div>
                                <span class="gender-text">Erkak</span>
                            </label>
                        </div>
                        <div class="gender-option">
                            <input type="radio" name="gender" value="female" id="gender_female" {{ old('gender') === 'female' ? 'checked' : '' }}>
                            <label for="gender_female">
                                <div class="gender-icon"><i class="ri-user-5-line"></i></div>
                                <span class="gender-text">Ayol</span>
                            </label>
                        </div>
                    </div>
                    @error('gender') <div class="form-error"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-gold" style="width:100%;margin-top:4px;">
                    <i class="ri-user-add-line"></i> Ro'yxatdan o'tish
                </button>
            </form>

            <div style="margin:20px 0; display:flex; align-items:center; gap:10px;">
                <div style="flex:1; height:1px; background:var(--white-10);"></div>
                <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase;">Yoki</div>
                <div style="flex:1; height:1px; background:var(--white-10);"></div>
            </div>

            <a href="{{ route('auth.google') }}" class="btn btn-outline" style="width:100%; display:flex; align-items:center; justify-content:center; gap:10px; border-color:var(--white-15); background:rgba(255,255,255,0.03);">
                <img src="https://www.gstatic.com/images/branding/product/1x/googleg_48dp.png" alt="Google" style="width:18px; height:18px;">
                Google orqali ro'yxatdan o'tish
            </a>

            <div class="auth-footer">
                Akkountingiz bormi? <a href="{{ route('login') }}">Kirish</a>
            </div>
        </div>
    </div>

<script>
    const phoneInput = document.getElementById('phoneInput');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            // Only allow digits
            this.value = this.value.replace(/\D/g, '');
            // Max 9 digits
            if (this.value.length > 9) this.value = this.value.slice(0, 9);
        });
        phoneInput.form.addEventListener('submit', function() {
            if (phoneInput.value.length > 0) {
                phoneInput.value = '+998' + phoneInput.value;
            }
        });
    }
</script>
</body>
</html>
