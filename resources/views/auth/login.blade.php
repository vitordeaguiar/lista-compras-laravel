<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Smart Listiq</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--bg:#09090b;--bg1:#111113;--bg2:#18181b;--bg3:#27272a;--border:#3f3f46;--accent:#a3e635;--accent2:#84cc16;--text:#fafafa;--text2:#a1a1aa;--text3:#71717a;--danger:#ef4444}
    body{background:var(--bg);color:var(--text);font-family:'Inter',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem}
    body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse 60% 40% at 70% 20%,rgba(163,230,53,.05) 0%,transparent 60%),radial-gradient(ellipse 50% 50% at 20% 80%,rgba(99,102,241,.04) 0%,transparent 60%);pointer-events:none}
    .auth-card{width:100%;max-width:400px;background:var(--bg2);border:1px solid var(--border);border-radius:16px;padding:2.25rem 2rem;position:relative}
    .logo{display:flex;align-items:center;gap:.5rem;margin-bottom:.3rem;text-decoration:none}
    .logo-icon{width:30px;height:30px;border-radius:8px;background:var(--accent);display:flex;align-items:center;justify-content:center;flex-shrink:0}
    .logo-text{font-size:.9rem;font-weight:700;color:var(--text)}
    .logo-text em{color:var(--accent);font-style:normal}
    .subtitle{color:var(--text3);font-size:.82rem;margin-bottom:1.75rem}
    h2{font-size:1.05rem;font-weight:700;margin-bottom:1.35rem;color:var(--text)}
    .form-group{margin-bottom:1rem}
    label{display:block;font-size:.68rem;color:var(--text2);margin-bottom:.3rem;letter-spacing:.06em;text-transform:uppercase}
    input[type="email"],input[type="password"],input[type="text"]{width:100%;background:var(--bg3);border:1px solid var(--border);color:var(--text);padding:.65rem .9rem;border-radius:9px;font-family:'Inter',sans-serif;font-size:.9rem;transition:border-color .2s;outline:none}
    input:focus{border-color:var(--accent)}
    input::placeholder{color:var(--text3)}
    .field-error{color:var(--danger);font-size:.73rem;margin-top:.28rem}
    .btn-primary{width:100%;background:var(--accent);color:#09090b;border:none;padding:.8rem;border-radius:9px;font-family:'Inter',sans-serif;font-size:.88rem;font-weight:700;cursor:pointer;transition:all .18s;margin-top:.4rem;letter-spacing:.01em}
    .btn-primary:hover{background:var(--accent2);transform:translateY(-1px)}
    .remember-row{display:flex;align-items:center;gap:.5rem;margin-bottom:1rem}
    .remember-row input[type="checkbox"]{accent-color:var(--accent);width:15px;height:15px}
    .remember-row label{margin:0;font-size:.82rem;text-transform:none;letter-spacing:0;color:var(--text2)}
    .auth-footer{text-align:center;margin-top:1.35rem;font-size:.82rem;color:var(--text3)}
    .auth-footer a{color:var(--accent);text-decoration:none}
    .auth-footer a:hover{text-decoration:underline}
    .alert-error{background:rgba(239,68,68,.09);border:1px solid rgba(239,68,68,.25);border-radius:9px;padding:.65rem .9rem;font-size:.82rem;color:var(--danger);margin-bottom:1.1rem}
    </style>
</head>
<body>
    <div class="auth-card">
        <a class="logo" href="#">
            <div class="logo-icon">
                <svg width="16" height="16" viewBox="0 0 28 28" fill="none"><path d="M7 9h14M7 14h9M7 19h11" stroke="#09090b" stroke-width="2.5" stroke-linecap="round"/><circle cx="21" cy="19" r="3.5" fill="#09090b"/><path d="M19.5 19l1 1 2-2" stroke="#a3e635" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <div class="logo-text">Smart <em>Listiq</em></div>
        </a>
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
