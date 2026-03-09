<?php

namespace App\Controllers;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Supplier;

class ReportController
{
    public function index()
    {
        global $pdo;
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $sectorId = $_SESSION['sector_id'] ?? 1;
        $params = ['start' => $startDate, 'end' => $endDate];

        // Status Summary
        $sqlSummary = "SELECT COUNT(*) as count, SUM(total) as revenue, AVG(total) as ticket FROM sales WHERE DATE(created_at) BETWEEN :start AND :end";
        if ($sectorId !== 'all') {
            $sqlSummary .= " AND sector_id = :sectorId";
            $params['sectorId'] = $sectorId;
        }
        $stmtSummary = $pdo->prepare($sqlSummary);
        $stmtSummary->execute($params);
        $summary = $stmtSummary->fetch(\PDO::FETCH_ASSOC);

        // Gross Profit
        $sqlProfit = "SELECT SUM(si.subtotal - (COALESCE(si.cost_price, 0) * si.quantity)) as profit
                      FROM sale_items si
                      JOIN sales s ON si.sale_id = s.id
                      WHERE DATE(s.created_at) BETWEEN :start AND :end";
        if ($sectorId !== 'all') {
            $sqlProfit .= " AND s.sector_id = :sectorId";
        }
        $stmtProfit = $pdo->prepare($sqlProfit);
        $stmtProfit->execute($params);
        $profit = $stmtProfit->fetch(\PDO::FETCH_ASSOC)['profit'] ?? 0;

        // Daily Revenue Chart (total + por forma de pagamento)
        $sqlDaily = "SELECT DATE(created_at) as date, SUM(total) as total 
                     FROM sales 
                     WHERE DATE(created_at) BETWEEN :start AND :end";
        if ($sectorId !== 'all') {
            $sqlDaily .= " AND sector_id = :sectorId";
        }
        $sqlDaily .= " GROUP BY DATE(created_at) ORDER BY date ASC";
        $stmtDaily = $pdo->prepare($sqlDaily);
        $stmtDaily->execute($params);
        $dailySales = $stmtDaily->fetchAll(\PDO::FETCH_ASSOC);

        // Faturamento por dia e por forma de pagamento (para gráfico empilhado e tabela)
        $sqlDailyByPayment = "SELECT DATE(created_at) as date, payment_method, SUM(total) as total 
                              FROM sales 
                              WHERE DATE(created_at) BETWEEN :start AND :end";
        if ($sectorId !== 'all') {
            $sqlDailyByPayment .= " AND sector_id = :sectorId";
        }
        $sqlDailyByPayment .= " GROUP BY DATE(created_at), payment_method ORDER BY date ASC";
        $stmtDailyByPayment = $pdo->prepare($sqlDailyByPayment);
        $stmtDailyByPayment->execute($params);
        $dailyByPaymentRaw = $stmtDailyByPayment->fetchAll(\PDO::FETCH_ASSOC);

        $chartLabels = [];
        $chartData = [];
        $maxRevenue = 0;
        $maxDate = '-';
        $minRevenue = 99999999;
        $minDate = '-';

        // Chaves dos 4 meios principais (cores fixas)
        $paymentKeys = ['Dinheiro', 'Cartão de Crédito', 'Cartão de Débito', 'PIX'];
        $dailyByPayment = []; // [date => ['Dinheiro'=>x, 'Cartão de Crédito'=>y, ...], ...]
        foreach ($dailySales as $d) {
            $dailyByPayment[$d['date']] = array_fill_keys($paymentKeys, 0);
        }
        foreach ($dailyByPaymentRaw as $r) {
            $dt = $r['date'];
            if (!isset($dailyByPayment[$dt])) {
                $dailyByPayment[$dt] = array_fill_keys($paymentKeys, 0);
            }
            $method = $r['payment_method'] ?? 'Outros';
            if (in_array($method, $paymentKeys, true)) {
                $dailyByPayment[$dt][$method] = (float) $r['total'];
            } else {
                if (!isset($dailyByPayment[$dt]['Outros'])) $dailyByPayment[$dt]['Outros'] = 0;
                $dailyByPayment[$dt]['Outros'] += (float) $r['total'];
            }
        }

        foreach ($dailySales as $d) {
            $formattedDate = date('d/m', strtotime($d['date']));
            $chartLabels[] = $formattedDate;
            $chartData[] = $d['total'];
            if ($d['total'] > $maxRevenue) {
                $maxRevenue = $d['total'];
                $maxDate = $formattedDate;
            }
            if ($d['total'] < $minRevenue) {
                $minRevenue = $d['total'];
                $minDate = $formattedDate;
            }
        }
        if ($minRevenue == 99999999)
            $minRevenue = 0;

        // Datasets por forma de pagamento (para Chart.js stacked bar)
        $chartDataByPayment = [];
        foreach ($paymentKeys as $key) {
            $chartDataByPayment[$key] = [];
            foreach ($dailySales as $d) {
                $dt = $d['date'];
                $chartDataByPayment[$key][] = isset($dailyByPayment[$dt][$key]) ? (float) $dailyByPayment[$dt][$key] : 0;
            }
        }

        // Payment Methods
        $sqlMethods = "SELECT payment_method, COUNT(*) as count, SUM(total) as total 
                       FROM sales 
                       WHERE DATE(created_at) BETWEEN :start AND :end";
        if ($sectorId !== 'all') {
            $sqlMethods .= " AND sector_id = :sectorId";
        }
        $sqlMethods .= " GROUP BY payment_method";
        $stmtMethods = $pdo->prepare($sqlMethods);
        $stmtMethods->execute($params);
        $paymentMethods = $stmtMethods->fetchAll(\PDO::FETCH_ASSOC);

        // Daily Movements
        $sqlMovements = "SELECT DATE(s.created_at) as date, COUNT(DISTINCT s.id) as count, SUM(si.subtotal) as revenue,
                                SUM(si.subtotal - (COALESCE(si.cost_price, 0) * si.quantity)) as profit
                         FROM sale_items si
                         JOIN sales s ON si.sale_id = s.id
                         WHERE DATE(s.created_at) BETWEEN :start AND :end";
        if ($sectorId !== 'all') {
            $sqlMovements .= " AND s.sector_id = :sectorId";
        }
        $sqlMovements .= " GROUP BY DATE(s.created_at) ORDER BY date DESC";
        $stmtMovements = $pdo->prepare($sqlMovements);
        $stmtMovements->execute($params);
        $movements = $stmtMovements->fetchAll(\PDO::FETCH_ASSOC);

        // Enriquecer cada dia com faturamento por forma de pagamento (para colunas coloridas na tabela)
        foreach ($movements as &$m) {
            $dt = $m['date'];
            foreach ($paymentKeys as $key) {
                $m[$key] = isset($dailyByPayment[$dt][$key]) ? (float) $dailyByPayment[$dt][$key] : 0;
            }
        }
        unset($m);

        require 'views/reports/advanced.php';
    }

