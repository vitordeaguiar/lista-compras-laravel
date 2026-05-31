@extends('layouts.app')

@php
    $prevMonth  = \Carbon\Carbon::parse($month.'-01')->subMonth()->format('Y-m');
    $nextMonth  = \Carbon\Carbon::parse($month.'-01')->addMonth()->format('Y-m');
    $monthLabel = ucfirst(\Carbon\Carbon::parse($month.'-01')->locale('pt_BR')->isoFormat('MMMM [de] YYYY'));
    $totalCards = $cards->count();
    $catIcons   = ['compras'=>'🛍️','assinatura'=>'🔄','eletronico'=>'💻','casa'=>'🏠','saude'=>'💊','outros'=>'📦'];
    $brandIcons = ['visa'=>'VISA','mastercard'=>'MC','elo'=>'ELO','amex'=>'AMEX','outro'=>'···'];
@endphp

@section('title', 'Cartões — '.$monthLabel)
@section('page-title', '💳 Cartões de Crédito')
@section('page-sub', $monthLabel)

@section('page-actions')
    <a href="{{ route('creditcards.index', ['month' => $prevMonth]) }}" class="btn btn-ghost btn-sm">◀</a>
    <span class="btn btn-ghost btn-sm" style="pointer-events:none;cursor:default">{{ $monthLabel }}</span>
    <a href="{{ route('creditcards.index', ['month' => $nextMonth]) }}" class="btn btn-ghost btn-sm">▶</a>
    <button class="btn btn-primary btn-sm" onclick="openModal('modal-novo-cartao')">+ Novo cartão</button>
@endsection

@push('styles')
<style>
/* ── Tabs ── */
.tabs-nav{display:flex;gap:.2rem;margin-bottom:1.25rem;background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:.22rem;flex-wrap:wrap;overflow-x:auto}
.tab-btn{padding:.38rem .7rem;border-radius:7px;border:none;background:none;color:var(--text2);font-family:'Inter',sans-serif;font-size:.72rem;font-weight:500;cursor:pointer;transition:all .15s;white-space:nowrap;display:flex;align-items:center;gap:.28rem}
.tab-btn.active{background:var(--bg3);color:var(--text)}
.tab-btn:hover:not(.active){color:var(--text)}
.tab-panel{display:none}
.tab-panel.active{display:block}

/* ── Summary cards grid ── */
.cc-summary-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(148px,1fr));gap:.7rem;margin-bottom:1.25rem}
.cc-sum-card{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:.85rem .95rem}
.cc-sum-lbl{font-size:.6rem;text-transform:uppercase;letter-spacing:.08em;color:var(--text3);margin-bottom:.28rem}
.cc-sum-val{font-size:1.3rem;font-weight:700;color:var(--text);line-height:1;font-variant-numeric:tabular-nums}
.cc-sum-hint{font-size:.6rem;color:var(--text3);margin-top:.22rem}

