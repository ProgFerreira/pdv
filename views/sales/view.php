<?php require 'views/layouts/header.php'; ?>
<?php
$isCancelled = isset($sale['status']) && $sale['status'] === 'cancelled';
?>
<div class="mb-6">
    <div class="flex justify-between items-center flex-wrap gap-4">
        <div class="flex items-center gap-3">
            <h2 class="text-2xl font-bold text-gray-800">🧾 Detalhes da Venda #<?php echo $sale['id']; ?></h2>
            <?php if ($isCancelled): ?>
                <span class="px-3 py-1 rounded-full text-sm font-bold bg-red-100 text-red-700">Cancelada</span>
            <?php endif; ?>
        </div>
        <a href="<?php echo htmlspecialchars(BASE_URL ?? '', ENT_QUOTES, 'UTF-8'); ?>?route=sale/index"
            class="inline-flex items-center gap-2 bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded shadow transition-colors">
            <i class="fas fa-arrow-left"></i>Voltar
        </a>
    </div>
</div>

<?php if (isset($_GET['error'])): ?>
    <div class="mb-6 p-4 rounded-lg flex items-center gap-3 <?php echo $_GET['error'] === 'already_cancelled' ? 'bg-amber-50 border border-amber-200 text-amber-800' : 'bg-red-50 border border-red-200 text-red-800'; ?>">
        <i class="fas fa-exclamation-circle"></i>
        <span><?php
            if ($_GET['error'] === 'already_cancelled') echo 'Esta venda já está cancelada.';
            else echo 'Não foi possível cancelar a venda. Tente novamente.';
        ?></span>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Sale Info Card -->
    <div class="bg-white shadow-md rounded-lg p-6 border border-gray-200">
        <h3 class="text-lg font-bold text-gray-700 mb-4">Informações da Venda</h3>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">Data/Hora:</span>
                <span class="font-medium">
                    <?php echo date('d/m/Y H:i', strtotime($sale['created_at'])); ?>
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Vendedor:</span>
                <span class="font-medium">
                    <?php echo $sale['user_name'] ?? '-'; ?>
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Cliente:</span>
                <span class="font-medium">
                    <?php echo htmlspecialchars(trim((string)($sale['customer_name'] ?? '')) ?: 'Não informado', ENT_QUOTES, 'UTF-8'); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Payment Info Card -->
    <div class="bg-white shadow-md rounded-lg p-6 border border-gray-200">
        <h3 class="text-lg font-bold text-gray-700 mb-4">Pagamento</h3>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">Forma:</span>
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                    <?php echo $sale['payment_method']; ?>
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Valor Pago:</span>
                <span class="font-medium">R$
                    <?php echo number_format($sale['amount_paid'], 2, ',', '.'); ?>
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Troco:</span>
                <span class="font-medium">R$
                    <?php echo number_format($sale['change_amount'], 2, ',', '.'); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Total Card -->
    <div
        class="bg-gradient-to-br from-green-500 to-green-600 shadow-md rounded-lg p-6 text-white flex flex-col justify-center">
        <?php
        $subtotalItems = 0;
        foreach ($sale['items'] as $item)
            $subtotalItems += $item['subtotal'];
        $discount = $sale['discount_amount'] ?? 0;
        ?>
        <div class="flex justify-between items-center text-sm opacity-90 mb-1">
            <span>Subtotal:</span>
            <span>R$ <?php echo number_format($subtotalItems, 2, ',', '.'); ?></span>
        </div>
        <?php if ($discount > 0): ?>
            <div class="flex justify-between items-center text-sm opacity-90 mb-3 pb-2 border-b border-white/20">
                <span>Desconto:</span>
                <span>- R$ <?php echo number_format($discount, 2, ',', '.'); ?></span>
            </div>
        <?php endif; ?>
        <h3 class="text-lg font-bold mb-1">Total da Venda</h3>
        <div class="text-4xl font-bold">R$
            <?php echo number_format($sale['total'], 2, ',', '.'); ?>
        </div>
    </div>
