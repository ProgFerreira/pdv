# Padrão de card aplicado – páginas alteradas

O padrão de título/card do sistema está definido em **`public/css/sistema-premium.css`** (classes `.card-standard`, `.card-standard-header`, `.card-standard-body`, `.card-standard-metric`). Todas as telas que tinham painéis com estilo antigo (`bg-white rounded shadow border`) foram atualizadas para usar esse padrão.

## Resumo do padrão (CSS)

- **`.card-standard`** – Container do card (fundo branco, borda, sombra, border-radius 10px).
- **`.card-standard-header`** – Cabeçalho com fundo #f8f9fa, ícone roxo #6366f1, título #343a40, borda inferior #dee2e6.
- **`.card-standard-body`** – Área de conteúdo com padding.
- **`.card-standard-metric`** – Cards de KPI/métrica com borda lateral colorida (`.border-l-success`, `.border-l-primary`, `.border-l-danger`, `.border-l-warning`, `.border-l-info`).
- **`.card-metric-label`** – Label dos KPIs (texto pequeno, cinza, maiúsculo).

---

## Páginas alteradas (para validação)

| # | Arquivo | O que foi alterado |
|---|---------|--------------------|
| 1 | **views/stock/index.php** | Tabela de entradas: card com header "Entradas de Estoque" + ícone warehouse. |
| 2 | **views/products/index.php** | Filtros → card-standard + header "Filtros". Cards de resumo (7) → card-standard-metric com border-l-*. Tabela → card "Listagem de Produtos". |
| 3 | **views/customers/index.php** | Tabela → card com header "Listagem de Clientes". |
| 4 | **views/user/index.php** | Tabela → card com header "Listagem de Usuários". |
| 5 | **views/supplier/index.php** | Tabela → card com header "Listagem de Fornecedores". |
| 6 | **views/brands/index.php** | Tabela → card com header "Listagem de Marcas". |
| 7 | **views/categories/index.php** | Tabela → card com header "Listagem de Categorias". |
| 8 | **views/giftcard/index.php** | Tabela principal → card "Listagem de Vales Presente". Bloco "Gastos do Vale" → card-standard-header (substituindo header roxo). |
| 9 | **views/audit/index.php** | Filtros → card-standard + header "Filtros". Tabela de logs → card com header "Histórico de ações". |
| 10 | **views/investment/index.php** | 2 cards de resumo → card-standard-metric (Total Investido, Ativos Cadastrados). Tabela → card "Listagem de Investimentos". |
| 11 | **views/cash/history.php** | 5 cards totalizadores → card-standard-metric. Filtros → card-standard + header "Filtros". Tabela → card "Histórico de Caixas". |
| 12 | **views/permission/index.php** | Bloco "Usuários por grupo" → card-standard + header. Cards por role → card-standard + card-standard-header. |
| 13 | **views/import/products.php** | Card "Upload de Planilha" → card-standard + header. Card "Estrutura da Planilha" e "Referências Disponíveis" → card-standard + header. |

## Páginas que já usavam o padrão (não alteradas nesta etapa)

- **views/dashboard/index.php** – Já usa card-standard e card-standard-metric.
- **views/sales/index.php** – Já usa card-standard e card-standard-metric.
- **views/receivable/index.php** – Já usa card-standard (ajuste anterior).
- **views/payable/index.php** – Já usa card-standard (ajuste anterior).

## Páginas não alteradas (opcional para depois)

- **views/reports/** (abc, payments, profitability, sectors, consigned, best_sellers, daily, etc.) – Podem receber o mesmo padrão em filtros e tabelas.
- **views/cash/report.php**, **views/report/cash_flow.php** – Idem.
- **views/pos/** – Layout específico do PDV; mantido como está.
- **views/errors/** (403, 404, 500) – Páginas de erro; estilo próprio.
- Formulários de criação/edição (create, edit, form) – Podem ser padronizados em uma próxima etapa.

---

**Como validar:** Abra cada rota correspondente (ex.: `?route=stock/index`, `?route=product/index`, `?route=customer/index`) e confira se os painéis exibem o cabeçalho claro com ícone roxo e linha separadora.
