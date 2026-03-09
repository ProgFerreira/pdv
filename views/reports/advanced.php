<?php require 'views/layouts/header.php'; ?>

<div class="flex flex-col gap-6">
<!-- Filtros -->
<div class="flex flex-col md:flex-row justify-between items-center gap-4">
    <h2 class="text-2xl font-bold text-gray-800">📊 Relatórios</h2>

    <div class="bg-white p-2 rounded-lg shadow-sm border border-gray-200">
        <form class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="route" value="report/index">

            <div class="flex items-center border rounded-md overflow-hidden bg-gray-50">
                <span class="px-3 text-gray-500 border-r"><i class="fas fa-calendar"></i></span>
                <input type="date" name="start_date"
                    class="bg-transparent border-none focus:ring-0 text-sm py-1.5 px-2 text-gray-700"
                    value="<?php echo $startDate; ?>">
            </div>

            <span class="text-gray-400 text-sm">até</span>

            <div class="flex items-center border rounded-md overflow-hidden bg-gray-50">
                <span class="px-3 text-gray-500 border-r"><i class="fas fa-calendar"></i></span>
                <input type="date" name="end_date"
                    class="bg-transparent border-none focus:ring-0 text-sm py-1.5 px-2 text-gray-700"
                    value="<?php echo $endDate; ?>">
            </div>

            <button type="submit"
                class="bg-primary hover:bg-primary-hover text-white text-sm font-medium py-1.5 px-4 rounded transition-colors shadow-sm">
                Filtrar
            </button>
        </form>
    </div>

    <div class="flex flex-wrap gap-2">
        <a href="?route=report/abc_curve"
            class="bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-medium py-2 px-4 rounded shadow-sm text-sm">
            <i class="fas fa-chart-pie mr-1 text-primary"></i> Curva ABC
        </a>
        <a href="?route=report/sector_performance"
            class="bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-medium py-2 px-4 rounded shadow-sm text-sm">
            <i class="fas fa-store mr-1 text-primary"></i> Setores
        </a>
        <a href="?route=report/payments"
            class="bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-medium py-2 px-4 rounded shadow-sm text-sm">
            <i class="fas fa-wallet mr-1 text-primary"></i> Meios de Pagamento
        </a>
        <a href="?route=report/profitability"
            class="bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-medium py-2 px-4 rounded shadow-sm text-sm">
            <i class="fas fa-chart-line mr-1 text-primary"></i> Lucratividade
        </a>
        <a href="?route=report/printable" target="_blank"
            class="bg-gray-800 hover:bg-black border border-gray-800 text-white font-medium py-2 px-4 rounded shadow-sm text-sm">
            <i class="fas fa-print mr-1"></i> Imprimir
        </a>
    </div>
</div>

<!-- Cards Resumo -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Faturamento -->
    <div class="bg-white rounded-lg shadow-sm border-l-4 border-blue-500 p-4">
        <h6 class="text-gray-500 text-xs uppercase font-bold tracking-wider mb-1">Faturamento</h6>
        <h3 class="text-blue-600 text-2xl font-bold">R$
            <?php echo number_format($summary['revenue'] ?? 0, 2, ',', '.'); ?>
        </h3>
    </div>

    <!-- Qtd. Vendas -->
    <div class="bg-white rounded-lg shadow-sm p-4">
        <h6 class="text-gray-500 text-xs uppercase font-bold tracking-wider mb-1">Qtd. Vendas</h6>
        <h3 class="text-gray-800 text-2xl font-bold"><?php echo $summary['count']; ?></h3>
    </div>

    <!-- Ticket Médio -->
    <div class="bg-white rounded-lg shadow-sm p-4">
        <h6 class="text-gray-500 text-xs uppercase font-bold tracking-wider mb-1">Ticket Médio</h6>
        <h3 class="text-gray-800 text-2xl font-bold">R$
            <?php echo number_format($summary['ticket'] ?? 0, 2, ',', '.'); ?>
        </h3>
    </div>

    <!-- Lucro Bruto -->
    <div class="bg-white rounded-lg shadow-sm border-l-4 border-green-500 p-4">
        <h6 class="text-gray-500 text-xs uppercase font-bold tracking-wider mb-1">Lucro Bruto</h6>
        <h3 class="text-green-600 text-2xl font-bold">R$ <?php echo number_format($profit, 2, ',', '.'); ?></h3>
    </div>
