<?php require 'views/layouts/header.php'; ?>

<div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 uppercase tracking-tight">🤝 Acerto de Consignados</h2>
        <p class="text-sm text-gray-500">Relatório de vendas para pagamento de fornecedores parceiros.</p>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <form class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 items-end">
            <input type="hidden" name="route" value="report/consigned">

            <div class="w-64">
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">🚚 Fornecedor</label>
                <select name="supplier_id" class="w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 bg-white text-sm">
                    <option value="">Todos os Parceiros</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?php echo $s['id']; ?>" <?php echo (($_GET['supplier_id'] ?? '') == $s['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($s['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">📅 De</label>
                <input type="date" name="start_date" class="rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 text-sm"
                    value="<?php echo $startDate; ?>">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">📅 Até</label>
                <input type="date" name="end_date" class="rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 text-sm"
                    value="<?php echo $endDate; ?>">
            </div>

            <button type="submit"
                class="bg-primary hover:bg-primary-hover text-white p-2.5 rounded-lg shadow-sm transition-colors">
                <i class="fas fa-filter"></i>
            </button>
            <a href="?route=report/consigned"
                class="bg-gray-100 hover:bg-gray-200 text-gray-400 p-2.5 rounded-lg border border-gray-200 transition-colors">
                <i class="fas fa-eraser"></i>
            </a>
        </form>
    </div>
</div>

<?php if (!empty($items)): ?>
    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100 mb-8">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            Fornecedor</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            Produto</th>
                        <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Qtd
                            Vendida</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            Venda Média</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            Total a Acertar</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php
                    $grandTotal = 0;
                    $currentSupplier = '';
                    foreach ($items as $index => $item):
                        $grandTotal += $item['total_revenue'];
                        ?>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($currentSupplier !== $item['supplier_name']): ?>
                                    <span class="text-sm font-black text-gray-800 uppercase">
                                        <?php echo htmlspecialchars($item['supplier_name']); ?>
                                    </span>
                                    <?php $currentSupplier = $item['supplier_name']; ?>
                                <?php else: ?>
                                    <span class="text-gray-300 text-xs">"</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo htmlspecialchars($item['product_name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-primary">
                                <?php echo $item['total_qty']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-400">
                                R$
                                <?php echo number_format($item['avg_price'], 2, ',', '.'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-black text-gray-900">
                                R$
                                <?php echo number_format($item['total_revenue'], 2, ',', '.'); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-gray-900 text-white">
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-right font-bold uppercase tracking-widest text-xs">Total do
                            Período:</td>
                        <td class="px-6 py-4 text-right font-black text-xl">
                            R$
                            <?php echo number_format($grandTotal, 2, ',', '.'); ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="flex justify-end">
        <button onclick="window.print()"
            class="bg-gray-800 hover:bg-black text-white px-8 py-3 rounded-xl font-bold shadow-lg transition-all transform hover:scale-105 flex items-center gap-2">
            <i class="fas fa-print"></i> Imprimir para Acerto
        </button>
    </div>
<?php else: ?>
    <div class="bg-white p-16 rounded-2xl shadow-sm border border-dashed border-gray-300 text-center">
        <div class="bg-gray-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-handshake text-3xl text-gray-200"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-400 uppercase tracking-widest">Nenhuma venda de consignado</h3>
        <p class="text-sm text-gray-400 mt-1">Ajuste o período ou certifique-se de que os produtos estão marcados como
            consignados.</p>
    </div>
<?php endif; ?>

<?php require 'views/layouts/footer.php'; ?>