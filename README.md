# Smart Listiq

Aplicação web para gerenciamento de listas de compras e finanças pessoais, desenvolvida com Laravel 11 e voltada para o mercado brasileiro.

---

## Funcionalidades

### Listas de Compras
- Criação de listas com nome, data e observações
- Adição, edição e exclusão de itens com quantidade, unidade e preço
- Marcação de itens como comprados
- Conclusão de lista com cálculo automático do total e aplicação de desconto
- Reabertura de listas concluídas

### Financeiro
- **Entradas**: registro de receitas mensais
- **Custos Fixos**: despesas recorrentes com vencimento configurável e ícones por categoria; geração automática de parcelas mensais e controle de pagamento
- **Custos Variáveis**: despesas avulsas categorizadas
- **Investimentos**: carteiras com aporte inicial e lançamentos mensais
- Abertura de mês inteligente — copia custos fixos e entradas do mês anterior

### Dashboard
- Resumo financeiro mensal: saldo, orçamento e despesas
- Contas com vencimento nos próximos 3 dias
- Feed de atividade recente
- Top 3 despesas por categoria
- Gráficos dos últimos 6 meses: receitas vs. despesas e gastos no mercado (visão mensal e semanal)

### Perfil e Configurações
- Edição de nome, e-mail e senha
- Tema escuro e cor de destaque personalizáveis
- Metas de orçamento e poupança mensais
- Preferências de notificações
- Gerenciamento de sessões ativas

### Administração
- Painel com estatísticas gerais
- Gestão de usuários: visualizar, excluir, conceder/revogar papel de administrador

---

## Tech Stack

| Camada | Tecnologia |
|--------|-----------|
| Backend | Laravel 11 (PHP 8.2+) |
| Frontend | Blade, HTML, CSS, JavaScript |
| Banco de dados | MySQL |
| Autenticação | Laravel Auth + verificação de e-mail por código |
| Servidor de e-mail | SMTP (Titan Mail) |

---

## Instalação

### Pré-requisitos
- PHP 8.2+
- Composer
- MySQL

### Passos

```bash
# 1. Clonar o repositório
git clone https://github.com/seu-usuario/lista-compras-full.git
cd lista-compras-full

# 2. Instalar dependências
composer install

# 3. Copiar e configurar o ambiente
cp .env.example .env

# 4. Gerar a chave da aplicação
php artisan key:generate

# 5. Criar o banco de dados e rodar as migrations
php artisan migrate

# 6. (Opcional) Popular com dados de teste
php artisan db:seed

# 7. Iniciar o servidor
php artisan serve
```

Acesse em: `http://localhost:8000`

---

## Configuração do Ambiente

Copie `.env.example` para `.env` e preencha as variáveis obrigatórias:

```env
APP_NAME="Smart Listiq"
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lista_compras
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=seu-servidor-smtp
MAIL_PORT=465
MAIL_USERNAME=seu@email.com
MAIL_PASSWORD=sua-senha
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=seu@email.com
MAIL_FROM_NAME="Smart Listiq"
```

---

## Estrutura de Pastas

```
├── app/
│   ├── Http/
│   │   ├── Controllers/   # 9 controllers (Auth, Dashboard, Finance, Lists, Admin…)
│   │   └── Middleware/    # AdminMiddleware, SecurityHeaders
│   └── Models/            # 12 modelos Eloquent
├── database/
│   ├── migrations/        # 17 migrations
│   └── seeders/
├── resources/views/       # Blade templates organizados por feature
├── routes/
│   └── web.php            # Todas as rotas da aplicação
└── config/                # Timezone: America/Sao_Paulo | Locale: pt_BR
```

---

## Segurança

- Proteção CSRF (padrão Laravel)
- Rate limiting no login: 5 tentativas → bloqueio de IP por 15 minutos
- Verificação de e-mail via código de 6 dígitos (validade: 15 minutos)
- Hashing de senhas com bcrypt
- Headers de segurança (X-Frame-Options, X-Content-Type-Options, etc.)
- Isolamento total de dados por usuário

---

## Licença

Este projeto é de uso privado. Todos os direitos reservados.

---

<p align="center">
  <img src="https://img.shields.io/badge/Criado%20por-VAF%20Solutions-blue?style=flat-square" />
  <img src="https://img.shields.io/badge/Ano-2026-lightgrey?style=flat-square" />
</p>
