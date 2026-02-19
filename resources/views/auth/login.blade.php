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
            <div class="auth-header">
                <div class="auth-icon">
                    <i class="ri-moon-clear-fill"></i>
                </div>
                <h1>Kirish</h1>
                <p>Hisobingizga kiring</p>
            </div>

            @if($errors->any())
                <div class="alert alert-error"><i class="ri-error-warning-fill"></i> {{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label"><i class="ri-user-line"></i> Email yoki telefon</label>
                    <input type="text" name="identity" class="form-input" value="{{ old('identity') }}" placeholder="email@example.com yoki +998..." required>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="ri-lock-line"></i> Parol</label>
                    <input type="password" name="password" class="form-input" placeholder="Parolingiz" required>
                </div>

                <div class="form-group" style="display:flex;align-items:center;gap:8px;">
                    <div class="custom-check">
                        <input type="checkbox" name="remember" id="remember">
                        <span class="checkmark"></span>
                    </div>
                    <label for="remember" style="color:var(--text-secondary);font-size:0.85rem;cursor:pointer;">Eslab qolish</label>
                </div>

                <button type="submit" class="btn btn-gold" style="width:100%;">
                    <i class="ri-login-box-line"></i> Kirish
                </button>
            </form>

            <div style="margin:20px 0; display:flex; align-items:center; gap:10px;">
                <div style="flex:1; height:1px; background:var(--white-10);"></div>
                <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase;">Yoki</div>
                <div style="flex:1; height:1px; background:var(--white-10);"></div>
            </div>

            <a href="{{ route('auth.google') }}" class="btn btn-outline" style="width:100%; display:flex; align-items:center; justify-content:center; gap:10px; border-color:var(--white-15); background:rgba(255,255,255,0.03);">
                <img src="https://www.gstatic.com/images/branding/product/1x/googleg_48dp.png" alt="Google" style="width:18px; height:18px;">
                Google orqali kirish
            </a>

            <div class="auth-footer">
                Hisobingiz yo'qmi? <a href="{{ route('register') }}">Ro'yxatdan o'tish</a>
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
