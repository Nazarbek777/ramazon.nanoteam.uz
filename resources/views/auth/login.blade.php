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

            <div class="auth-footer">
                Hisobingiz yo'qmi? <a href="{{ route('register') }}">Ro'yxatdan o'tish</a>
            </div>
        </div>
    </div>
</body>
</html>
