<?php require 'views/layouts/header.php'; ?>
<?php
$revenue = (float) ($summary['revenue'] ?? 0);
$count = (int) ($summary['count'] ?? 0);
$ticketMedio = (float) ($summary['ticket'] ?? 0);
$profitVal = (float) $profit;
$marginPct = $revenue > 0 ? ($profitVal / $revenue) * 100 : 0;

$topMethod = '-';
$topCount = 0;
foreach ($paymentMethods as $pm) {
    if ((int) $pm['count'] > $topCount) {
        $topCount = (int) $pm['count'];
        $topMethod = $pm['payment_method'];
    }
}
$totalCountPay = $count ?: 1;

$reportLinks = [
    ['route' => 'report/abc_curve', 'icon' => 'fa-chart-pie', 'label' => 'Curva ABC'],
    ['route' => 'report/sector_performance', 'icon' => 'fa-store', 'label' => 'Setores'],
    ['route' => 'report/payments', 'icon' => 'fa-wallet', 'label' => 'Pagamentos'],
    ['route' => 'report/profitability', 'icon' => 'fa-chart-line', 'label' => 'Lucratividade'],
    ['route' => 'report/printable', 'icon' => 'fa-print', 'label' => 'Imprimir', 'target' => '_blank', 'class' => 'btn-dark'],
];
?>
<style>
.report-page { width: 100%; max-width: 100%; display: flex; flex-direction: column; gap: 1rem; }
.report-page .report-header { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; }
.report-page .report-header h1 { margin: 0; font-size: 1.35rem; font-weight: 800; color: #1e293b; }
.report-page .report-filter-form { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; }
.report-page .report-date-wrap { display: inline-flex; align-items: center; border: 1px solid #e2e8f0; border-radius: 0.5rem; background: #f8fafc; overflow: hidden; }
.report-page .report-date-wrap span { padding: 0 0.65rem; color: #64748b; border-right: 1px solid #e2e8f0; }
.report-page .report-date-wrap input { border: 0; background: transparent; padding: 0.45rem 0.65rem; font-size: 0.8125rem; width: 8.75rem; }
.report-page .report-shortcuts { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem; }
@media (min-width: 640px) { .report-page .report-shortcuts { grid-template-columns: repeat(3, 1fr); } }
@media (min-width: 1024px) { .report-page .report-shortcuts { grid-template-columns: repeat(5, 1fr); } }
.report-page .report-shortcut { display: flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.5rem 0.75rem; border-radius: 0.5rem; border: 1px solid #e2e8f0; background: #fff; color: #475569; font-size: 0.8125rem; font-weight: 600; text-decoration: none; transition: background 0.15s, border-color 0.15s; }
.report-page .report-shortcut:hover { background: #f8fafc; border-color: #c7d2fe; color: #4f46e5; }
.report-page .report-shortcut.btn-dark { background: #1e293b; border-color: #1e293b; color: #fff; }
.report-page .report-shortcut.btn-dark:hover { background: #0f172a; color: #fff; }
.report-page .report-kpi-grid { gap: 0.75rem; }
.report-page .report-insights { display: grid; grid-template-columns: 1fr; gap: 0.75rem; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid #f1f5f9; }
@media (min-width: 768px) { .report-page .report-insights { grid-template-columns: repeat(3, 1fr); } }
.report-page .report-insight { text-align: center; padding: 0.65rem 0.5rem; border-radius: 0.5rem; background: #f8fafc; border: 1px solid #f1f5f9; }
.report-page .report-insight-label { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #64748b; margin-bottom: 0.25rem; }
.report-page .report-insight-value { font-size: 1.125rem; font-weight: 800; line-height: 1.2; }
.report-page .report-insight-sub { font-size: 0.75rem; color: #94a3b8; margin-top: 0.15rem; }
.report-page .report-chart-box { height: 280px; position: relative; }
.report-page .report-pay-bar { display: flex; height: 0.5rem; border-radius: 9999px; overflow: hidden; background: #e2e8f0; margin-top: 0.5rem; }
.report-page .report-pay-legend { display: flex; flex-wrap: wrap; gap: 0.35rem 0.75rem; margin-top: 0.5rem; font-size: 0.7rem; color: #64748b; }
.report-page .report-pay-legend span { display: inline-flex; align-items: center; gap: 0.25rem; }
.report-page .report-pay-dot { width: 0.5rem; height: 0.5rem; border-radius: 9999px; flex-shrink: 0; }
.report-page .table-standard { font-size: 0.8125rem; }
.report-page .table-standard th,
.report-page .table-standard td { padding: 0.5rem 0.65rem; white-space: nowrap; }
.report-page .table-standard th:first-child,
.report-page .table-standard td:first-child { position: sticky; left: 0; z-index: 1; background: inherit; text-align: left; }
.report-page .table-standard thead th { background: #f8fafc; }
</style>

<div class="report-page">
    <header class="report-header">
        <h1><i class="fas fa-chart-bar text-primary mr-2"></i>Relatórios</h1>
        <form method="GET" action="<?php echo BASE_URL; ?>" class="report-filter-form">
            <input type="hidden" name="route" value="report/index">
            <div class="report-date-wrap">
                <span><i class="fas fa-calendar-alt"></i></span>
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($startDate, ENT_QUOTES, 'UTF-8'); ?>" aria-label="Data inicial">
            </div>
            <span class="text-gray-400 text-sm">até</span>
            <div class="report-date-wrap">
                <span><i class="fas fa-calendar-alt"></i></span>
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($endDate, ENT_QUOTES, 'UTF-8'); ?>" aria-label="Data final">
            </div>
            <button type="submit" class="btn btn-primary btn-sm font-bold px-4">Filtrar</button>
        </form>
    </header>

    <div class="card-standard">
        <div class="card-standard-header"><i class="fas fa-external-link-alt"></i> Outros relatórios</div>
        <div class="card-standard-body py-3">
            <div class="report-shortcuts">
                <?php foreach ($reportLinks as $link):
                    $qs = http_build_query(array_merge(
                        ['route' => $link['route']],
                        in_array($link['route'], ['report/printable'], true) ? [] : ['start_date' => $startDate, 'end_date' => $endDate]
                    ));
                    $cls = 'report-shortcut' . (!empty($link['class']) ? ' ' . $link['class'] : '');
                ?>
                <a href="<?php echo BASE_URL; ?>?<?php echo htmlspecialchars($qs, ENT_QUOTES, 'UTF-8'); ?>"
                    class="<?php echo $cls; ?>"
                    <?php echo !empty($link['target']) ? ' target="' . htmlspecialchars($link['target'], ENT_QUOTES, 'UTF-8') . '"' : ''; ?>>
                    <i class="fas <?php echo htmlspecialchars($link['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                    <?php echo htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="report-kpi-grid cards-grid-default">
        <div class="card-standard-metric bg-primary text-white p-4 border-0 border-b-4 border-black/10">
            <h6 class="card-metric-label opacity-90">Faturamento</h6>
            <div class="flex justify-between items-center gap-2">
                <span class="text-lg xl:text-xl font-black truncate">R$ <?php echo number_format($revenue, 2, ',', '.'); ?></span>
                <i class="fas fa-hand-holding-usd text-base opacity-30 flex-shrink-0"></i>
            </div>
        </div>
        <div class="card-standard-metric p-4 border-l-primary">
            <h6 class="card-metric-label">Qtd. Vendas</h6>
            <div class="flex justify-between items-center gap-2">
                <span class="text-lg xl:text-xl font-black text-gray-800"><?php echo $count; ?></span>
                <i class="fas fa-shopping-cart text-base text-blue-100 flex-shrink-0"></i>
            </div>
        </div>
        <div class="card-standard-metric p-4 border-l-info">
            <h6 class="card-metric-label">Ticket Médio</h6>
            <div class="flex justify-between items-center gap-2">
                <span class="text-lg xl:text-xl font-black text-gray-800 truncate">R$ <?php echo number_format($ticketMedio, 2, ',', '.'); ?></span>
                <i class="fas fa-receipt text-base text-blue-100 flex-shrink-0"></i>
            </div>
        </div>
        <div class="card-standard-metric p-4 border-l-success">
            <h6 class="card-metric-label">Lucro Bruto</h6>
            <div class="flex justify-between items-center gap-2">
                <span class="text-lg xl:text-xl font-black text-green-600 truncate">R$ <?php echo number_format($profitVal, 2, ',', '.'); ?></span>
                <i class="fas fa-chart-line text-base text-green-100 flex-shrink-0"></i>
            </div>
            <p class="text-xs text-gray-500 mt-1 mb-0">Margem <?php echo number_format($marginPct, 1, ',', '.'); ?>%</p>
        </div>
    </div>

    <div class="card-standard">
        <div class="card-standard-header"><i class="fas fa-chart-column"></i> Faturamento por dia</div>
        <div class="card-standard-body">
            <div class="report-chart-box">
                <canvas id="revenueChart"></canvas>
            </div>
            <div class="report-insights">
                <div class="report-insight">
                    <div class="report-insight-label text-green-700"><i class="fas fa-arrow-up mr-1"></i>Maior faturamento</div>
                    <div class="report-insight-value text-green-600">R$ <?php echo number_format($maxRevenue, 2, ',', '.'); ?></div>
                    <div class="report-insight-sub"><?php echo htmlspecialchars($maxDate, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="report-insight">
                    <div class="report-insight-label text-red-600"><i class="fas fa-arrow-down mr-1"></i>Menor faturamento</div>
                    <div class="report-insight-value text-red-500">R$ <?php echo number_format($minRevenue, 2, ',', '.'); ?></div>
                    <div class="report-insight-sub"><?php echo htmlspecialchars($minDate, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="report-insight">
                    <div class="report-insight-label text-primary"><i class="fas fa-wallet mr-1"></i>Pagamento principal</div>
                    <div class="report-insight-value text-primary text-base"><?php echo htmlspecialchars($topMethod, ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="report-pay-bar">
                        <?php
                        $colors = ['bg-amber-500', 'bg-blue-500', 'bg-emerald-500', 'bg-teal-500', 'bg-indigo-500'];
                        $i = 0;
                        foreach ($paymentMethods as $pm):
                            $width = ((int) $pm['count'] / $totalCountPay) * 100;
                            if ($width <= 0) continue;
                        ?>
                        <div class="<?php echo $colors[$i % 5]; ?>" style="width:<?php echo $width; ?>%" title="<?php echo htmlspecialchars($pm['payment_method'], ENT_QUOTES, 'UTF-8'); ?>"></div>
                        <?php $i++; endforeach; ?>
                    </div>
                    <div class="report-pay-legend">
                        <?php $i = 0; foreach ($paymentMethods as $pm): ?>
                        <span><span class="report-pay-dot <?php echo $colors[$i++ % 5]; ?>"></span><?php echo htmlspecialchars($pm['payment_method'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo (int) $pm['count']; ?>)</span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
$paymentColumns = [
    'Dinheiro'          => ['bg' => 'bg-amber-50', 'th' => 'bg-amber-100 text-amber-900', 'label' => 'Dinheiro'],
    'Cartão de Crédito' => ['bg' => 'bg-blue-50',  'th' => 'bg-blue-100 text-blue-900',  'label' => 'Crédito'],
    'Cartão de Débito'  => ['bg' => 'bg-emerald-50', 'th' => 'bg-emerald-100 text-emerald-900', 'label' => 'Débito'],
    'PIX'               => ['bg' => 'bg-teal-50',   'th' => 'bg-teal-100 text-teal-900',   'label' => 'PIX'],
];
?>
    <div class="card-standard overflow-hidden">
        <div class="card-standard-header flex flex-wrap items-center justify-between gap-2">
            <span><i class="fas fa-table"></i> Movimentações por dia</span>
            <span class="text-xs font-normal text-gray-500"><?php echo date('d/m/Y', strtotime($startDate)); ?> — <?php echo date('d/m/Y', strtotime($endDate)); ?></span>
        </div>
        <div class="overflow-x-auto">
            <table class="table-standard min-w-full divide-y divide-gray-200 text-center w-full">
                <thead>
                    <tr>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase">Dia</th>
                        <th class="text-xs font-semibold text-gray-500 uppercase">Vendas</th>
                        <th class="text-xs font-semibold text-gray-500 uppercase">Ticket</th>
                        <?php foreach ($paymentColumns as $col): ?>
                        <th class="text-xs font-semibold uppercase <?php echo $col['th']; ?>"><?php echo htmlspecialchars($col['label'], ENT_QUOTES, 'UTF-8'); ?></th>
                        <?php endforeach; ?>
                        <th class="text-xs font-semibold text-green-700 uppercase">Lucro</th>
                        <th class="text-right text-xs font-semibold text-gray-700 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    <?php if (empty($movements)): ?>
                    <tr>
                        <td colspan="9" class="py-8 text-center text-gray-400">Nenhuma movimentação no período selecionado.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($movements as $m):
                        $ticket = $m['count'] > 0 ? $m['revenue'] / $m['count'] : 0;
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="text-left font-medium text-gray-700"><?php echo date('d/m/y', strtotime($m['date'])); ?></td>
                        <td class="text-gray-600"><?php echo (int) $m['count']; ?></td>
                        <td class="text-gray-600">R$ <?php echo number_format($ticket, 2, ',', '.'); ?></td>
                        <?php foreach ($paymentColumns as $key => $col): ?>
                        <td class="font-medium <?php echo $col['bg']; ?>">R$ <?php echo number_format($m[$key] ?? 0, 2, ',', '.'); ?></td>
                        <?php endforeach; ?>
                        <td class="text-green-600 font-medium">R$ <?php echo number_format($m['profit'], 2, ',', '.'); ?></td>
                        <td class="text-right font-bold text-gray-800">R$ <?php echo number_format($m['revenue'], 2, ',', '.'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    const ctx = document.getElementById('revenueChart');
    if (!ctx) return;
    const paymentColors = {
        'Dinheiro':          { bg: 'rgba(245, 158, 11, 0.9)',  border: 'rgb(245, 158, 11)' },
        'Cartão de Crédito': { bg: 'rgba(59, 130, 246, 0.9)', border: 'rgb(59, 130, 246)' },
        'Cartão de Débito':  { bg: 'rgba(16, 185, 129, 0.9)', border: 'rgb(16, 185, 129)' },
        'PIX':               { bg: 'rgba(20, 184, 166, 0.9)',  border: 'rgb(20, 184, 166)' }
    };
    const labels = <?php echo json_encode($chartLabels); ?>;
    const datasets = [
        { label: 'Dinheiro', data: <?php echo json_encode($chartDataByPayment['Dinheiro'] ?? []); ?>, backgroundColor: paymentColors['Dinheiro'].bg, borderColor: paymentColors['Dinheiro'].border, borderWidth: 1, borderRadius: 4, maxBarThickness: 40 },
        { label: 'Crédito', data: <?php echo json_encode($chartDataByPayment['Cartão de Crédito'] ?? []); ?>, backgroundColor: paymentColors['Cartão de Crédito'].bg, borderColor: paymentColors['Cartão de Crédito'].border, borderWidth: 1, borderRadius: 4, maxBarThickness: 40 },
        { label: 'Débito', data: <?php echo json_encode($chartDataByPayment['Cartão de Débito'] ?? []); ?>, backgroundColor: paymentColors['Cartão de Débito'].bg, borderColor: paymentColors['Cartão de Débito'].border, borderWidth: 1, borderRadius: 4, maxBarThickness: 40 },
        { label: 'PIX', data: <?php echo json_encode($chartDataByPayment['PIX'] ?? []); ?>, backgroundColor: paymentColors['PIX'].bg, borderColor: paymentColors['PIX'].border, borderWidth: 1, borderRadius: 4, maxBarThickness: 40 }
    ];
    new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: { labels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true, position: 'top', labels: { boxWidth: 12, font: { size: 11 } } },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const value = context.parsed.y || 0;
                            return context.dataset.label + ': R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        }
                    }
                }
            },
            scales: {
                x: { stacked: true, grid: { display: false }, ticks: { font: { size: 10 } } },
                y: { stacked: true, beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 } } }
            }
        }
    });
})();
</script>

<?php require 'views/layouts/footer.php'; ?>
