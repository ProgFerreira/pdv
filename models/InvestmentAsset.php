<?php

namespace App\Models;

class InvestmentAsset
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAll(array $filters = []): array
    {
        $sql = "SELECT a.id, a.descricao, a.categoria, a.valor_estimado, a.data_entrada, a.responsavel_id, a.origem, a.localizacao, a.observacoes, a.comprovante_arquivo, a.vida_util_meses, a.created_at,
                p.name AS responsavel_name
                FROM investment_assets a
                LEFT JOIN investment_participants p ON p.id = a.responsavel_id AND p.deleted_at IS NULL
                WHERE (a.deleted_at IS NULL)";
        $params = [];

        if (!empty($filters['categoria'])) {
            $sql .= " AND a.categoria = :categoria";
            $params['categoria'] = $filters['categoria'];
        }
        if (!empty($filters['origem'])) {
            $sql .= " AND a.origem = :origem";
            $params['origem'] = $filters['origem'];
        }
        if (!empty($filters['responsavel_id'])) {
            $sql .= " AND a.responsavel_id = :responsavel_id";
            $params['responsavel_id'] = $filters['responsavel_id'];
        }
        if (!empty($filters['localizacao'])) {
            $sql .= " AND a.localizacao LIKE :localizacao";
            $params['localizacao'] = '%' . trim($filters['localizacao']) . '%';
        }

        $sql .= " ORDER BY a.data_entrada DESC, a.id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getTotals(): array
    {
        $stmt = $this->pdo->query("
            SELECT COUNT(*) AS quantidade, COALESCE(SUM(valor_estimado), 0) AS valor_total
            FROM investment_assets WHERE deleted_at IS NULL
        ");
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return [
            'quantidade'  => (int) ($row['quantidade'] ?? 0),
            'valor_total' => (float) ($row['valor_total'] ?? 0),
        ];
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT a.*, p.name AS responsavel_name
            FROM investment_assets a
            LEFT JOIN investment_participants p ON p.id = a.responsavel_id AND p.deleted_at IS NULL
            WHERE a.id = :id AND (a.deleted_at IS NULL)
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO investment_assets (descricao, categoria, valor_estimado, data_entrada, responsavel_id, origem, localizacao, observacoes, comprovante_arquivo, vida_util_meses)
            VALUES (:descricao, :categoria, :valor_estimado, :data_entrada, :responsavel_id, :origem, :localizacao, :observacoes, :comprovante_arquivo, :vida_util_meses)
        ");
        return $stmt->execute([
            'descricao'         => trim($data['descricao'] ?? ''),
            'categoria'         => trim($data['categoria'] ?? '') ?: null,
            'valor_estimado'    => isset($data['valor_estimado']) ? (float) $data['valor_estimado'] : null,
            'data_entrada'      => $data['data_entrada'] ?? date('Y-m-d'),
            'responsavel_id'    => !empty($data['responsavel_id']) ? (int) $data['responsavel_id'] : null,
            'origem'            => in_array($data['origem'] ?? '', ['comprado', 'doado', 'emprestado'], true) ? $data['origem'] : 'comprado',
            'localizacao'       => trim($data['localizacao'] ?? '') ?: null,
            'observacoes'       => trim($data['observacoes'] ?? '') ?: null,
            'comprovante_arquivo' => trim($data['comprovante_arquivo'] ?? '') ?: null,
            'vida_util_meses'   => !empty($data['vida_util_meses']) ? (int) $data['vida_util_meses'] : null,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE investment_assets SET
                descricao = :descricao, categoria = :categoria, valor_estimado = :valor_estimado,
                data_entrada = :data_entrada, responsavel_id = :responsavel_id, origem = :origem,
                localizacao = :localizacao, observacoes = :observacoes, comprovante_arquivo = :comprovante_arquivo,
                vida_util_meses = :vida_util_meses, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        return $stmt->execute([
            'id'                => $id,
            'descricao'         => trim($data['descricao'] ?? ''),
            'categoria'         => trim($data['categoria'] ?? '') ?: null,
            'valor_estimado'    => isset($data['valor_estimado']) ? (float) $data['valor_estimado'] : null,
            'data_entrada'      => $data['data_entrada'] ?? date('Y-m-d'),
            'responsavel_id'    => !empty($data['responsavel_id']) ? (int) $data['responsavel_id'] : null,
            'origem'            => in_array($data['origem'] ?? '', ['comprado', 'doado', 'emprestado'], true) ? $data['origem'] : 'comprado',
            'localizacao'       => trim($data['localizacao'] ?? '') ?: null,
            'observacoes'       => trim($data['observacoes'] ?? '') ?: null,
            'comprovante_arquivo' => trim($data['comprovante_arquivo'] ?? '') ?: null,
            'vida_util_meses'   => !empty($data['vida_util_meses']) ? (int) $data['vida_util_meses'] : null,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE investment_assets SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getCategoriasDistinct(): array
    {
        $stmt = $this->pdo->query("SELECT DISTINCT categoria FROM investment_assets WHERE deleted_at IS NULL AND categoria IS NOT NULL AND categoria != '' ORDER BY categoria");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}
