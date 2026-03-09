<?php require 'views/layouts/header.php'; ?>

<div class="flex flex-col gap-6">
<?php
$cashModelForOpen = new \App\Models\CashRegister();
$userHasOpenRegister = $cashModelForOpen->getOpenRegister($_SESSION['user_id'] ?? 0);
?>
<div class="flex justify-between items-center flex-wrap gap-4">
    <h2 class="text-2xl font-bold text-gray-800 uppercase tracking-tight">📁 Fluxo de Caixa (Histórico)</h2>
    <?php if (!$userHasOpenRegister): ?>
        <a href="<?php echo BASE_URL; ?>?route=pos/index" class="btn bg-green-600 hover:bg-green-700 border-none text-white font-bold py-2.5 px-5 rounded-xl shadow-md no-underline flex items-center gap-2">
            <i class="fas fa-cash-register"></i> Abrir Caixa
        </a>
    <?php else: ?>
        <span class="text-sm text-green-600 font-bold flex items-center gap-2"><i class="fas fa-check-circle"></i> Caixa aberto</span>
    <?php endif; ?>
</div>
<p class="text-sm text-gray-500 mt-1"><?php echo $userHasOpenRegister ? 'Você já tem um caixa aberto. Use o PDV para vender e feche o caixa ao final.' : 'O caixa está fechado. Clique em &quot;Abrir Caixa&quot; para ir ao PDV e informar o valor inicial.'; ?></p>

<!-- Cards Totalizadores -->
<div class="cards-grid-default gap-4">
    <div class="card-standard-metric p-6 border-l-primary">
        <div class="flex items-center justify-between">
            <div>
                <p class="card-metric-label">Total de Caixas</p>
                <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo number_format($totals['total_registers'] ?? 0, 0, ',', '.'); ?></p>
            </div>
            <div class="bg-blue-100 p-3 rounded-full">
                <i class="fas fa-cash-register text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card-standard-metric p-6 border-l-success">
        <div class="flex items-center justify-between">
            <div>
                <p class="card-metric-label">Caixas Abertos</p>
                <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo number_format($totals['total_open'] ?? 0, 0, ',', '.'); ?></p>
            </div>
            <div class="bg-green-100 p-3 rounded-full">
                <i class="fas fa-unlock text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card-standard-metric p-6 border-l-info">
        <div class="flex items-center justify-between">
            <div>
                <p class="card-metric-label">Caixas Fechados</p>
                <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo number_format($totals['total_closed'] ?? 0, 0, ',', '.'); ?></p>
            </div>
            <div class="bg-gray-100 p-3 rounded-full">
                <i class="fas fa-lock text-gray-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card-standard-metric p-6 border-l-primary">
        <div class="flex items-center justify-between">
            <div>
                <p class="card-metric-label">Total de Vendas</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">R$ <?php echo number_format($totals['total_sales'] ?? 0, 2, ',', '.'); ?></p>
            </div>
            <div class="bg-purple-100 p-3 rounded-full">
                <i class="fas fa-shopping-cart text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card-standard-metric p-6 border-l-warning">
        <div class="flex items-center justify-between">
            <div>
                <p class="card-metric-label">Saldo Total</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">R$ <?php echo number_format(($totals['total_closing'] ?? 0) - ($totals['total_opening'] ?? 0), 2, ',', '.'); ?></p>
            </div>
            <div class="bg-amber-100 p-3 rounded-full">
                <i class="fas fa-wallet text-amber-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filtros de Pesquisa -->