/* ── Visual card ── */
.cc-cards-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:.85rem;margin-bottom:1.25rem}
.cc-card{border-radius:14px;padding:1rem 1.1rem;position:relative;overflow:hidden;cursor:pointer;transition:transform .15s,box-shadow .15s;color:#fff;min-height:140px;display:flex;flex-direction:column;justify-content:space-between}
.cc-card:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.35)}
.cc-card::before{content:'';position:absolute;top:-30px;right:-30px;width:120px;height:120px;border-radius:50%;background:rgba(255,255,255,.08)}
.cc-card::after{content:'';position:absolute;bottom:-40px;left:-20px;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,.05)}
.cc-card-top{display:flex;align-items:center;justify-content:space-between;position:relative;z-index:1}
.cc-chip{width:28px;height:20px;border-radius:4px;background:linear-gradient(135deg,#e8c97a,#c9a84c);border:1px solid rgba(255,255,255,.2)}
.cc-brand{font-size:.68rem;font-weight:800;letter-spacing:.08em;opacity:.9}
.cc-card-mid{position:relative;z-index:1}
.cc-card-name{font-size:.78rem;font-weight:600;letter-spacing:.04em;opacity:.95;margin-bottom:.15rem}
.cc-card-num{font-size:.62rem;opacity:.55;letter-spacing:.12em}
.cc-card-bot{display:flex;align-items:flex-end;justify-content:space-between;position:relative;z-index:1}
.cc-fatura-lbl{font-size:.55rem;opacity:.6;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.1rem}
.cc-fatura-val{font-size:1.05rem;font-weight:700;font-variant-numeric:tabular-nums}
.cc-limit-bar{height:3px;border-radius:99px;background:rgba(255,255,255,.2);margin-top:.35rem;overflow:hidden}
.cc-limit-fill{height:100%;border-radius:99px;background:rgba(255,255,255,.7);transition:width .3s}
.cc-venc{font-size:.6rem;opacity:.6;text-align:right}

/* ── Projection grid ── */
.proj-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:.4rem;margin-bottom:1.25rem}
.proj-card{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:.6rem .5rem;text-align:center}
.proj-card.current-month{border-color:rgba(var(--accent-rgb,45,212,191),.35);background:var(--adim)}
.proj-lbl{font-size:.58rem;color:var(--text3);margin-bottom:.2rem;text-transform:uppercase}
.proj-val{font-size:.88rem;font-weight:700;color:var(--text);font-variant-numeric:tabular-nums}

/* ── Card detail layout ── */
.card-detail-grid{display:grid;grid-template-columns:260px 1fr;gap:1rem}
.card-detail-left{display:flex;flex-direction:column;gap:.75rem}
.card-info-box{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:.85rem}
.card-info-row{display:flex;justify-content:space-between;align-items:center;padding:.28rem 0;font-size:.79rem;border-bottom:1px solid var(--border)}
.card-info-row:last-child{border-bottom:none}
.card-info-lbl{color:var(--text2)}
.card-info-val{font-weight:600;color:var(--text);font-variant-numeric:tabular-nums}
.limit-avail{height:6px;border-radius:99px;background:var(--bg3);overflow:hidden;margin-top:.55rem}
.limit-avail-fill{height:100%;border-radius:99px;background:var(--accent);transition:width .3s}

/* ── Fatura payment box ── */
.fatura-box{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:.85rem;display:flex;align-items:center;justify-content:space-between;gap:.5rem}
.fatura-box.paid{border-color:rgba(34,197,94,.25);background:rgba(34,197,94,.04)}
.fatura-lbl-sm{font-size:.6rem;text-transform:uppercase;letter-spacing:.08em;color:var(--text3);margin-bottom:.2rem}
.fatura-amount{font-size:1.1rem;font-weight:700;color:var(--text);font-variant-numeric:tabular-nums}

/* ── Installments list ── */
.inst-list{display:flex;flex-direction:column;gap:.38rem}
.inst-item{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:.65rem .85rem;transition:opacity .2s}
.inst-item.quitado{opacity:.4}
.inst-top{display:flex;align-items:center;gap:.55rem}
.inst-cat-icon{font-size:1rem;flex-shrink:0;width:24px;text-align:center}
.inst-desc{flex:1;min-width:0;font-size:.82rem;font-weight:500;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.inst-badge{font-size:.58rem;padding:.06rem .38rem;border-radius:99px;background:var(--bg3);border:1px solid var(--border);color:var(--text2);white-space:nowrap;flex-shrink:0}
.inst-badge.rec{background:rgba(99,102,241,.12);border-color:rgba(99,102,241,.25);color:#818cf8}
.inst-badge.done{background:rgba(34,197,94,.1);border-color:rgba(34,197,94,.2);color:#22c55e}
.inst-mid{margin:.45rem 0 .35rem;display:flex;align-items:center;gap:.3rem;flex-wrap:wrap}
.inst-dots{display:flex;align-items:center;gap:2px;flex-wrap:wrap}
.parc-dot{width:7px;height:7px;border-radius:50%;display:inline-block}
.parc-dot.done{background:#22c55e}
.parc-dot.current{background:var(--warning)}
.parc-dot.future{background:var(--bg3);border:1px solid var(--border2)}
.inst-bot{display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap}
.inst-val{font-size:.83rem;font-weight:700;color:var(--text);font-variant-numeric:tabular-nums}
.inst-remaining{font-size:.66rem;color:var(--text3)}
.inst-actions{display:flex;align-items:center;gap:.3rem}

/* ── Add installment form ── */
.add-inst-form{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:.85rem;margin-top:.75rem}
.add-inst-ttl{font-size:.68rem;font-weight:600;color:var(--text2);margin-bottom:.6rem;text-transform:uppercase;letter-spacing:.07em}
.form-row{display:flex;gap:.45rem;flex-wrap:wrap}
.form-row .form-group{flex:1;min-width:110px}
.form-row .form-group.sm{flex:0 0 90px;min-width:75px}
.toggle-row{display:flex;align-items:center;gap:.5rem;padding:.45rem 0;font-size:.8rem;color:var(--text2)}
.toggle-sw{position:relative;width:34px;height:18px;flex-shrink:0}
.toggle-sw input{opacity:0;width:0;height:0}
.toggle-sl{position:absolute;inset:0;background:var(--bg3);border:1px solid var(--border2);border-radius:99px;cursor:pointer;transition:.2s}
.toggle-sl::before{content:'';position:absolute;left:2px;top:50%;transform:translateY(-50%);width:12px;height:12px;border-radius:50%;background:var(--text3);transition:.2s}
.toggle-sw input:checked+.toggle-sl{background:var(--accent);border-color:var(--accent)}
.toggle-sw input:checked+.toggle-sl::before{left:18px;background:#09090b}

/* ── All installments tab ── */
.all-inst-filter{display:flex;gap:.35rem;margin-bottom:.85rem;flex-wrap:wrap}
.filter-chip{padding:.28rem .65rem;border-radius:99px;border:1px solid var(--border);background:none;color:var(--text3);font-size:.68rem;cursor:pointer;font-family:'Inter',sans-serif;transition:all .15s}
.filter-chip.active{background:var(--bg3);border-color:var(--border2);color:var(--text)}

/* ── Color swatches ── */
.color-swatches{display:flex;gap:.45rem;flex-wrap:wrap;margin-top:.3rem}
.color-swatch{width:28px;height:28px;border-radius:6px;cursor:pointer;border:2px solid transparent;transition:transform .15s,border-color .15s}
.color-swatch:hover{transform:scale(1.12)}
.color-swatch.selected{border-color:#fff;transform:scale(1.12)}

/* ── Card preview mini ── */
.card-preview{border-radius:12px;padding:.75rem .9rem;color:#fff;min-height:110px;display:flex;flex-direction:column;justify-content:space-between;margin-bottom:.75rem;transition:background .3s}
.card-preview .cp-top{display:flex;justify-content:space-between;align-items:center}
.card-preview .cp-chip{width:22px;height:15px;border-radius:3px;background:linear-gradient(135deg,#e8c97a,#c9a84c)}
.card-preview .cp-brand{font-size:.62rem;font-weight:800;letter-spacing:.06em;opacity:.9}
.card-preview .cp-name{font-size:.72rem;font-weight:600;opacity:.9;margin:.3rem 0 .1rem}
.card-preview .cp-num{font-size:.56rem;opacity:.5;letter-spacing:.1em}
.card-preview .cp-bot{font-size:.58rem;opacity:.55;text-align:right}

/* ── Inline edit ── */
.edit-wrap{display:inline-flex;align-items:center;gap:.25rem;flex-shrink:0}
.edit-val{cursor:pointer;font-weight:700;color:var(--text);font-size:.83rem;font-variant-numeric:tabular-nums;padding:.08rem .2rem;border-radius:4px;transition:all .15s}
.edit-val:hover{background:var(--bg3);color:var(--accent)}
.edit-form{display:none;align-items:center;gap:.18rem}
.edit-inp{width:90px;padding:.18rem .38rem;font-size:.8rem;border-radius:6px;background:var(--bg3);border:1px solid var(--accent);color:var(--text);font-family:'Inter',sans-serif;outline:none}
.edit-ok{padding:.18rem .38rem;font-size:.68rem;border:none;background:var(--accent);color:#09090b;border-radius:4px;cursor:pointer;font-family:'Inter',sans-serif;font-weight:700}
.del-btn{width:20px;height:20px;border-radius:50%;border:none;background:none;color:var(--text3);cursor:pointer;font-size:.85rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .15s;padding:0;line-height:1}
.del-btn:hover{background:rgba(239,68,68,.12);color:var(--danger)}
.tick{width:22px;height:22px;border-radius:50%;border:2px solid var(--border2);background:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.68rem;color:var(--text3);flex-shrink:0;transition:all .15s;padding:0;font-family:'Inter',sans-serif}
.tick:hover{border-color:var(--accent);color:var(--accent)}
.tick.on{background:var(--accent);border-color:var(--accent);color:#09090b;font-weight:700}

.empty-state{text-align:center;padding:2.5rem 1rem;color:var(--text3)}
.empty-state .es-icon{font-size:2.5rem;margin-bottom:.65rem}
.empty-state .es-title{font-size:.9rem;font-weight:600;color:var(--text2);margin-bottom:.3rem}
.empty-state .es-sub{font-size:.75rem}

@media(max-width:768px){
    .card-detail-grid{grid-template-columns:1fr}
    .cc-cards-grid{grid-template-columns:1fr}
    .proj-grid{grid-template-columns:repeat(3,1fr)}
}
</style>
@endpush

@section('content')

{{-- TABS NAV --}}
<div class="tabs-nav" id="tabs-nav">
    <button class="tab-btn active" onclick="setTab('visao-geral')" id="btn-visao-geral">💳 Visão Geral</button>
    @foreach($cards as $card)
        <button class="tab-btn" onclick="setTab('card-{{ $card->id }}')" id="btn-card-{{ $card->id }}">
            {{ $card->name }}
        </button>
    @endforeach
    <button class="tab-btn" onclick="setTab('todos-parc')" id="btn-todos-parc">📋 Todos</button>
</div>

{{-- ══════════════════════════════════════
     TAB: VISÃO GERAL
══════════════════════════════════════ --}}
<div id="tab-visao-geral" class="tab-panel active">

    {{-- Summary stats --}}
    <div class="cc-summary-grid">
        <div class="cc-sum-card">
            <div class="cc-sum-lbl">Total faturas</div>
            <div class="cc-sum-val" style="color:var(--danger)">R$ {{ number_format($totalFatura, 2, ',', '.') }}</div>
            <div class="cc-sum-hint">{{ $monthLabel }}</div>
        </div>
        <div class="cc-sum-card">
            <div class="cc-sum-lbl">Parcelamentos ativos</div>
            <div class="cc-sum-val">{{ $totalParcelamentos }}</div>
            <div class="cc-sum-hint">em {{ $totalCards }} cartão(ões)</div>
        </div>
        <div class="cc-sum-card">
            <div class="cc-sum-lbl">Compromisso futuro</div>
            <div class="cc-sum-val" style="color:var(--warning)">R$ {{ number_format($futureCommitment, 2, ',', '.') }}</div>
            <div class="cc-sum-hint">próximos 5 meses</div>
        </div>
        <div class="cc-sum-card">
            <div class="cc-sum-lbl">Faturas pagas</div>
            <div class="cc-sum-val" style="color:#22c55e">{{ $faturasPagas }}/{{ $totalCards }}</div>
            <div class="cc-sum-hint">este mês</div>
        </div>
    </div>

    {{-- Visual cards grid --}}
    @if($cards->isEmpty())
        <div class="empty-state">
            <div class="es-icon">💳</div>
            <div class="es-title">Nenhum cartão cadastrado</div>
            <div class="es-sub">Clique em "+ Novo cartão" para começar</div>
        </div>
    @else
        <div class="cc-cards-grid">
            @foreach($cards as $card)
            @php
                $used = $card->used_limit;
                $limit = (float)$card->credit_limit;
                $pct = $limit > 0 ? min(100, round($used / $limit * 100)) : 0;
            @endphp
            <div class="cc-card" style="background:linear-gradient(135deg,{{ $card->color }},{{ $card->color }}bb)"
                 onclick="setTab('card-{{ $card->id }}')">
                <div class="cc-card-top">
                    <div class="cc-chip"></div>
                    <div class="cc-brand">{{ $brandIcons[$card->brand] ?? '···' }}</div>
                </div>
                <div class="cc-card-mid">
                    <div class="cc-card-name">{{ Auth::user()->name }}</div>
                    <div class="cc-card-num">•••• •••• •••• ••••</div>
                    <div class="cc-card-name" style="font-size:.68rem;opacity:.7;margin-top:.15rem">{{ $card->name }}</div>
                </div>
                <div class="cc-card-bot">
                    <div>
                        <div class="cc-fatura-lbl">Fatura {{ $monthLabel }}</div>
                        <div class="cc-fatura-val">R$ {{ number_format($card->month_amount, 2, ',', '.') }}</div>
                        @if($limit > 0)
                        <div class="cc-limit-bar" style="width:100px">
                            <div class="cc-limit-fill" style="width:{{ $pct }}%"></div>
                        </div>
                        @endif
                    </div>
                    <div class="cc-venc">
                        Vence dia {{ $card->due_day }}<br>
                        @if($card->current_payment->paid)
                            <span style="color:#86efac;font-size:.62rem">✓ Pago</span>
                        @else
                            <span style="opacity:.55;font-size:.62rem">Pendente</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Projection 6 months --}}
        <div style="font-size:.65rem;text-transform:uppercase;letter-spacing:.1em;color:var(--text3);margin-bottom:.55rem">
            Projeção 6 meses — todas as faturas
        </div>
        <div class="proj-grid">
            @foreach($globalProjection as $i => $proj)
            <div class="proj-card {{ $i === 0 ? 'current-month' : '' }}">
                <div class="proj-lbl">{{ $proj['label'] }}</div>
                <div class="proj-val">R$ {{ number_format($proj['value'], 2, ',', '.') }}</div>
            </div>
            @endforeach
        </div>
    @endif
</div>

{{-- ══════════════════════════════════════
     TABS POR CARTÃO
══════════════════════════════════════ --}}
@foreach($cards as $card)
@php
    $limit  = (float)$card->credit_limit;
    $fatura = $card->month_amount;   // o que vence neste mês
    $used   = $card->used_limit;     // saldo devedor que ocupa o limite
    $avail  = max(0, $limit - $used);
    $pct    = $limit > 0 ? min(100, round($used / $limit * 100)) : 0;
    $payment = $card->current_payment;
@endphp
<div id="tab-card-{{ $card->id }}" class="tab-panel">
    <div class="card-detail-grid">

        {{-- LEFT: mini card + info + fatura --}}
        <div class="card-detail-left">
            {{-- Mini visual card --}}
            <div class="card-preview" style="background:linear-gradient(135deg,{{ $card->color }},{{ $card->color }}bb)">
                <div class="cp-top">
                    <div class="cp-chip"></div>
                    <div class="cp-brand">{{ $brandIcons[$card->brand] ?? '···' }}</div>
                </div>
                <div>
                    <div class="cp-name">{{ Auth::user()->name }}</div>
                    <div class="cp-num">•••• •••• •••• ••••</div>
                    <div class="cp-name" style="font-size:.64rem;opacity:.65;margin-top:.1rem">{{ $card->name }}</div>
                </div>
                <div class="cp-bot">Vence dia {{ $card->due_day }} · Fecha dia {{ $card->closing_day }}</div>
            </div>

            {{-- Card info --}}
            <div class="card-info-box">
                <div class="card-info-row">
                    <span class="card-info-lbl">Limite total</span>
                    <span class="card-info-val">R$ {{ number_format($limit, 2, ',', '.') }}</span>
                </div>
                <div class="card-info-row">
                    <span class="card-info-lbl">Fatura deste mês</span>
                    <span class="card-info-val" style="color:var(--danger)">R$ {{ number_format($fatura, 2, ',', '.') }}</span>
                </div>
                <div class="card-info-row">
                    <span class="card-info-lbl">Saldo devedor</span>
                    <span class="card-info-val" style="color:var(--warning)">R$ {{ number_format($used, 2, ',', '.') }}</span>
                </div>
                <div class="card-info-row">
                    <span class="card-info-lbl">Limite disponível</span>
                    <span class="card-info-val" style="color:#22c55e">R$ {{ number_format($avail, 2, ',', '.') }}</span>
                </div>
                @if($limit > 0)
                <div class="limit-avail" style="margin-top:.65rem">
                    <div class="limit-avail-fill" style="width:{{ $pct }}%;background:{{ $pct > 80 ? 'var(--danger)' : ($pct > 50 ? 'var(--warning)' : 'var(--accent)') }}"></div>
                </div>
                <div style="font-size:.6rem;color:var(--text3);margin-top:.3rem;text-align:right">{{ $pct }}% usado</div>
                @endif
            </div>

            {{-- Fatura payment --}}
            <div class="fatura-box {{ $payment->paid ? 'paid' : '' }}">
                <div>
                    <div class="fatura-lbl-sm">Fatura {{ $monthLabel }}</div>
                    <div class="fatura-amount">
                        R$ <span id="fatura-val-{{ $card->id }}">{{ number_format($payment->amount, 2, ',', '.') }}</span>
                    </div>
                    @if($payment->paid && $payment->paid_at)
                    <div style="font-size:.6rem;color:#22c55e;margin-top:.18rem">
                        Pago {{ \Carbon\Carbon::parse($payment->paid_at)->format('d/m/Y') }}
                    </div>
                    @endif
                </div>
                <div style="display:flex;flex-direction:column;gap:.35rem;align-items:flex-end">
                    <form method="POST" action="{{ route('creditcards.payments.toggle', $payment->id) }}">
                        @csrf
                        <button type="submit" class="tick {{ $payment->paid ? 'on' : '' }}" title="{{ $payment->paid ? 'Marcar pendente' : 'Marcar pago' }}">✓</button>
                    </form>
                    {{-- Inline edit amount --}}
                    <div class="edit-wrap">
                        <span class="edit-val" onclick="toggleEditFatura({{ $card->id }})">✏️</span>
                        <form class="edit-form" id="edit-fatura-{{ $card->id }}" method="POST"
                              action="{{ route('creditcards.payments.amount', $payment->id) }}" style="display:none">
                            @csrf @method('PATCH')
                            <input class="edit-inp" type="text" name="amount"
                                   value="{{ number_format($payment->amount, 2, ',', '.') }}"
                                   onkeydown="if(event.key==='Escape')toggleEditFatura({{ $card->id }})"
                                   oninput="maskMoney(this)">
                            <button type="submit" class="edit-ok">✓</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Projection mini --}}
            <div style="font-size:.62rem;text-transform:uppercase;letter-spacing:.09em;color:var(--text3);margin:.1rem 0 .38rem">
                Projeção
            </div>
            @foreach($card->projection as $i => $proj)
            <div style="display:flex;justify-content:space-between;font-size:.76rem;padding:.22rem 0;border-bottom:1px solid var(--border);{{ $i === 0 ? 'color:var(--text)' : 'color:var(--text2)' }}">
                <span>{{ $proj['label'] }}</span>
                <span style="font-weight:600;font-variant-numeric:tabular-nums">R$ {{ number_format($proj['value'], 2, ',', '.') }}</span>
            </div>
            @endforeach

            {{-- Edit card --}}
            <button type="button" class="btn btn-ghost btn-sm edit-card-btn" style="width:100%;margin-top:.5rem"
                    data-id="{{ $card->id }}"
                    data-name="{{ $card->name }}"
                    data-brand="{{ $card->brand }}"
                    data-limit="{{ number_format($card->credit_limit, 2, ',', '.') }}"
                    data-due="{{ $card->due_day }}"
                    data-closing="{{ $card->closing_day }}"
                    data-color="{{ $card->color }}"
                    onclick="openEditCard(this)">
                ✏️ Editar cartão
            </button>

            {{-- Remove card --}}
            <form method="POST" action="{{ route('creditcards.destroy', $card->id) }}" style="margin-top:.35rem"
                  onsubmit="return confirm('Desativar cartão {{ $card->name }}?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" style="width:100%">Desativar cartão</button>
            </form>
        </div>

        {{-- RIGHT: installments --}}
        <div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.65rem">
                <div style="font-size:.76rem;font-weight:600;color:var(--text)">
                    Parcelamentos
                    <span style="background:var(--bg3);border:1px solid var(--border);color:var(--text2);font-size:.62rem;padding:.06rem .38rem;border-radius:99px;margin-left:.35rem">
                        {{ $card->installments->count() }}
                    </span>
                </div>
            </div>

            <div class="inst-list" id="inst-list-{{ $card->id }}">
                @forelse($card->installments->sortBy(fn($i) => $i->isFullyPaid($card) ? 1 : 0) as $inst)
                @php
                    $isRec     = $inst->is_recurring;
                    $total     = $inst->total_installments;
                    $paidCount = $inst->paidInstallmentsCount($card);
                    $curr      = $inst->currentInstallment($card);
                    $fullyPaid = $inst->isFullyPaid($card);
                @endphp
                <div class="inst-item {{ $fullyPaid ? 'quitado' : '' }}">
                    <div class="inst-top">
                        <span class="inst-cat-icon">{{ $catIcons[$inst->category] ?? '📦' }}</span>
                        <span class="inst-desc" title="{{ $inst->description }}">{{ $inst->description }}</span>
                        @if($isRec)
                            <span class="inst-badge rec">Recorrente</span>
                        @elseif($fullyPaid)
                            <span class="inst-badge done">Quitado</span>
                        @else
                            <span class="inst-badge">{{ $curr }}/{{ $total }}</span>
                        @endif
                    </div>

                    @if(!$isRec && $total > 1)
                    <div class="inst-mid">
                        <div class="inst-dots" id="dots-{{ $inst->id }}">
                            @for($d = 1; $d <= min($total, 36); $d++)
                                @php
                                    $cls = $d <= $paidCount ? 'done' : ($d === $curr ? 'current' : 'future');
                                @endphp
                                <span class="parc-dot {{ $cls }}"></span>
                            @endfor
                            @if($total > 36)
                                <span style="font-size:.6rem;color:var(--text3)">+{{ $total - 36 }}</span>
                            @endif
                        </div>
                    </div>
                    @endif

                    <div class="inst-bot">
                        <div>
                            <div class="inst-val">R$ {{ number_format($inst->installment_amount, 2, ',', '.') }}/mês</div>
                            @if(!$isRec)
                            <div class="inst-remaining">
                                Restante: R$ {{ number_format($inst->getRemainingAmount($card), 2, ',', '.') }}
                                · até {{ $inst->getLastInstallmentMonth($card) }}
                            </div>
                            @endif
                        </div>
                        <div class="inst-actions">
                            @if(!$isRec && !$fullyPaid)
                            <form method="POST" action="{{ route('creditcards.installments.regress', $inst->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-ghost btn-sm" title="Voltar parcela" style="font-size:.65rem;padding:.22rem .45rem">◀</button>
                            </form>
                            <form method="POST" action="{{ route('creditcards.installments.advance', $inst->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-ghost btn-sm" title="Avançar parcela" style="font-size:.65rem;padding:.22rem .45rem">▶</button>
                            </form>
                            @endif
                            <button type="button" class="btn btn-ghost btn-sm edit-inst-btn" title="Editar"
                                    data-id="{{ $inst->id }}"
                                    data-desc="{{ $inst->description }}"
                                    data-cat="{{ $inst->category }}"
                                    data-total="{{ number_format($inst->total_amount, 2, ',', '.') }}"
                                    data-parc="{{ $inst->total_installments }}"
                                    data-date="{{ \Carbon\Carbon::parse($inst->purchase_date)->format('Y-m-d') }}"
                                    data-rec="{{ $inst->is_recurring ? 1 : 0 }}"
                                    onclick="openEditInst(this)"
                                    style="font-size:.65rem;padding:.22rem .45rem">✏️</button>
                            @if(!$fullyPaid)
                            <form method="POST" action="{{ route('creditcards.installments.payoff', $inst->id) }}"
                                  onsubmit="return confirm('Marcar como quitado?')">
                                @csrf
                                <button type="submit" class="btn btn-ghost btn-sm" style="font-size:.65rem;padding:.22rem .45rem;color:#22c55e">✓</button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('creditcards.installments.destroy', $inst->id) }}"
                                  onsubmit="return confirm('Remover parcelamento?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="del-btn" title="Remover">×</button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="empty-state" style="padding:1.5rem .5rem">
                    <div class="es-icon" style="font-size:1.8rem">🛍️</div>
                    <div class="es-sub">Nenhum parcelamento neste cartão</div>
                </div>
                @endforelse
            </div>

            {{-- Add installment form --}}
            <div class="add-inst-form">
                <div class="add-inst-ttl">+ Adicionar parcelamento</div>
                <form method="POST" action="{{ route('creditcards.installments.store', $card->id) }}">
                    @csrf
                    <div class="form-row">
                        <div class="form-group" style="flex:2;min-width:150px">
                            <label>Descrição</label>
                            <input type="text" name="description" required placeholder="Ex: iPhone 15">
                        </div>
                        <div class="form-group">
                            <label>Categoria</label>
                            <select name="category" required>
                                <option value="compras">🛍️ Compras</option>
                                <option value="assinatura">🔄 Assinatura</option>
                                <option value="eletronico">💻 Eletrônico</option>
                                <option value="casa">🏠 Casa</option>
                                <option value="saude">💊 Saúde</option>
                                <option value="outros">📦 Outros</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Valor total (R$)</label>
                            <input type="text" name="total_amount" required placeholder="1.500,00"
                                   class="money-input" oninput="maskMoney(this)">
                        </div>
                        <div class="form-group sm">
                            <label>Parcelas</label>
                            <input type="number" name="total_installments" required min="1" max="72" value="1"
                                   id="parc-count-{{ $card->id }}" oninput="updateParcLabel({{ $card->id }})">
                        </div>
                        <div class="form-group">
                            <label>Data da compra</label>
                            <input type="date" name="purchase_date" required value="{{ now()->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="toggle-row">
                        <label class="toggle-sw">
                            <input type="checkbox" name="is_recurring" value="1" id="rec-toggle-{{ $card->id }}"
                                   onchange="toggleRecurring({{ $card->id }})">
                            <span class="toggle-sl"></span>
                        </label>
                        <span>Recorrente mensal (assinatura sem fim)</span>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm" style="width:100%;margin-top:.35rem">
                        + Adicionar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach

{{-- ══════════════════════════════════════
     TAB: TODOS OS PARCELAMENTOS
══════════════════════════════════════ --}}
<div id="tab-todos-parc" class="tab-panel">

    <div class="all-inst-filter">
        <button class="filter-chip active" onclick="filterCard('all', this)">Todos</button>
        @foreach($cards as $card)
        <button class="filter-chip" onclick="filterCard('{{ $card->id }}', this)" data-card="{{ $card->id }}">
            {{ $card->name }}
        </button>
        @endforeach
    </div>

    <div class="inst-list" id="all-inst-list">
        @php $allInsts = $cards->flatMap(fn($c) => $c->installments->map(fn($i) => ['inst' => $i, 'card' => $c]))->sortBy(fn($x) => $x['inst']->isFullyPaid($x['card']) ? 1 : 0); @endphp
        @forelse($allInsts as $entry)
        @php
            $inst = $entry['inst']; $card = $entry['card'];
            $isRec = $inst->is_recurring;
            $fullyPaid = $inst->isFullyPaid($card);
        @endphp
        <div class="inst-item {{ $fullyPaid ? 'quitado' : '' }}" data-card-id="{{ $card->id }}">
            <div class="inst-top">
                <span class="inst-cat-icon">{{ $catIcons[$inst->category] ?? '📦' }}</span>
                <span class="inst-desc">{{ $inst->description }}</span>
                <span class="inst-badge" style="background:linear-gradient(135deg,{{ $card->color }}22,{{ $card->color }}11);border-color:{{ $card->color }}44;color:var(--text2)">
                    {{ $card->name }}
                </span>
                @if($isRec)
                    <span class="inst-badge rec">Rec.</span>
                @elseif($fullyPaid)
                    <span class="inst-badge done">Quitado</span>
                @else
                    <span class="inst-badge">{{ $inst->currentInstallment($card) }}/{{ $inst->total_installments }}</span>
                @endif
            </div>
            <div class="inst-bot" style="margin-top:.45rem">
                <div>
                    <div class="inst-val">R$ {{ number_format($inst->installment_amount, 2, ',', '.') }}/mês</div>
                    @if(!$isRec && !$fullyPaid)
                    <div class="inst-remaining">
                        Restante: R$ {{ number_format($inst->getRemainingAmount($card), 2, ',', '.') }}
                        · até {{ $inst->getLastInstallmentMonth($card) }}
                    </div>
                    @endif
                </div>
                <div class="inst-actions">
                    @if(!$isRec && !$fullyPaid)
                    <form method="POST" action="{{ route('creditcards.installments.regress', $inst->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm" title="Voltar parcela" style="font-size:.65rem;padding:.22rem .45rem">◀</button>
                    </form>
                    <form method="POST" action="{{ route('creditcards.installments.advance', $inst->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm" title="Avançar parcela" style="font-size:.65rem;padding:.22rem .45rem">▶</button>
                    </form>
                    @endif
                    <button type="button" class="btn btn-ghost btn-sm edit-inst-btn" title="Editar"
                            data-id="{{ $inst->id }}"
                            data-desc="{{ $inst->description }}"
                            data-cat="{{ $inst->category }}"
                            data-total="{{ number_format($inst->total_amount, 2, ',', '.') }}"
                            data-parc="{{ $inst->total_installments }}"
                            data-date="{{ \Carbon\Carbon::parse($inst->purchase_date)->format('Y-m-d') }}"
                            data-rec="{{ $inst->is_recurring ? 1 : 0 }}"
                            onclick="openEditInst(this)"
                            style="font-size:.65rem;padding:.22rem .45rem">✏️</button>
                    @if(!$fullyPaid)
                    <form method="POST" action="{{ route('creditcards.installments.payoff', $inst->id) }}"
                          onsubmit="return confirm('Quitar?')">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm" style="font-size:.65rem;padding:.22rem .45rem;color:#22c55e">✓</button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('creditcards.installments.destroy', $inst->id) }}"
                          onsubmit="return confirm('Remover?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="del-btn">×</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <div class="es-icon">🛍️</div>
            <div class="es-title">Nenhum parcelamento</div>
            <div class="es-sub">Adicione parcelamentos nos cartões</div>
        </div>
        @endforelse
    </div>
</div>

{{-- ══════════════════════════════════════
     MODAL: NOVO CARTÃO
══════════════════════════════════════ --}}
<div class="modal-backdrop" id="modal-novo-cartao" onclick="if(event.target===this)closeModal('modal-novo-cartao')">
    <div class="modal" style="max-width:460px">
        <div class="modal-title">💳 Novo Cartão</div>

        {{-- Card preview --}}
        <div class="card-preview" id="modal-card-preview" style="background:linear-gradient(135deg,#7c3aed,#7c3aedbb)">
            <div class="cp-top">
                <div class="cp-chip"></div>
                <div class="cp-brand" id="prev-brand">VISA</div>
            </div>
            <div>
                <div class="cp-name" id="prev-holder">{{ Auth::user()->name }}</div>
                <div class="cp-num">•••• •••• •••• ••••</div>
                <div class="cp-name" id="prev-name" style="font-size:.64rem;opacity:.65;margin-top:.1rem">Nome do cartão</div>
            </div>
            <div class="cp-bot" id="prev-venc">Vence dia — · Fecha dia —</div>
        </div>

        <form method="POST" action="{{ route('creditcards.store') }}">
            @csrf
            <input type="hidden" name="color" id="selected-color" value="#7c3aed">

            <div class="form-group">
                <label>Nome do cartão</label>
                <input type="text" name="name" required placeholder="Ex: Nubank Roxo"
                       oninput="document.getElementById('prev-name').textContent=this.value||'Nome do cartão'">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Bandeira</label>
                    <select name="brand" required onchange="updatePrevBrand(this.value)">
                        <option value="visa">VISA</option>
                        <option value="mastercard">MASTERCARD</option>
                        <option value="elo">ELO</option>
                        <option value="amex">AMEX</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Limite (R$)</label>
                    <input type="text" name="credit_limit" required placeholder="5.000,00" class="money-input" oninput="maskMoney(this)">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group sm">
                    <label>Dia venc.</label>
                    <input type="number" name="due_day" required min="1" max="31" placeholder="10"
                           oninput="updatePrevVenc()">
                </div>
                <div class="form-group sm">
                    <label>Dia fech.</label>
                    <input type="number" name="closing_day" required min="1" max="31" placeholder="3"
                           oninput="updatePrevVenc()">
                </div>
            </div>

            <div class="form-group">
                <label>Cor do cartão</label>
                <div class="color-swatches" id="color-swatches">
                    @php
                    $swatches = ['#7c3aed','#2563eb','#059669','#dc2626','#d97706','#0891b2','#be185d','#374151'];
                    @endphp
                    @foreach($swatches as $sw)
                    <div class="color-swatch {{ $sw === '#7c3aed' ? 'selected' : '' }}"
                         style="background:linear-gradient(135deg,{{ $sw }},{{ $sw }}bb)"
                         onclick="selectColor('{{ $sw }}', this)"
                         title="{{ $sw }}"></div>
                    @endforeach
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('modal-novo-cartao')">Cancelar</button>
                <button type="submit" class="btn btn-primary btn-sm">Salvar cartão</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════
     MODAL: EDITAR CARTÃO
══════════════════════════════════════ --}}
<div class="modal-backdrop" id="modal-editar-cartao" onclick="if(event.target===this)closeModal('modal-editar-cartao')">
    <div class="modal" style="max-width:460px">
        <div class="modal-title">✏️ Editar Cartão</div>
        <form method="POST" id="form-edit-cartao" action="">
            @csrf @method('PATCH')
            <input type="hidden" name="color" id="edit-selected-color" value="#7c3aed">

            <div class="form-group">
                <label>Nome do cartão</label>
                <input type="text" name="name" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Bandeira</label>
                    <select name="brand" required>
                        <option value="visa">VISA</option>
                        <option value="mastercard">MASTERCARD</option>
                        <option value="elo">ELO</option>
                        <option value="amex">AMEX</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Limite (R$)</label>
                    <input type="text" name="credit_limit" required class="money-input" oninput="maskMoney(this)">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group sm">
                    <label>Dia venc.</label>
                    <input type="number" name="due_day" required min="1" max="31">
                </div>
                <div class="form-group sm">
                    <label>Dia fech.</label>
                    <input type="number" name="closing_day" required min="1" max="31">
                </div>
            </div>

            <div class="form-group">
                <label>Cor do cartão</label>
                <div class="color-swatches" id="edit-color-swatches">
                    @foreach($swatches as $sw)
                    <div class="color-swatch" data-color="{{ $sw }}"
                         style="background:linear-gradient(135deg,{{ $sw }},{{ $sw }}bb)"
                         onclick="selectEditColor('{{ $sw }}', this)"
                         title="{{ $sw }}"></div>
                    @endforeach
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('modal-editar-cartao')">Cancelar</button>
                <button type="submit" class="btn btn-primary btn-sm">Salvar alterações</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════
     MODAL: EDITAR PARCELAMENTO
══════════════════════════════════════ --}}
<div class="modal-backdrop" id="modal-editar-parcelamento" onclick="if(event.target===this)closeModal('modal-editar-parcelamento')">
    <div class="modal" style="max-width:460px">
        <div class="modal-title">✏️ Editar Parcelamento</div>
        <form method="POST" id="form-edit-inst" action="">
            @csrf @method('PATCH')

            <div class="form-group">
                <label>Descrição</label>
                <input type="text" name="description" required placeholder="Ex: iPhone 15">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Categoria</label>
                    <select name="category" required>
                        <option value="compras">🛍️ Compras</option>
                        <option value="assinatura">🔄 Assinatura</option>
                        <option value="eletronico">💻 Eletrônico</option>
                        <option value="casa">🏠 Casa</option>
                        <option value="saude">💊 Saúde</option>
                        <option value="outros">📦 Outros</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Valor total (R$)</label>
                    <input type="text" name="total_amount" required class="money-input" oninput="maskMoney(this)">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group sm">
                    <label>Parcelas</label>
                    <input type="number" name="total_installments" required min="1" max="72">
                </div>
                <div class="form-group">
                    <label>Data da compra</label>
                    <input type="date" name="purchase_date" required>
                </div>
            </div>

            <div class="toggle-row">
                <label class="toggle-sw">
                    <input type="checkbox" name="is_recurring" value="1" id="edit-inst-rec">
                    <span class="toggle-sl"></span>
                </label>
                <span>Recorrente mensal (assinatura sem fim)</span>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('modal-editar-parcelamento')">Cancelar</button>
                <button type="submit" class="btn btn-primary btn-sm">Salvar alterações</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Tabs ──────────────────────────────────────────────────────────
function setTab(id) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    const panel = document.getElementById('tab-' + id);
    const btn   = document.getElementById('btn-' + id);
    if (panel) panel.classList.add('active');
    if (btn)   btn.classList.add('active');
}

// Read URL hash
document.addEventListener('DOMContentLoaded', function () {
    const h = location.hash.replace('#', '');
    if (h) setTab(h);
});

// ── Modal ─────────────────────────────────────────────────────────
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

// ── Card preview update ───────────────────────────────────────────
function updatePrevBrand(val) {
    const map = {visa:'VISA',mastercard:'MC',elo:'ELO',amex:'AMEX',outro:'···'};
    document.getElementById('prev-brand').textContent = map[val] || '···';
}
function updatePrevVenc() {
    const due = document.querySelector('[name=due_day]').value;
    const cls = document.querySelector('[name=closing_day]').value;
    document.getElementById('prev-venc').textContent =
        'Vence dia ' + (due || '—') + ' · Fecha dia ' + (cls || '—');
}
function selectColor(color, el) {
    document.getElementById('selected-color').value = color;
    document.getElementById('modal-card-preview').style.background =
        'linear-gradient(135deg,' + color + ',' + color + 'bb)';
    document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('selected'));
    el.classList.add('selected');
}

