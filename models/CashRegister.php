<?php

namespace App\Models;

class CashRegister
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    // Check if user has an open register
    public function getOpenRegister($userId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM cash_registers WHERE user_id = :user_id AND status = 'open' LIMIT 1");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch();
    }

    public function open($userId, $initialAmount)
    {
        try {
            $this->pdo->beginTransaction();

            // 1. Create Register
            $stmt = $this->pdo->prepare("
                INSERT INTO cash_registers (user_id, opening_balance, status) 
                VALUES (:user_id, :opening_balance, 'open')
            ");
            $stmt->execute([
                'user_id' => $userId,
                'opening_balance' => $initialAmount
            ]);
            $id = $this->pdo->lastInsertId();

            // 2. Log Opening Movement
            $this->addMovement($id, 'opening', $initialAmount, 'Abertura de Caixa');

            $this->pdo->commit();
            return $id;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function close($id, $closingBalance, $notes = '')
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("
                UPDATE cash_registers
                SET closing_balance = :closing_balance, status = 'closed', closed_at = NOW(), notes = :notes
                WHERE id = :id
            ");
            $stmt->execute([
                'id' => $id,
                'closing_balance' => $closingBalance,
                'notes' => $notes
            ]);

            // Log Closing (optional, strictly speaking closing balance isn't a movement but we check diffs elsewhere)
            // Or we can verify the difference here.

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function addMovement($registerId, $type, $amount, $description = '')
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO cash_movements (cash_register_id, type, amount, description)
            VALUES (:register_id, :type, :amount, :description)
        ");
        return $stmt->execute([
            'register_id' => $registerId,
            'type' => $type,
            'amount' => $amount,
            'description' => $description
        ]);
    }

    public function getSummary($id)
    {
        // Totais de movimentos (abertura, suprimento, sangria, despesa)
        $stmt = $this->pdo->prepare("
            SELECT 
                SUM(CASE WHEN type = 'opening' THEN amount ELSE 0 END) as opening,
                SUM(CASE WHEN type = 'sale' THEN amount ELSE 0 END) as sales_movements,
                SUM(CASE WHEN type = 'supply' THEN amount ELSE 0 END) as supply,
                SUM(CASE WHEN type = 'bleed' THEN amount ELSE 0 END) as bleed,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
            FROM cash_movements 
            WHERE cash_register_id = :id
        ");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];

        // Vendas reais vêm da tabela sales (PDV não grava em cash_movements)
        $stmtSales = $this->pdo->prepare("SELECT COALESCE(SUM(total), 0) as total FROM sales WHERE cash_register_id = :id");
        $stmtSales->execute(['id' => $id]);
        $rowSales = $stmtSales->fetch(\PDO::FETCH_ASSOC);
        $data['sales'] = (float) ($rowSales['total'] ?? 0);

        $data['current_balance'] = ($data['opening'] ?? 0) + ($data['sales'] ?? 0) + ($data['supply'] ?? 0) - ($data['bleed'] ?? 0) - ($data['expense'] ?? 0);
        return $data;
    }

    public function getSalesByPaymentMethod($registerId)
    {
        $stmt = $this->pdo->prepare("
            SELECT payment_method, COUNT(*) as count, SUM(total) as total
            FROM sales
            WHERE cash_register_id = :registerId
            GROUP BY payment_method
        ");
        $stmt->execute(['registerId' => $registerId]);
        return $stmt->fetchAll();
    }

    public function getMovements($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM cash_movements WHERE cash_register_id = :id ORDER BY created_at DESC");
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT cr.*, u.name as user_name FROM cash_registers cr JOIN users u ON cr.user_id = u.id WHERE cr.id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getAll($filters = [])
    {
        $sql = "SELECT cr.*, u.name as user_name 
                FROM cash_registers cr 
                JOIN users u ON cr.user_id = u.id 
                WHERE 1=1";

        $params = [];

        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(cr.opened_at) >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(cr.opened_at) <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND cr.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND cr.status = :status";
            $params['status'] = $filters['status'];
        }

        $sql .= " ORDER BY cr.opened_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getTotals($filters = [])
    {
        $sql = "SELECT 
                    COUNT(*) as total_registers,
                    SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as total_open,
                    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as total_closed,
                    SUM(opening_balance) as total_opening,
                    SUM(COALESCE(closing_balance, 0)) as total_closing
                FROM cash_registers cr
                WHERE 1=1";

        $params = [];

        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(cr.opened_at) >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(cr.opened_at) <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND cr.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND cr.status = :status";
            $params['status'] = $filters['status'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        // Calcular total de vendas dos caixas filtrados
        $registerIds = [];
        $registers = $this->getAll($filters);
        foreach ($registers as $reg) {
            $registerIds[] = $reg['id'];
        }

        $totalSales = 0;
        if (!empty($registerIds)) {
            $placeholders = implode(',', array_fill(0, count($registerIds), '?'));
            $stmt = $this->pdo->prepare("SELECT SUM(total) as total FROM sales WHERE cash_register_id IN ($placeholders)");
            $stmt->execute($registerIds);
            $salesResult = $stmt->fetch();
            $totalSales = $salesResult['total'] ?? 0;
        }

        $result['total_sales'] = $totalSales;
        return $result;
    }

    public function getSales($registerId)
    {
        $stmt = $this->pdo->prepare("
            SELECT s.*, u.name as user_name, c.name as customer_name
            FROM sales s
            LEFT JOIN users u ON s.user_id = u.id
            LEFT JOIN customers c ON s.customer_id = c.id
            WHERE s.cash_register_id = :register_id
            ORDER BY s.created_at DESC
        ");
        $stmt->execute(['register_id' => $registerId]);
        return $stmt->fetchAll();
    }

    public function update($id, $data)
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE cash_registers
                SET opening_balance = :opening_balance,
                    closing_balance = :closing_balance,
                    notes = :notes
                WHERE id = :id
            ");
            return $stmt->execute([
                'id' => $id,
                'opening_balance' => $data['opening_balance'],
                'closing_balance' => $data['closing_balance'] ?? null,
                'notes' => $data['notes'] ?? ''
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            // Verificar se há vendas associadas
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM sales WHERE cash_register_id = :id");
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch();

            if ($result['count'] > 0) {
                return false; // Não pode excluir se houver vendas
            }

            // Deletar movimentos primeiro
            $stmt = $this->pdo->prepare("DELETE FROM cash_movements WHERE cash_register_id = :id");
            $stmt->execute(['id' => $id]);

            // Deletar o registro
            $stmt = $this->pdo->prepare("DELETE FROM cash_registers WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (Exception $e) {
            return false;
        }
    }
}
