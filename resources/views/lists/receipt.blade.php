@extends('layouts.app')
@section('title','Importar do cupom')
@section('page-title','Importar do cupom')
@section('page-sub','Crie uma lista a partir da foto do cupom fiscal')

@push('styles')
<style>
.cupom-wrap{max-width:680px}
.ccard{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:2.2rem 1.4rem;text-align:center}
.ccard .cicon{width:64px;height:64px;border-radius:16px;background:var(--adim);border:1px solid rgba(45,212,191,.2);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;color:var(--accent)}
.btn-lg{padding:.8rem 1.4rem;font-size:.9rem;min-height:48px}
.ccard .hint{font-size:.76rem;color:var(--text3);margin-top:.9rem;line-height:1.5}

.status{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.4rem;display:flex;flex-direction:column;align-items:center;gap:.8rem}
.spinner{width:30px;height:30px;border:3px solid var(--bg4);border-top-color:var(--accent);border-radius:50%;animation:cspin .8s linear infinite}
@keyframes cspin{to{transform:rotate(360deg)}}
@media(prefers-reduced-motion:reduce){.spinner{animation-duration:2s}}
.status #statusText{font-size:.84rem;color:var(--text2);font-weight:500}
.progress{width:100%;max-width:280px;height:4px;background:var(--bg4);border-radius:99px;overflow:hidden}
.progress-fill{height:100%;width:8%;background:var(--accent);border-radius:99px;transition:width .3s}

.form-row{display:grid;grid-template-columns:1fr 160px;gap:.75rem}
.item-row{display:grid;grid-template-columns:1fr 70px 60px 90px 36px;gap:.4rem;align-items:center;margin-bottom:.45rem}
.item-row input{min-height:40px}
.it-remove{background:var(--danger-dim);border:1px solid rgba(239,68,68,.25);color:var(--danger);border-radius:7px;width:36px;height:40px;cursor:pointer;font-size:1.1rem;line-height:1;display:flex;align-items:center;justify-content:center;font-family:inherit;touch-action:manipulation}
.it-remove:hover{background:rgba(239,68,68,.18)}
.it-qty,.it-price{text-align:right;font-variant-numeric:tabular-nums}
#addItemBtn{margin:.3rem 0 .5rem}

.item-head{display:grid;grid-template-columns:1fr 70px 60px 90px 36px;gap:.4rem;font-size:.62rem;text-transform:uppercase;letter-spacing:.06em;color:var(--text3);margin-bottom:.35rem;padding:0 .2rem}
.item-head span:nth-child(2),.item-head span:nth-child(4){text-align:right}

@media(max-width:560px){
    .form-row{grid-template-columns:1fr}
    .item-row{grid-template-columns:1fr 56px 48px 74px 34px;gap:.3rem}
    .item-head{grid-template-columns:1fr 56px 48px 74px 34px;gap:.3rem}
}
</style>
@endpush

@section('content')
<div class="cupom-wrap">

    {{-- Passo 1 — captura --}}
    <div id="captureCard" class="ccard">
        <div class="cicon" aria-hidden="true">
            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                <circle cx="12" cy="13" r="4"/>
            </svg>
        </div>
        <input type="file" id="cupomFile" accept="image/*" capture="environment" hidden>
        <button type="button" id="captureBtn" class="btn btn-primary btn-lg">Tirar foto do cupom</button>
        <p class="hint">Lemos os itens automaticamente: primeiro tentamos o <strong>QR&nbsp;Code</strong> da NFC-e e, se não houver, lemos o <strong>texto</strong> da foto.</p>
    </div>

    {{-- Status / progresso --}}
    <div id="statusBox" class="status" aria-live="polite" hidden>
        <div class="spinner" aria-hidden="true"></div>
        <span id="statusText">Lendo cupom…</span>
        <div class="progress"><div id="progressFill" class="progress-fill"></div></div>
    </div>

    {{-- Erro --}}
    <div id="errorBox" class="alert alert-error" role="alert" hidden></div>

    {{-- Passo 2 — revisão --}}
    <form id="reviewForm" method="POST" action="{{ route('lists.receipt.store') }}" hidden>
        @csrf
        <div class="form-row">
            <div class="form-group">
                <label for="listName">Nome da lista</label>
                <input type="text" id="listName" name="name" maxlength="255" required>
            </div>
            <div class="form-group">
                <label for="listDate">Data</label>
                <input type="date" id="listDate" name="shopping_date" required>
            </div>
        </div>

        <div class="sec-label" style="margin-top:.4rem">
            Itens detectados <span class="sec-badge" id="itemCount">0</span>
        </div>
        <div class="item-head">
            <span>Item</span><span>Qtd</span><span>Un</span><span>Preço</span><span></span>
        </div>
        <div id="itemsContainer"></div>
        <button type="button" id="addItemBtn" class="btn btn-ghost btn-sm">+ Adicionar item</button>

        <div class="modal-footer" style="border-top:1px solid var(--border)">
            <a href="{{ route('lists.index') }}" class="btn btn-ghost">Cancelar</a>
            <button type="submit" class="btn btn-primary">Criar lista</button>
        </div>
    </form>

    @if($errors->any())
        <div class="alert alert-error" role="alert" style="margin-top:1rem">{{ $errors->first() }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script nonce="{{ $cspNonce }}">
    window.CUPOM = {
        extractUrl: @json(route('lists.receipt.extract')),
        csrf: document.querySelector('meta[name="csrf-token"]').content,
    };
</script>
<script src="/vendor/jsqr/jsQR.js"></script>
<script src="/vendor/tesseract/tesseract.min.js"></script>
<script src="/js/receipt-import.js"></script>
@endpush
