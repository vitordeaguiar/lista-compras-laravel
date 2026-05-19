@extends('layouts.app')
@section('title','Dashboard')
@section('page-title','Dashboard')
@section('page-sub'){{ now()->locale('pt_BR')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}@endsection
@section('page-actions')
    <a href="{{ route('lists.index') }}" class="btn btn-primary">+ Nova Lista</a>
@endsection

@push('styles')
<style>
.dash-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.1rem;margin-top:1rem}

/* In-progress list cards */
.lcard{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:.85rem .95rem;display:flex;align-items:center;gap:.8rem;transition:border-color .18s;text-decoration:none;color:inherit;margin-bottom:.5rem}
.lcard:last-child{margin-bottom:0}
.lcard:hover{border-color:var(--border2)}
.lcard-icon{width:36px;height:36px;border-radius:9px;background:var(--adim);border:1px solid rgba(147,197,253,.2);display:flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0}
.lcard-info{flex:1;min-width:0}
.lcard-name{font-size:.83rem;font-weight:600;color:var(--text);margin-bottom:.25rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.lcard-meta{font-size:.68rem;color:var(--text3);margin-bottom:.35rem}
.prog-bar{height:3px;background:var(--bg3);border-radius:99px;overflow:hidden}
.prog-fill{height:100%;background:var(--accent);border-radius:99px}
.lcard-right{font-size:.7rem;color:var(--text3);flex-shrink:0;text-align:right}
.lcard-right strong{display:block;font-size:.82rem;font-weight:700;color:var(--accent)}

/* Activity */
.act-item{display:flex;align-items:flex-start;gap:.65rem;padding:.6rem 0;border-bottom:1px solid var(--border)}
.act-item:last-child{border-bottom:none;padding-bottom:0}
.act-icon{width:28px;height:28px;border-radius:8px;background:var(--bg3);display:flex;align-items:center;justify-content:center;font-size:.75rem;flex-shrink:0;margin-top:.05rem}
.act-text{flex:1;min-width:0}
.act-main{font-size:.79rem;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.act-sub{font-size:.67rem;color:var(--text3);margin-top:.05rem}
.act-time{font-size:.63rem;color:var(--text3);flex-shrink:0;margin-top:.05rem}

/* CSS Bar Chart */
.chart-wrap{margin-top:.6rem;overflow-x:auto}
.chart-bars{display:flex;align-items:flex-end;gap:.5rem;height:90px;min-width:280px}
.chart-col{flex:1;display:flex;flex-direction:column;align-items:center;height:100%}
.chart-col-inner{flex:1;width:100%;display:flex;flex-direction:column;align-items:center;justify-content:flex-end}
.chart-bar{width:100%;background:var(--accent);border-radius:4px 4px 0 0;min-height:3px;opacity:.75;transition:opacity .2s}
.chart-bar:hover{opacity:1}
.chart-bar.empty{background:var(--bg3)}
.chart-label{font-size:.59rem;color:var(--text3);margin-top:.35rem;text-align:center}
.chart-val{font-size:.58rem;color:var(--text2);margin-bottom:.15rem;text-align:center}

.panel{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:1rem 1.1rem}
.panel-title{font-size:.72rem;font-weight:600;color:var(--text);margin-bottom:.85rem;display:flex;align-items:center;justify-content:space-between}
.panel-title a{font-size:.65rem;color:var(--text3);text-decoration:none;font-weight:400}
.panel-title a:hover{color:var(--accent)}

.empty-hint{text-align:center;padding:1.25rem;color:var(--text3);font-size:.78rem}

/* Status badge */
.badge-open{display:inline-flex;align-items:center;gap:.25rem;font-size:.62rem;font-weight:600;padding:.08rem .38rem;border-radius:5px;background:var(--adim);color:var(--accent);border:1px solid rgba(147,197,253,.2)}

@media(max-width:768px){
    .dash-grid{grid-template-columns:1fr}
}
</style>
@endpush

@section('content')

{{-- 4 STAT CARDS --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Listas abertas</div>
        <div class="stat-val">{{ $listasAbertas }}</div>
        <div class="stat-hint">em andamento agora</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Concluídas este mês</div>
        <div class="stat-val">{{ $concluidasNoMes }}</div>
        <div class="stat-hint">{{ now()->locale('pt_BR')->isoFormat('MMMM [de] YYYY') }}</div>
    </div>
    <div class="stat-card hl">
        <div class="stat-label">Gasto este mês</div>
        <div class="stat-val">R$ {{ number_format($gastoNoMes, 2, ',', '.') }}</div>
        <div class="stat-hint">em listas concluídas</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Itens comprados</div>
        <div class="stat-val">{{ $itensNoMes }}</div>
        <div class="stat-hint">neste mês</div>
    </div>
</div>

<div class="dash-grid">
    {{-- LISTAS EM ANDAMENTO --}}
    <div class="panel">
        <div class="panel-title">
            Listas em andamento
            <a href="{{ route('lists.index') }}">Ver todas →</a>
        </div>
        @forelse($listasEmAndamento as $list)
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
                    <div class="lcard-meta">{{ $list->shopping_date->locale('pt_BR')->isoFormat('D [de] MMM') }} · {{ $total }} {{ Str::plural('item', $total) }}</div>
                    <div class="prog-bar"><div class="prog-fill" style="width:{{ $pct }}%"></div></div>
                </div>
                <div class="lcard-right">
                    @if($est > 0)<strong>R$ {{ number_format($est, 0, ',', '.') }}</strong>@endif
                    <span>{{ $bought }}/{{ $total }}</span>
                </div>
            </a>
        @empty
            <div class="empty-hint">
                Nenhuma lista aberta.<br>
                <a href="{{ route('lists.index') }}" style="color:var(--accent);text-decoration:none">Criar nova lista →</a>
            </div>
        @endforelse
    </div>

    {{-- ATIVIDADE RECENTE --}}
    <div class="panel">
        <div class="panel-title">Atividade recente</div>
        @forelse($atividadeRecente as $ativ)
            <div class="act-item">
                <div class="act-icon">{{ $ativ['icon'] }}</div>
                <div class="act-text">
                    <div class="act-main">{{ $ativ['text'] }}</div>
                    @if($ativ['subtext'])<div class="act-sub">{{ $ativ['subtext'] }}</div>@endif
                </div>
                <div class="act-time">{{ \Carbon\Carbon::parse($ativ['at'])->locale('pt_BR')->diffForHumans() }}</div>
            </div>
        @empty
            <div class="empty-hint">Nenhuma atividade ainda.</div>
        @endforelse
    </div>
</div>

{{-- MONTHLY CHART --}}
<div class="panel" style="margin-top:1.1rem">
    <div class="panel-title">Gastos mensais — últimos 6 meses</div>
    <div class="chart-wrap">
        <div class="chart-bars">
            @foreach($gastosPorMes as $mes)
                @php $pct = $mes['total'] > 0 ? max(4, ($mes['total'] / $maxGasto) * 100) : 4; @endphp
                <div class="chart-col">
                    <div class="chart-col-inner">
                        @if($mes['total'] > 0)
                            <div class="chart-val">R$ {{ number_format($mes['total'], 0, ',', '.') }}</div>
                        @endif
                        <div class="chart-bar {{ $mes['total'] <= 0 ? 'empty' : '' }}" style="height:{{ $pct }}%"></div>
                    </div>
                    <div class="chart-label">{{ $mes['label'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@endsection
