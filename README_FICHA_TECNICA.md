# Módulo Ficha Técnica / Formação de Preço

Módulo para galeteria (pratos compostos: galeto + arroz + tropeiro + farofa, etc.): cadastro de **insumos**, montagem da **ficha técnica** por produto e cálculo automático de **custo total** e **preço sugerido** por margem bruta.

## Requisitos

- PHP 8.2+
- MySQL 8+
- Sistema PDV já instalado (produtos, categorias, etc.)

## Configuração do banco

1. Execute a migration que adiciona as colunas opcionais em `products` e cria as tabelas do módulo:

```bash
# Via MySQL (substitua pdv_religioso pelo nome do seu banco)
mysql -u root -p pdv_religioso < database/migrations/007_ficha_tecnica_ingredients_sheets.sql
```

Ou pelo phpMyAdmin: importe o conteúdo de `database/migrations/007_ficha_tecnica_ingredients_sheets.sql` no banco do projeto.

2. **Não quebra o sistema**: a migration só **adiciona** colunas em `products` (`yield_target_grams`, `margin_percent`) se ainda não existirem e cria as tabelas `ingredients`, `technical_sheets` e `technical_sheet_items`. Produtos e o restante do PDV continuam funcionando normalmente.

## Como usar

### 1. Cadastrar insumos (matérias-primas)

- Menu **Insumos (Ficha Técnica)** → Novo Insumo.
- Preencha: nome, código (opcional), unidade (kg, g, l, ml, un), custo por unidade (ex: R$ por kg).
- Ex.: "Frango inteiro", unidade kg, R$ 12,00/kg; "Arroz", kg, R$ 4,50/kg.

### 2. Cadastrar produto (prato)

- Em **Produtos**, crie ou edite o produto (prato).
- Opcional: preencha **Porção final (g)** e **Margem bruta (%)** (padrão 65%). Esses dados são usados na tela da ficha para exibir o preço sugerido.

### 3. Montar a ficha técnica

- Na listagem de **Produtos**, clique em **Ficha Técnica** no produto desejado.
- Será aberta a ficha técnica do prato (inicialmente vazia).
- Clique em **Adicionar item**: escolha o insumo, classificação (ex: proteína, acompanhamento), quantidade bruta, unidade do item e, se quiser, quantidade líquida (para cálculo automático de rendimento %).
- Repita para todos os ingredientes do prato.
- O sistema calcula automaticamente o custo de cada item (conversão de unidades: g↔kg, ml↔l) e o **custo total da ficha**.
- O **preço sugerido** é exibido no card à direita: `preço = custo_total / (1 - margem%/100)`.

### 4. Usar o preço sugerido

- Anote o valor sugerido e, se quiser, atualize o **Preço de venda** do produto em Editar Produto.

## Estrutura de arquivos

- **Migration**: `database/migrations/007_ficha_tecnica_ingredients_sheets.sql`
- **Helper de cálculos**: `helpers/Calc.php` (carregado por `config/helpers.php`)
- **Models**: `models/Ingredient.php`, `models/TechnicalSheet.php`
- **Controllers**: `controllers/IngredientController.php`, `controllers/TechnicalSheetController.php`
- **Views**: `views/ingredients/` (index, form), `views/sheets/` (view, item_form)

## Regras de cálculo

- **Rendimento %**: se informar quantidade líquida e bruta, `yield% = (qty_net / qty_brut) * 100`.
- **Custo do item**: conversões básicas (ex.: item em g, ingrediente em kg → custo = (qty_brut/1000) * custo_por_kg).
- **Custo total**: soma dos `item_total_cost` da ficha.
- **Preço sugerido**: `preço = custo_total / (1 - margem_percent/100)`.

## Permissões

As rotas do módulo (insumos e ficha técnica) usam a mesma permissão **Produtos** (`product`). Quem pode gerenciar produtos pode gerenciar insumos e fichas técnicas.

## Segurança

- CSRF em todos os formulários POST.
- Validação server-side (IDs, quantidades ≥ 0, insumos existentes).
- Saída escapada com `htmlspecialchars` nas views.
- PDO com prepared statements.
