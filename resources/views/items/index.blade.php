@extends('layouts.app')

@section('title', 'Minha Lista')

@push('styles')
<style>
/* ── PAGE ── */
.page-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;gap:1rem;flex-wrap:wrap}
.page-title{font-family:'Syne',sans-serif;font-weight:800;font-size:1.8rem;letter-spacing:-.03em;line-height:1.1}
.page-title span{color:var(--accent)}
.page-date{color:var(--muted);font-size:.8rem;margin-top:.25rem}
.confianca-link{display:inline-flex;align-items:center;gap:.4rem;background:var(--surface);border:1px solid var(--border);color:var(--muted);padding:.4rem .9rem;border-radius:9px;font-size:.78rem;text-decoration:none;transition:all .2s;white-space:nowrap}
.confianca-link:hover{border-color:var(--accent);color:var(--accent)}
.confianca-link svg{flex-shrink:0}

/* ── TOTALS BAR ── */
.totals-bar{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:.6rem;margin-bottom:1.75rem}
.total-card{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:.85rem 1rem}
.total-card.highlight{border-color:rgba(110,231,183,.3);background:rgba(110,231,183,.05)}
.total-label{font-size:.68rem;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:.3rem}
.total-value{font-family:'Syne',sans-serif;font-weight:800;font-size:1.2rem;color:var(--text)}
.total-card.highlight .total-value{color:var(--accent)}
.total-hint{font-size:.68rem;color:var(--muted);margin-top:.2rem}

