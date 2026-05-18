<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar E-mail — Smart Listiq</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--bg:#09090b;--bg2:#18181b;--bg3:#27272a;--border:#3f3f46;--accent:#a3e635;--accent2:#84cc16;--text:#fafafa;--text2:#a1a1aa;--text3:#71717a;--danger:#ef4444}
    body{background:var(--bg);color:var(--text);font-family:'Inter',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem}
    body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse 60% 40% at 70% 30%,rgba(163,230,53,.05) 0%,transparent 60%);pointer-events:none}
    .card{width:100%;max-width:420px;background:var(--bg2);border:1px solid var(--border);border-radius:16px;padding:2.1rem 2rem}
    .logo{display:flex;align-items:center;gap:.5rem;margin-bottom:.25rem;text-decoration:none}
    .logo-icon{width:30px;height:30px;border-radius:8px;background:var(--accent);display:flex;align-items:center;justify-content:center;flex-shrink:0}
    .logo-text{font-size:.9rem;font-weight:700;color:var(--text)}
    .logo-text em{color:var(--accent);font-style:normal}
    .sub{color:var(--text3);font-size:.8rem;margin-bottom:1.5rem}
    h2{font-size:1rem;font-weight:700;margin-bottom:.45rem;color:var(--text)}
    .hint{font-size:.79rem;color:var(--text3);margin-bottom:1.1rem;line-height:1.55}
    .hint strong{color:var(--text)}
    .fg{margin-bottom:.85rem}
    label{display:block;font-size:.66rem;color:var(--text2);margin-bottom:.25rem;text-transform:uppercase;letter-spacing:.05em}
    .code-input{width:100%;background:var(--bg3);border:1px solid var(--border);color:var(--text);padding:.7rem;border-radius:8px;font-family:'Inter',sans-serif;font-size:1.6rem;font-weight:700;outline:none;transition:border-color .2s;text-align:center;letter-spacing:.3em}
    .code-input:focus{border-color:var(--accent)}
    .field-error{color:var(--danger);font-size:.72rem;margin-top:.25rem}
    .btn{width:100%;background:var(--accent);color:#09090b;border:none;padding:.78rem;border-radius:8px;font-family:'Inter',sans-serif;font-size:.88rem;font-weight:700;cursor:pointer;margin-top:.4rem;transition:all .18s}
    .btn:hover{background:var(--accent2)}
    .footer{text-align:center;margin-top:1.1rem;font-size:.8rem;color:var(--text3)}
    .footer a{color:var(--accent);text-decoration:none}
    .steps{display:flex;gap:.35rem;margin-bottom:1.35rem}
    .step{flex:1;height:2.5px;border-radius:99px;background:var(--border)}
    .step.active{background:var(--accent)}
    .email-badge{display:inline-flex;align-items:center;gap:.35rem;background:var(--bg3);border:1px solid var(--border);border-radius:7px;padding:.3rem .7rem;font-size:.76rem;color:var(--accent);margin-bottom:1.1rem}
    </style>
</head>
<body>
<div class="card">
    <a class="logo" href="{{ route('login') }}">
        <div class="logo-icon"><svg width="16" height="16" viewBox="0 0 28 28" fill="none"><path d="M7 9h14M7 14h9M7 19h11" stroke="#09090b" stroke-width="2.5" stroke-linecap="round"/><circle cx="21" cy="19" r="3.5" fill="#09090b"/><path d="M19.5 19l1 1 2-2" stroke="#a3e635" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
        <div class="logo-text">Smart <em>Listiq</em></div>
    </a>
    <p class="sub">Crie sua conta — Passo 2 de 3</p>
    <div class="steps"><div class="step active"></div><div class="step active"></div><div class="step"></div></div>
    <h2>Verifique seu e-mail</h2>
    <div class="hint">
        Enviamos um código de 6 dígitos para <strong>{{ $email }}</strong>.<br>
        Verifique sua caixa de entrada (e spam).
    </div>

    @if($errors->any())
        <div style="background:rgba(239,68,68,.09);border:1px solid rgba(239,68,68,.25);border-radius:8px;padding:.62rem .88rem;font-size:.8rem;color:var(--danger);margin-bottom:.9rem">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('register.verify.post') }}">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">
        <div class="fg">
            <label>Código de verificação</label>
            <input type="tel" name="code" class="code-input" placeholder="000000" maxlength="6" required autofocus>
            @error('code')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <button type="submit" class="btn">Verificar código →</button>
    </form>
    <div class="footer">
        Não recebeu? <a href="{{ route('register') }}">Tentar outro e-mail</a>
    </div>
</div>
</body>
</html>
