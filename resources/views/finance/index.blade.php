@extends('layouts.app')

@php
    $prevMonth  = \Carbon\Carbon::parse($month.'-01')->subMonth()->format('Y-m');
    $nextMonth  = \Carbon\Carbon::parse($month.'-01')->addMonth()->format('Y-m');
    $monthLabel = ucfirst(\Carbon\Carbon::parse($month.'-01')->locale('pt_BR')->isoFormat('MMMM [de] YYYY'));
    $totalOut   = $totalFixed + $totalVariable + $supermarket + $totalInvestment;
    $pctUsed    = $totalIncome > 0 ? min(100, round($totalOut / $totalIncome * 100)) : 0;
    $fixedTotal = $fixedPayments->count();
@endphp

@section('title', 'Financeiro — '.$monthLabel)
@section('page-title', 'Financeiro')
@section('page-sub', $monthLabel)

@section('page-actions')
    <a href="{{ route('finance.index', ['month' => $prevMonth]) }}" class="btn btn-ghost btn-sm">◀</a>
    <a href="{{ route('finance.index', ['month' => $nextMonth]) }}" class="btn btn-ghost btn-sm">▶</a>
    <button class="btn btn-primary btn-sm" onclick="setTab('novo-mes')">✨ Abrir mês</button>
@endsection

@push('styles')
<style>
/* ── Tabs ── */
.tabs-nav{display:flex;gap:.2rem;margin-bottom:1.25rem;background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:.22rem;flex-wrap:wrap}
.tab-btn{flex:1;min-width:70px;padding:.38rem .5rem;border-radius:7px;border:none;background:none;color:var(--text2);font-family:'Inter',sans-serif;font-size:.72rem;font-weight:500;cursor:pointer;transition:all .15s;white-space:nowrap;display:flex;align-items:center;justify-content:center;gap:.28rem}
.tab-btn.active{background:var(--bg3);color:var(--text)}
.tab-btn:hover:not(.active){color:var(--text)}
.tab-panel{display:none}
.tab-panel.active{display:block}

/* ── Hero ── */
.hero-card{background:linear-gradient(135deg,var(--bg2) 0%,rgba(56,189,248,.05) 100%);border:1px solid rgba(56,189,248,.18);border-radius:14px;padding:1.25rem 1.4rem;margin-bottom:.85rem}
.hero-balance{font-size:2rem;font-weight:800;color:var(--accent);line-height:1;margin-bottom:.18rem}
.hero-balance.neg{color:var(--danger)}
.hero-lbl{font-size:.61rem;text-transform:uppercase;letter-spacing:.1em;color:var(--text3);margin-bottom:.7rem}
.hero-row{display:flex;gap:1.4rem;flex-wrap:wrap;margin-top:.7rem}
.hero-stat{display:flex;flex-direction:column;gap:.12rem}
.hero-stat-v{font-size:.88rem;font-weight:700;color:var(--text)}
.hero-stat-l{font-size:.57rem;color:var(--text3);text-transform:uppercase;letter-spacing:.07em}
.prog-bar{height:5px;background:var(--bg3);border-radius:99px;margin-top:.8rem;overflow:hidden}
.prog-fill{height:100%;border-radius:99px;transition:width .4s}

