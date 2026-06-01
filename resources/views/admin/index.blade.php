@extends('layouts.app')

@section('title', 'Painel Administrativo — Smart Listiq')
@section('page-title', '🛡️ Painel Administrativo')
@section('page-sub', $users->total() . ' usuários cadastrados')

@push('styles')
<style>
.admin-table{width:100%;border-collapse:collapse}
.admin-table th{font-size:.63rem;text-transform:uppercase;letter-spacing:.08em;color:var(--text3);padding:.5rem .75rem;border-bottom:1px solid var(--border);text-align:left;white-space:nowrap}
.admin-table td{padding:.6rem .75rem;border-bottom:1px solid var(--border);vertical-align:middle;font-size:.8rem}
.admin-table tr:last-child td{border-bottom:none}
.admin-table tr:hover td{background:var(--bg3)}
.user-avatar-sm{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--accent) 0%,var(--blue) 100%);display:inline-flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;color:#09090b;text-transform:uppercase;flex-shrink:0}
.badge{display:inline-block;font-size:.6rem;font-weight:700;padding:.1rem .42rem;border-radius:99px;white-space:nowrap}
.badge-admin{background:rgba(129,140,248,.15);color:#818cf8;border:1px solid rgba(129,140,248,.25)}
.badge-user{background:var(--bg3);color:var(--text3);border:1px solid var(--border)}
.badge-active{background:rgba(34,197,94,.12);color:#22c55e;border:1px solid rgba(34,197,94,.2)}
.badge-inactive{background:rgba(239,68,68,.1);color:var(--danger);border:1px solid rgba(239,68,68,.2)}
.search-wrap{display:flex;gap:.5rem;align-items:center}
.table-card{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden}
.pagination-wrap{padding:.75rem 1rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem}
.page-info{font-size:.72rem;color:var(--text3)}
.page-links{display:flex;gap:.2rem}
.page-links a,.page-links span{padding:.28rem .52rem;border-radius:6px;font-size:.72rem;text-decoration:none;color:var(--text2);border:1px solid var(--border);background:var(--bg3)}
.page-links a:hover{border-color:var(--accent);color:var(--accent)}
.page-links span.active-page{background:var(--accent);color:#09090b;border-color:var(--accent);font-weight:700}
</style>
@endpush

@section('page-actions')
    <form method="GET" action="{{ route('admin.index') }}" class="search-wrap">
        <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por nome ou e-mail…" style="width:220px">
        <button type="submit" class="btn btn-ghost btn-sm">🔍 Buscar</button>
        @if($search)<a href="{{ route('admin.index') }}" class="btn btn-ghost btn-sm">✕ Limpar</a>@endif
    </form>
@endsection

@section('content')

<div class="table-card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Usuário</th>
                <th>Cadastro</th>
                <th>Listas</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:.6rem">
                        <div class="user-avatar-sm">{{ mb_substr($user->name, 0, 1) }}</div>
                        <div>
                            <div style="font-weight:600;color:var(--text)">{{ $user->name }}</div>
                            <div style="font-size:.68rem;color:var(--text3)">{{ $user->email }}</div>
                        </div>
                    </div>
                </td>
                <td style="color:var(--text3);white-space:nowrap">
                    {{ $user->created_at->format('d/m/Y') }}
                </td>
                <td>
                    <span style="color:var(--text)">{{ $user->shopping_lists_count }}</span>
                    <span style="color:var(--text3);font-size:.72rem"> / {{ $user->completed_lists_count }} concl.</span>
                </td>
                <td>
                    <div style="display:flex;gap:.3rem;flex-wrap:wrap">
                        @if($user->is_admin)
                            <span class="badge badge-admin">👑 Admin</span>
                        @else
                            <span class="badge badge-user">Usuário</span>
                        @endif
                        <span class="badge badge-active">● Ativo</span>
                    </div>
                </td>
                <td>
                    <div style="display:flex;gap:.3rem;flex-wrap:wrap">
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-ghost btn-sm" title="Ver detalhes">👁</a>

                        @if($user->id !== Auth::id())
                            <form method="POST" action="{{ route('admin.users.toggle-admin', $user) }}" style="display:inline">
                                @csrf
                                <button type="submit" class="btn btn-ghost btn-sm" title="{{ $user->is_admin ? 'Remover admin' : 'Tornar admin' }}">👑</button>
                            </form>

                            <button type="button" class="btn btn-danger btn-sm" title="Excluir"
                                data-name="{{ $user->name }}" data-url="{{ route('admin.users.destroy', $user) }}"
                                onclick="confirmDelete(this.dataset.name, this.dataset.url)">🗑</button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center;padding:2rem;color:var(--text3)">
                    Nenhum usuário encontrado.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($users->hasPages())
    <div class="pagination-wrap">
        <div class="page-info">
            Mostrando {{ $users->firstItem() }}–{{ $users->lastItem() }} de {{ $users->total() }} usuários
        </div>
        <div class="page-links">
            @if($users->onFirstPage())
                <span>‹</span>
            @else
                <a href="{{ $users->previousPageUrl() . ($search ? '&search='.urlencode($search) : '') }}">‹</a>
            @endif

            @foreach($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                @if($page == $users->currentPage())
                    <span class="active-page">{{ $page }}</span>
                @else
                    <a href="{{ $url . ($search ? '&search='.urlencode($search) : '') }}">{{ $page }}</a>
                @endif
            @endforeach

            @if($users->hasMorePages())
                <a href="{{ $users->nextPageUrl() . ($search ? '&search='.urlencode($search) : '') }}">›</a>
            @else
                <span>›</span>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- Modal confirmação exclusão --}}
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
