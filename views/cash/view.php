<?php require 'views/layouts/header.php'; ?>

<div class="mb-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-gray-800 uppercase tracking-tight">👁️ Visualizar Caixa
            #<?php echo $register['id']; ?></h2>
        <a href="?route=cash/history"
            class="text-gray-600 hover:text-gray-800 bg-gray-100 px-4 py-2 rounded-lg transition-colors">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <!-- Informações Básicas -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden border border-gray-200">
        <div class="p-6 bg-gray-50 border-b border-gray-200">
            <h3 class="font-bold text-gray-700">Informações Básicas</h3>
        </div>
        <div class="p-6 space-y-4">
            <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                <span class="text-gray-600">ID:</span>
                <span class="font-bold text-gray-800">#<?php echo $register['id']; ?></span>
            </div>
            <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                <span class="text-gray-600">Operador:</span>
                <span class="font-bold text-gray-800"><?php echo htmlspecialchars($register['user_name']); ?></span>
            </div>
            <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                <span class="text-gray-600">Status:</span>
                <span>
                    <?php if ($register['status'] === 'open'): ?>
                        <span
                            class="px-2 py-1 text-xs font-bold rounded-full bg-green-100 text-green-700 uppercase border border-green-200">Aberto</span>
                    <?php else: ?>
                        <span
                            class="px-2 py-1 text-xs font-bold rounded-full bg-gray-100 text-gray-500 uppercase border border-gray-200">Fechado</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                <span class="text-gray-600">Abertura:</span>
                <span class="text-gray-800"><?php echo date('d/m/Y H:i', strtotime($register['opened_at'])); ?></span>
            </div>
            <?php if ($register['closed_at']): ?>
                <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                    <span class="text-gray-600">Fechamento:</span>
                    <span class="text-gray-800"><?php echo date('d/m/Y H:i', strtotime($register['closed_at'])); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Resumo Financeiro -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden border border-gray-200">
        <div class="p-6 bg-gray-50 border-b border-gray-200">
            <h3 class="font-bold text-gray-700">Resumo Financeiro</h3>
        </div>
        <div class="p-6 space-y-4">
            <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                <span class="text-gray-600">Saldo Inicial:</span>
                <span class="font-bold text-gray-800">R$
                    <?php echo number_format($register['opening_balance'], 2, ',', '.'); ?></span>
            </div>
            <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                <span class="text-green-600 font-medium">+ Vendas em Dinheiro:</span>
                <span class="font-bold text-green-600">R$
                    <?php echo number_format($summary['sales'] ?? 0, 2, ',', '.'); ?></span>
            </div>
            <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                <span class="text-blue-600 font-medium">+ Suprimentos:</span>
                <span class="font-bold text-blue-600">R$
                    <?php echo number_format($summary['supply'] ?? 0, 2, ',', '.'); ?></span>
            </div>
            <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                <span class="text-red-500 font-medium">- Sangrias:</span>
                <span class="font-bold text-red-500">R$
                    <?php echo number_format($summary['bleed'] ?? 0, 2, ',', '.'); ?></span>
            </div>
            <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                <span class="text-orange-600 font-medium">- Despesas/Pagamentos:</span>
                <span class="font-bold text-orange-600">R$
                    <?php echo number_format($summary['expense'] ?? 0, 2, ',', '.'); ?></span>
            </div>
            <div class="flex justify-between items-center pt-2 bg-gray-50 p-3 rounded">
                <span class="text-lg font-bold text-gray-800">Saldo Esperado:</span>
                <span class="text-lg font-bold text-primary">R$
                    <?php echo number_format($summary['current_balance'], 2, ',', '.'); ?></span>
            </div>
            <?php if ($register['closing_balance'] !== null): ?>
                <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                    <span class="text-gray-600">Saldo Informado:</span>
                    <span class="font-bold text-gray-800">R$
                        <?php echo number_format($register['closing_balance'], 2, ',', '.'); ?></span>
                </div>
                <?php
                $difference = $register['closing_balance'] - $summary['current_balance'];
                if ($difference != 0):
                    ?>
                    <div class="flex justify-between items-center pt-2">
                        <span
                            class="font-bold <?php echo $difference > 0 ? 'text-green-600' : 'text-red-600'; ?>">Diferença:</span>
                        <span class="font-bold <?php echo $difference > 0 ? 'text-green-600' : 'text-red-600'; ?>">R$
                            <?php echo number_format($difference, 2, ',', '.'); ?></span>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Vendas por Forma de Pagamento -->
<div class="bg-white shadow-lg rounded-lg overflow-hidden border border-gray-200 mb-6">
    <div class="p-6 bg-gray-50 border-b border-gray-200">
        <h3 class="font-bold text-gray-700">Vendas por Forma de Pagamento</h3>
    </div>
    <div class="p-6">
        <?php if (empty($paymentMethods)): ?>
            <p class="text-gray-500 text-center py-4">Nenhuma venda registrada neste turno.</p>
        <?php else: ?>
            <div class="space-y-3">
                <?php
                $totalGeralVendas = 0;
                foreach ($paymentMethods as $pm):
                    $totalGeralVendas += $pm['total'];
                    ?>
                    <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                        <span class="text-gray-600">
                            <?php echo htmlspecialchars($pm['payment_method']); ?>
                            <span class="text-xs text-gray-400">(<?php echo $pm['count']; ?>x)</span>
                        </span>
                        <span class="font-bold text-gray-800">R$ <?php echo number_format($pm['total'], 2, ',', '.'); ?></span>
                    </div>
                <?php endforeach; ?>
                <div class="flex justify-between items-center pt-2 bg-gray-50 p-3 rounded mt-3">
                    <span class="text-lg font-bold text-gray-800">Total de Vendas</span>
                    <span class="text-lg font-bold text-green-600">R$
                        <?php echo number_format($totalGeralVendas, 2, ',', '.'); ?></span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Observações -->
<?php if (!empty($register['notes'])): ?>
    <div class="bg-white shadow-lg rounded-lg overflow-hidden border border-gray-200 mb-6">
        <div class="p-6 bg-gray-50 border-b border-gray-200">
            <h3 class="font-bold text-gray-700">Observações</h3>
        </div>
        <div class="p-6">
            <p class="text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($register['notes']); ?></p>
        </div>
    </div>
<?php endif; ?>

<!-- Ações -->
<div class="flex gap-3">
    <a href="?route=cash/report&id=<?php echo $register['id']; ?>"
        class="bg-primary hover:bg-primary-hover text-white font-bold py-2 px-6 rounded-lg shadow-md flex items-center gap-2 transition-all">
        <i class="fas fa-file-invoice-dollar"></i> Ver Relatório
    </a>
    <?php if ($register['status'] === 'closed'): ?>
        <a href="?route=cash/edit&id=<?php echo $register['id']; ?>"
            class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2 px-6 rounded-lg shadow-md flex items-center gap-2 transition-all">
            <i class="fas fa-edit"></i> Editar
        </a>
    <?php endif; ?>
</div>

<?php require 'views/layouts/footer.php'; ?>