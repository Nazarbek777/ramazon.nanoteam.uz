<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ro'yxatdan o'tish — Ramazon Tracker</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="theme-guest">
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <span class="auth-moon">🌙</span>
                <h1>Ro'yxatdan o'tish</h1>
                <p>Ramazon safaringizni boshlang</p>
            </div>

            @if($errors->any())
                <div class="alert alert-error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label">Ismingiz</label>
                    <input type="text" name="name" class="form-input" value="{{ old('name') }}" placeholder="Ismingizni kiriting" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email') }}" placeholder="email@misol.uz" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Jinsingiz</label>
                    <div class="gender-select">
                        <div class="gender-option">
                            <input type="radio" name="gender" id="male" value="male" {{ old('gender', 'male') === 'male' ? 'checked' : '' }}>
                            <label for="male">
                                <span class="gender-icon">👨</span>
                                <span class="gender-text">Erkak</span>
                            </label>
                        </div>
                        <div class="gender-option">
                            <input type="radio" name="gender" id="female" value="female" {{ old('gender') === 'female' ? 'checked' : '' }}>
                            <label for="female">
                                <span class="gender-icon">👩</span>
                                <span class="gender-text">Ayol</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Parol</label>
                    <input type="password" name="password" class="form-input" placeholder="Kamida 6 ta belgi" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Parolni tasdiqlang</label>
                    <input type="password" name="password_confirmation" class="form-input" placeholder="Parolni qayta kiriting" required>
                </div>

                <button type="submit" class="btn btn-gold" style="width:100%">✨ Ro'yxatdan o'tish</button>
            </form>

            <div class="auth-footer">
                Hisobingiz bormi? <a href="{{ route('login') }}">Kirish</a>
            </div>
        </div>
    </div>
</body>
</html>
