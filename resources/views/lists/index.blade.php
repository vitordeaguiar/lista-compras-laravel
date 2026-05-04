@extends('layouts.app')
@section('title','Minhas listas')

@push('styles')
<style>
.phdr{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem}
.ptitle{font-family:'Syne',sans-serif;font-weight:800;font-size:1.6rem;letter-spacing:-.03em}
.ptitle span{color:var(--accent)}

.new-form{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1rem 1.1rem;margin-bottom:1.5rem}
.new-form .form-row{display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end}
.fg{flex:1;min-width:130px}
.fg label{display:block;font-size:.65rem;color:var(--muted);margin-bottom:.28rem;text-transform:uppercase;letter-spacing:.05em}
.fg input{background:var(--surface2);border:1px solid var(--border);color:var(--text);padding:.55rem .8rem;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:.85rem;width:100%;outline:none;transition:border-color .2s}
.fg input:focus{border-color:var(--accent)}
.btn-new{background:var(--accent);color:#0d0d0f;border:none;padding:.55rem 1.1rem;border-radius:8px;font-family:'Syne',sans-serif;font-weight:700;font-size:.82rem;cursor:pointer;white-space:nowrap;transition:all .2s}
.btn-new:hover{background:var(--accent2)}

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
</style>
@endpush

@section('content')
<div class="phdr">
    <div>
        <h1 class="ptitle">📋 <span>Minhas listas</span></h1>
        <p style="color:var(--muted);font-size:.78rem;margin-top:.2rem">Listas em aberto — da data mais próxima à mais distante</p>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap">
        <a href="{{ route('history.index') }}" class="btn btn-ghost btn-sm">📂 Histórico</a>
        <a href="{{ route('finance.index') }}" class="btn btn-ghost btn-sm">💰 Financeiro</a>
    </div>
</div>

<form class="new-form" method="POST" action="{{ route('lists.store') }}">
    @csrf
    <div class="form-row">
        <div class="fg">
            <label for="nl_name">Nome da lista</label>
            <input id="nl_name" type="text" name="name" value="{{ old('name') }}" required maxlength="255" placeholder="Ex.: Compras da semana">
        </div>
        <div class="fg" style="flex:0 0 160px;min-width:140px">
            <label for="nl_date">Data</label>
            <input id="nl_date" type="date" name="shopping_date" value="{{ old('shopping_date', now()->toDateString()) }}" required>
        </div>
        <div class="fg" style="flex:2;min-width:180px">
            <label for="nl_notes">Observações (opcional)</label>
            <input id="nl_notes" type="text" name="notes" value="{{ old('notes') }}" maxlength="500" placeholder="Lembrete…">
        </div>
        <button type="submit" class="btn-new">+ Criar</button>
    </div>
    @error('name')<p style="color:var(--danger);font-size:.78rem;margin-top:.5rem">{{ $message }}</p>@enderror
    @error('shopping_date')<p style="color:var(--danger);font-size:.78rem;margin-top:.5rem">{{ $message }}</p>@enderror
    @error('notes')<p style="color:var(--danger);font-size:.78rem;margin-top:.5rem">{{ $message }}</p>@enderror
</form>

@php
    $rows = $openLists->values();
    $listCount = $openLists->count();
@endphp

@if($listCount > 0)
<div class="summary-bar">
    <div class="sc">
        <div class="sc-label">Listas em aberto</div>
        <div class="sc-val">{{ $listCount }}</div>
    </div>
    <div class="sc hl">
        <div class="sc-label">Soma dos itens (preços)</div>
        <div class="sc-val">R$ {{ number_format($totalGasto, 2, ',', '.') }}</div>
    </div>
    <div class="sc">
        <div class="sc-label">Média por lista</div>
        <div class="sc-val">R$ {{ number_format($listCount > 0 ? $totalGasto / $listCount : 0, 2, ',', '.') }}</div>
    </div>
</div>
@endif

<div class="hlist">
@forelse($rows as $index => $list)
    @php
        $prev = $index > 0 ? $rows->get($index - 1) : null;
        $curVal = $list->computed_total;
        $prevVal = $prev ? $prev->computed_total : 0;
        $diff = $prev && $prevVal > 0 ? $curVal - $prevVal : null;
        $pct  = $prev && $prevVal > 0 ? round((($curVal - $prevVal) / $prevVal) * 100, 1) : null;
    @endphp
    <a class="hcard" href="{{ route('lists.show', $list) }}">
        <div class="hicon">🛒</div>
        <div class="hinfo">
            <div class="hname">{{ $list->name }}</div>
            <div class="hmeta">
                {{ $list->shopping_date->locale('pt_BR')->isoFormat('D [de] MMMM [de] YYYY') }}
                &nbsp;·&nbsp; {{ $list->items->count() }} itens
                @if($pct !== null)
                    @if($diff > 0)
                        <span class="cbadge up">▲ {{ abs($pct) }}% vs lista anterior</span>
                    @elseif($diff < 0)
                        <span class="cbadge down">▼ {{ abs($pct) }}% vs lista anterior</span>
                    @endif
                @endif
            </div>
        </div>
        <div class="htotal">
            @if($curVal > 0)
                R$ {{ number_format($curVal, 2, ',', '.') }}
            @else
                <span style="font-size:.75rem;font-weight:400;color:var(--muted)">sem preços</span>
            @endif
        </div>
    </a>
@empty
    <div class="empty">
        <div style="font-size:2.5rem;margin-bottom:.5rem">📭</div>
        Nenhuma lista em aberto. Crie uma acima para começar.
    </div>
@endforelse
</div>
@endsection
