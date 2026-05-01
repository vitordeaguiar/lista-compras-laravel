<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Lista de Compras')</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{
        --bg:#0d0d0f;--surface:#16161a;--surface2:#1e1e24;
        --border:#2a2a33;--accent:#6ee7b7;--accent2:#34d399;
        --accent-dim:rgba(110,231,183,.1);--text:#f0f0f3;--muted:#7b7b8e;
        --danger:#f87171;--warning:#fbbf24;--radius:13px;
    }
    body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;font-weight:300;min-height:100vh;line-height:1.6}
    nav{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.5rem;background:var(--surface);border-bottom:1px solid var(--border);position:sticky;top:0;z-index:100}
    .nav-brand{font-family:'Syne',sans-serif;font-weight:800;font-size:1.05rem;color:var(--accent);display:flex;align-items:center;gap:.4rem;text-decoration:none}
    .nav-brand span{color:var(--text)}
    .nav-center a{font-size:.82rem;color:var(--muted);text-decoration:none;padding:.3rem .7rem;border-radius:7px;transition:all .2s}
    .nav-center a:hover,.nav-center a.active{color:var(--accent);background:var(--accent-dim)}
    .nav-right{display:flex;align-items:center;gap:.75rem}
    .nav-user{font-size:.8rem;color:var(--muted)}
    .btn-logout{background:none;border:1px solid var(--border);color:var(--muted);padding:.3rem .8rem;border-radius:7px;font-size:.78rem;cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .2s}
    .btn-logout:hover{border-color:var(--danger);color:var(--danger)}
    main{max-width:720px;margin:0 auto;padding:2rem 1.25rem 4rem}
    .alert{padding:.75rem 1rem;border-radius:var(--radius);margin-bottom:1.25rem;font-size:.85rem;border-left:3px solid var(--accent);background:var(--accent-dim);color:var(--accent)}
    .alert-error{border-color:var(--danger);background:rgba(248,113,113,.08);color:var(--danger)}
    input[type="text"],input[type="email"],input[type="password"],input[type="date"],input[type="number"],textarea,select{width:100%;background:var(--surface2);border:1px solid var(--border);color:var(--text);padding:.7rem 1rem;border-radius:10px;font-family:'DM Sans',sans-serif;font-size:.9rem;transition:border-color .2s;outline:none}
    input:focus,textarea:focus,select:focus{border-color:var(--accent)}
    input::placeholder,textarea::placeholder{color:var(--muted);opacity:.6}
    textarea{resize:vertical;min-height:70px}
    label{display:block;font-size:.72rem;color:var(--muted);margin-bottom:.35rem;text-transform:uppercase;letter-spacing:.05em}
    .form-group{margin-bottom:1rem}
    .field-error{color:var(--danger);font-size:.75rem;margin-top:.3rem}
    .btn{display:inline-flex;align-items:center;gap:.4rem;padding:.65rem 1.2rem;border-radius:10px;font-family:'DM Sans',sans-serif;font-size:.88rem;font-weight:500;cursor:pointer;border:none;transition:all .2s;text-decoration:none}
    .btn-primary{background:var(--accent);color:#0d0d0f}
    .btn-primary:hover{background:var(--accent2);transform:translateY(-1px)}
    .btn-ghost{background:var(--surface2);border:1px solid var(--border);color:var(--muted)}
    .btn-ghost:hover{border-color:var(--accent);color:var(--accent)}
    .btn-danger{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:var(--danger)}
    .btn-danger:hover{background:rgba(248,113,113,.2)}
    .btn-sm{padding:.35rem .75rem;font-size:.78rem;border-radius:8px}
    </style>
    @stack('styles')
</head>
<body>
@auth
<nav>
    <a class="nav-brand" href="{{ route('lists.index') }}">🛒 <span>Lista de</span> Compras</a>
    <div class="nav-center">
        <a href="{{ route('lists.index') }}" class="{{ request()->routeIs('lists.index') ? 'active' : '' }}">Minhas Listas</a>
    </div>
    <div class="nav-right">
        <span class="nav-user">{{ Auth::user()->name }}</span>
        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="btn-logout">Sair</button></form>
    </div>
</nav>
@endauth
<main>
    @if(session('success'))<div class="alert">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif
    @yield('content')
</main>
</body>
</html>
