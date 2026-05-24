# Lista Compras Full — Documentação do Projeto

## Visão Geral

Aplicação web full-stack de gestão de lista de compras e finanças pessoais. Desenvolvida em Laravel 11 com Blade templates e MySQL. Interface em português do Brasil, com foco em usabilidade e segurança.

**Usuário admin principal:** vitordeaguiar0@gmail.com

---

## Stack Técnica

| Camada | Tecnologia |
|--------|-----------|
| Framework | Laravel 11 |
| Linguagem backend | PHP 8.2+ |
| Frontend | Blade Templates + CSS + JS vanilla |
| Banco de dados | MySQL (principal) |
| Autenticação | Laravel Auth + código de verificação por e-mail (6 dígitos) |
| Sessões | Driver database |
| Cache | Driver database |
| E-mail | SMTP (Titan Mail recomendado) |
| Locale | pt_BR / America/Sao_Paulo |

---

## Estrutura de Diretórios

```
app/
  Http/
    Controllers/     # 8 controllers (Auth, Dashboard, Finance, ShoppingList, ShoppingItem, History, Profile, Admin)
    Middleware/      # AdminMiddleware, SecurityHeaders
  Models/            # 12 Eloquent models
  Providers/         # AppServiceProvider (rate limiting)
config/              # app, database, auth, session, mail, cache, logging
database/
  migrations/        # 17 migrações
  seeders/           # UserSeeder, AdminSeeder (vitordeaguiar0@gmail.com)
public/              # Entry point + utilities (setup_mail.php, migrate.php, clearcache.php)
resources/views/     # Blade templates organizados por feature
routes/web.php       # Todas as rotas
```

---

## Módulos da Aplicação

### 1. Autenticação
- Registro com envio de código de 6 dígitos por e-mail (expira em 15 min)
- Login com rate limiting: 5 tentativas/min → bloqueio por 15 min
- Rate limiting no envio de código: 3 por min por IP
- Cookies de sessão: HTTP-only, SameSite=strict

### 2. Lista de Compras (`/listas`)
- Criação de listas com data
- Itens com nome, unidade, quantidade e preço
- Marcação individual de itens como comprado
- Conclusão da lista com total automático e desconto opcional
- Reabertura de lista concluída
- Histórico completo (`/historico`) com filtros de data e busca

### 3. Financeiro (`/financeiro`)
- **Entradas:** renda mensal
- **Custos Fixos:** recorrentes com ícone e dia de vencimento; pagamentos mensais gerados automaticamente
- **Custos Variáveis:** despesas avulsas por categoria
- **Investimentos:** portfólios por categoria (poupança, renda fixa, tesouro, ações/FII, cripto, outros) com aportes mensais
- **Abrir Mês:** copia custos fixos e entradas do mês anterior

### 4. Dashboard (`/dashboard`)
- Resumo financeiro mensal
- Gráficos de receita vs. despesa (6 meses)
- Gráficos de gastos no supermercado (semanal/mensal)
- Alertas de contas a vencer nos próximos 3 dias
- Feed de atividade recente

### 5. Perfil (`/perfil`)
- Edição de nome, e-mail e senha
- Configurações: tema (dark/light), cor de acento, dia de salário, orçamento mensal, meta de poupança, notificações, densidade de layout
- Gerenciamento de sessões ativas

### 6. Admin (`/admin`)
- Dashboard com estatísticas de usuários
- Detalhes por usuário
- Deletar usuário (cascata)
- Toggle de status admin

---

## Controllers

| Controller | Responsabilidade |
|-----------|-----------------|
| `AuthController` | Login, registro e verificação por e-mail |
| `DashboardController` | Resumo financeiro e gráficos |
| `ShoppingListController` | CRUD de listas de compras |
| `ShoppingItemController` | CRUD de itens de lista |
| `HistoryController` | Histórico de listas concluídas |
| `FinanceController` | Toda a gestão financeira |
| `ProfileController` | Perfil e configurações do usuário |
| `AdminController` | Administração de usuários |

---

## Models

