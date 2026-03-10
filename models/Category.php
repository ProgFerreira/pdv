<?php

namespace App\Models;

class Category
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
            $stmt = $this->pdo->query("SELECT * FROM categories ORDER BY name");
            return $stmt->fetchAll();
        }
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE sector_id = :sectorId ORDER BY name");
        $stmt->execute(['sectorId' => $sectorId]);
        return $stmt->fetchAll();
    }

    /**
     * Lista categorias para página pública (ex.: abas do link de pedidos), sem usar sessão.
     * @param int|null $sectorId Se null ou 0, retorna todas; senão filtra por sector_id.
     */
    public static function getAllForPublic(?int $sectorId = null): array
    {
        global $pdo;
        if ($sectorId !== null && $sectorId > 0) {
            $stmt = $pdo->prepare("SELECT * FROM categories WHERE sector_id = :sectorId ORDER BY name");
            $stmt->execute(['sectorId' => $sectorId]);
        } else {
            $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
        }
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $sectorId = $_SESSION['sector_id'] ?? 1;
        $sql = "INSERT INTO categories (name, sector_id) VALUES (:name, :sectorId)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'name' => $data['name'],
            'sectorId' => $sectorId
        ]);
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE categories SET name = :name WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name']
        ]);
    }

    public function delete($id)
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM categories WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            return false; // Likely constraint violation
        }
    }
}