</div>

<!-- Items Table -->
<div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="text-lg font-bold text-gray-700">Itens da Venda</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Qtd
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Preço
                        Unit.</th>
                    <th scope="col"
                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php
                $sumBruto = 0;
                foreach ($sale['items'] as $item):
                    $sumBruto += $item['subtotal'];
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <?php if (!empty($item['product_image'])): ?>
                                    <img src="<?php echo $item['product_image']; ?>"
                                        class="h-10 w-10 rounded-full object-cover mr-3">
                                <?php else: ?>
                                    <div
                                        class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mr-3">
                                        <i class="fas fa-box"></i>
                                    </div>
                                <?php endif; ?>
                                <span class="text-sm font-medium text-gray-900">
                                    <?php echo $item['product_name']; ?>
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                            <?php echo $item['quantity']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                            R$ <?php echo number_format($item['unit_price'], 2, ',', '.'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">
                            R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                <tr>
                    <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Subtotal Bruto:</td>
                    <td class="px-6 py-3 text-right text-sm font-bold text-gray-900">R$
                        <?php echo number_format($sumBruto, 2, ',', '.'); ?></td>
                </tr>
                <?php if ($sale['discount_amount'] > 0): ?>
                    <tr>
                        <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-red-600">Desconto Aplicado:
                        </td>
                        <td class="px-6 py-3 text-right text-sm font-bold text-red-600">- R$
                            <?php echo number_format($sale['discount_amount'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endif; ?>
                <tr class="bg-gray-100">
                    <td colspan="3" class="px-6 py-4 text-right text-base font-bold text-gray-900">TOTAL LÍQUIDO:</td>
                    <td class="px-6 py-4 text-right text-xl font-black text-green-600">R$
                        <?php echo number_format($sale['total'], 2, ',', '.'); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php
// Monta texto do pedido para WhatsApp (resumo)
$whatsappLines = [
    '📋 *Pedido #' . (int)$sale['id'] . '*',
    'Data: ' . date('d/m/Y H:i', strtotime($sale['created_at'])),
    'Cliente: ' . (trim((string)($sale['customer_name'] ?? '')) ?: 'Não informado'),
    '',
    '*Itens:*'
];
foreach ($sale['items'] as $item) {
    $whatsappLines[] = '• ' . $item['product_name'] . ' x ' . (int)$item['quantity'] . ' - R$ ' . number_format($item['subtotal'], 2, ',', '.');
}
$whatsappLines[] = '';
if (($sale['discount_amount'] ?? 0) > 0) {
    $whatsappLines[] = 'Desconto: - R$ ' . number_format($sale['discount_amount'], 2, ',', '.');
    $whatsappLines[] = '';
}
$whatsappLines[] = '*Total: R$ ' . number_format($sale['total'], 2, ',', '.') . '*' . "\n" . 'Pagamento: ' . ($sale['payment_method'] ?? '-');
$whatsappText = implode("\n", $whatsappLines);
$customerPhone = isset($sale['customer_phone']) ? preg_replace('/\D/', '', trim($sale['customer_phone'])) : '';
?>

<?php $baseUrl = htmlspecialchars(BASE_URL ?? '', ENT_QUOTES, 'UTF-8'); ?>
<div class="mt-6 flex flex-wrap items-center justify-end gap-3">
    <button type="button" id="btn-whatsapp-sale" data-whatsapp-text="<?php echo htmlspecialchars($whatsappText, ENT_QUOTES, 'UTF-8'); ?>" data-customer-phone="<?php echo htmlspecialchars($customerPhone, ENT_QUOTES, 'UTF-8'); ?>"
        class="inline-flex items-center gap-2 bg-[#25D366] hover:bg-[#20BD5A] text-white font-bold py-2 px-6 rounded shadow transition-colors">
        <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        Enviar por WhatsApp
    </button>
    <a href="<?php echo $baseUrl; ?>?route=pos/receipt&id=<?php echo (int) $sale['id']; ?>"
        target="_blank"
        class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white font-bold py-2 px-6 rounded shadow transition-colors">
        <i class="fas fa-print"></i>Imprimir cupom
    </a>
    <?php if (!$isCancelled): ?>
        <a href="<?php echo $baseUrl; ?>?route=sale/open&id=<?php echo (int) $sale['id']; ?>"
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded shadow transition-colors">
            <i class="fas fa-folder-open"></i>Abrir no PDV
        </a>
        <?php if (hasPermission('sale_cancel')): ?>
            <a href="<?php echo $baseUrl; ?>?route=sale/cancel&id=<?php echo (int) $sale['id']; ?>"
                onclick="return confirm('Cancelar esta venda? O estoque, caixa e fiado/vales serão estornados. Esta ação fica registrada em log.');"
                class="inline-flex items-center gap-2 bg-red-100 hover:bg-red-200 text-red-700 font-bold py-2 px-6 rounded shadow transition-colors border border-red-200">
                <i class="fas fa-times-circle"></i>Cancelar venda
            </a>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Modal WhatsApp: informar número para enviar o pedido -->
<div id="modal-whatsapp-sale" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-modal="true" role="dialog">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 transition-opacity" aria-hidden="true" id="modal-whatsapp-backdrop"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-2">Enviar pedido por WhatsApp</h3>
            <p class="text-sm text-gray-500 mb-4">Informe o número com DDD (ex: 11999998888). O pedido será enviado como mensagem.</p>
            <label class="block text-sm font-medium text-gray-700 mb-1">Número do celular</label>
            <input type="tel" id="whatsapp-phone-input" placeholder="11999998888" maxlength="15"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
            <div class="mt-4 flex gap-2 justify-end">
                <button type="button" id="modal-whatsapp-cancel" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancelar</button>
                <button type="button" id="modal-whatsapp-send" class="px-4 py-2 rounded-lg bg-[#25D366] text-white hover:bg-[#20BD5A] font-medium">Abrir WhatsApp</button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var btn = document.getElementById('btn-whatsapp-sale');
    var modal = document.getElementById('modal-whatsapp-sale');
    var input = document.getElementById('whatsapp-phone-input');
    var backdrop = document.getElementById('modal-whatsapp-backdrop');
    var sendBtn = document.getElementById('modal-whatsapp-send');
    var cancelBtn = document.getElementById('modal-whatsapp-cancel');

    if (!btn || !modal) return;

    var whatsappText = btn.getAttribute('data-whatsapp-text') || '';
    var customerPhone = (btn.getAttribute('data-customer-phone') || '').replace(/\D/g, '');

    function onlyDigits(s) { return (s || '').replace(/\D/g, ''); }
    function openModal() {
        input.value = customerPhone ? (customerPhone.length <= 11 ? '55' + customerPhone : customerPhone) : '';
        modal.classList.remove('hidden');
        input.focus();
    }
    function closeModal() {
        modal.classList.add('hidden');
    }

    btn.addEventListener('click', openModal);
    cancelBtn.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);

    // Abrir modal automaticamente se veio da listagem com ?whatsapp=1
    if (window.location.search.indexOf('whatsapp=1') !== -1) {
        openModal();
        var qs = window.location.search.replace(/&?whatsapp=1&?/, '&').replace(/^&/, '?').replace(/&$/, '');
        history.replaceState({}, '', window.location.pathname + (qs || '?') + window.location.hash);
    }

    sendBtn.addEventListener('click', function() {
        var raw = onlyDigits(input.value);
        if (raw.length < 10) {
            alert('Informe um número válido com DDD (ex: 11999998888).');
            return;
        }
        var number = raw.length <= 11 ? '55' + raw : raw;
        var url = 'https://wa.me/' + number + '?text=' + encodeURIComponent(whatsappText);
        window.open(url, '_blank', 'noopener');
        closeModal();
    });

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') sendBtn.click();
    });
})();
</script>

<?php require 'views/layouts/footer.php'; ?>