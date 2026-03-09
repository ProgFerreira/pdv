<?php require 'views/layouts/header.php'; ?>

<div class="flex flex-col gap-6">
<!-- Header & Filtros -->
<div class="flex flex-col md:flex-row justify-between items-center gap-4">
    <h2 class="text-2xl font-bold text-gray-800">📈 Lucratividade por Produto</h2>
    
    <div class="flex items-center gap-2">
         <a href="?route=report/index" class="text-gray-500 hover:text-primary font-medium text-sm">Voltar</a>
    </div>

    <div class="bg-gray-100 p-1.5 rounded-lg">
        <form class="flex items-center gap-2">
            <input type="hidden" name="route" value="report/profitability">
            <input type="date" name="start_date" class="rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 text-sm" value="<?php echo $startDate; ?>">
            <span class="text-gray-400 text-sm">até</span>
            <input type="date" name="end_date" class="rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 text-sm" value="<?php echo $endDate; ?>">
            <button type="submit" class="bg-white hover:bg-gray-50 text-gray-600 p-1.5 rounded-md shadow-sm border border-gray-200">
                <i class="fas fa-filter"></i>
            </button>
        </form>
    </div>
</div>

<!-- Cards Topo -->
<div class="cards-grid-default gap-6">
    <div class="bg-blue-50 border border-blue-100 rounded-lg p-6 text-center">
        <p class="text-blue-600 font-medium mb-1">Vendas Totais</p>
        <h3 class="text-3xl font-bold text-blue-800">R$ <?php echo number_format($totalSold, 2, ',', '.'); ?></h3>
    </div>
    <div class="bg-green-50 border border-green-100 rounded-lg p-6 text-center">
        <p class="text-green-600 font-medium mb-1">Lucro Bruto Total</p>
        <h3 class="text-3xl font-bold text-green-800">R$ <?php echo number_format($totalProfit, 2, ',', '.'); ?></h3>
    </div>
    <div class="bg-red-50 border border-red-100 rounded-lg p-6 text-center">
        <p class="text-red-600 font-medium mb-1">Prejuízo Total</p>
        <h3 class="text-3xl font-bold text-red-800">R$ 0,00</h3>
    </div>
</div>

<!-- Tabela -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Qtd</th>
                <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Custo Total</th>
                <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Venda Total</th>
                <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Lucro Bruto</th>
                <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Margem</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200 text-sm">
            <?php foreach($products as $p): 
                $totalSold = (float)($p['total_sold'] ?? 0);
                $totalCost = (float)($p['total_cost'] ?? 0);
                $profit = $totalSold - $totalCost;
                $margin = $totalSold > 0 ? ($profit / $totalSold) * 100 : 0;
            ?>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-800"><?php echo $p['name']; ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-gray-500"><?php echo $p['id']; ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-gray-500"><?php echo $p['qty']; ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-gray-500">R$ <?php echo number_format($totalCost, 2, ',', '.'); ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-gray-500">R$ <?php echo number_format($totalSold, 2, ',', '.'); ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-green-600 font-bold">R$ <?php echo number_format($profit, 2, ',', '.'); ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                    <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded-full">
                        <?php echo number_format($margin, 1, ',', '.'); ?>%
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</div><!-- /.flex.flex-col.gap-6 -->

<?php require 'views/layouts/footer.php'; ?>
