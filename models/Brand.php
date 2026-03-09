<?php

namespace App\Models;

class Brand
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAll()
    {
        $sectorId = $_SESSION['sector_id'] ?? 1;
        if ($sectorId === 'all') {
            $stmt = $this->pdo->query("SELECT * FROM brands ORDER BY name");
            return $stmt->fetchAll();
        }
        $stmt = $this->pdo->prepare("SELECT * FROM brands WHERE sector_id = :sectorId ORDER BY name");
        $stmt->execute(['sectorId' => $sectorId]);
        return $stmt->fetchAll();
    }

    public function create($data)
    {
        $sectorId = $_SESSION['sector_id'] ?? 1;
        $stmt = $this->pdo->prepare("INSERT INTO brands (name, sector_id) VALUES (:name, :sectorId)");
        return $stmt->execute([
            'name' => $data['name'],
            'sectorId' => $sectorId
        ]);
    }

    public function getById($id)
    {
        $sectorId = $_SESSION['sector_id'] ?? 1;
        $stmt = $this->pdo->prepare("SELECT * FROM brands WHERE id = :id AND sector_id = :sectorId");
        $stmt->execute(['id' => $id, 'sectorId' => $sectorId]);
        return $stmt->fetch();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE brands SET name = :name WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name']
        ]);
    }

    public function delete($id)
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM brands WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            return false; // Likely constraint violation
        }
    }
}
