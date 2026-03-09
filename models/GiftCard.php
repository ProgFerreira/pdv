<?php

namespace App\Models;

class GiftCard
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAll($filters = [])
    {
        $sql = "SELECT g.*, c.name as customer_name,
                       COALESCE(SUM(CASE WHEN gl.type = 'debit' THEN gl.amount ELSE 0 END), 0) as total_spent
                FROM gift_cards g 
                LEFT JOIN customers c ON g.customer_id = c.id 
                LEFT JOIN gift_card_logs gl ON gl.gift_card_id = g.id
                WHERE 1=1";

        $params = [];
        if (!empty($filters['query'])) {
            $sql .= " AND (g.code LIKE :query OR c.name LIKE :query)";
            $params['query'] = "%" . $filters['query'] . "%";
        }

        $sql .= " GROUP BY g.id ORDER BY g.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getByCode($code)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM gift_cards WHERE code = ?");
        $stmt->execute([$code]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO gift_cards (code, initial_value, balance, customer_id, sale_id, expiry_date) 
            VALUES (:code, :initial_value, :balance, :customer_id, :sale_id, :expiry_date)
        ");
        return $stmt->execute([
            'code' => $data['code'],
            'initial_value' => $data['amount'],
            'balance' => $data['amount'],
            'customer_id' => $data['customer_id'] ?? null,
            'sale_id' => $data['sale_id'] ?? null,
            'expiry_date' => $data['expiry_date'] ?? null
        ]);
    }

    public function update($id, $data)
    {
        $stmt = $this->pdo->prepare("
            UPDATE gift_cards 
            SET customer_id = :customer_id, 
                expiry_date = :expiry_date
            WHERE id = :id
        ");
        return $stmt->execute([
            'customer_id' => $data['customer_id'] ?? null,
            'expiry_date' => $data['expiry_date'] ?? null,
            'id' => $id
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM gift_cards WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function refund($id, $cashRegisterId)
    {
        try {
            $this->pdo->beginTransaction();

            $card = $this->getById($id);
            if (!$card || $card['status'] === 'refunded') {
                throw new Exception("Vale inválido ou já estornado.");
            }

            $refundAmount = $card['balance'];

            // 1. Mark as Refunded
            $stmt = $this->pdo->prepare("UPDATE gift_cards SET status = 'cancelled', balance = 0 WHERE id = ?");
            $stmt->execute([$id]);

            // 2. Log in Gift Card Logs
            $stmtLog = $this->pdo->prepare("
                INSERT INTO gift_card_logs (gift_card_id, type, amount) 
                VALUES (?, 'refund', ?)
            ");
            $stmtLog->execute([$id, $refundAmount]);

            // 3. Cash Register Movement (Out)
            if ($cashRegisterId) {
                $stmtMove = $this->pdo->prepare("
                    INSERT INTO cash_movements (cash_register_id, type, amount, description) 
                    VALUES (?, 'expense', ?, ?)
                ");
                $stmtMove->execute([$cashRegisterId, $refundAmount, "Estorno Vale Presente #{$card['code']}"]);
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function use($id, $amount, $saleId)
    {
        // 1. Debit from Card
        $stmt = $this->pdo->prepare("
            UPDATE gift_cards 
            SET balance = balance - :amount, 
                status = IF(balance - :amount <= 0, 'used', 'active') 
            WHERE id = :id AND balance >= :amount
        ");
        $res = $stmt->execute(['amount' => $amount, 'id' => $id]);

        if (!$res || $stmt->rowCount() == 0) {
            throw new Exception("Saldo insuficiente ou Vale inválido.");
        }

        // 2. Log Transaction
        $stmtLog = $this->pdo->prepare("
            INSERT INTO gift_card_logs (gift_card_id, sale_id, type, amount) 
            VALUES (:gift_card_id, :sale_id, 'debit', :amount)
        ");
        return $stmtLog->execute([
            'gift_card_id' => $id,
            'sale_id' => $saleId,
            'amount' => $amount
        ]);
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT g.*, c.name as customer_name 
            FROM gift_cards g 
            LEFT JOIN customers c ON g.customer_id = c.id 
            WHERE g.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getExpenses($giftCardId)
    {
        $stmt = $this->pdo->prepare("
            SELECT gl.*, s.id as sale_id, s.total as sale_total, s.created_at as sale_date,
                   s.payment_method, u.name as user_name
            FROM gift_card_logs gl
            LEFT JOIN sales s ON gl.sale_id = s.id
            LEFT JOIN users u ON s.user_id = u.id
            WHERE gl.gift_card_id = :gift_card_id AND gl.type = 'debit'
            ORDER BY gl.created_at DESC
        ");
        $stmt->execute(['gift_card_id' => $giftCardId]);
        return $stmt->fetchAll();
    }
}
