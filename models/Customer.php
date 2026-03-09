<?php

namespace App\Models;

class Customer {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM customers ORDER BY name");
        return $stmt->fetchAll();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO customers (name, phone, email, address) VALUES (:name, :phone, :email, :address)");
        $ok = $stmt->execute([
            'name' => $data['name'] ?? '',
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null
        ]);
        return $ok ? (int) $this->pdo->lastInsertId() : false;
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE customers SET name = :name, phone = :phone, email = :email, address = :address WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'address' => $data['address']
        ]);
    }
    
    public function search($term) {
        $stmt = $this->pdo->prepare("SELECT * FROM customers WHERE name LIKE :term OR phone LIKE :term LIMIT 10");
        $stmt->execute(['term' => "%$term%"]);
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM customers WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}