<div class="card-standard">
    <div class="card-standard-header"><i class="fas fa-filter"></i> Filtros</div>
    <div class="card-standard-body">
    <form method="GET" action="<?php echo BASE_URL; ?>" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <input type="hidden" name="route" value="cash/history">
        
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Data Inicial</label>
            <input type="date" name="start_date" value="<?php echo htmlspecialchars($_GET['start_date'] ?? ''); ?>"
                class="w-full rounded-lg border-gray-200 shadow-sm p-2 text-sm focus:border-primary focus:ring-primary">
        </div>
        
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Data Final</label>
            <input type="date" name="end_date" value="<?php echo htmlspecialchars($_GET['end_date'] ?? ''); ?>"
                class="w-full rounded-lg border-gray-200 shadow-sm p-2 text-sm focus:border-primary focus:ring-primary">
        </div>
        
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Operador</label>
            <select name="user_id" class="w-full rounded-lg border-gray-200 shadow-sm p-2 text-sm focus:border-primary focus:ring-primary">
                <option value="">Todos</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?php echo $u['id']; ?>" <?php echo (isset($_GET['user_id']) && $_GET['user_id'] == $u['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($u['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Status</label>
            <select name="status" class="w-full rounded-lg border-gray-200 shadow-sm p-2 text-sm focus:border-primary focus:ring-primary">
                <option value="">Todos</option>
                <option value="open" <?php echo (isset($_GET['status']) && $_GET['status'] == 'open') ? 'selected' : ''; ?>>Aberto</option>
                <option value="closed" <?php echo (isset($_GET['status']) && $_GET['status'] == 'closed') ? 'selected' : ''; ?>>Fechado</option>
            </select>
        </div>
        
        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 bg-primary hover:bg-primary-hover text-white font-bold py-2 px-4 rounded-lg transition-colors">
                <i class="fas fa-search mr-2"></i> Filtrar
            </button>
            <a href="?route=cash/history" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg transition-colors">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 rounded shadow-sm mb-6 flex items-center gap-3">
        <i class="fas fa-check-circle text-emerald-500 text-xl"></i>
        <span>
            <?php
            if ($_GET['success'] == 'updated')
                echo "Caixa atualizado com sucesso!";
            if ($_GET['success'] == 'deleted')
                echo "Caixa excluído com sucesso!";
            ?>
        </span>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded shadow-sm mb-6 flex items-center gap-3">
        <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
        <span>
            <?php
            if ($_GET['error'] == 'not_found')
                echo "Caixa não encontrado.";
            if ($_GET['error'] == 'cannot_edit_open')
                echo "Não é possível editar um caixa aberto. Feche-o primeiro.";
            if ($_GET['error'] == 'cannot_delete_open')
                echo "Não é possível excluir um caixa aberto. Feche-o primeiro.";
            if ($_GET['error'] == 'update_failed')
                echo "Erro ao atualizar o caixa.";
            if ($_GET['error'] == 'delete_failed')
                echo "Erro ao excluir o caixa. Verifique se há vendas associadas.";
            else
                echo "Ocorreu um erro ao processar sua solicitação.";
            ?>
        </span>
    </div>
<?php endif; ?>

