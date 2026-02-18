<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirish — Ramazon Tracker</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="theme-guest">
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <span class="auth-moon">🌙</span>
                <h1>Xush kelibsiz!</h1>
                <p>Hisobingizga kiring</p>
            </div>

            @if($errors->any())
                <div class="alert alert-error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email') }}" placeholder="email@misol.uz" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Parol</label>
                    <input type="password" name="password" class="form-input" placeholder="Parolingiz" required>
                </div>

                <div class="form-group" style="display:flex;align-items:center;gap:8px;">
                    <div class="custom-check">
                        <input type="checkbox" name="remember" id="remember">
                        <span class="checkmark"></span>
                    </div>
                    <label for="remember" style="font-size:0.9rem;color:var(--text-secondary);cursor:pointer;">Eslab qolish</label>
                </div>

                <button type="submit" class="btn btn-gold" style="width:100%">Kirish</button>
            </form>

            <div class="auth-footer">
                Hisobingiz yo'qmi? <a href="{{ route('register') }}">Ro'yxatdan o'tish</a>
            </div>
        </div>
    </div>
</body>
</html>
