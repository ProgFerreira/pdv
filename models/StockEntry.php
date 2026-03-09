<?php

namespace App\Models;

class StockEntry
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function create($userId, $data)
    {
        try {
            $this->pdo->beginTransaction();

            $sectorId = $_SESSION['sector_id'] ?? 1;
            // 1. Create Entry Header
            $stmt = $this->pdo->prepare("
                INSERT INTO stock_entries (user_id, reference, supplier, supplier_id, purchase_id, total_amount, entry_date, notes, sector_id) 
                VALUES (:user_id, :reference, :supplier, :supplier_id, :purchase_id, :total_amount, :entry_date, :notes, :sector_id)
            ");
            $stmt->execute([
                'user_id' => $userId,
                'reference' => $data['reference'] ?? '',
                'supplier' => $data['supplier'] ?? '',
                'supplier_id' => $data['supplier_id'] ?? null,
                'purchase_id' => $data['purchase_id'] ?? null,
                'total_amount' => $data['total_amount'],
                'entry_date' => $data['entry_date'] ?? date('Y-m-d'),
                'notes' => $data['notes'] ?? '',
                'sector_id' => $sectorId
            ]);
            $entryId = $this->pdo->lastInsertId();

            $stmtItem = $this->pdo->prepare("
                INSERT INTO stock_entry_items (entry_id, product_id, quantity, cost_price) 
                VALUES (:entry_id, :product_id, :quantity, :cost_price)
            ");

            $stmtUpdateProduct = $this->pdo->prepare("
                UPDATE products SET stock = stock + :quantity, cost_price = :cost_price WHERE id = :id
            ");

            $stmtBatch = $this->pdo->prepare("
                INSERT INTO stock_batches (product_id, stock_entry_id, initial_quantity, current_quantity, cost_price) 
                VALUES (:product_id, :entry_id, :initial_quantity, :current_quantity, :cost_price)
            ");

            foreach ($data['items'] as $item) {
                // Insert Item (Line details)
                $stmtItem->execute([
                    'entry_id' => $entryId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'cost_price' => $item['cost_price']
                ]);

                // Create Batch for FIFO
                $stmtBatch->execute([
                    'product_id' => $item['product_id'],
                    'entry_id' => $entryId,
                    'initial_quantity' => $item['quantity'],
                    'current_quantity' => $item['quantity'],
                    'cost_price' => $item['cost_price']
                ]);

                // Update Product (Keep for backward compatibility and fast sum)
                $stmtUpdateProduct->execute([
                    'quantity' => $item['quantity'],
                    'cost_price' => $item['cost_price'],
                    'id' => $item['product_id']
                ]);
            }

            $this->pdo->commit();
            return $entryId;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function getAll()
    {
        $sectorId = $_SESSION['sector_id'] ?? 1;
        $sql = "SELECT se.*, u.name as user_name,
                (SELECT COUNT(*) FROM stock_entry_items WHERE entry_id = se.id) as items_count,
                (SELECT SUM(initial_quantity) FROM stock_batches WHERE stock_entry_id = se.id) as total_initial,
                (SELECT SUM(current_quantity) FROM stock_batches WHERE stock_entry_id = se.id) as total_current
                FROM stock_entries se
                LEFT JOIN users u ON se.user_id = u.id";

        $params = [];
        if ($sectorId !== 'all') {
            $sql .= " WHERE se.sector_id = :sectorId";
            $params['sectorId'] = $sectorId;
        }

        $sql .= " ORDER BY se.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT se.*, u.name as user_name 
            FROM stock_entries se 
            LEFT JOIN users u ON se.user_id = u.id 
            WHERE se.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $entry = $stmt->fetch();

        if ($entry) {
            $stmtItems = $this->pdo->prepare("
                SELECT sei.*, p.name as product_name, p.price as product_price, sb.initial_quantity, sb.current_quantity 
                FROM stock_entry_items sei 
                JOIN products p ON sei.product_id = p.id 
                LEFT JOIN stock_batches sb ON sb.stock_entry_id = sei.entry_id AND sb.product_id = sei.product_id
                WHERE sei.entry_id = :id
            ");
            $stmtItems->execute(['id' => $id]);
            $entry['items'] = $stmtItems->fetchAll();
        }

        return $entry;
    }

    public function updateHeader($id, $data)
    {
        $stmt = $this->pdo->prepare("
            UPDATE stock_entries 
            SET reference = :reference, 
                supplier = :supplier, 
                entry_date = :entry_date, 
                notes = :notes 
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'reference' => $data['reference'],
            'supplier' => $data['supplier'],
            'entry_date' => $data['entry_date'],
            'notes' => $data['notes']
        ]);
    }

    /**
     * Adiciona novos itens a uma nota de entrada existente.
     * Insere em stock_entry_items, stock_batches, atualiza products.stock e stock_entries.total_amount.
     *
     * @param int $entryId
     * @param array<int, array{product_id: int, quantity: int, cost_price: float}> $items
     * @return bool
     */
    public function addItems($entryId, array $items)
    {
        if (empty($items)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $stmtItem = $this->pdo->prepare("
                INSERT INTO stock_entry_items (entry_id, product_id, quantity, cost_price) 
                VALUES (:entry_id, :product_id, :quantity, :cost_price)
            ");
            $stmtUpdateProduct = $this->pdo->prepare("
                UPDATE products SET stock = stock + :quantity, cost_price = :cost_price WHERE id = :id
            ");
            $stmtBatch = $this->pdo->prepare("
                INSERT INTO stock_batches (product_id, stock_entry_id, initial_quantity, current_quantity, cost_price) 
                VALUES (:product_id, :entry_id, :initial_quantity, :current_quantity, :cost_price)
            ");

            $addTotal = 0.0;

            foreach ($items as $item) {
                $qty = (int) ($item['quantity'] ?? 0);
                $cost = (float) ($item['cost_price'] ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                $stmtItem->execute([
                    'entry_id' => $entryId,
                    'product_id' => $item['product_id'],
                    'quantity' => $qty,
                    'cost_price' => $cost
                ]);

                $stmtBatch->execute([
                    'product_id' => $item['product_id'],
                    'entry_id' => $entryId,
                    'initial_quantity' => $qty,
                    'current_quantity' => $qty,
                    'cost_price' => $cost
                ]);

                $stmtUpdateProduct->execute([
                    'quantity' => $qty,
                    'cost_price' => $cost,
                    'id' => $item['product_id']
                ]);

                $addTotal += $qty * $cost;
            }

            if ($addTotal <= 0) {
                $this->pdo->rollBack();
                return false;
            }

            $stmtUpdateEntry = $this->pdo->prepare("
                UPDATE stock_entries SET total_amount = total_amount + :add_total WHERE id = :id
            ");
            $stmtUpdateEntry->execute([
                'add_total' => $addTotal,
                'id' => $entryId
            ]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $this->pdo->beginTransaction();

            // 1. Fetch items to revert stock
            $stmtItems = $this->pdo->prepare("SELECT product_id, quantity FROM stock_entry_items WHERE entry_id = :id");
            $stmtItems->execute(['id' => $id]);
            $items = $stmtItems->fetchAll();

            $stmtUpdateProduct = $this->pdo->prepare("UPDATE products SET stock = stock - :quantity WHERE id = :id");

            foreach ($items as $item) {
                $stmtUpdateProduct->execute([
                    'quantity' => $item['quantity'],
                    'id' => $item['product_id']
                ]);
            }

            // 2. Delete the entry (Cascades to stock_entry_items and stock_batches)
            $stmtDelete = $this->pdo->prepare("DELETE FROM stock_entries WHERE id = :id");
            $stmtDelete->execute(['id' => $id]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
}
