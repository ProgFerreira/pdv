<?php require 'views/layouts/header.php'; ?>

<div class="w-[95vw] max-w-full mx-auto">
    <div class="card-standard overflow-hidden">
        <div class="card-standard-header">
            <i class="fas fa-tags"></i>
            <?php echo $isEdit ? 'Editar Categoria' : 'Nova Categoria'; ?>
        </div>
        <div class="card-standard-body">
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 mb-4">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div class="lg:col-span-6">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nome da Categoria</label>
                        <input type="text" id="name" name="name"
                            value="<?php echo isset($category) ? htmlspecialchars($category['name']) : ''; ?>" required
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50"
                            placeholder="Ex: Terços, Bíblias, Imagens...">
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit"
                        class="btn btn-primary inline-flex items-center gap-2">
                        <i class="fas fa-save"></i>
                        <?php echo $isEdit ? 'Atualizar' : 'Cadastrar'; ?>
                    </button>
                    <a href="?route=category/index"
                        class="btn btn-outline-secondary inline-flex items-center gap-2 no-underline">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>
