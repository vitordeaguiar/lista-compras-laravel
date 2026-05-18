@extends('layouts.app')
@section('title','Minhas Listas')
@section('page-title','Minhas Listas')
@section('page-sub','Organize suas compras do dia a dia')
@section('page-actions')
    <button class="btn btn-primary" onclick="document.getElementById('newListModal').classList.add('open')">+ Nova Lista</button>
@endsection

@push('styles')
<style>
.lists-grid{display:flex;flex-direction:column;gap:.5rem}
.lcard{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:.9rem 1rem;display:flex;align-items:center;gap:.85rem;transition:border-color .18s,transform .15s;text-decoration:none;color:inherit}
.lcard:hover{border-color:var(--border2);transform:translateX(2px)}
.lcard-icon{width:38px;height:38px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
.lcard-info{flex:1;min-width:0}
.lcard-name{font-size:.88rem;font-weight:600;color:var(--text);margin-bottom:.2rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.lcard-meta{font-size:.7rem;color:var(--text3);display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-bottom:.4rem}
.lcard-meta .dot{opacity:.35}
.prog-bar{height:3px;background:var(--bg3);border-radius:99px;overflow:hidden}
.prog-fill{height:100%;background:var(--accent);border-radius:99px;transition:width .3s}
.lcard-right{display:flex;flex-direction:column;align-items:flex-end;gap:.3rem;flex-shrink:0}
.badge-open{font-size:.62rem;font-weight:600;padding:.1rem .4rem;border-radius:5px;background:var(--adim);color:var(--accent);border:1px solid rgba(163,230,53,.2)}
.badge-done{font-size:.62rem;font-weight:600;padding:.1rem .4rem;border-radius:5px;background:var(--bluedim);color:#818cf8;border:1px solid rgba(99,102,241,.2)}
.lcard-val{font-size:.82rem;font-weight:700;color:var(--accent)}
.lcard-prog-label{font-size:.63rem;color:var(--text3)}

.empty-state{text-align:center;padding:3.5rem 1rem;color:var(--text3);font-size:.83rem}
.empty-state .emoji{font-size:2.2rem;display:block;margin-bottom:.6rem}

.icon-box-green{background:var(--adim);border:1px solid rgba(163,230,53,.2)}
.icon-box-blue{background:var(--bluedim);border:1px solid rgba(99,102,241,.2)}
.icon-box-yellow{background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.2)}
.icon-box-red{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2)}
</style>
@endpush

@section('content')

{{-- NEW LIST MODAL --}}
<div class="modal-backdrop" id="newListModal" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal">
        <div class="modal-title">📋 Nova Lista de Compras</div>
        <form method="POST" action="{{ route('lists.store') }}">
            @csrf
            <div class="form-group">
                <label>Nome da lista</label>
                <input type="text" name="name" placeholder="Ex: Compras da semana, Churrasco…" required autofocus value="{{ old('name') }}">
                @error('name')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>Data prevista</label>
                <input type="date" name="shopping_date" required value="{{ old('shopping_date', now()->toDateString()) }}">
                @error('shopping_date')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>Observações (opcional)</label>
                <textarea name="notes" rows="2" placeholder="Alguma anotação importante…">{{ old('notes') }}</textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('newListModal').classList.remove('open')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Criar lista</button>
            </div>
        </form>
    </div>
</div>

@php
    $iconBoxes = ['icon-box-green','icon-box-blue','icon-box-yellow','icon-box-red'];
@endphp

@if($openLists->count() > 0)
<div class="lists-grid">
    @foreach($openLists as $i => $list)
        @php
            $total  = $list->items->count();
            $bought = $list->items->where('purchased', true)->count();
            $pct    = $total > 0 ? round(($bought / $total) * 100) : 0;
            $est    = $list->computedTotal;
            $box    = $iconBoxes[$i % 4];
        @endphp
        <a class="lcard" href="{{ route('lists.show', $list) }}">
            <div class="lcard-icon {{ $box }}">🛒</div>
            <div class="lcard-info">
                <div class="lcard-name">{{ $list->name }}</div>
                <div class="lcard-meta">
                    <span>{{ $list->shopping_date->locale('pt_BR')->isoFormat('D [de] MMMM') }}</span>
                    <span class="dot">•</span>
                    <span>{{ $total }} {{ Str::plural('item', $total) }}</span>
                    @if($est > 0)
                        <span class="dot">•</span>
                        <span style="color:var(--accent)">R$ {{ number_format($est, 2, ',', '.') }}</span>
                    @endif
                </div>
                <div class="prog-bar"><div class="prog-fill" style="width:{{ $pct }}%"></div></div>
            </div>
            <div class="lcard-right">
                <span class="badge-open">Em andamento</span>
                <span class="lcard-prog-label">{{ $bought }}/{{ $total }} comprados</span>
            </div>
        </a>
    @endforeach
</div>
@else
<div class="empty-state">
    <span class="emoji">🛒</span>
    <p>Nenhuma lista aberta.<br>Crie uma nova lista para começar!</p>
    <button class="btn btn-primary" style="margin-top:1rem" onclick="document.getElementById('newListModal').classList.add('open')">+ Nova Lista</button>
</div>
@endif

@if($errors->any())
<script>document.getElementById('newListModal').classList.add('open')</script>
@endif
@endsection
