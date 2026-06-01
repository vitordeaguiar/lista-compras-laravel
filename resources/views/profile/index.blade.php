@extends('layouts.app')
@section('title', 'Perfil & Configurações — Smart Listiq')
@section('page-title', 'Perfil & Configurações')
@section('page-sub', Auth::user()->email)

@push('styles')
<style>
/* ── PROFILE LAYOUT ── */
.prof-layout{display:grid;grid-template-columns:190px 1fr;gap:1.25rem;align-items:start}
.prof-sidebar{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:.45rem;position:sticky;top:1rem}
.prof-tab-btn{display:flex;align-items:center;gap:.45rem;width:100%;background:none;border:none;color:var(--text2);font-family:'Inter',sans-serif;font-size:.79rem;font-weight:500;padding:.48rem .7rem;border-radius:7px;cursor:pointer;text-align:left;transition:all .15s;white-space:nowrap}
.prof-tab-btn:hover{background:var(--bg3);color:var(--text)}
.prof-tab-btn.active{background:var(--adim);color:var(--accent)}
.prof-tab-icon{font-size:.82rem;flex-shrink:0;width:16px;text-align:center}

.prof-card{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:1.35rem;margin-bottom:1rem}
.prof-card:last-child{margin-bottom:0}
.prof-card-title{font-size:.88rem;font-weight:700;color:var(--text);margin-bottom:.18rem}
.prof-card-sub{font-size:.71rem;color:var(--text3);margin-bottom:1rem}

/* Avatar */
.prof-avatar-row{display:flex;align-items:center;gap:1rem;margin-bottom:1.35rem}
.prof-avatar-big{width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,var(--accent) 0%,var(--blue) 100%);display:flex;align-items:center;justify-content:center;font-size:1.65rem;font-weight:700;color:#09090b;flex-shrink:0;text-transform:uppercase}
.prof-avatar-name{font-size:1rem;font-weight:700;color:var(--text)}
.prof-avatar-email{font-size:.74rem;color:var(--text3);margin-top:.1rem}

