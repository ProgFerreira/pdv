<?php
$origemLabels = $origemLabels ?? ['comprado' => 'Comprado', 'doado' => 'Doado', 'emprestado' => 'Emprestado'];
?>
<form method="GET" action="<?php echo e($baseUrl); ?>" class="mb-6">
    <input type="hidden" name="route" value="investment/index">
    <input type="hidden" name="tab" value="bens">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
        <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Categoria</label>
            <select name="asset_categoria" class="w-full rounded-lg border border-gray-300 p-2 text-sm bg-white">
                <option value="">Todas</option>
                <?php foreach ($assetCategorias as $c): ?>
                    <option value="<?php echo e($c); ?>" <?php echo ($assetFilters['categoria'] ?? '') === $c ? 'selected' : ''; ?>><?php echo e($c); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Origem</label>
            <select name="asset_origem" class="w-full rounded-lg border border-gray-300 p-2 text-sm bg-white">
                <option value="">Todas</option>
                <?php foreach ($origemLabels as $k => $l): ?>
                    <option value="<?php echo e($k); ?>" <?php echo ($assetFilters['origem'] ?? '') === $k ? 'selected' : ''; ?>><?php echo e($l); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Responsável</label>
            <select name="asset_responsavel" class="w-full rounded-lg border border-gray-300 p-2 text-sm bg-white">
                <option value="">Todos</option>
                <?php foreach ($participants as $p): ?>
                    <option value="<?php echo (int)($p['id'] ?? 0); ?>" <?php echo ($assetFilters['responsavel_id'] ?? '') == ($p['id'] ?? '') ? 'selected' : ''; ?>><?php echo e($p['name'] ?? ''); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="btn bg-indigo-600 hover:bg-indigo-700 border-none text-white btn-sm rounded-lg font-bold"><i class="fas fa-search mr-1"></i> Filtrar</button>
            <a href="?route=investment/index&tab=bens" class="btn btn-ghost btn-sm text-gray-500">Limpar</a>
        </div>
    </div>
</form>

<p class="text-sm text-gray-500 mb-4">Total de bens: <strong><?php echo (int)$assetTotals['quantidade']; ?></strong> — Valor estimado total: <strong>R$ <?php echo number_format($assetTotals['valor_total'], 2, ',', '.'); ?></strong></p>

<div class="overflow-x-auto">
    <table class="table table-compact w-full">
        <thead>
            <tr class="text-[10px] text-gray-400 uppercase tracking-widest bg-gray-50/50">
                <th class="py-3 px-3">Descrição</th>
                <th class="px-3">Categoria</th>
                <th class="px-3">Origem</th>
                <th class="px-3">Responsável</th>
                <th class="px-3">Localização</th>
                <th class="px-3 text-right">Valor est.</th>
                <th class="px-3">Data entrada</th>
                <th class="text-right px-3">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php if (empty($assets)): ?>
                <tr>
                    <td colspan="8" class="text-center py-12 text-gray-400">
                        <p class="mb-2">Nenhum bem/equipamento cadastrado.</p>
                        <a href="<?php echo e($baseUrl); ?>?route=investment/assetCreate" class="text-indigo-600 font-bold hover:underline">Cadastrar primeiro bem</a>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($assets as $a): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-3 text-sm font-bold text-gray-800"><?php echo e($a['descricao']); ?></td>
                        <td class="px-3 text-xs text-gray-600"><?php echo e($a['categoria'] ?? '—'); ?></td>
                        <td class="px-3"><span class="text-xs px-2 py-0.5 rounded <?php echo ($a['origem'] ?? '') === 'doado' ? 'bg-purple-100 text-purple-800' : (($a['origem'] ?? '') === 'emprestado' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700'); ?>"><?php echo e($origemLabels[$a['origem'] ?? ''] ?? $a['origem'] ?? '—'); ?></span></td>
                        <td class="px-3 text-sm text-gray-700"><?php echo e($a['responsavel_name'] ?? '—'); ?></td>
                        <td class="px-3 text-xs text-gray-600"><?php echo e($a['localizacao'] ?? '—'); ?></td>
                        <td class="px-3 text-sm font-bold text-right">R$ <?php echo number_format((float)($a['valor_estimado'] ?? 0), 2, ',', '.'); ?></td>
                        <td class="px-3 text-xs text-gray-600"><?php echo date('d/m/Y', strtotime($a['data_entrada'] ?? '')); ?></td>
                        <td class="text-right px-3">
                            <a href="<?php echo e($baseUrl); ?>?route=investment/assetEdit&id=<?php echo (int)$a['id']; ?>" class="btn btn-ghost btn-xs text-gray-400 hover:text-indigo-600" title="Editar"><i class="fas fa-edit"></i></a>
                            <form action="?route=investment/assetDelete" method="POST" class="inline" onsubmit="return confirm('Excluir este bem?');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="id" value="<?php echo (int)$a['id']; ?>">
                                <button type="submit" class="btn btn-ghost btn-xs text-gray-400 hover:text-red-600" title="Excluir"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
