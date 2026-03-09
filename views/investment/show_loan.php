<?php
$investment = $investment ?? null;
$payments = $payments ?? [];
$totalPago = (float) ($totalPago ?? 0);
$saldoDevedor = (float) ($saldoDevedor ?? 0);
$valorPrincipal = (float) ($investment['valor'] ?? 0);
$baseUrl = BASE_URL ?? '';
?>
<?php require 'views/layouts/header.php'; ?>

<div class="flex flex-col gap-6">
    <div class="flex justify-between items-center flex-wrap gap-4">
        <div>
            <a href="<?php echo e($baseUrl); ?>?route=investment/index&tab=financeiro" class="text-indigo-600 hover:underline text-sm font-bold mb-2 inline-block"><i class="fas fa-arrow-left mr-1"></i> Voltar ao Financeiro</a>
            <h1 class="text-2xl font-black text-gray-800">Detalhe do Empréstimo</h1>
            <p class="text-sm text-gray-400"><?php echo e($investment['pessoa'] ?? ''); ?> — <?php echo e($investment['produto'] ?? ''); ?></p>
        </div>
    </div>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'payment'): ?>
        <div class="p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 flex items-center gap-3">
            <i class="fas fa-check-circle text-green-600"></i>
            <span>Pagamento registrado com sucesso.</span>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 flex items-center gap-3">
            <i class="fas fa-exclamation-circle text-red-600"></i>
            <span><?php echo isset($_GET['error']) && $_GET['error'] === 'valor' ? 'Informe um valor válido para o pagamento.' : 'Não foi possível registrar o pagamento.'; ?></span>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card-standard overflow-hidden">
            <div class="card-standard-header">Resumo</div>
            <div class="card-standard-body space-y-3">
                <div class="flex justify-between"><span class="text-gray-600">Valor principal</span><strong>R$ <?php echo number_format($valorPrincipal, 2, ',', '.'); ?></strong></div>
                <div class="flex justify-between"><span class="text-gray-600">Total pago</span><strong class="text-green-600">R$ <?php echo number_format($totalPago, 2, ',', '.'); ?></strong></div>
                <div class="flex justify-between border-t pt-3"><span class="text-gray-600">Saldo devedor</span><strong class="text-red-600 text-lg">R$ <?php echo number_format($saldoDevedor, 2, ',', '.'); ?></strong></div>
                <div class="flex justify-between text-sm text-gray-500">
                    <span>Data prevista devolução</span>
                    <span><?php echo !empty($investment['data_devolucao_prevista']) ? date('d/m/Y', strtotime($investment['data_devolucao_prevista'])) : '—'; ?></span>
                </div>
            </div>
        </div>
        <div class="card-standard overflow-hidden">
            <div class="card-standard-header">Registrar pagamento</div>
            <div class="card-standard-body">
                <form action="<?php echo e($baseUrl); ?>?route=investment/paymentStore" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="investimento_id" value="<?php echo (int)($investment['id'] ?? 0); ?>">
                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Data pagamento</label>
                            <input type="date" name="data_pagamento" value="<?php echo date('Y-m-d'); ?>" required class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Valor pago (R$)</label>
                            <input type="text" name="valor_pago" placeholder="0,00" required class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Forma de pagamento</label>
                            <select name="forma_pagamento" class="w-full border border-gray-300 rounded-lg p-2 text-sm bg-white">
                                <option value="">—</option>
                                <option value="PIX">PIX</option>
                                <option value="Dinheiro">Dinheiro</option>
                                <option value="Transferência">Transferência</option>
                                <option value="Cartão">Cartão</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Observação</label>
                            <input type="text" name="observacao" placeholder="Opcional" class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                        </div>
                        <button type="submit" class="btn bg-indigo-600 hover:bg-indigo-700 border-none text-white rounded-lg font-bold">
                            <i class="fas fa-check mr-2"></i> Registrar pagamento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card-standard overflow-hidden">
        <div class="card-standard-header">Histórico de pagamentos</div>
        <div class="overflow-x-auto">
            <table class="table table-compact w-full">
                <thead>
                    <tr class="text-[10px] text-gray-400 uppercase tracking-widest bg-gray-50/50">
                        <th class="py-3 px-3">Data</th>
                        <th class="px-3">Forma</th>
                        <th class="px-3 text-right">Valor pago</th>
                        <th class="px-3">Observação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($payments)): ?>
                        <tr><td colspan="4" class="py-8 text-center text-gray-400">Nenhum pagamento registrado ainda.</td></tr>
                    <?php else: ?>
                        <?php foreach ($payments as $pay): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-3 text-sm font-bold"><?php echo date('d/m/Y', strtotime($pay['data_pagamento'])); ?></td>
                                <td class="px-3 text-sm text-gray-600"><?php echo e($pay['forma_pagamento'] ?? '—'); ?></td>
                                <td class="px-3 text-right font-bold text-green-600">R$ <?php echo number_format((float)($pay['valor_pago'] ?? 0), 2, ',', '.'); ?></td>
                                <td class="px-3 text-sm text-gray-500"><?php echo e($pay['observacao'] ?? '—'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>
