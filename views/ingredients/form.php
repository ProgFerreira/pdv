<?php require 'views/layouts/header.php'; ?>

<div class="w-full max-w-2xl mx-auto px-4 py-6">
    <div class="card-standard">
        <div class="card-standard-header">
            <i class="fas fa-flask"></i>
            <?php echo $isEdit ? 'Editar Insumo' : 'Novo Insumo'; ?>
        </div>
        <div class="card-standard-body">
            <?php if (!empty($error)): ?>
                <div class="mb-4 p-3 bg-red-100 border border-red-200 text-red-800 rounded-lg text-sm"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo BASE_URL; ?>?route=<?php echo $isEdit ? 'ingredient/edit&id=' . (int)($ingredient['id'] ?? 0) : 'ingredient/create'; ?>">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Código</label>
                        <input type="text" name="code" value="<?php echo e($ingredient['code'] ?? ''); ?>"
                            class="w-full rounded-md border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="Ex: INS-001">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required value="<?php echo e($ingredient['name'] ?? ''); ?>"
                            class="w-full rounded-md border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="Ex: Frango inteiro">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unidade</label>
                        <select name="unit" class="w-full rounded-md border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary">
                            <?php
                            $units = ['kg' => 'kg', 'g' => 'g', 'l' => 'l', 'ml' => 'ml', 'un' => 'un'];
                            $current = $ingredient['unit'] ?? 'kg';
                            foreach ($units as $v => $l):
                            ?>
                                <option value="<?php echo e($v); ?>" <?php echo $current === $v ? 'selected' : ''; ?>><?php echo e($l); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Custo por unidade (R$)</label>
                        <input type="text" name="cost_per_unit" value="<?php echo e(number_format((float)($ingredient['cost_per_unit'] ?? 0), 4, ',', '')); ?>"
                            class="w-full rounded-md border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="0,0000">
                        <p class="text-xs text-gray-500 mt-1">Ex: custo por kg, por litro ou por unidade.</p>
                    </div>
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="active" value="1" <?php echo (isset($ingredient['active']) && $ingredient['active']) ? 'checked' : ''; ?>>
                            <span class="text-sm text-gray-700">Ativo</span>
                        </label>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <button type="submit" class="btn btn-primary rounded-lg px-4 py-2">
                        <i class="fas fa-save mr-2"></i><?php echo $isEdit ? 'Salvar' : 'Cadastrar'; ?>
                    </button>
                    <a href="<?php echo BASE_URL; ?>?route=ingredient/index" class="btn bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg px-4 py-2 no-underline">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>
