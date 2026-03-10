<?php require 'views/layouts/header.php'; ?>
<?php
$revenue = (float)($totals['revenue'] ?? 0);
$count = (int)($totals['count'] ?? 0);
$ticketMedio = $count > 0 ? $revenue / $count : 0;
$marginPct = $revenue > 0 ? ((float)($totals['profit'] ?? 0) / $revenue) * 100 : 0;
?>
<div class="sale-page-wrap flex flex-col gap-6" style="width:100%;max-width:100%;">
<div class="flex justify-between items-center flex-wrap gap-2">
    <h2 class="text-2xl font-bold text-gray-800">🛒 Vendas</h2>
    <div class="flex items-center gap-2">
        <?php
        $exportQuery = http_build_query(array_merge(['route' => 'sale/exportExcel'], [
            'start_date' => $filters['start_date'],
            'end_date' => $filters['end_date'],
            'sector_id' => $filters['sector_id'],
            'cash_register_id' => $filters['cash_register_id'],
            'payment_method' => $filters['payment_method'],
            'customer_query' => $filters['customer_query'],
            'delivered' => $filters['delivered'] ?? ''
        ]));
        ?>
        <a href="<?php echo BASE_URL; ?>?<?php echo $exportQuery; ?>"
            class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded shadow flex items-center gap-2 transition-colors">
            <i class="fas fa-file-excel"></i> Exportar para Excel
        </a>
        <?php if (hasPermission('pos')): ?>
        <a href="<?php echo BASE_URL; ?>?route=pos/index"
            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded shadow flex items-center gap-2 transition-colors">
            <i class="fas fa-plus"></i> Nova Venda
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Cards de Resumo: 6 por linha, logo após o título -->
<div class="sale-cards-grid cards-grid-default">
    <div class="card-standard-metric bg-primary text-white p-4 border-0 border-b-4 border-black/10">
        <h6 class="card-metric-label opacity-90">Faturamento</h6>
        <div class="flex justify-between items-center gap-2">
            <span class="text-lg xl:text-xl font-black truncate">R$ <?php echo number_format($revenue, 2, ',', '.'); ?></span>
            <i class="fas fa-hand-holding-usd text-base opacity-30 flex-shrink-0"></i>
        </div>
    </div>
    <div class="card-standard-metric p-4 border-l-danger">
        <h6 class="card-metric-label">Total de Custos</h6>
        <div class="flex justify-between items-center gap-2">
            <span class="text-lg xl:text-xl font-black text-red-600 truncate">R$ <?php echo number_format($totals['total_costs'] ?? 0, 2, ',', '.'); ?></span>
            <i class="fas fa-dollar-sign text-base text-red-100 flex-shrink-0"></i>
        </div>
    </div>
    <div class="card-standard-metric p-4 border-l-primary">
        <h6 class="card-metric-label">Qtd. Vendas</h6>
        <div class="flex justify-between items-center gap-2">
            <span class="text-lg xl:text-xl font-black text-gray-800"><?php echo $count; ?></span>
            <i class="fas fa-shopping-cart text-base text-blue-100 flex-shrink-0"></i>
        </div>
    </div>
    <div class="card-standard-metric p-4 border-l-success">
        <h6 class="card-metric-label">Lucro Estimado</h6>
        <div class="flex justify-between items-center gap-2">
            <span class="text-lg xl:text-xl font-black text-green-600 truncate">R$ <?php echo number_format($totals['profit'] ?? 0, 2, ',', '.'); ?></span>
            <i class="fas fa-trophy text-base text-green-100 flex-shrink-0"></i>
        </div>
    </div>
    <div class="card-standard-metric p-4 border-l-info">
        <h6 class="card-metric-label">Ticket Médio</h6>
        <div class="flex justify-between items-center gap-2">
            <span class="text-lg xl:text-xl font-black text-gray-800 truncate">R$ <?php echo number_format($ticketMedio, 2, ',', '.'); ?></span>
            <i class="fas fa-receipt text-base text-blue-100 flex-shrink-0"></i>
        </div>
    </div>
    <div class="card-standard-metric p-4 border-l-warning">
        <h6 class="card-metric-label">Margem %</h6>
        <div class="flex justify-between items-center gap-2">
            <span class="text-lg xl:text-xl font-black text-amber-600"><?php echo number_format($marginPct, 1, ',', '.'); ?>%</span>
            <i class="fas fa-percent text-base text-amber-100 flex-shrink-0"></i>
        </div>
    </div>