/* ── ADD FORM ── */
.add-form{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:1.1rem 1.2rem;margin-bottom:1.75rem}
.add-form-row{display:flex;gap:.6rem;flex-wrap:wrap;align-items:flex-end}
.fg{flex:1;min-width:120px}
.fg.sm{max-width:90px}
.fg.md{max-width:120px}
.fg label{display:block;font-size:.68rem;color:var(--muted);margin-bottom:.3rem;text-transform:uppercase;letter-spacing:.05em}
.fg input{background:var(--surface2);border:1px solid var(--border);color:var(--text);padding:.6rem .85rem;border-radius:9px;font-family:'DM Sans',sans-serif;font-size:.9rem;width:100%;outline:none;transition:border-color .2s}
.fg input:focus{border-color:var(--accent)}
.fg input::placeholder{color:var(--muted)}
.price-input{position:relative}
.price-input span{position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.85rem;pointer-events:none}
.price-input input{padding-left:1.7rem}
.btn-add{background:var(--accent);color:#0d0d0f;border:none;padding:.6rem 1.2rem;border-radius:9px;font-family:'Syne',sans-serif;font-weight:700;font-size:.88rem;cursor:pointer;white-space:nowrap;transition:all .2s;display:flex;align-items:center;gap:.3rem}
.btn-add:hover{background:var(--accent2);transform:translateY(-1px)}

/* ── SECTION HEADER ── */
.section-label{font-size:.68rem;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:.65rem;display:flex;align-items:center;justify-content:space-between}
.count-badge{background:var(--surface2);border:1px solid var(--border);color:var(--muted);font-size:.65rem;padding:.12rem .5rem;border-radius:99px}

/* ── ITEM CARDS ── */
.items-list{display:flex;flex-direction:column;gap:.45rem;margin-bottom:1.5rem}
.item-card{background:var(--surface);border:1px solid var(--border);border-radius:13px;display:flex;align-items:center;gap:.65rem;padding:.8rem .95rem;transition:all .2s;animation:slideIn .18s ease}
@keyframes slideIn{from{opacity:0;transform:translateY(-5px)}to{opacity:1;transform:none}}
.item-card:hover{border-color:#3a3a45}
.item-card.purchased{opacity:.45;background:#111115}
.item-card.purchased .iname{text-decoration:line-through;color:var(--muted)}

.check-btn{flex-shrink:0;width:26px;height:26px;border-radius:50%;border:2px solid var(--border);background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.72rem;color:transparent;transition:all .2s}
.check-btn:hover{border-color:var(--accent);color:var(--accent)}
.item-card.purchased .check-btn{background:var(--accent);border-color:var(--accent);color:#0d0d0f}

.iinfo{flex:1;min-width:0}
.iname{font-size:.92rem;color:var(--text);display:flex;align-items:center;gap:.5rem;flex-wrap:wrap}
.iname .unit-tag{font-size:.7rem;background:var(--surface2);border:1px solid var(--border);color:var(--muted);padding:.05rem .4rem;border-radius:5px}
.imeta{display:flex;align-items:center;gap:.6rem;margin-top:.25rem;flex-wrap:wrap}

/* inline price edit */
.price-pill{display:inline-flex;align-items:center;gap:.3rem;background:rgba(110,231,183,.08);border:1px solid rgba(110,231,183,.2);border-radius:7px;padding:.15rem .55rem;font-size:.75rem;color:var(--accent);cursor:pointer;transition:all .2s}
.price-pill:hover{background:rgba(110,231,183,.15)}
.price-pill.empty{background:var(--surface2);border-color:var(--border);color:var(--muted)}
.price-pill.empty:hover{border-color:var(--accent);color:var(--accent)}

.qty-display{font-size:.75rem;color:var(--muted)}
.subtotal-display{font-size:.75rem;color:var(--muted);font-style:italic}

/* inline edit form */
.inline-edit{display:none;align-items:center;gap:.4rem;margin-top:.4rem;flex-wrap:wrap}
.inline-edit.show{display:flex}
.inline-edit input{background:var(--surface2);border:1px solid var(--border);color:var(--text);padding:.3rem .6rem;border-radius:7px;font-size:.8rem;font-family:'DM Sans',sans-serif;outline:none;width:90px;transition:border-color .2s}
.inline-edit input:focus{border-color:var(--accent)}
.inline-edit .price-wrap{position:relative}
.inline-edit .price-wrap span{position:absolute;left:.5rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.75rem;pointer-events:none}
.inline-edit .price-wrap input{padding-left:1.3rem}
.btn-save{background:var(--accent);color:#0d0d0f;border:none;padding:.3rem .7rem;border-radius:7px;font-size:.75rem;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .2s}
.btn-save:hover{background:var(--accent2)}
.btn-cancel-edit{background:none;border:1px solid var(--border);color:var(--muted);padding:.3rem .6rem;border-radius:7px;font-size:.75rem;cursor:pointer;font-family:'DM Sans',sans-serif}

.del-btn{flex-shrink:0;background:none;border:none;color:var(--muted);cursor:pointer;padding:.3rem;border-radius:6px;font-size:.8rem;opacity:0;transition:all .15s}
.item-card:hover .del-btn{opacity:1}
.del-btn:hover{color:var(--danger);background:rgba(248,113,113,.1)}

/* ── EMPTY ── */
.empty-state{text-align:center;padding:2.5rem 1rem;color:var(--muted);font-size:.88rem}
.empty-state .emoji{font-size:2rem;display:block;margin-bottom:.5rem}

/* ── DIVIDER / RESET ── */
.divider{border:none;border-top:1px solid var(--border);margin:1.75rem 0 1.25rem}
.reset-area{text-align:center;margin-top:1.2rem}
.btn-reset{background:none;border:1px solid var(--border);color:var(--muted);padding:.45rem 1.1rem;border-radius:9px;font-family:'DM Sans',sans-serif;font-size:.8rem;cursor:pointer;transition:all .2s}
.btn-reset:hover{border-color:var(--accent);color:var(--accent)}
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Minha <span>Lista</span></h1>
        <p class="page-date">{{ now()->locale('pt_BR')->isoFormat('dddd, D [de] MMMM') }}</p>
    </div>
    <a class="confianca-link" href="https://www.confianca.com.br/bauru" target="_blank" rel="noopener">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        Ver preços no Confiança
    </a>
</div>

{{-- TOTALS --}}
@if($hasPrices)
<div class="totals-bar">
    <div class="total-card">
        <div class="total-label">A comprar</div>
        <div class="total-value">R$ {{ number_format($totalPending, 2, ',', '.') }}</div>
        <div class="total-hint">{{ $pending->whereNotNull('price')->count() }} itens com preço</div>
    </div>
    <div class="total-card">
        <div class="total-label">Já comprado</div>
        <div class="total-value">R$ {{ number_format($totalPurchased, 2, ',', '.') }}</div>
        <div class="total-hint">{{ $purchased->whereNotNull('price')->count() }} itens</div>
    </div>
    <div class="total-card highlight">
        <div class="total-label">Total geral</div>
        <div class="total-value">R$ {{ number_format($totalGeral, 2, ',', '.') }}</div>
        <div class="total-hint">estimado com preços informados</div>
    </div>
</div>
@endif

{{-- ADD FORM --}}
<form class="add-form" method="POST" action="{{ route('items.store') }}">
    @csrf
    <div class="add-form-row">
        <div class="fg">
            <label>Produto</label>
            <input type="text" name="name" placeholder="Ex: Arroz Tio João 5kg" value="{{ old('name') }}" required>
        </div>
        <div class="fg sm">
            <label>Qtd</label>
            <input type="number" name="qty" placeholder="1" value="{{ old('qty', 1) }}" min="0.001" step="0.001">
        </div>
        <div class="fg sm">
            <label>Unid.</label>
            <input type="text" name="unit" placeholder="un, kg…" value="{{ old('unit') }}">
        </div>
        <div class="fg md">
            <label>Preço unit. (R$)</label>
            <div class="price-input">
                <span>R$</span>
                <input type="number" name="price" placeholder="0,00" value="{{ old('price') }}" min="0" step="0.01">
            </div>
        </div>
        <button type="submit" class="btn-add">＋ Adicionar</button>
    </div>
    @error('name') <p style="color:var(--danger);font-size:.8rem;margin-top:.5rem">{{ $message }}</p> @enderror
</form>

{{-- PENDING ITEMS --}}
<div class="section-label">
    A comprar
    <span class="count-badge">{{ $pending->count() }}</span>
</div>

<div class="items-list">
@forelse($pending as $item)
    <div class="item-card" id="card-{{ $item->id }}">
        <form method="POST" action="{{ route('items.toggle', $item) }}" style="display:contents">
            @csrf @method('PATCH')
            <button type="submit" class="check-btn" title="Marcar comprado">✓</button>
        </form>

        <div class="iinfo">
            <div class="iname">
                {{ $item->name }}
                @if($item->unit) <span class="unit-tag">{{ $item->unit }}</span> @endif
            </div>
            <div class="imeta">
                <span class="qty-display">Qtd: {{ rtrim(rtrim(number_format($item->qty, 3, ',', '.'), '0'), ',') }}</span>
                <span class="price-pill {{ $item->price ? '' : 'empty' }}"
                      onclick="toggleEdit({{ $item->id }})"
                      title="Clique para editar preço">
                    {{ $item->price ? 'R$ ' . number_format($item->price, 2, ',', '.') : '+ preço' }}
                </span>
                @if($item->subtotal)
                    <span class="subtotal-display">= R$ {{ number_format($item->subtotal, 2, ',', '.') }}</span>
                @endif
            </div>
            <div class="inline-edit" id="edit-{{ $item->id }}">
                <form method="POST" action="{{ route('items.update', $item) }}" style="display:contents">
                    @csrf @method('PATCH')
                    <div class="price-wrap">
                        <span>R$</span>
                        <input type="number" name="price" value="{{ $item->price }}" placeholder="0,00" step="0.01" min="0">
                    </div>
                    <input type="number" name="qty" value="{{ $item->qty }}" placeholder="Qtd" step="0.001" min="0.001" style="width:70px">
                    <button type="submit" class="btn-save">Salvar</button>
                </form>
                <button class="btn-cancel-edit" onclick="toggleEdit({{ $item->id }})">Cancelar</button>
            </div>
        </div>

        <form method="POST" action="{{ route('items.destroy', $item) }}" onsubmit="return confirm('Remover?')">
            @csrf @method('DELETE')
            <button type="submit" class="del-btn" title="Remover">✕</button>
        </form>
    </div>
@empty
    <div class="empty-state">
        <span class="emoji">🎉</span>
        Tudo comprado! Adicione novos itens acima.
    </div>
@endforelse
</div>

{{-- PURCHASED --}}
@if($purchased->count() > 0)
<hr class="divider">
<div class="section-label">
    Comprados hoje
    <span class="count-badge">{{ $purchased->count() }}</span>
</div>
<div class="items-list">
    @foreach($purchased as $item)
    <div class="item-card purchased">
        <form method="POST" action="{{ route('items.toggle', $item) }}" style="display:contents">
            @csrf @method('PATCH')
            <button type="submit" class="check-btn" title="Desmarcar">✓</button>
        </form>
        <div class="iinfo">
            <div class="iname">
                {{ $item->name }}
                @if($item->unit) <span class="unit-tag">{{ $item->unit }}</span> @endif
            </div>
            <div class="imeta">
                <span class="qty-display">Qtd: {{ rtrim(rtrim(number_format($item->qty, 3, ',', '.'), '0'), ',') }}</span>
                @if($item->price)
                    <span class="price-pill">R$ {{ number_format($item->price, 2, ',', '.') }}</span>
                    <span class="subtotal-display">= R$ {{ number_format($item->subtotal, 2, ',', '.') }}</span>
                @endif
            </div>
        </div>
        <form method="POST" action="{{ route('items.destroy', $item) }}" onsubmit="return confirm('Remover?')">
            @csrf @method('DELETE')
            <button type="submit" class="del-btn" title="Remover">✕</button>
        </form>
    </div>
    @endforeach
</div>
<div class="reset-area">
    <form method="POST" action="{{ route('items.reset') }}" onsubmit="return confirm('Desmarcar todos os comprados de hoje?')">
        @csrf
        <button type="submit" class="btn-reset">↺ Reiniciar lista do dia</button>
    </form>
</div>
@endif

<script>
function toggleEdit(id) {
    const el = document.getElementById('edit-' + id);
    el.classList.toggle('show');
    if (el.classList.contains('show')) {
        el.querySelector('input[name="price"]').focus();
    }
}
</script>
@endsection
