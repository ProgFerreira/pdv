<?php

namespace App\Models;

/**
 * Insumos (matérias-primas) para Ficha Técnica.
 * Unidades: kg, g, l, ml, un. cost_per_unit = custo por essa unidade.
 */
class Ingredient
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /** @return array<int, array> */
    public function getAll(bool $activeOnly = false): array
    {
        $sql = "SELECT * FROM ingredients WHERE 1=1";
        if ($activeOnly) {
            $sql .= " AND active = 1";
        }
        $sql .= " ORDER BY name";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM ingredients WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getByCode(string $code): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM ingredients WHERE code = :code LIMIT 1");
        $stmt->execute(['code' => $code]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO ingredients (code, name, unit, cost_per_unit, active)
                VALUES (:code, :name, :unit, :cost_per_unit, :active)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'code' => $data['code'] ?? null,
            'name' => $data['name'],
            'unit' => $data['unit'] ?? 'kg',
            'cost_per_unit' => (float) ($data['cost_per_unit'] ?? 0),
            'active' => isset($data['active']) ? (int) $data['active'] : 1,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE ingredients SET code = :code, name = :name, unit = :unit,
                cost_per_unit = :cost_per_unit, active = :active
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'code' => $data['code'] ?? null,
            'name' => $data['name'],
            'unit' => $data['unit'] ?? 'kg',
            'cost_per_unit' => (float) ($data['cost_per_unit'] ?? 0),
            'active' => isset($data['active']) ? (int) $data['active'] : 1,
        ]);
    }

    /**
     * Verifica se o insumo está em uso em alguma ficha técnica.
     */
    public function isUsedInSheets(int $id): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM technical_sheet_items WHERE ingredient_id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() !== false;
    }

    public function delete(int $id): bool
    {
        if ($this->isUsedInSheets($id)) {
            return false;
        }
        $stmt = $this->pdo->prepare("DELETE FROM ingredients WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function toggleActive(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE ingredients SET active = NOT active WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
