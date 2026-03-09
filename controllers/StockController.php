<?php

namespace App\Controllers;

use App\Models\StockEntry;
use App\Models\Payable;
use App\Models\Product;
use App\Models\Supplier;

class StockController
{
    public function index()
    {
        $stockModel = new StockEntry();
        $entries = $stockModel->getAll();
        require 'views/stock/index.php';
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stockModel = new StockEntry();

            $data = [
                'reference' => $_POST['reference'] ?? '',
                'supplier' => $_POST['supplier'] ?? '',
                'total_amount' => str_replace(',', '.', $_POST['total_amount']),
                'entry_date' => $_POST['entry_date'] ?? date('Y-m-d'),
                'notes' => $_POST['notes'] ?? '',
                'items' => []
            ];

            // Parse items from post
            if (isset($_POST['product_id']) && is_array($_POST['product_id'])) {
                foreach ($_POST['product_id'] as $index => $prodId) {
                    $data['items'][] = [
                        'product_id' => $prodId,
                        'quantity' => $_POST['quantity'][$index],
                        'cost_price' => str_replace(',', '.', $_POST['cost_price'][$index])
                    ];
                }
            }

            if ($stockModel->create($_SESSION['user_id'], $data)) {
                // Se marcado para criar registro no Contas a Pagar
                if (!empty($_POST['create_payable'])) {
                    $payableModel = new Payable();
                    $payableModel->create([
                        'description' => "Compra ref. " . ($data['reference'] ?: 'S/N') . " - " . ($data['supplier'] ?: 'Vários'),
                        'total_amount' => $data['total_amount'],
                        'due_date' => $_POST['due_date_payable'] ?: date('Y-m-d'),
                        'supplier_id' => !empty($_POST['supplier_id']) ? $_POST['supplier_id'] : null,
                        'sector_id' => $_SESSION['sector_id'],
                        'notes' => 'Gerado automaticamente via Entrada de Estoque'
                    ]);
                }

                header('Location: ' . BASE_URL . '?route=stock/index&success=1');
                exit;
            } else {
                $error = "Erro ao registrar entrada de estoque.";
            }
        }

        $productModel = new Product();
        $products = $productModel->getAll();
        $supplierModel = new Supplier();
        $suppliers = $supplierModel->getAll();

        require 'views/stock/create.php';
    }

    public function view()
    {
        $id = $_GET['id'] ?? 0;
        $stockModel = new StockEntry();
        $entry = $stockModel->getById($id);

        if (!$entry) {
            header('Location: ' . BASE_URL . '?route=stock/index');
            exit;
        }

        require 'views/stock/view.php';
    }

    public function edit()
    {
        $id = $_GET['id'] ?? 0;
        $stockModel = new StockEntry();
        $entry = $stockModel->getById($id);

        if (!$entry) {
            header('Location: ' . BASE_URL . '?route=stock/index');
            exit;
        }

        require 'views/stock/edit.php';
    }

    /**
     * GET: exibe formulário para adicionar mais produtos à nota.
     * POST: processa e insere os novos itens.
     */
    public function addItems()
    {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        $stockModel = new StockEntry();
        $entry = $stockModel->getById($id);

        if (!$entry) {
            header('Location: ' . BASE_URL . '?route=stock/index');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $items = [];
            if (isset($_POST['product_id']) && is_array($_POST['product_id'])) {
                foreach ($_POST['product_id'] as $index => $prodId) {
                    $items[] = [
                        'product_id' => (int) $prodId,
                        'quantity' => (int) ($_POST['quantity'][$index] ?? 0),
                        'cost_price' => (float) str_replace(',', '.', $_POST['cost_price'][$index] ?? '0')
                    ];
                }
            }

            if (empty($items)) {
                header('Location: ' . BASE_URL . '?route=stock/addItems&id=' . $id . '&error=no_items');
                exit;
            }

            if ($stockModel->addItems($id, $items)) {
                header('Location: ' . BASE_URL . '?route=stock/view&id=' . $id . '&success=items_added');
                exit;
            }

            header('Location: ' . BASE_URL . '?route=stock/addItems&id=' . $id . '&error=failed');
            exit;
        }

        require 'views/stock/add_items.php';
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            $stockModel = new StockEntry();

            $data = [
                'reference' => $_POST['reference'] ?? '',
                'supplier' => $_POST['supplier'] ?? '',
                'entry_date' => $_POST['entry_date'] ?? date('Y-m-d'),
                'notes' => $_POST['notes'] ?? ''
            ];

            if ($stockModel->updateHeader($id, $data)) {
                header('Location: ' . BASE_URL . '?route=stock/index&success=updated');
            } else {
                header('Location: ' . BASE_URL . '?route=stock/edit&id=' . $id . '&error=failed');
            }
            exit;
        }
    }

    public function delete()
    {
        $id = $_GET['id'] ?? 0;
        $stockModel = new StockEntry();

        if ($stockModel->delete($id)) {
            header('Location: ' . BASE_URL . '?route=stock/index&success=deleted');
        } else {
            header('Location: ' . BASE_URL . '?route=stock/index&error=failed');
        }
        exit;
    }
}
