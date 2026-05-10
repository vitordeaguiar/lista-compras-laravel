@extends('layouts.app')
@section('title', $list->name)

@push('styles')
<style>
/* breadcrumb */
.breadcrumb{display:flex;align-items:center;gap:.5rem;font-size:.78rem;color:var(--muted);margin-bottom:1.5rem}
.breadcrumb a{color:var(--muted);text-decoration:none;transition:color .2s}
.breadcrumb a:hover{color:var(--accent)}
.breadcrumb .sep{opacity:.35}

/* header */
.list-header{margin-bottom:1.5rem}
.list-title-row{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:.6rem}
.list-title{font-family:'Syne',sans-serif;font-weight:800;font-size:1.6rem;letter-spacing:-.03em}
.list-actions{display:flex;gap:.5rem;align-items:center;flex-wrap:wrap}
.list-meta{display:flex;align-items:center;gap:.8rem;flex-wrap:wrap;font-size:.8rem;color:var(--muted)}
.meta-pill{display:inline-flex;align-items:center;gap:.3rem;background:var(--surface);border:1px solid var(--border);border-radius:7px;padding:.2rem .65rem}
.meta-pill.green{border-color:rgba(110,231,183,.3);color:var(--accent);background:var(--adim)}

/* totals */
.totals-bar{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:.6rem;margin-bottom:1.5rem}
.tc{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:.85rem 1rem}
.tc.hl{border-color:rgba(110,231,183,.3);background:rgba(110,231,183,.05)}
.tc-label{font-size:.65rem;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:.25rem}
.tc-val{font-family:'Syne',sans-serif;font-weight:800;font-size:1.15rem;color:var(--text)}
.tc.hl .tc-val{color:var(--accent)}
.tc-hint{font-size:.63rem;color:var(--muted);margin-top:.15rem}

/* progress */
.progress-section{margin-bottom:1.5rem}
.progress-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:.4rem;font-size:.78rem;color:var(--muted)}
.big-progress{height:6px;background:var(--surface2);border-radius:99px;overflow:hidden}
.big-progress-fill{height:100%;background:var(--accent);border-radius:99px;transition:width .4s}

