<?php

namespace App\Models;

class CashMovement
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAll($filters = [])
    {
        $sql = "SELECT mc.*, cb.nome as conta_nome, u.name as usuario_nome 
                FROM movimentacao_caixa mc
                JOIN contas_bancarias cb ON mc.conta_bancaria_id = cb.id
                LEFT JOIN users u ON mc.usuario_id = u.id
                WHERE 1=1";

        $params = [];
        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(mc.created_at) >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(mc.created_at) <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }
        if (!empty($filters['conta_bancaria_id'])) {
            $sql .= " AND mc.conta_bancaria_id = :conta_id";
            $params['conta_id'] = $filters['conta_bancaria_id'];
        }

        $sql .= " ORDER BY mc.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create($data)
    {
        $owner = false;
        try {
            if (!$this->pdo->inTransaction()) {
                $this->pdo->beginTransaction();
                $owner = true;
            }

            $stmt = $this->pdo->prepare("
                INSERT INTO movimentacao_caixa (tipo, descricao, valor, conta_bancaria_id, origem, referencia_id, usuario_id) 
                VALUES (:tipo, :descricao, :valor, :conta_bancaria_id, :origem, :referencia_id, :usuario_id)
            ");

            $stmt->execute([
                'tipo' => $data['tipo'],
                'descricao' => $data['descricao'],
                'valor' => $data['valor'],
                'conta_bancaria_id' => $data['conta_bancaria_id'],
                'origem' => $data['origem'] ?? 'OUTRO',
                'referencia_id' => $data['referencia_id'] ?? null,
                'usuario_id' => $data['usuario_id'] ?? ($_SESSION['user_id'] ?? 1)
            ]);

            // Update Financial Account Balance
            $accModel = new FinancialAccount();
            $accModel->updateBalance($data['conta_bancaria_id'], $data['valor'], $data['tipo']);

            if ($owner)
                $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($owner && $this->pdo->inTransaction())
                $this->pdo->rollBack();
            throw $e;
        }
    }
}
