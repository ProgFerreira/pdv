<?php

namespace App\Controllers;

use App\Models\Customer;

class CustomerController {
    public function index() {
        $customerModel = new Customer();
        $customers = $customerModel->getAll();
        require 'views/customers/index.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $customerModel = new Customer();
            $data = [
                'name' => $_POST['name'],
                'phone' => $_POST['phone'],
                'email' => $_POST['email'],
                'address' => $_POST['address']
            ];
            
            if ($customerModel->create($data)) {
                header('Location: ' . BASE_URL . '?route=customer/index');
                exit;
            }
        }
        $isEdit = false;
        require 'views/customers/form.php';
    }

    public function edit() {
        $id = $_GET['id'] ?? 0;
        $customerModel = new Customer();
        $customer = $customerModel->getById($id);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'],
                'phone' => $_POST['phone'],
                'email' => $_POST['email'],
                'address' => $_POST['address']
            ];
            
            if ($customerModel->update($id, $data)) {
                header('Location: ' . BASE_URL . '?route=customer/index');
                exit;
            }
        }
        
        $isEdit = true;
        require 'views/customers/form.php';
    }
    
    public function search() {
        $term = $_GET['term'] ?? '';
        $customerModel = new Customer();
        $customers = $customerModel->search($term);
        header('Content-Type: application/json');
        echo json_encode($customers);
    }

    /**
     * Atualiza endereço do cliente (PDV: após preencher CEP via API e informar número/complemento).
     * POST JSON: customer_id, cep, address_street, address_number, address_complement, address_neighborhood, address_city, address_state
     */
    public function updateAddress() {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
            return;
        }
        $input = $GLOBALS['_JSON_BODY'] ?? json_decode(file_get_contents('php://input'), true) ?? [];
        $input = is_array($input) ? $input : [];
        $customerId = isset($input['customer_id']) ? (int) $input['customer_id'] : 0;
        if ($customerId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Cliente inválido']);
            return;
        }
        $customerModel = new Customer();
        $customer = $customerModel->getById($customerId);
        if (!$customer) {
            echo json_encode(['success' => false, 'message' => 'Cliente não encontrado']);
            return;
        }
        $data = [
            'cep' => isset($input['cep']) ? trim((string) $input['cep']) : null,
            'address_street' => isset($input['address_street']) ? trim((string) $input['address_street']) : null,
            'address_number' => isset($input['address_number']) ? trim((string) $input['address_number']) : null,
            'address_complement' => isset($input['address_complement']) ? trim((string) $input['address_complement']) : null,
            'address_neighborhood' => isset($input['address_neighborhood']) ? trim((string) $input['address_neighborhood']) : null,
            'address_city' => isset($input['address_city']) ? trim((string) $input['address_city']) : null,
            'address_state' => isset($input['address_state']) ? trim((string) $input['address_state']) : null,
        ];
        if ($customerModel->updateAddress($customerId, $data)) {
            $updated = $customerModel->getById($customerId);
            $line = Customer::buildDeliveryLine($updated ?: []);
            echo json_encode(['success' => true, 'delivery_address' => $line]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar endereço']);
        }
    }

    /**
     * Cadastro rápido do PDV: cria cliente com endereço em uma chamada.
     * POST JSON: name, phone, email (opcional), cep, address_street, address_number, address_complement, address_neighborhood, address_city, address_state
     */
    public function storeFromPos() {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
            return;
        }
        $input = $GLOBALS['_JSON_BODY'] ?? json_decode(file_get_contents('php://input'), true) ?? [];
        $input = is_array($input) ? $input : [];
        $name = isset($input['name']) ? trim((string) $input['name']) : '';
        $phone = isset($input['phone']) ? trim((string) $input['phone']) : '';
        if ($name === '') {
            echo json_encode(['success' => false, 'message' => 'Informe o nome do cliente']);
            return;
        }
        if ($phone === '') {
            echo json_encode(['success' => false, 'message' => 'Informe o telefone do cliente']);
            return;
        }
        $customerModel = new Customer();
        $data = [
            'name' => $name,
            'phone' => $phone,
            'email' => isset($input['email']) ? trim((string) $input['email']) : null,
            'cep' => isset($input['cep']) ? trim((string) $input['cep']) : null,
            'address_street' => isset($input['address_street']) ? trim((string) $input['address_street']) : null,
            'address_number' => isset($input['address_number']) ? trim((string) $input['address_number']) : null,
            'address_complement' => isset($input['address_complement']) ? trim((string) $input['address_complement']) : null,
            'address_neighborhood' => isset($input['address_neighborhood']) ? trim((string) $input['address_neighborhood']) : null,
            'address_city' => isset($input['address_city']) ? trim((string) $input['address_city']) : null,
            'address_state' => isset($input['address_state']) ? trim((string) $input['address_state']) : null,
        ];
        try {
            $id = $customerModel->createWithAddress($data);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar. Execute a migration 008_customer_address_delivery.sql se ainda não executou.']);
            return;
        }
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar cliente']);
            return;
        }
        $customer = $customerModel->getById((int) $id);
        echo json_encode(['success' => true, 'customer' => $customer]);
    }
}
