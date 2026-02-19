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
            <div class="auth-header" style="margin-bottom:20px;">
                <div class="auth-icon" style="width:48px;height:48px;font-size:1.8rem;margin-bottom:12px;">
                    <i class="ri-moon-clear-fill"></i>
                </div>
                <h1 style="font-size:1.4rem;margin-bottom:4px;">Ro'yxatdan o'tish</h1>
                <p style="font-size:0.85rem;">Ramazon Tracker'ga xush kelibsiz</p>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="form-group" style="margin-bottom:12px;">
                    <label class="form-label" style="font-size:0.8rem;"><i class="ri-user-line"></i> Ism</label>
                    <input type="text" name="name" class="form-input" value="{{ old('name') }}" placeholder="Ismingiz" required style="height:44px;font-size:0.9rem;">
                    @error('name') <div class="form-error"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>

                <div class="form-group" style="margin-bottom:12px;">
                    <label class="form-label" style="font-size:0.8rem;"><i class="ri-phone-line"></i> Telefon raqam</label>
                    <div style="display:flex; align-items:center;">
                        <span style="padding:0 10px; height:44px; display:flex; align-items:center; background:var(--input-bg,rgba(255,255,255,0.05)); border:1px solid var(--input-border,rgba(212,168,67,0.2)); border-right:none; border-radius:12px 0 0 12px; color:var(--text-secondary); font-size:0.9rem; white-space:nowrap;">+998</span>
                        <input type="tel" name="phone" id="phoneInput" class="form-input" 
                               value="{{ old('phone') ? preg_replace('/^\+998/', '', old('phone')) : '' }}"
                               placeholder="90 123 45 67" maxlength="9"
                               required
                               style="height:44px;font-size:0.9rem;border-radius:0 12px 12px 0; border-left:none;">
                    </div>
                    @error('phone') <div class="form-error"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>

                <div class="form-group" style="margin-bottom:12px;">
                    <label class="form-label" style="font-size:0.8rem;"><i class="ri-lock-line"></i> Parol</label>
                    <input type="password" name="password" class="form-input" placeholder="Kamida 6 belgili" required style="height:44px;font-size:0.9rem;">
                    @error('password') <div class="form-error"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>

                <div class="form-group" style="margin-bottom:15px;">
                    <label class="form-label" style="font-size:0.8rem;"><i class="ri-genderless-line"></i> Jinsingiz</label>
                    <div class="gender-select" style="gap:10px;">
                        <div class="gender-option" style="flex:1;">
                            <input type="radio" name="gender" value="male" id="gender_male" {{ old('gender', 'male') === 'male' ? 'checked' : '' }}>
                            <label for="gender_male" style="padding:10px;min-height:auto;">
                                <div class="gender-icon" style="font-size:1.1rem;margin-bottom:2px;"><i class="ri-user-6-line"></i></div>
                                <span class="gender-text" style="font-size:0.8rem;">Erkak</span>
                            </label>
                        </div>
                        <div class="gender-option" style="flex:1;">
                            <input type="radio" name="gender" value="female" id="gender_female" {{ old('gender') === 'female' ? 'checked' : '' }}>
                            <label for="gender_female" style="padding:10px;min-height:auto;">
                                <div class="gender-icon" style="font-size:1.1rem;margin-bottom:2px;"><i class="ri-user-5-line"></i></div>
                                <span class="gender-text" style="font-size:0.8rem;">Ayol</span>
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-gold" style="width:100%;height:46px;font-size:0.95rem;">
                    <i class="ri-user-add-line"></i> Ro'yxatdan o'tish
                </button>
            </form>

            <div style="margin:15px 0; display:flex; align-items:center; gap:8px;">
                <div style="flex:1; height:1px; background:var(--white-10);"></div>
                <div style="font-size:0.65rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px;">Yoki</div>
                <div style="flex:1; height:1px; background:var(--white-10);"></div>
            </div>

            <a href="{{ route('auth.google') }}" class="btn btn-outline" style="width:100%;height:44px; display:flex; align-items:center; justify-content:center; gap:10px; border-color:var(--white-15); background:rgba(255,255,255,0.03); font-size:0.88rem;">
                <img src="https://www.gstatic.com/images/branding/product/1x/googleg_48dp.png" alt="Google" style="width:16px; height:16px;">
                Google orqali kirish
            </a>

            <div class="auth-footer" style="margin-top:20px;font-size:0.85rem;">
                Akkountingiz bormi? <a href="{{ route('login') }}" style="font-weight:700;color:var(--gold);">Kirish</a>
            </div>
        </div>
    </div>

<script>
    const phoneInput = document.getElementById('phoneInput');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
            if (this.value.length > 9) this.value = this.value.slice(0, 9);
        });
        phoneInput.form.addEventListener('submit', function() {
            if (phoneInput.value.length > 0 && !phoneInput.value.startsWith('+998')) {
                phoneInput.value = '+998' + phoneInput.value;
            }
        });
    }
</script>
</body>
</html>
