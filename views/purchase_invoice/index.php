<?php require 'views/layouts/header.php'; ?>

<div class="flex flex-col gap-6">
    <?php if (!empty($_GET['success'])): ?>
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl flex items-center gap-2">
            <i class="fas fa-check-circle"></i>
            <span>
                <?php
                if ($_GET['success'] === 'created') echo 'Nota fiscal cadastrada com sucesso.';
                elseif ($_GET['success'] === 'updated') echo 'Nota fiscal atualizada.';
                elseif ($_GET['success'] === 'deleted') echo 'Nota fiscal excluída.';
                else echo 'Operação realizada.';
                ?>
            </span>
        </div>
    <?php endif; ?>
    <?php if (!empty($_GET['error'])): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl flex items-center gap-2">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo ($_GET['error'] === 'fornecedor') ? 'Informe o fornecedor.' : 'Erro ao salvar. Tente novamente.'; ?></span>
        </div>
    <?php endif; ?>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-800">Notas Fiscais de Compras</h1>
            <p class="text-sm text-gray-400">Armazene e consulte NFs de compras: fornecedor, data, valor, status e comprovante (imagem/PDF).</p>
        </div>
        <a href="<?php echo BASE_URL; ?>?route=purchase_invoice/create"
            class="inline-flex items-center gap-2 btn btn-primary rounded-xl shadow-md font-bold">
            <i class="fas fa-plus"></i> Nova Nota Fiscal
        </a>
    </div>

    <!-- Filtros -->
    <div class="card-standard">
        <div class="card-standard-header"><i class="fas fa-filter"></i> Filtros</div>
        <div class="card-standard-body">
            <form class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 items-end" method="get">
                <input type="hidden" name="route" value="purchase_invoice/index">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-300 p-2 rounded-lg bg-white text-sm">
                        <option value="">Todos</option>
                        <option value="PENDENTE" <?php echo ($filters['status'] ?? '') === 'PENDENTE' ? 'selected' : ''; ?>>Pendente</option>
                        <option value="PAGO" <?php echo ($filters['status'] ?? '') === 'PAGO' ? 'selected' : ''; ?>>Pago</option>
                        <option value="CANCELADO" <?php echo ($filters['status'] ?? '') === 'CANCELADO' ? 'selected' : ''; ?>>Cancelado</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fornecedor</label>
                    <select name="supplier_id" class="w-full border border-gray-300 p-2 rounded-lg bg-white text-sm">
                        <option value="">Todos</option>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?php echo (int) $s['id']; ?>" <?php echo (($filters['supplier_id'] ?? '') == $s['id']) ? 'selected' : ''; ?>><?php echo e($s['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data emissão (início)</label>
                    <input type="date" name="start_date" value="<?php echo e($filters['start_date'] ?? ''); ?>"
                        class="w-full border border-gray-300 p-2 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data emissão (fim)</label>
                    <input type="date" name="end_date" value="<?php echo e($filters['end_date'] ?? ''); ?>"
                        class="w-full border border-gray-300 p-2 rounded-lg text-sm">
                </div>
                <button type="submit" class="btn btn-neutral btn-sm rounded-lg">Filtrar</button>
            </form>
        </div>
    </div>

    <!-- Tabela -->
    <div class="card-standard overflow-hidden">
        <div class="card-standard-header"><i class="fas fa-file-invoice"></i> Listagem</div>
        <div class="overflow-x-auto">
            <table class="table table-compact w-full">
                <thead>
                    <tr class="text-[10px] text-gray-400 uppercase tracking-widest bg-gray-50/50">
                        <th class="py-4 px-6 text-left">Data</th>
                        <th class="text-left">Fornecedor / Telefone</th>
                        <th class="text-right">Valor</th>
                        <th class="text-center">Status</th>
                        <th class="text-left">Pagamento</th>
                        <th class="text-right pr-6">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-12 text-gray-400 italic">Nenhuma nota fiscal cadastrada.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $row): ?>
                            <?php $fornecedor = !empty($row['fornecedor_nome']) ? $row['fornecedor_nome'] : ($row['supplier_name'] ?? '—'); ?>
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="py-4 px-6 text-left text-sm font-medium text-gray-800">
                                    <?php echo date('d/m/Y', strtotime($row['data_emissao'])); ?>
                                </td>
                                <td class="text-left">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-800"><?php echo e($fornecedor); ?></span>
                                        <span class="text-xs text-gray-500"><?php echo e($row['telefone'] ?? '—'); ?></span>
                                    </div>
                                </td>
                                <td class="text-right font-bold text-gray-800">
                                    R$ <?php echo number_format($row['valor'], 2, ',', '.'); ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $sc = ['PENDENTE' => 'badge-warning', 'PAGO' => 'badge-success', 'CANCELADO' => 'badge-ghost'];
                                    $c = $sc[$row['status']] ?? 'badge-ghost';
                                    ?>
                                    <span class="badge <?php echo $c; ?> badge-sm text-[10px] font-bold"><?php echo e($row['status']); ?></span>
                                </td>
                                <td class="text-left text-xs text-gray-600">
                                    <?php if (!empty($row['data_pagamento'])): ?>
                                        <?php echo date('d/m/Y', strtotime($row['data_pagamento'])); ?>
                                        <?php if (!empty($row['pago_por_nome'])): ?>
                                            <span class="text-gray-400">por <?php echo e($row['pago_por_nome']); ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-1">
                                        <?php if (!empty($row['arquivo_path'])): ?>
                                            <a href="<?php echo BASE_URL; ?>?route=purchase_invoice/download&id=<?php echo (int) $row['id']; ?>"
                                                target="_blank" class="btn btn-ghost btn-xs" title="Ver arquivo"><i class="fas fa-file-pdf"></i></a>
                                        <?php endif; ?>
                                        <a href="<?php echo BASE_URL; ?>?route=purchase_invoice/show&id=<?php echo (int) $row['id']; ?>"
                                            class="btn btn-ghost btn-xs" title="Ver"><i class="fas fa-eye"></i></a>
                                        <a href="<?php echo BASE_URL; ?>?route=purchase_invoice/edit&id=<?php echo (int) $row['id']; ?>"
                                            class="btn btn-ghost btn-xs" title="Editar"><i class="fas fa-edit"></i></a>
                                        <a href="<?php echo BASE_URL; ?>?route=purchase_invoice/delete&id=<?php echo (int) $row['id']; ?>"
                                            onclick="return confirm('Excluir esta nota fiscal?');"
                                            class="btn btn-ghost btn-xs text-red-500" title="Excluir"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>
