<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Smart Listiq')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="shortcut icon" href="/favicon.svg">
    <meta name="theme-color" content="#2dd4bf">

    {{-- PWA --}}
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/192.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Smart Listiq">
    <script nonce="{{ $cspNonce ?? '' }}">
    (function(){
        try{
            var aMap={'#2dd4bf':'#14b8a6','#6366f1':'#4f46e5','#10b981':'#059669','#38bdf8':'#0ea5e9','#93c5fd':'#60a5fa','#818cf8':'#6366f1','#c084fc':'#a855f7','#fb7185':'#f43f5e','#fbbf24':'#f59e0b','#f97316':'#ea580c'};
            var dMap={'#2dd4bf':'rgba(45,212,191,.1)','#6366f1':'rgba(99,102,241,.1)','#10b981':'rgba(16,185,129,.1)','#38bdf8':'rgba(56,189,248,.1)','#93c5fd':'rgba(147,197,253,.1)','#818cf8':'rgba(129,140,248,.1)','#c084fc':'rgba(192,132,252,.1)','#fb7185':'rgba(251,113,133,.1)','#fbbf24':'rgba(251,191,36,.1)','#f97316':'rgba(249,115,22,.1)'};
            var t=localStorage.getItem('sl_theme')||'dark';
            var a=localStorage.getItem('sl_accent')||'#2dd4bf';
            if(t==='light')document.documentElement.classList.add('light');
            document.documentElement.style.setProperty('--accent',a);
            document.documentElement.style.setProperty('--accent2',aMap[a]||a);
            document.documentElement.style.setProperty('--adim',dMap[a]||'rgba(45,212,191,.1)');
        }catch(e){}
    })();
    </script>
    <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{
        --bg:#09090b;--bg1:#111113;--bg2:#18181b;--bg3:#27272a;--bg4:#2e2e32;
        --border:#3f3f46;--border2:#52525b;
        --accent:#2dd4bf;--accent2:#14b8a6;--adim:rgba(45,212,191,.1);
        --text:#fafafa;--text2:#a1a1aa;--text3:#71717a;
        --danger:#ef4444;--danger-dim:rgba(239,68,68,.1);
        --warning:#f59e0b;--warning-dim:rgba(245,158,11,.1);
        --blue:#6366f1;--bluedim:rgba(99,102,241,.12);
        --green:#22c55e;--green-dim:rgba(34,197,94,.1);
        --shadow-sm:0 1px 3px rgba(0,0,0,.35),0 1px 2px rgba(0,0,0,.2);
        --shadow-md:0 4px 12px rgba(0,0,0,.4),0 2px 4px rgba(0,0,0,.25);
        --radius:10px;--radius-sm:7px;--radius-lg:14px;
        --sb-width:240px;
    }
    html.light{
        --bg:#f4f6fa;--bg1:#ffffff;--bg2:#f1f5f9;--bg3:#e8edf5;--bg4:#dde3ee;
        --border:#dde3ee;--border2:#c8d0df;
        --text:#0f172a;--text2:#475569;--text3:#94a3b8;
        --adim:rgba(45,212,191,.1);
        --shadow-sm:0 1px 3px rgba(0,0,0,.08),0 1px 2px rgba(0,0,0,.05);
        --shadow-md:0 4px 12px rgba(0,0,0,.1),0 2px 4px rgba(0,0,0,.06);
    }
    body{background:var(--bg);color:var(--text);font-family:'Inter',sans-serif;min-height:100vh;line-height:1.6;font-size:15px;-webkit-font-smoothing:antialiased}

    /* ── APP SHELL ── */
    .app-shell{display:flex;min-height:100vh}

    /* ── SIDEBAR ── */
    .sidebar{width:var(--sb-width);min-height:100vh;background:var(--bg1);border-right:1px solid var(--border);position:fixed;left:0;top:0;bottom:0;display:flex;flex-direction:column;z-index:50}
    .sb-logo{padding:1.1rem 1rem;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:.65rem;text-decoration:none;flex-shrink:0}
    .sb-logo-icon{width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,var(--accent) 0%,var(--accent2) 100%);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 12px var(--adim)}
    .sb-logo-text{font-size:.9rem;font-weight:700;color:var(--text);line-height:1.15;letter-spacing:-.01em}
    .sb-logo-text em{color:var(--accent);font-style:normal}
    .sb-nav{flex:1;padding:.6rem .55rem;display:flex;flex-direction:column;gap:.06rem;overflow-y:auto}
    .sb-section{font-size:.6rem;font-weight:600;text-transform:uppercase;letter-spacing:.1em;color:var(--text3);padding:.5rem .6rem .25rem;margin-top:.35rem}
    .sb-section:first-child{margin-top:0}
    .sb-item{display:flex;align-items:center;gap:.55rem;padding:.52rem .7rem;border-radius:8px;text-decoration:none;color:var(--text2);font-size:.82rem;font-weight:500;transition:background .14s,color .14s;white-space:nowrap;position:relative}
    .sb-item:hover{background:var(--bg3);color:var(--text)}
    .sb-item.active{background:var(--adim);color:var(--accent)}
    .sb-item.active::before{content:'';position:absolute;left:0;top:20%;bottom:20%;width:3px;border-radius:0 3px 3px 0;background:var(--accent)}
    .sb-icon{width:18px;height:18px;flex-shrink:0;opacity:.8;display:flex;align-items:center;justify-content:center}
    .sb-item.active .sb-icon{opacity:1}
    .sb-badge{margin-left:auto;background:var(--accent);color:#09090b;font-size:.58rem;font-weight:700;padding:.07rem .35rem;border-radius:99px;min-width:18px;text-align:center;line-height:1.6}
    .sb-user{padding:.75rem 1rem;border-top:1px solid var(--border);display:flex;align-items:center;gap:.6rem;flex-shrink:0}
    .sb-avatar{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--accent) 0%,var(--blue) 100%);display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;color:#09090b;flex-shrink:0;text-transform:uppercase;box-shadow:0 0 0 2px var(--adim)}
    .sb-uinfo{flex:1;min-width:0}
    .sb-uname{font-size:.78rem;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.2}
    .sb-uemail{font-size:.62rem;color:var(--text3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:.1rem}
    .sb-logout{background:none;border:none;color:var(--text3);cursor:pointer;padding:.25rem;border-radius:6px;transition:color .15s,background .15s;flex-shrink:0;display:flex;align-items:center;justify-content:center;width:28px;height:28px;font-family:'Inter',sans-serif}
    .sb-logout:hover{color:var(--danger);background:var(--danger-dim)}

    /* ── MAIN AREA ── */
    .main-area{margin-left:var(--sb-width);flex:1;display:flex;flex-direction:column;min-height:100vh}
    .topbar{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.85rem;border-bottom:1px solid var(--border);background:var(--bg);gap:1rem;flex-wrap:wrap;min-height:64px;position:sticky;top:0;z-index:40;backdrop-filter:blur(8px)}
    .topbar-left .topbar-title{font-size:1rem;font-weight:700;color:var(--text);line-height:1.2;letter-spacing:-.01em}
    .topbar-left .topbar-sub{font-size:.72rem;color:var(--text3);margin-top:.1rem}
    .topbar-actions{display:flex;align-items:center;gap:.5rem;flex-shrink:0;flex-wrap:wrap}
    .page-content{padding:1.5rem 1.85rem 3rem;flex:1}

    /* ── ALERTS ── */
    .alert{padding:.6rem .9rem;border-radius:var(--radius);margin-bottom:.9rem;font-size:.8rem;font-weight:500;border-left:3px solid var(--accent);background:var(--adim);color:var(--accent);display:flex;align-items:center;gap:.5rem}
    .alert-error{border-color:var(--danger);background:var(--danger-dim);color:var(--danger)}
    .alert-info{border-color:var(--warning);background:var(--warning-dim);color:var(--warning)}

    /* ── INPUTS ── */
    input[type="text"],input[type="email"],input[type="password"],input[type="date"],input[type="number"],input[type="tel"],textarea,select{width:100%;background:var(--bg3);border:1px solid var(--border);color:var(--text);padding:.55rem .85rem;border-radius:8px;font-family:'Inter',sans-serif;font-size:.85rem;transition:border-color .18s,box-shadow .18s;outline:none;min-height:38px}
    input:focus,textarea:focus,select:focus{border-color:var(--accent);box-shadow:0 0 0 3px var(--adim)}
    input::placeholder,textarea::placeholder{color:var(--text3)}
    label{display:block;font-size:.72rem;color:var(--text2);margin-bottom:.3rem;font-weight:500;letter-spacing:.01em}
    .form-group{margin-bottom:.85rem}
    .field-error{color:var(--danger);font-size:.72rem;margin-top:.22rem;display:flex;align-items:center;gap:.25rem}

    /* ── BUTTONS ── */
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:.35rem;padding:.5rem .95rem;border-radius:8px;font-family:'Inter',sans-serif;font-size:.8rem;font-weight:600;cursor:pointer;border:none;transition:all .16s;text-decoration:none;white-space:nowrap;min-height:36px;letter-spacing:.01em}
    .btn-primary{background:linear-gradient(135deg,var(--accent) 0%,var(--accent2) 100%);color:#09090b;box-shadow:0 2px 8px var(--adim)}
    .btn-primary:hover{filter:brightness(1.08);transform:translateY(-1px);box-shadow:0 4px 14px var(--adim)}
    .btn-primary:active{transform:translateY(0);box-shadow:var(--shadow-sm)}
    .btn-ghost{background:var(--bg2);border:1px solid var(--border);color:var(--text2)}
    .btn-ghost:hover{border-color:var(--border2);color:var(--text);background:var(--bg3)}
    .btn-danger{background:var(--danger-dim);border:1px solid rgba(239,68,68,.25);color:var(--danger)}
    .btn-danger:hover{background:rgba(239,68,68,.18)}
    .btn-sm{padding:.3rem .65rem;font-size:.74rem;border-radius:6px;min-height:30px}

    /* ── STAT CARDS ── */
    .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(148px,1fr));gap:.8rem;margin-bottom:1.5rem}
    .stat-card{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1rem 1.1rem;transition:border-color .2s}
    .stat-card:hover{border-color:var(--border2)}
    .stat-card.hl,.stat-card.accent{border-color:rgba(45,212,191,.25);background:var(--adim)}
    .stat-card.danger{border-color:rgba(239,68,68,.2);background:var(--danger-dim)}
    .stat-card.warning{border-color:rgba(245,158,11,.2);background:var(--warning-dim)}
    .stat-card.blue{border-color:rgba(99,102,241,.2);background:var(--bluedim)}
    .stat-label{font-size:.62rem;text-transform:uppercase;letter-spacing:.09em;color:var(--text3);margin-bottom:.3rem}
    .stat-val{font-size:1.35rem;font-weight:800;color:var(--text);line-height:1;letter-spacing:-.02em;font-variant-numeric:tabular-nums}
    .stat-card.hl .stat-val,.stat-card.accent .stat-val{color:var(--accent)}
    .stat-card.danger .stat-val{color:var(--danger)}
    .stat-card.warning .stat-val{color:var(--warning)}
    .stat-card.blue .stat-val{color:var(--blue)}
    .stat-hint{font-size:.62rem;color:var(--text3);margin-top:.28rem}

    /* ── SECTION LABEL ── */
    .sec-label{font-size:.63rem;text-transform:uppercase;letter-spacing:.1em;color:var(--text3);margin-bottom:.6rem;display:flex;align-items:center;justify-content:space-between;font-weight:600}
    .sec-badge{background:var(--bg3);border:1px solid var(--border);color:var(--text3);font-size:.57rem;padding:.06rem .34rem;border-radius:99px}

    /* ── MODAL ── */
    .modal-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:200;align-items:center;justify-content:center;padding:1rem;backdrop-filter:blur(2px)}
    .modal-backdrop.open{display:flex}
    .modal{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.45rem;width:100%;max-width:420px;box-shadow:var(--shadow-md)}
    .modal-title{font-size:.92rem;font-weight:700;margin-bottom:1rem;color:var(--text);display:flex;align-items:center;gap:.5rem}
    .modal-footer{display:flex;gap:.45rem;justify-content:flex-end;margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border)}

    /* ── BOTTOM NAV (mobile) ── */
    .bottom-nav{display:none;position:fixed;bottom:0;left:0;right:0;background:var(--bg1);border-top:1px solid var(--border);height:60px;z-index:100;align-items:center;justify-content:space-around;padding:0 .25rem;padding-bottom:env(safe-area-inset-bottom)}
    .bn-item{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.2rem;padding:.5rem .7rem;border-radius:10px;text-decoration:none;color:var(--text3);font-size:.57rem;font-weight:600;flex:1;transition:color .15s;min-width:0;min-height:44px;touch-action:manipulation}
    .bn-item.active{color:var(--accent)}
    .bn-item:hover{color:var(--text2)}
    .bn-icon{width:20px;height:20px;display:flex;align-items:center;justify-content:center}

    /* ── FOOTER ── */
    .app-footer{text-align:center;padding:.7rem 1rem;border-top:1px solid var(--border);font-size:.62rem;color:var(--text3);letter-spacing:.04em}

    /* ── MOBILE RESPONSIVE ── */
    @media(max-width:768px){
        .sidebar{display:none}
        .main-area{margin-left:0}
        .topbar{padding:.85rem 1.1rem}
        .page-content{padding:1.1rem 1.1rem 76px}
        .bottom-nav{display:flex}
        .stats-grid{grid-template-columns:1fr 1fr}
        .modal{max-width:96vw}
        .app-footer{margin-bottom:60px}
    }
    </style>
    @stack('styles')
