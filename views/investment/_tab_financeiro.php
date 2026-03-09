<?php
$tiposLabels = $tiposLabels ?? [];
$estadosLabels = $estadosLabels ?? [];
$statusLabels = $statusLabels ?? [];
$formasPagamento = ['PIX', 'Dinheiro', 'Transferência', 'Cartão', 'À vista', 'Parcelado', 'Outro'];
?>
<form method="GET" action="<?php echo e($baseUrl); ?>" class="mb-6">
    <input type="hidden" name="route" value="investment/index">
    <input type="hidden" name="tab" value="financeiro">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3">
        <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Data de</label>
            <input type="date" name="start_date" value="<?php echo e($filters['start_date']); ?>" class="w-full rounded-lg border border-gray-300 p-2 text-sm">
        </div>
        <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Data até</label>
            <input type="date" name="end_date" value="<?php echo e($filters['end_date']); ?>" class="w-full rounded-lg border border-gray-300 p-2 text-sm">
        </div>
        <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Tipo</label>
            <select name="tipo" class="w-full rounded-lg border border-gray-300 p-2 text-sm bg-white">
                <option value="">Todos</option>
                <?php foreach ($tiposLabels as $k => $l): ?>
                    <option value="<?php echo e($k); ?>" <?php echo ($filters['tipo'] ?? '') === $k ? 'selected' : ''; ?>><?php echo e($l); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Status</label>
            <select name="status" class="w-full rounded-lg border border-gray-300 p-2 text-sm bg-white">
                <option value="">Todos</option>
                <?php foreach ($statusLabels as $k => $l): ?>
                    <option value="<?php echo e($k); ?>" <?php echo ($filters['status'] ?? '') === $k ? 'selected' : ''; ?>><?php echo e($l); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Forma pagamento</label>
            <select name="forma_pagamento" class="w-full rounded-lg border border-gray-300 p-2 text-sm bg-white">
                <option value="">Todas</option>
                <?php foreach ($formasPagamento as $fp): ?>
                    <option value="<?php echo e($fp); ?>" <?php echo ($filters['forma_pagamento'] ?? '') === $fp ? 'selected' : ''; ?>><?php echo e($fp); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Pessoa</label>
            <input type="text" name="pessoa" value="<?php echo e($filters['pessoa']); ?>" list="list-pessoas-fin" placeholder="Nome" class="w-full rounded-lg border border-gray-300 p-2 text-sm">
            <datalist id="list-pessoas-fin">
                <?php foreach ($pessoas as $p): ?><option value="<?php echo e($p); ?>"><?php endforeach; ?>
            </datalist>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="btn bg-indigo-600 hover:bg-indigo-700 border-none text-white btn-sm rounded-lg font-bold"><i class="fas fa-search mr-1"></i> Filtrar</button>
            <a href="?route=investment/index&tab=financeiro" class="btn btn-ghost btn-sm text-gray-500">Limpar</a>
        </div>
    </div>
</form>

<div class="overflow-x-auto">
    <table class="table table-compact w-full">
        <thead>
            <tr class="text-[10px] text-gray-400 uppercase tracking-widest bg-gray-50/50">
                <th class="py-3 px-3">Data</th>
                <th class="px-3">Pessoa</th>
                <th class="px-3">Descrição</th>
                <th class="px-3">Tipo</th>
                <th class="px-3">Forma pag.</th>
                <th class="px-3 text-right">Valor</th>
                <th class="px-3 text-right">Saldo devedor</th>
                <th class="text-right px-3">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php if (empty($investments)): ?>
                <tr>
                    <td colspan="8" class="text-center py-12 text-gray-400">
                        <p class="mb-2">Nenhum lançamento encontrado.</p>
                        <a href="<?php echo e($baseUrl); ?>?route=investment/create" class="text-indigo-600 font-bold hover:underline">Cadastrar primeiro registro</a>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($investments as $inv): ?>
                    <?php
                    $tipo = $inv['tipo'] ?? '';
                    $badgeClass = 'bg-gray-100 text-gray-700';
                    if (in_array($tipo, ['aporte', 'aporte_socio', 'investimento_dinheiro'], true)) $badgeClass = 'bg-green-100 text-green-800';
                    elseif ($tipo === 'emprestimo') $badgeClass = 'bg-blue-100 text-blue-800';
                    elseif ($tipo === 'doacao') $badgeClass = 'bg-purple-100 text-purple-800';
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-3 text-xs font-bold text-gray-600 whitespace-nowrap"><?php echo date('d/m/Y', strtotime($inv['data'] ?? '')); ?></td>
                        <td class="px-3 text-sm text-gray-800"><?php echo e($inv['pessoa'] ?? '—'); ?></td>
                        <td class="px-3 text-sm text-gray-800"><?php echo e($inv['produto'] ?? '—'); ?></td>
                        <td class="px-3">
                            <span class="text-xs font-bold px-2 py-0.5 rounded <?php echo $badgeClass; ?>"><?php echo e($tiposLabels[$tipo] ?? $tipo); ?></span>
                        </td>
                        <td class="px-3 text-xs text-gray-600"><?php echo e($inv['forma_pagamento'] ?? '—'); ?></td>
                        <td class="px-3 text-sm font-bold text-gray-800 text-right whitespace-nowrap">R$ <?php echo number_format((float)($inv['valor'] ?? 0), 2, ',', '.'); ?></td>
                        <td class="px-3 text-right whitespace-nowrap">
                            <?php if ($tipo === 'emprestimo' && isset($inv['saldo_devedor'])): ?>
                                <span class="text-sm font-bold text-red-600">R$ <?php echo number_format($inv['saldo_devedor'], 2, ',', '.'); ?></span>
                                <a href="<?php echo e($baseUrl); ?>?route=investment/show&id=<?php echo (int)$inv['id']; ?>" class="ml-1 text-indigo-600 text-xs hover:underline" title="Ver detalhes / Pagamentos">Detalhe</a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td class="text-right px-3">
                            <a href="<?php echo e($baseUrl); ?>?route=investment/edit&id=<?php echo (int)$inv['id']; ?>" class="btn btn-ghost btn-xs text-gray-400 hover:text-indigo-600" title="Editar"><i class="fas fa-edit"></i></a>
                            <form action="?route=investment/delete" method="POST" class="inline" onsubmit="return confirm('Excluir este lançamento?');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="id" value="<?php echo (int)$inv['id']; ?>">
                                <button type="submit" class="btn btn-ghost btn-xs text-gray-400 hover:text-red-600" title="Excluir"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
