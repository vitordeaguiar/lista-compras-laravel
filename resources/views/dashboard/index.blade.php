@extends('layouts.app')
@section('title','Dashboard')
@section('page-title','Dashboard')
@section('page-sub'){{ now()->locale('pt_BR')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}@endsection
@section('page-actions')
    <a href="{{ route('lists.index') }}" class="btn btn-primary btn-sm">+ Nova Lista</a>
@endsection

@push('styles')
<style>
/* ── Finance cards ── */
.dash-fcard{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:.9rem 1rem;transition:border-color .2s}
.dash-fcard:hover{border-color:var(--border2)}
.dash-fcard.featured{border-color:rgba(45,212,191,.25);background:var(--adim)}
.dash-fcard.danger{border-color:rgba(239,68,68,.2);background:var(--danger-dim)}
.dash-fcard.warning{border-color:rgba(245,158,11,.2);background:var(--warning-dim)}
.dash-fcard-ic{width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;margin-bottom:.5rem;background:var(--bg3)}
.dash-fcard.featured .dash-fcard-ic{background:var(--adim)}
.dash-fcard.danger .dash-fcard-ic{background:var(--danger-dim)}
.dash-fcard.warning .dash-fcard-ic{background:var(--warning-dim)}
.dash-fcard-lbl{font-size:.6rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--text3);margin-bottom:.2rem}
.dash-fcard-val{font-size:1.3rem;font-weight:800;letter-spacing:-.025em;font-variant-numeric:tabular-nums;line-height:1}
.dash-fcard-diff{font-size:.6rem;margin-top:.2rem}

/* ── Budget bar ── */
.dash-budget{background:linear-gradient(135deg,rgba(45,212,191,.06) 0%,rgba(99,102,241,.06) 100%);border:1px solid rgba(45,212,191,.15);border-radius:var(--radius-lg);padding:.9rem 1.1rem}
.dash-bitem{display:inline-flex;align-items:center;gap:.22rem;font-size:.58rem;color:var(--text2)}
.dash-bdot{width:6px;height:6px;border-radius:50%;display:inline-block;flex-shrink:0}

/* ── Cards ── */
.dash-card{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden}
.dash-card-hdr{display:flex;align-items:center;justify-content:space-between;padding:.62rem .88rem;border-bottom:1px solid var(--border)}
.dash-card-title{font-size:.72rem;font-weight:700;display:flex;align-items:center;gap:.35rem}
.dash-card-act{font-size:.6rem;color:var(--accent);text-decoration:none;font-weight:500}
.dash-card-act:hover{text-decoration:underline}