/* add form */
.add-form{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:1rem 1.1rem;margin-bottom:1.75rem}
.form-row{display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end}
.fg{flex:1;min-width:110px}
.fg.sm{flex:0 0 70px;min-width:70px}
.fg.md{flex:0 0 120px;min-width:120px}
.fg label{display:block;font-size:.65rem;color:var(--muted);margin-bottom:.28rem;text-transform:uppercase;letter-spacing:.05em}
.fg input{background:var(--surface2);border:1px solid var(--border);color:var(--text);padding:.55rem .75rem;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:.88rem;width:100%;outline:none;transition:border-color .2s}
.fg input:focus{border-color:var(--accent)}
.fg input::placeholder{color:var(--muted);opacity:.6}
.pr-wrap{position:relative}
.pr-wrap em{position:absolute;left:.6rem;top:50%;transform:translateY(-50%);color:var(--muted);font-style:normal;font-size:.75rem;pointer-events:none}
.pr-wrap input{padding-left:1.6rem}
.btn-add{background:var(--accent);color:#0d0d0f;border:none;padding:.55rem 1rem;border-radius:8px;font-family:'Syne',sans-serif;font-weight:700;font-size:.85rem;cursor:pointer;white-space:nowrap;transition:all .2s}
.btn-add:hover{background:var(--accent2);transform:translateY(-1px)}

/* section label */
.slabel{font-size:.65rem;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:.6rem;display:flex;align-items:center;justify-content:space-between}
.sbadge{background:var(--surface2);border:1px solid var(--border);color:var(--muted);font-size:.6rem;padding:.1rem .45rem;border-radius:99px}

/* items */
.items-list{display:flex;flex-direction:column;gap:.4rem;margin-bottom:1.5rem}
.item-card{background:var(--surface);border:1px solid var(--border);border-radius:12px;display:flex;align-items:flex-start;gap:.6rem;padding:.75rem .9rem;transition:border-color .2s;animation:si .15s ease}
@keyframes si{from{opacity:0;transform:translateY(-4px)}to{opacity:1;transform:none}}
.item-card:hover{border-color:#3a3a45}
.item-card.done{opacity:.5;background:#111115}
.item-card.done .iname{text-decoration:line-through;color:var(--muted)}

.chk{flex-shrink:0;margin-top:2px;width:24px;height:24px;border-radius:50%;border:2px solid var(--border);background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.68rem;color:transparent;transition:all .2s}
.chk:hover{border-color:var(--accent);color:var(--accent)}
.item-card.done .chk{background:var(--accent);border-color:var(--accent);color:#0d0d0f}

.iinfo{flex:1;min-width:0}
.iname-row{display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;margin-bottom:.2rem}
.iname{font-size:.9rem;color:var(--text)}
.unit-tag{font-size:.65rem;background:var(--surface2);border:1px solid var(--border);color:var(--muted);padding:.03rem .4rem;border-radius:5px}
.imeta{display:flex;align-items:center;gap:.45rem;flex-wrap:wrap}
.qty-tag{font-size:.7rem;color:var(--muted)}

/* price pill */
.ppill{display:inline-flex;align-items:center;gap:.2rem;padding:.1rem .45rem;border-radius:6px;font-size:.7rem;cursor:pointer;transition:all .2s;border:1px solid rgba(110,231,183,.25);background:rgba(110,231,183,.07);color:var(--accent)}
.ppill.empty{border-color:var(--border);background:var(--surface2);color:var(--muted)}
.ppill:hover{background:rgba(110,231,183,.15)}
.ppill.empty:hover{border-color:var(--accent);color:var(--accent)}
.sub{font-size:.7rem;color:var(--muted);font-style:italic}

/* inline edit */
.iedit{display:none;align-items:center;gap:.35rem;margin-top:.4rem;flex-wrap:wrap}
.iedit.open{display:flex}
.iedit input{background:var(--surface2);border:1px solid var(--border);color:var(--text);padding:.3rem .55rem;border-radius:6px;font-size:.8rem;font-family:inherit;outline:none;transition:border-color .2s}
.iedit input:focus{border-color:var(--accent)}
.iedit .pwrap{position:relative}
.iedit .pwrap em{position:absolute;left:.45rem;top:50%;transform:translateY(-50%);color:var(--muted);font-style:normal;font-size:.7rem;pointer-events:none}
.iedit .pwrap input{padding-left:1.3rem;width:100px}
.iedit .qinput{width:60px}
.bsave{background:var(--accent);color:#0d0d0f;border:none;padding:.3rem .65rem;border-radius:6px;font-size:.75rem;font-weight:700;cursor:pointer;font-family:inherit;transition:all .2s}
.bsave:hover{background:var(--accent2)}
.bcancel{background:none;border:1px solid var(--border);color:var(--muted);padding:.3rem .55rem;border-radius:6px;font-size:.75rem;cursor:pointer;font-family:inherit}

/* toggle+price modal */
.tp-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:300;align-items:center;justify-content:center;padding:1rem}
.tp-overlay.open{display:flex}
.tp-modal{background:var(--surface);border:1px solid var(--border);border-radius:18px;padding:1.5rem;width:100%;max-width:360px}
.tp-title{font-family:'Syne',sans-serif;font-weight:800;font-size:1rem;margin-bottom:.4rem}
.tp-item-name{font-size:.82rem;color:var(--muted);margin-bottom:1.1rem}
.tp-price-wrap{margin-bottom:1rem}
.tp-price-input{width:100%;background:var(--surface2);border:1px solid var(--border);color:var(--text);padding:.75rem .85rem;border-radius:10px;font-family:'DM Sans',sans-serif;font-size:1.1rem;font-weight:500;outline:none;transition:border-color .2s}
.tp-price-input:focus{border-color:var(--accent)}
.tp-hint{font-size:.75rem;color:var(--muted);margin-bottom:1.1rem}
.tp-actions{display:flex;gap:.5rem}
.tp-btn-check{flex:1;background:var(--accent);color:#0d0d0f;border:none;padding:.75rem;border-radius:10px;font-family:'Syne',sans-serif;font-weight:800;font-size:.9rem;cursor:pointer;transition:all .2s}
.tp-btn-check:hover{background:var(--accent2)}
.tp-btn-skip{background:none;border:1px solid var(--border);color:var(--muted);padding:.75rem 1rem;border-radius:10px;font-family:'DM Sans',sans-serif;font-size:.85rem;cursor:pointer;transition:all .2s}
.tp-btn-skip:hover{border-color:var(--accent);color:var(--accent)}
.tp-btn-cancel{background:none;border:none;color:var(--muted);padding:.4rem;font-size:.8rem;cursor:pointer;font-family:inherit;width:100%;text-align:center;margin-top:.5rem}

.del{flex-shrink:0;background:none;border:none;color:var(--muted);cursor:pointer;padding:.25rem;border-radius:5px;font-size:.75rem;opacity:0;transition:all .15s;margin-top:2px}
.item-card:hover .del{opacity:1}
.del:hover{color:var(--danger);background:rgba(248,113,113,.1)}

.completed-banner{background:var(--adim);border:1px solid rgba(110,231,183,.25);border-radius:var(--radius);padding:1rem 1.1rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:.75rem;font-size:.85rem;color:var(--accent)}
.notes-box{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:.85rem 1rem;margin-bottom:1.5rem;font-size:.82rem;color:var(--muted);font-style:italic}
.conclude-box{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1.1rem;margin-top:2rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap}
.conclude-text strong{color:var(--text);display:block;font-size:.9rem;margin-bottom:.2rem}
.conclude-text{font-size:.82rem;color:var(--muted)}
.divider{border:none;border-top:1px solid var(--border);margin:1.5rem 0 1.25rem}
.empty-state{text-align:center;padding:2rem;color:var(--muted);font-size:.85rem}
</style>
@endpush

@section('content')
{{-- Toggle+Price Modal --}}
<div class="tp-overlay" id="tpOverlay">
    <div class="tp-modal">
        <div class="tp-title">✓ Marcar como comprado</div>
        <div class="tp-item-name" id="tpItemName"></div>
        <div class="tp-price-wrap">
            <input type="text" class="tp-price-input" id="tpPriceInput" placeholder="0,00" inputmode="numeric" aria-label="Preço em reais (opcional)">
        </div>
        <div class="tp-hint">Informe o preço pago (opcional). Você pode pular.</div>
        <div class="tp-actions">
            <button class="tp-btn-skip" onclick="submitToggle(false)">Pular</button>
            <button class="tp-btn-check" onclick="submitToggle(true)">✓ Confirmar</button>
        </div>
        <button class="tp-btn-cancel" onclick="closeToggleModal()">Cancelar</button>
        <form id="tpForm" method="POST" style="display:none">
            @csrf @method('PATCH')
            <input type="hidden" name="price" id="tpFormPrice">
        </form>
    </div>
</div>

{{-- Breadcrumb --}}
<div class="breadcrumb">
    <a href="{{ route('lists.index') }}">Minhas Listas</a>
    <span class="sep">›</span>
    <span>{{ $list->name }}</span>
</div>

{{-- Header --}}
<div class="list-header">
    <div class="list-title-row">
        <h1 class="list-title">{{ $list->name }}</h1>
        <div class="list-actions">
            <a href="https://www.confianca.com.br/bauru" target="_blank" class="btn btn-ghost btn-sm">🔗 Confiança</a>
            @if($list->isCompleted())
                <form method="POST" action="{{ route('lists.reopen', $list) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-ghost btn-sm" onclick="return confirm('Reabrir esta lista para edição?')">
                        🔓 Reabrir lista
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('lists.destroy', $list) }}" onsubmit="return confirm('Excluir esta lista?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                </form>
            @endif
        </div>
    </div>
    <div class="list-meta">
        <span class="meta-pill">📅 {{ $list->shopping_date->locale('pt_BR')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</span>
        @if($list->isCompleted())
            <span class="meta-pill green">✅ Concluída em {{ $list->completed_at->locale('pt_BR')->isoFormat('D/MM/YYYY [às] HH:mm') }}</span>
        @else
            <span class="meta-pill" style="color:var(--accent);border-color:rgba(110,231,183,.2)">🟢 Aberta</span>
        @endif
    </div>
</div>

@if($list->isCompleted())
<div class="completed-banner">
    ✅ <span>Lista concluída. Clique em <strong>🔓 Reabrir lista</strong> para editar novamente.</span>
</div>
@endif

@if($list->notes)
<div class="notes-box">📝 {{ $list->notes }}</div>
@endif

@php
    $allItems  = $list->items;
    $pending   = $allItems->where('purchased', false);
    $purchased = $allItems->where('purchased', true);
    $total     = $allItems->count();
    $boughtCnt = $purchased->count();
    $pct       = $total > 0 ? round(($boughtCnt / $total) * 100) : 0;
    $tPend     = $pending->sum(fn($i) => $i->subtotal ?? 0);
    $tDone     = $purchased->sum(fn($i) => $i->subtotal ?? 0);
    $tGeral    = $tPend + $tDone;
    $hasPrices = $allItems->contains(fn($i) => $i->price !== null);
@endphp

{{-- Totals --}}
@if($hasPrices)
<div class="totals-bar">
    <div class="tc">
        <div class="tc-label">A comprar</div>
        <div class="tc-val">R$ {{ number_format($tPend, 2, ',', '.') }}</div>
        <div class="tc-hint">{{ $pending->whereNotNull('price')->count() }} itens com preço</div>
    </div>
    <div class="tc">
        <div class="tc-label">Já comprado</div>
        <div class="tc-val">R$ {{ number_format($tDone, 2, ',', '.') }}</div>
        <div class="tc-hint">{{ $purchased->whereNotNull('price')->count() }} itens</div>
    </div>
    <div class="tc hl">
        <div class="tc-label">Total geral</div>
        <div class="tc-val">R$ {{ number_format($tGeral, 2, ',', '.') }}</div>
        <div class="tc-hint">estimado com preços informados</div>
    </div>
</div>
@endif

{{-- Progress --}}
@if($total > 0)
<div class="progress-section">
    <div class="progress-header">
        <span>Progresso da lista</span>
        <span>{{ $boughtCnt }}/{{ $total }} • {{ $pct }}%</span>
    </div>
    <div class="big-progress">
        <div class="big-progress-fill" style="width:{{ $pct }}%"></div>
    </div>
</div>
@endif

{{-- Add form --}}
@if($list->isOpen())
<div class="add-form">
    <form method="POST" action="{{ route('items.store', $list) }}">
        @csrf
        <div class="form-row">
            <div class="fg"><label>Produto</label><input type="text" name="name" placeholder="Ex: Arroz Tio João 5kg" required></div>
            <div class="fg sm"><label>Qtd</label><input type="number" name="qty" placeholder="1" value="1" min="0.001" step="0.001"></div>
            <div class="fg sm"><label>Unid.</label><input type="text" name="unit" placeholder="un,kg…"></div>
            <div class="fg md">
                <label>Preço (R$)</label>
                <div class="pr-wrap">
                    <em>R$</em>
                    <input type="text" name="price" class="price-mask" placeholder="0,00" inputmode="numeric">
                </div>
            </div>
            <button type="submit" class="btn-add">＋ Adicionar</button>
        </div>
    </form>
</div>
@endif

{{-- Pending items --}}
<div class="slabel">
    A comprar <span class="sbadge">{{ $pending->count() }}</span>
</div>
<div class="items-list">
@forelse($pending as $item)
    <div class="item-card" id="icard-{{ $item->id }}">
        {{-- Check button: opens modal if open list, disabled if completed --}}
        @if($list->isOpen())
            <button type="button" class="chk" onclick="openToggleModal({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ route('items.toggle', [$list, $item]) }}')" title="Marcar comprado">✓</button>
        @else
            <div class="chk" style="cursor:default;opacity:.4">✓</div>
        @endif

        <div class="iinfo">
            <div class="iname-row">
                <span class="iname">{{ $item->name }}</span>
                @if($item->unit)<span class="unit-tag">{{ $item->unit }}</span>@endif
            </div>
            <div class="imeta">
                <span class="qty-tag">Qtd: {{ rtrim(rtrim(number_format($item->qty,3,',','.'), '0'), ',') }}</span>
                @if($list->isOpen())
                    <span class="ppill {{ $item->price ? '' : 'empty' }}" onclick="toggleEdit({{ $item->id }})">
                        {{ $item->price ? 'R$ '.number_format($item->price,2,',','.') : '+ preço' }}
                    </span>
                @elseif($item->price)
                    <span class="ppill">R$ {{ number_format($item->price,2,',','.') }}</span>
                @endif
                @if($item->subtotal)<span class="sub">= R$ {{ number_format($item->subtotal,2,',','.') }}</span>@endif
            </div>
            @if($list->isOpen())
            <div class="iedit" id="edit-{{ $item->id }}">
                <form method="POST" action="{{ route('items.update', [$list, $item]) }}" style="display:contents">
                    @csrf @method('PATCH')
                    <div class="pwrap"><em>R$</em><input type="text" name="price" class="price-mask" value="{{ $item->price ? number_format($item->price,2,',','.') : '' }}" placeholder="0,00" inputmode="numeric"></div>
                    <input class="qinput" type="number" name="qty" value="{{ $item->qty }}" step="0.001" min="0.001">
                    <button type="submit" class="bsave">Salvar</button>
                </form>
                <button class="bcancel" onclick="closeEdit({{ $item->id }})">Cancelar</button>
            </div>
            @endif
        </div>

        @if($list->isOpen())
        <form method="POST" action="{{ route('items.destroy', [$list, $item]) }}" onsubmit="return confirm('Remover?')">
            @csrf @method('DELETE')
            <button type="submit" class="del" title="Remover">✕</button>
        </form>
        @endif
    </div>
@empty
    <div class="empty-state">{{ $list->isOpen() ? 'Adicione o primeiro item acima.' : 'Nenhum item pendente.' }}</div>
@endforelse
</div>

{{-- Purchased items --}}
@if($purchased->count() > 0)
<hr class="divider">
<div class="slabel">
    Comprados <span class="sbadge">{{ $purchased->count() }}</span>
</div>
<div class="items-list">
    @foreach($purchased as $item)
    <div class="item-card done">
        @if($list->isOpen())
            <button type="button" class="chk" onclick="openToggleModal({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ route('items.toggle', [$list, $item]) }}')" title="Desmarcar">✓</button>
        @else
            <div class="chk" style="background:var(--accent);border-color:var(--accent);color:#0d0d0f;cursor:default">✓</div>
        @endif
        <div class="iinfo">
            <div class="iname-row">
                <span class="iname">{{ $item->name }}</span>
                @if($item->unit)<span class="unit-tag">{{ $item->unit }}</span>@endif
            </div>
            <div class="imeta">
                <span class="qty-tag">Qtd: {{ rtrim(rtrim(number_format($item->qty,3,',','.'), '0'), ',') }}</span>
                {{-- Always allow price edit on purchased items (open or completed) --}}
                <span class="ppill {{ $item->price ? '' : 'empty' }}" onclick="toggleEdit({{ $item->id }})">
                    {{ $item->price ? 'R$ '.number_format($item->price,2,',','.') : '+ preço' }}
                </span>
                @if($item->subtotal)<span class="sub">= R$ {{ number_format($item->subtotal,2,',','.') }}</span>@endif
            </div>
            {{-- Price edit available even on completed lists for purchased items --}}
            <div class="iedit" id="edit-{{ $item->id }}">
                <form method="POST" action="{{ route('items.update', [$list, $item]) }}" style="display:contents">
                    @csrf @method('PATCH')
                    <div class="pwrap"><em>R$</em><input type="text" name="price" class="price-mask" value="{{ $item->price ? number_format($item->price,2,',','.') : '' }}" placeholder="0,00" inputmode="numeric"></div>
                    <button type="submit" class="bsave">Salvar</button>
                </form>
                <button class="bcancel" onclick="closeEdit({{ $item->id }})">Cancelar</button>
            </div>
        </div>
        @if($list->isOpen())
        <form method="POST" action="{{ route('items.destroy', [$list, $item]) }}" onsubmit="return confirm('Remover?')">
            @csrf @method('DELETE')
            <button type="submit" class="del" title="Remover">✕</button>
        </form>
        @endif
    </div>
    @endforeach
</div>
@endif

{{-- Conclude box --}}
@if($list->isOpen())
<div class="conclude-box">
    <div class="conclude-text">
        <strong>Concluir lista</strong>
        Ao concluir, a lista entra no histórico. Você pode reabrir depois se precisar.
    </div>
    <form method="POST" action="{{ route('lists.complete', $list) }}" onsubmit="return confirm('Concluir esta lista de compras?')">
        @csrf @method('PATCH')
        <button type="submit" class="btn btn-primary">✅ Concluir lista</button>
    </form>
</div>
@endif

<script>
// ── Price mask ─────────────────────────────────────────────────────────────
function applyMask(input) {
    let v = input.value.replace(/\D/g, '');
    if (!v) { input.value = ''; return; }
    v = (parseInt(v, 10) / 100).toFixed(2);
    input.value = v.replace('.', ',');
}
document.querySelectorAll('.price-mask').forEach(el => {
    el.addEventListener('input', () => applyMask(el));
    el.addEventListener('focus', () => { if (!el.value) el.value = ''; });
});

// Convert comma price to dot before form submit
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', () => {
        form.querySelectorAll('.price-mask').forEach(el => {
            el.value = el.value.replace(',', '.');
        });
    });
});

// ── Inline edit ────────────────────────────────────────────────────────────
function toggleEdit(id) {
    const el = document.getElementById('edit-' + id);
    const isOpen = el.classList.contains('open');
    document.querySelectorAll('.iedit.open').forEach(e => e.classList.remove('open'));
    if (!isOpen) {
        el.classList.add('open');
        const inp = el.querySelector('input');
        if (inp) inp.focus();
    }
}
function closeEdit(id) {
    document.getElementById('edit-' + id)?.classList.remove('open');
}

// ── Toggle + price modal ───────────────────────────────────────────────────
let currentToggleUrl = null;

function openToggleModal(itemId, itemName, url) {
    currentToggleUrl = url;
    document.getElementById('tpItemName').textContent = itemName;
    document.getElementById('tpPriceInput').value = '';
    document.getElementById('tpOverlay').classList.add('open');
    setTimeout(() => document.getElementById('tpPriceInput').focus(), 100);
}

function closeToggleModal() {
    document.getElementById('tpOverlay').classList.remove('open');
    currentToggleUrl = null;
}

function submitToggle(withPrice) {
    if (!currentToggleUrl) return;
    const form = document.getElementById('tpForm');
    form.action = currentToggleUrl;

    if (withPrice) {
        const raw = document.getElementById('tpPriceInput').value.replace(',', '.');
        const val = parseFloat(raw);
        document.getElementById('tpFormPrice').value = (!isNaN(val) && val > 0) ? val : '';
    } else {
        document.getElementById('tpFormPrice').value = '';
    }

    closeToggleModal();
    form.submit();
}

// Price mask on modal input
document.getElementById('tpPriceInput').addEventListener('input', function() {
    applyMask(this);
});
// Allow Enter to confirm
document.getElementById('tpPriceInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); submitToggle(true); }
    if (e.key === 'Escape') closeToggleModal();
});

// Close modal on backdrop click
document.getElementById('tpOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeToggleModal();
});
</script>
@endsection