/* ── Summary ── */
.sum-card{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:.85rem 1.1rem;margin-bottom:.85rem}
.sum-row{display:flex;justify-content:space-between;align-items:center;padding:.3rem 0;border-bottom:1px solid var(--border);font-size:.8rem}
.sum-row:last-child{border-bottom:none;font-weight:700;font-size:.86rem;padding-top:.45rem}
.sum-lbl{color:var(--text2);display:flex;align-items:center;gap:.35rem}
.sum-val{font-weight:600;font-variant-numeric:tabular-nums}
.c-green{color:#22c55e}
.c-red{color:var(--danger)}
.c-acc{color:var(--accent)}
.c-pur{color:#818cf8}

/* ── Charts ── */
.charts-row{display:grid;grid-template-columns:1fr 1fr;gap:.85rem;margin-bottom:.85rem}
.chart-card{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:.9rem}
.chart-ttl{font-size:.63rem;text-transform:uppercase;letter-spacing:.09em;color:var(--text3);margin-bottom:.75rem}
.donut-wrap{display:flex;align-items:center;gap:1rem;flex-wrap:wrap}
.donut-legend{display:flex;flex-direction:column;gap:.35rem;flex:1;min-width:90px}
.leg-item{display:flex;align-items:center;gap:.38rem;font-size:.7rem}
.leg-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.leg-lbl{color:var(--text2);flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.leg-val{color:var(--text);font-weight:600;font-variant-numeric:tabular-nums}
.bc-wrap{display:flex;align-items:flex-end;gap:.38rem;height:88px}
.bc-col{display:flex;flex-direction:column;align-items:center;flex:1;gap:.2rem;height:100%}
.bc-bars{display:flex;align-items:flex-end;gap:2px;flex:1;width:100%;justify-content:center}
.bc-bar{width:9px;border-radius:3px 3px 0 0;min-height:2px}
.bc-inc{background:#22c55e}
.bc-exp{background:var(--danger)}
.bc-lbl{font-size:.56rem;color:var(--text3);white-space:nowrap}
.bc-legend{display:flex;gap:.6rem;margin-top:.4rem;font-size:.61rem}
.bc-leg-item{display:flex;align-items:center;gap:.28rem;color:var(--text3)}
.bc-leg-dot{width:7px;height:7px;border-radius:2px}

/* ── Finance list ── */
.fin-list{display:flex;flex-direction:column;gap:.35rem;margin-bottom:.75rem}
.fin-item{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:.58rem .8rem;display:flex;align-items:center;gap:.6rem;transition:opacity .2s}
.fin-item.paid{opacity:.5}
.tick{width:22px;height:22px;border-radius:50%;border:2px solid var(--border2);background:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.68rem;color:var(--text3);flex-shrink:0;transition:all .15s;padding:0;font-family:'Inter',sans-serif}
.tick:hover{border-color:var(--accent);color:var(--accent)}
.tick.on{background:var(--accent);border-color:var(--accent);color:#09090b;font-weight:700}
.fin-icon{font-size:.95rem;flex-shrink:0;width:18px;text-align:center}
.fin-name{flex:1;min-width:0;font-size:.8rem;color:var(--text)}
.fin-item.paid .fin-name{text-decoration:line-through;color:var(--text3)}
.fin-badge{font-size:.6rem;padding:.08rem .38rem;border-radius:99px;white-space:nowrap;flex-shrink:0}
.b-ok{background:rgba(34,197,94,.12);color:#22c55e}
.b-due{background:rgba(245,158,11,.1);color:var(--warning)}
.b-late{background:rgba(239,68,68,.1);color:var(--danger)}

/* ── Inline edit ── */
.edit-wrap{display:inline-flex;align-items:center;gap:.25rem;flex-shrink:0}
.edit-val{cursor:pointer;font-weight:700;color:var(--text);font-size:.83rem;font-variant-numeric:tabular-nums;padding:.08rem .2rem;border-radius:4px;transition:all .15s}
.edit-val:hover{background:var(--bg3);color:var(--accent)}
.edit-form{display:none;align-items:center;gap:.18rem}
.edit-inp{width:78px;padding:.18rem .38rem;font-size:.8rem;border-radius:6px;background:var(--bg3);border:1px solid var(--accent);color:var(--text);font-family:'Inter',sans-serif;outline:none}
.edit-ok{padding:.18rem .38rem;font-size:.68rem;border:none;background:var(--accent);color:#09090b;border-radius:4px;cursor:pointer;font-family:'Inter',sans-serif;font-weight:700}

/* ── Section header ── */
.sec-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:.65rem}
.sec-ttl{font-size:.76rem;font-weight:600;color:var(--text)}
.sec-cnt{background:var(--bg3);border:1px solid var(--border);color:var(--text2);font-size:.62rem;padding:.08rem .38rem;border-radius:99px}
.fin-footer{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:.55rem .95rem;display:flex;justify-content:space-between;align-items:center;font-size:.8rem;margin-top:.35rem}
.fin-footer strong{font-variant-numeric:tabular-nums;color:var(--accent)}

/* ── Add form ── */
.add-card{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:.8rem;margin-top:.85rem}
.add-ttl{font-size:.68rem;font-weight:600;color:var(--text2);margin-bottom:.55rem;text-transform:uppercase;letter-spacing:.07em}
.form-row{display:flex;gap:.45rem;flex-wrap:wrap}
.form-row .form-group{flex:1;min-width:110px}
.form-row .form-group.sm{flex:0 0 80px;min-width:65px}
.hint{font-size:.68rem;color:var(--text3);margin-bottom:.65rem}

/* ── Category tabs ── */
.cat-tabs{display:flex;gap:.18rem;flex-wrap:wrap;margin-bottom:.65rem}
.cat-tab{padding:.25rem .55rem;border-radius:6px;border:1px solid var(--border);background:none;color:var(--text3);font-size:.66rem;cursor:pointer;font-family:'Inter',sans-serif;transition:all .15s}
.cat-tab.active{background:var(--bg3);color:var(--text);border-color:var(--border2)}

/* ── Investments ── */
.inv-hero{background:linear-gradient(135deg,rgba(129,140,248,.07) 0%,rgba(129,140,248,.02) 100%);border:1px solid rgba(129,140,248,.18);border-radius:14px;padding:1.1rem 1.3rem;margin-bottom:.85rem}
.inv-hero .hero-balance{color:#818cf8}
.inv-item .edit-val{color:#818cf8}
.inv-item .edit-ok{background:#818cf8}

/* ── Novo Mês ── */
.nm-banner{background:rgba(56,189,248,.05);border:1px solid rgba(56,189,248,.16);border-radius:var(--radius);padding:.85rem 1rem;margin-bottom:.85rem;font-size:.8rem;color:var(--text2);line-height:1.5}
.nm-banner strong{color:var(--accent)}
.nm-sec-ttl{font-size:.64rem;text-transform:uppercase;letter-spacing:.09em;color:var(--text3);margin:.75rem 0 .38rem}
.nm-list{display:flex;flex-direction:column;gap:.28rem;margin-bottom:.5rem}
.nm-item{display:flex;align-items:center;gap:.55rem;padding:.4rem .7rem;background:var(--bg2);border:1px solid var(--border);border-radius:8px;font-size:.78rem;cursor:pointer}
.nm-item input[type=checkbox]{width:14px;height:14px;accent-color:var(--accent);flex-shrink:0;cursor:pointer}
.inv-note{background:rgba(129,140,248,.05);border:1px solid rgba(129,140,248,.13);border-radius:8px;padding:.58rem .8rem;font-size:.76rem;color:var(--text2);margin-bottom:.5rem;line-height:1.5}

@media(max-width:768px){
    .tabs-nav{gap:.1rem}
    .tab-btn{font-size:.63rem;padding:.3rem .35rem;min-width:55px}
    .charts-row{grid-template-columns:1fr}
    .hero-row{gap:.9rem}
    .hero-balance{font-size:1.55rem}
    .form-row .form-group{min-width:100%}
    .form-row .form-group.sm{min-width:100%;flex:1}
}
</style>
@endpush

@section('content')

{{-- TABS NAV --}}
<div class="tabs-nav">
    <button class="tab-btn active" onclick="setTab('resumo')" id="btn-resumo">📊 Resumo</button>
    <button class="tab-btn" onclick="setTab('fixos')" id="btn-fixos">
        📌 Fixos @if($fixedTotal > 0)<span class="sec-cnt">{{ $paidFixed }}/{{ $fixedTotal }}</span>@endif
    </button>
    <button class="tab-btn" onclick="setTab('variaveis')" id="btn-variaveis">🎲 Variáveis</button>
    <button class="tab-btn" onclick="setTab('investimentos')" id="btn-investimentos">📈 Invest.</button>
    <button class="tab-btn" onclick="setTab('novo-mes')" id="btn-novo-mes">✨ Novo Mês</button>
</div>

{{-- ══════════════════════════════════════
     TAB: RESUMO
══════════════════════════════════════ --}}
<div id="tab-resumo" class="tab-panel active">

    {{-- Hero balance --}}
    <div class="hero-card">
        <div class="hero-lbl">Saldo disponível — {{ $monthLabel }}</div>
        <div class="hero-balance {{ $balance < 0 ? 'neg' : '' }}">
            R$ {{ number_format(abs($balance), 2, ',', '.') }}
        </div>
        <div class="hero-row">
            <div class="hero-stat">
                <span class="hero-stat-v c-green">R$ {{ number_format($totalIncome, 2, ',', '.') }}</span>
                <span class="hero-stat-l">Entradas</span>
            </div>
            <div class="hero-stat">
                <span class="hero-stat-v c-red">R$ {{ number_format($totalOut, 2, ',', '.') }}</span>
                <span class="hero-stat-l">Saídas</span>
            </div>
            <div class="hero-stat">
                <span class="hero-stat-v">{{ $paidFixed }}/{{ $fixedTotal }}</span>
                <span class="hero-stat-l">Fixos pagos</span>
            </div>
            <div class="hero-stat">
                <span class="hero-stat-v c-pur">R$ {{ number_format($totalInvestment, 2, ',', '.') }}</span>
                <span class="hero-stat-l">Investido</span>
            </div>
        </div>
        @if($totalIncome > 0)
            <div class="prog-bar" title="{{ $pctUsed }}% da renda comprometida">
                <div class="prog-fill" style="width:{{ $pctUsed }}%;background:{{ $pctUsed > 90 ? 'var(--danger)' : ($pctUsed > 70 ? 'var(--warning)' : 'var(--accent)') }}"></div>
            </div>
            <div style="font-size:.6rem;color:var(--text3);margin-top:.28rem">{{ $pctUsed }}% da renda comprometida</div>
        @endif
    </div>

    {{-- Summary detail --}}
    <div class="sum-card">
        <div class="sum-row">
            <span class="sum-lbl">💰 Entradas</span>
            <span class="sum-val c-green">+ R$ {{ number_format($totalIncome, 2, ',', '.') }}</span>
        </div>
        <div class="sum-row">
            <span class="sum-lbl">📌 Fixos</span>
            <span class="sum-val c-red">− R$ {{ number_format($totalFixed, 2, ',', '.') }}</span>
        </div>
        <div class="sum-row">
            <span class="sum-lbl">🎲 Variáveis</span>
            <span class="sum-val c-red">− R$ {{ number_format($totalVariable, 2, ',', '.') }}</span>
        </div>
        <div class="sum-row">
            <span class="sum-lbl">🛒 Supermercado</span>
            <span class="sum-val c-red">− R$ {{ number_format($supermarket, 2, ',', '.') }}</span>
        </div>
        <div class="sum-row">
            <span class="sum-lbl">📈 Investimentos</span>
            <span class="sum-val c-pur">− R$ {{ number_format($totalInvestment, 2, ',', '.') }}</span>
        </div>
        <div class="sum-row">
            <span class="sum-lbl" style="color:var(--text);font-weight:700">= Saldo livre</span>
            <span class="sum-val {{ $balance >= 0 ? 'c-green' : 'c-red' }}">R$ {{ number_format($balance, 2, ',', '.') }}</span>
        </div>
    </div>

    {{-- Charts --}}
    <div class="charts-row">

        {{-- Donut chart --}}
        <div class="chart-card">
            <div class="chart-ttl">Por categoria</div>
            @php
                $dTotal = collect($chartDonut)->sum('value');
                $r = 45; $cx = 60; $cy = 60;
                $circ = 2 * M_PI * $r;
                $dOff = 0;
            @endphp
            <div class="donut-wrap">
                <svg viewBox="0 0 120 120" width="105" height="105" style="flex-shrink:0;transform:rotate(-90deg)">
                    @if($dTotal <= 0)
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="none" stroke="var(--bg3)" stroke-width="18"/>
                    @else
                        @foreach($chartDonut as $sl)
                            @php
                                $sv  = max(0, (float)($sl['value'] ?? 0));
                                $slen = ($sv / $dTotal) * $circ;
                            @endphp
                            @if($slen > 0.5)
                                <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="none"
                                    stroke="{{ $sl['color'] }}" stroke-width="18"
                                    stroke-dasharray="{{ number_format($slen,3,'.',''). ' ' .number_format($circ-$slen,3,'.','') }}"
                                    stroke-dashoffset="{{ number_format(-$dOff,3,'.','') }}"/>
                            @endif
                            @php $dOff += $slen; @endphp
                        @endforeach
                    @endif
                </svg>
                <div class="donut-legend">
                    @foreach($chartDonut as $sl)
                        <div class="leg-item">
                            <span class="leg-dot" style="background:{{ $sl['color'] }}"></span>
                            <span class="leg-lbl">{{ $sl['label'] }}</span>
                            <span class="leg-val">R$ {{ number_format((float)($sl['value']??0),2,',','.') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Bar chart --}}
        <div class="chart-card">
            <div class="chart-ttl">Últimos 6 meses</div>
            @php $maxBV = max(1, collect($chartBars)->max(fn($b) => max($b['income'], $b['expense']))); @endphp
            <div class="bc-wrap">
                @foreach($chartBars as $bar)
                    @php
                        $ih = max(2, round($bar['income']  / $maxBV * 72));
                        $eh = max(2, round($bar['expense'] / $maxBV * 72));
                    @endphp
                    <div class="bc-col">
                        <div class="bc-bars">
                            <div class="bc-bar bc-inc" style="height:{{ $ih }}px" title="Entradas: R$ {{ number_format($bar['income'],2,',','.') }}"></div>
                            <div class="bc-bar bc-exp" style="height:{{ $eh }}px" title="Saídas: R$ {{ number_format($bar['expense'],2,',','.') }}"></div>
                        </div>
                        <div class="bc-lbl">{{ $bar['label'] }}</div>
                    </div>
                @endforeach
            </div>
            <div class="bc-legend">
                <span class="bc-leg-item"><span class="bc-leg-dot" style="background:#22c55e"></span>Entradas</span>
                <span class="bc-leg-item"><span class="bc-leg-dot" style="background:var(--danger)"></span>Saídas</span>
            </div>
        </div>

    </div>

    {{-- Add income --}}
    <div class="add-card">
        <div class="add-ttl">+ Adicionar entrada</div>
        <form method="POST" action="{{ route('finance.income.store') }}">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">
            <div class="form-row">
                <div class="form-group" style="flex:2">
                    <label>Nome</label>
                    <input type="text" name="name" placeholder="Ex: Salário, Freelance…" required>
                </div>
                <div class="form-group">
                    <label>Valor (R$)</label>
                    <input type="number" name="amount" step="0.01" min="0" placeholder="0,00" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm" style="margin-top:.3rem">Adicionar</button>
        </form>
    </div>

</div>

{{-- ══════════════════════════════════════
     TAB: FIXOS
══════════════════════════════════════ --}}
<div id="tab-fixos" class="tab-panel">

    <div class="sec-hd">
        <span class="sec-ttl">Custos fixos</span>
        @if($fixedTotal > 0)
            <span class="sec-cnt">{{ $paidFixed }}/{{ $fixedTotal }} pagos</span>
        @endif
    </div>
    <p class="hint">Clique no círculo para marcar como pago · Clique no valor para editar</p>

    <div class="fin-list">
        @forelse($fixedPayments as $p)
            @php
                $today  = (int) now()->format('j');
                $dueDay = (int) ($p->fixedCost?->due_day ?? 1);
                $isLate = !$p->paid && $today > $dueDay;
            @endphp
            <div class="fin-item {{ $p->paid ? 'paid' : '' }}">
                <form method="POST" action="{{ route('finance.fixed.toggle', $p->id) }}" style="display:inline">
                    @csrf @method('PATCH')
                    <button type="submit" class="tick {{ $p->paid ? 'on' : '' }}">{{ $p->paid ? '✓' : '' }}</button>
                </form>
                <span class="fin-icon">{{ $p->fixedCost?->icon ?? '📋' }}</span>
                <span class="fin-name">{{ $p->fixedCost?->name ?? '—' }}</span>
                @if($p->paid)
                    <span class="fin-badge b-ok">✓ pago</span>
                @elseif($isLate)
                    <span class="fin-badge b-late">🔴 atrasado</span>
                @else
                    <span class="fin-badge b-due">⏰ dia {{ $dueDay }}</span>
                @endif
                <div class="edit-wrap">
                    <span class="edit-val" onclick="startEdit(this)">R$ {{ number_format($p->amount, 2, ',', '.') }}</span>
                    <form class="edit-form" method="POST" action="{{ route('finance.fixed.update', $p->id) }}">
                        @csrf @method('PATCH')
                        <input type="number" name="amount" step="0.01" min="0" value="{{ $p->amount }}" class="edit-inp">
                        <button type="submit" class="edit-ok">✓</button>
                    </form>
                </div>
            </div>
        @empty
            <div style="text-align:center;padding:2rem;color:var(--text3);font-size:.8rem">
                Nenhum custo fixo neste mês. Adicione abaixo.
            </div>
        @endforelse
    </div>

    @if($fixedPayments->isNotEmpty())
        <div class="fin-footer">
            <span style="color:var(--text2)">Total fixos</span>
            <strong>R$ {{ number_format($totalFixed, 2, ',', '.') }}</strong>
        </div>
    @endif

    <div class="add-card">
        <div class="add-ttl">+ Adicionar custo fixo</div>
        <form method="POST" action="{{ route('finance.fixed.store') }}">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">
            <div class="form-row">
                <div class="form-group" style="flex:2">
                    <label>Nome</label>
                    <input type="text" name="name" placeholder="Ex: Aluguel, Internet, Netflix…" required>
                </div>
                <div class="form-group">
                    <label>Valor (R$)</label>
                    <input type="number" name="amount" step="0.01" min="0" placeholder="0,00" required>
                </div>
                <div class="form-group sm">
                    <label>Dia venc.</label>
                    <input type="number" name="due_day" min="1" max="31" placeholder="10" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm" style="margin-top:.3rem">Adicionar</button>
        </form>
    </div>

</div>

{{-- ══════════════════════════════════════
     TAB: VARIÁVEIS
══════════════════════════════════════ --}}
<div id="tab-variaveis" class="tab-panel">

    @php
        $catIcons = ['lazer'=>'🎮','delivery'=>'🛵','compras'=>'🛍️','transporte'=>'🚌','saude'=>'💊','educacao'=>'📚','outros'=>'📦'];
        $catNames = ['lazer'=>'Lazer','delivery'=>'Delivery','compras'=>'Compras','transporte'=>'Transporte','saude'=>'Saúde','educacao'=>'Educação','outros'=>'Outros'];
    @endphp

    <div class="cat-tabs">
        <button class="cat-tab active" onclick="filterCat('all',this)">Todos ({{ $variables->count() }})</button>
        @foreach($catIcons as $cat => $icon)
            @php $cc = $variables->where('category',$cat)->count(); @endphp
            @if($cc > 0)
                <button class="cat-tab" onclick="filterCat('{{ $cat }}',this)">{{ $icon }} {{ $catNames[$cat] }} ({{ $cc }})</button>
            @endif
        @endforeach
    </div>

    <div class="fin-list" id="var-list">
        @forelse($variables as $v)
            <div class="fin-item {{ $v->paid ? 'paid' : '' }}" data-cat="{{ $v->category }}">
                <form method="POST" action="{{ route('finance.variable.toggle', $v->id) }}" style="display:inline">
                    @csrf @method('PATCH')
                    <button type="submit" class="tick {{ $v->paid ? 'on' : '' }}">{{ $v->paid ? '✓' : '' }}</button>
                </form>
                <span class="fin-icon">{{ $catIcons[$v->category] ?? '📦' }}</span>
                <span class="fin-name">{{ $v->name }}</span>
                <span class="fin-badge" style="background:var(--bg3);color:var(--text3)">{{ $catNames[$v->category] ?? $v->category }}</span>
                @if($v->spent_at)
                    <span style="font-size:.61rem;color:var(--text3);flex-shrink:0">{{ $v->spent_at->format('d/m') }}</span>
                @endif
                <div class="edit-wrap">
                    <span class="edit-val" onclick="startEdit(this)">R$ {{ number_format($v->amount, 2, ',', '.') }}</span>
                    <form class="edit-form" method="POST" action="{{ route('finance.variable.update', $v->id) }}">
                        @csrf @method('PATCH')
                        <input type="number" name="amount" step="0.01" min="0" value="{{ $v->amount }}" class="edit-inp">
                        <button type="submit" class="edit-ok">✓</button>
                    </form>
                </div>
            </div>
        @empty
            <div style="text-align:center;padding:2rem;color:var(--text3);font-size:.8rem">
                Sem gastos variáveis em {{ $monthLabel }}.
            </div>
        @endforelse
    </div>

    @if($variables->isNotEmpty())
        <div class="fin-footer">
            <span style="color:var(--text2)">Total variáveis</span>
            <strong>R$ {{ number_format($totalVariable, 2, ',', '.') }}</strong>
        </div>
    @endif

    <div class="add-card">
        <div class="add-ttl">+ Adicionar gasto variável</div>
        <form method="POST" action="{{ route('finance.variable.store') }}">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">
            <div class="form-row">
                <div class="form-group" style="flex:2">
                    <label>Nome</label>
                    <input type="text" name="name" placeholder="Ex: Cinema, iFood, Farmácia…" required>
                </div>
                <div class="form-group">
                    <label>Categoria</label>
                    <select name="category" required>
                        @foreach($catIcons as $cat => $icon)
                            <option value="{{ $cat }}">{{ $icon }} {{ $catNames[$cat] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Valor (R$)</label>
                    <input type="number" name="amount" step="0.01" min="0" placeholder="0,00" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm" style="margin-top:.3rem">Adicionar</button>
        </form>
    </div>

</div>

{{-- ══════════════════════════════════════
     TAB: INVESTIMENTOS
══════════════════════════════════════ --}}
<div id="tab-investimentos" class="tab-panel">

    @php
        $invCatIcons = ['poupanca'=>'🏦','renda_fixa'=>'📄','tesouro'=>'🏛️','acoes_fii'=>'📊','cripto'=>'₿','outros'=>'💼'];
        $invCatNames = ['poupanca'=>'Poupança','renda_fixa'=>'Renda Fixa','tesouro'=>'Tesouro','acoes_fii'=>'Ações/FII','cripto'=>'Cripto','outros'=>'Outros'];
    @endphp

    <div class="inv-hero">
        <div class="hero-lbl">Total aportado em {{ $monthLabel }}</div>
        <div class="hero-balance" style="color:#818cf8">R$ {{ number_format($totalInvestment, 2, ',', '.') }}</div>
        <div class="hero-row">
            <div class="hero-stat">
                <span class="hero-stat-v" style="color:#818cf8">{{ $investments->count() }}</span>
                <span class="hero-stat-l">Carteiras</span>
            </div>
            <div class="hero-stat">
                <span class="hero-stat-v">{{ $investments->filter(fn($i) => $i->entries->isNotEmpty())->count() }}</span>
                <span class="hero-stat-l">Com aporte</span>
            </div>
        </div>
    </div>

    <div class="fin-list">
        @forelse($investments as $inv)
            @php
                $entry       = $inv->entries->first();
                $entryAmt    = $entry ? (float) $entry->amount : 0;
                $totalAmt    = (float) ($inv->entries_sum_amount ?? 0);
                $monthsSince = $inv->started_at->diffInMonths(now()) + 1;
            @endphp
            <div class="fin-item inv-item" style="align-items:flex-start;padding:.75rem .85rem">
                <span class="fin-icon" style="margin-top:.1rem">{{ $invCatIcons[$inv->category] ?? '💼' }}</span>
                <div style="flex:1;min-width:0">
                    <div style="font-size:.8rem;color:var(--text);font-weight:600">{{ $inv->name }}</div>
                    <div style="font-size:.62rem;color:var(--text3);margin-top:.1rem">
                        {{ $invCatNames[$inv->category] ?? $inv->category }} · {{ $monthsSince }} {{ $monthsSince==1?'mês':'meses' }} desde {{ $inv->started_at->locale('pt_BR')->isoFormat('MMM/YYYY') }}
                    </div>
                    <div style="display:flex;gap:1.2rem;margin-top:.45rem;flex-wrap:wrap">
                        <div>
                            <div style="font-size:.58rem;text-transform:uppercase;letter-spacing:.07em;color:var(--text3)">Total acumulado</div>
                            <div style="font-size:.88rem;font-weight:700;color:#818cf8">R$ {{ number_format($totalAmt, 2, ',', '.') }}</div>
                        </div>
                        <div>
                            <div style="font-size:.58rem;text-transform:uppercase;letter-spacing:.07em;color:var(--text3)">Aporte este mês</div>
                            <div class="edit-wrap" style="margin-top:.05rem">
                                <span class="edit-val" style="color:var(--text);font-size:.88rem" onclick="startEdit(this)">R$ {{ number_format($entryAmt, 2, ',', '.') }}</span>
                                @if($entry)
                                    <form class="edit-form" method="POST" action="{{ route('finance.investment.update', $entry->id) }}">
                                        @csrf @method('PATCH')
                                        <input type="number" name="amount" step="0.01" min="0" value="{{ $entryAmt }}" class="edit-inp">
                                        <button type="submit" class="edit-ok" style="background:#818cf8">✓</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div style="text-align:center;padding:2rem;color:var(--text3);font-size:.8rem">
                Nenhum investimento ainda. Adicione abaixo.
            </div>
        @endforelse
    </div>

    @if($investments->isNotEmpty())
        <div class="fin-footer" style="border-color:rgba(129,140,248,.2)">
            <span style="color:var(--text2)">Total investido no mês</span>
            <strong style="color:#818cf8">R$ {{ number_format($totalInvestment, 2, ',', '.') }}</strong>
        </div>
    @endif

    <div class="add-card" style="border-color:rgba(129,140,248,.15)">
        <div class="add-ttl" style="color:#818cf8">+ Adicionar / aportar</div>
        <form method="POST" action="{{ route('finance.investment.store') }}">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">
            <div class="form-row">
                <div class="form-group" style="flex:2">
                    <label>Carteira</label>
                    <input type="text" name="name" placeholder="Ex: Tesouro Selic, BTC, Nubank…" required>
                </div>
                <div class="form-group">
                    <label>Categoria</label>
                    <select name="category" required>
                        @foreach($invCatIcons as $cat => $icon)
                            <option value="{{ $cat }}">{{ $icon }} {{ $invCatNames[$cat] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Aporte (R$)</label>
                    <input type="number" name="amount" step="0.01" min="0" placeholder="0,00" required>
                </div>
            </div>
            <button type="submit" class="btn btn-sm" style="margin-top:.3rem;background:#818cf8;color:#09090b;font-weight:700">Aportar</button>
        </form>
    </div>

</div>

{{-- ══════════════════════════════════════
     TAB: NOVO MÊS
══════════════════════════════════════ --}}
<div id="tab-novo-mes" class="tab-panel">

    @php
        $nmNext  = \Carbon\Carbon::parse($month.'-01')->addMonth()->format('Y-m');
        $nmLabel = ucfirst(\Carbon\Carbon::parse($month.'-01')->addMonth()->locale('pt_BR')->isoFormat('MMMM [de] YYYY'));
    @endphp

    <div class="nm-banner">
        <strong>✨ Abrir {{ $nmLabel }}</strong><br>
        Selecione o que deseja copiar do mês atual para o próximo.
    </div>

    <form method="POST" action="{{ route('finance.open-month') }}">
        @csrf
        <input type="hidden" name="month" value="{{ $nmNext }}">

        @if($prevFixedPayments->isNotEmpty())
            <div class="nm-sec-ttl">📌 Custos Fixos</div>
            <div class="nm-list">
                @foreach($prevFixedPayments as $p)
                    <label class="nm-item">
                        <input type="checkbox" name="fixed_ids[]" value="{{ $p->fixed_cost_id }}" checked>
                        <span style="font-size:.9rem">{{ $p->fixedCost?->icon ?? '📋' }}</span>
                        <span style="flex:1">{{ $p->fixedCost?->name ?? '—' }}</span>
                        <span style="color:var(--text2);font-size:.78rem;font-variant-numeric:tabular-nums">R$ {{ number_format($p->amount, 2, ',', '.') }}</span>
                    </label>
                @endforeach
            </div>
        @endif

        @if($incomes->isNotEmpty())
            <div class="nm-sec-ttl">💰 Entradas</div>
            <div class="nm-list">
                @foreach($incomes as $inc)
                    <label class="nm-item">
                        <input type="checkbox" name="income_ids[]" value="{{ $inc->id }}" checked>
                        <span style="font-size:.9rem">💵</span>
                        <span style="flex:1">{{ $inc->name }}</span>
                        <span style="color:#22c55e;font-size:.78rem;font-variant-numeric:tabular-nums">R$ {{ number_format($inc->amount, 2, ',', '.') }}</span>
                    </label>
                @endforeach
            </div>
        @endif

        @if($investments->isNotEmpty())
            <div class="nm-sec-ttl">📈 Investimentos</div>
            <div class="inv-note">
                Os investimentos existentes são mantidos automaticamente. Novos aportes podem ser adicionados na aba <strong>Investimentos</strong>.
            </div>
        @endif

        @if($prevFixedPayments->isEmpty() && $incomes->isEmpty() && $investments->isEmpty())
            <div style="text-align:center;padding:2rem;color:var(--text3);font-size:.8rem">
                Nenhum lançamento neste mês. Adicione fixos e entradas nas abas acima.
            </div>
        @endif

        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:.75rem">
            ✨ Confirmar e abrir {{ $nmLabel }}
        </button>
    </form>

</div>

<script>
// ── Tabs ──────────────────────────────────────────────────────
function setTab(name) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    var panel = document.getElementById('tab-' + name);
    var btn   = document.getElementById('btn-' + name);
    if (panel) panel.classList.add('active');
    if (btn)   btn.classList.add('active');
    try { sessionStorage.setItem('finTab', name); } catch(e) {}
}

// Restore tab on load
(function() {
    try {
        var saved = sessionStorage.getItem('finTab');
        if (saved && document.getElementById('tab-' + saved)) setTab(saved);
    } catch(e) {}
})();

// ── Inline edit ───────────────────────────────────────────────
function startEdit(spanEl) {
    var wrap  = spanEl.closest('.edit-wrap');
    var form  = wrap.querySelector('.edit-form');
    if (!form) return;
    var input = form.querySelector('input[type=number]');
    spanEl.style.display = 'none';
    form.style.display   = 'inline-flex';
    input.focus();
    input.select();
    input.onkeydown = function(e) {
        if (e.key === 'Enter')  { e.preventDefault(); form.submit(); }
        if (e.key === 'Escape') { form.style.display = 'none'; spanEl.style.display = ''; }
    };
    input.onblur = function() {
        setTimeout(function() {
            if (form.contains(document.activeElement)) return;
            form.style.display = 'none';
            spanEl.style.display = '';
        }, 160);
    };
}

// ── Category filter ───────────────────────────────────────────
function filterCat(cat, btnEl) {
    document.querySelectorAll('.cat-tab').forEach(function(b) { b.classList.remove('active'); });
    if (btnEl) btnEl.classList.add('active');
    document.querySelectorAll('#var-list .fin-item').forEach(function(item) {
        item.style.display = (cat === 'all' || item.dataset.cat === cat) ? '' : 'none';
    });
}
</script>

@endsection