<div class="card-standard overflow-hidden">
    <div class="card-standard-header"><i class="fas fa-cash-register"></i> Histórico de Caixas</div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 text-[10px] uppercase font-bold text-gray-500 tracking-widest">
                <tr>
                    <th class="px-6 py-3 text-left">ID</th>
                    <th class="px-6 py-3 text-left">Operador</th>
                    <th class="px-6 py-3 text-left">Abertura</th>
                    <th class="px-6 py-3 text-left">Fechamento</th>
                    <th class="px-6 py-3 text-right">Saldo Inicial</th>
                    <th class="px-6 py-3 text-right">Saldo Final</th>
                    <th class="px-6 py-3 text-center">Status</th>
                    <th class="px-6 py-3 text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($registers)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-3 opacity-30"></i>
                            <p>Nenhum caixa encontrado com os filtros aplicados.</p>
                        </td>
                    </tr>
                <?php else: ?>
                <?php foreach ($registers as $reg): 
                    $sales = $salesByRegister[$reg['id']] ?? [];
                    $hasSales = count($sales) > 0;
                ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <div class="flex items-center gap-2">
                                <?php if ($hasSales): ?>
                                    <button onclick="toggleSales(<?php echo $reg['id']; ?>)" 
                                            class="text-gray-400 hover:text-gray-600 transition-colors"
                                            title="Ver vendas">
                                        <i class="fas fa-chevron-down" id="icon-<?php echo $reg['id']; ?>"></i>
                                    </button>
                                <?php endif; ?>
                                #<?php echo $reg['id']; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium">
                            <?php echo $reg['user_name']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('d/m/Y H:i', strtotime($reg['opened_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $reg['closed_at'] ? date('d/m/Y H:i', strtotime($reg['closed_at'])) : '<span class="italic text-gray-400">Em aberto</span>'; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600 font-bold">
                            R$
                            <?php echo number_format($reg['opening_balance'], 2, ',', '.'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600 font-bold">
                            <?php echo $reg['closing_balance'] ? 'R$ ' . number_format($reg['closing_balance'], 2, ',', '.') : '-'; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <?php if ($reg['status'] === 'open'): ?>
                                <span
                                    class="px-2 py-1 text-[10px] font-bold rounded-full bg-green-100 text-green-700 uppercase border border-green-200">Aberto</span>
                            <?php else: ?>
                                <span
                                    class="px-2 py-1 text-[10px] font-bold rounded-full bg-gray-100 text-gray-500 uppercase border border-gray-200">Fechado</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-2">
                                <a href="?route=cash/view&id=<?php echo $reg['id']; ?>"
                                    class="text-blue-600 hover:text-blue-900 bg-blue-50 p-2 rounded transition-colors"
                                    title="Visualizar">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($reg['status'] === 'closed'): ?>
                                    <a href="?route=cash/edit&id=<?php echo $reg['id']; ?>"
                                        class="text-amber-600 hover:text-amber-800 bg-amber-50 p-2 rounded transition-colors"
                                        title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?route=cash/delete&id=<?php echo $reg['id']; ?>"
                                        onclick="return confirm('Tem certeza que deseja excluir este caixa? Esta ação não pode ser desfeita e só é permitida se não houver vendas associadas.');"
                                        class="text-red-600 hover:text-red-800 bg-red-50 p-2 rounded transition-colors"
                                        title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($reg['status'] === 'open'): ?>
                                    <a href="?route=cash/close"
                                        class="text-red-600 hover:text-red-900 bg-red-50 p-2 rounded transition-colors"
                                        title="Fechar este Caixa">
                                        <i class="fas fa-lock"></i> FECHAR
                                    </a>
                                <?php endif; ?>
                                <a href="?route=cash/report&id=<?php echo $reg['id']; ?>"
                                    class="text-primary hover:text-primary-hover bg-primary/10 p-2 rounded transition-colors"
                                    title="Ver Relatório">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php if ($hasSales): ?>
                        <tr id="sales-row-<?php echo $reg['id']; ?>" class="hidden">
                            <td colspan="8" class="px-6 py-4 bg-gray-50">
                                <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                                    <div class="p-4 border-b border-gray-200">
                                        <h4 class="font-bold text-gray-700 flex items-center gap-2">
                                            <i class="fas fa-shopping-cart"></i>
                                            Vendas do Caixa #<?php echo $reg['id']; ?>
                                            <span class="text-sm font-normal text-gray-500">(<?php echo count($sales); ?> venda<?php echo count($sales) > 1 ? 's' : ''; ?>)</span>
                                        </h4>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Data/Hora</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Vendedor</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Pagamento</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <?php foreach ($sales as $sale): ?>
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-4 py-2 text-sm font-medium text-gray-900">#<?php echo $sale['id']; ?></td>
                                                        <td class="px-4 py-2 text-sm text-gray-500"><?php echo date('d/m/Y H:i', strtotime($sale['created_at'])); ?></td>
                                                        <td class="px-4 py-2 text-sm text-gray-600"><?php echo htmlspecialchars($sale['customer_name'] ?? 'Avulso'); ?></td>
                                                        <td class="px-4 py-2 text-sm text-gray-600"><?php echo htmlspecialchars($sale['user_name'] ?? '-'); ?></td>
                                                        <td class="px-4 py-2 text-sm">
                                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                                <?php echo htmlspecialchars($sale['payment_method'] ?? '-'); ?>
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-right font-bold text-gray-900">
                                                            R$ <?php echo number_format($sale['total'], 2, ',', '.'); ?>
                                                        </td>
                                                        <td class="px-4 py-2 text-center">
                                                            <a href="?route=sale/view&id=<?php echo $sale['id']; ?>" 
                                                               class="text-blue-600 hover:text-blue-900 bg-blue-50 p-1.5 rounded transition-colors"
                                                               title="Ver Venda">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div><!-- /.flex.flex-col.gap-6 -->

<script>
function toggleSales(registerId) {
    const row = document.getElementById('sales-row-' + registerId);
    const icon = document.getElementById('icon-' + registerId);
    
    if (row.classList.contains('hidden')) {
        row.classList.remove('hidden');
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    } else {
        row.classList.add('hidden');
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}
</script>

<?php require 'views/layouts/footer.php'; ?>