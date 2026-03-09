<?php

namespace App\Controllers;

use App\Models\GiftCard;
use App\Models\Customer;
use App\Models\CashRegister;

class GiftCardController
{
    public function index()
    {
        $model = new GiftCard();
        $filters = ['query' => $_GET['query'] ?? ''];
        $cards = $model->getAll($filters);
        
        // Buscar gastos se houver filtro por código específico
        $expenses = [];
        $selectedCard = null;
        $giftCardCode = $_GET['giftcard_code'] ?? '';
        
        if (!empty($giftCardCode)) {
            $selectedCard = $model->getByCode($giftCardCode);
            if ($selectedCard) {
                $expenses = $model->getExpenses($selectedCard['id']);
            }
        }
        
        require 'views/giftcard/index.php';
    }

    public function check()
    {
        $code = $_GET['code'] ?? '';
        $model = new GiftCard();
        $card = $model->getByCode($code);

        if ($card) {
            echo json_encode(['success' => true, 'balance' => (float) $card['balance'], 'id' => $card['id']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Vale não encontrado ou inativo.']);
        }
    }

    public function create()
    {
        $customerModel = new Customer();
        $customers = $customerModel->getAll();
        require 'views/giftcard/form.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new GiftCard();

            if (empty($_POST['code'])) {
                $_POST['code'] = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            }

            $_POST['amount'] = str_replace(',', '.', $_POST['initial_value'] ?? '0');

            if ($model->create($_POST)) {
                header('Location: ' . BASE_URL . '?route=giftcard/index&success=created');
            } else {
                header('Location: ' . BASE_URL . '?route=giftcard/create&error=failed');
            }
            exit;
        }
    }

    public function edit()
    {
        $id = $_GET['id'] ?? 0;
        $model = new GiftCard();
        $card = $model->getById($id);

        $customerModel = new Customer();
        $customers = $customerModel->getAll();

        if (!$card) {
            header('Location: ' . BASE_URL . '?route=giftcard/index');
            exit;
        }

        require 'views/giftcard/form.php';
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            $model = new GiftCard();
            if ($model->update($id, $_POST)) {
                header('Location: ' . BASE_URL . '?route=giftcard/index&success=updated');
            } else {
                header('Location: ' . BASE_URL . '?route=giftcard/edit&id=' . $id . '&error=failed');
            }
            exit;
        }
    }

    public function delete()
    {
        $id = $_GET['id'] ?? 0;
        $model = new GiftCard();
        if ($model->delete($id)) {
            header('Location: ' . BASE_URL . '?route=giftcard/index&success=deleted');
        } else {
            header('Location: ' . BASE_URL . '?route=giftcard/index&error=failed');
        }
        exit;
    }

    public function refund()
    {
        $id = $_GET['id'] ?? 0;
        $model = new GiftCard();
        $card = $model->getById($id);

        if (!$card) {
            header('Location: ' . BASE_URL . '?route=giftcard/index&error=not_found');
            exit;
        }

        // Check if there is an open register to debit
        $cashModel = new CashRegister();
        $openRegister = $cashModel->getOpenRegister($_SESSION['user_id']);

        if (!$openRegister) {
            header('Location: ' . BASE_URL . '?route=giftcard/index&error=register_closed');
            exit;
        }

        if ($model->refund($id, $openRegister['id'])) {
            header('Location: ' . BASE_URL . '?route=giftcard/index&success=refunded');
        } else {
            header('Location: ' . BASE_URL . '?route=giftcard/index&error=refund_failed');
        }
        exit;
    }

    public function view()
    {
        $id = $_GET['id'] ?? 0;
        $model = new GiftCard();
        $card = $model->getById($id);

        if (!$card) {
            echo "Vale Presente não encontrado.";
            exit;
        }

        require 'views/giftcard/virtual.php';
    }
}