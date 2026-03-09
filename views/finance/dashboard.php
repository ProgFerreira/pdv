<?php require 'views/layouts/header.php'; ?>

<div class="flex flex-col gap-8">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Dashboard Financeiro</h1>
            <p class="text-gray-500">Visão geral da saúde financeira da sua empresa.</p>
        </div>
        <div class="flex gap-2">
            <button class="btn btn-primary btn-sm rounded-lg shadow-sm" onclick="window.location.reload()">
                <i class="fas fa-sync-alt"></i> Atualizar Dados
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="cards-grid-default gap-6">
        <!-- Saldo Total -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-wallet text-6xl text-primary"></i>
            </div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Saldo Total</p>
            <h2 class="text-2xl font-black text-gray-800">
                R$ <?php 
                    $totalBalance = array_reduce($data['accounts'], fn($carry, $item) => $carry + $item['saldo_inicial'], 0);
                    echo number_format($totalBalance, 2, ',', '.'); 
                ?>
            </h2>
            <div class="mt-4 flex flex-col gap-1">
                <?php foreach($data['accounts'] as $acc): ?>
                    <div class="flex justify-between text-[10px] font-medium text-gray-500">
                        <span><?php echo $acc['nome']; ?></span>
                        <span class="font-bold text-gray-700">R$ <?php echo number_format($acc['saldo_inicial'], 2, ',', '.'); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- A Receber Hoje -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-hand-holding-usd text-6xl text-green-500"></i>
            </div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">A Receber (Até Hoje)</p>
            <h2 class="text-2xl font-black text-green-600">
                R$ <?php 
                    $totalR = array_reduce($data['receivables_today'], fn($carry, $item) => $carry + $item['saldo_aberto'], 0);
                    echo number_format($totalR, 2, ',', '.'); 
                ?>
            </h2>
            <p class="text-[10px] text-gray-400 mt-2"><?php echo count($data['receivables_today']); ?> lançamentos pendentes</p>
            <a href="?route=receivable/index" class="btn btn-ghost btn-xs text-primary p-0 mt-3 font-bold hover:bg-transparent">Gerenciar <i class="fas fa-chevron-right text-[8px]"></i></a>
        </div>

        <!-- A Pagar Hoje -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-file-invoice-dollar text-6xl text-red-500"></i>
            </div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">A Pagar (Até Hoje)</p>
            <h2 class="text-2xl font-black text-red-600">
                R$ <?php 
                    $totalP = array_reduce($data['payables_today'], fn($carry, $item) => $carry + $item['saldo_aberto'], 0);
                    echo number_format($totalP, 2, ',', '.'); 
                ?>
            </h2>
            <p class="text-[10px] text-gray-400 mt-2"><?php echo count($data['payables_today']); ?> contas para pagar</p>
            <a href="?route=payable/index" class="btn btn-ghost btn-xs text-primary p-0 mt-3 font-bold hover:bg-transparent">Gerenciar <i class="fas fa-chevron-right text-[8px]"></i></a>
        </div>

        <!-- Projeção 7 Dias -->
        <div class="bg-gray-800 p-6 rounded-2xl shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity text-white">
                <i class="fas fa-chart-line text-6xl"></i>
            </div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Fluxo (Prox. 7 Dias)</p>
            <?php 
                $projR = array_reduce($data['receivables_7d'], fn($carry, $item) => $carry + $item['saldo_aberto'], 0);
                $projP = array_reduce($data['payables_7d'], fn($carry, $item) => $carry + $item['saldo_aberto'], 0);
                $net = $projR - $projP;
            ?>
            <h2 class="text-2xl font-black <?php echo $net >= 0 ? 'text-blue-400' : 'text-red-400'; ?>">
                R$ <?php echo number_format($net, 2, ',', '.'); ?>
            </h2>
            <div class="mt-4 flex flex-col gap-1">
                <div class="flex justify-between text-[10px] font-medium text-gray-400">
                    <span>Entradas:</span>
                    <span class="text-green-400">+ R$ <?php echo number_format($projR, 2, ',', '.'); ?></span>
                </div>
                <div class="flex justify-between text-[10px] font-medium text-gray-400">
                    <span>Saídas:</span>
                    <span class="text-red-400">- R$ <?php echo number_format($projP, 2, ',', '.'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Próximos Recebimentos -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-bold text-gray-700 flex items-center gap-2">
                    <i class="fas fa-calendar-plus text-primary"></i> Próximos Recebimentos (7 dias)
                </h3>
                <span class="badge badge-primary badge-sm"><?php echo count($data['receivables_7d']); ?></span>
            </div>
            <div class="overflow-x-auto">
                <table class="table table-compact w-full">
                    <thead>
                        <tr class="text-[10px] text-gray-400 uppercase tracking-widest">
                            <th class="bg-transparent">Vencimento</th>
                            <th class="bg-transparent">Descrição / Cliente</th>
                            <th class="bg-transparent text-right">Valor</th>
                            <th class="bg-transparent"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if(empty($data['receivables_7d'])): ?>
                            <tr><td colspan="4" class="text-center py-8 text-gray-400 italic text-sm">Nenhum recebimento previsto para os próximos 7 dias.</td></tr>
                        <?php else: ?>
                            <?php foreach($data['receivables_7d'] as $r): ?>
                                <tr class="hover:bg-gray-50 transition-colors group">
                                    <td class="text-xs font-bold text-gray-600">
                                        <?php echo date('d/m', strtotime($r['data_vencimento'])); ?>
                                    </td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-gray-800"><?php echo $r['descricao']; ?></span>
                                            <span class="text-[10px] text-gray-400"><?php echo $r['customer_name'] ?? 'Consumidor'; ?></span>
                                        </div>
                                    </td>
                                    <td class="text-right text-xs font-black text-green-600">
                                        R$ <?php echo number_format($r['saldo_aberto'], 2, ',', '.'); ?>
                                    </td>
                                    <td class="text-right">
                                        <a href="?route=receivable/index" class="btn btn-ghost btn-xs text-gray-300 group-hover:text-primary transition-colors">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Próximos Pagamentos -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-bold text-gray-700 flex items-center gap-2">
                    <i class="fas fa-calendar-minus text-red-500"></i> Próximos Pagamentos (7 dias)
                </h3>
                <span class="badge badge-error badge-sm text-white"><?php echo count($data['payables_7d']); ?></span>
            </div>
            <div class="overflow-x-auto">
                <table class="table table-compact w-full">
                    <thead>
                        <tr class="text-[10px] text-gray-400 uppercase tracking-widest">
                            <th class="bg-transparent">Vencimento</th>
                            <th class="bg-transparent">Descrição / Fornecedor</th>
                            <th class="bg-transparent text-right">Valor</th>
                            <th class="bg-transparent"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if(empty($data['payables_7d'])): ?>
                            <tr><td colspan="4" class="text-center py-8 text-gray-400 italic text-sm">Nenhum pagamento previsto para os próximos 7 dias.</td></tr>
                        <?php else: ?>
                            <?php foreach($data['payables_7d'] as $p): ?>
                                <tr class="hover:bg-gray-50 transition-colors group">
                                    <td class="text-xs font-bold text-gray-600">
                                        <?php echo date('d/m', strtotime($p['data_vencimento'])); ?>
                                    </td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-gray-800"><?php echo $p['descricao']; ?></span>
                                            <span class="text-[10px] text-gray-400"><?php echo $p['supplier_name'] ?? 'Diversos'; ?></span>
                                        </div>
                                    </td>
                                    <td class="text-right text-xs font-black text-red-600">
                                        R$ <?php echo number_format($p['saldo_aberto'], 2, ',', '.'); ?>
                                    </td>
                                    <td class="text-right">
                                        <a href="?route=payable/index" class="btn btn-ghost btn-xs text-gray-300 group-hover:text-primary transition-colors">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="flex flex-wrap gap-4 justify-center">
        <a href="?route=receivable/index" class="btn btn-outline btn-primary rounded-xl px-8">
            <i class="fas fa-arrow-up mr-2"></i> Novo Recebível
        </a>
        <a href="?route=payable/index" class="btn btn-outline btn-error rounded-xl px-8">
            <i class="fas fa-arrow-down mr-2"></i> Nova Despesa
        </a>
        <a href="?route=account/index" class="btn btn-outline btn-neutral rounded-xl px-8">
            <i class="fas fa-university mr-2"></i> Contas Bancárias
        </a>
        <a href="?route=planoContas/index" class="btn btn-outline btn-neutral rounded-xl px-8">
            <i class="fas fa-sitemap mr-2"></i> Plano de Contas
        </a>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>
