<?php require 'views/layouts/header.php'; ?>

<div class="flex flex-col gap-6">
<!-- Header & Filtros -->
<div class="flex flex-col md:flex-row justify-between items-center gap-4">
    <div class="flex items-center gap-4">
        <h2 class="text-2xl font-bold text-gray-800">Relatório de meios de pagamentos ℹ️</h2>
    </div>
    
    <div class="flex items-center gap-2">
         <a href="?route=report/index" class="text-gray-500 hover:text-primary font-medium text-sm">Voltar para Resumo</a>
    </div>

    <div class="bg-gray-100 p-1.5 rounded-lg flex items-center">
        <form class="flex items-center gap-2">
            <input type="hidden" name="route" value="report/payments">
            
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-calendar text-gray-400"></i>
                </div>
                <input type="date" name="start_date" class="rounded-md border border-gray-300 pl-10 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 text-sm" value="<?php echo $startDate; ?>">
            </div>
            
            <span class="text-gray-400 text-sm">até</span>
            
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                     <i class="fas fa-calendar text-gray-400"></i>
                </div>
                <input type="date" name="end_date" class="rounded-md border border-gray-300 pl-10 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 text-sm" value="<?php echo $endDate; ?>">
            </div>

            <button type="submit" class="bg-white hover:bg-gray-50 text-gray-600 p-1.5 rounded-md shadow-sm border border-gray-200 transition-colors">
                <i class="fas fa-filter"></i>
            </button>
        </form>
    </div>
</div>

<!-- Chart & Summary Area -->
<div class="cards-grid-default gap-6 border border-blue-200 rounded-lg overflow-hidden">
    <!-- Chart Section -->
    <div class="lg:col-span-2 bg-white p-6 border-r border-blue-100">
        <canvas id="paymentChart" height="250"></canvas>
    </div>

    <!-- Summary Section -->
    <div class="bg-blue-50/50 p-6 flex flex-col justify-center items-center text-center">
        <p class="text-gray-500 font-medium mb-2">Quantidade de vendas</p>
        <h3 class="text-5xl font-bold text-gray-800"><?php echo $totalCount; ?></h3>
    </div>
</div>

<!-- Detailed Table -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Meio de pagamento</th>
                <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Qtd. de vendas</th>
                <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Valor em vendas</th>
                <th class="px-6 py-4 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Devoluções</th>
                <th class="px-6 py-4 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Sangria</th>
                <th class="px-6 py-4 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Suprimento</th>
                <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200 text-sm">
            <?php foreach($data as $row): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-700"><?php echo $row['payment_method']; ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-gray-500"><?php echo $row['count']; ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-gray-500">R$ <?php echo number_format($row['total'], 2, ',', '.'); ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-gray-400">R$ 0,00</td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-gray-400">R$ 0,00</td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-gray-400">R$ 0,00</td>
                <td class="px-6 py-4 whitespace-nowrap text-right font-bold text-gray-800">R$ <?php echo number_format($row['total'], 2, ',', '.'); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot class="bg-gray-50 font-bold text-gray-800">
            <tr>
                <td class="px-6 py-4">Total</td>
                <td class="px-6 py-4 text-center"><?php echo $totalCount; ?></td>
                <td class="px-6 py-4 text-center">R$ <?php echo number_format($totalSales, 2, ',', '.'); ?></td>
                <td class="px-6 py-4 text-center text-gray-400">R$ 0,00</td>
                <td class="px-6 py-4 text-center text-gray-400">R$ 0,00</td>
                <td class="px-6 py-4 text-center text-gray-400">R$ 0,00</td>
                <td class="px-6 py-4 text-right">R$ <?php echo number_format($totalSales, 2, ',', '.'); ?></td>
            </tr>
        </tfoot>
    </table>
</div>

</div><!-- /.flex.flex-col.gap-6 -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('paymentChart').getContext('2d');
new Chart(ctx, {
    type: 'bar', // Chart.js 3+ uses 'bar' with indexAxis: 'y' for horizontal
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Total R$',
            data: <?php echo json_encode($values); ?>,
            backgroundColor: '#38bdf8', // Light Blue (Tailwind Sky 400)
            barThickness: 20,
            borderRadius: 4
        }]
    },
    options: {
        indexAxis: 'y', // Convert to Horizontal Bar
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.x !== null) {
                            label += new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(context.parsed.x);
                        }
                        return label;
                    }
                }
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                grid: { display: false }
            },
            y: {
                grid: { display: false }
            }
        }
    }
});
</script>

<?php require 'views/layouts/footer.php'; ?>
