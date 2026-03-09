# PDV Artigos Religiosos

Sistema de Ponto de Venda em PHP (MVC), MySQL e Tailwind CSS.

## Requisitos

- PHP 8.0+
- MySQL 8+
- Extensões PHP: PDO, pdo_mysql, json, mbstring, fileinfo, session

## Configuração

### 1. Variáveis de ambiente

Copie o arquivo de exemplo e ajuste os valores:

```bash
cp .env.example .env
```

Edite `.env` com:

- **APP_ENV**: `development` ou `production` (em produção não são exibidos detalhes de erros)
- **APP_URL**: URL base do sistema (ex: `http://localhost/PDV/`)
- **DB_HOST**, **DB_NAME**, **DB_USER**, **DB_PASS**: conexão MySQL
- **SESSION_SECURE**: `1` se usar HTTPS, `0` em local
- **RATE_LIMIT_LOGIN_ATTEMPTS** / **RATE_LIMIT_LOGIN_WINDOW**: limite de tentativas de login (padrão 5 em 15 min)

### 2. Composer (opcional)

Para usar `vlucas/phpdotenv` para carregar o `.env`:

```bash
composer install
```

Sem Composer, o sistema usa um carregador simples de `.env` em `config/env.php`.

### 3. Banco de dados

Crie o banco e execute as migrations na ordem:

- Opção A: executar manualmente os arquivos em `database/` na ordem (schema.sql, schema_v2.sql … schema_v15_*.sql).
- Opção B: executar apenas a migration de segurança/auditoria:
  - Pelo navegador: acesse `http://localhost/PDV/run_migration_v12.php`
  - Ou no terminal: `php run_migration_v12.php`
- Opção C: usar o runner geral: `php database/run_migrations.php`

### 4. Rodar localmente

- Servidor web com document root na pasta do projeto (ex: `http://localhost/PDV/`) e `index.php` como entrada.
- Ou, com PHP embutido: `php -S localhost:8000` na raiz do projeto e acesse `http://localhost:8000`.

## Estrutura do projeto

```
/config          Configuração (database, env, helpers, rotas, permissões)
/controllers     Controllers (Auth, Dashboard, Product, Sale, etc.)
/models          Models (User, Product, Sale, AuditLog, etc.)
/views           Views (layouts, auth, dashboard, products, etc.)
/database        Migrations SQL (schema*.sql) e runner
/public          Assets (css, js, uploads)
/storage         Logs e rate limit (criado automaticamente)
```

## Design system (componentes UI)

Kit de componentes para manter consistência visual. Use as classes abaixo nas views.

| Componente | Classes | Uso |
|------------|---------|-----|
| **Botões** | `.btn`, `.btn-primary`, `.btn-secondary`, `.btn-danger`, `.btn-success`, `.btn-outline-primary` | Ações; variantes `-sm`, `-lg` quando suportado |
| **Inputs** | `.ui-input`, `.ui-select`, `.ui-textarea`, `.ui-label`, `.ui-error` | Formulários; adicione `.is-invalid` para erro |
| **Cards** | `.ui-card`, `.ui-card-header`, `.ui-card-body`, `.ui-card-footer` | Blocos de conteúdo |
| **Tabelas** | `.ui-table-wrap`, `.ui-table`, `.ui-table thead th`, `.ui-table-hover`, `.ui-table-sm` | Listagens |
| **Badges** | `.ui-badge`, `.ui-badge-success`, `.ui-badge-secondary` | Status, contadores |
| **Alerts** | `.ui-alert`, `.ui-alert-success`, `.ui-alert-danger`, `.ui-alert-warning`, `.ui-alert-info` | Mensagens inline |
| **Toast** | `.toast`, `.toast-success`, `.toast-error`, `.toast-warning` | Mensagens flash (canto da tela) |
| **Modal** | `.modal`, `.modal-dialog`, `.modal-content`, `.modal-header`, `.modal-body`, `.modal-footer`, `.btn-close` | Diálogos |

**Partials reutilizáveis** (`views/partials/`):

- **table.php**: `$headers` (array), `$rows` (array de arrays de células), `$actions` (callable opcional), `$tableClass`.
- **input.php**: `$name`, `$label`, `$type`, `$value`, `$error`, `$attributes`, `$id`.
- **alert.php**: `$type` (success|error|warning|info), `$message`, `$dismissible`.

Regra de escape: **toda string vinda do banco deve ser exibida com o helper `e()`** para evitar XSS. Use `money()` para valores monetários e `date_br()` para datas (definidos em `config/helpers.php`).

## Segurança (checklist)

- [x] Credenciais em `.env` (não no código)
- [x] CSRF em formulários POST
- [x] Sessão com cookie httponly, samesite e regenerate após login
- [x] Rate limit no login (por IP)
- [x] Headers de segurança (X-Frame-Options, X-Content-Type-Options, CSP, Referrer-Policy). Em produção, prefira remover `unsafe-inline` da CSP (usar arquivos .js/.css locais e nonce para scripts necessários).
- [x] Saída escapada nas views (proteção XSS)
- [x] Upload de imagens validado (extensão, MIME, tamanho máx. 5MB)
- [x] PDO com prepared statements (proteção SQL injection)
- [x] `.htaccess` bloqueia acesso direto a `/config`, `/database`, `/storage`, `/controllers`, `/models`, `/views`
- [x] Em produção (`APP_ENV=production`), erros são logados e exibida página 500 genérica

## Importação

O módulo de importação de produtos (CSV/Excel) está em `ImportController` e `views/import/products.php`. A tabela `import_jobs` (opcional) permite registrar cada importação para auditoria e futura fila; veja `database/schema_import_jobs.sql`.

## Licença

Uso interno / sob demanda.