</head>
<body>
@auth
<div class="app-shell">
    <aside class="sidebar">
        <a class="sb-logo" href="{{ route('dashboard') }}">
            <div class="sb-logo-icon">
                <svg width="18" height="18" viewBox="0 0 28 28" fill="none">
                    <path d="M7 9h14M7 14h9M7 19h11" stroke="#09090b" stroke-width="2.5" stroke-linecap="round"/>
                    <circle cx="21" cy="19" r="3.5" fill="#09090b"/>
                    <path d="M19.5 19l1 1 2-2" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="sb-logo-text">Smart <em>Listiq</em></div>
        </a>

        <nav class="sb-nav">
            @php $openCount = Auth::user()->shoppingLists()->where('status','open')->count(); @endphp

            <div class="sb-section">Principal</div>

            <a href="{{ route('dashboard') }}" class="sb-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="sb-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                </span>
                Dashboard
            </a>

            <a href="{{ route('lists.index') }}" class="sb-item {{ request()->routeIs('lists.*') ? 'active' : '' }}">
                <span class="sb-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/>
                        <line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/>
                        <line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
                    </svg>
                </span>
                Minhas Listas
                @if($openCount > 0)<span class="sb-badge">{{ $openCount }}</span>@endif
            </a>

            <a href="{{ route('history.index') }}" class="sb-item {{ request()->routeIs('history.*') ? 'active' : '' }}">
                <span class="sb-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                </span>
                Histórico
            </a>

            <div class="sb-section">Finanças</div>

            <a href="{{ route('finance.index') }}" class="sb-item {{ request()->routeIs('finance.*') ? 'active' : '' }}">
                <span class="sb-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </span>
                Financeiro
            </a>

            <a href="{{ route('creditcards.index') }}" class="sb-item {{ request()->routeIs('creditcards.*') ? 'active' : '' }}">
                <span class="sb-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>
                    </svg>
                </span>
                Cartões
            </a>

            <div class="sb-section">Conta</div>

            <a href="{{ route('profile.index') }}" class="sb-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <span class="sb-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                    </svg>
                </span>
                Configurações
            </a>

            @if(Auth::user()->is_admin)
            <a href="/admin" class="sb-item {{ request()->is('admin*') ? 'active' : '' }}">
                <span class="sb-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </span>
                Admin
            </a>
            @endif

            <button id="pwa-sb-item" class="sb-item" onclick="pwaTriggerInstall()"
                style="display:none;border:none;width:100%;text-align:left;cursor:pointer;background:none;color:var(--accent)">
                <span class="sb-icon" style="color:var(--accent)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                </span>
                Instalar app
            </button>
        </nav>

        <div class="sb-user">
            <div class="sb-avatar">{{ mb_substr(Auth::user()->name, 0, 1) }}</div>
            <a href="{{ route('profile.index') }}" class="sb-uinfo" style="text-decoration:none;display:block">
                <div class="sb-uname">{{ Auth::user()->name }}</div>
                <div class="sb-uemail">{{ Auth::user()->email }}</div>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sb-logout" title="Sair">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    <div class="main-area">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
                <div class="topbar-sub">@yield('page-sub')</div>
            </div>
            <div class="topbar-actions" style="display:flex;align-items:center;gap:.5rem">
                <button id="pwa-topbar-btn" onclick="pwaTriggerInstall()"
                    style="display:none;align-items:center;gap:.35rem;background:var(--adim);border:1px solid rgba(45,212,191,.3);color:var(--accent);padding:.35rem .75rem;border-radius:8px;font-size:.75rem;font-weight:600;cursor:pointer;font-family:inherit;transition:all .18s"
                    title="Instalar Smart Listiq">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Instalar
                </button>
                @yield('page-actions')
            </div>
        </div>
        <main class="page-content">
            @if(session('success'))<div class="alert">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif
            @if(session('info'))<div class="alert alert-info">{{ session('info') }}</div>@endif
            @yield('content')
        </main>
        <footer class="app-footer">VAF Solutions &copy; 2026</footer>
    </div>

    {{-- Bottom Navigation (mobile only) --}}
    <nav class="bottom-nav">
        <a href="{{ route('dashboard') }}" class="bn-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="bn-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
            </span>
            <span>Início</span>
        </a>
        <a href="{{ route('lists.index') }}" class="bn-item {{ request()->routeIs('lists.*') ? 'active' : '' }}">
            <span class="bn-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/>
                    <line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/>
                    <line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
                </svg>
            </span>
            <span>Listas</span>
        </a>
        <a href="{{ route('history.index') }}" class="bn-item {{ request()->routeIs('history.*') ? 'active' : '' }}">
            <span class="bn-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
            </span>
            <span>Histórico</span>
        </a>
        <a href="{{ route('finance.index') }}" class="bn-item {{ request()->routeIs('finance.*') ? 'active' : '' }}">
            <span class="bn-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </span>
            <span>Financeiro</span>
        </a>
        <a href="{{ route('profile.index') }}" class="bn-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <span class="bn-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                </svg>
            </span>
            <span>Perfil</span>
        </a>
    </nav>
