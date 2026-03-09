<?php

namespace App\Models;

class Investment
{
    private $pdo;

    /** Nome da coluna de data no banco: 'data' ou 'data_aquisicao' (estrutura antiga) */
    private $dateColumn = 'data';

    /** Se a tabela tem coluna deleted_at (soft delete) */
    private $hasDeletedAt = false;

    /** Se a tabela tem colunas participant_id, finalidade, status */
    private $hasExtraCols = false;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
        $this->detectDateColumn();
        $this->detectDeletedAt();
        $this->detectExtraCols();
    }

    private function detectDateColumn(): void
    {
        try {
            $stmt = $this->pdo->query("SHOW COLUMNS FROM investimentos LIKE 'data'");
            if ($stmt->rowCount() > 0) {
                $this->dateColumn = 'data';
                return;
            }
            $stmt = $this->pdo->query("SHOW COLUMNS FROM investimentos LIKE 'data_aquisicao'");
            if ($stmt->rowCount() > 0) {
                $this->dateColumn = 'data_aquisicao';
            }
        } catch (\Throwable $e) {
            // tabela pode não existir ainda
        }
    }

    private function detectDeletedAt(): void
    {
        try {
            $stmt = $this->pdo->query("SHOW COLUMNS FROM investimentos LIKE 'deleted_at'");
            $this->hasDeletedAt = $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
        }
    }

    private function detectExtraCols(): void
    {
        try {
            $stmt = $this->pdo->query("SHOW COLUMNS FROM investimentos LIKE 'participant_id'");
            $this->hasExtraCols = $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
        }
    }

    private function whereNotDeleted(): string
    {
        return $this->hasDeletedAt ? ' AND (deleted_at IS NULL)' : '';
    }

    /**
     * Lista investimentos com filtros opcionais.
     * Filtros: start_date, end_date, tipo, estado, pessoa
     */
    public function getAll(array $filters = []): array
    {
        $dc = $this->dateColumn;
        $sql = "SELECT id, `{$dc}` AS data, pessoa, valor, produto, tipo, estado, observacoes, documento_numero, quantidade, data_devolucao_prevista, forma_pagamento, categoria_ativo, created_at FROM investimentos WHERE 1=1";
        $params = [];

        if (!empty($filters['start_date'])) {
            $sql .= " AND `{$dc}` >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND `{$dc}` <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }
        if (!empty($filters['tipo'])) {
            $sql .= " AND tipo = :tipo";
            $params['tipo'] = $filters['tipo'];
        }
        if (!empty($filters['estado'])) {
            $sql .= " AND estado = :estado";
            $params['estado'] = $filters['estado'];
        }
        if (!empty($filters['pessoa'])) {
            $sql .= " AND pessoa LIKE :pessoa";
            $params['pessoa'] = '%' . trim($filters['pessoa']) . '%';
        }
        if (!empty($filters['categoria_ativo'])) {
            $sql .= " AND categoria_ativo = :categoria_ativo";
            $params['categoria_ativo'] = $filters['categoria_ativo'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['forma_pagamento'])) {
            $sql .= " AND forma_pagamento = :forma_pagamento";
            $params['forma_pagamento'] = $filters['forma_pagamento'];
        }

        $sql .= " ORDER BY `{$dc}` DESC, id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Retorna totais: total_valor e quantidade (count) conforme filtros.
     */
    public function getTotals(array $filters = []): array
    {
        $dc = $this->dateColumn;
        $sql = "SELECT COUNT(*) AS quantidade, COALESCE(SUM(valor), 0) AS total_valor FROM investimentos WHERE 1=1" . $this->whereNotDeleted();
        $params = [];

        if (!empty($filters['start_date'])) {
            $sql .= " AND `{$dc}` >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND `{$dc}` <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }
        if (!empty($filters['tipo'])) {
            $sql .= " AND tipo = :tipo";
            $params['tipo'] = $filters['tipo'];
        }
        if (!empty($filters['estado'])) {
            $sql .= " AND estado = :estado";
            $params['estado'] = $filters['estado'];
        }
        if (!empty($filters['pessoa'])) {
            $sql .= " AND pessoa LIKE :pessoa";
            $params['pessoa'] = '%' . trim($filters['pessoa']) . '%';
        }
        if (!empty($filters['categoria_ativo'])) {
            $sql .= " AND categoria_ativo = :categoria_ativo";
            $params['categoria_ativo'] = $filters['categoria_ativo'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['forma_pagamento'])) {
            $sql .= " AND forma_pagamento = :forma_pagamento";
            $params['forma_pagamento'] = $filters['forma_pagamento'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return [
            'total_valor' => (float) ($row['total_valor'] ?? 0),
            'quantidade' => (int) ($row['quantidade'] ?? 0),
        ];
    }

    public function getById(int $id): ?array
    {
        $dc = $this->dateColumn;
        $cols = "id, `{$dc}` AS data, pessoa, valor, produto, tipo, estado, observacoes, documento_numero, quantidade, data_devolucao_prevista, forma_pagamento, categoria_ativo, created_at";
        if ($this->hasExtraCols) {
            $cols .= ", participant_id, finalidade, status";
        }
        $stmt = $this->pdo->prepare("
            SELECT {$cols}
            FROM investimentos WHERE id = :id
        " . $this->whereNotDeleted());
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * KPIs para o módulo: total aportes, empréstimos, doações, equipamentos, participantes, dívida em aberto.
     * Tipos considerados: aporte = aporte_socio, investimento_dinheiro, aporte; emprestimo; doacao.
     */
    public function getKpis(): array
    {
        $where = $this->whereNotDeleted();
        $dc = $this->dateColumn;

        $totalAportes = 0;
        try {
            $stmt = $this->pdo->query("SELECT COALESCE(SUM(valor), 0) AS t FROM investimentos WHERE tipo IN ('aporte','aporte_socio','investimento_dinheiro')" . $where);
            $totalAportes = (float) ($stmt->fetch(\PDO::FETCH_ASSOC)['t'] ?? 0);
        } catch (\Throwable $e) {
        }

        $totalEmprestado = 0;
        try {
            $stmt = $this->pdo->query("SELECT COALESCE(SUM(valor), 0) AS t FROM investimentos WHERE tipo = 'emprestimo'" . $where);
            $totalEmprestado = (float) ($stmt->fetch(\PDO::FETCH_ASSOC)['t'] ?? 0);
        } catch (\Throwable $e) {
        }

        $totalDoado = 0;
        try {
            $stmt = $this->pdo->query("SELECT COALESCE(SUM(valor), 0) AS t FROM investimentos WHERE tipo = 'doacao'" . $where);
            $totalDoado = (float) ($stmt->fetch(\PDO::FETCH_ASSOC)['t'] ?? 0);
        } catch (\Throwable $e) {
        }

        $totalEquipamentos = 0;
        $numParticipantes = 0;
        try {
            $stmt = $this->pdo->query("SELECT COALESCE(SUM(valor_estimado), 0) AS t, COUNT(*) AS c FROM investment_assets WHERE deleted_at IS NULL");
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $totalEquipamentos = (float) ($row['t'] ?? 0);
        } catch (\Throwable $e) {
        }
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) AS c FROM investment_participants WHERE deleted_at IS NULL");
            $numParticipantes = (int) ($stmt->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0);
        } catch (\Throwable $e) {
        }

        $totalEmAberto = 0;
        try {
            $stmt = $this->pdo->query("SELECT id, valor FROM investimentos WHERE tipo = 'emprestimo'" . $where);
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $pago = (new InvestmentLoanPayment())->getTotalPago((int) $row['id']);
                $totalEmAberto += max(0, (float) $row['valor'] - $pago);
            }
        } catch (\Throwable $e) {
        }

        return [
            'total_aportes'       => $totalAportes,
            'total_emprestado'   => $totalEmprestado,
            'total_doado'        => $totalDoado,
            'total_equipamentos' => $totalEquipamentos,
            'num_participantes'  => $numParticipantes,
            'total_em_aberto'    => $totalEmAberto,
        ];
    }

    /**
     * Saldo devedor de um empréstimo (valor principal - total pago).
     */
    public function getSaldoDevedor(int $investimentoId): float
    {
        $inv = $this->getById($investimentoId);
        if (!$inv || ($inv['tipo'] ?? '') !== 'emprestimo') {
            return 0.0;
        }
        $valor = (float) ($inv['valor'] ?? 0);
        $pago = (new InvestmentLoanPayment())->getTotalPago($investimentoId);
        return max(0, $valor - $pago);
    }

    /**
     * Participação societária: por pessoa, total aportado e % (apenas aportes; doações e empréstimos não entram).
     */
    public function getParticipacaoSocietaria(): array
    {
        $whereDeleted = $this->hasDeletedAt ? ' AND (i.deleted_at IS NULL)' : '';
        $sql = "SELECT i.pessoa, SUM(i.valor) AS total_aportado
                FROM investimentos i
                WHERE i.tipo IN ('aporte','aporte_socio','investimento_dinheiro')" . $whereDeleted . "
                GROUP BY i.pessoa";
        try {
            $stmt = $this->pdo->query($sql);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $rows = [];
        }
        try {
            $stmt = $this->pdo->query("SHOW COLUMNS FROM investimentos LIKE 'participant_id'");
            if ($stmt->rowCount() > 0) {
                $sql2 = "SELECT COALESCE(p.name, i.pessoa) AS pessoa_nome, i.participant_id, i.pessoa, SUM(i.valor) AS total_aportado
                        FROM investimentos i
                        LEFT JOIN investment_participants p ON p.id = i.participant_id AND p.deleted_at IS NULL
                        WHERE i.tipo IN ('aporte','aporte_socio','investimento_dinheiro')" . $whereDeleted . "
                        GROUP BY i.participant_id, COALESCE(p.name, i.pessoa)";
                $stmt = $this->pdo->query($sql2);
                $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
        } catch (\Throwable $e) {
        }

        $totalGeral = 0;
        foreach ($rows as $r) {
            $totalGeral += (float) ($r['total_aportado'] ?? 0);
        }

        $out = [];
        foreach ($rows as $r) {
            $total = (float) ($r['total_aportado'] ?? 0);
            $nome = $r['pessoa_nome'] ?? $r['pessoa'] ?? 'Sem nome';
            if (trim((string) $nome) === '') {
                $nome = 'Sem nome';
            }
            $out[] = [
                'pessoa_nome'    => $nome,
                'participant_id' => $r['participant_id'] ?? null,
                'total_aportado' => $total,
                'percentual'     => $totalGeral > 0 ? round(($total / $totalGeral) * 100, 2) : 0,
            ];
        }
        return $out;
    }

    public function create(array $data): bool
    {
        $dc = $this->dateColumn;
        $stmt = $this->pdo->prepare("
            INSERT INTO investimentos (`{$dc}`, pessoa, valor, produto, tipo, estado, observacoes, documento_numero, quantidade, data_devolucao_prevista, forma_pagamento, categoria_ativo)
            VALUES (:data, :pessoa, :valor, :produto, :tipo, :estado, :observacoes, :documento_numero, :quantidade, :data_devolucao_prevista, :forma_pagamento, :categoria_ativo)
        ");
        $params = [
            'data' => $data['data'],
            'pessoa' => $data['pessoa'] ?? null,
            'valor' => $data['valor'],
            'produto' => $data['produto'] ?? null,
            'tipo' => $data['tipo'] ?? 'aporte',
            'estado' => !empty($data['estado']) ? $data['estado'] : null,
            'observacoes' => !empty($data['observacoes']) ? $data['observacoes'] : null,
            'documento_numero' => !empty($data['documento_numero']) ? $data['documento_numero'] : null,
            'quantidade' => (int) ($data['quantidade'] ?? 1),
            'data_devolucao_prevista' => !empty($data['data_devolucao_prevista']) ? $data['data_devolucao_prevista'] : null,
            'forma_pagamento' => !empty($data['forma_pagamento']) ? $data['forma_pagamento'] : null,
            'categoria_ativo' => !empty($data['categoria_ativo']) ? $data['categoria_ativo'] : null,
        ];
        $ok = $stmt->execute($params);
        if ($ok && !empty($data['participant_id'])) {
            try {
                $lastId = (int) $this->pdo->lastInsertId();
                $up = $this->pdo->prepare("UPDATE investimentos SET participant_id = :pid WHERE id = :id");
                $up->execute(['pid' => $data['participant_id'], 'id' => $lastId]);
            } catch (\Throwable $e) {
            }
        }
        return $ok;
    }

    public function update(int $id, array $data): bool
    {
        $dc = $this->dateColumn;
        $stmt = $this->pdo->prepare("
            UPDATE investimentos SET
                `{$dc}` = :data,
                pessoa = :pessoa,
                valor = :valor,
                produto = :produto,
                tipo = :tipo,
                estado = :estado,
                observacoes = :observacoes,
                documento_numero = :documento_numero,
                quantidade = :quantidade,
                data_devolucao_prevista = :data_devolucao_prevista,
                forma_pagamento = :forma_pagamento,
                categoria_ativo = :categoria_ativo
            WHERE id = :id
        ");
        $ok = $stmt->execute([
            'id' => $id,
            'data' => $data['data'],
            'pessoa' => $data['pessoa'] ?? null,
            'valor' => $data['valor'],
            'produto' => $data['produto'] ?? null,
            'tipo' => $data['tipo'] ?? 'aporte',
            'estado' => !empty($data['estado']) ? $data['estado'] : null,
            'observacoes' => !empty($data['observacoes']) ? $data['observacoes'] : null,
            'documento_numero' => !empty($data['documento_numero']) ? $data['documento_numero'] : null,
            'quantidade' => (int) ($data['quantidade'] ?? 1),
            'data_devolucao_prevista' => !empty($data['data_devolucao_prevista']) ? $data['data_devolucao_prevista'] : null,
            'forma_pagamento' => !empty($data['forma_pagamento']) ? $data['forma_pagamento'] : null,
            'categoria_ativo' => !empty($data['categoria_ativo']) ? $data['categoria_ativo'] : null,
        ]);
        if ($ok && array_key_exists('participant_id', $data)) {
            try {
                $up = $this->pdo->prepare("UPDATE investimentos SET participant_id = :pid WHERE id = :id");
                $up->execute(['pid' => $data['participant_id'] ?? null, 'id' => $id]);
            } catch (\Throwable $e) {
            }
        }
        return $ok;
    }

    public function delete(int $id): bool
    {
        if ($this->hasDeletedAt) {
            $stmt = $this->pdo->prepare("UPDATE investimentos SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        }
        $stmt = $this->pdo->prepare("DELETE FROM investimentos WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /** Lista pessoas distintas para filtro/autocomplete */
    public function getPessoasDistinct(): array
    {
        $stmt = $this->pdo->query("
            SELECT DISTINCT pessoa FROM investimentos
            WHERE pessoa IS NOT NULL AND pessoa != ''
            ORDER BY pessoa
        ");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /** Lista categorias distintas para filtro (requer coluna categoria_ativo) */
    public function getCategoriasDistinct(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT DISTINCT categoria_ativo FROM investimentos
                WHERE categoria_ativo IS NOT NULL AND categoria_ativo != ''
                ORDER BY categoria_ativo
            ");
            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Throwable $e) {
            return [];
        }
    }
}
