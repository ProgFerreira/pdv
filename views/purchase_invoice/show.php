<?php
$fornecedor = !empty($item['fornecedor_nome']) ? $item['fornecedor_nome'] : ($item['supplier_name'] ?? '—');
$statusClass = ['PENDENTE' => 'badge-warning', 'PAGO' => 'badge-success', 'CANCELADO' => 'badge-ghost'];
?>
<?php require 'views/layouts/header.php'; ?>

<div class="max-w-4xl mx-auto">
    <?php if (!empty($_GET['success'])): ?>
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl flex items-center gap-2 mb-4">
            <i class="fas fa-check-circle"></i>
            <span><?php echo ($_GET['success'] === 'updated') ? 'Nota fiscal atualizada.' : 'Nota fiscal cadastrada com sucesso.'; ?></span>
        </div>
    <?php endif; ?>

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-800">Nota Fiscal de Compra #<?php echo (int) $item['id']; ?></h1>
            <p class="text-sm text-gray-500"><?php echo e($fornecedor); ?> — <?php echo date('d/m/Y', strtotime($item['data_emissao'])); ?></p>
        </div>
        <div class="flex gap-2">
            <a href="<?php echo BASE_URL; ?>?route=purchase_invoice/edit&id=<?php echo (int) $item['id']; ?>"
                class="btn btn-primary rounded-xl font-bold"><i class="fas fa-edit mr-2"></i>Editar</a>
            <a href="<?php echo BASE_URL; ?>?route=purchase_invoice/index" class="btn btn-ghost rounded-xl">Voltar</a>
        </div>
    </div>

    <div class="card-standard overflow-hidden">
        <div class="card-standard-header"><i class="fas fa-info-circle"></i> Dados da nota</div>
        <div class="card-standard-body">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <dt class="text-xs font-bold text-gray-400 uppercase tracking-widest">Fornecedor</dt>
                    <dd class="text-lg font-bold text-gray-800"><?php echo e($fornecedor); ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-gray-400 uppercase tracking-widest">Telefone</dt>
                    <dd class="text-gray-700"><?php echo e($item['telefone'] ?? '—'); ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-gray-400 uppercase tracking-widest">Data da NF</dt>
                    <dd class="text-gray-700"><?php echo date('d/m/Y', strtotime($item['data_emissao'])); ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-gray-400 uppercase tracking-widest">Valor</dt>
                    <dd class="text-xl font-black text-indigo-600">R$ <?php echo number_format($item['valor'], 2, ',', '.'); ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-gray-400 uppercase tracking-widest">Status</dt>
                    <dd>
                        <span class="badge <?php echo $statusClass[$item['status']] ?? 'badge-ghost'; ?> font-bold"><?php echo e($item['status']); ?></span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-gray-400 uppercase tracking-widest">Data pagamento</dt>
                    <dd class="text-gray-700"><?php echo !empty($item['data_pagamento']) ? date('d/m/Y', strtotime($item['data_pagamento'])) : '—'; ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-gray-400 uppercase tracking-widest">Pago por quem</dt>
                    <dd class="text-gray-700"><?php echo e($item['pago_por_nome'] ?? '—'); ?></dd>
                </div>
                <?php if (!empty($item['observacoes'])): ?>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-bold text-gray-400 uppercase tracking-widest">Observações</dt>
                        <dd class="text-gray-700 mt-1"><?php echo nl2br(e($item['observacoes'])); ?></dd>
                    </div>
                <?php endif; ?>
            </dl>
        </div>
    </div>

    <?php if (!empty($item['arquivo_path']) && is_file($item['arquivo_path'])): ?>
        <div class="card-standard overflow-hidden mt-6">
            <div class="card-standard-header"><i class="fas fa-file"></i> Comprovante / Anexo</div>
            <div class="card-standard-body">
                <p class="text-sm text-gray-600 mb-3"><?php echo e($item['arquivo_nome_original'] ?? basename($item['arquivo_path'])); ?></p>
                <a href="<?php echo BASE_URL; ?>?route=purchase_invoice/download&id=<?php echo (int) $item['id']; ?>"
                    target="_blank" class="btn btn-primary rounded-xl">
                    <i class="fas fa-external-link-alt mr-2"></i>Abrir arquivo
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require 'views/layouts/footer.php'; ?>
