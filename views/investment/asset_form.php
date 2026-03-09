<?php
$asset = $asset ?? null;
$participants = $participants ?? [];
$isEdit = !empty($asset);
$baseUrl = BASE_URL ?? '';
$categoriasSugeridas = ['Freezer', 'Forno', 'Utensílio', 'Móvel', 'Eletrônico', 'Veículo', 'Outro'];
?>
<?php require 'views/layouts/header.php'; ?>

<div class="max-w-3xl">
    <div class="card-standard overflow-hidden">
        <div class="card-standard-header flex items-center justify-between flex-wrap gap-2">
            <span><i class="fas fa-box mr-2"></i><?php echo $isEdit ? 'Editar bem/equipamento' : 'Novo bem/equipamento'; ?></span>
            <a href="<?php echo e($baseUrl); ?>?route=investment/index&tab=bens" class="btn btn-ghost btn-sm text-gray-500 hover:text-gray-700 no-underline"><i class="fas fa-arrow-left mr-1"></i> Voltar</a>
        </div>
        <div class="card-standard-body">
            <form action="<?php echo e($baseUrl); ?>?route=<?php echo $isEdit ? 'investment/assetUpdate' : 'investment/assetStore'; ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php if ($isEdit): ?><input type="hidden" name="id" value="<?php echo (int)($asset['id'] ?? 0); ?>"><?php endif; ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Descrição <span class="text-red-500">*</span></label>
                        <input type="text" name="descricao" required value="<?php echo e($asset['descricao'] ?? ''); ?>" placeholder="Ex: Forno industrial" class="w-full border border-gray-300 rounded-lg p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Categoria</label>
                        <input type="text" name="categoria" value="<?php echo e($asset['categoria'] ?? ''); ?>" list="list-cat-asset" placeholder="Ex: Forno, Freezer" class="w-full border border-gray-300 rounded-lg p-2">
                        <datalist id="list-cat-asset">
                            <?php foreach ($categoriasSugeridas as $c): ?><option value="<?php echo e($c); ?>"><?php endforeach; ?>
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Valor estimado (R$)</label>
                        <input type="text" name="valor_estimado" value="<?php echo e($asset['valor_estimado'] ?? ''); ?>" placeholder="0,00" class="w-full border border-gray-300 rounded-lg p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Data de entrada</label>
                        <input type="date" name="data_entrada" value="<?php echo e($asset['data_entrada'] ?? date('Y-m-d')); ?>" class="w-full border border-gray-300 rounded-lg p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Origem</label>
                        <select name="origem" class="w-full border border-gray-300 rounded-lg p-2 bg-white">
                            <option value="comprado" <?php echo ($asset['origem'] ?? '') === 'comprado' ? 'selected' : ''; ?>>Comprado</option>
                            <option value="doado" <?php echo ($asset['origem'] ?? '') === 'doado' ? 'selected' : ''; ?>>Doado</option>
                            <option value="emprestado" <?php echo ($asset['origem'] ?? '') === 'emprestado' ? 'selected' : ''; ?>>Emprestado</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Responsável / Quem doou</label>
                        <select name="responsavel_id" class="w-full border border-gray-300 rounded-lg p-2 bg-white">
                            <option value="">—</option>
                            <?php foreach ($participants as $p): ?>
                                <option value="<?php echo (int)($p['id'] ?? 0); ?>" <?php echo (($asset['responsavel_id'] ?? '') == ($p['id'] ?? '')) ? 'selected' : ''; ?>><?php echo e($p['name'] ?? ''); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Localização</label>
                        <input type="text" name="localizacao" value="<?php echo e($asset['localizacao'] ?? ''); ?>" placeholder="Ex: Cozinha, Estoque" class="w-full border border-gray-300 rounded-lg p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Vida útil (meses)</label>
                        <input type="number" name="vida_util_meses" value="<?php echo e($asset['vida_util_meses'] ?? ''); ?>" min="1" placeholder="Opcional" class="w-full border border-gray-300 rounded-lg p-2">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Observações</label>
                        <textarea name="observacoes" rows="2" class="w-full border border-gray-300 rounded-lg p-2"><?php echo e($asset['observacoes'] ?? ''); ?></textarea>
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t flex gap-3">
                    <button type="submit" class="btn bg-indigo-600 hover:bg-indigo-700 border-none text-white rounded-xl font-bold"><i class="fas fa-save mr-2"></i> Salvar</button>
                    <a href="<?php echo e($baseUrl); ?>?route=investment/index&tab=bens" class="btn btn-ghost text-gray-600">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>
