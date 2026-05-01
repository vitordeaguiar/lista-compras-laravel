@extends('layouts.app')
@section('title','Minhas Listas')

@push('styles')
<style>
.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.75rem;gap:1rem;flex-wrap:wrap}
.page-title{font-family:'Syne',sans-serif;font-weight:800;font-size:1.7rem;letter-spacing:-.03em}
.page-title span{color:var(--accent)}

/* New list modal */
.modal-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:200;align-items:center;justify-content:center;padding:1rem}
.modal-backdrop.open{display:flex}
.modal{background:var(--surface);border:1px solid var(--border);border-radius:18px;padding:1.75rem;width:100%;max-width:440px}
.modal-title{font-family:'Syne',sans-serif;font-weight:800;font-size:1.15rem;margin-bottom:1.25rem}
.modal-footer{display:flex;gap:.6rem;justify-content:flex-end;margin-top:1.25rem}

/* Open lists */
.open-lists{display:flex;flex-direction:column;gap:.75rem;margin-bottom:2.5rem}
.list-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1rem 1.1rem;display:flex;align-items:center;gap:.9rem;transition:all .2s;text-decoration:none;color:inherit}
.list-card:hover{border-color:#3a3a45;transform:translateX(3px)}
.list-card-icon{width:40px;height:40px;border-radius:10px;background:var(--accent-dim);border:1px solid rgba(110,231,183,.2);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
.list-card-info{flex:1;min-width:0}
.list-card-name{font-size:.95rem;font-weight:500;color:var(--text);margin-bottom:.15rem}
.list-card-meta{font-size:.75rem;color:var(--muted);display:flex;align-items:center;gap:.6rem;flex-wrap:wrap}
.list-card-meta .dot{opacity:.4}
.list-card-right{display:flex;flex-direction:column;align-items:flex-end;gap:.3rem;flex-shrink:0}
.progress-bar{width:80px;height:4px;background:var(--surface2);border-radius:99px;overflow:hidden}
.progress-fill{height:100%;background:var(--accent);border-radius:99px;transition:width .3s}
.progress-label{font-size:.65rem;color:var(--muted)}

/* Section headers */
.section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem}
.section-title{font-family:'Syne',sans-serif;font-weight:700;font-size:1rem;color:var(--text)}
.section-badge{background:var(--surface2);border:1px solid var(--border);color:var(--muted);font-size:.68rem;padding:.15rem .55rem;border-radius:99px}

/* History */
.history-list{display:flex;flex-direction:column;gap:.6rem}
.history-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:.9rem 1.1rem;display:flex;align-items:center;gap:.9rem;text-decoration:none;color:inherit;transition:all .2s}
.history-card:hover{border-color:#3a3a45}
.history-icon{width:36px;height:36px;border-radius:9px;background:var(--surface2);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0;opacity:.7}
.history-info{flex:1;min-width:0}
.history-name{font-size:.88rem;color:var(--text);margin-bottom:.1rem}
.history-meta{font-size:.72rem;color:var(--muted)}
.history-total{font-family:'Syne',sans-serif;font-weight:700;font-size:.95rem;color:var(--text);flex-shrink:0}
.history-total.zero{color:var(--muted);font-size:.78rem;font-family:'DM Sans',sans-serif;font-weight:400}

.status-dot{display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--accent);margin-right:.3rem;vertical-align:middle}
.empty-state{text-align:center;padding:3rem 1rem;color:var(--muted);font-size:.88rem}
.empty-state .emoji{font-size:2.5rem;display:block;margin-bottom:.6rem}
.empty-state p{line-height:1.8}

/* compare badge */
.compare-badge{display:inline-flex;align-items:center;gap:.3rem;font-size:.7rem;padding:.15rem .5rem;border-radius:6px}
.compare-badge.up{background:rgba(248,113,113,.1);color:var(--danger)}
.compare-badge.down{background:rgba(110,231,183,.1);color:var(--accent)}
.compare-badge.same{background:var(--surface2);color:var(--muted)}
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Minhas <span>Listas</span></h1>
        <p style="color:var(--muted);font-size:.8rem;margin-top:.2rem">Organize e acompanhe suas compras</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('newListModal').classList.add('open')">
        + Nova Lista
    </button>
