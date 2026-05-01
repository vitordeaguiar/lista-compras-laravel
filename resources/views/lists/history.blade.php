@extends('layouts.app')
@section('title', $list->name . ' — Histórico')

@push('styles')
<style>
.breadcrumb{display:flex;align-items:center;gap:.5rem;font-size:.78rem;color:var(--muted);margin-bottom:1.25rem}
.breadcrumb a{color:var(--muted);text-decoration:none;transition:color .2s}
.breadcrumb a:hover{color:var(--accent)}
.breadcrumb span{color:var(--muted);opacity:.4}

.hist-header{margin-bottom:1.75rem}
.hist-title{font-family:'Syne',sans-serif;font-weight:800;font-size:1.6rem;letter-spacing:-.03em;color:var(--text)}
.hist-meta{display:flex;align-items:center;gap:1rem;margin-top:.5rem;flex-wrap:wrap}
.meta-pill{font-size:.75rem;color:var(--muted);display:flex;align-items:center;gap:.3rem}
.status-badge{font-size:.65rem;padding:.2rem .65rem;border-radius:99px;font-weight:600;background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.25);color:#34d399}

/* SUMMARY CARDS */
.summary-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:.65rem;margin-bottom:2rem}
.sc{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:.9rem 1rem}
.sc.hl{border-color:rgba(110,231,183,.35);background:rgba(110,231,183,.05)}
.sc-label{font-size:.62rem;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:.3rem}
.sc-val{font-family:'Syne',sans-serif;font-weight:800;font-size:1.2rem;color:var(--text)}
.sc.hl .sc-val{color:var(--accent)}
.sc-hint{font-size:.62rem;color:var(--muted);margin-top:.15rem}

