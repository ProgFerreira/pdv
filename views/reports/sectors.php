<?php require 'views/layouts/header.php'; ?>

<div class="flex flex-col gap-6">
<div class="flex flex-col md:flex-row justify-between items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 uppercase tracking-tight">🏢 Desempenho por Setor</h2>
        <p class="text-sm text-gray-500">Comparativo financeiro entre as unidades de negócio.</p>
    </div>
    <div class="bg-white p-2 rounded-lg shadow-sm border border-gray-200">
        <form class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="route" value="report/sector_performance">
            <input type="date" name="start_date" class="rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 text-sm"
                value="<?php echo $startDate; ?>">
            <span class="text-gray-400">até</span>
            <input type="date" name="end_date" class="rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 text-sm"
                value="<?php echo $endDate; ?>">
            <button type="submit"
                class="bg-primary hover:bg-primary-hover text-white text-sm font-medium py-1.5 px-4 rounded transition-colors shadow-sm">
                Filtrar
            </button>
        </form>
    </div>
</div>

<div class="cards-grid-default gap-8">
    <?php foreach ($performance as $p): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-black text-gray-800 uppercase tracking-wide flex items-center gap-2">
                    <span
                        class="text-[10px] bg-gray-200 text-gray-500 px-1.5 py-0.5 rounded font-mono">#<?php echo $p['id']; ?></span>
                    📍 <?php echo htmlspecialchars($p['sector_name']); ?>
                </h3>
                <span class="text-xs text-gray-400 font-bold">
                    <?php echo $p['sales_count']; ?> vendas no período
                </span>
            </div>

            <div class="p-6 space-y-6">
                <!-- Principais Métricas -->
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Faturamento</p>
                        <p class="text-lg font-bold text-gray-800">R$
                            <?php echo number_format($p['revenue'], 2, ',', '.'); ?>
                        </p>
                    </div>
                    <div class="text-center">
                        <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Custo Mercadoria</p>
                        <p class="text-lg font-bold text-red-500">R$
                            <?php echo number_format($p['costs'], 2, ',', '.'); ?>
                        </p>
                    </div>
                    <div class="text-center">
                        <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Lucro Bruto</p>
                        <p class="text-lg font-bold text-green-600">R$
                            <?php echo number_format($p['profit'], 2, ',', '.'); ?>
                        </p>
                    </div>
                </div>

                <!-- Barra de Margem -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-bold text-gray-600 uppercase">Margem de Lucro Bruta</span>
                        <span class="text-sm font-black text-primary">
                            <?php echo number_format($p['margin'], 1, ',', '.'); ?>%
                        </span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-3">
                        <div class="bg-primary h-3 rounded-full shadow-inner transition-all duration-1000"
                            style="width: <?php echo min(100, $p['margin']); ?>%"></div>
                    </div>
                </div>
            </div>

            <!-- Footer do Card -->
            <div class="p-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2 text-xs text-gray-400">
                <i class="fas fa-chart-line"></i>
                <span>Média de Ticket: R$
                    <?php echo $p['sales_count'] > 0 ? number_format($p['revenue'] / $p['sales_count'], 2, ',', '.') : '0,00'; ?>
                </span>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Comparativo Visual (Opcional, se houver muitos dados) -->
<?php if (count($performance) > 1): ?>
    <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
        <h3 class="font-black text-gray-800 uppercase tracking-widest text-center mb-8">Participação no Faturamento Total
        </h3>
        <div class="flex items-center justify-center gap-1">
            <?php
            $totalRev = array_sum(array_column($performance, 'revenue'));
            foreach ($performance as $idx => $p):
                $perc = $totalRev > 0 ? ($p['revenue'] / $totalRev) * 100 : 0;
                $colors = ['bg-primary', 'bg-blue-400', 'bg-indigo-400'];
                ?>
                <div class="h-12 flex items-center justify-center text-[10px] font-bold text-white transition-all hover:scale-105 first:rounded-l-full last:rounded-r-full <?php echo $colors[$idx % 3]; ?>"
                    style="width: <?php echo $perc; ?>%" title="<?php echo $p['sector_name']; ?>">
                    <?php echo $perc > 5 ? number_format($perc, 0) . '%' : ''; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="flex justify-center gap-6 mt-6 flex-wrap">
            <?php foreach ($performance as $idx => $p): ?>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full <?php echo ['bg-primary', 'bg-blue-400', 'bg-indigo-400'][$idx % 3]; ?>">
                    </div>
                    <span class="text-xs font-bold text-gray-600 uppercase">
                        <?php echo $p['sector_name']; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

</div><!-- /.flex.flex-col.gap-6 -->

<?php require 'views/layouts/footer.php'; ?>