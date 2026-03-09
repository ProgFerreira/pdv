<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\CashRegister;

/**
 * Orquestra criação de venda: itens, pagamento, caixa, (recebíveis se a prazo).
 */
class SaleService
{
    public function __construct(
        private Sale $sale,
        private CashRegister $cashRegister
    ) {
    }

    public static function create(): self
    {
        return new self(new Sale(), new CashRegister());
    }

    /**
     * Cria uma venda no PDV (carrinho, forma pagamento, caixa aberto).
     * @param int $userId
     * @param array $cart Array de itens { id, name, price, quantity, stock }
     * @return int|false saleId ou false
     */
    public function createSale(int $userId, array $cart, string $paymentMethod, float $amountPaid, float $change, ?int $customerId, ?int $cashRegisterId, float $discount = 0, ?int $giftCardId = null)
    {
        if (!$cashRegisterId) {
            return false;
        }
        return $this->sale->create($userId, $cart, $paymentMethod, $amountPaid, $change, $customerId, $cashRegisterId, $discount, $giftCardId);
    }

    public function getOpenRegister(int $userId): ?array
    {
        return $this->cashRegister->getOpenRegister($userId) ?: null;
    }

    public function getSaleById(int $id): ?array
    {
        return $this->sale->getById($id) ?: null;
    }

    public function cancelSale(int $saleId, int $userId): bool
    {
        return $this->sale->cancel($saleId, $userId);
    }
}
