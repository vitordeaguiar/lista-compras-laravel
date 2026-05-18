@extends('layouts.app')
@section('title','Financeiro')
@section('page-title','Financeiro')
@section('page-sub','Resumo das listas concluídas no período')
@section('page-actions')
    <a href="{{ route('history.index') }}" class="btn btn-ghost btn-sm">🕐 Histórico</a>
@endsection

@push('styles')
<style>
.filter-bar{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:.85rem .95rem;margin-bottom:1.25rem;display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end}
.fg{flex:1;min-width:120px}
.fg label{display:block;font-size:.61rem;color:var(--text2);margin-bottom:.22rem;text-transform:uppercase;letter-spacing:.05em}
.fg input{background:var(--bg3);border:1px solid var(--border);color:var(--text);padding:.48rem .75rem;border-radius:7px;font-family:'Inter',sans-serif;font-size:.82rem;width:100%;outline:none;transition:border-color .2s}
.fg input:focus{border-color:var(--accent)}
.btn-filter{background:var(--accent);color:#09090b;border:none;padding:.48rem 1rem;border-radius:7px;font-family:'Inter',sans-serif;font-weight:700;font-size:.79rem;cursor:pointer;white-space:nowrap;transition:all .18s}
.btn-filter:hover{background:var(--accent2)}
.btn-clear{background:none;border:1px solid var(--border);color:var(--text2);padding:.48rem .85rem;border-radius:7px;font-size:.79rem;cursor:pointer;font-family:'Inter',sans-serif;transition:all .18s;text-decoration:none;display:inline-flex;align-items:center}
.btn-clear:hover{border-color:var(--danger);color:var(--danger)}

.sec{margin-bottom:1.75rem}
.sec-h{font-size:.78rem;font-weight:600;color:var(--text);margin-bottom:.8rem}

/* Bar chart */
.bar-row{display:flex;align-items:center;gap:.6rem;margin-bottom:.5rem;font-size:.75rem}
.bar-name{flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--text3)}
.bar-track{flex:0 0 40%;height:6px;background:var(--bg3);border-radius:99px;overflow:hidden;border:1px solid var(--border)}
.bar-fill{height:100%;background:var(--accent);border-radius:99px;transition:width .4s}
.bar-val{flex:0 0 auto;font-weight:700;font-size:.75rem;color:var(--accent)}

/* Table */
.data-table{width:100%;border-collapse:collapse;font-size:.79rem}
.data-table th,.data-table td{padding:.5rem .6rem;text-align:left;border-bottom:1px solid var(--border)}
.data-table th{color:var(--text3);font-weight:500;font-size:.65rem;text-transform:uppercase;letter-spacing:.05em}
.data-table td{color:var(--text)}
.data-table tr:hover td{background:rgba(255,255,255,.015)}
.data-table .num{text-align:right;font-variant-numeric:tabular-nums}
.medal{font-size:.85rem}

.empty{text-align:center;padding:2.5rem;color:var(--text3);font-size:.85rem}
</style>
@endpush

@php $numListas = $listIds->count(); @endphp

@section('content')

{{-- FILTERS --}}
<form class="filter-bar" method="GET" action="{{ route('finance.index') }}">
    <div class="fg">
        <label for="f_from">De</label>
        <input id="f_from" type="date" name="date_from" value="{{ $dateFrom }}">
    </div>
    <div class="fg">
        <label for="f_to">Até</label>
        <input id="f_to" type="date" name="date_to" value="{{ $dateTo }}">
    </div>
    <button type="submit" class="btn-filter">Atualizar</button>
    @if(request()->filled('date_from') || request()->filled('date_to'))
        <a href="{{ route('finance.index') }}" class="btn-clear">✕ Período padrão</a>
    @endif
</form>

@if($numListas > 0)

{{-- 4 STAT CARDS --}}
<div class="stats-grid">
    <div class="stat-card hl">
        <div class="stat-label">Total gasto</div>
        <div class="stat-val">R$ {{ number_format($totalGasto, 2, ',', '.') }}</div>
        <div class="stat-hint">no período selecionado</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Listas</div>
        <div class="stat-val">{{ $numListas }}</div>
        <div class="stat-hint">concluídas no período</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Item top</div>
        <div class="stat-val" style="font-size:.95rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $topItemName }}</div>
        <div class="stat-hint">mais comprado</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Itens únicos</div>
        <div class="stat-val">{{ $itensUnicos }}</div>
        <div class="stat-hint">produtos distintos</div>
    </div>
</div>

{{-- BAR CHART por lista --}}
@php $maxLista = max(0.01, (float) collect($gastosPorLista)->max('total')); @endphp
<div class="sec">
    <h2 class="sec-h">Gasto por lista no período</h2>
    @foreach($gastosPorLista as $row)
        @php $pct = ($row['total'] / $maxLista) * 100; @endphp
        <div class="bar-row">
            <span class="bar-name" title="{{ $row['name'] }}">{{ $row['label'] }} — {{ $row['name'] }}</span>
            <div class="bar-track"><div class="bar-fill" style="width:{{ $pct }}%"></div></div>
            <span class="bar-val">R$ {{ number_format($row['total'], 2, ',', '.') }}</span>
        </div>
    @endforeach
</div>

{{-- TOP ITEMS TABLE --}}
<div class="sec">
    <h2 class="sec-h">Itens mais comprados</h2>
    @if($topItems->isEmpty())
        <p class="empty">Sem itens marcados como comprados nesse período.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th class="num">Vezes</th>
                    <th class="num">Qtd total</th>
                    <th class="num">Gasto</th>
                    <th class="num">Preço médio</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topItems as $idx => $it)
                    <tr>
                        <td class="medal">
                            @if($idx === 0) 🥇
                            @elseif($idx === 1) 🥈
                            @elseif($idx === 2) 🥉
                            @else <span style="color:var(--text3);font-size:.75rem">{{ $idx+1 }}</span>
                            @endif
                        </td>
                        <td>{{ ucfirst($it->item_name) }}</td>
                        <td class="num">{{ (int) $it->vezes }}×</td>
                        <td class="num">{{ number_format((float) $it->total_qty, 2, ',', '.') }}</td>
                        <td class="num" style="color:var(--accent);font-weight:600">R$ {{ number_format((float) $it->total_gasto, 2, ',', '.') }}</td>
                        <td class="num">{{ $it->preco_medio !== null ? 'R$ '.number_format((float) $it->preco_medio, 2, ',', '.') : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- BY MONTH TABLE --}}
<div class="sec">
    <h2 class="sec-h">Por mês</h2>
    @if($gastosPorMes->isEmpty())
        <p class="empty">Sem dados por mês nesse período.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Mês</th>
                    <th class="num">Listas</th>
                    <th class="num">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($gastosPorMes as $m)
                    <tr>
                        <td>{{ $m->mes }}</td>
                        <td class="num">{{ (int) $m->num_listas }}</td>
                        <td class="num" style="color:var(--accent);font-weight:600">R$ {{ number_format((float) $m->total, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@else
    <div class="empty">
        <div style="font-size:2rem;margin-bottom:.75rem">📭</div>
        Nenhuma lista concluída neste período. Ajuste as datas ou conclua listas em
        <a href="{{ route('lists.index') }}" style="color:var(--accent)">Minhas listas</a>.
    </div>
@endif
@endsection
