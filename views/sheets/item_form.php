<?php
$isEdit = !empty($item);
$item = $item ?? [];
?>
<?php require 'views/layouts/header.php'; ?>

<div class="w-full max-w-xl mx-auto px-4 py-6">
    <div class="mb-4">
        <a href="<?php echo BASE_URL; ?>?route=technicalSheet/view&product_id=<?php echo (int)$product['id']; ?>"
            class="text-sm text-gray-600 hover:text-gray-800"><i class="fas fa-arrow-left mr-1"></i> Voltar à ficha técnica</a>
    </div>
    <div class="card-standard">
        <div class="card-standard-header"><?php echo $isEdit ? 'Editar item da ficha' : 'Adicionar item à ficha'; ?>: <?php echo e($product['name']); ?></div>
        <div class="card-standard-body">
            <?php if (!empty($error)): ?>
                <div class="mb-4 p-3 bg-red-100 border border-red-200 text-red-800 rounded-lg text-sm"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo BASE_URL; ?>?route=<?php echo $isEdit ? 'technicalSheet/itemUpdate' : 'technicalSheet/itemAdd'; ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="sheet_id" value="<?php echo (int)$sheet['id']; ?>">
                <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                <?php if ($isEdit): ?>
                <input type="hidden" name="item_id" value="<?php echo (int)$item['id']; ?>">
                <?php endif; ?>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Insumo <span class="text-red-500">*</span></label>
                        <select name="ingredient_id" required class="w-full rounded-md border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary">
                            <option value="">Selecione...</option>
                            <?php foreach ($ingredients as $ing): ?>
                                <option value="<?php echo (int)$ing['id']; ?>" <?php echo ($isEdit && (int)($item['ingredient_id'] ?? 0) === (int)$ing['id']) ? 'selected' : ''; ?>>
                                    <?php echo e($ing['name']); ?> (<?php echo e($ing['unit']); ?> – <?php echo money($ing['cost_per_unit'] ?? 0); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Classificação</label>
                        <input type="text" name="item_classification" value="<?php echo e($item['item_classification'] ?? ''); ?>"
                            class="w-full rounded-md border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="Ex: proteína, base, acompanhamento, embalagem">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade bruta <span class="text-red-500">*</span></label>
                            <input type="text" name="item_qty_brut" required value="<?php echo e(isset($item['item_qty_brut']) ? number_format((float)$item['item_qty_brut'], 2, ',', '') : ''); ?>"
                                class="w-full rounded-md border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Ex: 500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Unidade do item</label>
                            <select name="item_unit" class="w-full rounded-md border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary">
                                <?php
                                $units = ['g' => 'g', 'kg' => 'kg', 'ml' => 'ml', 'l' => 'l', 'un' => 'un'];
                                $currentUnit = $item['item_unit'] ?? 'g';
                                foreach ($units as $v => $l):
                                ?>
                                    <option value="<?php echo e($v); ?>" <?php echo $currentUnit === $v ? 'selected' : ''; ?>><?php echo e($l); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade líquida (opcional)</label>
                            <input type="text" name="item_qty_net" value="<?php echo e(isset($item['item_qty_net']) && $item['item_qty_net'] !== null ? number_format((float)$item['item_qty_net'], 2, ',', '') : ''); ?>"
                                class="w-full rounded-md border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Ex: 400">
                            <p class="text-xs text-gray-500 mt-1">Se informado, o rendimento % será calculado automaticamente.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rendimento % (opcional)</label>
                            <input type="text" name="item_yield_percent" value="<?php echo e(isset($item['item_yield_percent']) && $item['item_yield_percent'] !== null ? number_format((float)$item['item_yield_percent'], 1, ',', '') : ''); ?>"
                                class="w-full rounded-md border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Ex: 80">
                        </div>
                    </div>
                </div>
                <br>
                <div class="mt-6 flex gap-3">
                    <button type="submit" class="btn btn-primary rounded-lg px-4 py-2">
                        <i class="fas fa-<?php echo $isEdit ? 'save' : 'plus'; ?> mr-2"></i><?php echo $isEdit ? 'Salvar alterações' : 'Adicionar à ficha'; ?>
                    </button>
                    <a href="<?php echo BASE_URL; ?>?route=technicalSheet/view&product_id=<?php echo (int)$product['id']; ?>"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg px-4 py-2 no-underline">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>
