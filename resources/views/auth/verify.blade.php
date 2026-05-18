<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar E-mail — Smart Listiq</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--bg:#0d0d0f;--surface:#16161a;--surface2:#1e1e24;--border:#2a2a33;--accent:#6ee7b7;--accent2:#34d399;--text:#f0f0f3;--muted:#7b7b8e;--danger:#f87171}
    body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem}
    body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse 60% 40% at 70% 30%,rgba(110,231,183,.06) 0%,transparent 60%);pointer-events:none}
    .card{width:100%;max-width:420px;background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:2.25rem 2rem}
    .logo{font-family:'Syne',sans-serif;font-weight:800;font-size:1.4rem;color:var(--text);margin-bottom:.25rem;display:flex;align-items:center;gap:.5rem}
    .sub{color:var(--muted);font-size:.85rem;margin-bottom:1.75rem}
    h2{font-family:'Syne',sans-serif;font-size:1.1rem;font-weight:800;margin-bottom:.5rem}
    .hint{font-size:.82rem;color:var(--muted);margin-bottom:1.25rem;line-height:1.5}
    .hint strong{color:var(--text)}
    .fg{margin-bottom:.9rem}
    label{display:block;font-size:.7rem;color:var(--muted);margin-bottom:.3rem;text-transform:uppercase;letter-spacing:.05em}
    .code-input{width:100%;background:var(--surface2);border:1px solid var(--border);color:var(--text);padding:.75rem;border-radius:9px;font-family:'Syne',sans-serif;font-size:1.8rem;font-weight:800;outline:none;transition:border-color .2s;text-align:center;letter-spacing:.3em}
    .code-input:focus{border-color:var(--accent)}
    .field-error{color:var(--danger);font-size:.75rem;margin-top:.28rem}
    .btn{width:100%;background:var(--accent);color:#0d0d0f;border:none;padding:.8rem;border-radius:9px;font-family:'Syne',sans-serif;font-size:.9rem;font-weight:800;cursor:pointer;margin-top:.5rem;transition:all .2s}
    .btn:hover{background:var(--accent2)}
    .footer{text-align:center;margin-top:1.25rem;font-size:.82rem;color:var(--muted)}
    .footer a{color:var(--accent);text-decoration:none}
    .steps{display:flex;gap:.4rem;margin-bottom:1.5rem}
    .step{flex:1;height:3px;border-radius:99px;background:var(--border)}
    .step.active{background:var(--accent)}
    .email-badge{display:inline-flex;align-items:center;gap:.4rem;background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:.35rem .75rem;font-size:.8rem;color:var(--accent);margin-bottom:1.25rem}
    </style>
</head>
<body>
<div class="card">
    <div class="logo"><svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0"><rect width="28" height="28" rx="8" fill="#C8F060"/><path d="M7 9h14M7 14h9M7 19h11" stroke="#0a0a0a" stroke-width="2" stroke-linecap="round"/><circle cx="21" cy="19" r="3" fill="#0a0a0a"/><path d="M19.5 19l1 1 2-2" stroke="#C8F060" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg> <span><span style="font-weight:900;letter-spacing:-.02em">Smart</span> <span style="color:var(--accent);font-weight:900">Listiq</span></span></div>
    <p class="sub">Crie sua conta — Passo 2 de 3</p>
    <div class="steps"><div class="step active"></div><div class="step active"></div><div class="step"></div></div>
    <h2>Verifique seu e-mail</h2>
    <div class="hint">
        Enviamos um código de 6 dígitos para <strong>{{ $email }}</strong>.<br>
        Verifique sua caixa de entrada (e spam).
    </div>

    @if($errors->any())
        <div style="background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);border-radius:9px;padding:.7rem .9rem;font-size:.82rem;color:var(--danger);margin-bottom:1rem">{{ $errors->first() }}</div>
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