/* ── List rows ── */
.dash-lrow{display:flex;align-items:center;gap:.58rem;padding:.55rem .82rem;border-bottom:1px solid var(--border);text-decoration:none;color:inherit;transition:background .15s}
.dash-lrow:last-of-type{border-bottom:none}
.dash-lrow:hover{background:rgba(255,255,255,.02)}
.dash-lrow-ic{width:26px;height:26px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:.7rem;flex-shrink:0}
.dash-lrow-body{flex:1;min-width:0}
.dash-lrow-name{font-size:.7rem;font-weight:600;margin-bottom:.07rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dash-lrow-meta{font-size:.58rem;color:var(--text3)}
.dash-pbar{width:42px;height:2px;background:var(--bg3);border-radius:99px;overflow:hidden}
.dash-pfill{height:100%;background:var(--accent);border-radius:99px}

/* ── Bill rows ── */
.dash-vrow{display:flex;align-items:center;gap:.52rem;padding:.5rem .82rem;border-bottom:1px solid var(--border)}
.dash-vrow:last-child{border-bottom:none}
.dash-vrow-ic{width:24px;height:24px;border-radius:5px;display:flex;align-items:center;justify-content:center;font-size:.65rem;flex-shrink:0}
.dash-vrow-body{flex:1;min-width:0}
.dash-vrow-name{font-size:.68rem;font-weight:600;margin-bottom:.06rem}
.dash-vrow-meta{font-size:.56rem;color:var(--text3);display:flex;align-items:center;gap:.25rem}
.dash-vbdg{font-size:.5rem;font-weight:600;padding:.05rem .28rem;border-radius:4px}
.dash-vlate{background:var(--danger-dim);color:var(--danger)}
.dash-vwarn{background:var(--warning-dim);color:var(--warning)}

/* ── Activity rows ── */
.dash-arow{display:flex;align-items:flex-start;gap:.5rem;padding:.48rem .82rem;border-bottom:1px solid var(--border)}
.dash-arow:last-child{border-bottom:none}
.dash-adot{width:6px;height:6px;border-radius:50%;flex-shrink:0;margin-top:4px}

/* ── Charts ── */
.dash-chart-ctrl{display:flex;align-items:center;gap:.35rem;padding:.42rem .78rem;border-bottom:1px solid var(--border);background:rgba(255,255,255,.01)}
.dash-seg{display:flex;gap:.1rem;background:var(--bg3);border-radius:7px;padding:.12rem}
.dash-seg-btn{font-size:.6rem;font-weight:600;padding:.18rem .52rem;border-radius:5px;cursor:pointer;border:none;font-family:inherit;color:var(--text3);background:transparent;transition:all .15s}
.dash-seg-btn.on{background:var(--bg2);color:var(--text);box-shadow:0 1px 3px rgba(0,0,0,.4)}
.dash-sel{background:var(--bg3);border:1px solid var(--border);color:var(--text2);padding:.18rem .48rem;border-radius:6px;font-family:inherit;font-size:.6rem;outline:none;cursor:pointer}
.dash-sel:focus{border-color:var(--accent)}
.dash-bar-grp{flex:1;display:flex;flex-direction:column;align-items:center;gap:.15rem;padding:0 .1rem}
.dash-bar-wrap{display:flex;gap:.08rem;align-items:flex-end;width:100%}
.dash-bar{flex:1;border-radius:3px 3px 0 0;min-height:2px;cursor:pointer;transition:opacity .15s}
.dash-bar:hover{opacity:.7}
.dash-bar-lbl{font-size:.44rem;color:var(--text3);font-weight:500;white-space:nowrap;text-align:center;width:100%;overflow:hidden;text-overflow:ellipsis}
.dash-cleg{display:flex;align-items:center;gap:.2rem;font-size:.55rem;color:var(--text3)}
.dash-cleg-dot{width:6px;height:6px;border-radius:2px;flex-shrink:0}

@media(max-width:768px){
    .dash-4col{grid-template-columns:1fr 1fr!important}
    .dash-3col{grid-template-columns:1fr!important}
    .dash-2col{grid-template-columns:1fr!important}
}
</style>
@endpush

@section('content')

{{-- ── 4 FINANCE CARDS ── --}}
<div class="dash-4col" style="display:grid;grid-template-columns:repeat(4,1fr);gap:.6rem;margin-bottom:.75rem">

    <div class="dash-fcard">
        <div class="dash-fcard-ic">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
        </div>
        <div class="dash-fcard-lbl">Entradas {{ now()->locale('pt_BR')->isoFormat('MMM') }}</div>
        <div class="dash-fcard-val" style="color:var(--green)">R$ {{ number_format($totalIncome, 0, ',', '.') }}</div>
        <div class="dash-fcard-diff" style="color:var(--green)">↑ salário recebido</div>
    </div>

    <div class="dash-fcard danger">
        <div class="dash-fcard-ic">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg>
        </div>
        <div class="dash-fcard-lbl">Saídas {{ now()->locale('pt_BR')->isoFormat('MMM') }}</div>
        <div class="dash-fcard-val" style="color:var(--danger)">R$ {{ number_format($totalOut, 0, ',', '.') }}</div>
        <div class="dash-fcard-diff" style="color:var(--text3)">fixos + variáveis + cartões</div>
    </div>

    <div class="dash-fcard featured">
        <div class="dash-fcard-ic">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="dash-fcard-lbl">Saldo livre</div>
        <div class="dash-fcard-val" style="color:{{ $balance >= 0 ? 'var(--accent)' : 'var(--danger)' }}">
            R$ {{ number_format(abs($balance), 0, ',', '.') }}
        </div>
        <div class="dash-fcard-diff" style="color:var(--warning)">{{ $budgetPct }}% comprometido</div>
    </div>

    <div class="dash-fcard warning">
        <div class="dash-fcard-ic">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--warning)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="dash-fcard-lbl">A pagar</div>
        <div class="dash-fcard-val" style="color:var(--warning)">{{ $upcomingBills->count() }} contas</div>
        <div class="dash-fcard-diff" style="color:var(--warning)">vence em breve</div>
    </div>

