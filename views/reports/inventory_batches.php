<?php require 'views/layouts/header.php'; ?>

<div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 uppercase tracking-tight">📋 Logística de Estoque (Lotes/NF)</h2>
        <p class="text-sm text-gray-500">Visualize seu estoque organizado por data de entrada e Nota Fiscal para
            controle FIFO.</p>
    </div>
    <div class="flex gap-2">
        <button onclick="window.print()"
            class="btn btn-primary bg-gray-800 hover:bg-black text-white border-none px-6 py-2.5 rounded-lg font-black shadow-md transition-all flex items-center gap-2 active:scale-95">
            <i class="fas fa-print"></i> Imprimir Lista
        </button>
    </div>
</div>

<div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Entrada / NF</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Data
                        Entrada</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Produto</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Fornecedor da Nota</th>
                    <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Qtd. Restante</th>
                    <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Idade (Dias)</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                <?php if (empty($batches)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center text-gray-400 italic">
                            Nenhum lote com estoque disponível no momento.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($batches as $b):
                        $entryDate = new DateTime($b['entry_date']);
                        $today = new DateTime();
                        $age = $today->diff($entryDate)->days;
                        $ageColor = $age > 30 ? 'text-red-600 font-bold' : ($age > 15 ? 'text-amber-600' : 'text-emerald-600');
                        ?>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-700">
                                #
                                <?php echo $b['nf_reference'] ?: $b['stock_entry_id']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                                <?php echo date('d/m/Y', strtotime($b['entry_date'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-gray-900">
                                <?php echo htmlspecialchars($b['product_name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($b['entry_supplier'] ?: '-'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="bg-primary/10 text-primary font-black px-3 py-1 rounded-full text-xs">
                                    <?php echo number_format($b['current_quantity'], 0); ?> un
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm <?php echo $ageColor; ?>">
                                <?php echo $age; ?> dias
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6 bg-blue-50 p-4 rounded-xl border border-blue-100 flex items-start gap-3">
    <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
    <p class="text-xs text-blue-800 leading-relaxed">
        <strong>Dica de Logística:</strong> Os itens no topo da lista são os que entraram há mais tempo. <br>
        Priorize a venda e exposição destes produtos para manter um estoque sempre novo (Giro FIFO).
    </p>
</div>

<?php require 'views/layouts/footer.php'; ?>