// ── Money mask ────────────────────────────────────────────────────
function maskMoney(el) {
    let v = el.value.replace(/\D/g, '');
    if (!v) { el.value = ''; return; }
    v = (parseInt(v, 10) / 100).toFixed(2);
    el.value = parseFloat(v).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}
document.querySelectorAll('.money-input').forEach(el => {
    el.addEventListener('input', () => maskMoney(el));
});

// ── Fatura inline edit ────────────────────────────────────────────
function toggleEditFatura(cardId) {
    const f = document.getElementById('edit-fatura-' + cardId);
    f.style.display = f.style.display === 'none' ? 'flex' : 'none';
}

// ── Recurring toggle (disable parcelas field) ─────────────────────
function toggleRecurring(cardId) {
    const cb   = document.getElementById('rec-toggle-' + cardId);
    const inp  = document.getElementById('parc-count-' + cardId);
    if (cb.checked) {
        inp.value = 1;
        inp.disabled = true;
    } else {
        inp.disabled = false;
    }
}

// ── Filter all-installments by card ──────────────────────────────
function filterCard(cardId, btn) {
    document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('#all-inst-list .inst-item').forEach(el => {
        if (cardId === 'all' || el.dataset.cardId == cardId) {
            el.style.display = '';
        } else {
            el.style.display = 'none';
        }
    });
}

