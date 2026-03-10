<?php require dirname(__DIR__) . '/layouts/header.php'; ?>
<?php
$displayName = trim($order['customer_name'] ?? $order['guest_name'] ?? '');
if ($displayName === '') {
    $displayName = 'Não informado';
}
$displayPhone = trim($order['customer_phone'] ?? $order['guest_phone'] ?? '');
$isPending = ($order['status'] ?? '') === 'pending';
$canAddItems = empty($order['sale_id']); // pedido ainda não convertido em venda
$errorMsg = [
    'caixa_fechado' => 'Abra o caixa antes de converter o pedido em venda.',
    'order_invalid' => 'Pedido não encontrado ou já foi convertido/cancelado.',
    'empty' => 'Pedido sem itens.',
    'customer' => 'Erro ao criar cliente.',
    'sale' => 'Erro ao criar venda. Tente novamente.',
];
$getError = $_GET['error'] ?? '';
?>
<div class="mb-6">
    <div class="flex justify-between items-center flex-wrap gap-4">
        <h2 class="text-2xl font-bold text-gray-800">Pedido #<?php echo (int) $order['id']; ?></h2>
        <a href="<?php echo htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8'); ?>?route=order/index" class="inline-flex items-center gap-2 py-2 px-4 rounded-lg font-bold shadow transition-colors no-underline border-2 border-slate-600 bg-slate-600 hover:bg-slate-700 hover:border-slate-700 text-white" style="color: #fff !important;">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<?php if ($getError && isset($errorMsg[$getError])): ?>
<div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 flex items-center gap-3">
    <i class="fas fa-exclamation-circle"></i>
    <span><?php echo htmlspecialchars($errorMsg[$getError], ENT_QUOTES, 'UTF-8'); ?></span>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white shadow-md rounded-lg p-6 border border-gray-200">
        <h3 class="text-lg font-bold text-gray-700 mb-4">Cliente</h3>
        <div class="space-y-2 text-sm">
            <p><span class="text-gray-500">Nome:</span> <?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></p>
            <p><span class="text-gray-500">Telefone:</span> <?php echo htmlspecialchars($displayPhone, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php if (!empty($order['guest_email'])): ?>
            <p><span class="text-gray-500">E-mail:</span> <?php echo htmlspecialchars($order['guest_email'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <p><span class="text-gray-500">Entrega:</span> <?php echo !empty($order['is_pickup']) ? 'Retirada no local' : 'Entrega'; ?></p>
            <?php if (empty($order['is_pickup']) && !empty($order['delivery_address'])): ?>
            <p class="text-gray-600 mt-2"><?php echo nl2br(htmlspecialchars($order['delivery_address'], ENT_QUOTES, 'UTF-8')); ?></p>
            <?php endif; ?>
            <?php if (!empty($order['observation'])): ?>
            <p class="mt-2"><span class="text-gray-500">Obs:</span> <?php echo htmlspecialchars($order['observation'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="lg:col-span-2 bg-white shadow-md rounded-lg p-6 border border-gray-200">
        <h3 class="text-lg font-bold text-gray-700 mb-4">Itens</h3>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Produto</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qtd</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Unit.</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($items as $row): ?>
                <tr>
                    <td class="px-4 py-2 text-sm text-gray-900"><?php echo htmlspecialchars($row['product_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td class="px-4 py-2 text-sm text-right"><?php echo (int) $row['quantity']; ?></td>
                    <td class="px-4 py-2 text-sm text-right">R$ <?php echo number_format((float) $row['unit_price'], 2, ',', '.'); ?></td>
                    <td class="px-4 py-2 text-sm text-right font-medium">R$ <?php echo number_format((float) $row['subtotal'], 2, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="mt-4 text-right font-bold text-gray-900">Total: R$ <?php echo number_format((float) $order['total'], 2, ',', '.'); ?></p>

        <?php if ($canAddItems): ?>
        <div class="mt-4 flex flex-wrap gap-3 items-center">
            <a href="<?php echo htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8'); ?>?route=order/index" class="inline-flex items-center gap-2 py-2.5 px-4 rounded-lg font-bold shadow no-underline border-2 border-emerald-600 bg-emerald-600 hover:bg-emerald-700 text-white" style="color: #fff !important;">
                <i class="fas fa-plus-circle"></i> Adicionar itens
            </a>
        </div>
        <?php endif; ?>
        <?php if ($isPending): ?>
        <form method="post" action="<?php echo BASE_URL; ?>?route=order/convertToSale" class="mt-6 pt-6 border-t border-gray-200">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded shadow transition-colors">
                <i class="fas fa-cash-register"></i> Transformar em venda
            </button>
        </form>
        <?php elseif (!empty($order['sale_id'])): ?>
        <p class="mt-6 pt-6 border-t border-gray-200">
            <a href="<?php echo BASE_URL; ?>?route=sale/view&id=<?php echo (int) $order['sale_id']; ?>" class="text-green-600 hover:underline font-medium">Ver venda #<?php echo (int) $order['sale_id']; ?></a>
        </p>
        <?php endif; ?>
    </div>
</div>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
