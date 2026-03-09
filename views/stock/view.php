<?php require 'views/layouts/header.php'; ?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">📄 Detalhes da Entrada #
            <?php echo $entry['id']; ?>
        </h2>
        <p class="text-sm text-gray-500">Visualizando informações completas desta nota de entrada.</p>
    </div>
    <div class="flex gap-3">
        <a href="?route=stock/addItems&id=<?php echo $entry['id']; ?>"
            class="bg-emerald-100 hover:bg-emerald-200 text-emerald-700 px-4 py-2 rounded-lg font-bold transition-colors flex items-center gap-2">
            <i class="fas fa-plus-circle"></i> Adicionar itens
        </a>
        <a href="?route=stock/edit&id=<?php echo $entry['id']; ?>"
            class="bg-amber-100 hover:bg-amber-200 text-amber-700 px-4 py-2 rounded-lg font-bold transition-colors flex items-center gap-2">
            <i class="fas fa-edit"></i> Editar Cabeçalho
        </a>
        <a href="?route=stock/index"
            class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-lg font-bold transition-colors flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<?php if (isset($_GET['success']) && $_GET['success'] === 'items_added'): ?>
    <div class="mb-6 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3">
        <i class="fas fa-check-circle"></i>
        <span>Novos itens foram adicionados à nota com sucesso.</span>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Nota Fiscal /
            Referência</label>
        <p class="text-lg font-bold text-gray-800">
            <?php echo htmlspecialchars($entry['reference'] ?: 'Sem referência'); ?>
        </p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Fornecedor</label>
        <p class="text-lg font-bold text-gray-800">
            <?php echo htmlspecialchars($entry['supplier'] ?: 'Não informado'); ?>
        </p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Data de Entrada</label>
        <p class="text-lg font-bold text-gray-800">
            <?php echo date('d/m/Y', strtotime($entry['entry_date'])); ?>
        </p>
    </div>
</div>

<div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
    <div class="bg-gray-50/50 px-6 py-4 border-b border-gray-100">
        <h4 class="font-bold text-gray-700">🛒 Itens desta Entrada</h4>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/30">
                <tr>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Produto</th>
                    <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Qtd. Rec.</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Saldo Atual</th>
                    <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Custo Unit.</th>
                    <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Margem</th>
                    <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Subtotal</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                <?php foreach ($entry['items'] as $item):
                    $initial = (float) ($item['initial_quantity'] ?? $item['quantity']);
                    $current = (float) ($item['current_quantity'] ?? 0);
                    $percent = $initial > 0 ? ($current / $initial) * 100 : 0;
                    $barColor = $percent < 20 ? 'bg-red-500' : ($percent < 50 ? 'bg-amber-500' : 'bg-emerald-500');
                    ?>
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">
                            <?php echo htmlspecialchars($item['product_name']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-black text-gray-400">
                            <?php echo number_format($initial, 0); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-16 bg-gray-100 rounded-full h-1 overflow-hidden">
                                    <div class="<?php echo $barColor; ?> h-full transition-all"
                                        style="width: <?php echo $percent; ?>%"></div>
                                </div>
                                <span
                                    class="text-sm font-black <?php echo $percent < 20 ? 'text-red-600' : 'text-gray-800'; ?>">
                                    <?php echo number_format($current, 0); ?>
                                </span>
                            </div>
                            <p class="text-[9px] text-gray-400 uppercase tracking-tighter">Saldo Lote
                                (<?php echo number_format($percent, 0); ?>%)</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500 font-mono">
                            R$ <?php echo number_format($item['cost_price'], 2, ',', '.'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <?php
                            $margin = 0;
                            $productPrice = $item['product_price'] ?? 0;
                            if ($productPrice > 0 && $item['cost_price'] > 0) {
                                $margin = (($productPrice - $item['cost_price']) / $productPrice) * 100;
                            }
                            $marginColor = $margin >= 30 ? 'text-green-700 bg-green-100' : ($margin >= 15 ? 'text-yellow-700 bg-yellow-100' : ($margin > 0 ? 'text-orange-700 bg-orange-100' : 'text-red-700 bg-red-100'));
                            ?>
                            <span class="px-2 py-1 inline-flex text-xs font-bold rounded-full <?php echo $marginColor; ?>" title="Margem de Lucro">
                                <?php echo $margin > 0 ? number_format($margin, 1, ',', '.') . '%' : '-'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-black text-gray-900 font-mono">
                            R$ <?php echo number_format($item['quantity'] * $item['cost_price'], 2, ',', '.'); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="4"
                        class="px-6 py-4 text-right font-black text-gray-400 uppercase tracking-widest text-[10px]">
                        Total da Nota:</td>
                    <td class="px-6 py-4 text-right font-black text-xl text-gray-800 font-mono">
                        R$
                        <?php echo number_format($entry['total_amount'], 2, ',', '.'); ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php if (!empty($entry['notes'])): ?>
    <div class="mt-8 bg-amber-50 p-6 rounded-2xl border border-amber-100">
        <label class="block text-[10px] font-black text-amber-400 uppercase tracking-widest mb-2">Observações
            Internas</label>
        <p class="text-sm text-amber-900 leading-relaxed italic">
            <?php echo nl2br(htmlspecialchars($entry['notes'])); ?>
        </p>
    </div>
<?php endif; ?>

<div class="mt-8 pt-8 border-t border-gray-100 flex justify-between items-center text-xs text-gray-400">
    <div class="flex gap-4">
        <span>Criado por: <strong>
                <?php echo htmlspecialchars($entry['user_name']); ?>
            </strong></span>
        <span>ID da Transação: #
            <?php echo $entry['id']; ?>
        </span>
    </div>
    <div class="flex gap-2 text-red-400 hover:text-red-600 transition-colors cursor-pointer"
        onclick="if(confirm('ATENÇÃO: A exclusão desta entrada irá estornar o estoque de todos os itens. Deseja prosseguir?')) window.location.href='?route=stock/delete&id=<?php echo $entry['id']; ?>'">
        <i class="fas fa-trash"></i> Excluir Entrada Permanentemente
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>