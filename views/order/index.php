<?php require dirname(__DIR__) . '/layouts/header.php'; ?>
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Pedidos pelo link</h2>
    <p class="text-gray-600 mt-1">Pedidos feitos pela página pública. Converta em venda para enviar para a fila.</p>
</div>

<div class="bg-white rounded-lg border border-gray-200 shadow-sm mb-4 p-4">
    <form method="get" action="" class="flex flex-wrap items-end gap-4">
        <input type="hidden" name="route" value="order/index">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status" class="rounded-md border border-gray-300 px-3 py-2 text-sm">
                <option value="pending" <?php echo ($filters['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pendentes</option>
                <option value="converted" <?php echo ($filters['status'] ?? '') === 'converted' ? 'selected' : ''; ?>>Convertidos</option>
                <option value="cancelled" <?php echo ($filters['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelados</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data início</label>
            <input type="date" name="start_date" value="<?php echo htmlspecialchars($filters['start_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="rounded-md border border-gray-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data fim</label>
            <input type="date" name="end_date" value="<?php echo htmlspecialchars($filters['end_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="rounded-md border border-gray-300 px-3 py-2 text-sm">
        </div>
        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md hover:opacity-90 text-sm font-medium">Filtrar</button>
    </form>
</div>

<div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Telefone</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php foreach ($orders as $o): ?>
            <?php
                $displayName = trim($o['customer_name'] ?? $o['guest_name'] ?? '');
                if ($displayName === '') {
                    $displayName = 'Não informado';
                }
                $displayPhone = trim($o['customer_phone'] ?? $o['guest_phone'] ?? '');
                $statusLabel = $o['status'] === 'pending' ? 'Pendente' : ($o['status'] === 'converted' ? 'Convertido' : 'Cancelado');
                $statusClass = $o['status'] === 'pending' ? 'bg-amber-100 text-amber-800' : ($o['status'] === 'converted' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800');
            ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm text-gray-900"><?php echo (int) $o['id']; ?></td>
                <td class="px-4 py-3 text-sm text-gray-600"><?php echo date('d/m/Y H:i', strtotime($o['created_at'])); ?></td>
                <td class="px-4 py-3 text-sm text-gray-900"><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($displayPhone, ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="px-4 py-3 text-sm text-right font-medium">R$ <?php echo number_format((float) $o['total'], 2, ',', '.'); ?></td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded text-xs font-medium <?php echo $statusClass; ?>"><?php echo htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="<?php echo BASE_URL; ?>?route=order/view&id=<?php echo (int) $o['id']; ?>" class="text-primary hover:underline text-sm font-medium">Ver</a>
                    <?php if ($o['status'] === 'converted' && !empty($o['sale_id'])): ?>
                    | <a href="<?php echo BASE_URL; ?>?route=sale/view&id=<?php echo (int) $o['sale_id']; ?>" class="text-green-600 hover:underline text-sm">Venda #<?php echo (int) $o['sale_id']; ?></a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (empty($orders)): ?>
    <div class="px-4 py-8 text-center text-gray-500">Nenhum pedido encontrado.</div>
    <?php endif; ?>
</div>

<p class="mt-4 text-sm text-gray-600">
    Link para o cliente: <code class="bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars(BASE_URL . '?route=order/form', ENT_QUOTES, 'UTF-8'); ?></code>
</p>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