</div>

{{-- ── BUDGET BAR ── --}}
<div class="dash-budget" style="margin-bottom:.75rem">
    <div style="display:flex;justify-content:space-between;margin-bottom:.4rem">
        <span style="font-size:.7rem;font-weight:700;color:var(--accent)">
            Orçamento de {{ now()->locale('pt_BR')->isoFormat('MMMM') }} — {{ $budgetPct }}% comprometido
        </span>
        <span style="font-size:.68rem;font-weight:700;color:var(--warning)">
            R$ {{ number_format($balance, 2, ',', '.') }} restam
        </span>
    </div>
    <div style="height:5px;background:rgba(255,255,255,.07);border-radius:99px;overflow:hidden;margin-bottom:.35rem">
        <div style="height:100%;width:{{ $budgetPct }}%;background:linear-gradient(90deg,var(--green) 0%,var(--warning) 65%,var(--danger) 90%);border-radius:99px"></div>
    </div>
    <div style="display:flex;gap:.75rem;flex-wrap:wrap">
        <span class="dash-bitem"><span class="dash-bdot" style="background:var(--blue)"></span>Fixos R$ {{ number_format($totalFixed, 0, ',', '.') }}</span>
        <span class="dash-bitem"><span class="dash-bdot" style="background:var(--warning)"></span>Variáveis R$ {{ number_format($totalVariable, 0, ',', '.') }}</span>
        <span class="dash-bitem"><span class="dash-bdot" style="background:var(--accent)"></span>Mercado R$ {{ number_format($totalSupermarket, 0, ',', '.') }}</span>
        @if($totalCreditCard > 0)
        <span class="dash-bitem"><span class="dash-bdot" style="background:#f43f5e"></span>Cartões R$ {{ number_format($totalCreditCard, 0, ',', '.') }}</span>
        @endif
        <span class="dash-bitem"><span class="dash-bdot" style="background:var(--bg3)"></span>Livre R$ {{ number_format($balance, 0, ',', '.') }}</span>
    </div>
</div>

