<?php

namespace App\Services;

use App\Models\Payable;
use App\Models\Receivable;
use App\Models\PlanoContas;
use App\Models\FinancialAccount;

/**
 * Orquestra operações financeiras: contas a pagar/receber, pagamentos, listagens por competência/caixa.
 */
class FinanceService
{
    public function __construct(
        private Payable $payable,
        private Receivable $receivable
    ) {
    }

    public static function create(): self
    {
        return new self(new Payable(), new Receivable());
    }

    public function listPayables(array $filters = []): array
    {
        return $this->payable->getAll($filters);
    }

    public function listReceivables(array $filters = []): array
    {
        return $this->receivable->getAll($filters);
    }

    public function registerPayablePayment(int $id, float $amount, ?int $contaBancariaId, string $formaPagamento): bool
    {
        return $this->payable->addPayment($id, $amount, $contaBancariaId, $formaPagamento);
    }

    public function cancelPayable(int $id): bool
    {
        global $pdo;
        $stmt = $pdo->prepare("UPDATE contas_pagar SET status = 'CANCELADO', saldo_aberto = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getPayableById(int $id): ?array
    {
        return $this->payable->getById($id) ?: null;
    }

    public function getReceivableById(int $id): ?array
    {
        return $this->receivable->getById($id) ?: null;
    }
}
