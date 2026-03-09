<?php

namespace App\Services;

use App\Models\CashRegister;

/**
 * Orquestra abertura/fechamento de caixa e movimentações.
 */
class CashService
{
    public function __construct(private CashRegister $cashRegister)
    {
    }

    public static function create(): self
    {
        return new self(new CashRegister());
    }

    public function getOpenRegister(int $userId): ?array
    {
        return $this->cashRegister->getOpenRegister($userId) ?: null;
    }

    public function open(int $userId, float $openingBalance): ?array
    {
        $id = $this->cashRegister->open($userId, $openingBalance);
        return $id ? $this->cashRegister->getById($id) : null;
    }

    public function close(int $registerId, float $closingBalance, string $notes = ''): bool
    {
        return $this->cashRegister->close($registerId, $closingBalance, $notes);
    }

    public function getSummary(int $registerId): array
    {
        return $this->cashRegister->getSummary($registerId) ?: ['current_balance' => 0];
    }

    public function addMovement(int $registerId, string $type, float $amount, string $description): bool
    {
        return $this->cashRegister->addMovement($registerId, $type, $amount, $description);
    }
}
