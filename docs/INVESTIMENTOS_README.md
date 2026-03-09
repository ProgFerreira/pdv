# Módulo Controle de Investimentos

## Regras de negócio

### Aporte (capital)
- **Não é dívida.** Entra no patrimônio / capital da operação.
- Pode gerar ou alterar **participação societária**.
- Finalidades permitidas: investimento inicial, capital de giro, compra de equipamento.
- Deve ser usado para calcular o quadro de sócios (participação %).

### Empréstimo (dívida)
- **É dívida do negócio.** Deve ser pago ao credor.
- Controla: valor principal, datas, forma de pagamento.
- **Saldo devedor** = valor principal − total dos pagamentos registrados (baixas).
- Status sugeridos: em aberto, parcial, quitado, vencido (conforme saldo e vencimento).
- Na tela de **detalhe do empréstimo** é possível registrar pagamentos (baixas) e acompanhar o saldo.

### Doação
- **Não é dívida** e **não gera participação societária**.
- Pode ser em dinheiro ou em equipamento. Se for equipamento, cadastre na aba **Bens/Equipamentos** com origem “Doado”.

### Bens/Equipamentos
- Patrimônio físico: freezer, forno, móveis, etc.
- Origem: comprado, doado ou emprestado.
- Campos: descrição, categoria, valor estimado, data de entrada, responsável (quem doou/emprestou), localização, vida útil (opcional).

---

## Cálculo da participação societária

- **Fórmula:** `% participação = (total de aportes da pessoa) / (total de aportes de todos) × 100`
- **Entram no cálculo:** apenas registros do tipo **aporte** (incluindo “Aporte de Sócio” e “Investimento em Dinheiro”).
- **Não entram:** doações e empréstimos.

Exemplo:  
- Sócio A aportou R$ 60.000  
- Sócio B aportou R$ 40.000  
- Total de aportes = R$ 100.000  
- A = 60%, B = 40%.

---

## Estrutura de tabelas (resumo)

- **investment_participants** – Cadastro de participantes (pessoas).
- **investimentos** – Registros financeiros (aporte, empréstimo, doação); pode ter `participant_id`, `finalidade`, `status`, `deleted_at`.
- **investment_loan_payments** – Pagamentos/baixas de empréstimos.
- **investment_assets** – Bens/equipamentos (descrição, categoria, origem, valor estimado, etc.).

---

## Como rodar a migration

Se a tabela `investimentos` ainda não existir, execute primeiro:

```bash
php run_migration_investments.php
```

Em seguida (ou se `investimentos` já existir):

```bash
php run_migration_investments_v2.php
```

Isso cria as tabelas `investment_participants`, `investment_loan_payments`, `investment_assets` e adiciona as colunas extras em `investimentos` quando ainda não existirem.

---

## Abas da tela principal

1. **Financeiro recebido** – Listagem de aportes, empréstimos e doações; filtros; link “Detalhe” para empréstimos (pagamentos e saldo).
2. **Bens/Equipamentos** – CRUD de bens; filtros por categoria, origem e responsável.
3. **Participação societária** – Quadro com % por pessoa (só aportes) e lista de participantes cadastrados.
4. **Relatórios** – Resumo geral, por pessoa e patrimônio (equipamentos).

Os **KPIs** no topo (Total Investido, Total Emprestado, Total Doado, Valor Equipamentos, Nº Participantes, Dívida em Aberto) são exibidos em todas as abas.
