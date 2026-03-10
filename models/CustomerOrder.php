<?php

namespace App\Models;

class CustomerOrder
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Lista produtos ativos para a página pública (sem depender de sessão).
     */
    public static function getProductsForPublic(?int $sectorId = null): array
    {
        global $pdo;
        $sql = "SELECT id, name, price, image FROM products WHERE active = 1";
        $params = [];
        if ($sectorId !== null && $sectorId > 0) {
            $sql .= " AND sector_id = :sector_id";
            $params['sector_id'] = $sectorId;
        }
        $sql .= " ORDER BY name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Cria pedido a partir do formulário público (itens + dados do cliente).
     * Retorna ID do pedido ou false.
     */
    public function create(array $data): int|false
    {
        $items = $data['items'] ?? [];
        if (empty($items)) {
            return false;
        }

        $customerId = isset($data['customer_id']) && (int) $data['customer_id'] > 0 ? (int) $data['customer_id'] : null;
        $guestName = trim((string) ($data['guest_name'] ?? ''));
        $guestPhone = trim((string) ($data['guest_phone'] ?? '')) ?: null;
        $guestEmail = trim((string) ($data['guest_email'] ?? '')) ?: null;
        $deliveryAddress = trim((string) ($data['delivery_address'] ?? '')) ?: null;
        $isPickup = !empty($data['is_pickup']);
        $observation = trim((string) ($data['observation'] ?? '')) ?: null;
        $sectorId = isset($data['sector_id']) && (int) $data['sector_id'] > 0 ? (int) $data['sector_id'] : null;
        $token = isset($data['token']) && $data['token'] !== '' ? substr(trim($data['token']), 0, 64) : null;

        $total = 0;
        foreach ($items as $row) {
            $qty = (int) ($row['quantity'] ?? 0);
            $price = (float) ($row['unit_price'] ?? 0);
            $total += $qty * $price;
        }

        if ($total <= 0) {
            return false;
        }

        if ($customerId === null && $guestName === '') {
            return false;
        }

        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("
                INSERT INTO customer_orders (token, customer_id, guest_name, guest_phone, guest_email, delivery_address, is_pickup, observation, total, status, sector_id)
                VALUES (:token, :customer_id, :guest_name, :guest_phone, :guest_email, :delivery_address, :is_pickup, :observation, :total, 'pending', :sector_id)
            ");
            $stmt->execute([
                'token' => $token,
                'customer_id' => $customerId,
                'guest_name' => $guestName ?: null,
                'guest_phone' => $guestPhone,
                'guest_email' => $guestEmail,
                'delivery_address' => $isPickup ? null : $deliveryAddress,
                'is_pickup' => $isPickup ? 1 : 0,
                'observation' => $observation,
                'total' => $total,
                'sector_id' => $sectorId,
            ]);
            $orderId = (int) $this->pdo->lastInsertId();

            $stmtItem = $this->pdo->prepare("
                INSERT INTO customer_order_items (customer_order_id, product_id, quantity, unit_price, subtotal)
                VALUES (:customer_order_id, :product_id, :quantity, :unit_price, :subtotal)
            ");
            $productModel = new Product();
            foreach ($items as $row) {
                $productId = (int) ($row['product_id'] ?? 0);
                $quantity = (int) ($row['quantity'] ?? 0);
                $unitPrice = (float) ($row['unit_price'] ?? 0);
                if ($productId <= 0 || $quantity <= 0) {
                    continue;
                }
                $prod = $productModel->getById($productId);
                if (!$prod) {
                    continue;
                }
                $price = $unitPrice > 0 ? $unitPrice : (float) ($prod['price'] ?? 0);
                $subtotal = $price * $quantity;
                $stmtItem->execute([
                    'customer_order_id' => $orderId,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'subtotal' => $subtotal,
                ]);
            }
            $this->pdo->commit();
            return $orderId;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT o.*, c.name AS customer_name, c.phone AS customer_phone
            FROM customer_orders o
            LEFT JOIN customers c ON o.customer_id = c.id
            WHERE o.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getItems(int $orderId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT i.*, p.name AS product_name
            FROM customer_order_items i
            JOIN products p ON p.id = i.product_id
            WHERE i.customer_order_id = :order_id
            ORDER BY i.id
        ");
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Lista pedidos para o operador (filtro por status e data).
     */
    public function getAll(int $limit = 50, int $offset = 0, array $filters = []): array
    {
        $sql = "SELECT o.*, c.name AS customer_name, c.phone AS customer_phone
                FROM customer_orders o
                LEFT JOIN customers c ON o.customer_id = c.id
                WHERE 1=1";
        $params = [];
        if (!empty($filters['status'])) {
            $sql .= " AND o.status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(o.created_at) >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(o.created_at) <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }
        $sql .= " ORDER BY o.created_at DESC LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function markConverted(int $orderId, int $saleId): bool
    {
        $stmt = $this->pdo->prepare("UPDATE customer_orders SET status = 'converted', sale_id = :sale_id, updated_at = NOW() WHERE id = :id");
        return $stmt->execute(['sale_id' => $saleId, 'id' => $orderId]);
    }

    public function cancel(int $orderId): bool
    {
        $stmt = $this->pdo->prepare("UPDATE customer_orders SET status = 'cancelled', updated_at = NOW() WHERE id = :id AND status = 'pending'");
        return $stmt->execute(['id' => $orderId]) && $stmt->rowCount() > 0;
    }
}
