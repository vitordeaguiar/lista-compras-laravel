@extends('layouts.app')

@section('title', 'Admin — ' . $user->name)
@section('page-title', '👤 ' . $user->name)
@section('page-sub', $user->email)

@section('page-actions')
    <a href="{{ route('admin.index') }}" class="btn btn-ghost btn-sm">← Voltar</a>
    @if($user->id !== Auth::id())
        <button type="button" class="btn btn-danger btn-sm"
            data-name="{{ $user->name }}" data-url="{{ route('admin.users.destroy', $user) }}"
            onclick="confirmDelete(this.dataset.name, this.dataset.url)">🗑 Excluir</button>
    @endif
@endsection

@section('content')

{{-- Header --}}
<div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:1.25rem 1.4rem;margin-bottom:1rem;display:flex;align-items:center;gap:1.2rem;flex-wrap:wrap">
    <div style="width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,var(--accent) 0%,var(--blue) 100%);display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:700;color:#09090b;text-transform:uppercase;flex-shrink:0">
        {{ mb_substr($user->name, 0, 1) }}
    </div>
    <div style="flex:1;min-width:0">
        <div style="font-size:1rem;font-weight:700;color:var(--text);display:flex;align-items:center;gap:.5rem;flex-wrap:wrap">
            {{ $user->name }}
            @if($user->is_admin)
                <span style="font-size:.6rem;font-weight:700;padding:.1rem .42rem;border-radius:99px;background:rgba(129,140,248,.15);color:#818cf8;border:1px solid rgba(129,140,248,.25)">👑 Admin</span>
            @endif
        </div>
        <div style="font-size:.76rem;color:var(--text3);margin-top:.18rem">{{ $user->email }}</div>
        <div style="font-size:.71rem;color:var(--text3);margin-top:.1rem">Cadastrado em {{ $user->created_at->format('d/m/Y \à\s H:i') }}</div>
    </div>
    @if($user->id !== Auth::id())
        <form method="POST" action="{{ route('admin.users.toggle-admin', $user) }}">
            @csrf
            <button type="submit" class="btn btn-ghost btn-sm">
                {{ $user->is_admin ? '🚫 Remover admin' : '👑 Tornar admin' }}
            </button>
        </form>
    @endif
</div>

{{-- Stats grid --}}
<div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(140px,1fr))">
    <div class="stat-card">
        <div class="stat-label">Listas criadas</div>
        <div class="stat-val">{{ $stats['lists'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Listas concluídas</div>
        <div class="stat-val">{{ $stats['completed'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Itens adicionados</div>
        <div class="stat-val">{{ $stats['items'] }}</div>
    </div>
    <div class="stat-card hl">
        <div class="stat-label">Total de entradas</div>
        <div class="stat-val" style="font-size:.95rem">R$ {{ number_format($stats['income'], 2, ',', '.') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total fixos</div>
        <div class="stat-val" style="font-size:.95rem;color:var(--danger)">R$ {{ number_format($stats['fixed'], 2, ',', '.') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total variáveis</div>
        <div class="stat-val" style="font-size:.95rem;color:var(--danger)">R$ {{ number_format($stats['variable'], 2, ',', '.') }}</div>
    </div>
</div>

{{-- Modal exclusão --}}
<div class="modal-backdrop" id="del-modal">
    <div class="modal">
        <div class="modal-title">🗑 Excluir usuário</div>
        <p style="font-size:.82rem;color:var(--text2);margin-bottom:.5rem">
            Tem certeza que deseja excluir <strong id="del-user-name" style="color:var(--text)"></strong>?<br>
            <span style="font-size:.75rem;color:var(--danger)">Esta ação é irreversível e remove todos os dados do usuário.</span>
        </p>
        <form id="del-form" method="POST">
            @csrf @method('DELETE')
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeDelModal()">Cancelar</button>
                <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
            </div>
        </form>
    </div>
</div>

<script nonce="{{ $cspNonce }}">
function confirmDelete(name, action) {
    document.getElementById('del-user-name').textContent = name;
    document.getElementById('del-form').action = action;
    document.getElementById('del-modal').classList.add('open');
}
function closeDelModal() {
    document.getElementById('del-modal').classList.remove('open');
}
document.getElementById('del-modal').addEventListener('click', function(e) {
    if (e.target === this) closeDelModal();
});
</script>

@endsection