    public function abc_curve()
    {
        global $pdo;
        $sectorId = $_SESSION['sector_id'] ?? 'all';
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        $catId = $_GET['category_id'] ?? '';
        $brandId = $_GET['brand_id'] ?? '';
        $productQuery = $_GET['product_query'] ?? '';

        $params = ['start' => $startDate, 'end' => $endDate];

        $sql = "SELECT p.name, SUM(si.subtotal) as revenue
                FROM sale_items si
                JOIN sales s ON si.sale_id = s.id
                JOIN products p ON si.product_id = p.id
                WHERE DATE(s.created_at) BETWEEN :start AND :end";

        if ($sectorId !== 'all') {
            $sql .= " AND s.sector_id = :sectorId";
            $params['sectorId'] = $sectorId;
        }

        if (!empty($catId)) {
            $sql .= " AND p.category_id = :catId";
            $params['catId'] = $catId;
        }

        if (!empty($brandId)) {
            $sql .= " AND p.brand_id = :brandId";
            $params['brandId'] = $brandId;
        }

        if (!empty($productQuery)) {
            $sql .= " AND p.name LIKE :pQuery";
            $params['pQuery'] = "%$productQuery%";
        }

        $sql .= " GROUP BY p.id ORDER BY revenue DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $totalRevenue = array_sum(array_column($products, 'revenue'));
        $cumulativeRevenue = 0;
        foreach ($products as &$p) {
            $cumulativeRevenue += $p['revenue'];
            $p['share'] = ($totalRevenue > 0) ? ($p['revenue'] / $totalRevenue) * 100 : 0;
            $p['cumulative_share'] = ($totalRevenue > 0) ? ($cumulativeRevenue / $totalRevenue) * 100 : 0;
            if ($p['cumulative_share'] <= 80)
                $p['class'] = 'A';
            elseif ($p['cumulative_share'] <= 95)
                $p['class'] = 'B';
            else
                $p['class'] = 'C';
        }
        unset($p);

        // Fetch for filters
        $catModel = new Category();
        $categories = $catModel->getAll();
        $brandModel = new Brand();
        $brands = $brandModel->getAll();

        require 'views/reports/abc.php';
    }

