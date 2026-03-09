<?php

namespace App\Models;

class InvestmentLoanPayment
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getByInvestimentoId(int $investimentoId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, investimento_id, data_pagamento, valor_pago, forma_pagamento, comprovante, observacao, created_by, created_at
            FROM investment_loan_payments
            WHERE investimento_id = :id
            ORDER BY data_pagamento ASC, id ASC
        ");
        $stmt->execute(['id' => $investimentoId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getTotalPago(int $investimentoId): float
    {
        $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(valor_pago), 0) AS total FROM investment_loan_payments WHERE investimento_id = :id");
        $stmt->execute(['id' => $investimentoId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (float) ($row['total'] ?? 0);
    }

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO investment_loan_payments (investimento_id, data_pagamento, valor_pago, forma_pagamento, comprovante, observacao, created_by)
            VALUES (:investimento_id, :data_pagamento, :valor_pago, :forma_pagamento, :comprovante, :observacao, :created_by)
        ");
        return $stmt->execute([
            'investimento_id'  => (int) $data['investimento_id'],
            'data_pagamento'   => $data['data_pagamento'],
            'valor_pago'       => (float) ($data['valor_pago'] ?? 0),
            'forma_pagamento'  => trim($data['forma_pagamento'] ?? '') ?: null,
            'comprovante'      => trim($data['comprovante'] ?? '') ?: null,
            'observacao'       => trim($data['observacao'] ?? '') ?: null,
            'created_by'       => isset($data['created_by']) ? (int) $data['created_by'] : null,
        ]);
    }
}
