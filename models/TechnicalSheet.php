<?php

namespace App\Models;

/**
 * Ficha Técnica: 1 por produto. Itens em technical_sheet_items.
 * Cálculo de custo total e preço sugerido via helpers (Calc).
 */
class TechnicalSheet
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getByProductId(int $productId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM technical_sheets WHERE product_id = :product_id LIMIT 1");
        $stmt->execute(['product_id' => $productId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM technical_sheets WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Cria ficha técnica para o produto (se não existir).
     * @return int|false id da ficha ou false
     */
    public function getOrCreateForProduct(int $productId)
    {
        $sheet = $this->getByProductId($productId);
        if ($sheet) {
            return (int) $sheet['id'];
        }
        $stmt = $this->pdo->prepare("INSERT INTO technical_sheets (product_id, notes) VALUES (:product_id, NULL)");
        if (!$stmt->execute(['product_id' => $productId])) {
            return false;
        }
        return (int) $this->pdo->lastInsertId();
    }

    public function updateNotes(int $sheetId, ?string $notes): bool
    {
        $stmt = $this->pdo->prepare("UPDATE technical_sheets SET notes = :notes WHERE id = :id");
        return $stmt->execute(['id' => $sheetId, 'notes' => $notes]);
    }

    /**
     * Lista itens da ficha com dados do ingrediente.
     * @return array<int, array>
     */
    public function getItems(int $sheetId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT tsi.*, i.name AS ingredient_name, i.code AS ingredient_code, i.unit AS ingredient_unit
            FROM technical_sheet_items tsi
            JOIN ingredients i ON i.id = tsi.ingredient_id
            WHERE tsi.sheet_id = :sheet_id
            ORDER BY tsi.id
        ");
        $stmt->execute(['sheet_id' => $sheetId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Adiciona item à ficha; calcula yield (se qty_net informado), custo e total.
     */
    public function addItem(int $sheetId, array $data): bool
    {
        $ingredientId = (int) ($data['ingredient_id'] ?? 0);
        $ingredient = (new Ingredient())->getById($ingredientId);
        if (!$ingredient) {
            return false;
        }

        $qtyBrut = (float) ($data['item_qty_brut'] ?? 0);
        $qtyNet = isset($data['item_qty_net']) && $data['item_qty_net'] !== '' ? (float) $data['item_qty_net'] : null;
        $itemUnit = $data['item_unit'] ?? 'g';
        $yieldPercent = null;
        if ($qtyBrut > 0 && $qtyNet !== null && $qtyNet > 0) {
            $yieldPercent = calc_yield_percent($qtyNet, $qtyBrut);
        } elseif (!empty($data['item_yield_percent'])) {
            $yieldPercent = (float) $data['item_yield_percent'];
        }

        $costPerUnit = (float) ($ingredient['cost_per_unit'] ?? 0);
        $itemTotalCost = calc_item_cost_simple(
            $qtyBrut,
            $itemUnit,
            $ingredient['unit'],
            $costPerUnit
        );

        $sql = "INSERT INTO technical_sheet_items
                (sheet_id, ingredient_id, item_classification, item_qty_brut, item_qty_net, item_unit,
                 item_yield_percent, item_cost_per_unit, item_total_cost)
                VALUES (:sheet_id, :ingredient_id, :item_classification, :item_qty_brut, :item_qty_net, :item_unit,
                        :item_yield_percent, :item_cost_per_unit, :item_total_cost)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'sheet_id' => $sheetId,
            'ingredient_id' => $ingredientId,
            'item_classification' => $data['item_classification'] ?? null,
            'item_qty_brut' => $qtyBrut,
            'item_qty_net' => $qtyNet,
            'item_unit' => $itemUnit,
            'item_yield_percent' => $yieldPercent,
            'item_cost_per_unit' => $costPerUnit,
            'item_total_cost' => $itemTotalCost,
        ]);
    }

    /**
     * Retorna um item da ficha (com dados do ingrediente) para edição.
     */
    public function getItemById(int $itemId, int $sheetId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT tsi.*, i.name AS ingredient_name, i.code AS ingredient_code, i.unit AS ingredient_unit
            FROM technical_sheet_items tsi
            JOIN ingredients i ON i.id = tsi.ingredient_id
            WHERE tsi.id = :id AND tsi.sheet_id = :sheet_id
        ");
        $stmt->execute(['id' => $itemId, 'sheet_id' => $sheetId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Atualiza item da ficha; recalcula yield e custo total.
     */
    public function updateItem(int $itemId, int $sheetId, array $data): bool
    {
        $existing = $this->getItemById($itemId, $sheetId);
        if (!$existing) {
            return false;
        }

        $ingredientId = (int) ($data['ingredient_id'] ?? $existing['ingredient_id']);
        $ingredient = (new Ingredient())->getById($ingredientId);
        if (!$ingredient) {
            return false;
        }

        $qtyBrut = (float) ($data['item_qty_brut'] ?? 0);
        $qtyNet = isset($data['item_qty_net']) && $data['item_qty_net'] !== '' ? (float) $data['item_qty_net'] : null;
        $itemUnit = $data['item_unit'] ?? 'g';
        $yieldPercent = null;
        if ($qtyBrut > 0 && $qtyNet !== null && $qtyNet > 0) {
            $yieldPercent = calc_yield_percent($qtyNet, $qtyBrut);
        } elseif (!empty($data['item_yield_percent'])) {
            $yieldPercent = (float) $data['item_yield_percent'];
        }

        $costPerUnit = (float) ($ingredient['cost_per_unit'] ?? 0);
        $itemTotalCost = calc_item_cost_simple($qtyBrut, $itemUnit, $ingredient['unit'], $costPerUnit);

        $stmt = $this->pdo->prepare("
            UPDATE technical_sheet_items SET
                ingredient_id = :ingredient_id,
                item_classification = :item_classification,
                item_qty_brut = :item_qty_brut,
                item_qty_net = :item_qty_net,
                item_unit = :item_unit,
                item_yield_percent = :item_yield_percent,
                item_cost_per_unit = :item_cost_per_unit,
                item_total_cost = :item_total_cost
            WHERE id = :id AND sheet_id = :sheet_id
        ");
        return $stmt->execute([
            'id' => $itemId,
            'sheet_id' => $sheetId,
            'ingredient_id' => $ingredientId,
            'item_classification' => $data['item_classification'] ?? null,
            'item_qty_brut' => $qtyBrut,
            'item_qty_net' => $qtyNet,
            'item_unit' => $itemUnit,
            'item_yield_percent' => $yieldPercent,
            'item_cost_per_unit' => $costPerUnit,
            'item_total_cost' => $itemTotalCost,
        ]);
    }

    /**
     * Remove item da ficha.
     */
    public function deleteItem(int $itemId, int $sheetId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM technical_sheet_items WHERE id = :id AND sheet_id = :sheet_id");
        return $stmt->execute(['id' => $itemId, 'sheet_id' => $sheetId]);
    }

    /**
     * Recalcula custo de um item (após alteração de custo do ingrediente) e atualiza no banco.
     */
    public function recalcItem(int $itemId): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT tsi.*, i.unit AS ingredient_unit, i.cost_per_unit
            FROM technical_sheet_items tsi
            JOIN ingredients i ON i.id = tsi.ingredient_id
            WHERE tsi.id = :id
        ");
        $stmt->execute(['id' => $itemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            return false;
        }
        $total = calc_item_cost_simple(
            (float) $row['item_qty_brut'],
            $row['item_unit'],
            $row['ingredient_unit'],
            (float) $row['cost_per_unit']
        );
        $stmt = $this->pdo->prepare("UPDATE technical_sheet_items SET item_cost_per_unit = :cost, item_total_cost = :total WHERE id = :id");
        return $stmt->execute([
            'id' => $itemId,
            'cost' => (float) $row['cost_per_unit'],
            'total' => $total,
        ]);
    }

    /**
     * Soma item_total_cost dos itens da ficha.
     */
    public function getTotalCost(int $sheetId): float
    {
        $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(item_total_cost), 0) AS total FROM technical_sheet_items WHERE sheet_id = :sheet_id");
        $stmt->execute(['sheet_id' => $sheetId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (float) ($row['total'] ?? 0);
    }
}
