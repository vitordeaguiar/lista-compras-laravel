<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completar Cadastro — Smart Listiq</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--bg:#0d0d0f;--surface:#16161a;--surface2:#1e1e24;--border:#2a2a33;--accent:#6ee7b7;--accent2:#34d399;--text:#f0f0f3;--muted:#7b7b8e;--danger:#f87171}
    body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem}
    body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse 60% 40% at 50% 20%,rgba(110,231,183,.07) 0%,transparent 60%);pointer-events:none}
    .card{width:100%;max-width:420px;background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:2.25rem 2rem}
    .logo{font-family:'Syne',sans-serif;font-weight:800;font-size:1.4rem;color:var(--text);margin-bottom:.25rem;display:flex;align-items:center;gap:.5rem}
    .sub{color:var(--muted);font-size:.85rem;margin-bottom:1.75rem}
    h2{font-family:'Syne',sans-serif;font-size:1.1rem;font-weight:800;margin-bottom:1.25rem}
    .verified-badge{display:inline-flex;align-items:center;gap:.4rem;background:rgba(110,231,183,.1);border:1px solid rgba(110,231,183,.25);border-radius:8px;padding:.35rem .75rem;font-size:.78rem;color:var(--accent);margin-bottom:1.25rem}
    .fg{margin-bottom:.9rem}
    label{display:block;font-size:.7rem;color:var(--muted);margin-bottom:.3rem;text-transform:uppercase;letter-spacing:.05em}
    input{width:100%;background:var(--surface2);border:1px solid var(--border);color:var(--text);padding:.65rem .9rem;border-radius:9px;font-family:'DM Sans',sans-serif;font-size:.9rem;outline:none;transition:border-color .2s}
    input:focus{border-color:var(--accent)}
    .field-error{color:var(--danger);font-size:.75rem;margin-top:.28rem}
    .btn{width:100%;background:var(--accent);color:#0d0d0f;border:none;padding:.8rem;border-radius:9px;font-family:'Syne',sans-serif;font-size:.9rem;font-weight:800;cursor:pointer;margin-top:.5rem;transition:all .2s}
    .btn:hover{background:var(--accent2)}
    .steps{display:flex;gap:.4rem;margin-bottom:1.5rem}
    .step{flex:1;height:3px;border-radius:99px;background:var(--border)}
    .step.active{background:var(--accent)}
    </style>
</head>
<body>
<div class="card">
    <div class="logo"><svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0"><rect width="28" height="28" rx="8" fill="#C8F060"/><path d="M7 9h14M7 14h9M7 19h11" stroke="#0a0a0a" stroke-width="2" stroke-linecap="round"/><circle cx="21" cy="19" r="3" fill="#0a0a0a"/><path d="M19.5 19l1 1 2-2" stroke="#C8F060" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg> <span><span style="font-weight:900;letter-spacing:-.02em">Smart</span> <span style="color:var(--accent);font-weight:900">Listiq</span></span></div>
    <p class="sub">Crie sua conta — Passo 3 de 3</p>
    <div class="steps"><div class="step active"></div><div class="step active"></div><div class="step active"></div></div>
    <h2>Complete seu cadastro</h2>
    <div class="verified-badge">✅ E-mail {{ $email }} verificado</div>

    @if($errors->any())
        <div style="background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);border-radius:9px;padding:.7rem .9rem;font-size:.82rem;color:var(--danger);margin-bottom:1rem">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('register.complete') }}">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">
        <input type="hidden" name="code"  value="{{ $code }}">
        <div class="fg">
            <label>Seu nome</label>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="Como quer ser chamado?" required autofocus>
            @error('name')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="fg">
            <label>Senha</label>
            <input type="password" name="password" placeholder="Mínimo 6 caracteres" required>
            @error('password')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="fg">
            <label>Confirmar senha</label>
            <input type="password" name="password_confirmation" placeholder="Repita a senha" required>
        </div>
        <button type="submit" class="btn">✓ Criar minha conta</button>
    </form>
</div>
</body>
</html>