</div>
@else
<style>body{display:flex;align-items:center;justify-content:center;min-height:100vh;padding:1rem}</style>
@yield('content')
@endauth
@stack('scripts')

{{-- ── PWA: Service Worker + Install Banner ── --}}
<div id="pwa-banner" style="
    display:none;position:fixed;bottom:0;left:0;right:0;z-index:150;
    background:#111113;border-top:1px solid #3f3f46;
    padding:.85rem 1.25rem calc(.85rem + env(safe-area-inset-bottom));
    display:none;align-items:center;gap:.75rem;flex-wrap:wrap;
    box-shadow:0 -4px 20px rgba(0,0,0,.4);
" aria-live="polite">
    <div style="flex:1;min-width:180px">
        <div style="font-size:.85rem;font-weight:700;color:#fafafa;margin-bottom:.18rem">Instalar Smart Listiq</div>
        <div style="font-size:.74rem;color:#a1a1aa">Adicione à tela inicial para acesso rápido</div>
    </div>
    <div style="display:flex;gap:.5rem;flex-shrink:0">
        <button id="pwa-dismiss-btn" style="
            background:none;border:1px solid #3f3f46;color:#a1a1aa;
            padding:.45rem .85rem;border-radius:8px;font-size:.78rem;
            cursor:pointer;font-family:inherit;transition:all .15s;
        ">Agora não</button>
        <button id="pwa-install-btn" style="
            background:linear-gradient(135deg,#2dd4bf,#14b8a6);color:#09090b;
            border:none;padding:.45rem .95rem;border-radius:8px;font-size:.78rem;
            font-weight:700;cursor:pointer;font-family:inherit;transition:filter .18s;
        ">Instalar</button>
    </div>
</div>

<script nonce="{{ $cspNonce ?? '' }}">
// ── Service Worker ──────────────────────────────────────────────────────
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/sw.js').catch(function () {});
    });
}