{{-- ── 3 COLUNAS: LISTAS + CONTAS + ATIVIDADE ── --}}
<div class="dash-3col" style="display:grid;grid-template-columns:1.1fr 1fr 1fr;gap:.7rem;margin-bottom:.75rem">

    {{-- Listas abertas --}}
    <div class="dash-card">
        <div class="dash-card-hdr">
            <span class="dash-card-title">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--text2)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                Listas abertas
            </span>
            <a href="{{ route('lists.index') }}" class="dash-card-act">Ver todas →</a>
        </div>
        @forelse($openLists->take(2) as $list)
            @php
                $total  = $list->items->count();
                $bought = $list->items->where('purchased', true)->count();
                $pct    = $total > 0 ? round($bought / $total * 100) : 0;
                $pending = $list->items->where('purchased', false)->sum(fn($i) => $i->price * ($i->qty ?? 1));
            @endphp
            <a href="{{ route('lists.show', $list) }}" class="dash-lrow">
                <div class="dash-lrow-ic" style="background:var(--adim)">🛒</div>
                <div class="dash-lrow-body">
                    <div class="dash-lrow-name">{{ $list->name }}</div>
                    <div class="dash-lrow-meta">
                        {{ $total }} itens
                        @if($pending > 0) · <span style="color:var(--accent)">R$ {{ number_format($pending, 2, ',', '.') }}</span>@endif
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.2rem">
                    <div class="dash-pbar"><div class="dash-pfill" style="width:{{ $pct }}%"></div></div>
                    <div style="font-size:.55rem;color:var(--text3)">{{ $bought }}/{{ $total }}</div>
                </div>
            </a>
        @empty
            <div style="padding:1.5rem;text-align:center;color:var(--text3);font-size:.75rem">Nenhuma lista aberta</div>
        @endforelse
        <div style="padding:.5rem .82rem">
            <a href="{{ route('lists.index') }}" class="btn btn-primary" style="width:100%;justify-content:center;font-size:.68rem">+ Nova lista</a>
        </div>
    </div>

    {{-- Contas a vencer --}}
    <div class="dash-card">
        <div class="dash-card-hdr">
            <span class="dash-card-title">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--text2)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                Contas a vencer
            </span>
            <a href="{{ route('finance.index') }}" class="dash-card-act">Financeiro →</a>
        </div>
        @forelse($upcomingBills as $bill)
            <div class="dash-vrow">
                <div class="dash-vrow-ic" style="background:var(--warning-dim)">{{ $bill->fixedCost->icon ?? '💳' }}</div>
                <div class="dash-vrow-body">
                    <div class="dash-vrow-name">{{ $bill->fixedCost->name ?? 'Conta' }}</div>
                    <div class="dash-vrow-meta">
                        dia {{ $bill->due_day_num }}/{{ now()->format('m') }}
                        @if($bill->days_until < 0)
                            <span class="dash-vbdg dash-vlate">atrasado</span>
                        @elseif($bill->days_until === 0)
                            <span class="dash-vbdg dash-vwarn">hoje!</span>
                        @else
                            <span class="dash-vbdg dash-vwarn">{{ $bill->days_until }}d</span>
                        @endif
                    </div>
                </div>
                <div style="font-size:.68rem;font-weight:700;color:{{ $bill->days_until < 0 ? 'var(--danger)' : 'var(--warning)' }}">
                    R$ {{ number_format($bill->amount, 0, ',', '.') }}
                </div>
            </div>
        @empty
            <div style="padding:1.5rem;text-align:center;color:var(--text3);font-size:.75rem">✅ Nenhuma conta pendente</div>
        @endforelse
    </div>

    {{-- Atividade recente --}}
    <div class="dash-card">
        <div class="dash-card-hdr">
            <span class="dash-card-title">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--text2)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                Atividade recente
            </span>
        </div>
        @forelse($recentActivity as $act)
            <div class="dash-arow">
                <div class="dash-adot" style="background:{{ $act['color'] === 'accent' ? 'var(--accent)' : ($act['color'] === 'danger' ? 'var(--danger)' : 'var(--blue)') }}"></div>
                <div>
                    <div style="font-size:.67rem;color:var(--text2)">
                        {{ $act['text'] }}
                        @if($act['value'])<strong style="color:var(--text)"> — {{ $act['value'] }}</strong>@endif
                    </div>
                    <div style="font-size:.56rem;color:var(--text3);margin-top:.08rem">{{ $act['time'] }}</div>
                </div>
            </div>
        @empty
            <div style="padding:1.5rem;text-align:center;color:var(--text3);font-size:.75rem">Nenhuma atividade ainda.</div>
        @endforelse
    </div>

</div>

