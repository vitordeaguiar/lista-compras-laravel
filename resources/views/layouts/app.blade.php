<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Smart Listiq')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{
        --bg:#09090b;--bg1:#111113;--bg2:#18181b;--bg3:#27272a;
        --border:#3f3f46;--border2:#52525b;
        --accent:#a3e635;--accent2:#84cc16;--adim:rgba(163,230,53,.1);
        --text:#fafafa;--text2:#a1a1aa;--text3:#71717a;
        --danger:#ef4444;--warning:#f59e0b;--blue:#6366f1;--bluedim:rgba(99,102,241,.12);
        --radius:10px;
    }
    body{background:var(--bg);color:var(--text);font-family:'Inter',sans-serif;min-height:100vh;line-height:1.5;font-size:14px}

    /* ── APP SHELL ── */
    .app-shell{display:flex;min-height:100vh}

    /* ── SIDEBAR ── */
    .sidebar{width:220px;min-height:100vh;background:var(--bg1);border-right:1px solid var(--border);position:fixed;left:0;top:0;bottom:0;display:flex;flex-direction:column;z-index:50}
    .sb-logo{padding:1rem .9rem;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:.55rem;text-decoration:none;flex-shrink:0}
    .sb-logo-icon{width:30px;height:30px;border-radius:8px;background:var(--accent);display:flex;align-items:center;justify-content:center;flex-shrink:0}
    .sb-logo-text{font-size:.88rem;font-weight:700;color:var(--text);line-height:1.15}
    .sb-logo-text em{color:var(--accent);font-style:normal}
    .sb-nav{flex:1;padding:.5rem .45rem;display:flex;flex-direction:column;gap:.05rem;overflow-y:auto}
    .sb-item{display:flex;align-items:center;gap:.5rem;padding:.48rem .65rem;border-radius:8px;text-decoration:none;color:var(--text2);font-size:.79rem;font-weight:500;transition:all .15s;white-space:nowrap}
    .sb-item:hover{background:var(--bg3);color:var(--text)}
    .sb-item.active{background:var(--adim);color:var(--accent)}
    .sb-icon{font-size:.82rem;flex-shrink:0;width:16px;text-align:center}
    .sb-badge{margin-left:auto;background:var(--accent);color:#09090b;font-size:.58rem;font-weight:700;padding:.06rem .32rem;border-radius:99px;min-width:16px;text-align:center;line-height:1.5}
    .sb-user{padding:.65rem .9rem;border-top:1px solid var(--border);display:flex;align-items:center;gap:.5rem;flex-shrink:0}
    .sb-avatar{width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,var(--accent) 0%,var(--blue) 100%);display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;color:#09090b;flex-shrink:0;text-transform:uppercase}
    .sb-uinfo{flex:1;min-width:0}
    .sb-uname{font-size:.74rem;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .sb-uplan{font-size:.59rem;color:var(--text3)}
    .sb-logout{background:none;border:none;color:var(--text3);cursor:pointer;padding:.2rem .3rem;border-radius:5px;font-size:.72rem;transition:color .15s;flex-shrink:0;font-family:'Inter',sans-serif}
    .sb-logout:hover{color:var(--danger)}

    /* ── MAIN AREA ── */
    .main-area{margin-left:220px;flex:1;display:flex;flex-direction:column;min-height:100vh}
    .topbar{display:flex;align-items:center;justify-content:space-between;padding:.9rem 1.75rem;border-bottom:1px solid var(--border);background:var(--bg);gap:1rem;flex-wrap:wrap;min-height:60px}
    .topbar-left .topbar-title{font-size:.95rem;font-weight:700;color:var(--text);line-height:1.2}
    .topbar-left .topbar-sub{font-size:.71rem;color:var(--text3);margin-top:.08rem}
    .topbar-actions{display:flex;align-items:center;gap:.45rem;flex-shrink:0;flex-wrap:wrap}
    .page-content{padding:1.4rem 1.75rem 3rem;flex:1}

    /* ── ALERTS ── */
    .alert{padding:.55rem .85rem;border-radius:var(--radius);margin-bottom:.85rem;font-size:.79rem;border-left:3px solid var(--accent);background:var(--adim);color:var(--accent)}
    .alert-error{border-color:var(--danger);background:rgba(239,68,68,.08);color:var(--danger)}
    .alert-info{border-color:var(--warning);background:rgba(245,158,11,.08);color:var(--warning)}

    /* ── INPUTS ── */
    input[type="text"],input[type="email"],input[type="password"],input[type="date"],input[type="number"],input[type="tel"],textarea,select{width:100%;background:var(--bg3);border:1px solid var(--border);color:var(--text);padding:.52rem .8rem;border-radius:8px;font-family:'Inter',sans-serif;font-size:.82rem;transition:border-color .2s;outline:none}
    input:focus,textarea:focus,select:focus{border-color:var(--accent)}
    input::placeholder,textarea::placeholder{color:var(--text3)}
    label{display:block;font-size:.63rem;color:var(--text2);margin-bottom:.25rem;text-transform:uppercase;letter-spacing:.06em}
    .form-group{margin-bottom:.8rem}
    .field-error{color:var(--danger);font-size:.71rem;margin-top:.2rem}

    /* ── BUTTONS ── */
    .btn{display:inline-flex;align-items:center;gap:.32rem;padding:.48rem .85rem;border-radius:8px;font-family:'Inter',sans-serif;font-size:.79rem;font-weight:600;cursor:pointer;border:none;transition:all .15s;text-decoration:none;white-space:nowrap}
    .btn-primary{background:var(--accent);color:#09090b}
    .btn-primary:hover{background:var(--accent2);transform:translateY(-1px)}
    .btn-ghost{background:var(--bg2);border:1px solid var(--border);color:var(--text2)}
    .btn-ghost:hover{border-color:var(--border2);color:var(--text)}
    .btn-danger{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:var(--danger)}
    .btn-danger:hover{background:rgba(239,68,68,.16)}
    .btn-sm{padding:.3rem .6rem;font-size:.72rem;border-radius:6px}

    /* ── STAT CARDS ── */
    .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(148px,1fr));gap:.7rem;margin-bottom:1.4rem}
    .stat-card{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:.85rem .95rem}
    .stat-card.hl{border-color:rgba(163,230,53,.22);background:rgba(163,230,53,.04)}
    .stat-label{font-size:.6rem;text-transform:uppercase;letter-spacing:.08em;color:var(--text3);margin-bottom:.28rem}
    .stat-val{font-size:1.3rem;font-weight:700;color:var(--text);line-height:1}
    .stat-card.hl .stat-val{color:var(--accent)}
    .stat-hint{font-size:.6rem;color:var(--text3);margin-top:.22rem}

    /* ── SECTION LABEL ── */
    .sec-label{font-size:.63rem;text-transform:uppercase;letter-spacing:.1em;color:var(--text3);margin-bottom:.55rem;display:flex;align-items:center;justify-content:space-between}
    .sec-badge{background:var(--bg3);border:1px solid var(--border);color:var(--text3);font-size:.57rem;padding:.06rem .34rem;border-radius:99px}

    /* ── MODAL ── */
    .modal-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:200;align-items:center;justify-content:center;padding:1rem}
    .modal-backdrop.open{display:flex}
    .modal{background:var(--bg2);border:1px solid var(--border);border-radius:14px;padding:1.35rem;width:100%;max-width:420px}
    .modal-title{font-size:.88rem;font-weight:700;margin-bottom:.95rem;color:var(--text)}
    .modal-footer{display:flex;gap:.45rem;justify-content:flex-end;margin-top:.95rem}
    </style>
    @stack('styles')
