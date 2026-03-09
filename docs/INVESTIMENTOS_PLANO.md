# Módulo Controle de Investimentos — Plano de Implementação

## 1) Mudanças necessárias no banco

### Novas tabelas
| Tabela | Descrição |
|--------|-----------|
| **investment_participants** | Participantes (pessoas que aportam, emprestam ou doam). Campos: id, name, contact, document, created_at, updated_at, deleted_at. |
| **investment_loan_payments** | Pagamentos/baixas de empréstimos. Campos: id, investimento_id, data_pagamento, valor_pago, forma_pagamento, comprovante, observacao, created_by, created_at. |
| **investment_assets** | Bens/equipamentos. Campos: id, descricao, categoria, valor_estimado, data_entrada, responsavel_id (FK participant), origem (comprado/doado/emprestado), localizacao, observacoes, comprovante_arquivo, vida_util_meses, created_at, updated_at, deleted_at. |

### Alterações na tabela existente `investimentos`
- **participant_id** INT NULL FK → investment_participants (opcional; manter `pessoa` para compatibilidade).
- **tipo** restrito a: `aporte`, `emprestimo`, `doacao` (mapear antigos: compra/aporte_socio/investimento_dinheiro → aporte).
- **finalidade** VARCHAR(50) NULL: investimento_inicial, capital_giro, compra_equipamento.
- **status** VARCHAR(20) NULL: em_aberto, parcial, quitado, vencido (só para tipo=emprestimo).
- **taxa_juros** DECIMAL(8,4) NULL, **tipo_juros** VARCHAR(20) NULL (simples/composto).
- **data_inicio** DATE NULL, **data_prevista_devolucao** DATE NULL (já existe data_devolucao_prevista → renomear ou usar).
- **comprovante_arquivo** VARCHAR(255) NULL.
- **created_by** INT NULL, **updated_by** INT NULL, **updated_at** TIMESTAMP NULL, **deleted_at** TIMESTAMP NULL (soft delete).

Índices: (participant_id, tipo, data), (status), (deleted_at).

---

## 2) Telas / Rotas a criar ou alterar

| Rota | Descrição |
|------|-----------|
| **investment/index** | Página principal com abas (Financeiro, Bens, Participação, Relatórios) e KPIs no topo. |
| **investment/create** | Novo registro financeiro (aporte/empréstimo/doação). |
| **investment/store** | POST criar. |
| **investment/edit** | Editar registro. |
| **investment/update** | POST atualizar. |
| **investment/delete** | Soft delete. |
| **investment/show** | Detalhe de empréstimo (resumo + tabela de pagamentos + registrar pagamento). |
| **investment/paymentStore** | POST registrar pagamento de empréstimo. |
| **investment/export** | Exportar CSV (filtros atuais). |
| **investment/participants** | Listar participantes (pode ser aba ou rota). |
| **investment/participantCreate** | Criar participante. |
| **investment/participantStore** | POST criar participante. |
| **investment/participantEdit** | Editar participante. |
| **investment/participantUpdate** | POST atualizar. |
| **investment/participantDelete** | Excluir participante. |
| **investment/assets** | Listar bens (aba ou rota). |
| **investment/assetCreate** | Criar bem. |
| **investment/assetStore** | POST criar. |
| **investment/assetEdit** | Editar bem. |
| **investment/assetUpdate** | POST atualizar. |
| **investment/assetDelete** | Excluir bem. |
| **investment/reports** | Aba Relatórios (resumo geral, por pessoa, dívidas, patrimônio). |

Todas protegidas por permissão `investment_manage`.

---

## 3) Checklist de implementação

- [x] Migration SQL: investment_participants, alter investimentos, loan_payments, assets.
- [ ] Model Participant (CRUD + listForSelect).
- [ ] Model Investment (getAll com filtros, getTotals por tipo, getKpis, getById, create, update, soft delete, getLoanPayments, getSaldoDevedor).
- [ ] Model LoanPayment (create, listByInvestimento).
- [ ] Model Asset (CRUD, getTotals, list com filtros).
- [ ] InvestmentController: index (tabs + KPIs), create/edit/store/update/delete, show (empréstimo), paymentStore.
- [ ] ParticipantController ou métodos em InvestmentController para participantes.
- [ ] AssetController ou métodos em InvestmentController para bens.
- [ ] View index com abas (Financeiro, Bens, Participação, Relatórios) e 6 cards KPI.
- [ ] View listagem financeiro (filtros, tabela, badges por tipo, saldo devedor).
- [ ] View create/edit registro financeiro.
- [ ] View show empréstimo (resumo + pagamentos + form pagamento).
- [ ] View participantes (listagem + CRUD).
- [ ] View bens (listagem + CRUD).
- [ ] View participação societária (quadro: pessoa, total aportado, %).
- [ ] View relatórios (resumo geral, por pessoa, dívidas, patrimônio).
- [ ] Rotas e permissões em config.
- [ ] README do módulo (regras: aporte vs empréstimo vs doação; cálculo de participação).

---

## Regras de negócio (resumo)

- **Aporte**: não é dívida; gera participação societária; finalidade: investimento inicial / capital de giro / compra equipamento.
- **Empréstimo**: dívida do negócio; controla principal, juros, datas, parcelas, saldo; status: em_aberto, parcial, quitado, vencido; pagamentos em loan_payments.
- **Doação**: não é dívida e não gera participação; pode ser dinheiro ou equipamento (equipamento → assets com origem=doação).
- **Participação societária**: % = total_aportes_pessoa / total_aportes_geral (apenas aportes; doações e empréstimos não entram).
