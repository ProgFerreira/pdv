<?php
// Resumo geral já está nos KPIs no topo; aqui repetimos em formato de relatório + por pessoa, dívidas, patrimônio
?>
<h3 class="text-lg font-black text-gray-800 mb-4">Resumo Geral</h3>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="p-4 rounded-xl border border-gray-200 bg-white">
        <p class="text-xs text-gray-500 uppercase font-bold">Total Aportes</p>
        <p class="text-xl font-black text-green-600">R$ <?php echo number_format($kpis['total_aportes'], 2, ',', '.'); ?></p>
    </div>
    <div class="p-4 rounded-xl border border-gray-200 bg-white">
        <p class="text-xs text-gray-500 uppercase font-bold">Total Empréstimos</p>
        <p class="text-xl font-black text-blue-600">R$ <?php echo number_format($kpis['total_emprestado'], 2, ',', '.'); ?></p>
    </div>
    <div class="p-4 rounded-xl border border-gray-200 bg-white">
        <p class="text-xs text-gray-500 uppercase font-bold">Total Doações</p>
        <p class="text-xl font-black text-purple-600">R$ <?php echo number_format($kpis['total_doado'], 2, ',', '.'); ?></p>
    </div>
    <div class="p-4 rounded-xl border border-gray-200 bg-white">
        <p class="text-xs text-gray-500 uppercase font-bold">Dívidas em aberto</p>
        <p class="text-xl font-black text-red-600">R$ <?php echo number_format($kpis['total_em_aberto'], 2, ',', '.'); ?></p>
    </div>
</div>

<h3 class="text-lg font-black text-gray-800 mb-4">Por Pessoa (Participação + totais)</h3>
<p class="text-sm text-gray-500 mb-4">Quadro societário com valor total aportado e percentual.</p>
<div class="overflow-x-auto mb-8">
    <table class="table table-compact w-full">
        <thead>
            <tr class="text-[10px] text-gray-400 uppercase tracking-widest bg-gray-50/50">
                <th class="py-3 px-3">Participante</th>
                <th class="px-3 text-right">Total aportado</th>
                <th class="px-3 text-right">%</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($participacao)): ?>
                <tr><td colspan="3" class="py-6 text-center text-gray-400">Nenhum dado.</td></tr>
            <?php else: ?>
                <?php foreach ($participacao as $p): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-3 font-bold"><?php echo e($p['pessoa_nome']); ?></td>
                        <td class="px-3 text-right font-bold">R$ <?php echo number_format($p['total_aportado'], 2, ',', '.'); ?></td>
                        <td class="px-3 text-right"><?php echo number_format($p['percentual'], 1, ',', '.'); ?>%</td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<h3 class="text-lg font-black text-gray-800 mb-4">Patrimônio (Equipamentos)</h3>
<div class="p-4 rounded-xl border border-gray-200 bg-white mb-6">
    <p class="text-sm text-gray-500">Total de bens: <strong><?php echo (int)$assetTotals['quantidade']; ?></strong> — Valor estimado: <strong>R$ <?php echo number_format($assetTotals['valor_total'], 2, ',', '.'); ?></strong></p>
</div>

<p class="text-sm text-gray-500">Para dívidas por vencimento e saldo por pessoa, utilize a aba <strong>Financeiro Recebido</strong> e filtre por tipo &quot;Empréstimo&quot; e status. Use o link &quot;Detalhe&quot; em cada empréstimo para ver pagamentos e saldo.</p>
