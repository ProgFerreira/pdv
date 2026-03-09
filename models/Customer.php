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

    /**
     * Atualiza apenas o endereço do cliente (usado no PDV após preencher via CEP).
     * Aceita: cep, address_street, address_number, address_complement, address_neighborhood, address_city, address_state
     */
    public function updateAddress(int $id, array $data): bool {
        $line = self::buildDeliveryLine($data);
        $stmt = $this->pdo->prepare("
            UPDATE customers SET
                cep = :cep,
                address_street = :address_street,
                address_number = :address_number,
                address_complement = :address_complement,
                address_neighborhood = :address_neighborhood,
                address_city = :address_city,
                address_state = :address_state,
                address = :address
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'cep' => $data['cep'] ?? null,
            'address_street' => $data['address_street'] ?? null,
            'address_number' => $data['address_number'] ?? null,
            'address_complement' => $data['address_complement'] ?? null,
            'address_neighborhood' => $data['address_neighborhood'] ?? null,
            'address_city' => $data['address_city'] ?? null,
            'address_state' => $data['address_state'] ?? null,
            'address' => $line ?: null,
        ]);
    }

    /**
     * Monta uma única linha de endereço de entrega a partir dos campos (para exibir e imprimir no cupom).
     */
    public static function buildDeliveryLine(array $c): string {
        $parts = array_filter([
            trim((string) ($c['address_street'] ?? '')),
            trim((string) ($c['address_number'] ?? '')),
            trim((string) ($c['address_complement'] ?? '')),
            trim((string) ($c['address_neighborhood'] ?? '')),
            trim((string) ($c['address_city'] ?? '')),
            trim((string) ($c['address_state'] ?? '')),
        ]);
        if (empty($parts)) {
            return trim((string) ($c['address'] ?? ''));
        }
        $line = implode(', ', $parts);
        $cep = trim((string) ($c['cep'] ?? ''));
        if ($cep !== '') {
            $line .= ' - CEP: ' . $cep;
        }
        return $line;
    }
}
