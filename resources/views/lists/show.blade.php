@extends('layouts.app')
@section('title', $list->name)

@section('page-title')
    <span style="font-size:.7rem;color:var(--text3);font-weight:400">
        <a href="{{ route('lists.index') }}" style="color:var(--text3);text-decoration:none;transition:color .15s" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text3)'">Minhas Listas</a>
        <span style="margin:0 .35rem;opacity:.4">›</span>
    </span>{{ $list->name }}
@endsection

@section('page-sub')
    {{ $list->shopping_date->locale('pt_BR')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
    @if($list->isCompleted())
        · <span style="color:var(--accent)">✅ Concluída</span>
    @else
        · <span style="color:var(--accent)">🟢 Aberta</span>
    @endif
@endsection

@section('page-actions')
    <a href="https://www.confianca.com.br/bauru" target="_blank" class="btn btn-ghost btn-sm">🔗 Confiança</a>
    @if($list->isCompleted())
        <form method="POST" action="{{ route('lists.reopen', $list) }}">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-ghost btn-sm" onclick="return confirm('Reabrir esta lista para edição?')">🔓 Reabrir</button>
        </form>
    @else
        <form method="POST" action="{{ route('lists.destroy', $list) }}" onsubmit="return confirm('Excluir esta lista?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
        </form>
    @endif
@endsection

@push('styles')
<style>
/* totals */
.totals-bar{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:.6rem;margin-bottom:1.25rem}
.tc{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:.8rem .9rem}
.tc.hl{border-color:rgba(163,230,53,.22);background:rgba(163,230,53,.04)}
.tc-label{font-size:.61rem;text-transform:uppercase;letter-spacing:.07em;color:var(--text3);margin-bottom:.22rem}
.tc-val{font-size:1.15rem;font-weight:700;color:var(--text)}
.tc.hl .tc-val{color:var(--accent)}
.tc-hint{font-size:.6rem;color:var(--text3);margin-top:.12rem}

/* progress */
.progress-section{margin-bottom:1.25rem}
.progress-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:.35rem;font-size:.75rem;color:var(--text3)}
.big-progress{height:5px;background:var(--bg3);border-radius:99px;overflow:hidden}
.big-progress-fill{height:100%;background:var(--accent);border-radius:99px;transition:width .4s}

/* add form */
.add-form{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:.85rem .95rem;margin-bottom:1.5rem}
.form-row{display:flex;gap:.4rem;flex-wrap:wrap;align-items:flex-end}
.fg{flex:1;min-width:110px}
.fg.sm{flex:0 0 65px;min-width:65px}
.fg.md{flex:0 0 115px;min-width:115px}
.fg label{display:block;font-size:.61rem;color:var(--text2);margin-bottom:.22rem;text-transform:uppercase;letter-spacing:.05em}
.fg input{background:var(--bg3);border:1px solid var(--border);color:var(--text);padding:.48rem .7rem;border-radius:7px;font-family:'Inter',sans-serif;font-size:.82rem;width:100%;outline:none;transition:border-color .2s}
.fg input:focus{border-color:var(--accent)}
.fg input::placeholder{color:var(--text3)}
.pr-wrap{position:relative}
.pr-wrap em{position:absolute;left:.55rem;top:50%;transform:translateY(-50%);color:var(--text3);font-style:normal;font-size:.72rem;pointer-events:none}
.pr-wrap input{padding-left:1.5rem}
.btn-add{background:var(--accent);color:#09090b;border:none;padding:.48rem .9rem;border-radius:7px;font-family:'Inter',sans-serif;font-weight:700;font-size:.8rem;cursor:pointer;white-space:nowrap;transition:all .18s}
.btn-add:hover{background:var(--accent2);transform:translateY(-1px)}

/* items */
.items-list{display:flex;flex-direction:column;gap:.35rem;margin-bottom:1.25rem}
.item-card{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);display:flex;align-items:flex-start;gap:.55rem;padding:.68rem .85rem;transition:border-color .18s;animation:si .15s ease}
@keyframes si{from{opacity:0;transform:translateY(-3px)}to{opacity:1;transform:none}}
.item-card:hover{border-color:var(--border2)}
.item-card.done{opacity:.45}
.item-card.done .iname{text-decoration:line-through;color:var(--text3)}

.chk{flex-shrink:0;margin-top:1px;width:22px;height:22px;border-radius:50%;border:1.5px solid var(--border);background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.65rem;color:transparent;transition:all .18s}
.chk:hover{border-color:var(--accent);color:var(--accent)}
.item-card.done .chk{background:var(--accent);border-color:var(--accent);color:#09090b}

.iinfo{flex:1;min-width:0}
.iname-row{display:flex;align-items:center;gap:.35rem;flex-wrap:wrap;margin-bottom:.18rem}
.iname{font-size:.85rem;color:var(--text)}
.unit-tag{font-size:.62rem;background:var(--bg3);border:1px solid var(--border);color:var(--text2);padding:.02rem .36rem;border-radius:4px}
.imeta{display:flex;align-items:center;gap:.4rem;flex-wrap:wrap}
.qty-tag{font-size:.67rem;color:var(--text3)}

/* price pill */
.ppill{display:inline-flex;align-items:center;gap:.18rem;padding:.08rem .4rem;border-radius:5px;font-size:.68rem;cursor:pointer;transition:all .18s;border:1px solid rgba(163,230,53,.22);background:rgba(163,230,53,.06);color:var(--accent)}
.ppill.empty{border-color:var(--border);background:var(--bg3);color:var(--text3)}
.ppill:hover{background:rgba(163,230,53,.12)}
.ppill.empty:hover{border-color:var(--accent);color:var(--accent)}
.sub{font-size:.67rem;color:var(--text3);font-style:italic}

/* inline edit */
.iedit{display:none;align-items:center;gap:.3rem;margin-top:.35rem;flex-wrap:wrap}
.iedit.open{display:flex}
.iedit input{background:var(--bg3);border:1px solid var(--border);color:var(--text);padding:.28rem .5rem;border-radius:5px;font-size:.77rem;font-family:inherit;outline:none;transition:border-color .2s}
.iedit input:focus{border-color:var(--accent)}
.iedit .pwrap{position:relative}
.iedit .pwrap em{position:absolute;left:.4rem;top:50%;transform:translateY(-50%);color:var(--text3);font-style:normal;font-size:.67rem;pointer-events:none}
.iedit .pwrap input{padding-left:1.2rem;width:95px}
.iedit .qinput{width:58px}
.bsave{background:var(--accent);color:#09090b;border:none;padding:.28rem .6rem;border-radius:5px;font-size:.72rem;font-weight:700;cursor:pointer;font-family:inherit;transition:all .18s}
.bsave:hover{background:var(--accent2)}
.bcancel{background:none;border:1px solid var(--border);color:var(--text2);padding:.28rem .5rem;border-radius:5px;font-size:.72rem;cursor:pointer;font-family:inherit}

/* toggle+price modal */
.tp-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:300;align-items:center;justify-content:center;padding:1rem}
.tp-overlay.open{display:flex}
.tp-modal{background:var(--bg2);border:1px solid var(--border);border-radius:14px;padding:1.4rem;width:100%;max-width:340px}
.tp-title{font-size:.9rem;font-weight:700;margin-bottom:.35rem;color:var(--text)}
.tp-item-name{font-size:.79rem;color:var(--text2);margin-bottom:1rem}
.tp-price-input{width:100%;background:var(--bg3);border:1px solid var(--border);color:var(--text);padding:.7rem .8rem;border-radius:9px;font-family:'Inter',sans-serif;font-size:1rem;font-weight:600;outline:none;transition:border-color .2s}
.tp-price-input:focus{border-color:var(--accent)}
.tp-hint{font-size:.72rem;color:var(--text3);margin:-.25rem 0 .9rem}
.tp-actions{display:flex;gap:.45rem}
.tp-btn-check{flex:1;background:var(--accent);color:#09090b;border:none;padding:.7rem;border-radius:9px;font-family:'Inter',sans-serif;font-weight:700;font-size:.85rem;cursor:pointer;transition:all .18s}
.tp-btn-check:hover{background:var(--accent2)}
.tp-btn-skip{background:none;border:1px solid var(--border);color:var(--text2);padding:.7rem .9rem;border-radius:9px;font-family:'Inter',sans-serif;font-size:.82rem;cursor:pointer;transition:all .18s}
.tp-btn-skip:hover{border-color:var(--accent);color:var(--accent)}
.tp-btn-cancel{background:none;border:none;color:var(--text3);padding:.35rem;font-size:.75rem;cursor:pointer;font-family:inherit;width:100%;text-align:center;margin-top:.45rem}

.del{flex-shrink:0;background:none;border:none;color:var(--text3);cursor:pointer;padding:.22rem;border-radius:4px;font-size:.72rem;opacity:0;transition:all .15s;margin-top:1px}
.item-card:hover .del{opacity:1}
.del:hover{color:var(--danger);background:rgba(239,68,68,.1)}

.completed-banner{background:var(--adim);border:1px solid rgba(163,230,53,.22);border-radius:var(--radius);padding:.85rem 1rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.65rem;font-size:.82rem;color:var(--accent)}
.notes-box{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:.75rem .9rem;margin-bottom:1.25rem;font-size:.79rem;color:var(--text3);font-style:italic}

.conclude-box{background:var(--bg2);border:1px solid rgba(163,230,53,.22);border-radius:var(--radius);padding:1rem 1.1rem;margin-top:1.75rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap}
.conclude-text strong{color:var(--text);display:block;font-size:.85rem;margin-bottom:.18rem}
.conclude-text{font-size:.78rem;color:var(--text3)}
.divider{border:none;border-top:1px solid var(--border);margin:1.25rem 0 1rem}
.empty-state{text-align:center;padding:1.75rem;color:var(--text3);font-size:.82rem}
</style>
@endpush

@section('content')
{{-- Toggle+Price Modal --}}
<div class="tp-overlay" id="tpOverlay">
    <div class="tp-modal">
        <div class="tp-title">✓ Marcar como comprado</div>
        <div class="tp-item-name" id="tpItemName"></div>
        <div style="margin-bottom:.65rem">
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

@if($list->isCompleted())
<div class="completed-banner">
    ✅ <span>Lista concluída. Clique em <strong>🔓 Reabrir</strong> para editar novamente.</span>
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
        <span>{{ $boughtCnt }}/{{ $total }} · {{ $pct }}%</span>
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
                <div class="pr-wrap"><em>R$</em><input type="text" name="price" class="price-mask" placeholder="0,00" inputmode="numeric"></div>
            </div>
            <button type="submit" class="btn-add">＋ Adicionar</button>
        </div>
    </form>
</div>
@endif

{{-- Pending items --}}
<div class="sec-label">
    A comprar <span class="sec-badge">{{ $pending->count() }}</span>
</div>
<div class="items-list">
@forelse($pending as $item)
    <div class="item-card" id="icard-{{ $item->id }}">
        @if($list->isOpen())
            <button type="button" class="chk" onclick="openToggleModal({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ route('items.toggle', [$list, $item]) }}')" title="Marcar comprado">✓</button>
        @else
            <div class="chk" style="cursor:default;opacity:.35">✓</div>
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
<div class="sec-label">
    Comprados <span class="sec-badge">{{ $purchased->count() }}</span>
</div>
<div class="items-list">
    @foreach($purchased as $item)
    <div class="item-card done">
        @if($list->isOpen())
            <button type="button" class="chk" onclick="openToggleModal({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ route('items.toggle', [$list, $item]) }}')" title="Desmarcar">✓</button>
        @else
            <div class="chk" style="background:var(--accent);border-color:var(--accent);color:#09090b;cursor:default">✓</div>
        @endif
        <div class="iinfo">
            <div class="iname-row">
                <span class="iname">{{ $item->name }}</span>
                @if($item->unit)<span class="unit-tag">{{ $item->unit }}</span>@endif
            </div>
            <div class="imeta">
                <span class="qty-tag">Qtd: {{ rtrim(rtrim(number_format($item->qty,3,',','.'), '0'), ',') }}</span>
                <span class="ppill {{ $item->price ? '' : 'empty' }}" onclick="toggleEdit({{ $item->id }})">
                    {{ $item->price ? 'R$ '.number_format($item->price,2,',','.') : '+ preço' }}
                </span>
                @if($item->subtotal)<span class="sub">= R$ {{ number_format($item->subtotal,2,',','.') }}</span>@endif
            </div>
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
function applyMask(input) {
    let v = input.value.replace(/\D/g, '');
    if (!v) { input.value = ''; return; }
    v = (parseInt(v, 10) / 100).toFixed(2);
    input.value = v.replace('.', ',');
}
document.querySelectorAll('.price-mask').forEach(el => {
    el.addEventListener('input', () => applyMask(el));
});
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', () => {
        form.querySelectorAll('.price-mask').forEach(el => {
            el.value = el.value.replace(',', '.');
        });
    });
});
function toggleEdit(id) {
    const el = document.getElementById('edit-' + id);
    const isOpen = el.classList.contains('open');
    document.querySelectorAll('.iedit.open').forEach(e => e.classList.remove('open'));
    if (!isOpen) { el.classList.add('open'); el.querySelector('input')?.focus(); }
}
function closeEdit(id) { document.getElementById('edit-' + id)?.classList.remove('open'); }

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
document.getElementById('tpPriceInput').addEventListener('input', function() { applyMask(this); });
document.getElementById('tpPriceInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); submitToggle(true); }
    if (e.key === 'Escape') closeToggleModal();
});
document.getElementById('tpOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeToggleModal();
});
</script>
@endsection
