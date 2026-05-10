@extends('layouts.app')
@section('title','Minhas Listas')

@push('styles')
<style>
.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.75rem;gap:1rem;flex-wrap:wrap}
.page-title{font-family:'Syne',sans-serif;font-weight:800;font-size:1.7rem;letter-spacing:-.03em}
.page-title span{color:var(--accent)}
.modal-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:200;align-items:center;justify-content:center;padding:1rem}
.modal-backdrop.open{display:flex}
.modal{background:var(--surface);border:1px solid var(--border);border-radius:18px;padding:1.75rem;width:100%;max-width:440px}
.modal-title{font-family:'Syne',sans-serif;font-weight:800;font-size:1.15rem;margin-bottom:1.25rem}
.modal-footer{display:flex;gap:.6rem;justify-content:flex-end;margin-top:1.25rem}
.open-lists{display:flex;flex-direction:column;gap:.75rem}
.lcard{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1rem 1.1rem;display:flex;align-items:center;gap:.9rem;transition:all .2s;text-decoration:none;color:inherit}
.lcard:hover{border-color:#3a3a45;transform:translateX(3px)}
.lcard-icon{width:40px;height:40px;border-radius:10px;background:var(--adim);border:1px solid rgba(110,231,183,.2);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
.lcard-info{flex:1;min-width:0}
.lcard-name{font-size:.95rem;font-weight:500;color:var(--text);margin-bottom:.15rem}
.lcard-meta{font-size:.75rem;color:var(--muted);display:flex;align-items:center;gap:.6rem;flex-wrap:wrap}
.lcard-right{display:flex;flex-direction:column;align-items:flex-end;gap:.3rem;flex-shrink:0}
.progress-bar{width:80px;height:4px;background:var(--surface2);border-radius:99px;overflow:hidden}
.progress-fill{height:100%;background:var(--accent);border-radius:99px;transition:width .3s}
.progress-label{font-size:.65rem;color:var(--muted)}
.empty-state{text-align:center;padding:3rem 1rem;color:var(--muted);font-size:.88rem}
.empty-state .emoji{font-size:2.5rem;display:block;margin-bottom:.6rem}
.shortcuts{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:.6rem;margin-top:2rem}
.shortcut{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:.9rem 1rem;text-decoration:none;color:inherit;transition:all .2s;display:flex;align-items:center;gap:.6rem}
.shortcut:hover{border-color:#3a3a45}
.shortcut-icon{font-size:1.2rem}
.shortcut-info .sh-label{font-size:.7rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em}
.shortcut-info .sh-title{font-size:.85rem;font-weight:500}
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Minhas <span>Listas</span></h1>
        <p style="color:var(--muted);font-size:.8rem;margin-top:.2rem">Organize suas compras do dia a dia</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('newListModal').classList.add('open')">+ Nova Lista</button>
</div>

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
                <textarea name="notes" placeholder="Alguma anotação importante…">{{ old('notes') }}</textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('newListModal').classList.remove('open')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Criar lista</button>
            </div>
        </form>
    </div>
</div>

@if($openLists->count() > 0)
<div class="open-lists">
    @foreach($openLists as $list)
        @php
            $total  = $list->items->count();
            $bought = $list->items->where('purchased', true)->count();
            $pct    = $total > 0 ? round(($bought / $total) * 100) : 0;
            $est    = $list->computedTotal;
        @endphp
        <a class="lcard" href="{{ route('lists.show', $list) }}">
            <div class="lcard-icon">🛒</div>
            <div class="lcard-info">
                <div class="lcard-name">{{ $list->name }}</div>
                <div class="lcard-meta">
                    <span>{{ $list->shopping_date->locale('pt_BR')->isoFormat('D [de] MMMM') }}</span>
                    <span style="opacity:.4">•</span>
                    <span>{{ $total }} {{ Str::plural('item', $total) }}</span>
                    @if($est > 0)
                        <span style="opacity:.4">•</span>
                        <span style="color:var(--accent)">R$ {{ number_format($est, 2, ',', '.') }}</span>
                    @endif
                </div>
            </div>
            <div class="lcard-right">
                <div class="progress-bar"><div class="progress-fill" style="width:{{ $pct }}%"></div></div>
                <div class="progress-label">{{ $bought }}/{{ $total }} comprados</div>
            </div>
        </a>
    @endforeach
</div>
@else
<div class="empty-state">
    <span class="emoji">🛒</span>
    <p>Nenhuma lista aberta.<br>Crie uma nova lista para começar!</p>
</div>
@endif

<div class="shortcuts">
    <a class="shortcut" href="{{ route('history.index') }}">
        <span class="shortcut-icon">📂</span>
        <div class="shortcut-info">
            <div class="sh-label">Ver</div>
            <div class="sh-title">Histórico</div>
        </div>
    </a>
    <a class="shortcut" href="{{ route('finance.index') }}">
        <span class="shortcut-icon">💰</span>
        <div class="shortcut-info">
            <div class="sh-label">Analisar</div>
            <div class="sh-title">Financeiro</div>
        </div>
    </a>
</div>

@if($errors->any())
<script>document.getElementById('newListModal').classList.add('open')</script>
@endif
@endsection