// ── Editar cartão ─────────────────────────────────────────────────
const ROUTE_CARD_UPDATE = "{{ route('creditcards.update', 'CARDID') }}";
function openEditCard(btn) {
    const d = btn.dataset;
    const f = document.getElementById('form-edit-cartao');
    f.action = ROUTE_CARD_UPDATE.replace('CARDID', d.id);
    f.querySelector('[name=name]').value         = d.name;
    f.querySelector('[name=brand]').value        = d.brand;
    f.querySelector('[name=credit_limit]').value = d.limit;
    f.querySelector('[name=due_day]').value      = d.due;
    f.querySelector('[name=closing_day]').value  = d.closing;
    document.getElementById('edit-selected-color').value = d.color;
    document.querySelectorAll('#edit-color-swatches .color-swatch').forEach(s => {
        s.classList.toggle('selected', s.dataset.color === d.color);
    });
    openModal('modal-editar-cartao');
}
function selectEditColor(color, el) {
    document.getElementById('edit-selected-color').value = color;
    document.querySelectorAll('#edit-color-swatches .color-swatch').forEach(s => s.classList.remove('selected'));
    el.classList.add('selected');
}

// ── Editar parcelamento ───────────────────────────────────────────
const ROUTE_INST_UPDATE = "{{ route('creditcards.installments.update', 'INSTID') }}";
function openEditInst(btn) {
    const d = btn.dataset;
    const f = document.getElementById('form-edit-inst');
    f.action = ROUTE_INST_UPDATE.replace('INSTID', d.id);
    f.querySelector('[name=description]').value        = d.desc;
    f.querySelector('[name=category]').value           = d.cat;
    f.querySelector('[name=total_amount]').value       = d.total;
    f.querySelector('[name=total_installments]').value = d.parc;
    f.querySelector('[name=purchase_date]').value      = d.date;
    document.getElementById('edit-inst-rec').checked   = d.rec === '1';
    openModal('modal-editar-parcelamento');
}
</script>
@endpush