</div>

<?php if (isset($_GET['success']) && $_GET['success'] === 'cancelled'): ?>
    <div class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 flex items-center gap-3">
        <i class="fas fa-check-circle"></i>
        <span>Venda cancelada com sucesso. Estoque foi estornado, valores de caixa/fiado ajustados e a quantidade de vendas atualizada. A venda aparece na lista como cancelada. Ação registrada em log.</span>
    </div>
<?php endif; ?>
<?php if (isset($_GET['success']) && $_GET['success'] === 'delivered'): ?>
    <div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3">
        <i class="fas fa-truck"></i>
        <span>Pedido marcado como entregue.</span>
    </div>
<?php endif; ?>
<?php if (isset($_GET['success']) && $_GET['success'] === 'delivery_removed'): ?>
    <div class="p-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 flex items-center gap-3">
        <i class="fas fa-undo"></i>
        <span>Entrega desmarcada.</span>
    </div>
<?php endif; ?>

<div class="card-standard">
    <div class="card-standard-header"><i class="fas fa-filter"></i> Filtros</div>
    <div class="card-standard-body">
    <form method="GET" action="<?php echo BASE_URL; ?>" class="flex flex-col gap-6">
        <input type="hidden" name="route" value="sale/index">

        <!-- Primeira Linha: Filtros Principais -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">📅 De</label>
                <input type="date" name="start_date" value="<?php echo $filters['start_date']; ?>"
                    class="w-full rounded-lg border-gray-200 shadow-sm focus:border-primary focus:ring-primary text-sm p-2">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">📅 Até</label>
                <input type="date" name="end_date" value="<?php echo $filters['end_date']; ?>"
                    class="w-full rounded-lg border-gray-200 shadow-sm focus:border-primary focus:ring-primary text-sm p-2">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">🚚 Entrega</label>
                <select name="delivered"
                    class="w-full rounded-lg border-gray-200 shadow-sm focus:border-primary text-sm p-2">
                    <option value="" <?php echo (($filters['delivered'] ?? '') === '') ? 'selected' : ''; ?>>Todos</option>
                    <option value="1" <?php echo (($filters['delivered'] ?? '') === '1') ? 'selected' : ''; ?>>Entregue</option>
                    <option value="0" <?php echo (($filters['delivered'] ?? '') === '0') ? 'selected' : ''; ?>>Não entregue</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">💳 Pagamento</label>
                <select name="payment_method"
                    class="w-full rounded-lg border-gray-200 shadow-sm focus:border-primary text-sm p-2">
                    <option value="">Todos</option>
                    <option value="Dinheiro" <?php echo ($filters['payment_method'] == 'Dinheiro') ? 'selected' : ''; ?>>
                        💵 Dinheiro</option>
                    <option value="Cartão de Crédito" <?php echo ($filters['payment_method'] == 'Cartão de Crédito') ? 'selected' : ''; ?>>💳 Crédito</option>
                    <option value="Cartão de Débito" <?php echo ($filters['payment_method'] == 'Cartão de Débito') ? 'selected' : ''; ?>>💳 Débito</option>
                    <option value="PIX" <?php echo ($filters['payment_method'] == 'PIX') ? 'selected' : ''; ?>>📱 PIX
                    </option>
                    <option value="A Prazo" <?php echo ($filters['payment_method'] == 'A Prazo') ? 'selected' : ''; ?>>⏳ A
                        Prazo</option>
                    <option value="Vale Presente" <?php echo ($filters['payment_method'] == 'Vale Presente') ? 'selected' : ''; ?>>🎁 Vale Presente</option>
                </select>
            </div>

            <?php if (isAdmin()): ?>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">📍 Setor</label>
                    <select name="sector_id"
                        class="w-full rounded-lg border-gray-200 shadow-sm focus:border-primary text-sm p-2">
                        <option value="all" <?php echo ($filters['sector_id'] === 'all' || !$filters['sector_id']) ? 'selected' : ''; ?>>Todos</option>
                        <?php foreach ($sectors as $s): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo ($filters['sector_id'] == $s['id']) ? 'selected' : ''; ?>>
                                <?php echo $s['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="flex items-end gap-2">
                <button type="submit"
                    class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white font-bold py-2 px-4 rounded-lg shadow transition-colors flex-1 text-sm">
                    <i class="fas fa-filter"></i><span>Filtrar</span>
                </button>
                <a href="?route=sale/index"
                    class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-500 font-bold py-2 px-4 rounded-lg border border-gray-200 transition-colors text-sm">
                    <i class="fas fa-trash-alt"></i><span>Limpar filtros</span>
                </a>
            </div>
        </div>

        <!-- Segunda Linha: Buscas Específicas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 border-t border-gray-50 pt-4">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i
                        class="fas fa-user text-xs"></i></span>
                <input type="text" name="customer_query" placeholder="Buscar por Cliente (Nome ou ID)..."
                    value="<?php echo $filters['customer_query']; ?>"
                    class="w-full rounded-lg border-gray-200 pl-10 text-sm p-2">
            </div>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i
                        class="fas fa-cash-register text-xs"></i></span>
                <input type="number" name="cash_register_id" placeholder="Filtrar por Número do Caixa (#)..."
                    value="<?php echo $filters['cash_register_id']; ?>"
                    class="w-full rounded-lg border-gray-200 pl-10 text-sm p-2">
            </div>
        </div>
    </form>
    </div>
</div>

<!-- Quantidade vendida por produto (respeita os filtros acima) -->
<div class="card-standard overflow-hidden">
    <div class="card-standard-header"><i class="fas fa-boxes"></i> Quantidade vendida por produto</div>
    <div class="overflow-x-auto">
        <?php if (empty($productsSold)): ?>
            <p class="p-4 text-gray-500 text-sm">Nenhuma venda no período com os filtros aplicados.</p>
        <?php else: ?>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qtd. vendida</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Faturamento</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($productsSold as $row): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3 text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($row['product_name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                            </td>
                            <td class="px-6 py-3 text-sm text-right font-semibold text-gray-800">
                                <?php echo (int) ($row['quantity'] ?? 0); ?>
                            </td>
                            <td class="px-6 py-3 text-sm text-right text-gray-600">
                                R$ <?php echo number_format((float) ($row['subtotal'] ?? 0), 2, ',', '.'); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<div class="card-standard overflow-hidden">
    <div class="card-standard-header"><i class="fas fa-list"></i> Listagem de Vendas</div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data/Hora
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Caixa
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefone
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendedor
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Setor
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pagamento
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($sales as $s):
                    $saleCancelled = isset($s['status']) && $s['status'] === 'cancelled';
                    $whatsappSent = !empty($s['whatsapp_sent_at']);
                    $delivered = !empty($s['delivered_at']);
                    $rowBg = $saleCancelled ? 'bg-red-50/50' : ($delivered ? 'bg-emerald-100 border-l-4 border-emerald-500' : ($whatsappSent ? 'bg-green-50/70' : ''));
                ?>
                    <tr class="hover:opacity-95 transition-colors <?php echo $rowBg; ?>">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#
                            <?php echo $s['id']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($saleCancelled): ?>
                                <span class="px-2 py-1 text-xs font-bold rounded-full bg-red-100 text-red-700 border border-red-200">Cancelada</span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs font-bold rounded-full bg-green-100 text-green-700 border border-green-200">Concluída</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div>
                                <?php echo date('d/m/Y', strtotime($s['created_at'])); ?>
                            </div>
                            <div class="text-xs text-gray-400">
                                <?php echo date('H:i', strtotime($s['created_at'])); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                            #<?php echo $s['cash_register_id'] ?? '-'; ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 min-w-[120px]" title="Cliente">
                            <?php echo htmlspecialchars(trim((string)($s['customer_name'] ?? '')) ?: '-', ENT_QUOTES, 'UTF-8'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                            <?php echo htmlspecialchars(trim((string)($s['customer_phone'] ?? '')) ?: '-', ENT_QUOTES, 'UTF-8'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $s['user_name'] ?? '-'; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span
                                class="px-2 py-1 text-[10px] font-bold rounded bg-gray-100 text-gray-600 uppercase border border-gray-200">
                                <?php echo htmlspecialchars($s['sector_name'] ?? 'Loja'); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                <?php echo $s['payment_method']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold <?php echo $saleCancelled ? 'text-gray-500 line-through' : 'text-gray-900'; ?>">
                            R$
                            <?php echo number_format($s['total'], 2, ',', '.'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <a href="?route=pos/receipt&id=<?php echo (int) $s['id']; ?>"
                                    target="_blank"
                                    class="text-primary hover:opacity-90 bg-primary/10 p-2 rounded hover:bg-primary/20 transition-colors"
                                    title="Imprimir cupom">
                                    <i class="fas fa-print"></i>
                                </a>
                                <a href="?route=sale/view&id=<?php echo $s['id']; ?>"
                                    class="text-blue-600 hover:text-blue-900 bg-blue-50 p-2 rounded hover:bg-blue-100 transition-colors"
                                    title="Visualizar">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if (!$saleCancelled): ?>
                                    <a href="?route=sale/view&id=<?php echo (int) $s['id']; ?>&whatsapp=1"
                                        class="text-green-600 hover:text-green-900 bg-green-50 p-2 rounded hover:bg-green-100 transition-colors"
                                        title="Enviar por WhatsApp">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                    <a href="?route=sale/open&id=<?php echo $s['id']; ?>"
                                        class="text-green-600 hover:text-green-900 bg-green-50 p-2 rounded hover:bg-green-100 transition-colors"
                                        title="Abrir no PDV">
                                        <i class="fas fa-folder-open"></i>
                                    </a>
                                    <?php if ($delivered): ?>
                                        <a href="?route=sale/unmarkDelivered&id=<?php echo (int) $s['id']; ?>"
                                            class="text-emerald-700 hover:text-emerald-900 bg-emerald-100 p-2 rounded hover:bg-emerald-200 transition-colors"
                                            title="Desmarcar entrega (clique para retirar)">
                                            <i class="fas fa-truck"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="?route=sale/markDelivered&id=<?php echo (int) $s['id']; ?>"
                                            class="text-slate-600 hover:text-emerald-700 bg-slate-100 p-2 rounded hover:bg-emerald-100 transition-colors"
                                            title="Marcar como entregue">
                                            <i class="fas fa-truck"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (hasPermission('sale_cancel')): ?>
                                        <a href="<?php echo htmlspecialchars(BASE_URL ?? '', ENT_QUOTES, 'UTF-8'); ?>?route=sale/cancel&id=<?php echo (int) $s['id']; ?>"
                                            onclick="return confirm('Cancelar esta venda? O estoque será estornado, os valores de caixa/fiado ajustados e a venda ficará marcada como cancelada. Esta ação fica registrada em log.');"
                                            class="text-red-700 hover:text-red-900 bg-red-50 p-2 rounded hover:bg-red-100 transition-colors border border-red-200"
                                            title="Cancelar venda">
                                            <i class="fas fa-times-circle"></i>
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</div>

<?php require 'views/layouts/footer.php'; ?>