</head>
<body>
@auth
<div class="app-shell">
    <aside class="sidebar">
        <a class="sb-logo" href="{{ route('dashboard') }}">
            <div class="sb-logo-icon">
                <svg width="16" height="16" viewBox="0 0 28 28" fill="none">
                    <path d="M7 9h14M7 14h9M7 19h11" stroke="#09090b" stroke-width="2.5" stroke-linecap="round"/>
                    <circle cx="21" cy="19" r="3.5" fill="#09090b"/>
                    <path d="M19.5 19l1 1 2-2" stroke="#a3e635" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="sb-logo-text">Smart <em>Listiq</em></div>
        </a>

        <nav class="sb-nav">
            @php $openCount = Auth::user()->shoppingLists()->where('status','open')->count(); @endphp
            <a href="{{ route('dashboard') }}" class="sb-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="sb-icon">⊞</span> Dashboard
            </a>
            <a href="{{ route('lists.index') }}" class="sb-item {{ request()->routeIs('lists.*') ? 'active' : '' }}">
                <span class="sb-icon">≡</span> Minhas Listas
                @if($openCount > 0)<span class="sb-badge">{{ $openCount }}</span>@endif
            </a>
            <a href="{{ route('history.index') }}" class="sb-item {{ request()->routeIs('history.*') ? 'active' : '' }}">
                <span class="sb-icon">🕐</span> Histórico
            </a>
            <a href="{{ route('finance.index') }}" class="sb-item {{ request()->routeIs('finance.*') ? 'active' : '' }}">
                <span class="sb-icon">📊</span> Financeiro
            </a>
        </nav>

        <div class="sb-user">
            <div class="sb-avatar">{{ mb_substr(Auth::user()->name, 0, 1) }}</div>
            <div class="sb-uinfo">
                <div class="sb-uname">{{ Auth::user()->name }}</div>
                <div class="sb-uplan">Free Plan</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sb-logout" title="Sair">⇥</button>
            </form>
        </div>
    </aside>

    <div class="main-area">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
                <div class="topbar-sub">@yield('page-sub')</div>
            </div>
            <div class="topbar-actions">@yield('page-actions')</div>
        </div>
        <main class="page-content">
            @if(session('success'))<div class="alert">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif
            @if(session('info'))<div class="alert alert-info">{{ session('info') }}</div>@endif
            @yield('content')
        </main>
    </div>
</div>
@else
<style>body{display:flex;align-items:center;justify-content:center;min-height:100vh;padding:1rem}</style>
@yield('content')
@endauth
</body>
</html>