</div>

<!-- Gráfico e Destaques -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Faturamento por Dia -->
    <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h6 class="font-bold text-gray-700">Faturamento por dia</h6>
        </div>
        <div class="p-6 h-[300px]">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Cards Laterais (Highlights) -->
    <div class="space-y-4">
        <!-- Melhor Dia -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
            <small
                class="text-green-600 uppercase text-xs font-bold tracking-wider flex justify-center items-center gap-2">
                <i class="fas fa-arrow-up"></i> Maior Faturamento
            </small>
            <h4 class="text-2xl font-bold text-green-600 my-2">R$ <?php echo number_format($maxRevenue, 2, ',', '.'); ?>
            </h4>
            <small class="text-gray-500"><?php echo $maxDate; ?></small>
        </div>

        <!-- Pior Dia -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
            <small
                class="text-red-500 uppercase text-xs font-bold tracking-wider flex justify-center items-center gap-2">
                <i class="fas fa-arrow-down"></i> Menor Faturamento
            </small>
            <h4 class="text-2xl font-bold text-red-500 my-2">R$ <?php echo number_format($minRevenue, 2, ',', '.'); ?>
            </h4>
            <small class="text-gray-500"><?php echo $minDate; ?></small>
        </div>

        <!-- Pagamentos -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-3 border-b border-gray-100 bg-gray-50">
                <h6 class="font-bold text-gray-700 text-sm">Pagamento Mais Utilizado</h6>
            </div>
            <div class="p-6">
                <?php
                $topMethod = '';
                $topCount = 0;
                foreach ($paymentMethods as $pm) {
                    if ($pm['count'] > $topCount) {
                        $topCount = $pm['count'];
                        $topMethod = $pm['payment_method'];
                    }
                }
                ?>
                <h5 class="text-primary font-bold text-lg mb-3"><?php echo $topMethod ?: '-'; ?></h5>

                <div class="flex h-2 rounded-full overflow-hidden bg-gray-200">
                    <?php
                    $colors = ['bg-blue-500', 'bg-teal-400', 'bg-yellow-400', 'bg-indigo-500'];
                    $i = 0;
                    $totalCount = $summary['count'] ?: 1;
                    foreach ($paymentMethods as $pm):
                        $width = ($pm['count'] / $totalCount) * 100;
                        $color = $colors[$i % 4];
                        $i++;
                        ?>
                        <div class="<?php echo $color; ?>" style="width: <?php echo $width; ?>%"
                            title="<?php echo $pm['payment_method']; ?>: <?php echo $pm['count']; ?>"></div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2 text-xs text-gray-500">
                    <?php $i = 0;
                    foreach ($paymentMethods as $pm): ?>
                        <div class="flex items-center gap-1">
                            <div class="w-2 h-2 rounded-full <?php echo $colors[$i++ % 4]; ?>"></div>
                            <span><?php echo $pm['payment_method']; ?> (<?php echo $pm['count']; ?>)</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$paymentColumns = [
    'Dinheiro'          => ['bg' => 'bg-amber-100', 'th' => 'bg-amber-200 text-amber-900', 'label' => '💵 Dinheiro'],
    'Cartão de Crédito' => ['bg' => 'bg-blue-100',  'th' => 'bg-blue-200 text-blue-900',  'label' => '💳 Crédito'],
    'Cartão de Débito'  => ['bg' => 'bg-emerald-100', 'th' => 'bg-emerald-200 text-emerald-900', 'label' => '💳 Débito'],
    'PIX'               => ['bg' => 'bg-teal-100',   'th' => 'bg-teal-200 text-teal-900',   'label' => '💠 PIX'],
];
?>
<!-- Tabela de Movimentações (Faturamento por dia com colunas por forma de pagamento) -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
        <h6 class="font-bold text-gray-700">Movimentações</h6>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-center">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dia</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Vendas</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket Médio</th>
                    <?php foreach ($paymentColumns as $key => $col): ?>
                    <th class="px-3 py-3 text-center text-xs font-medium uppercase tracking-wider <?php echo $col['th']; ?>"><?php echo $col['label']; ?></th>
                    <?php endforeach; ?>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Lucro Bruto</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Faturamento</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm">
                <?php foreach ($movements as $m):
                    $ticket = $m['count'] > 0 ? $m['revenue'] / $m['count'] : 0;
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap text-left font-medium text-gray-600"><?php echo date('d/m/y', strtotime($m['date'])); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-500"><?php echo $m['count']; ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-500">R$ <?php echo number_format($ticket, 2, ',', '.'); ?></td>
                        <?php foreach ($paymentColumns as $key => $col): ?>
                        <td class="px-3 py-3 whitespace-nowrap font-medium <?php echo $col['bg']; ?>">R$ <?php echo number_format($m[$key] ?? 0, 2, ',', '.'); ?></td>
                        <?php endforeach; ?>
                        <td class="px-4 py-3 whitespace-nowrap text-green-600 font-medium">R$ <?php echo number_format($m['profit'], 2, ',', '.'); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-right font-bold text-gray-800">R$ <?php echo number_format($m['revenue'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</div><!-- /.flex.flex-col.gap-6 -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const paymentColors = {
        'Dinheiro':          { bg: 'rgba(245, 158, 11, 0.9)',  border: 'rgb(245, 158, 11)' },
        'Cartão de Crédito': { bg: 'rgba(59, 130, 246, 0.9)', border: 'rgb(59, 130, 246)' },
        'Cartão de Débito':  { bg: 'rgba(16, 185, 129, 0.9)', border: 'rgb(16, 185, 129)' },
        'PIX':               { bg: 'rgba(20, 184, 166, 0.9)',  border: 'rgb(20, 184, 166)' }
    };
    const labels = <?php echo json_encode($chartLabels); ?>;
    const datasets = [
        { label: '💵 Dinheiro',          data: <?php echo json_encode($chartDataByPayment['Dinheiro'] ?? []); ?>, backgroundColor: paymentColors['Dinheiro'].bg,          borderColor: paymentColors['Dinheiro'].border, borderWidth: 1, borderRadius: 4, maxBarThickness: 48 },
        { label: '💳 Cartão Crédito',    data: <?php echo json_encode($chartDataByPayment['Cartão de Crédito'] ?? []); ?>, backgroundColor: paymentColors['Cartão de Crédito'].bg, borderColor: paymentColors['Cartão de Crédito'].border, borderWidth: 1, borderRadius: 4, maxBarThickness: 48 },
        { label: '💳 Cartão Débito',     data: <?php echo json_encode($chartDataByPayment['Cartão de Débito'] ?? []); ?>, backgroundColor: paymentColors['Cartão de Débito'].bg, borderColor: paymentColors['Cartão de Débito'].border, borderWidth: 1, borderRadius: 4, maxBarThickness: 48 },
        { label: '💠 PIX',               data: <?php echo json_encode($chartDataByPayment['PIX'] ?? []); ?>, backgroundColor: paymentColors['PIX'].bg, borderColor: paymentColors['PIX'].border, borderWidth: 1, borderRadius: 4, maxBarThickness: 48 }
    ];
    new Chart(ctx, {
        type: 'bar',
        data: { labels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true, position: 'top' },
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
                x: { stacked: true, grid: { display: false } },
                y: { stacked: true, beginAtZero: true, grid: { borderDash: [5, 5], color: '#f3f4f6' } }
            }
        }
    });
</script>

<?php require 'views/layouts/footer.php'; ?>