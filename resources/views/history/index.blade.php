@extends('layouts.app')
@section('title','Histórico')
@section('page-title','Histórico')
@section('page-sub','Todas as suas listas concluídas')
@section('page-actions')
    <a href="{{ route('finance.index') }}" class="btn btn-ghost btn-sm">📊 Financeiro</a>
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

.hlist{display:flex;flex-direction:column;gap:.4rem}
.hcard{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:.8rem .95rem;display:flex;align-items:center;gap:.75rem;text-decoration:none;color:inherit;transition:border-color .18s,transform .15s}
.hcard:hover{border-color:var(--border2);transform:translateX(2px)}
.hicon{width:34px;height:34px;border-radius:8px;background:var(--adim);border:1px solid rgba(147,197,253,.15);display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0}
.hinfo{flex:1;min-width:0}
.hname{font-size:.84rem;font-weight:600;margin-bottom:.1rem;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.hmeta{font-size:.67rem;color:var(--text3);display:flex;align-items:center;gap:.4rem;flex-wrap:wrap}
.htotal{font-weight:700;font-size:.9rem;color:var(--accent);flex-shrink:0}
.cbadge{display:inline-flex;align-items:center;gap:.18rem;font-size:.6rem;padding:.08rem .36rem;border-radius:4px}
.cbadge.up{background:rgba(239,68,68,.1);color:var(--danger)}
.cbadge.down{background:var(--adim);color:var(--accent)}
.empty{text-align:center;padding:3rem;color:var(--text3);font-size:.85rem}

.pagination-wrap{margin-top:1rem;display:flex;justify-content:center;gap:.35rem;flex-wrap:wrap}
.pagination-wrap a,.pagination-wrap span{background:var(--bg2);border:1px solid var(--border);color:var(--text2);padding:.3rem .7rem;border-radius:6px;font-size:.75rem;text-decoration:none;transition:all .18s}
.pagination-wrap a:hover{border-color:var(--accent);color:var(--accent)}
.pagination-wrap span.current{border-color:var(--accent);color:var(--accent);background:var(--adim)}

@media(max-width:768px){
    .filter-bar{flex-direction:column}
    .fg{min-width:unset;width:100%}
    .btn-filter,.btn-clear{width:100%;justify-content:center;text-align:center}
    .hcard{width:100%}
}
</style>
@endpush

@section('content')

{{-- SUMMARY --}}
@if($lists->total() > 0)
<div class="stats-grid" style="margin-bottom:1.1rem">
    <div class="stat-card hl">
        <div class="stat-label">Total gasto</div>
        <div class="stat-val">R$ {{ number_format($totalGasto, 2, ',', '.') }}</div>
        <div class="stat-hint">no filtro atual</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Listas</div>
        <div class="stat-val">{{ $lists->total() }}</div>
        <div class="stat-hint">encontradas</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Média por lista</div>
        <div class="stat-val">R$ {{ number_format($lists->total() > 0 ? $totalGasto / $lists->total() : 0, 2, ',', '.') }}</div>
        <div class="stat-hint">valor médio gasto</div>
    </div>
</div>
@endif

{{-- FILTERS --}}
<form class="filter-bar" method="GET" action="{{ route('history.index') }}">
    <div class="fg">
        <label>De</label>
        <input type="date" name="date_from" value="{{ request('date_from') }}">
    </div>
    <div class="fg">
        <label>Até</label>
        <input type="date" name="date_to" value="{{ request('date_to') }}">
    </div>
    <div class="fg">
        <label>Buscar lista</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nome da lista…">
    </div>
    <button type="submit" class="btn-filter">Filtrar</button>
    @if(request()->hasAny(['date_from','date_to','search']))
        <a href="{{ route('history.index') }}" class="btn-clear">✕ Limpar</a>
    @endif
</form>

{{-- LIST --}}
<div class="hlist">
@forelse($lists as $index => $list)
    @php
        $prev = $lists->items()[$index + 1] ?? null;
        $diff = $prev && $prev->total > 0 ? $list->total - $prev->total : null;
        $pct  = $prev && $prev->total > 0 ? round((($list->total - $prev->total) / $prev->total) * 100, 1) : null;
    @endphp
    <a class="hcard" href="{{ route('lists.show', $list) }}">
        <div class="hicon">✅</div>
        <div class="hinfo">
            <div class="hname">{{ $list->name }}</div>
            <div class="hmeta">
                <span>{{ $list->shopping_date->locale('pt_BR')->isoFormat('D [de] MMMM [de] YYYY') }}</span>
                <span style="opacity:.3">·</span>
                <span>{{ $list->items->count() }} itens</span>
                @if($pct !== null)
                    @if($diff > 0)
                        <span class="cbadge up">▲ {{ abs($pct) }}% vs anterior</span>
                    @elseif($diff < 0)
                        <span class="cbadge down">▼ {{ abs($pct) }}% vs anterior</span>
                    @endif
                @endif
            </div>
        </div>
        <div class="htotal">
            @if($list->total > 0)
                R$ {{ number_format($list->total, 2, ',', '.') }}
            @else
                <span style="font-size:.72rem;font-weight:400;color:var(--text3)">sem preços</span>
            @endif
        </div>
    </a>
@empty
    <div class="empty">
        <div style="font-size:2rem;margin-bottom:.5rem">📭</div>
        Nenhuma lista encontrada com esses filtros.
    </div>
@endforelse
</div>

@if($lists->hasPages())
<div class="pagination-wrap">
    {{ $lists->links('pagination::simple-bootstrap-4') }}
</div>
@endif
@endsection
