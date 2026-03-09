<?php

namespace App\Models;

class Supplier
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAll()
    {
        $stmt = $this->pdo->query("SELECT * FROM suppliers ORDER BY name");
        return $stmt->fetchAll();
    }

    public function create($data)
    {
        $sql = "INSERT INTO suppliers (name, contact_person, phone, email, address) 
                VALUES (:name, :contact_person, :phone, :email, :address)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'name' => $data['name'],
            'contact_person' => $data['contact_person'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null
        ]);
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM suppliers WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE suppliers 
                SET name = :name, 
                    contact_person = :contact_person, 
                    phone = :phone, 
                    email = :email, 
                    address = :address 
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'contact_person' => $data['contact_person'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM suppliers WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
