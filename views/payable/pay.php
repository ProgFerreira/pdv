<?php require 'views/layouts/header.php'; ?>

<div class="mb-6">
    <a href="?route=payable/index" class="text-primary hover:text-primary-hover text-sm font-medium mb-2 inline-block">
        <i class="fas fa-arrow-left mr-1"></i> Voltar para a lista
    </a>
    <h2 class="text-2xl font-bold text-gray-800">💰 Baixar Pagamento (Contas a Pagar)</h2>
</div>

<div class="max-w-xl mx-auto">
    <?php if (isset($_GET['error']) && $_GET['error'] == 'cash_closed'): ?>
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0"><i class="fas fa-exclamation-circle text-red-400"></i></div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">
                        O caixa deve estar <strong>ABERTO</strong> para realizar um pagamento.
                        <a href="?route=cash/history" class="font-bold underline">Abrir caixa agora.</a>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
        <div class="p-6 bg-gray-50 border-b">
            <h3 class="font-bold text-gray-700">Detalhes da Despesa</h3>
            <p class="text-xl text-primary font-bold mt-1">
                <?php echo htmlspecialchars($payable['description']); ?>
            </p>
            <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-400 uppercase text-[10px] font-bold block">Total da Dívida</span>
                    <span class="font-bold text-gray-800">R$
                        <?php echo number_format($payable['total_amount'], 2, ',', '.'); ?>
                    </span>
                </div>
                <div>
                    <span class="text-gray-400 uppercase text-[10px] font-bold block">Valor Restante</span>
                    <span class="font-bold text-red-600">R$
                        <?php echo number_format($payable['total_amount'] - $payable['paid_amount'], 2, ',', '.'); ?>
                    </span>
                </div>
            </div>
        </div>

        <form action="?route=payable/pay&id=<?php echo (int) $payable['id']; ?>" method="POST"
            class="p-6 space-y-4 text-left">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Valor a ser Pago agora (R$)</label>
                <input type="number" step="0.01" name="amount" required
                    value="<?php echo number_format($payable['total_amount'] - $payable['paid_amount'], 2, '.', ''); ?>"
                    class="w-full text-2xl font-bold text-primary border-gray-300 rounded-md shadow-sm p-3 focus:border-primary focus:ring-primary">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Observações do Pagamento</label>
                <textarea name="notes" rows="2"
                    class="w-full border-gray-300 rounded-md shadow-sm p-2 text-sm focus:border-primary focus:ring-primary"
                    placeholder="Ex: Pago via PIX, dinheiro do caixa..."></textarea>
            </div>

            <div class="pt-4">
                <button type="submit"
                    class="w-full py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg transition shadow-md flex items-center justify-center gap-2">
                    <i class="fas fa-check-circle"></i> Confirmar Pagamento
                </button>
            </div>
        </form>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>