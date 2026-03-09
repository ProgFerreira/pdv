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
            'customer_query' => $filters['customer_query']
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
    <div class="p-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 flex items-center gap-3">
        <i class="fas fa-check-circle"></i>
        <span>Venda cancelada. Estoque e movimentações foram estornados. Ação registrada em log.</span>
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
                    class="bg-primary hover:bg-primary-hover text-white font-bold py-2 px-4 rounded-lg shadow transition-colors flex-1 text-sm">
                    <i class="fas fa-filter"></i>
                </button>
                <a href="?route=sale/index"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-500 font-bold py-2 px-4 rounded-lg border border-gray-200 transition-colors text-sm">
                    <i class="fas fa-trash-alt"></i>
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
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data/Hora
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Caixa
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente
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
                <?php foreach ($sales as $s): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#
                            <?php echo $s['id']; ?>
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
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">
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
                                <a href="?route=sale/view&id=<?php echo $s['id']; ?>&whatsapp=1"
                                    class="text-green-600 hover:text-green-900 bg-green-50 p-2 rounded hover:bg-green-100 transition-colors"
                                    title="Enviar por WhatsApp">
                                    <svg class="w-4 h-4 inline" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                </a>
                                <a href="?route=sale/open&id=<?php echo $s['id']; ?>"
                                    class="text-green-600 hover:text-green-900 bg-green-50 p-2 rounded hover:bg-green-100 transition-colors"
                                    title="Abrir no PDV">
                                    <i class="fas fa-folder-open"></i>
                                </a>
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