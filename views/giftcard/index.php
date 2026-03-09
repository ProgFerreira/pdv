<?php require 'views/layouts/header.php'; ?>

<div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">🎟️ Vales Presente</h2>
        <p class="text-sm text-gray-500">Gestão e controle de cartões pré-pagos e créditos.</p>
    </div>
    <div class="flex gap-2 flex-wrap">
        <div class="bg-white p-1 rounded-lg border border-gray-200 flex items-center shadow-sm">
            <form class="flex items-center">
                <input type="hidden" name="route" value="giftcard/index">
                <input type="text" name="query" placeholder="Buscar código ou cliente..."
                    value="<?php echo $_GET['query'] ?? ''; ?>"
                    class="border-0 focus:ring-0 text-sm p-2 w-64 rounded-l-lg">
                <button type="submit"
                    class="bg-gray-50 hover:bg-gray-100 text-gray-400 px-4 py-2 rounded-r-lg transition-colors border-l">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
        <div class="bg-white p-1 rounded-lg border border-gray-200 flex items-center shadow-sm">
            <form class="flex items-center" method="GET">
                <input type="hidden" name="route" value="giftcard/index">
                <input type="text" name="giftcard_code" placeholder="Código do Vale (ver gastos)..."
                    value="<?php echo htmlspecialchars($_GET['giftcard_code'] ?? ''); ?>"
                    class="border-0 focus:ring-0 text-sm p-2 w-64 rounded-l-lg">
                <button type="submit"
                    class="bg-blue-50 hover:bg-blue-100 text-blue-500 px-4 py-2 rounded-r-lg transition-colors border-l">
                    <i class="fas fa-receipt"></i>
                </button>
            </form>
        </div>
        <a href="?route=giftcard/create"
            class="bg-primary hover:bg-primary-hover text-white font-bold py-2 px-6 rounded-lg shadow-md flex items-center gap-2 transition-all transform hover:scale-105 border-0">
            <i class="fas fa-plus"></i> Novo Vale
        </a>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div
        class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 rounded shadow-sm mb-8 flex items-center gap-3">
        <i class="fas fa-check-circle text-emerald-500 text-xl"></i>
        <span>
            <?php
            if ($_GET['success'] == 'created')
                echo "Vale gerado com sucesso! O código já pode ser usado.";
            if ($_GET['success'] == 'updated')
                echo "Cadastro do Vale atualizado com sucesso.";
            if ($_GET['success'] == 'deleted')
                echo "Vale Presente removido permanentemente.";
            if ($_GET['success'] == 'refunded')
                echo "Estorno realizado! O saldo foi zerado e o dinheiro registrado como saída no caixa.";
            ?>
        </span>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded shadow-sm mb-8 flex items-center gap-3">
        <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
        <span>
            <?php
            if ($_GET['error'] == 'register_closed')
                echo "Erro: Abra o caixa antes de fazer um estorno em dinheiro.";
            if ($_GET['error'] == 'refund_failed')
                echo "Erro ao processar o estorno. Verifique se o vale ainda tem saldo.";
            else
                echo "Ocorreu um erro ao processar sua solicitação.";
            ?>
        </span>
    </div>
<?php endif; ?>