// ── PWA Install ─────────────────────────────────────────────────────────────
(function () {
    var DISMISSED_KEY = 'sl_pwa_banner_dismissed';
    var deferredPrompt = null;

    var sbItem     = document.getElementById('pwa-sb-item');
    var topbarBtn  = document.getElementById('pwa-topbar-btn');
    var banner     = document.getElementById('pwa-banner');
    var installBtn = document.getElementById('pwa-install-btn');
    var dismissBtn = document.getElementById('pwa-dismiss-btn');

    // Expõe função global para os botões de install
    window.pwaTriggerInstall = function () {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then(function (choice) {
            deferredPrompt = null;
            hideAll();
            localStorage.setItem(DISMISSED_KEY, '1');
        });
    };

    function showInstallButtons() {
        if (sbItem)    sbItem.style.display    = 'flex';
        if (topbarBtn) topbarBtn.style.display = 'inline-flex';
        // Banner aparece após 5s para quem ainda não dispensou
        if (!localStorage.getItem(DISMISSED_KEY)) {
            setTimeout(function () {
                if (deferredPrompt && banner) banner.style.display = 'flex';
            }, 5000);
        }
    }

    function hideAll() {
        if (sbItem)    sbItem.style.display    = 'none';
        if (topbarBtn) topbarBtn.style.display = 'none';
        if (banner)    banner.style.display    = 'none';
    }

    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredPrompt = e;
        showInstallButtons();
    });

    if (installBtn) installBtn.addEventListener('click', function () { window.pwaTriggerInstall(); });

    if (dismissBtn) {
        dismissBtn.addEventListener('click', function () {
            localStorage.setItem(DISMISSED_KEY, '1');
            if (banner) banner.style.display = 'none';
            // Mantém botão sidebar/topbar visível para instalar depois
        });
    }

    window.addEventListener('appinstalled', function () {
        deferredPrompt = null;
        hideAll();
        localStorage.setItem(DISMISSED_KEY, '1');
    });
})();
</script>
</body>
</html>
