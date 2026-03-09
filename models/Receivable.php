<?php

namespace App\Models;

class Receivable
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAll($filters = [])
    {
        $sql = "SELECT cr.*, c.name as customer_name, pc.nome as categoria_nome 
                FROM contas_receber cr
                LEFT JOIN customers c ON cr.cliente_id = c.id 
                LEFT JOIN plano_contas pc ON cr.categoria_id = pc.id
                WHERE 1=1";

        $params = [];
        if (!empty($filters['status'])) {
            $sql .= " AND cr.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['origem'])) {
            $sql .= " AND cr.origem = :origem";
            $params['origem'] = $filters['origem'];
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND cr.data_vencimento >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND cr.data_vencimento <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }

        $sql .= " ORDER BY cr.data_vencimento ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT cr.*, c.name as customer_name, pc.nome as categoria_nome 
            FROM contas_receber cr
            LEFT JOIN customers c ON cr.cliente_id = c.id 
            LEFT JOIN plano_contas pc ON cr.categoria_id = pc.id
            WHERE cr.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
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
                INSERT INTO contas_receber (
                    cliente_id, numero_documento, origem, descricao, valor_total, valor_recebido, saldo_aberto, 
                    data_competencia, data_vencimento, status, forma_recebimento, categoria_id, observacoes,
                    pdv_venda_id, created_by
                ) VALUES (
                    :cliente_id, :numero_documento, :origem, :descricao, :valor_total, :valor_recebido, :saldo_aberto, 
                    :data_competencia, :data_vencimento, :status, :forma_recebimento, :categoria_id, :observacoes,
                    :pdv_venda_id, :created_by
                )
            ");

            $valorRecebido = $data['valor_recebido'] ?? 0;
            $valorTotal = $data['valor_total'];
            $saldoAberto = $valorTotal - $valorRecebido;

            $status = 'ABERTO';
            if ($valorRecebido > 0) {
                $status = ($saldoAberto <= 0) ? 'RECEBIDO' : 'PARCIAL';
            }

            $stmt->execute([
                'cliente_id' => $data['cliente_id'] ?? null,
                'numero_documento' => $data['numero_documento'] ?? null,
                'origem' => $data['origem'] ?? 'MANUAL',
                'descricao' => $data['descricao'],
                'valor_total' => $valorTotal,
                'valor_recebido' => $valorRecebido,
                'saldo_aberto' => $saldoAberto,
                'data_competencia' => $data['data_competencia'],
                'data_vencimento' => $data['data_vencimento'],
                'status' => $status,
                'forma_recebimento' => $data['forma_recebimento'] ?? null,
                'categoria_id' => $data['categoria_id'],
                'observacoes' => $data['observacoes'] ?? null,
                'pdv_venda_id' => $data['pdv_venda_id'] ?? null,
                'created_by' => $_SESSION['user_id'] ?? null
            ]);

            $receivableId = $this->pdo->lastInsertId();

            if ($valorRecebido > 0 && !empty($data['conta_bancaria_id'])) {
                $movementModel = new CashMovement();
                $movementModel->create([
                    'tipo' => 'ENTRADA',
                    'descricao' => "Recbto CR #$receivableId - " . $data['descricao'],
                    'valor' => $valorRecebido,
                    'conta_bancaria_id' => $data['conta_bancaria_id'],
                    'origem' => 'RECEBIMENTO_CR',
                    'referencia_id' => $receivableId,
                    'usuario_id' => $_SESSION['user_id'] ?? 1
                ]);
            }

            if ($owner)
                $this->pdo->commit();
            return $receivableId;
        } catch (Exception $e) {
            if ($owner && $this->pdo->inTransaction())
                $this->pdo->rollBack();
            throw $e;
        }
    }

    public function addPayment($receivableId, $amount, $contaBancariaId, $formaRecebimento, $notes = '')
    {
        $owner = false;
        try {
            if (!$this->pdo->inTransaction()) {
                $this->pdo->beginTransaction();
                $owner = true;
            }

            $cr = $this->getById($receivableId);
            if (!$cr)
                throw new Exception("Recebível não encontrado.");

            $newReceivedAmount = $cr['valor_recebido'] + $amount;
            $newSaldoAberto = $cr['valor_total'] - $newReceivedAmount;
            $status = ($newSaldoAberto <= 0) ? 'RECEBIDO' : 'PARCIAL';
            $dataRecebimento = ($status === 'RECEBIDO') ? date('Y-m-d') : null;

            $stmtUpdate = $this->pdo->prepare("
                UPDATE contas_receber 
                SET valor_recebido = :received_amount, 
                    saldo_aberto = :saldo_aberto, 
                    status = :status,
                    data_recebimento = :data_recebimento,
                    forma_recebimento = :forma
                WHERE id = :id
            ");
            $stmtUpdate->execute([
                'received_amount' => $newReceivedAmount,
                'saldo_aberto' => $newSaldoAberto,
                'status' => $status,
                'data_recebimento' => $dataRecebimento,
                'forma' => $formaRecebimento,
                'id' => $receivableId
            ]);

            $movementModel = new CashMovement();
            $movementModel->create([
                'tipo' => 'ENTRADA',
                'descricao' => "Recbto CR #$receivableId - " . $cr['descricao'],
                'valor' => $amount,
                'conta_bancaria_id' => $contaBancariaId,
                'origem' => 'RECEBIMENTO_CR',
                'referencia_id' => $receivableId,
                'usuario_id' => $_SESSION['user_id'] ?? 1
            ]);

            if ($owner)
                $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($owner && $this->pdo->inTransaction())
                $this->pdo->rollBack();
            throw $e;
        }
    }

    public function updateOverdue()
    {
        $stmt = $this->pdo->prepare("
            UPDATE contas_receber 
            SET status = 'VENCIDO' 
            WHERE status IN ('ABERTO', 'PARCIAL') 
            AND data_vencimento < CURRENT_DATE()
        ");
        return $stmt->execute();
    }
}