/* Toggle Switch */
.toggle-row{display:flex;align-items:center;justify-content:space-between;padding:.65rem 0;border-bottom:1px solid var(--border)}
.toggle-row:last-child{border-bottom:none;padding-bottom:0}
.toggle-row:first-child{padding-top:0}
.toggle-label{flex:1;min-width:0}
.toggle-label-title{font-size:.82rem;font-weight:500;color:var(--text)}
.toggle-label-sub{font-size:.68rem;color:var(--text3);margin-top:.08rem}
.toggle-switch{position:relative;width:38px;height:21px;flex-shrink:0;margin-left:.75rem}
.toggle-switch input{opacity:0;width:0;height:0;position:absolute}
.toggle-track{position:absolute;inset:0;background:var(--bg3);border:1px solid var(--border2);border-radius:99px;cursor:pointer;transition:background .2s,border-color .2s}
.toggle-track::after{content:'';position:absolute;left:3px;top:3px;width:13px;height:13px;background:var(--text3);border-radius:50%;transition:transform .2s,background .2s}
.toggle-switch input:checked+.toggle-track{background:var(--accent);border-color:var(--accent)}
.toggle-switch input:checked+.toggle-track::after{transform:translateX(17px);background:#09090b}

/* Accent Colors */
.accent-swatches{display:flex;flex-wrap:wrap;gap:.6rem;margin-top:.75rem}
.accent-swatch{width:30px;height:30px;border-radius:50%;cursor:pointer;border:2.5px solid transparent;transition:all .18s;flex-shrink:0;position:relative}
.accent-swatch:hover{transform:scale(1.15)}
.accent-swatch.active{border-color:var(--text)!important;box-shadow:0 0 0 2px var(--bg2),0 0 0 4px var(--text)}
.accent-swatch.active::after{content:'✓';position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:800;color:#09090b}

/* Theme Cards */
.theme-cards{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-top:.75rem}
.theme-card{border:2px solid var(--border);border-radius:10px;padding:.85rem;cursor:pointer;transition:border-color .18s;position:relative}
.theme-card:hover{border-color:var(--border2)}
.theme-card.active{border-color:var(--accent)}
.theme-card-preview{height:52px;border-radius:7px;margin-bottom:.5rem;overflow:hidden;display:flex;gap:3px;padding:4px;background:var(--bg3)}
.tc-p-sidebar{width:28%;border-radius:4px;background:#111113}
.tc-p-main{flex:1;border-radius:4px;background:#09090b}
.theme-card.light-card .tc-p-sidebar{background:#e2e8f0}
.theme-card.light-card .tc-p-main{background:#f8fafc}
.theme-card-label{font-size:.77rem;font-weight:600;color:var(--text);display:flex;align-items:center;gap:.4rem}
.theme-card input[type="radio"]{position:absolute;opacity:0;pointer-events:none}
.tc-dot{width:14px;height:14px;border-radius:50%;border:2px solid var(--border2);flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.5rem;font-weight:800;transition:all .15s}
.theme-card.active .tc-dot{background:var(--accent);border-color:var(--accent);color:#09090b}

/* Density Options */
.density-options{display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-top:.75rem}
.density-opt{border:1.5px solid var(--border);border-radius:8px;padding:.7rem .9rem;cursor:pointer;transition:all .15s;display:flex;align-items:flex-start;gap:.55rem}
.density-opt:hover{border-color:var(--border2)}
.density-opt.active{border-color:var(--accent);background:var(--adim)}
.density-opt input[type="radio"]{margin-top:.15rem;accent-color:var(--accent);flex-shrink:0}

/* Day Grid */
.day-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:.3rem;margin-top:.75rem}
.day-btn{background:var(--bg3);border:1px solid var(--border);color:var(--text2);font-size:.72rem;font-weight:500;padding:.35rem .2rem;border-radius:6px;cursor:pointer;text-align:center;transition:all .15s;font-family:'Inter',sans-serif}
.day-btn:hover{border-color:var(--accent);color:var(--accent)}
.day-btn.active{background:var(--accent);border-color:var(--accent);color:#09090b;font-weight:700}

/* Sessions */
.session-card{background:var(--bg3);border:1px solid var(--border);border-radius:9px;padding:.85rem 1rem;display:flex;align-items:center;gap:.8rem;margin-bottom:.5rem}
.session-card:last-child{margin-bottom:0}
.session-card.current{border-color:rgba(45,212,191,.25);background:rgba(45,212,191,.04)}
.session-icon{font-size:1.35rem;flex-shrink:0}
.session-info{flex:1;min-width:0}
.session-device{font-size:.81rem;font-weight:600;color:var(--text)}
.session-meta{font-size:.67rem;color:var(--text3);margin-top:.1rem}
.session-badge{font-size:.6rem;background:var(--adim);color:var(--accent);border:1px solid rgba(45,212,191,.25);padding:.07rem .4rem;border-radius:99px;margin-left:.35rem;font-weight:700}

/* Save bar */
.save-bar{display:flex;justify-content:flex-end;margin-top:.5rem}

/* Info note */
.info-note{display:flex;align-items:flex-start;gap:.5rem;background:var(--bluedim);border:1px solid rgba(99,102,241,.2);border-radius:8px;padding:.65rem .85rem;margin-bottom:1rem;font-size:.76rem;color:var(--text2);line-height:1.45}

@media(max-width:768px){
    .prof-layout{grid-template-columns:1fr}
    .prof-sidebar{position:static;display:flex;flex-wrap:wrap;gap:.2rem;padding:.3rem}
    .prof-tab-btn{flex:1;min-width:calc(33% - .1rem);justify-content:center;font-size:.68rem;padding:.38rem .3rem}
    .prof-tab-icon{display:none}
    .theme-cards,.density-options{grid-template-columns:1fr 1fr}
}
</style>
@endpush

@section('content')
<div class="prof-layout">

    {{-- ── SIDEBAR ── --}}
    <aside class="prof-sidebar">
        <button class="prof-tab-btn active" data-tab="tab-perfil">
            <span class="prof-tab-icon">✏️</span> Meu Perfil
        </button>
        <button class="prof-tab-btn" data-tab="tab-seguranca">
            <span class="prof-tab-icon">🔒</span> Segurança
        </button>
        <button class="prof-tab-btn" data-tab="tab-aparencia">
            <span class="prof-tab-icon">🎨</span> Aparência
        </button>
        <button class="prof-tab-btn" data-tab="tab-notificacoes">
            <span class="prof-tab-icon">🔔</span> Notificações
        </button>
        <button class="prof-tab-btn" data-tab="tab-financeiro">
            <span class="prof-tab-icon">💰</span> Financeiro
        </button>
        <button class="prof-tab-btn" data-tab="tab-sessoes">
            <span class="prof-tab-icon">💻</span> Sessões
        </button>
    </aside>

    {{-- ── CONTENT ── --}}
    <div class="prof-body">

        {{-- ───────────────────────── TAB: PERFIL ───────────────────────── --}}
        <div id="tab-perfil" class="prof-panel">

            <div class="prof-card">
                <div class="prof-avatar-row">
                    <div class="prof-avatar-big">{{ mb_substr($user->name, 0, 1) }}</div>
                    <div>
                        <div class="prof-avatar-name">{{ $user->name }}</div>
                        <div class="prof-avatar-email">{{ $user->email }}</div>
                    </div>
                </div>

                <div class="prof-card-title">Informações Pessoais</div>
                <div class="prof-card-sub">Atualize seu nome e endereço de e-mail.</div>

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label>Nome completo</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>E-mail</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group" id="current-password-group" style="display:none">
                        <label>Senha atual <span style="color:var(--danger)">*</span></label>
                        <input type="password" name="current_password" autocomplete="current-password" placeholder="Obrigatória para alterar o e-mail">
                        @error('current_password')<div class="field-error">{{ $message }}</div>@enderror
                        <div style="font-size:.68rem;color:var(--text3);margin-top:.25rem">Necessária apenas ao alterar o e-mail.</div>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Salvar Perfil</button>
                </form>
            </div>

            <div class="prof-card" style="border-color:rgba(239,68,68,.2);background:rgba(239,68,68,.03)">
                <div class="prof-card-title" style="color:var(--danger)">Zona de Perigo</div>
                <div class="prof-card-sub">Ações permanentes e irreversíveis.</div>
                <div class="info-note" style="background:rgba(239,68,68,.05);border-color:rgba(239,68,68,.2)">
                    ⚠️ A exclusão de conta não está disponível nesta versão.
                </div>
            </div>
        </div>

        {{-- ─────────────────────── TAB: SEGURANÇA ─────────────────────── --}}
        <div id="tab-seguranca" class="prof-panel" hidden>

            <div class="prof-card">
                <div class="prof-card-title">Alterar Senha</div>
                <div class="prof-card-sub">Use uma senha forte com pelo menos 8 caracteres.</div>

                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label>Senha atual</label>
                        <input type="password" name="current_password" autocomplete="current-password" required>
                        @error('current_password')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Nova senha</label>
                        <input type="password" name="password" autocomplete="new-password" required>
                        @error('password')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Confirmar nova senha</label>
                        <input type="password" name="password_confirmation" autocomplete="new-password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">🔒 Alterar Senha</button>
                </form>
            </div>

            <div class="prof-card">
                <div class="prof-card-title">Autenticação em Dois Fatores</div>
                <div class="prof-card-sub">Proteção extra para sua conta.</div>
                <div class="info-note">
                    🔐 A autenticação em dois fatores (2FA) não está disponível nesta versão.
                </div>
            </div>
        </div>

        {{-- ── FORMULÁRIO COMBINADO: APARÊNCIA + NOTIFICAÇÕES + FINANCEIRO ── --}}
        <form method="POST" action="{{ route('profile.settings') }}" id="settings-form">
            @csrf
            @method('PUT')

            {{-- ─────────────────────── TAB: APARÊNCIA ─────────────────────── --}}
            <div id="tab-aparencia" class="prof-panel" hidden>

                <div class="prof-card">
                    <div class="prof-card-title">Tema</div>
                    <div class="prof-card-sub">Escolha entre o tema escuro ou claro da interface.</div>
                    <div class="theme-cards">
                        <label class="theme-card {{ $settings->theme === 'dark' ? 'active' : '' }}" id="tc-dark">
                            <input type="radio" name="theme" value="dark"
                                {{ $settings->theme === 'dark' ? 'checked' : '' }}>
                            <div class="theme-card-preview">
                                <div class="tc-p-sidebar"></div>
                                <div class="tc-p-main"></div>
                            </div>
                            <div class="theme-card-label">
                                <span class="tc-dot">{{ $settings->theme === 'dark' ? '✓' : '' }}</span>
                                🌙 Escuro
                            </div>
                        </label>
                        <label class="theme-card light-card {{ $settings->theme === 'light' ? 'active' : '' }}" id="tc-light">
                            <input type="radio" name="theme" value="light"
                                {{ $settings->theme === 'light' ? 'checked' : '' }}>
                            <div class="theme-card-preview">
                                <div class="tc-p-sidebar"></div>
                                <div class="tc-p-main"></div>
                            </div>
                            <div class="theme-card-label">
                                <span class="tc-dot">{{ $settings->theme === 'light' ? '✓' : '' }}</span>
                                ☀️ Claro
                            </div>
                        </label>
                    </div>
                </div>

                <div class="prof-card">
                    <div class="prof-card-title">Cor de Destaque</div>
                    <div class="prof-card-sub">Personaliza botões, ícones ativos e elementos de destaque.</div>
                    <input type="hidden" name="accent_color" id="accent-input" value="{{ $settings->accent_color }}">
                    <div class="accent-swatches">
                        @foreach([
                            '#2dd4bf','#6366f1','#10b981','#38bdf8',
                            '#93c5fd','#818cf8','#c084fc','#fb7185',
                            '#fbbf24','#f97316'
                        ] as $color)
                        <div class="accent-swatch {{ $settings->accent_color === $color ? 'active' : '' }}"
                             style="background:{{ $color }};border-color:{{ $color }}"
                             data-color="{{ $color }}"
                             onclick="pickAccent('{{ $color }}',this)"
                             title="{{ $color }}"></div>
                        @endforeach
                    </div>
                </div>

                <div class="prof-card">
                    <div class="prof-card-title">Densidade de Layout</div>
                    <div class="prof-card-sub">Ajusta o espaçamento dos elementos na interface.</div>
                    <div class="density-options">
                        <label class="density-opt {{ $settings->layout_density === 'comfortable' ? 'active' : '' }}">
                            <input type="radio" name="layout_density" value="comfortable"
                                {{ $settings->layout_density === 'comfortable' ? 'checked' : '' }}>
                            <div>
                                <div style="font-size:.8rem;font-weight:600;color:var(--text)">Confortável</div>
                                <div style="font-size:.67rem;color:var(--text3);margin-top:.1rem">Mais espaçamento</div>
                            </div>
                        </label>
                        <label class="density-opt {{ $settings->layout_density === 'compact' ? 'active' : '' }}">
                            <input type="radio" name="layout_density" value="compact"
                                {{ $settings->layout_density === 'compact' ? 'checked' : '' }}>
                            <div>
                                <div style="font-size:.8rem;font-weight:600;color:var(--text)">Compacto</div>
                                <div style="font-size:.67rem;color:var(--text3);margin-top:.1rem">Mais conteúdo</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="save-bar">
                    <button type="submit" class="btn btn-primary">💾 Salvar Aparência</button>
                </div>
            </div>

            {{-- ──────────────────── TAB: NOTIFICAÇÕES ──────────────────── --}}
            <div id="tab-notificacoes" class="prof-panel" hidden>

                <div class="prof-card">
                    <div class="prof-card-title">Alertas</div>
                    <div class="prof-card-sub">Configure quando e como receber lembretes automáticos.</div>

                    <div class="toggle-row">
                        <div class="toggle-label">
                            <div class="toggle-label-title">Alerta de orçamento</div>
                            <div class="toggle-label-sub">Avisa quando os gastos mensais superarem o limite definido</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="notify_budget_alert" value="1"
                                {{ $settings->notify_budget_alert ? 'checked' : '' }}>
                            <span class="toggle-track"></span>
                        </label>
                    </div>

                    <div class="toggle-row">
                        <div class="toggle-label">
                            <div class="toggle-label-title">Resumo mensal</div>
                            <div class="toggle-label-sub">Receba um resumo financeiro ao final de cada mês</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="notify_monthly_summary" value="1"
                                {{ $settings->notify_monthly_summary ? 'checked' : '' }}>
                            <span class="toggle-track"></span>
                        </label>
                    </div>

                    <div class="toggle-row">
                        <div class="toggle-label">
                            <div class="toggle-label-title">Lembrete de lista</div>
                            <div class="toggle-label-sub">Lembrete quando uma lista estiver aberta por muitos dias</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="notify_list_reminder" value="1"
                                {{ $settings->notify_list_reminder ? 'checked' : '' }}>
                            <span class="toggle-track"></span>
                        </label>
                    </div>

                    <div class="toggle-row">
                        <div class="toggle-label">
                            <div class="toggle-label-title">Abertura de novo mês</div>
                            <div class="toggle-label-sub">Lembrete para planejar o mês seguinte antes do vencimento</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="notify_new_month" value="1"
                                {{ $settings->notify_new_month ? 'checked' : '' }}>
                            <span class="toggle-track"></span>
                        </label>
                    </div>
                </div>

                <div class="prof-card">
                    <div class="prof-card-title">Canais de Envio</div>
                    <div class="prof-card-sub">Por onde deseja receber as notificações.</div>

                    <div class="toggle-row">
                        <div class="toggle-label">
                            <div class="toggle-label-title">📧 E-mail</div>
                            <div class="toggle-label-sub">Notificações para {{ $user->email }}</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="notify_email" value="1"
                                {{ $settings->notify_email ? 'checked' : '' }}>
                            <span class="toggle-track"></span>
                        </label>
                    </div>

                    <div class="toggle-row">
                        <div class="toggle-label">
                            <div class="toggle-label-title">🔔 Push</div>
                            <div class="toggle-label-sub">Notificações push no navegador (requer permissão)</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="notify_push" value="1"
                                {{ $settings->notify_push ? 'checked' : '' }}>
                            <span class="toggle-track"></span>
                        </label>
                    </div>
                </div>

                <div class="prof-card">
                    <div class="prof-card-title">Antecedência de Vencimentos</div>
                    <div class="prof-card-sub">Quantos dias antes do vencimento de um gasto fixo você quer ser notificado.</div>
                    <div class="form-group" style="margin-bottom:0;max-width:180px">
                        <label>Dias de antecedência</label>
                        <input type="number" name="notify_due_days"
                               value="{{ $settings->notify_due_days }}" min="0" max="30">
                    </div>
                </div>

                <div class="save-bar">
                    <button type="submit" class="btn btn-primary">💾 Salvar Notificações</button>
                </div>
            </div>

            {{-- ──────────────────── TAB: FINANCEIRO ──────────────────── --}}
            <div id="tab-financeiro" class="prof-panel" hidden>

                <div class="prof-card">
                    <div class="prof-card-title">Dia de Recebimento</div>
                    <div class="prof-card-sub">O dia do mês em que você costuma receber seu salário ou renda principal.</div>
                    <input type="hidden" name="salary_day" id="salary-day-input" value="{{ $settings->salary_day }}">
                    <div class="day-grid">
                        @for($d = 1; $d <= 31; $d++)
                        <button type="button"
                                class="day-btn {{ (int)$settings->salary_day === $d ? 'active' : '' }}"
                                onclick="pickDay({{ $d }}, this)">{{ $d }}</button>
                        @endfor
                    </div>
                </div>

                <div class="prof-card">
                    <div class="prof-card-title">Orçamento Mensal</div>
                    <div class="prof-card-sub">Limite máximo de gastos por mês. Deixe em 0 para desativar.</div>
                    <div class="form-group" style="margin-bottom:0;max-width:240px">
                        <label>Orçamento (R$)</label>
                        <input type="text" inputmode="decimal" name="monthly_budget"
                               value="{{ $settings->monthly_budget ? number_format($settings->monthly_budget, 2, ',', '.') : '' }}"
                               placeholder="0,00" class="money-input profile-money">
                    </div>
                </div>

                <div class="prof-card">
                    <div class="prof-card-title">Meta de Poupança</div>
                    <div class="prof-card-sub">Valor mensal que você deseja poupar ou investir.</div>
                    <div class="form-group" style="margin-bottom:0;max-width:240px">
                        <label>Meta (R$)</label>
                        <input type="text" inputmode="decimal" name="monthly_savings_goal"
                               value="{{ $settings->monthly_savings_goal ? number_format($settings->monthly_savings_goal, 2, ',', '.') : '' }}"
                               placeholder="0,00" class="money-input profile-money">
                    </div>
                </div>

                <div class="prof-card">
                    <div class="prof-card-title">Automações de Mês Novo</div>
                    <div class="prof-card-sub">O que copiar automaticamente ao abrir um novo mês financeiro.</div>

                    <div class="toggle-row">
                        <div class="toggle-label">
                            <div class="toggle-label-title">Copiar gastos fixos</div>
                            <div class="toggle-label-sub">Cria os pagamentos fixos do mês anterior no mês novo</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="auto_copy_fixed" value="1"
                                {{ $settings->auto_copy_fixed ? 'checked' : '' }}>
                            <span class="toggle-track"></span>
                        </label>
                    </div>

                    <div class="toggle-row">
                        <div class="toggle-label">
                            <div class="toggle-label-title">Copiar entradas de renda</div>
                            <div class="toggle-label-sub">Copia salários e receitas recorrentes para o novo mês</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="auto_copy_incomes" value="1"
                                {{ $settings->auto_copy_incomes ? 'checked' : '' }}>
                            <span class="toggle-track"></span>
                        </label>
                    </div>

                    <div class="toggle-row">
                        <div class="toggle-label">
                            <div class="toggle-label-title">Manter investimentos</div>
                            <div class="toggle-label-sub">Mantém os investimentos ativos visíveis no novo mês</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="auto_keep_investments" value="1"
                                {{ $settings->auto_keep_investments ? 'checked' : '' }}>
                            <span class="toggle-track"></span>
                        </label>
                    </div>
                </div>

                <div class="save-bar">
                    <button type="submit" class="btn btn-primary">💾 Salvar Configurações</button>
                </div>
            </div>
        </form>
        {{-- FIM do formulário combinado --}}

        {{-- ──────────────────────── TAB: SESSÕES ──────────────────────── --}}
        <div id="tab-sessoes" class="prof-panel" hidden>

            <div class="prof-card">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.2rem">
                    <div>
                        <div class="prof-card-title">Sessões Ativas</div>
                        <div class="prof-card-sub" style="margin-bottom:0">Dispositivos conectados à sua conta.</div>
                    </div>
                    @if($sessions->where('is_current', false)->count() > 0)
                    <form method="POST" action="{{ route('profile.sessions.destroy-all') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger"
                                onclick="return confirm('Encerrar todas as outras sessões ativas?')">
                            ✕ Encerrar Outras
                        </button>
                    </form>
                    @endif
                </div>

                @if($sessions->isEmpty())
                <div style="text-align:center;padding:2.5rem 1rem;color:var(--text3)">
                    <div style="font-size:2rem;margin-bottom:.5rem">💻</div>
                    <div style="font-size:.82rem">Nenhuma sessão encontrada.</div>
                    <div style="font-size:.7rem;margin-top:.3rem">O rastreamento de sessões requer o driver "database".</div>
                </div>
                @else
                <div style="margin-top:1rem">
                    @foreach($sessions as $sess)
                    @php
                        $ua       = $sess->user_agent ?? '';
                        $isMobile = str_contains($ua, 'Mobile') || str_contains($ua, 'Android');
                        $icon     = $isMobile ? '📱' : (
                                        str_contains($ua, 'Chrome')  ? '🌐' : (
                                        str_contains($ua, 'Firefox') ? '🦊' : (
                                        str_contains($ua, 'Safari')  ? '🧭' : '💻')));
                        $browser  = $isMobile ? 'Dispositivo Móvel' : (
                                        str_contains($ua, 'Chrome')  ? 'Google Chrome' : (
                                        str_contains($ua, 'Firefox') ? 'Mozilla Firefox' : (
                                        str_contains($ua, 'Safari')  ? 'Safari' : 'Navegador')));
                    @endphp
                    <div class="session-card {{ $sess->is_current ? 'current' : '' }}">
                        <div class="session-icon">{{ $icon }}</div>
                        <div class="session-info">
                            <div class="session-device">
                                {{ $browser }}
                                @if($sess->is_current)
                                    <span class="session-badge">Sessão atual</span>
                                @endif
                            </div>
                            <div class="session-meta">
                                🌐 {{ $sess->ip_address ?? 'IP desconhecido' }}
                                &nbsp;·&nbsp;
                                🕐 Ativo {{ $sess->last_active->diffForHumans() }}
                            </div>
                        </div>
                        @if(!$sess->is_current)
                        <form method="POST" action="{{ route('profile.session.destroy') }}">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="session_id" value="{{ $sess->id }}">
                            <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Encerrar esta sessão?')">Revogar</button>
                        </form>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

    </div>{{-- /.prof-body --}}
</div>{{-- /.prof-layout --}}

<script nonce="{{ $cspNonce }}">
// ── SENHA OBRIGATÓRIA AO ALTERAR E-MAIL ─────────────────────────────────────
(function () {
    const emailInput   = document.querySelector('input[name="email"]');
    const pwdGroup     = document.getElementById('current-password-group');
    const pwdInput     = pwdGroup ? pwdGroup.querySelector('input') : null;
    if (!emailInput || !pwdGroup) return;
    const originalEmail = emailInput.defaultValue;
    @if($errors->has('current_password'))
    pwdGroup.style.display = 'block';
    @endif
    emailInput.addEventListener('input', function () {
        const changed = this.value.trim() !== originalEmail;
        pwdGroup.style.display = changed ? 'block' : 'none';
        if (pwdInput) pwdInput.required = changed;
    });
})();

// ── TAB NAVIGATION ──────────────────────────────────────────────────────────
const tabBtns   = document.querySelectorAll('.prof-tab-btn');
const tabPanels = document.querySelectorAll('.prof-panel');

function switchTab(id) {
    tabPanels.forEach(p => p.hidden = true);
    tabBtns.forEach(b => b.classList.remove('active'));
    const panel = document.getElementById(id);
    const btn   = document.querySelector(`.prof-tab-btn[data-tab="${id}"]`);
    if (panel) panel.hidden = false;
    if (btn)   btn.classList.add('active');
    sessionStorage.setItem('profileTab', id);
    if (history.replaceState) history.replaceState(null, '', '#' + id);
}

tabBtns.forEach(btn => btn.addEventListener('click', () => switchTab(btn.dataset.tab)));

// Restore active tab from hash or sessionStorage
const savedTab = location.hash.slice(1) || sessionStorage.getItem('profileTab') || 'tab-perfil';
if (document.getElementById(savedTab)) switchTab(savedTab);
else switchTab('tab-perfil');

// ── THEME TOGGLE ─────────────────────────────────────────────────────────────
document.querySelectorAll('input[name="theme"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const isDark = this.value === 'dark';
        document.documentElement.classList.toggle('light', !isDark);
        localStorage.setItem('sl_theme', this.value);
        // Update card states
        document.getElementById('tc-dark').classList.toggle('active', isDark);
        document.getElementById('tc-light').classList.toggle('active', !isDark);
        document.querySelector('#tc-dark .tc-dot').textContent  = isDark ? '✓' : '';
        document.querySelector('#tc-light .tc-dot').textContent = !isDark ? '✓' : '';
    });
});

// ── ACCENT PICKER ─────────────────────────────────────────────────────────────
const accentMap = {
    '#2dd4bf':{a2:'#14b8a6',dim:'rgba(45,212,191,.1)'},
    '#6366f1':{a2:'#4f46e5',dim:'rgba(99,102,241,.1)'},
    '#10b981':{a2:'#059669',dim:'rgba(16,185,129,.1)'},
    '#38bdf8':{a2:'#0ea5e9',dim:'rgba(56,189,248,.1)'},
    '#93c5fd':{a2:'#60a5fa',dim:'rgba(147,197,253,.1)'},
    '#818cf8':{a2:'#6366f1',dim:'rgba(129,140,248,.1)'},
    '#c084fc':{a2:'#a855f7',dim:'rgba(192,132,252,.1)'},
    '#fb7185':{a2:'#f43f5e',dim:'rgba(251,113,133,.1)'},
    '#fbbf24':{a2:'#f59e0b',dim:'rgba(251,191,36,.1)'},
    '#f97316':{a2:'#ea580c',dim:'rgba(249,115,22,.1)'},
};

function pickAccent(color, el) {
    document.querySelectorAll('.accent-swatch').forEach(s => s.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('accent-input').value = color;
    const m = accentMap[color] || {a2:color, dim:'rgba(45,212,191,.1)'};
    document.documentElement.style.setProperty('--accent', color);
    document.documentElement.style.setProperty('--accent2', m.a2);
    document.documentElement.style.setProperty('--adim', m.dim);
    localStorage.setItem('sl_accent', color);
}

// ── DAY PICKER ───────────────────────────────────────────────────────────────
function pickDay(day, el) {
    document.querySelectorAll('.day-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('salary-day-input').value = day;
}

// ── DENSITY TOGGLE ───────────────────────────────────────────────────────────
document.querySelectorAll('input[name="layout_density"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.density-opt').forEach(o => o.classList.remove('active'));
        this.closest('.density-opt').classList.add('active');
    });
});

// ── MÁSCARA MONETÁRIA ────────────────────────────────────────────────────────
function maskMoney(input) {
    input.addEventListener('input', function() {
        let v = this.value.replace(/\D/g, '');
        if (!v) { this.value = ''; return; }
        v = (parseInt(v, 10) / 100).toFixed(2);
        this.value = v.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    });
    input.addEventListener('blur', function() {
        if (this.value && !this.value.includes(',')) {
            this.value = this.value + ',00';
        }
    });
}

document.querySelectorAll('.money-input').forEach(function(inp) { maskMoney(inp); });

const settingsForm = document.getElementById('settings-form');
if (settingsForm) {
    settingsForm.addEventListener('submit', function() {
        this.querySelectorAll('.money-input').forEach(function(inp) {
            inp.value = inp.value.replace(/\./g, '').replace(',', '.');
        });
    });
}
</script>
@endsection
