<?php require 'views/layouts/header.php'; ?>

<div class="receipt-print-area max-w-2xl mx-auto px-4 py-8">
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
        <!-- Cabeçalho -->
        <div class="bg-gradient-to-br from-emerald-50 to-teal-50 px-6 py-8 text-center border-b border-gray-100">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 mb-4">
                <i class="fas fa-check-circle text-4xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Venda Realizada!</h1>
            <p class="text-lg text-gray-500 mt-1">Venda #<?php echo str_pad($sale['id'], 6, '0', STR_PAD_LEFT); ?></p>
        </div>

        <div class="p-6 sm:p-8">
            <?php
            $displayCustomerName = !empty($sale['customer_name'])
                ? $sale['customer_name']
                : (null);
            if ($displayCustomerName === null && !empty($sale['customer_id'])) {
                $custModel = new Customer();
                $cust = $custModel->getById($sale['customer_id']);
                $displayCustomerName = isset($cust['name']) ? $cust['name'] : '';
            }
            if (!empty($displayCustomerName)):
            ?>
                <div class="mb-6 p-4 rounded-xl bg-sky-50 border border-sky-100">
                    <p class="text-xs font-medium text-sky-600 uppercase tracking-wider mb-1">Cliente</p>
                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($displayCustomerName); ?></p>
                    <?php
                    $displayPhone = !empty($sale['customer_phone']) ? trim($sale['customer_phone']) : '';
                    if ($displayPhone === '' && !empty($sale['customer_id'])) {
                        $custModel = isset($custModel) ? $custModel : new \App\Models\Customer();
                        $cust = $custModel->getById($sale['customer_id']);
                        $displayPhone = isset($cust['phone']) ? trim((string)$cust['phone']) : '';
                    }
                    if ($displayPhone !== ''): ?>
                        <p class="text-sm text-gray-600 mt-1">Tel: <?php echo htmlspecialchars($displayPhone); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($sale['is_pickup'])): ?>
                        <p class="text-xs font-medium text-emerald-600 uppercase tracking-wider mt-3 mb-1">Retirada no local</p>
                    <?php elseif (!empty($sale['delivery_address'])): ?>
                        <p class="text-xs font-medium text-sky-600 uppercase tracking-wider mt-3 mb-1">Endereço de entrega</p>
                        <p class="text-sm text-gray-700"><?php echo htmlspecialchars($sale['delivery_address']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Itens -->
            <div class="mb-6">
                <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3">Itens</h2>
                <ul class="divide-y divide-gray-100 border border-gray-200 rounded-xl overflow-hidden">
                    <?php foreach ($sale['items'] as $item): ?>
                        <li class="flex justify-between items-start gap-4 px-4 py-3 bg-white hover:bg-gray-50/50 transition-colors">
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-gray-800">
                                    <?php echo (int) $item['quantity']; ?>× <?php echo htmlspecialchars($item['product_name']); ?>
                                </p>
                                <p class="text-sm text-gray-500 mt-0.5">
                                    R$ <?php echo number_format((float) $item['unit_price'], 2, ',', '.'); ?> un.
                                </p>
                            </div>
                            <span class="font-bold text-gray-900 whitespace-nowrap">
                                R$ <?php echo number_format((float) $item['subtotal'], 2, ',', '.'); ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                    <li class="flex justify-between items-center px-4 py-4 bg-gray-50 font-bold text-gray-900 border-t-2 border-gray-200">
                        <span>TOTAL</span>
                        <span class="text-xl text-primary">R$ <?php echo number_format((float) $sale['total'], 2, ',', '.'); ?></span>
                    </li>
                </ul>
            </div>

            <!-- Pagamento -->
            <div class="grid grid-cols-2 gap-4 mb-8">
                <div class="p-4 rounded-xl bg-gray-50 border border-gray-100">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Pagamento</p>
                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($sale['payment_method'] ?? '-'); ?></p>
                </div>
                <div class="p-4 rounded-xl bg-gray-50 border border-gray-100">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Troco</p>
                    <p class="font-semibold text-gray-800">R$ <?php echo number_format((float) ($sale['change_amount'] ?? 0), 2, ',', '.'); ?></p>
                </div>
            </div>

            <!-- Botões (ocultos na impressão) -->
            <div class="no-print print:hidden space-y-4">
                <p class="text-sm font-medium text-gray-500 mb-3">Imprimir <span class="text-gray-400 font-normal">(atalhos: <kbd class="px-1.5 py-0.5 rounded bg-gray-200 text-xs">F1</kbd> A4 · <kbd class="px-1.5 py-0.5 rounded bg-gray-200 text-xs">F3</kbd> térmica)</span></p>
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button" id="btn-print-a4" onclick="window.print()"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border-2 border-gray-200 bg-white text-gray-700 font-semibold hover:bg-gray-50 hover:border-gray-300 transition-all">
                        <i class="fas fa-print"></i>
                        Imprimir (A4) <kbd class="ml-1 text-xs font-normal opacity-75">F1</kbd>
                    </button>
                    <button type="button" id="btn-receipt-thermal"
                        onclick="window.open('?route=pos/receipt_thermal&id=<?php echo (int) $sale['id']; ?>', '_blank', 'width=360,height=640')"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border-2 border-gray-200 bg-white text-gray-700 font-semibold hover:bg-gray-50 hover:border-gray-300 transition-all">
                        <i class="fas fa-receipt"></i>
                        Cupom Térmica <kbd class="ml-1 text-xs font-normal opacity-75">F3</kbd>
                    </button>
                </div>

                <div class="pt-2">
                    <a href="<?php echo htmlspecialchars(BASE_URL); ?>?route=pos/index"
                        class="block w-full text-center py-4 px-6 rounded-xl bg-primary hover:bg-primary-hover text-white font-bold text-lg shadow-lg shadow-primary/20 hover:shadow-primary/30 transition-all">
                        <i class="fas fa-cash-register mr-2"></i>
                        Nova Venda
                    </a>
                </div>
            </div>
        </div>

        <!-- Rodapé -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 text-center">
            <p class="text-gray-500 text-sm">Obrigado, volte sempre 🙏</p>
            <p class="text-gray-400 text-xs mt-1"><?php echo date('d/m/Y H:i', strtotime($sale['created_at'])); ?></p>
        </div>
    </div>
</div>

<style>
@media print {
    /* Imprimir apenas o cupom: esconde nav, footer, botões e tudo fora da área do recibo */
    body * { visibility: hidden; }
    .receipt-print-area,
    .receipt-print-area * { visibility: visible; }
    .no-print,
    .receipt-print-area .no-print { display: none !important; visibility: hidden !important; }
    .receipt-print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        max-width: none;
        padding: 0;
        margin: 0;
    }
    .receipt-print-area .rounded-2xl {
        box-shadow: none;
        border: 1px solid #e5e7eb;
    }
}
</style>

<script>
(function() {
    var saleId = <?php echo (int) $sale['id']; ?>;
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F1') {
            e.preventDefault();
            document.getElementById('btn-print-a4') && document.getElementById('btn-print-a4').click();
        } else if (e.key === 'F3') {
            e.preventDefault();
            window.open('?route=pos/receipt_thermal&id=' + saleId, '_blank', 'width=360,height=640');
        }
    });
})();
</script>

<?php require 'views/layouts/footer.php'; ?>
