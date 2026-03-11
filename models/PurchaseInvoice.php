<?php

namespace App\Models;

/**
 * Notas Fiscais de Compras: armazena NF com fornecedor, data, upload (imagem/PDF), status, valor, data pagamento e quem pagou.
 */
class PurchaseInvoice
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * @param array{status?: string, supplier_id?: int, start_date?: string, end_date?: string} $filters
     * @return array
     */
    public function getAll(array $filters = []): array
    {
        $sql = "SELECT nfc.*, s.name as supplier_name,
                u.name as pago_por_nome, creator.name as created_by_name
                FROM notas_fiscais_compras nfc
                LEFT JOIN suppliers s ON nfc.supplier_id = s.id
                LEFT JOIN users u ON nfc.pago_por_user_id = u.id
                LEFT JOIN users creator ON nfc.created_by = creator.id
                WHERE (nfc.deleted_at IS NULL)";

        $params = [];
        if (!empty($filters['status'])) {
            $sql .= " AND nfc.status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['supplier_id'])) {
            $sql .= " AND nfc.supplier_id = :supplier_id";
            $params['supplier_id'] = (int) $filters['supplier_id'];
        }
        if (!empty($filters['start_date'])) {
            $sql .= " AND nfc.data_emissao >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND nfc.data_emissao <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }

        $sql .= " ORDER BY nfc.data_emissao DESC, nfc.id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT nfc.*, s.name as supplier_name, s.phone as supplier_phone,
                   u.name as pago_por_nome, creator.name as created_by_name
            FROM notas_fiscais_compras nfc
            LEFT JOIN suppliers s ON nfc.supplier_id = s.id
            LEFT JOIN users u ON nfc.pago_por_user_id = u.id
            LEFT JOIN users creator ON nfc.created_by = creator.id
            WHERE nfc.id = ? AND (nfc.deleted_at IS NULL)
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * @param array{supplier_id?: int|null, fornecedor_nome: string, telefone?: string, data_emissao: string, arquivo_path?: string|null, arquivo_nome_original?: string|null, status: string, valor: float, data_pagamento?: string|null, pago_por_user_id?: int|null, observacoes?: string|null} $data
     */
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO notas_fiscais_compras (
                supplier_id, fornecedor_nome, telefone, data_emissao, arquivo_path, arquivo_nome_original,
                status, valor, data_pagamento, pago_por_user_id, observacoes, created_by
            ) VALUES (
                :supplier_id, :fornecedor_nome, :telefone, :data_emissao, :arquivo_path, :arquivo_nome_original,
                :status, :valor, :data_pagamento, :pago_por_user_id, :observacoes, :created_by
            )
        ");
        $stmt->execute([
            'supplier_id' => !empty($data['supplier_id']) ? (int) $data['supplier_id'] : null,
            'fornecedor_nome' => $data['fornecedor_nome'] ?? '',
            'telefone' => $data['telefone'] ?? null,
            'data_emissao' => $data['data_emissao'],
            'arquivo_path' => $data['arquivo_path'] ?? null,
            'arquivo_nome_original' => $data['arquivo_nome_original'] ?? null,
            'status' => $data['status'] ?? 'PENDENTE',
            'valor' => (float) ($data['valor'] ?? 0),
            'data_pagamento' => !empty($data['data_pagamento']) ? $data['data_pagamento'] : null,
            'pago_por_user_id' => !empty($data['pago_por_user_id']) ? (int) $data['pago_por_user_id'] : null,
            'observacoes' => $data['observacoes'] ?? null,
            'created_by' => $_SESSION['user_id'] ?? null
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @param array{supplier_id?: int|null, fornecedor_nome?: string, telefone?: string, data_emissao?: string, arquivo_path?: string|null, arquivo_nome_original?: string|null, status?: string, valor?: float, data_pagamento?: string|null, pago_por_user_id?: int|null, observacoes?: string|null} $data
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE notas_fiscais_compras SET
                supplier_id = :supplier_id,
                fornecedor_nome = :fornecedor_nome,
                telefone = :telefone,
                data_emissao = :data_emissao,
                arquivo_path = COALESCE(:arquivo_path, arquivo_path),
                arquivo_nome_original = COALESCE(:arquivo_nome_original, arquivo_nome_original),
                status = :status,
                valor = :valor,
                data_pagamento = :data_pagamento,
                pago_por_user_id = :pago_por_user_id,
                observacoes = :observacoes,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND (deleted_at IS NULL)
        ");
        return $stmt->execute([
            'supplier_id' => !empty($data['supplier_id']) ? (int) $data['supplier_id'] : null,
            'fornecedor_nome' => $data['fornecedor_nome'] ?? '',
            'telefone' => $data['telefone'] ?? null,
            'data_emissao' => $data['data_emissao'] ?? date('Y-m-d'),
            'arquivo_path' => $data['arquivo_path'] ?? null,
            'arquivo_nome_original' => $data['arquivo_nome_original'] ?? null,
            'status' => $data['status'] ?? 'PENDENTE',
            'valor' => (float) ($data['valor'] ?? 0),
            'data_pagamento' => !empty($data['data_pagamento']) ? $data['data_pagamento'] : null,
            'pago_por_user_id' => !empty($data['pago_por_user_id']) ? (int) $data['pago_por_user_id'] : null,
            'observacoes' => $data['observacoes'] ?? null,
            $id
        ]);
    }

    /** Soft delete */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE notas_fiscais_compras SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /** Retorna o nome do fornecedor para exibição (cadastro ou texto). */
    public function getFornecedorDisplay(array $row): string
    {
        if (!empty($row['fornecedor_nome'])) {
            return $row['fornecedor_nome'];
        }
        return $row['supplier_name'] ?? '—';
    }
}
