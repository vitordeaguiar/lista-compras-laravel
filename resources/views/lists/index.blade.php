@extends('layouts.app')
@section('title','Histórico')

@push('styles')
<style>
.phdr{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem}
.ptitle{font-family:'Syne',sans-serif;font-weight:800;font-size:1.6rem;letter-spacing:-.03em}
.ptitle span{color:var(--accent)}

.filter-bar{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1rem 1.1rem;margin-bottom:1.5rem;display:flex;gap:.6rem;flex-wrap:wrap;align-items:flex-end}
.fg{flex:1;min-width:130px}
.fg label{display:block;font-size:.65rem;color:var(--muted);margin-bottom:.28rem;text-transform:uppercase;letter-spacing:.05em}
.fg input,.fg select{background:var(--surface2);border:1px solid var(--border);color:var(--text);padding:.55rem .8rem;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:.85rem;width:100%;outline:none;transition:border-color .2s}
.fg input:focus,.fg select:focus{border-color:var(--accent)}
.btn-filter{background:var(--accent);color:#0d0d0f;border:none;padding:.55rem 1.1rem;border-radius:8px;font-family:'Syne',sans-serif;font-weight:700;font-size:.82rem;cursor:pointer;white-space:nowrap;transition:all .2s}
.btn-filter:hover{background:var(--accent2)}
.btn-clear{background:none;border:1px solid var(--border);color:var(--muted);padding:.55rem .9rem;border-radius:8px;font-size:.82rem;cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .2s;text-decoration:none;display:inline-flex;align-items:center}
.btn-clear:hover{border-color:var(--danger);color:var(--danger)}

.summary-bar{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:.6rem;margin-bottom:1.5rem}
.sc{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:.8rem 1rem}
.sc.hl{border-color:rgba(110,231,183,.3);background:rgba(110,231,183,.05)}
.sc-label{font-size:.62rem;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:.25rem}
.sc-val{font-family:'Syne',sans-serif;font-weight:800;font-size:1.1rem}
.sc.hl .sc-val{color:var(--accent)}

.hlist{display:flex;flex-direction:column;gap:.5rem}
.hcard{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:.85rem 1rem;display:flex;align-items:center;gap:.85rem;text-decoration:none;color:inherit;transition:all .2s}
.hcard:hover{border-color:#3a3a45;transform:translateX(2px)}
.hicon{width:36px;height:36px;border-radius:9px;background:var(--surface2);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0;opacity:.7}
.hinfo{flex:1;min-width:0}
.hname{font-size:.88rem;font-weight:500;margin-bottom:.1rem}
.hmeta{font-size:.7rem;color:var(--muted);display:flex;align-items:center;gap:.4rem;flex-wrap:wrap}
.htotal{font-family:'Syne',sans-serif;font-weight:800;font-size:.95rem;flex-shrink:0}
.cbadge{display:inline-flex;align-items:center;gap:.2rem;font-size:.63rem;padding:.1rem .4rem;border-radius:5px}
.cbadge.up{background:rgba(248,113,113,.1);color:var(--danger)}
.cbadge.down{background:rgba(110,231,183,.1);color:var(--accent)}
.empty{text-align:center;padding:3rem;color:var(--muted);font-size:.88rem}
.pagination-wrap{margin-top:1.25rem;display:flex;justify-content:center;gap:.4rem;flex-wrap:wrap}
.pagination-wrap a,.pagination-wrap span{background:var(--surface);border:1px solid var(--border);color:var(--muted);padding:.35rem .75rem;border-radius:7px;font-size:.78rem;text-decoration:none;transition:all .2s}
.pagination-wrap a:hover{border-color:var(--accent);color:var(--accent)}
.pagination-wrap span.current{border-color:var(--accent);color:var(--accent);background:rgba(110,231,183,.08)}
</style>
@endpush

@section('content')
<div class="phdr">
    <div>
        <h1 class="ptitle">📂 <span>Histórico</span></h1>
        <p style="color:var(--muted);font-size:.78rem;margin-top:.2rem">Todas as suas listas concluídas</p>
    </div>
    <a href="{{ route('finance.index') }}" class="btn btn-ghost btn-sm">💰 Ver financeiro</a>
</div>

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
    <button type="submit" class="btn-filter">🔍 Filtrar</button>
    @if(request()->hasAny(['date_from','date_to','search']))
        <a href="{{ route('history.index') }}" class="btn-clear">✕ Limpar</a>
    @endif
</form>

{{-- SUMMARY --}}
@if($openLists->total() > 0)
<div class="summary-bar">
    <div class="sc">
        <div class="sc-label">Listas encontradas</div>
        <div class="sc-val">{{ $openLists->total() }}</div>
    </div>
    <div class="sc hl">
        <div class="sc-label">Total gasto</div>
        <div class="sc-val">R$ {{ number_format($totalGasto, 2, ',', '.') }}</div>
    </div>
    <div class="sc">
        <div class="sc-label">Média por lista</div>
        <div class="sc-val">R$ {{ number_format($openLists->total() > 0 ? $totalGasto / $openLists->total() : 0, 2, ',', '.') }}</div>
    </div>
</div>
@endif

{{-- LIST --}}
<div class="hlist">
@forelse($openLists as $index => $list)
    @php
        $prev = $openLists->items()[$index + 1] ?? null;
        $diff = $prev && $prev->total > 0 ? $list->total - $prev->total : null;
        $pct  = $prev && $prev->total > 0 ? round((($list->total - $prev->total) / $prev->total) * 100, 1) : null;
    @endphp
    <a class="hcard" href="{{ route('lists.show', $list) }}">
        <div class="hicon">✅</div>
        <div class="hinfo">
            <div class="hname">{{ $list->name }}</div>
            <div class="hmeta">
                {{ $list->shopping_date->locale('pt_BR')->isoFormat('D [de] MMMM [de] YYYY') }}
                &nbsp;·&nbsp; {{ $list->items->count() }} itens
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
                <span style="font-size:.75rem;font-weight:400;color:var(--muted)">sem preços</span>
            @endif
        </div>
    </a>
@empty
    <div class="empty">
        <div style="font-size:2.5rem;margin-bottom:.5rem">📭</div>
        Nenhuma lista encontrada com esses filtros.
    </div>
@endforelse
</div>

{{-- PAGINATION --}}
@if($openLists->hasPages())
<div class="pagination-wrap">
    {{$openLists->links('pagination::simple-bootstrap-4')}}
</div>
@endif
@endsection
