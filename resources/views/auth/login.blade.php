<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Smart Listiq</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg: #0d0d0f;
            --surface: #16161a;
            --surface2: #1e1e24;
            --border: #2a2a33;
            --accent: #6ee7b7;
            --accent2: #34d399;
            --text: #f0f0f3;
            --muted: #7b7b8e;
            --danger: #f87171;
            --radius: 14px;
        }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-weight: 300;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        /* background decoration */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 40% at 70% 20%, rgba(110,231,183,0.06) 0%, transparent 60%),
                radial-gradient(ellipse 50% 50% at 20% 80%, rgba(52,211,153,0.04) 0%, transparent 60%);
            pointer-events: none;
        }
        .auth-card {
            width: 100%;
            max-width: 400px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2.5rem 2rem;
            position: relative;
        }
        .logo {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 1.5rem;
            color: var(--text);
            margin-bottom: 0.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .subtitle {
            color: var(--muted);
            font-size: 0.88rem;
            margin-bottom: 2rem;
        }
        h2 {
            font-family: 'Syne', sans-serif;
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--text);
        }
        .form-group { margin-bottom: 1.1rem; }
        label { display: block; font-size: 0.78rem; color: var(--muted); margin-bottom: 0.4rem; letter-spacing: 0.05em; text-transform: uppercase; }
        input[type="email"], input[type="password"], input[type="text"] {
            width: 100%;
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            transition: border-color 0.2s;
            outline: none;
        }
        input:focus { border-color: var(--accent); }
        .field-error { color: var(--danger); font-size: 0.78rem; margin-top: 0.35rem; }
        .btn-primary {
            width: 100%;
            background: var(--accent);
            color: #0d0d0f;
            border: none;
            padding: 0.85rem;
            border-radius: 10px;
            font-family: 'Syne', sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 0.5rem;
            letter-spacing: 0.02em;
        }
        .btn-primary:hover { background: var(--accent2); transform: translateY(-1px); }
        .remember-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.1rem;
        }
        .remember-row input[type="checkbox"] { accent-color: var(--accent); width: 16px; height: 16px; }
        .remember-row label { margin: 0; font-size: 0.85rem; text-transform: none; letter-spacing: 0; }
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.85rem;
            color: var(--muted);
        }
        .auth-footer a { color: var(--accent); text-decoration: none; }
        .auth-footer a:hover { text-decoration: underline; }
        .alert-error {
            background: rgba(248,113,113,0.1);
            border: 1px solid rgba(248,113,113,0.3);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 0.85rem;
            color: var(--danger);
            margin-bottom: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="logo"><svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0"><rect width="28" height="28" rx="8" fill="#C8F060"/><path d="M7 9h14M7 14h9M7 19h11" stroke="#0a0a0a" stroke-width="2" stroke-linecap="round"/><circle cx="21" cy="19" r="3" fill="#0a0a0a"/><path d="M19.5 19l1 1 2-2" stroke="#C8F060" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg> <span><span style="font-weight:900;letter-spacing:-.02em">Smart</span> <span style="color:var(--accent);font-weight:900">Listiq</span></span></div>
        <p class="subtitle">Organize suas compras com facilidade</p>
        <h2>Entrar na conta</h2>

        @if($errors->any())
            <div class="alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label>E-mail</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="seu@email.com" required autofocus>
                @error('email') <span class="field-error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label>Senha</label>
                <input type="password" name="password" placeholder="••••••••" required>
                @error('password') <span class="field-error">{{ $message }}</span> @enderror
            </div>
            <div class="remember-row">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">Lembrar de mim</label>
            </div>
            <button type="submit" class="btn-primary">Entrar</button>
        </form>

        <div class="auth-footer">
            Não tem conta? <a href="{{ route('register') }}">Criar conta</a>
        </div>
    </div>
</body>
</html>