    public function sector_performance()
    {
        global $pdo;
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        // Robust Subquery Query to avoid data multiplication
        $sql = "SELECT 
                    sec.id,
                    sec.name as sector_name,
                    COALESCE(rev.total_revenue, 0) as revenue,
                    COALESCE(cst.total_cost, 0) as costs,
                    COALESCE(rev.cnt, 0) as sales_count
                FROM sectors sec
                LEFT JOIN (
                    SELECT sector_id, SUM(total) as total_revenue, COUNT(*) as cnt
                    FROM sales
                    WHERE DATE(created_at) BETWEEN :s1 AND :e1
                    GROUP BY sector_id
                ) rev ON sec.id = rev.sector_id
                LEFT JOIN (
                    SELECT s.sector_id, SUM(si.cost_price * si.quantity) as total_cost
                    FROM sale_items si
                    JOIN sales s ON si.sale_id = s.id
                    WHERE DATE(s.created_at) BETWEEN :s2 AND :e2
                    GROUP BY s.sector_id
                ) cst ON sec.id = cst.sector_id
                ORDER BY sec.id ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            's1' => $startDate,
            'e1' => $endDate,
            's2' => $startDate,
            'e2' => $endDate
        ]);
        $performance = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($performance as &$p) {
            $p['profit'] = $p['revenue'] - $p['costs'];
            $p['margin'] = ($p['revenue'] > 0) ? ($p['profit'] / $p['revenue']) * 100 : 0;
        }
        unset($p);

        require 'views/reports/sectors.php';
    }

    public function payments()
    {
        global $pdo;
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $sectorId = $_SESSION['sector_id'] ?? 1;
        $params = ['start' => $startDate, 'end' => $endDate];

        $sql = "SELECT payment_method, COUNT(*) as count, SUM(total) as total
                FROM sales WHERE DATE(created_at) BETWEEN :start AND :end";
        if ($sectorId !== 'all') {
            $sql .= " AND sector_id = :sectorId";
            $params['sectorId'] = $sectorId;
        }
        $sql .= " GROUP BY payment_method ORDER BY total DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $totalSales = array_sum(array_column($data, 'total'));
        $totalCount = array_sum(array_column($data, 'count'));
        $labels = array_column($data, 'payment_method');
        $values = array_column($data, 'total');

        require 'views/reports/payments.php';
    }

    public function profitability()
    {
        global $pdo;
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $sectorId = $_SESSION['sector_id'] ?? 1;
        $params = ['start' => $startDate, 'end' => $endDate];

        $sql = "SELECT p.id, p.name, p.ean, SUM(si.quantity) as qty,
                       SUM(si.cost_price * si.quantity) as total_cost,
                       SUM(si.subtotal) as total_sold
                FROM sale_items si
                JOIN sales s ON si.sale_id = s.id
                JOIN products p ON si.product_id = p.id
                WHERE DATE(s.created_at) BETWEEN :start AND :end";
        if ($sectorId !== 'all') {
            $sql .= " AND s.sector_id = :sectorId";
            $params['sectorId'] = $sectorId;
        }
        $sql .= " GROUP BY p.id ORDER BY total_sold DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $totalSold = array_sum(array_column($products, 'total_sold'));
        $totalCost = array_sum(array_column($products, 'total_cost'));
        $totalProfit = $totalSold - $totalCost;

        require 'views/reports/profitability.php';
    }

    public function printable()
    {
        global $pdo;
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $sectorId = $_SESSION['sector_id'] ?? 1;
        $params = ['start' => $startDate, 'end' => $endDate];

        $sqlTotal = "SELECT SUM(total) as total, COUNT(*) as count FROM sales WHERE DATE(created_at) BETWEEN :start AND :end";
        if ($sectorId !== 'all') {
            $sqlTotal .= " AND sector_id = :sectorId";
            $params['sectorId'] = $sectorId;
        }
        $stmtTotal = $pdo->prepare($sqlTotal);
        $stmtTotal->execute($params);
        $totals = $stmtTotal->fetch(\PDO::FETCH_ASSOC);

        $sqlMethods = "SELECT payment_method, SUM(total) as total, COUNT(*) as count FROM sales WHERE DATE(created_at) BETWEEN :start AND :end";
        if ($sectorId !== 'all') {
            $sqlMethods .= " AND sector_id = :sectorId";
        }
        $sqlMethods .= " GROUP BY payment_method";
        $stmtMethods = $pdo->prepare($sqlMethods);
        $stmtMethods->execute($params);
        $methods = $stmtMethods->fetchAll(\PDO::FETCH_ASSOC);

        $sqlProducts = "SELECT p.name, SUM(si.quantity) as qty, SUM(si.subtotal) as total
                        FROM sale_items si JOIN sales s ON si.sale_id = s.id JOIN products p ON si.product_id = p.id
                        WHERE DATE(s.created_at) BETWEEN :start AND :end";
        if ($sectorId !== 'all') {
            $sqlProducts .= " AND s.sector_id = :sectorId";
        }
        $sqlProducts .= " GROUP BY p.id ORDER BY p.name ASC";
        $stmtProducts = $pdo->prepare($sqlProducts);
        $stmtProducts->execute($params);
        $products = $stmtProducts->fetchAll(\PDO::FETCH_ASSOC);

        require 'views/reports/printable.php';
    }

    public function best_sellers()
    {
        global $pdo;
        $sectorId = $_SESSION['sector_id'] ?? 'all';
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        $catId = $_GET['category_id'] ?? '';
        $brandId = $_GET['brand_id'] ?? '';

        $params = ['start' => $startDate, 'end' => $endDate];

        $sql = "SELECT p.id, p.name, p.image, 
                       SUM(si.quantity) as total_qty, 
                       SUM(si.subtotal) as total_revenue,
                       AVG(si.unit_price) as avg_price
                FROM sale_items si
                JOIN sales s ON si.sale_id = s.id
                JOIN products p ON si.product_id = p.id
                WHERE DATE(s.created_at) BETWEEN :start AND :end";

        if ($sectorId !== 'all') {
            $sql .= " AND s.sector_id = :sectorId";
            $params['sectorId'] = $sectorId;
        }

        if (!empty($catId)) {
            $sql .= " AND p.category_id = :catId";
            $params['catId'] = $catId;
        }

        if (!empty($brandId)) {
            $sql .= " AND p.brand_id = :brandId";
            $params['brandId'] = $brandId;
        }

        $sql .= " GROUP BY p.id ORDER BY total_qty DESC LIMIT 50";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Fetch for filters
        $catModel = new Category();
        $categories = $catModel->getAll();
        $brandModel = new Brand();
        $brands = $brandModel->getAll();

        require 'views/reports/best_sellers.php';
    }
    public function consigned()
    {
        global $pdo;
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $supplierId = $_GET['supplier_id'] ?? '';

        $params = ['start' => $startDate, 'end' => $endDate];

        $sql = "SELECT sup.name as supplier_name, p.name as product_name, 
                       SUM(si.quantity) as total_qty, 
                       SUM(si.subtotal) as total_revenue,
                       AVG(si.unit_price) as avg_price
                FROM sale_items si
                JOIN sales s ON si.sale_id = s.id
                JOIN products p ON si.product_id = p.id
                JOIN suppliers sup ON p.supplier_id = sup.id
                WHERE p.is_consigned = 1 
                AND DATE(s.created_at) BETWEEN :start AND :end";

        if (!empty($supplierId)) {
            $sql .= " AND p.supplier_id = :supplierId";
            $params['supplierId'] = $supplierId;
        }

        $sql .= " GROUP BY sup.id, p.id ORDER BY sup.name ASC, total_revenue DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Fetch suppliers for the filter
        $suppModel = new Supplier();
        $suppliers = $suppModel->getAll();

        require 'views/reports/consigned.php';
    }
    public function inventory_batches()
    {
        global $pdo;
        $sectorId = $_SESSION['sector_id'] ?? 'all';
        $params = [];

        $sql = "SELECT sb.*, p.name as product_name, se.reference as nf_reference, se.entry_date, se.supplier as entry_supplier
                FROM stock_batches sb
                JOIN products p ON sb.product_id = p.id
                JOIN stock_entries se ON sb.stock_entry_id = se.id
                WHERE sb.current_quantity > 0";

        if ($sectorId !== 'all') {
            $sql .= " AND p.sector_id = :sectorId";
            $params['sectorId'] = $sectorId;
        }

        $sql .= " ORDER BY se.entry_date ASC, p.name ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $batches = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        require 'views/reports/inventory_batches.php';
    }
}
