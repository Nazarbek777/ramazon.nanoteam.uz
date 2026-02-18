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
                    <input type="text" name="phone" class="form-input" value="{{ old('phone') }}" placeholder="+998901234567">
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

            <div class="auth-footer">
                Akkountingiz bormi? <a href="{{ route('login') }}">Kirish</a>
            </div>
        </div>
    </div>
</body>
</html>