{{-- ── 2 GRÁFICOS ── --}}
<div class="dash-2col" style="display:grid;grid-template-columns:1fr 1fr;gap:.7rem">

    {{-- GRÁFICO FINANCEIRO --}}
    <div class="dash-card">
        <div class="dash-card-hdr">
            <span class="dash-card-title" id="finTitle">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--text2)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                <span id="finTitleText">Financeiro — Semanal</span>
            </span>
            <a href="{{ route('finance.index') }}" class="dash-card-act">Ver financeiro →</a>
        </div>
        <div class="dash-chart-ctrl">
            <div class="dash-seg">
                <button class="dash-seg-btn on" onclick="setMode('fin','semanal',this)">Semanal</button>
                <button class="dash-seg-btn" onclick="setMode('fin','mensal',this)">Mensal</button>
            </div>
            <select class="dash-sel" id="finMonthSel" onchange="renderChart('fin')">
                @for($i = 5; $i >= 0; $i--)
                    <option value="{{ now()->subMonths($i)->format('Y-m') }}" {{ $i === 0 ? 'selected' : '' }}>
                        {{ now()->subMonths($i)->locale('pt_BR')->isoFormat('MMM/YY') }}
                    </option>
                @endfor
            </select>
        </div>
        <div style="padding:.6rem .78rem .5rem">
            <div style="height:72px;display:flex;align-items:flex-end;gap:0" id="finChart"></div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:.3rem">
                <div style="display:flex;gap:.5rem" id="finLegend"></div>
                <div style="font-size:.6rem;color:var(--text2)" id="finTotal"></div>
            </div>
        </div>
    </div>

    {{-- GRÁFICO MERCADO --}}
    <div class="dash-card">
        <div class="dash-card-hdr">
            <span class="dash-card-title" id="mktTitle">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--text2)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                <span id="mktTitleText">Mercado — Semanal</span>
            </span>
            <a href="{{ route('history.index') }}" class="dash-card-act">Ver histórico →</a>
        </div>
        <div class="dash-chart-ctrl">
            <div class="dash-seg">
                <button class="dash-seg-btn on" onclick="setMode('mkt','semanal',this)">Semanal</button>
                <button class="dash-seg-btn" onclick="setMode('mkt','mensal',this)">Mensal</button>
            </div>
            <select class="dash-sel" id="mktMonthSel" onchange="renderChart('mkt')">
                @for($i = 5; $i >= 0; $i--)
                    <option value="{{ now()->subMonths($i)->format('Y-m') }}" {{ $i === 0 ? 'selected' : '' }}>
                        {{ now()->subMonths($i)->locale('pt_BR')->isoFormat('MMM/YY') }}
                    </option>
                @endfor
            </select>
        </div>
        <div style="padding:.6rem .78rem .5rem">
            <div style="height:72px;display:flex;align-items:flex-end;gap:0" id="mktChart"></div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:.3rem">
                <div style="display:flex;gap:.5rem" id="mktLegend"></div>
                <div style="font-size:.6rem;color:var(--text2)" id="mktTotal"></div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script nonce="{{ $cspNonce }}">
const finWeeklyData  = @json($allFinWeekly);
const mktWeeklyData  = @json($allMktWeekly);
const finMonthlyData = @json($finMonthly);
const mktMonthlyData = @json($mktMonthly);
const chartModes     = { fin: 'semanal', mkt: 'semanal' };

function setMode(chart, mode, btn) {
    chartModes[chart] = mode;
    btn.closest('.dash-seg').querySelectorAll('.dash-seg-btn')
        .forEach(b => b.classList.remove('on'));
    btn.classList.add('on');
    document.getElementById(chart + 'MonthSel').style.display = mode === 'semanal' ? '' : 'none';
    renderChart(chart);
}

