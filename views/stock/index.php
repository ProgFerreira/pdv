<?php require 'views/layouts/header.php'; ?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">📦 Entradas de Estoque</h2>
    <a href="<?php echo BASE_URL; ?>?route=stock/create"
        class="btn bg-indigo-600 hover:bg-indigo-700 text-white border-none rounded-xl shadow-md font-black transition-all active:scale-95 flex items-center gap-2">
        <i class="fas fa-plus"></i> Nova Entrada
    </a>
</div>

<div class="card-standard overflow-hidden">
    <div class="card-standard-header"><i class="fas fa-warehouse"></i> Entradas de Estoque</div>
    <div class="overflow-x-auto overflow-y-visible">
        <table class="min-w-full divide-y divide-gray-200" style="min-width: 960px;">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Referência</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Fornecedor</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Qtd.
                        Inicial</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo
                        Atual / Giro</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider stock-actions-cell">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($entries)): ?>
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">Nenhuma entrada registrada.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($entries as $e):
                        $initial = (float) ($e['total_initial'] ?? 0);
                        $current = (float) ($e['total_current'] ?? 0);
                        $percent = $initial > 0 ? ($current / $initial) * 100 : 0;
                        $barColor = $percent < 20 ? 'bg-red-500' : ($percent < 50 ? 'bg-amber-500' : 'bg-emerald-500');
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 font-mono">
                                #<?php echo $e['id']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d/m/Y', strtotime($e['entry_date'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($e['reference'] ?: '-'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($e['supplier'] ?: '-'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-bold">
                                    <?php echo number_format($initial, 0); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-24 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="<?php echo $barColor; ?> h-full transition-all"
                                            style="width: <?php echo $percent; ?>%"></div>
                                    </div>
                                    <span
                                        class="text-sm font-black <?php echo $percent < 20 ? 'text-red-600' : 'text-gray-800'; ?>">
                                        <?php echo number_format($current, 0); ?>
                                    </span>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1 uppercase tracking-tighter">Restante da Nota
                                    (<?php echo number_format($percent, 0); ?>%)</p>
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold <?php echo $e['total_amount'] > 0 ? 'text-gray-900' : 'text-gray-400'; ?>">
                                R$
                                <?php echo number_format($e['total_amount'], 2, ',', '.'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($e['user_name']); ?>
                            </td>
                            <td class="stock-actions-cell px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-2 flex-nowrap">
                                    <a href="?route=stock/view&id=<?php echo $e['id']; ?>"
                                        class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 bg-blue-50 px-2 py-1.5 rounded-lg transition-colors text-sm"
                                        title="Ver Detalhes"><i class="fas fa-eye"></i><span>Ver</span></a>
                                    <a href="?route=stock/edit&id=<?php echo $e['id']; ?>"
                                        class="inline-flex items-center gap-1 text-amber-600 hover:text-amber-800 bg-amber-50 px-2 py-1.5 rounded-lg transition-colors text-sm"
                                        title="Editar"><i class="fas fa-edit"></i><span>Editar</span></a>
                                    <a href="?route=stock/addItems&id=<?php echo $e['id']; ?>"
                                        class="inline-flex items-center gap-1 text-emerald-600 hover:text-emerald-800 bg-emerald-50 px-2 py-1.5 rounded-lg transition-colors text-sm"
                                        title="Adicionar itens"><i class="fas fa-plus-circle"></i><span>Adicionar itens</span></a>
                                    <a href="?route=stock/delete&id=<?php echo $e['id']; ?>"
                                        onclick="return confirm('ATENÇÃO: A exclusão desta entrada irá estornar o estoque de todos os itens. Deseja prosseguir?')"
                                        class="inline-flex items-center gap-1 text-red-500 hover:text-red-700 bg-red-50 px-2 py-1.5 rounded-lg transition-colors text-sm"
                                        title="Excluir"><i class="fas fa-trash"></i><span>Excluir</span></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>

        </table>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>