</div>

{{-- MODAL NOVA LISTA --}}
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

{{-- LISTAS ABERTAS --}}
@if($openLists->count() > 0)
<div class="section-header">
    <div class="section-title"><span class="status-dot"></span>Listas abertas</div>
    <span class="section-badge">{{ $openLists->count() }}</span>
</div>
<div class="open-lists">
    @foreach($openLists as $list)
        @php
            $total     = $list->items->count();
            $bought    = $list->items->where('purchased', true)->count();
            $pct       = $total > 0 ? round(($bought / $total) * 100) : 0;
            $estimated = $list->computedTotal;
        @endphp
        <a class="list-card" href="{{ route('lists.show', $list) }}">
            <div class="list-card-icon">🛒</div>
            <div class="list-card-info">
                <div class="list-card-name">{{ $list->name }}</div>
                <div class="list-card-meta">
                    <span>{{ $list->shopping_date->locale('pt_BR')->isoFormat('D [de] MMMM') }}</span>
                    <span class="dot">•</span>
                    <span>{{ $total }} {{ Str::plural('item', $total) }}</span>
                    @if($estimated > 0)
                        <span class="dot">•</span>
                        <span style="color:var(--accent)">R$ {{ number_format($estimated, 2, ',', '.') }}</span>
                    @endif
                </div>
            </div>
            <div class="list-card-right">
                <div class="progress-bar"><div class="progress-fill" style="width:{{ $pct }}%"></div></div>
                <div class="progress-label">{{ $bought }}/{{ $total }} comprados</div>
            </div>
        </a>
    @endforeach
</div>
@else
<div class="empty-state" style="margin-bottom:2rem">
    <span class="emoji">🛒</span>
    <p>Nenhuma lista aberta.<br>Crie uma nova lista para começar!</p>
</div>
@endif

{{-- HISTÓRICO --}}
<div class="section-header" style="margin-top:1rem">
    <div class="section-title">📂 Histórico</div>
    <span class="section-badge">{{ $history->total() }}</span>
</div>

@if($history->count() > 0)
<div class="history-list">
    @foreach($history as $index => $list)
        @php
            $prev = $history->items()[$index + 1] ?? null;
            $diff = $prev ? $list->total - $prev->total : null;
            $pct  = ($prev && $prev->total > 0) ? round((($list->total - $prev->total) / $prev->total) * 100, 1) : null;
        @endphp
        <a class="history-card" href="{{ route('lists.show', $list) }}">
            <div class="history-icon">✅</div>
            <div class="history-info">
                <div class="history-name">{{ $list->name }}</div>
                <div class="history-meta">
                    {{ $list->shopping_date->locale('pt_BR')->isoFormat('D [de] MMMM [de] YYYY') }}
                    &nbsp;·&nbsp;
                    {{ $list->items->count() }} itens
                    @if($pct !== null)
                        &nbsp;
                        @if($diff > 0)
                            <span class="compare-badge up">▲ {{ abs($pct) }}% vs anterior</span>
                        @elseif($diff < 0)
                            <span class="compare-badge down">▼ {{ abs($pct) }}% vs anterior</span>
                        @else
                            <span class="compare-badge same">= igual ao anterior</span>
                        @endif
                    @endif
                </div>
            </div>
            @if($list->total > 0)
                <div class="history-total">R$ {{ number_format($list->total, 2, ',', '.') }}</div>
            @else
                <div class="history-total zero">sem preços</div>
            @endif
        </a>
    @endforeach
</div>
{{ $history->links() }}
@else
<div class="empty-state">
    <span class="emoji">📭</span>
    <p>Nenhuma lista concluída ainda.<br>Conclua uma lista após as compras para<br>ela aparecer aqui.</p>
</div>
@endif

@if($errors->any())
<script>document.getElementById('newListModal').classList.add('open')</script>
@endif
@endsection
