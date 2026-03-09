<?php

namespace App\Models;

class Payable
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAll($filters = [])
    {
        $sql = "SELECT cp.*, s.name as supplier_name, pc.nome as categoria_nome 
                FROM contas_pagar cp
                LEFT JOIN suppliers s ON cp.fornecedor_id = s.id 
                LEFT JOIN plano_contas pc ON cp.categoria_id = pc.id
                WHERE 1=1";

        $params = [];
        if (!empty($filters['status'])) {
            $sql .= " AND cp.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND cp.data_vencimento >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND cp.data_vencimento <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }

        if (!empty($filters['categoria_id'])) {
            $sql .= " AND cp.categoria_id = :categoria_id";
            $params['categoria_id'] = $filters['categoria_id'];
        }

        if (!empty($filters['supplier_id'])) {
            $sql .= " AND cp.fornecedor_id = :supplier_id";
            $params['supplier_id'] = $filters['supplier_id'];
        }

        $sql .= " ORDER BY cp.data_vencimento ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT cp.*, s.name as supplier_name, pc.nome as categoria_nome 
            FROM contas_pagar cp
            LEFT JOIN suppliers s ON cp.fornecedor_id = s.id 
            LEFT JOIN plano_contas pc ON cp.categoria_id = pc.id
            WHERE cp.id = ?
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
                INSERT INTO contas_pagar (
                    fornecedor_id, numero_documento, descricao, valor_total, valor_pago, saldo_aberto,
                    data_competencia, data_vencimento, status, categoria_id, forma_pagamento, observacoes,
                    recorrente, regra_recorrencia, parcelado, qtd_parcelas, pai_id, created_by
                ) VALUES (
                    :fornecedor_id, :numero_documento, :descricao, :valor_total, :valor_pago, :saldo_aberto,
                    :data_competencia, :data_vencimento, :status, :categoria_id, :forma_pagamento, :observacoes,
                    :recorrente, :regra_recorrencia, :parcelado, :qtd_parcelas, :pai_id, :created_by
                )
            ");

            $valorPago = $data['valor_pago'] ?? 0;
            $valorTotal = (float) ($data['valor_total'] ?? 0);
            $saldoAberto = $valorTotal - $valorPago;

            $status = 'ABERTO';
            if ($valorPago > 0) {
                $status = ($saldoAberto <= 0) ? 'PAGO' : 'PARCIAL';
            }

            $recorrente = !empty($data['recorrente']) ? 1 : 0;
            $parcelado = !empty($data['parcelado']) ? 1 : 0;
            $qtdParcelas = (int) ($data['qtd_parcelas'] ?? 1);
            if ($qtdParcelas < 1) {
                $qtdParcelas = 1;
            }

            $stmt->execute([
                'fornecedor_id' => !empty($data['supplier_id']) ? (int) $data['supplier_id'] : null,
                'numero_documento' => $data['numero_documento'] ?? null,
                'descricao' => $data['descricao'] ?? $data['description'] ?? '',
                'valor_total' => $valorTotal,
                'valor_pago' => $valorPago,
                'saldo_aberto' => $saldoAberto,
                'data_competencia' => $data['data_competencia'] ?? date('Y-m-d'),
                'data_vencimento' => $data['data_vencimento'] ?? date('Y-m-d'),
                'status' => $status,
                'categoria_id' => (int) ($data['categoria_id'] ?? 0),
                'forma_pagamento' => $data['forma_pagamento'] ?? null,
                'observacoes' => $data['observacoes'] ?? null,
                'recorrente' => $recorrente,
                'regra_recorrencia' => ($recorrente && !empty($data['regra_recorrencia'])) ? $data['regra_recorrencia'] : null,
                'parcelado' => $parcelado,
                'qtd_parcelas' => $qtdParcelas,
                'pai_id' => !empty($data['pai_id']) ? (int) $data['pai_id'] : null,
                'created_by' => $_SESSION['user_id'] ?? null
            ]);

            $payableId = $this->pdo->lastInsertId();

            if ($valorPago > 0 && !empty($data['conta_bancaria_id'])) {
                $movementModel = new CashMovement();
                $movementModel->create([
                    'tipo' => 'SAIDA',
                    'descricao' => "Pagto CP #$payableId - " . $data['descricao'],
                    'valor' => $valorPago,
                    'conta_bancaria_id' => $data['conta_bancaria_id'],
                    'origem' => 'PAGAMENTO_CP',
                    'referencia_id' => $payableId,
                    'usuario_id' => $_SESSION['user_id'] ?? 1
                ]);
            }

            if ($owner)
                $this->pdo->commit();
            return $payableId;
        } catch (Exception $e) {
            if ($owner && $this->pdo->inTransaction())
                $this->pdo->rollBack();
            throw $e;
        }
    }

    public function addPayment($payableId, $amount, $contaBancariaId, $formaPagamento)
    {
        $owner = false;
        try {
            if (!$this->pdo->inTransaction()) {
                $this->pdo->beginTransaction();
                $owner = true;
            }

            $cp = $this->getById($payableId);
            if (!$cp)
                throw new Exception("Conta a pagar não encontrada.");

            $newPaidAmount = $cp['valor_pago'] + $amount;
            $newSaldoAberto = $cp['valor_total'] - $newPaidAmount;
            $status = ($newSaldoAberto <= 0) ? 'PAGO' : 'PARCIAL';
            $dataPagamento = ($status === 'PAGO') ? date('Y-m-d') : null;

            $stmtUpdate = $this->pdo->prepare("
                UPDATE contas_pagar 
                SET valor_pago = :paid_amount, 
                    saldo_aberto = :saldo_aberto, 
                    status = :status,
                    data_pagamento = :data_pagamento
                WHERE id = :id
            ");
            $stmtUpdate->execute([
                'paid_amount' => $newPaidAmount,
                'saldo_aberto' => $newSaldoAberto,
                'status' => $status,
                'data_pagamento' => $dataPagamento,
                'id' => $payableId
            ]);

            $movementModel = new CashMovement();
            $movementModel->create([
                'tipo' => 'SAIDA',
                'descricao' => "Pagto CP #$payableId - " . $cp['descricao'],
                'valor' => $amount,
                'conta_bancaria_id' => $contaBancariaId,
                'origem' => 'PAGAMENTO_CP',
                'referencia_id' => $payableId,
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
            UPDATE contas_pagar 
            SET status = 'VENCIDO' 
            WHERE status IN ('ABERTO', 'PARCIAL') 
            AND data_vencimento < CURRENT_DATE()
        ");
        return $stmt->execute();
    }
}
