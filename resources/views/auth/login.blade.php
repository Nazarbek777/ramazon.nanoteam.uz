<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirish — Ramazon Tracker</title>
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
                <h1 style="font-size:1.4rem;margin-bottom:4px;">Kirish</h1>
                <p style="font-size:0.85rem;">Hisobingizga kiring</p>
            </div>

            @if($errors->any())
                <div class="alert alert-error" style="padding:10px;font-size:0.8rem;margin-bottom:15px;"><i class="ri-error-warning-fill"></i> {{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group" style="margin-bottom:12px;">
                    <label class="form-label" style="font-size:0.8rem;"><i class="ri-user-line"></i> Email yoki telefon</label>
                    <input type="text" name="identity" class="form-input" value="{{ old('identity') }}" placeholder="email@example.com yoki 90..." required style="height:44px;font-size:0.9rem;">
                </div>

                <div class="form-group" style="margin-bottom:12px;">
                    <label class="form-label" style="font-size:0.8rem;"><i class="ri-lock-line"></i> Parol</label>
                    <input type="password" name="password" class="form-input" placeholder="Parolingiz" required style="height:44px;font-size:0.9rem;">
                </div>

                <div class="form-group" style="display:flex;align-items:center;gap:8px;margin-bottom:15px;">
                    <div class="custom-check">
                        <input type="checkbox" name="remember" id="remember">
                        <span class="checkmark" style="width:18px;height:18px;"></span>
                    </div>
                    <label for="remember" style="color:var(--text-secondary);font-size:0.8rem;cursor:pointer;">Eslab qolish</label>
                </div>

                <button type="submit" class="btn btn-gold" style="width:100%;height:46px;font-size:0.95rem;">
                    <i class="ri-login-box-line"></i> Kirish
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
                Hisobingiz yo'qmi? <a href="{{ route('register') }}" style="font-weight:700;color:var(--gold);">Ro'yxatdan o'tish</a>
            </div>
        </div>
    </div>

<script>
    // If user types only digits in identity field, treat as phone and prepend +998
    const identityInput = document.querySelector('input[name="identity"]');
    if (identityInput) {
        identityInput.form.addEventListener('submit', function() {
            const val = identityInput.value.trim();
            if (/^\d+$/.test(val)) {
                identityInput.value = '+998' + val;
            }
        });
    }
</script>
</body>
</html>