<div class="card-standard overflow-hidden">
    <div class="card-standard-header"><i class="fas fa-gift"></i> Listagem de Vales Presente</div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Código</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Cliente</th>
                    <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">V.
                        Inicial</th>
                    <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Saldo Atual</th>
                    <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Status</th>
                    <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($cards)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Nenhum vale presente encontrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($cards as $c):
                        $statusClass = 'bg-gray-100 text-gray-800';
                        if ($c['status'] == 'active')
                            $statusClass = 'bg-green-100 text-green-800';
                        if ($c['status'] == 'used')
                            $statusClass = 'bg-blue-100 text-blue-800';
                        if ($c['status'] == 'expired')
                            $statusClass = 'bg-red-100 text-red-800';
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono font-bold text-primary">
                                <?php echo htmlspecialchars($c['code']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($c['customer_name'] ?? 'Avulso'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">R$
                                <?php echo number_format($c['initial_value'], 2, ',', '.'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                <div class="font-bold text-gray-900">R$
                                    <?php echo number_format($c['balance'], 2, ',', '.'); ?>
                                </div>
                                <?php if (($c['total_spent'] ?? 0) > 0): ?>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Gasto: R$ <?php echo number_format($c['total_spent'], 2, ',', '.'); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                    <?php echo strtoupper($c['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-xs text-gray-500">
                                <?php echo date('d/m/Y H:i', strtotime($c['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end items-center gap-2">
                                    <?php
                                    $shareUrl = BASE_URL . "?route=giftcard/view&id=" . $c['id'];
                                    $msg = urlencode("🎁 Você recebeu um Vale Presente da Loja Religiosa!\n\n💰 Valor: R$ " . number_format($c['balance'], 2, ',', '.') . "\n🎫 Código: " . $c['code'] . "\n\nVeja seu cartão virtual aqui:\n" . $shareUrl);
                                    ?>
                                    <a href="https://wa.me/?text=<?php echo $msg; ?>" target="_blank"
                                        class="text-green-600 hover:text-green-900 bg-green-50 p-2 rounded-lg hover:bg-green-100 transition-colors"
                                        title="WhatsApp">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                    <a href="?route=giftcard/view&id=<?php echo $c['id']; ?>" target="_blank"
                                        class="text-blue-500 hover:text-blue-700 bg-blue-50 p-2 rounded-lg transition-colors"
                                        title="Ver Cartão">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="?route=giftcard/edit&id=<?php echo $c['id']; ?>" 
                                        class="text-amber-600 hover:text-amber-800 bg-amber-50 p-2 rounded-lg transition-colors"
                                        title="Editar">
                                        <i class="fas fa-cog"></i>
                                    </a>
                                    
                                    <?php if($c['status'] == 'active' && $c['balance'] > 0): ?>
                                    <a href="?route=giftcard/refund&id=<?php echo $c['id']; ?>" 
                                        onclick="return confirm('ATENÇÃO: Este vale será CANCELADO e o valor de faturamento será DEVOLVIDO AO CLIENTE. O sistema registrará uma saída em dinheiro no seu caixa. Confirmar estorno?')"
                                        class="text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-lg transition-colors"
                                        title="Estornar Valor">
                                        <i class="fas fa-history"></i>
                                    </a>
                                    <?php endif; ?>

                                    <a href="?route=giftcard/delete&id=<?php echo $c['id']; ?>" 
                                        onclick="return confirm('Deseja excluir este registro permanentemente? Isso não gera devolução de dinheiro.')"
                                        class="text-gray-400 hover:text-gray-600 p-2 rounded-lg transition-colors"
                                        title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (!empty($giftCardCode) && isset($selectedCard)): ?>
    <?php if ($selectedCard): ?>
        <div class="card-standard overflow-hidden mt-8">
            <div class="card-standard-header">
                <i class="fas fa-receipt"></i>
                <span>Gastos do Vale: <span class="font-mono"><?php echo htmlspecialchars($selectedCard['code']); ?></span> — Cliente: <?php echo htmlspecialchars($selectedCard['customer_name'] ?? 'Avulso'); ?> | Saldo: R$ <?php echo number_format($selectedCard['balance'], 2, ',', '.'); ?></span>
            </div>
            <?php if (empty($expenses)): ?>
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-3 opacity-30"></i>
                    <p>Nenhum gasto registrado para este vale presente.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data/Hora</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Venda</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendedor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pagamento</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Gasto</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total da Venda</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($expenses as $exp): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('d/m/Y H:i', strtotime($exp['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php if ($exp['sale_id']): ?>
                                            #<?php echo $exp['sale_id']; ?>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo htmlspecialchars($exp['user_name'] ?? '-'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <?php if ($exp['payment_method']): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                <?php echo htmlspecialchars($exp['payment_method']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-red-600">
                                        R$ <?php echo number_format($exp['amount'], 2, ',', '.'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">
                                        <?php if ($exp['sale_total']): ?>
                                            R$ <?php echo number_format($exp['sale_total'], 2, ',', '.'); ?>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <?php if ($exp['sale_id']): ?>
                                            <a href="?route=sale/view&id=<?php echo $exp['sale_id']; ?>" 
                                               class="text-blue-600 hover:text-blue-900 bg-blue-50 p-2 rounded hover:bg-blue-100 transition-colors inline-block"
                                               title="Ver Venda">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-right text-sm font-bold text-gray-700">
                                    Total Gasto:
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-bold text-red-600">
                                    R$ <?php 
                                        $totalSpent = array_sum(array_column($expenses, 'amount'));
                                        echo number_format($totalSpent, 2, ',', '.'); 
                                    ?>
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-500 text-yellow-800 p-4 rounded shadow-sm mt-8 flex items-center gap-3">
            <i class="fas fa-exclamation-triangle text-yellow-500 text-xl"></i>
            <span>Vale Presente com código "<?php echo htmlspecialchars($giftCardCode); ?>" não encontrado.</span>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require 'views/layouts/footer.php'; ?>