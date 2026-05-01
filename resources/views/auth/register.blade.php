<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro — Lista de Compras</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg: #0d0d0f; --surface: #16161a; --surface2: #1e1e24;
            --border: #2a2a33; --accent: #6ee7b7; --accent2: #34d399;
            --text: #f0f0f3; --muted: #7b7b8e; --danger: #f87171;
        }
        body {
            background: var(--bg); color: var(--text);
            font-family: 'DM Sans', sans-serif; font-weight: 300;
            min-height: 100vh; display: flex; align-items: center;
            justify-content: center; padding: 1rem;
        }
        body::before {
            content: ''; position: fixed; inset: 0;
            background: radial-gradient(ellipse 60% 40% at 30% 30%, rgba(110,231,183,0.06) 0%, transparent 60%);
            pointer-events: none;
        }
        .auth-card {
            width: 100%; max-width: 400px;
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 20px; padding: 2.5rem 2rem;
        }
        .logo { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 1.5rem; color: var(--accent); margin-bottom: 0.3rem; }
        .subtitle { color: var(--muted); font-size: 0.88rem; margin-bottom: 2rem; }
        h2 { font-family: 'Syne', sans-serif; font-size: 1.2rem; font-weight: 700; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1.1rem; }
        label { display: block; font-size: 0.78rem; color: var(--muted); margin-bottom: 0.4rem; letter-spacing: 0.05em; text-transform: uppercase; }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%; background: var(--surface2); border: 1px solid var(--border);
            color: var(--text); padding: 0.75rem 1rem; border-radius: 10px;
            font-family: 'DM Sans', sans-serif; font-size: 0.95rem; transition: border-color 0.2s; outline: none;
        }
        input:focus { border-color: var(--accent); }
        .field-error { color: var(--danger); font-size: 0.78rem; margin-top: 0.35rem; }
        .btn-primary {
            width: 100%; background: var(--accent); color: #0d0d0f; border: none;
            padding: 0.85rem; border-radius: 10px; font-family: 'Syne', sans-serif;
            font-size: 0.95rem; font-weight: 700; cursor: pointer; transition: all 0.2s; margin-top: 0.5rem;
        }
        .btn-primary:hover { background: var(--accent2); transform: translateY(-1px); }
        .auth-footer { text-align: center; margin-top: 1.5rem; font-size: 0.85rem; color: var(--muted); }
        .auth-footer a { color: var(--accent); text-decoration: none; }
        .auth-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="logo">🛒 Lista de Compras</div>
        <p class="subtitle">Crie sua conta gratuita</p>
        <h2>Criar conta</h2>

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="form-group">
                <label>Nome</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Seu nome" required autofocus>
                @error('name') <span class="field-error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label>E-mail</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="seu@email.com" required>
                @error('email') <span class="field-error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label>Senha</label>
                <input type="password" name="password" placeholder="Mínimo 6 caracteres" required>
                @error('password') <span class="field-error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label>Confirmar senha</label>
                <input type="password" name="password_confirmation" placeholder="Repita a senha" required>
            </div>
            <button type="submit" class="btn-primary">Criar conta</button>
        </form>

        <div class="auth-footer">
            Já tem conta? <a href="{{ route('login') }}">Entrar</a>
        </div>
    </div>
</body>
</html>
