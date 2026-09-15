<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirish - Parvoz o'quv markazi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #082f49 50%, #0f172a 100%);
            min-height: 100vh;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .input-dark {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #fff;
        }

        .input-dark:focus {
            outline: none;
            border-color: #0ea5e9;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0ea5e9, #06b6d4);
        }
    </style>
</head>

<body class="flex items-center justify-center px-4">
    <div class="glass-card rounded-3xl p-8 w-full max-w-sm text-center">
        <div class="text-5xl mb-4">🎓</div>
        <h1 class="text-2xl font-bold text-white mb-1">Parvoz o'quv markazi</h1>
        <p class="text-slate-400 text-sm mb-8">O'qituvchi paneli</p>

        <form method="POST" action="{{ route('parvoz.login.submit') }}">
            @csrf
            <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="10" required
                autofocus placeholder="• • • • • •"
                class="input-dark w-full px-4 py-4 rounded-2xl text-center text-2xl tracking-[0.5em] font-bold mb-4">

            @error('code')
                <p class="text-rose-400 text-sm mb-4">{{ $message }}</p>
            @enderror

            <button class="btn-primary w-full py-4 rounded-2xl font-bold text-white text-lg">Kirish</button>
        </form>

        <p class="text-slate-500 text-xs mt-6">Kodingizni administratordan oling</p>
    </div>
</body>

</html>