function renderChart(chart) {
    const mode   = chartModes[chart];
    const sel    = document.getElementById(chart + 'MonthSel').value;
    const wrap   = document.getElementById(chart + 'Chart');
    const legend = document.getElementById(chart + 'Legend');
    const total  = document.getElementById(chart + 'Total');
    const title  = document.getElementById(chart + 'TitleText');
    wrap.innerHTML = '';

    const isFin  = chart === 'fin';
    const accent = isFin ? 'var(--blue)' : 'var(--accent)';
    const label  = isFin ? 'Financeiro' : 'Mercado';

    if (mode === 'semanal') {
        title.textContent = label + ' — Semanal';
        const data = isFin ? (finWeeklyData[sel] || []) : (mktWeeklyData[sel] || []);
        const max  = Math.max(...data.map(d => d.value), 1);
        const tot  = data.reduce((a, d) => a + d.value, 0);
        legend.innerHTML = `<div class="dash-cleg"><div class="dash-cleg-dot" style="background:${accent}"></div>Por semana</div>`;
        total.innerHTML  = `Total: <strong style="color:${isFin ? 'var(--danger)' : 'var(--accent)'}">R$ ${tot.toLocaleString('pt-BR', {minimumFractionDigits:2})}</strong>`;
        data.forEach(d => {
            const h   = Math.max(3, Math.round((d.value / max) * 65));
            const grp = document.createElement('div');
            grp.className = 'dash-bar-grp';
            grp.innerHTML = `
                <div class="dash-bar-wrap" style="height:${h}px;align-items:flex-end">
                    <div class="dash-bar" style="height:${h}px;background:${accent};opacity:.85" title="R$ ${d.value.toLocaleString('pt-BR')}"></div>
                </div>
                <div class="dash-bar-lbl">${d.label}</div>`;
            wrap.appendChild(grp);
        });
    } else {
        title.textContent = label + ' — Mensal';
        if (isFin) {
            const max  = Math.max(...finMonthlyData.map(m => Math.max(m.income, m.expense)), 1);
            const last = finMonthlyData[finMonthlyData.length - 1];
            legend.innerHTML = `
                <div class="dash-cleg"><div class="dash-cleg-dot" style="background:var(--green)"></div>Entradas</div>
                <div class="dash-cleg"><div class="dash-cleg-dot" style="background:var(--danger)"></div>Saídas</div>`;
            total.innerHTML = `${last.label}: <strong style="color:var(--green)">+${last.income.toLocaleString('pt-BR',{minimumFractionDigits:0})}</strong> / <strong style="color:var(--danger)">-${last.expense.toLocaleString('pt-BR',{minimumFractionDigits:0})}</strong>`;
            finMonthlyData.forEach(m => {
                const he  = Math.max(3, Math.round((m.income  / max) * 65));
                const hs  = Math.max(3, Math.round((m.expense / max) * 65));
                const grp = document.createElement('div');
                grp.className = 'dash-bar-grp';
                grp.innerHTML = `
                    <div class="dash-bar-wrap" style="align-items:flex-end;gap:.1rem">
                        <div class="dash-bar" style="height:${he}px;background:var(--green);opacity:.8" title="Entrada: R$ ${m.income.toLocaleString('pt-BR')}"></div>
                        <div class="dash-bar" style="height:${hs}px;background:var(--danger);opacity:.7" title="Saída: R$ ${m.expense.toLocaleString('pt-BR')}"></div>
                    </div>
                    <div class="dash-bar-lbl">${m.label}</div>`;
                wrap.appendChild(grp);
            });
        } else {
            const max = Math.max(...mktMonthlyData.map(m => m.value), 1);
            const tot = mktMonthlyData.reduce((a, m) => a + m.value, 0);
            legend.innerHTML = `<div class="dash-cleg"><div class="dash-cleg-dot" style="background:var(--accent)"></div>Gastos mercado</div>`;
            total.innerHTML  = `Acumulado: <strong style="color:var(--accent)">R$ ${tot.toLocaleString('pt-BR', {minimumFractionDigits:2})}</strong>`;
            mktMonthlyData.forEach(m => {
                const h   = Math.max(3, Math.round((m.value / max) * 65));
                const grp = document.createElement('div');
                grp.className = 'dash-bar-grp';
                grp.innerHTML = `
                    <div class="dash-bar-wrap" style="align-items:flex-end">
                        <div class="dash-bar" style="height:${h}px;background:var(--accent);opacity:.85" title="R$ ${m.value.toLocaleString('pt-BR')}"></div>
                    </div>
                    <div class="dash-bar-lbl">${m.label}</div>`;
                wrap.appendChild(grp);
            });
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    renderChart('fin');
    renderChart('mkt');
});
</script>
@endpush
