<?php

namespace App\Controllers;

use App\Models\CustomerOrder;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\CashRegister;
use App\Models\AuditLog;

class OrderController
{
    /**
     * Página pública: formulário de pedido pelo link (produtos + carrinho + dados).
     */
    public function form()
    {
        $sectorId = isset($_GET['sector_id']) ? (int) $_GET['sector_id'] : null;
        $products = CustomerOrder::getProductsForPublic($sectorId);
        $baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        foreach ($products as &$p) {
            if (!empty($p['image'])) {
                $p['image'] = $baseUrl . '/' . ltrim(str_replace('\\', '/', $p['image']), '/');
            }
        }
        unset($p);
        require 'views/order/form.php';
    }

    /**
     * POST público: envia o pedido (itens + dados do cliente). Retorna JSON.
     */
    public function submit()
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
            exit;
        }

        $input = $GLOBALS['_JSON_BODY'] ?? [];
        if (empty($input) && !empty($_POST)) {
            $input = $_POST;
            if (!empty($input['items_json'])) {
                $decoded = json_decode($input['items_json'], true);
                $input['items'] = is_array($decoded) ? $decoded : [];
                unset($input['items_json']);
            }
        }
        $input = is_array($input) ? $input : [];

        $items = $input['items'] ?? [];
        if (empty($items)) {
            echo json_encode(['success' => false, 'message' => 'Adicione ao menos um item ao pedido.']);
            exit;
        }

        $data = [
            'items' => $items,
            'customer_id' => isset($input['customer_id']) ? (int) $input['customer_id'] : null,
            'guest_name' => trim((string) ($input['guest_name'] ?? '')),
            'guest_phone' => trim((string) ($input['guest_phone'] ?? '')),
            'guest_email' => trim((string) ($input['guest_email'] ?? '')),
            'delivery_address' => trim((string) ($input['delivery_address'] ?? '')),
            'is_pickup' => !empty($input['is_pickup']),
            'observation' => trim((string) ($input['observation'] ?? '')),
            'sector_id' => isset($input['sector_id']) && (int) $input['sector_id'] > 0 ? (int) $input['sector_id'] : null,
            'token' => $input['token'] ?? null,
        ];

        if ($data['customer_id'] === 0) {
            $data['customer_id'] = null;
        }
        if ($data['customer_id'] === null && $data['guest_name'] === '') {
            echo json_encode(['success' => false, 'message' => 'Informe seu nome.']);
            exit;
        }

        $orderModel = new CustomerOrder();
        $orderId = $orderModel->create($data);
        if ($orderId) {
            echo json_encode(['success' => true, 'order_id' => $orderId, 'message' => 'Pedido enviado com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Não foi possível registrar o pedido. Tente novamente.']);
        }
        exit;
    }

    /**
     * Listagem de pedidos (operador logado).
     */
    public function index()
    {
        $orderModel = new CustomerOrder();
        $filters = [
            'status' => $_GET['status'] ?? 'pending',
            'start_date' => $_GET['start_date'] ?? date('Y-m-d'),
            'end_date' => $_GET['end_date'] ?? date('Y-m-d'),
        ];
        $orders = $orderModel->getAll(50, 0, $filters);
        require 'views/order/index.php';
    }

    /**
     * Detalhe do pedido (operador).
     */
    public function view()
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . '?route=order/index');
            exit;
        }
        $orderModel = new CustomerOrder();
        $order = $orderModel->getById($id);
        if (!$order) {
            header('Location: ' . BASE_URL . '?route=order/index');
            exit;
        }
        $items = $orderModel->getItems($id);
        require 'views/order/view.php';
    }

    /**
     * Converte pedido em venda (operador). Exige caixa aberto.
     */
    public function convertToSale()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?route=order/index');
            exit;
        }
        $orderId = (int) ($_POST['order_id'] ?? $_GET['order_id'] ?? 0);
        if ($orderId <= 0) {
            header('Location: ' . BASE_URL . '?route=order/index&error=invalid');
            exit;
        }

        $orderModel = new CustomerOrder();
        $order = $orderModel->getById($orderId);
        if (!$order || ($order['status'] ?? '') !== 'pending') {
            header('Location: ' . BASE_URL . '?route=order/index&error=order_invalid');
            exit;
        }

        $items = $orderModel->getItems($orderId);
        if (empty($items)) {
            header('Location: ' . BASE_URL . '?route=order/view&id=' . $orderId . '&error=empty');
            exit;
        }

        $cashModel = new CashRegister();
        $register = $cashModel->getOpenRegister($_SESSION['user_id']);
        if (!$register) {
            header('Location: ' . BASE_URL . '?route=order/view&id=' . $orderId . '&error=caixa_fechado');
            exit;
        }

        $customerId = !empty($order['customer_id']) ? (int) $order['customer_id'] : null;
        if ($customerId === null) {
            $customerModel = new Customer();
            $customerId = $customerModel->create([
                'name' => $order['guest_name'] ?? 'Cliente link',
                'phone' => $order['guest_phone'] ?? null,
                'email' => $order['guest_email'] ?? null,
                'address' => $order['delivery_address'] ?? null,
            ]);
            if (!$customerId) {
                header('Location: ' . BASE_URL . '?route=order/view&id=' . $orderId . '&error=customer');
                exit;
            }
        }

        $cart = [];
        foreach ($items as $row) {
            $cart[] = [
                'id' => (int) $row['product_id'],
                'price' => (float) $row['unit_price'],
                'quantity' => (int) $row['quantity'],
            ];
        }

        $sectorBackup = $_SESSION['sector_id'] ?? null;
        if (!empty($order['sector_id'])) {
            $_SESSION['sector_id'] = (int) $order['sector_id'];
        }
        $saleModel = new Sale();
        $saleId = $saleModel->create(
            (int) $_SESSION['user_id'],
            $cart,
            'A definir',
            (float) $order['total'],
            0,
            $customerId,
            (int) $register['id'],
            0,
            null,
            $order['delivery_address'] ?? null,
            !empty($order['is_pickup']),
            $order['observation'] ?? null
        );
        if ($sectorBackup !== null) {
            $_SESSION['sector_id'] = $sectorBackup;
        }

        if (!$saleId) {
            header('Location: ' . BASE_URL . '?route=order/view&id=' . $orderId . '&error=sale');
            exit;
        }

        $orderModel->markConverted($orderId, $saleId);
        $audit = new AuditLog();
        $audit->log('order_convert', 'customer_order', $orderId, ['sale_id' => $saleId]);

        header('Location: ' . BASE_URL . '?route=sale/view&id=' . $saleId . '&success=from_order');
        exit;
    }
}
