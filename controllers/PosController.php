<?php

namespace App\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\CashRegister;
use App\Models\AuditLog;

class PosController
{
    public function index()
    {
        $editSale = null;
        $canDiscount = hasPermission('pos_discount');

        if (isset($_GET['edit_sale'])) {
            $saleId = $_GET['edit_sale'];
            $saleModel = new Sale();
            $editSale = $saleModel->getById($saleId);
        }

        require 'views/pos/index.php';
    }

    public function search()
    {
        $term = $_GET['term'] ?? '';
        $productModel = new Product();
        $products = $productModel->search($term);

        $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        $products = array_map(function ($p) use ($base) {
            if (!empty($p['image'])) {
                $p['image'] = $base . '/' . ltrim(str_replace('\\', '/', $p['image']), '/');
            }
            return $p;
        }, $products);

        header('Content-Type: application/json');
        echo json_encode($products);
        exit;
    }

    public function checkout()
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
            exit;
        }

        try {
            // Read JSON input (pode vir de $GLOBALS['_JSON_BODY'] se o index.php já leu para CSRF)
            $input = $GLOBALS['_JSON_BODY'] ?? json_decode(file_get_contents('php://input'), true) ?? [];
            $input = is_array($input) ? $input : [];

            $cart = $input['cart'] ?? [];
            $paymentMethod = $input['paymentMethod'] ?? 'money';
            $amountPaid = $input['amountPaid'] ?? 0;
            $change = $input['change'] ?? 0;
            $discount = (float) ($input['discount'] ?? 0);
            $customerId = isset($input['customerId']) ? (int) $input['customerId'] : null;
            if ($customerId === 0) {
                $customerId = null;
            }
            $customerName = isset($input['customerName']) ? trim((string) $input['customerName']) : '';
            $isPickup = !empty($input['isPickup']);
            $deliveryAddress = $isPickup ? null : (isset($input['deliveryAddress']) ? trim((string) $input['deliveryAddress']) : null);
            $giftCardId = $input['giftCardId'] ?? null;
            $observation = isset($input['observation']) ? trim((string) $input['observation']) : null;
            if ($observation === '') {
                $observation = null;
            }

            // Se não há cliente selecionado mas foi digitado um nome, cria o cliente e associa à venda
            if ($customerId === null && $customerName !== '') {
                try {
                    $customerModel = new \App\Models\Customer();
                    $newId = $customerModel->create([
                        'name' => $customerName,
                        'phone' => null,
                        'email' => null,
                        'address' => null,
                    ]);
                    if ($newId) {
                        $customerId = (int) $newId;
                    }
                } catch (\Throwable $e) {
                    $logDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
                    if (!is_dir($logDir)) {
                        @mkdir($logDir, 0755, true);
                    }
                    @file_put_contents($logDir . DIRECTORY_SEPARATOR . 'error.log', date('Y-m-d H:i:s') . ' [checkout create customer] ' . $e->getMessage() . "\n", FILE_APPEND | LOCK_EX);
                }
            }

            if ($discount > 0 && !hasPermission('pos_discount')) {
                echo json_encode(['success' => false, 'message' => 'Você não tem permissão para aplicar desconto.']);
                exit;
            }

            if (empty($cart)) {
                echo json_encode(['success' => false, 'message' => 'Carrinho vazio']);
                exit;
            }

            $saleModel = new Sale();

            // Get Open Register
            $cashModel = new CashRegister();
            $register = $cashModel->getOpenRegister($_SESSION['user_id']);
            $cashRegisterId = $register ? $register['id'] : null;

            if (!$cashRegisterId) {
                echo json_encode(['success' => false, 'message' => 'Caixa fechado. Abra o caixa antes de vender.']);
                exit;
            }

            $saleId = $saleModel->create($_SESSION['user_id'], $cart, $paymentMethod, $amountPaid, $change, $customerId, $cashRegisterId, $discount, $giftCardId, $deliveryAddress, $isPickup, $observation);

            if ($saleId) {
                $audit = new AuditLog();
                $audit->log('sale_create', 'sale', (int) $saleId, [
                    'discount' => $discount,
                    'payment_method' => $paymentMethod,
                ]);
                echo json_encode(['success' => true, 'saleId' => $saleId]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao processar venda']);
            }
        } catch (\Throwable $e) {
            $logDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            @file_put_contents($logDir . DIRECTORY_SEPARATOR . 'error.log', date('Y-m-d H:i:s') . ' [checkout] ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND | LOCK_EX);
            $isProduction = (getenv('APP_ENV') ?: 'development') === 'production';
            $message = $isProduction ? 'Erro ao processar venda. Tente novamente.' : $e->getMessage();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $message]);
        }
        exit;
    }

    public function receipt()
    {
        $saleId = $_GET['id'] ?? 0;
        $saleModel = new Sale();
        $sale = $saleModel->getById($saleId);

        if (!$sale) {
            echo "Venda não encontrada.";
            exit;
        }

        require 'views/pos/receipt.php';
    }

    public function receipt_thermal()
    {
        $saleId = $_GET['id'] ?? 0;
        $saleModel = new Sale();
        $sale = $saleModel->getById($saleId);

        if (!$sale) {
            echo "Venda não encontrada.";
            exit;
        }

        // Garantir nome e telefone do cliente na notinha (getById já traz pelo JOIN; fallback se cliente foi removido)
        if (!empty($sale['customer_id']) && (empty($sale['customer_name']) || empty($sale['customer_phone']))) {
            global $pdo;
            $stmt = $pdo->prepare("SELECT name, phone FROM customers WHERE id = ?");
            $stmt->execute([$sale['customer_id']]);
            $cust = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($cust) {
                if (empty($sale['customer_name'])) $sale['customer_name'] = $cust['name'] ?? '';
                if (empty($sale['customer_phone'])) $sale['customer_phone'] = $cust['phone'] ?? '';
            }
        }

        require 'views/pos/receipt_thermal.php';
    }
}
