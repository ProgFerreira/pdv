<?php require 'views/layouts/header.php'; ?>

<div class="max-w-2xl mx-auto">
    <div class="mb-6 text-center">
        <h2 class="text-3xl font-bold text-gray-800">🔐 Fechamento de Caixa</h2>
        <p class="text-gray-500">Confira os valores antes de fechar.</p>
    </div>

    <!-- Vendas por Forma de Pagamento -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden border border-gray-200 mb-6">
        <div class="p-6 bg-gray-50 border-b border-gray-200">
            <h5 class="font-bold text-gray-700">Vendas por Forma de Pagamento</h5>
        </div>
        <div class="p-6 space-y-3">
            <?php if (empty($paymentMethods)): ?>
                <p class="text-gray-500 text-center py-4">Nenhuma venda registrada neste turno.</p>
            <?php else: ?>
                <?php 
                $totalVendas = 0;
                foreach ($paymentMethods as $pm): 
                    $totalVendas += $pm['total'];
                ?>
                    <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                        <span class="text-gray-600">
                            <?php echo htmlspecialchars($pm['payment_method']); ?>
                            <span class="text-xs text-gray-400">(<?php echo $pm['count']; ?>x)</span>
                        </span>
                        <span class="font-bold text-gray-800">R$
                            <?php echo number_format($pm['total'], 2, ',', '.'); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
                <div class="flex justify-between items-center pt-2 bg-gray-50 p-3 rounded mt-3">
                    <span class="text-lg font-bold text-gray-800">Total de Vendas</span>
                    <span class="text-lg font-bold text-green-600">R$
                        <?php echo number_format($totalVendas, 2, ',', '.'); ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Resumo do Caixa (Dinheiro) -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden border border-gray-200 mb-6">
        <div class="p-6 bg-gray-50 border-b border-gray-200">
            <h5 class="font-bold text-gray-700">Resumo do Caixa (Dinheiro)</h5>
        </div>
        <div class="p-6 space-y-4">
            <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                <span class="text-gray-600">Fundo de Troco (Abertura)</span>
                <span class="font-bold text-gray-800">R$
                    <?php echo number_format($summary['opening'], 2, ',', '.'); ?>
                </span>
            </div>
            <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                <span class="text-green-600 font-medium">+ Vendas em Dinheiro</span>
                <span class="font-bold text-green-600">R$
                    <?php echo number_format($summary['sales'], 2, ',', '.'); ?>
                </span>
            </div>
            <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                <span class="text-blue-600 font-medium">+ Suprimentos</span>
                <span class="font-bold text-blue-600">R$
                    <?php echo number_format($summary['supply'], 2, ',', '.'); ?>
                </span>
            </div>
            <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                <span class="text-red-500 font-medium">- Sangrias</span>
                <span class="font-bold text-red-500">R$
                    <?php echo number_format($summary['bleed'], 2, ',', '.'); ?>
                </span>
            </div>

            <div class="flex justify-between items-center pt-2 bg-gray-50 p-3 rounded">
                <span class="text-xl font-bold text-gray-800">Saldo Esperado em Caixa</span>
                <span class="text-xl font-bold text-primary">R$
                    <?php echo number_format($summary['current_balance'], 2, ',', '.'); ?>
                </span>
            </div>
        </div>
    </div>

    <form method="POST" class="bg-white shadow-lg rounded-lg p-6 border border-gray-200">
        <?php echo csrf_field(); ?>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Valor em Caixa (Informado)</label>
            <div class="relative rounded-md shadow-sm">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <span class="text-gray-500 sm:text-sm">R$</span>
                </div>
                <input type="text" name="closing_balance" required
                    class="w-full rounded-md border border-gray-300 pl-10 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 sm:text-lg"
                    placeholder="0,00" value="<?php echo number_format($summary['current_balance'], 2, ',', ''); ?>">
            </div>
            <p class="text-xs text-gray-500 mt-1">Informe o valor físico contado na gaveta.</p>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Observações (Quebra/Sobra)</label>
            <textarea name="notes" rows="3"
                class="w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"></textarea>
        </div>

        <div class="flex font-bold gap-3">
            <a href="?route=dashboard/index"
                class="w-full bg-gray-500 hover:bg-gray-600 text-white py-3 rounded-lg text-center transition-colors">
                Cancelar
            </a>
            <button type="submit"
                class="w-full bg-red-600 hover:bg-red-700 text-white py-3 rounded-lg transition-colors"
                onclick="return confirm('Confirma o fechamento do caixa?');">
                <i class="fas fa-lock mr-2"></i> Fechar Caixa
            </button>
        </div>
    </form>
</div>

<?php require 'views/layouts/footer.php'; ?>