<?php
$isEdit = isset($item) && !empty($item['id']);
$title = $isEdit ? 'Editar Nota Fiscal de Compra' : 'Nova Nota Fiscal de Compra';
$action = $isEdit ? '?route=purchaseInvoice/update' : '?route=purchaseInvoice/store';
$fornecedorNome = $isEdit ? ($item['fornecedor_nome'] ?? '') : '';
$telefone = $isEdit ? ($item['telefone'] ?? '') : '';
$dataEmissao = $isEdit ? ($item['data_emissao'] ?? date('Y-m-d')) : date('Y-m-d');
$status = $isEdit ? ($item['status'] ?? 'PENDENTE') : 'PENDENTE';
$valor = $isEdit ? ($item['valor'] ?? 0) : 0;
$dataPagamento = $isEdit ? ($item['data_pagamento'] ?? '') : '';
$pagoPorId = $isEdit ? ($item['pago_por_user_id'] ?? '') : '';
$observacoes = $isEdit ? ($item['observacoes'] ?? '') : '';
$supplierId = $isEdit ? ($item['supplier_id'] ?? '') : '';
?>
<?php require 'views/layouts/header.php'; ?>

<div class="max-w-4xl mx-auto">
    <div class="card-standard overflow-hidden">
        <div class="card-standard-header">
            <i class="fas fa-file-invoice"></i>
            <?php echo e($title); ?>
        </div>
        <div class="card-standard-body">
            <?php if (!empty($_GET['error'])): ?>
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-4 flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo ($_GET['error'] === 'fornecedor') ? 'Informe o fornecedor.' : 'Erro ao salvar.'; ?></span>
                </div>
            <?php endif; ?>

            <form action="<?php echo e($action); ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
                <?php echo csrf_field(); ?>
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">
                <?php endif; ?>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fornecedor *</label>
                        <input type="text" name="fornecedor_nome" required placeholder="Nome do fornecedor"
                            value="<?php echo e($fornecedorNome); ?>"
                            class="w-full border border-gray-300 p-2 rounded-lg focus:border-primary focus:ring-1 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vincular ao cadastro (opcional)</label>
                        <select name="supplier_id" id="supplier_id" class="w-full border border-gray-300 p-2 rounded-lg bg-white">
                            <option value="">— Nenhum —</option>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?php echo (int) $s['id']; ?>" <?php echo ($supplierId == $s['id']) ? 'selected' : ''; ?>><?php echo e($s['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                        <input type="text" name="telefone" placeholder="(00) 00000-0000"
                            value="<?php echo e($telefone); ?>"
                            class="w-full border border-gray-300 p-2 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data da NF *</label>
                        <input type="date" name="data_emissao" required value="<?php echo e($dataEmissao); ?>"
                            class="w-full border border-gray-300 p-2 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valor (R$) *</label>
                        <input type="number" name="valor" step="0.01" min="0" required
                            value="<?php echo number_format($valor, 2, '.', ''); ?>"
                            class="w-full border border-gray-300 p-2 rounded-lg font-semibold">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full border border-gray-300 p-2 rounded-lg bg-white">
                            <option value="PENDENTE" <?php echo $status === 'PENDENTE' ? 'selected' : ''; ?>>Pendente</option>
                            <option value="PAGO" <?php echo $status === 'PAGO' ? 'selected' : ''; ?>>Pago</option>
                            <option value="CANCELADO" <?php echo $status === 'CANCELADO' ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data pagamento</label>
                        <input type="date" name="data_pagamento" value="<?php echo e($dataPagamento); ?>"
                            class="w-full border border-gray-300 p-2 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pago por quem</label>
                        <select name="pago_por_user_id" class="w-full border border-gray-300 p-2 rounded-lg bg-white">
                            <option value="">— Selecione —</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?php echo (int) $u['id']; ?>" <?php echo ($pagoPorId == $u['id']) ? 'selected' : ''; ?>><?php echo e($u['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload (imagem ou PDF)</label>
                    <input type="file" name="arquivo" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,image/*,application/pdf"
                        class="w-full border border-gray-300 p-2 rounded-lg text-sm">
                    <p class="text-xs text-gray-500 mt-1">Máx. 10 MB. Formatos: JPG, PNG, GIF, WEBP, PDF.</p>
                    <?php if ($isEdit && !empty($item['arquivo_nome_original'])): ?>
                        <p class="text-xs text-gray-600 mt-1">Arquivo atual: <strong><?php echo e($item['arquivo_nome_original']); ?></strong>. Envie outro para substituir.</p>
                    <?php endif; ?>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                    <textarea name="observacoes" rows="3" placeholder="Observações..."
                        class="w-full border border-gray-300 p-2 rounded-lg"><?php echo e($observacoes); ?></textarea>
                </div>

                <div class="flex gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn btn-primary rounded-xl font-bold">
                        <i class="fas fa-save mr-2"></i><?php echo $isEdit ? 'Salvar alterações' : 'Cadastrar'; ?>
                    </button>
                    <a href="<?php echo BASE_URL; ?>?route=purchaseInvoice/index" class="btn btn-ghost rounded-xl">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>