| Model | Relacionamentos |
|-------|----------------|
| `User` | HasMany: ShoppingLists, FinancialData, Settings |
| `ShoppingList` | BelongsTo: User; HasMany: ShoppingItems |
| `ShoppingItem` | BelongsTo: ShoppingList |
| `EmailVerification` | — (expiração em 15 min) |
| `FinancialMonth` | BelongsTo: User |
| `FinancialIncome` | BelongsTo: User |
| `FinancialFixedCost` | BelongsTo: User; HasMany: Payments |
| `FinancialFixedPayment` | BelongsTo: FixedCost, User |
| `FinancialVariableCost` | BelongsTo: User |
| `FinancialInvestment` | BelongsTo: User; HasMany: Entries |
| `FinancialInvestmentEntry` | BelongsTo: Investment, User |
| `UserSetting` | BelongsTo: User (1:1) |

---

## Banco de Dados — Tabelas Principais

| Tabela | Finalidade |
|--------|-----------|
| `users` | Contas (is_admin flag) |
| `sessions` | Sessões (driver database) |
| `email_verifications` | Código de verificação |
| `shopping_lists` | Listas de compras |
| `shopping_items` | Itens das listas |
| `financial_months` | Controle de meses |
| `financial_incomes` | Entradas mensais |
| `financial_fixed_costs` | Custos fixos recorrentes |
| `financial_fixed_payments` | Instâncias mensais de custos fixos |
| `financial_variable_costs` | Custos variáveis |
| `financial_investments` | Portfólios de investimento |
| `financial_investment_entries` | Aportes mensais |
| `user_settings` | Preferências do usuário |
| `cache` / `cache_locks` | Cache da aplicação |

---

## Rotas Principais

```
GET  /login                         # Formulário de login
POST /login                         # Processar login
GET  /register                      # Formulário de cadastro
POST /register/send-code            # Enviar código de verificação
POST /register/verify               # Verificar código
POST /register/complete             # Completar cadastro

GET  /dashboard                     # Dashboard principal
GET  /listas                        # Listar listas de compras
POST /listas                        # Criar lista
GET  /listas/{list}                 # Ver lista
PATCH /listas/{list}/concluir       # Concluir lista
PATCH /listas/{list}/reabrir        # Reabrir lista
DELETE /listas/{list}               # Deletar lista

GET  /historico                     # Histórico de listas
GET  /financeiro                    # Dashboard financeiro
POST /financeiro/fixos              # Adicionar custo fixo
POST /financeiro/variaveis          # Adicionar custo variável
POST /financeiro/entradas           # Adicionar renda
POST /financeiro/investimentos      # Adicionar investimento
POST /financeiro/abrir-mes          # Abrir novo mês

GET  /perfil                        # Perfil e configurações
GET  /admin                         # Painel admin
```

---

## Segurança

- CSRF: proteção Laravel padrão
- Headers: X-Frame-Options, CSP, HSTS, X-Content-Type-Options, Permissions-Policy
- Senhas: bcrypt
- Rate limiting: login (5/min), código de registro (3/min), geral (60/min)
- Sessões: HTTP-only, SameSite=strict, cookie seguro

---

## Comandos Úteis

```bash
# Desenvolvimento
php artisan serve                  # Servidor local (porta 8000)
php artisan migrate                # Executar migrações
php artisan migrate:fresh --seed   # Recriar banco e popular com seeders
php artisan db:seed                # Executar seeders
php artisan key:generate           # Gerar APP_KEY
php artisan cache:clear            # Limpar cache
php artisan route:list             # Listar rotas
php artisan tinker                 # REPL interativo

# Testes
./vendor/bin/phpunit               # Executar testes (SQLite in-memory)

# Dependências
composer install                   # Instalar dependências
composer update                    # Atualizar dependências
```

---

## Ambiente e Configuração

- Copiar `.env.example` para `.env` e configurar:
  - `DB_*` — credenciais MySQL
  - `MAIL_*` — configuração SMTP (Titan Mail)
  - `APP_URL` — URL da aplicação
  - `APP_KEY` — gerado com `php artisan key:generate`
- Timezone: `America/Sao_Paulo`
- Locale: `pt_BR`

---

## Convenções do Projeto

- **Git:** commitar sempre direto na `main`, sem criar branches
- **Idioma:** interface e código em português do Brasil
- **Banco:** MySQL em produção, SQLite in-memory em testes
- **Frontend:** sem framework JS — apenas vanilla JS e Blade
- **Autenticação:** fluxo próprio com verificação por e-mail, sem pacotes externos (ex: Breeze/Jetstream)
