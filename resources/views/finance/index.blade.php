@extends('layouts.app')
@section('title','Financeiro')

@push('styles')
<style>
.phdr{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem}
.ptitle{font-family:'Syne',sans-serif;font-weight:800;font-size:1.6rem;letter-spacing:-.03em}
.ptitle span{color:var(--accent)}

.filter-bar{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1rem 1.1rem;margin-bottom:1.5rem;display:flex;gap:.6rem;flex-wrap:wrap;align-items:flex-end}
.fg{flex:1;min-width:130px}
.fg label{display:block;font-size:.65rem;color:var(--muted);margin-bottom:.28rem;text-transform:uppercase;letter-spacing:.05em}
.fg input{background:var(--surface2);border:1px solid var(--border);color:var(--text);padding:.55rem .8rem;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:.85rem;width:100%;outline:none;transition:border-color .2s}
.fg input:focus{border-color:var(--accent)}
.btn-filter{background:var(--accent);color:#0d0d0f;border:none;padding:.55rem 1.1rem;border-radius:8px;font-family:'Syne',sans-serif;font-weight:700;font-size:.82rem;cursor:pointer;white-space:nowrap;transition:all .2s}
.btn-filter:hover{background:var(--accent2)}
.btn-clear{background:none;border:1px solid var(--border);color:var(--muted);padding:.55rem .9rem;border-radius:8px;font-size:.82rem;cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .2s;text-decoration:none;display:inline-flex;align-items:center}
.btn-clear:hover{border-color:var(--danger);color:var(--danger)}

.summary-bar{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:.6rem;margin-bottom:1.75rem}
.sc{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:.8rem 1rem}
.sc.hl{border-color:rgba(110,231,183,.3);background:rgba(110,231,183,.05)}
.sc-label{font-size:.62rem;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:.25rem}
.sc-val{font-family:'Syne',sans-serif;font-weight:800;font-size:1.1rem}
.sc.hl .sc-val{color:var(--accent)}

.sec{margin-bottom:2rem}
.sec-h{font-family:'Syne',sans-serif;font-weight:700;font-size:1rem;margin-bottom:.85rem;color:var(--text)}

.bar-row{display:flex;align-items:center;gap:.65rem;margin-bottom:.55rem;font-size:.78rem}
.bar-name{flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--muted)}
.bar-track{flex:0 0 45%;height:8px;background:var(--surface2);border-radius:99px;overflow:hidden;border:1px solid var(--border)}
.bar-fill{height:100%;background:var(--accent);border-radius:99px;transition:width .4s}
.bar-val{flex:0 0 auto;font-family:'Syne',sans-serif;font-weight:700;font-size:.78rem;color:var(--accent)}

.data-table{width:100%;border-collapse:collapse;font-size:.82rem}
.data-table th,.data-table td{padding:.55rem .65rem;text-align:left;border-bottom:1px solid var(--border)}
.data-table th{color:var(--muted);font-weight:500;font-size:.68rem;text-transform:uppercase;letter-spacing:.05em}
.data-table td{color:var(--text)}
.data-table tr:hover td{background:rgba(255,255,255,.02)}
.data-table .num{text-align:right;font-variant-numeric:tabular-nums}

.empty{text-align:center;padding:2.5rem;color:var(--muted);font-size:.88rem}
</style>
@endpush

@php
    $numListas = $listIds->count();
    $maxLista = max(0.01, (float) $gastosPorLista->max('total'));
@endphp

@section('content')
<div class="phdr">
    <div>
        <h1 class="ptitle">💰 <span>Financeiro</span></h1>
        <p style="color:var(--muted);font-size:.78rem;margin-top:.2rem">Resumo das listas concluídas no período</p>
    </div>
    <a href="{{ route('history.index') }}" class="btn btn-ghost btn-sm">📂 Histórico</a>
</div>

<form class="filter-bar" method="GET" action="{{ route('finance.index') }}">
    <div class="fg">
        <label for="f_from">De</label>
        <input id="f_from" type="date" name="date_from" value="{{ $dateFrom }}">
    </div>
    <div class="fg">
        <label for="f_to">Até</label>
        <input id="f_to" type="date" name="date_to" value="{{ $dateTo }}">
    </div>
    <button type="submit" class="btn-filter">🔍 Atualizar</button>
    @if(request()->filled('date_from') || request()->filled('date_to'))
        <a href="{{ route('finance.index') }}" class="btn-clear">✕ Período padrão</a>
    @endif
</form>

@if($numListas > 0)
<div class="summary-bar">
    <div class="sc">
        <div class="sc-label">Listas no período</div>
        <div class="sc-val">{{ $numListas }}</div>
    </div>
    <div class="sc hl">
        <div class="sc-label">Total gasto</div>
        <div class="sc-val">R$ {{ number_format($totalGasto, 2, ',', '.') }}</div>
    </div>
    <div class="sc">
        <div class="sc-label">Média por lista</div>
        <div class="sc-val">R$ {{ number_format($numListas > 0 ? $totalGasto / $numListas : 0, 2, ',', '.') }}</div>
    </div>
</div>

<div class="sec">
    <h2 class="sec-h">Gasto por lista (no período)</h2>
    @foreach($gastosPorLista as $row)
        @php $pct = ($row['total'] / $maxLista) * 100; @endphp
        <div class="bar-row">
            <span class="bar-name" title="{{ $row['name'] }}">{{ $row['label'] }} — {{ $row['name'] }}</span>
            <div class="bar-track"><div class="bar-fill" style="width:{{ $pct }}%"></div></div>
            <span class="bar-val">R$ {{ number_format($row['total'], 2, ',', '.') }}</span>
        </div>
    @endforeach
</div>

<div class="sec">
    <h2 class="sec-h">Itens mais comprados</h2>
    @if($topItems->isEmpty())
        <p class="empty">Sem itens marcados como comprados nesse período.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="num">Vezes</th>
                    <th class="num">Qtd total</th>
                    <th class="num">Gasto</th>
                    <th class="num">Preço médio</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topItems as $it)
                    <tr>
                        <td>{{ $it->item_name }}</td>
                        <td class="num">{{ (int) $it->vezes }}</td>
                        <td class="num">{{ number_format((float) $it->total_qty, 2, ',', '.') }}</td>
                        <td class="num">R$ {{ number_format((float) $it->total_gasto, 2, ',', '.') }}</td>
                        <td class="num">R$ {{ $it->preco_medio !== null ? number_format((float) $it->preco_medio, 2, ',', '.') : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<div class="sec">
    <h2 class="sec-h">Por mês</h2>
    @if($gastosPorMes->isEmpty())
        <p class="empty">Sem dados agrupados por mês nesse período.</p>
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
                        <td class="num">R$ {{ number_format((float) $m->total, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@else
    <div class="empty">
        <div style="font-size:2.5rem;margin-bottom:.75rem">📭</div>
        Nenhuma lista concluída neste período. Ajuste as datas ou conclua listas em <a href="{{ route('lists.index') }}" style="color:var(--accent)">Minhas listas</a>.
    </div>
@endif
@endsection
