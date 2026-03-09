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
}
