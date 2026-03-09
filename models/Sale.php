<?php

namespace App\Models;

class Sale
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function create($userId, $cart, $paymentMethod, $amountPaid, $change, $customerId = null, $cashRegisterId = null, $discountAmount = 0, $giftCardId = null, $deliveryAddress = null)
    {
        $sectorId = $_SESSION['sector_id'] ?? 1;

        if ($sectorId === 'all') {
            if (!empty($cart)) {
                $productModel = new Product();
                $firstItem = $productModel->getById($cart[0]['id']);
                $sectorId = $firstItem['sector_id'] ?? 1;
            } else {
                $sectorId = 1;
            }
        }

        try {
            $this->pdo->beginTransaction();

            $giftCardModel = new GiftCard();

            // 1. Calculate Subtotal
            $subtotal = 0;
            foreach ($cart as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }

            // Apply Discount
            $total = $subtotal - $discountAmount;
            if ($total < 0)
                $total = 0;

            // 2. Insert Sale (delivery_address = endereço de entrega para imprimir no cupom)
            $stmt = $this->pdo->prepare("
                INSERT INTO sales (user_id, customer_id, delivery_address, cash_register_id, total, discount_amount, payment_method, amount_paid, change_amount, sector_id) 
                VALUES (:user_id, :customer_id, :delivery_address, :cash_register_id, :total, :discount_amount, :payment_method, :amount_paid, :change_amount, :sector_id)
            ");
            $stmt->execute([
                'user_id' => $userId,
                'customer_id' => $customerId,
                'delivery_address' => $deliveryAddress ?: null,
                'cash_register_id' => $cashRegisterId,
                'total' => $total,
                'discount_amount' => $discountAmount,
                'payment_method' => $paymentMethod,
                'amount_paid' => $amountPaid,
                'change_amount' => $change,
                'sector_id' => $sectorId
            ]);
            $saleId = $this->pdo->lastInsertId();

            // 3. New Integration: Generate Receivable (opcional: falha não invalida a venda)
            try {
                $receivableModel = new Receivable();
                $cat = $this->pdo->query("SELECT id FROM plano_contas WHERE nome = 'Vendas Balcão' LIMIT 1")->fetch();
                $categoryId = $cat['id'] ?? null;
                $acc = $this->pdo->query("SELECT id FROM contas_bancarias WHERE nome = 'CAIXA PDV' LIMIT 1")->fetch();
                $accountId = $acc['id'] ?? null;

                $receivableData = [
                    'cliente_id' => $customerId,
                    'origem' => 'PDV',
                    'descricao' => "Venda #$saleId",
                    'valor_total' => $total,
                    'data_competencia' => date('Y-m-d'),
                    'data_vencimento' => ($paymentMethod === 'A Prazo') ? date('Y-m-d', strtotime('+30 days')) : date('Y-m-d'),
                    'forma_recebimento' => $paymentMethod,
                    'categoria_id' => $categoryId,
                    'pdv_venda_id' => $saleId,
                    'conta_bancaria_id' => $accountId
                ];
                if ($paymentMethod !== 'A Prazo') {
                    $receivableData['valor_recebido'] = $total;
                } else {
                    $receivableData['valor_recebido'] = $amountPaid;
                }
                $receivableModel->create($receivableData);
            } catch (\Throwable $e) {
                $logDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
                if (!is_dir($logDir)) {
                    @mkdir($logDir, 0755, true);
                }
                @file_put_contents($logDir . DIRECTORY_SEPARATOR . 'error.log', date('Y-m-d H:i:s') . ' [Sale::create receivable] ' . $e->getMessage() . "\n", FILE_APPEND | LOCK_EX);
            }

            // 4. Itens da venda e baixa de estoque
            $stmtItem = $this->pdo->prepare("
                INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, cost_price, subtotal) 
                VALUES (:sale_id, :product_id, :quantity, :unit_price, :cost_price, :subtotal)
            ");

            $stmtStock = $this->pdo->prepare("
                UPDATE products SET stock = stock - :quantity WHERE id = :id
            ");

            $productModel = new Product();
            $useBatches = false;
            try {
                $chk = $this->pdo->query("SHOW TABLES LIKE 'stock_batches'");
                $useBatches = $chk && $chk->rowCount() > 0;
            } catch (\Throwable $e) {
            }
            $stmtBatch = $useBatches ? $this->pdo->prepare("
                SELECT id, current_quantity FROM stock_batches 
                WHERE product_id = :product_id AND current_quantity > 0 
                ORDER BY created_at ASC
            ") : null;
            $stmtUpdateBatch = $useBatches ? $this->pdo->prepare("
                UPDATE stock_batches SET current_quantity = current_quantity - :deduction WHERE id = :id
            ") : null;

            foreach ($cart as $item) {
                $subtotalItem = $item['price'] * $item['quantity'];
                $prod = $productModel->getById($item['id']);
                $cost = $prod['cost_price'] ?? 0;

                $stmtItem->execute([
                    'sale_id' => $saleId,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'cost_price' => $cost,
                    'subtotal' => $subtotalItem
                ]);

                if (!empty($prod['is_gift_card'])) {
                    $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
                    $giftCardModel->create([
                        'code' => $code,
                        'amount' => $subtotalItem,
                        'customer_id' => $customerId,
                        'sale_id' => $saleId
                    ]);
                } else {
                    if ($useBatches && $stmtBatch && $stmtUpdateBatch) {
                        $remainingToDeduct = $item['quantity'];
                        $stmtBatch->execute(['product_id' => $item['id']]);
                        $batches = $stmtBatch->fetchAll();
                        foreach ($batches as $batch) {
                            if ($remainingToDeduct <= 0) break;
                            $deduction = min($remainingToDeduct, $batch['current_quantity']);
                            $stmtUpdateBatch->execute(['deduction' => $deduction, 'id' => $batch['id']]);
                            $remainingToDeduct -= $deduction;
                        }
                    }
                    $stmtStock->execute([
                        'quantity' => $item['quantity'],
                        'id' => $item['id']
                    ]);
                }
            }

            if ($paymentMethod === 'Vale Presente' && $giftCardId) {
                if (!$giftCardModel->use($giftCardId, $total, $saleId)) {
                    throw new Exception("Falha ao utilizar Vale Presente.");
                }
            }

            $this->pdo->commit();
            return $saleId;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT s.*, c.name as customer_name, c.phone as customer_phone
            FROM sales s
            LEFT JOIN customers c ON s.customer_id = c.id
            WHERE s.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $sale = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($sale) {
            $stmtItems = $this->pdo->prepare("
                SELECT si.*, p.name as product_name, p.image as product_image 
                FROM sale_items si 
                JOIN products p ON si.product_id = p.id 
                WHERE si.sale_id = :sale_id
            ");
            $stmtItems->execute(['sale_id' => $id]);
            $sale['items'] = $stmtItems->fetchAll();
        }
        return $sale;
    }

    public function getAll($limit = 100, $offset = 0, $filters = [])
    {
        $sessionSectorId = $_SESSION['sector_id'] ?? 1;
        $sql = "SELECT s.*, u.name as user_name, c.name as customer_name, sec.name as sector_name
                FROM sales s
                LEFT JOIN users u ON s.user_id = u.id
                LEFT JOIN customers c ON s.customer_id = c.id
                LEFT JOIN sectors sec ON s.sector_id = sec.id
                WHERE 1=1 AND (COALESCE(s.status, 'completed') = 'completed' OR s.status = 'cancelled')";

        $params = [];
        $targetSector = $filters['sector_id'] ?? $sessionSectorId;

        if ($_SESSION['user_role'] !== 'admin') {
            $sql .= " AND s.sector_id = :sectorId";
            $params['sectorId'] = $_SESSION['sector_id'];
        } elseif ($targetSector && $targetSector !== 'all') {
            $sql .= " AND s.sector_id = :sectorId";
            $params['sectorId'] = $targetSector;
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(s.created_at) >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(s.created_at) <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }

        // New Filters
        if (!empty($filters['cash_register_id'])) {
            $sql .= " AND s.cash_register_id = :cash_id";
            $params['cash_id'] = $filters['cash_register_id'];
        }

        if (!empty($filters['payment_method'])) {
            $sql .= " AND s.payment_method = :pay_method";
            $params['pay_method'] = $filters['payment_method'];
        }

        if (!empty($filters['customer_query'])) {
            $sql .= " AND (c.name LIKE :c_query OR s.customer_id = :c_id)";
            $params['c_query'] = "%" . $filters['customer_query'] . "%";
            $params['c_id'] = $filters['customer_query'];
        }

        $sql .= " ORDER BY s.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', (int) $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, \PDO::PARAM_INT);
        foreach ($params as $key => $val) {
            $stmt->bindValue(':' . $key, $val);
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getTotals($filters = [])
    {
        $sessionSectorId = $_SESSION['sector_id'] ?? 1;
        $sql = "SELECT COUNT(s.id) as count, SUM(s.total) as revenue,
                       SUM((SELECT SUM(si.cost_price * si.quantity) FROM sale_items si WHERE si.sale_id = s.id)) as total_costs,
                       SUM(s.total - (SELECT SUM(si.cost_price * si.quantity) FROM sale_items si WHERE si.sale_id = s.id)) as profit
                FROM sales s
                LEFT JOIN customers c ON s.customer_id = c.id
                WHERE 1=1 AND COALESCE(s.status, 'completed') = 'completed'";

        $params = [];
        $targetSector = $filters['sector_id'] ?? $sessionSectorId;

        if ($_SESSION['user_role'] !== 'admin') {
            $sql .= " AND s.sector_id = :sectorId";
            $params['sectorId'] = $_SESSION['sector_id'];
        } elseif ($targetSector && $targetSector !== 'all') {
            $sql .= " AND s.sector_id = :sectorId";
            $params['sectorId'] = $targetSector;
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(s.created_at) >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(s.created_at) <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }

        if (!empty($filters['cash_register_id'])) {
            $sql .= " AND s.cash_register_id = :cash_id";
            $params['cash_id'] = $filters['cash_register_id'];
        }

        if (!empty($filters['payment_method'])) {
            $sql .= " AND s.payment_method = :pay_method";
            $params['pay_method'] = $filters['payment_method'];
        }

        if (!empty($filters['customer_query'])) {
            $sql .= " AND (c.name LIKE :c_query OR s.customer_id = :c_id)";
            $params['c_query'] = "%" . $filters['customer_query'] . "%";
            $params['c_id'] = $filters['customer_query'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Quantidade de produtos vendidos por produto (respeitando os mesmos filtros da listagem de vendas).
     * Retorna: product_id, product_name, quantity, subtotal
     *
     * @param array $filters start_date, end_date, sector_id, cash_register_id, payment_method, customer_query
     * @return array
     */
    public function getQuantitySoldByProduct($filters = [])
    {
        $sessionSectorId = $_SESSION['sector_id'] ?? 1;
        $sql = "SELECT p.id as product_id, p.name as product_name,
                       SUM(si.quantity) as quantity,
                       SUM(si.subtotal) as subtotal
                FROM sale_items si
                JOIN sales s ON si.sale_id = s.id
                JOIN products p ON si.product_id = p.id
                LEFT JOIN customers c ON s.customer_id = c.id
                WHERE 1=1 AND COALESCE(s.status, 'completed') = 'completed'";

        $params = [];
        $targetSector = $filters['sector_id'] ?? $sessionSectorId;

        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] !== 'admin') {
            $sql .= " AND s.sector_id = :sectorId";
            $params['sectorId'] = $_SESSION['sector_id'];
        } elseif ($targetSector && $targetSector !== 'all') {
            $sql .= " AND s.sector_id = :sectorId";
            $params['sectorId'] = $targetSector;
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(s.created_at) >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(s.created_at) <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }

        if (!empty($filters['cash_register_id'])) {
            $sql .= " AND s.cash_register_id = :cash_id";
            $params['cash_id'] = $filters['cash_register_id'];
        }

        if (!empty($filters['payment_method'])) {
            $sql .= " AND s.payment_method = :pay_method";
            $params['pay_method'] = $filters['payment_method'];
        }

        if (!empty($filters['customer_query'])) {
            $sql .= " AND (c.name LIKE :c_query OR s.customer_id = :c_id)";
            $params['c_query'] = "%" . $filters['customer_query'] . "%";
            $params['c_id'] = $filters['customer_query'];
        }

        $sql .= " GROUP BY p.id, p.name ORDER BY quantity DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Cancela uma venda: estorna estoque, movimentações de caixa, fiado e vale.
     * Registra status cancelled, cancelled_at, cancelled_by.
     */
    public function cancel(int $saleId, int $userId): bool
    {
        $sale = $this->getById($saleId);
        if (!$sale || (isset($sale['status']) && $sale['status'] === 'cancelled')) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // 1. Stock Reversal
            $stmtStock = $this->pdo->prepare("UPDATE products SET stock = stock + :qty WHERE id = :id");
            foreach ($sale['items'] as $item) {
                $stmtStock->execute(['qty' => (int) $item['quantity'], 'id' => (int) $item['product_id']]);
            }

            // 2. Financial Reversal
            $receivableModel = new Receivable();
            $stmtCr = $this->pdo->prepare("SELECT * FROM contas_receber WHERE pdv_venda_id = :sale_id");
            $stmtCr->execute(['sale_id' => $saleId]);
            $cr = $stmtCr->fetch();

            if ($cr) {
                // If anything was received, reverse it in cash movements
                if ((float) $cr['valor_recebido'] > 0) {
                    $movementModel = new CashMovement();
                    $movementModel->create([
                        'tipo' => 'SAIDA',
                        'descricao' => "Estorno Venda #$saleId (CR #{$cr['id']})",
                        'valor' => $cr['valor_recebido'],
                        'conta_bancaria_id' => $cr['conta_bancaria_id'] ?? 1,
                        'origem' => 'AJUSTE',
                        'referencia_id' => $cr['id'],
                        'usuario_id' => $userId
                    ]);
                }

                // Mark receivable as cancelled
                $this->pdo->prepare("UPDATE contas_receber SET status = 'CANCELADO', saldo_aberto = 0 WHERE id = :id")
                    ->execute(['id' => $cr['id']]);
            }

            // 3. Gift Card Reversal
            if ($sale['payment_method'] === 'Vale Presente') {
                $stmtLogs = $this->pdo->prepare("
                    SELECT gift_card_id, amount FROM gift_card_logs WHERE sale_id = :id AND type = 'debit'
                ");
                $stmtLogs->execute(['id' => $saleId]);
                $debits = $stmtLogs->fetchAll();
                foreach ($debits as $d) {
                    $this->pdo->prepare("
                        UPDATE gift_cards SET balance = balance + :amt, status = 'active' WHERE id = :id
                    ")->execute(['amt' => (float) $d['amount'], 'id' => (int) $d['gift_card_id']]);
                }
            }

            // 4. Mark Sale as Cancelled
            $stmt = $this->pdo->prepare("
                UPDATE sales SET status = 'cancelled', cancelled_at = NOW(), cancelled_by = :uid WHERE id = :id
            ");
            $stmt->execute(['uid' => $userId, 'id' => $saleId]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
}
