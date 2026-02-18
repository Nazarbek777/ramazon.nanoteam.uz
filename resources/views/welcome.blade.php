<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Ramazon oyida kunlik ibodatlaringizni belgilab boring">
    <title>Ramazon Tracker — Kunlik Ibodat Ilovasi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="theme-guest">
    <div class="welcome-wrapper">
        <div class="welcome-icon">
            <i class="ri-moon-clear-fill"></i>
        </div>
        <h1 class="welcome-title">Ramazon Tracker</h1>
        <p class="welcome-arabic">رمضان مبارك</p>
        <p class="welcome-desc">
            Ramazon oyida kunlik ibodatlaringizni belgilab boring.<br>
            Namoz, ro'za, Qur'on, zikr va sadaqa — hammasini bir joyda kuzating.
        </p>
        <div class="welcome-actions">
            <a href="{{ route('register') }}" class="btn btn-gold"><i class="ri-user-add-line"></i> Ro'yxatdan o'tish</a>
            <a href="{{ route('login') }}" class="btn btn-outline"><i class="ri-login-box-line"></i> Kirish</a>
        </div>

        <div class="welcome-features">
            <div class="card welcome-feature">
                <div class="feature-icon"><i class="ri-checkbox-circle-line"></i></div>
                <h3>Kunlik checklist</h3>
                <p>Har kuni amallaringizni belgilang</p>
            </div>
            <div class="card welcome-feature">
                <div class="feature-icon"><i class="ri-fire-line"></i></div>
                <h3>Streak</h3>
                <p>Ketma-ket kunlarni kuzating</p>
            </div>
            <div class="card welcome-feature">
                <div class="feature-icon"><i class="ri-focus-3-line"></i></div>
                <h3>Maqsadlar</h3>
                <p>Ramazon uchun maqsad qo'ying</p>
            </div>
            <div class="card welcome-feature">
                <div class="feature-icon"><i class="ri-bar-chart-box-line"></i></div>
                <h3>Hisobotlar</h3>
                <p>Haftalik va oylik tahlil</p>
            </div>
        </div>
    </div>
</body>
</html>