/* ITEMS TABLE */
.items-section-label{font-size:.68rem;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:.75rem;display:flex;align-items:center;gap:.5rem}
.items-section-label::after{content:'';flex:1;height:1px;background:var(--border)}
.items-table{width:100%;border-collapse:collapse;margin-bottom:1.75rem}
.items-table th{font-size:.62rem;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);text-align:left;padding:.45rem .65rem;border-bottom:1px solid var(--border)}
.items-table td{padding:.7rem .65rem;border-bottom:1px solid rgba(42,42,51,.5);font-size:.85rem;vertical-align:middle}
.items-table tr:last-child td{border-bottom:none}
.item-purchased td{opacity:.5}
.item-name{color:var(--text)}
.item-purchased .item-name{text-decoration:line-through}
.item-qty{color:var(--muted);font-size:.78rem;text-align:center}
.item-price{color:var(--muted);font-size:.78rem;text-align:right}
.item-sub{font-family:'Syne',sans-serif;font-weight:700;font-size:.88rem;color:var(--accent);text-align:right}
.item-sub.null{color:var(--muted);font-family:'DM Sans',sans-serif;font-weight:400;font-size:.78rem}
.chk-icon{display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;font-size:.6rem}
.chk-done{background:var(--accent);color:#0d0d0f}
.chk-no{border:2px solid var(--border)}

/* ACTIONS */
.hist-actions{display:flex;gap:.65rem;flex-wrap:wrap;margin-top:1rem}
.btn-ghost{background:none;border:1px solid var(--border);color:var(--muted);padding:.6rem 1.1rem;border-radius:9px;font-size:.82rem;font-family:'DM Sans',sans-serif;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem;transition:all .2s}
.btn-ghost:hover{border-color:var(--accent);color:var(--accent)}
.btn-reopen{background:none;border:1px solid rgba(110,231,183,.3);color:var(--accent);padding:.6rem 1.1rem;border-radius:9px;font-size:.82rem;font-family:'DM Sans',sans-serif;cursor:pointer;display:inline-flex;align-items:center;gap:.35rem;transition:all .2s}
.btn-reopen:hover{background:rgba(110,231,183,.08)}
</style>
@endpush

@section('content')
<div class="breadcrumb">
    <a href="{{ route('lists.index') }}">← Minhas Listas</a>
    <span>/</span>
    <span>{{ $list->name }}</span>
</div>

<div class="hist-header">
    <h1 class="hist-title">{{ $list->name }}</h1>
    <div class="hist-meta">
        <span class="status-badge">✓ Concluída</span>
        <span class="meta-pill">📅 {{ $list->shopping_date->locale('pt_BR')->isoFormat('D [de] MMMM [de] YYYY') }}</span>
        <span class="meta-pill">🕐 Concluída em {{ $list->concluded_at->locale('pt_BR')->isoFormat('D [de] MMM [às] HH:mm') }}</span>
    </div>
</div>

{{-- SUMMARY --}}
@php
    $purchased = $list->items->where('purchased', true);
    $notPurchased = $list->items->where('purchased', false);
    $totalItens = $list->items->count();
    $totalComprados = $purchased->count();
    $totalNaoComprados = $notPurchased->count();
    $totalComPreco = $list->items->filter(fn($i) => $i->price)->count();
@endphp
<div class="summary-grid">
    <div class="sc">
        <div class="sc-label">Total de itens</div>
        <div class="sc-val">{{ $totalItens }}</div>
        <div class="sc-hint">{{ $totalComprados }} comprados · {{ $totalNaoComprados }} não comprados</div>
    </div>
    <div class="sc">
        <div class="sc-label">Itens com preço</div>
        <div class="sc-val">{{ $totalComPreco }}</div>
        <div class="sc-hint">de {{ $totalItens }} cadastrados</div>
    </div>
    <div class="sc hl">
        <div class="sc-label">Total gasto</div>
        <div class="sc-val">{{ $list->total > 0 ? 'R$ '.number_format($list->total, 2, ',', '.') : '—' }}</div>
        <div class="sc-hint">{{ $list->total > 0 ? 'soma dos itens com preço' : 'sem preços cadastrados' }}</div>
    </div>
</div>

{{-- PURCHASED ITEMS --}}
@if($purchased->count() > 0)
<div class="items-section-label">Itens comprados</div>
<table class="items-table">
    <thead>
        <tr>
            <th></th>
            <th>Produto</th>
            <th style="text-align:center">Qtd</th>
            <th style="text-align:right">Preço unit.</th>
            <th style="text-align:right">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($purchased->sortBy('name') as $item)
        <tr class="item-purchased">
            <td style="width:28px"><span class="chk-icon chk-done">✓</span></td>
            <td class="item-name">{{ $item->name }}@if($item->unit) <small style="color:var(--muted);font-size:.72rem">({{ $item->unit }})</small>@endif</td>
            <td class="item-qty">{{ rtrim(rtrim(number_format($item->qty,3,',','.'), '0'), ',') }}</td>
            <td class="item-price">{{ $item->price ? 'R$ '.number_format($item->price,2,',','.') : '—' }}</td>
            <td class="item-sub {{ $item->subtotal ? '' : 'null' }}">{{ $item->subtotal ? 'R$ '.number_format($item->subtotal,2,',','.') : '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- NOT PURCHASED --}}
@if($notPurchased->count() > 0)
<div class="items-section-label">Não comprados</div>
<table class="items-table">
    <thead>
        <tr>
            <th></th>
            <th>Produto</th>
            <th style="text-align:center">Qtd</th>
            <th style="text-align:right">Preço unit.</th>
            <th style="text-align:right">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($notPurchased->sortBy('name') as $item)
        <tr>
            <td style="width:28px"><span class="chk-icon chk-no"></span></td>
            <td class="item-name">{{ $item->name }}@if($item->unit) <small style="color:var(--muted);font-size:.72rem">({{ $item->unit }})</small>@endif</td>
            <td class="item-qty">{{ rtrim(rtrim(number_format($item->qty,3,',','.'), '0'), ',') }}</td>
            <td class="item-price">{{ $item->price ? 'R$ '.number_format($item->price,2,',','.') : '—' }}</td>
            <td class="item-sub {{ $item->subtotal ? '' : 'null' }}">{{ $item->subtotal ? 'R$ '.number_format($item->subtotal,2,',','.') : '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<div class="hist-actions">
    <a href="{{ route('lists.index') }}" class="btn-ghost">← Voltar para listas</a>
    <form method="POST" action="{{ route('lists.reopen', $list) }}">
        @csrf
        <button type="submit" class="btn-reopen">↺ Reabrir lista</button>
    </form>
</div>
@endsection
