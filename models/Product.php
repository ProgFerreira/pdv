<?php

namespace App\Models;

class Product
{
    private $pdo;

    /** @var bool|null cache: tabela products tem colunas da Ficha Técnica? */
    private static $hasFichaTecnicaColumns = null;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /** Verifica se as colunas yield_target_grams e margin_percent existem (migration 007). */
    private function hasFichaTecnicaColumns(): bool
    {
        if (self::$hasFichaTecnicaColumns !== null) {
            return self::$hasFichaTecnicaColumns;
        }
        try {
            $stmt = $this->pdo->query("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'products' AND column_name = 'margin_percent' LIMIT 1");
            self::$hasFichaTecnicaColumns = $stmt && $stmt->fetch();
        } catch (\Throwable $e) {
            self::$hasFichaTecnicaColumns = false;
        }
        return self::$hasFichaTecnicaColumns;
    }

    public function getAll($filters = [])
    {
        $sectorId = $filters['sector_id'] ?? ($_SESSION['sector_id'] ?? 1);

        $sql = "SELECT p.*, c.name as category_name, b.name as brand_name, s.name as sector_name, sup.name as supplier_name,
                COALESCE(sold.qty_sold, 0) AS qty_sold,
                COALESCE(sold.sold_revenue, 0) AS sold_revenue,
                COALESCE(sold.sold_cost, 0) AS sold_cost
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                LEFT JOIN sectors s ON p.sector_id = s.id
                LEFT JOIN suppliers sup ON p.supplier_id = sup.id
                LEFT JOIN (
                    SELECT si.product_id,
                           SUM(si.quantity) AS qty_sold,
                           SUM(si.unit_price * si.quantity) AS sold_revenue,
                           SUM(prod.cost_price * si.quantity) AS sold_cost
                    FROM sale_items si
                    INNER JOIN sales sa ON sa.id = si.sale_id AND COALESCE(sa.status, 'completed') = 'completed'
                    INNER JOIN products prod ON prod.id = si.product_id
                    GROUP BY si.product_id
                ) sold ON sold.product_id = p.id
                WHERE 1=1";

        $params = [];

        if ($sectorId !== 'all') {
            $sql .= " AND p.sector_id = :sector_id";
            $params['sector_id'] = $sectorId;
        }

        if (!empty($filters['name'])) {
            $sql .= " AND p.name LIKE :name";
            $params['name'] = "%{$filters['name']}%";
        }

        if (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }

        if (!empty($filters['ean'])) {
            $sql .= " AND p.ean = :ean";
            $params['ean'] = $filters['ean'];
        }

        if (!empty($filters['code'])) {
            $sql .= " AND p.code LIKE :code";
            $params['code'] = '%' . $filters['code'] . '%';
        }

        if (isset($filters['is_consigned']) && $filters['is_consigned'] !== '') {
            $sql .= " AND p.is_consigned = :is_consigned";
            $params['is_consigned'] = $filters['is_consigned'];
        }

        $sql .= " ORDER BY p.name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create($data)
    {
        $sectorId = $data['sector_id'] ?? ($_SESSION['sector_id'] ?? 1);
        $base = "INSERT INTO products (name, code, category_id, brand_id, price, cost_price, stock, unit, location, ean, observations, image, sector_id, is_gift_card, is_consigned, supplier_id";
        $placeholders = ":name, :code, :category_id, :brand_id, :price, :cost_price, :stock, :unit, :location, :ean, :observations, :image, :sector_id, :is_gift_card, :is_consigned, :supplier_id";
        $params = [
            'name' => $data['name'],
            'code' => !empty($data['code']) ? trim($data['code']) : null,
            'category_id' => $data['category_id'],
            'brand_id' => $data['brand_id'] ?: null,
            'price' => $data['price'],
            'cost_price' => $data['cost_price'],
            'stock' => $data['stock'],
            'unit' => $data['unit'],
            'location' => $data['location'],
            'ean' => $data['ean'],
            'observations' => $data['observations'],
            'image' => $data['image'],
            'sector_id' => $sectorId,
            'is_gift_card' => $data['is_gift_card'] ?? 0,
            'is_consigned' => $data['is_consigned'] ?? 0,
            'supplier_id' => !empty($data['supplier_id']) ? $data['supplier_id'] : null
        ];
        if ($this->hasFichaTecnicaColumns()) {
            $base .= ", yield_target_grams, margin_percent) VALUES ($placeholders, :yield_target_grams, :margin_percent)";
            $params['yield_target_grams'] = isset($data['yield_target_grams']) && $data['yield_target_grams'] !== '' ? (int) $data['yield_target_grams'] : null;
            $params['margin_percent'] = isset($data['margin_percent']) && $data['margin_percent'] !== '' ? (float) str_replace(',', '.', $data['margin_percent']) : 65.00;
        } else {
            $base .= ") VALUES ($placeholders)";
        }
        $stmt = $this->pdo->prepare($base);
        return $stmt->execute($params);
    }

    /**
     * Busca produtos para o PDV (nome, EAN, código).
     * @param string $term
     * @param int|null $categoryId Filtro por categoria (ex.: abas Bebidas, Sobremesas).
     */
    public function search($term, $categoryId = null)
    {
        $sectorId = $_SESSION['sector_id'] ?? 1;
        $t = "%" . trim($term) . "%";
        $sql = "SELECT * FROM products WHERE active = 1 AND (name LIKE :term OR ean LIKE :term2 OR code LIKE :term3 OR code = :term_exact)";

        $params = ['term' => $t, 'term2' => $t, 'term3' => $t, 'term_exact' => trim($term)];
        if ($sectorId !== 'all') {
            $sql .= " AND sector_id = :sector_id";
            $params['sector_id'] = $sectorId;
        }
        if ($categoryId !== null && $categoryId !== '') {
            $sql .= " AND category_id = :category_id";
            $params['category_id'] = (int) $categoryId;
        }

        $sql .= " ORDER BY name LIMIT 50";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getCategories()
    {
        $cat = new Category();
        return $cat->getAll();
    }

    public function getBrands()
    {
        $brand = new Brand();
        return $brand->getAll();
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function update($id, $data)
    {
        $fields = [
            'name = :name',
            'code = :code',
            'category_id = :category_id',
            'brand_id = :brand_id',
            'price = :price',
            'cost_price = :cost_price',
            'stock = :stock',
            'unit = :unit',
            'location = :location',
            'ean = :ean',
            'observations = :observations',
            'is_consigned = :is_consigned',
            'supplier_id = :supplier_id'
        ];

        $params = [
            'id' => $id,
            'name' => $data['name'],
            'code' => !empty($data['code']) ? trim($data['code']) : null,
            'category_id' => $data['category_id'],
            'brand_id' => $data['brand_id'] ?: null,
            'price' => $data['price'],
            'cost_price' => $data['cost_price'],
            'stock' => $data['stock'],
            'unit' => $data['unit'],
            'location' => $data['location'],
            'ean' => $data['ean'],
            'observations' => $data['observations'],
            'is_consigned' => $data['is_consigned'] ?? 0,
            'supplier_id' => !empty($data['supplier_id']) ? $data['supplier_id'] : null
        ];

        if ($this->hasFichaTecnicaColumns() && (isset($data['yield_target_grams']) || array_key_exists('yield_target_grams', $data))) {
            $fields[] = 'yield_target_grams = :yield_target_grams';
            $params['yield_target_grams'] = isset($data['yield_target_grams']) && $data['yield_target_grams'] !== '' ? (int) $data['yield_target_grams'] : null;
        }
        if ($this->hasFichaTecnicaColumns() && (isset($data['margin_percent']) || array_key_exists('margin_percent', $data))) {
            $fields[] = 'margin_percent = :margin_percent';
            $params['margin_percent'] = isset($data['margin_percent']) && $data['margin_percent'] !== '' ? (float) str_replace(',', '.', $data['margin_percent']) : 65.00;
        }

        if (isset($data['sector_id']) && $data['sector_id'] !== null) {
            $fields[] = 'sector_id = :sector_id';
            $params['sector_id'] = $data['sector_id'];
        }

        if (!empty($data['image'])) {
            $fields[] = 'image = :image';
            $params['image'] = $data['image'];
        }

        $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function toggleActive($id)
    {
        $stmt = $this->pdo->prepare("UPDATE products SET active = NOT active WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function delete($id)
    {
        // Check for sales dependencies might be needed, but for MVP soft delete (active=0) is better.
        // If hard delete is requested:
        try {
            $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            return false; // Likely constraint violation
        }
    }

    public function getBatches($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT sb.*, se.reference as nf_reference, se.entry_date, se.supplier as entry_supplier
            FROM stock_batches sb
            JOIN stock_entries se ON sb.stock_entry_id = se.id
            WHERE sb.product_id = :id AND sb.current_quantity > 0
            ORDER BY se.entry_date ASC
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll();
    }
}
