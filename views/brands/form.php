<?php require 'views/layouts/header.php'; ?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">
        <?php echo $isEdit ? '✏️ Editar Marca' : '➕ Nova Marca'; ?>
    </h2>
</div>

<div class="bg-white shadow-md rounded-lg p-6 border border-gray-200">
    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
        <?php echo csrf_field(); ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="lg:col-span-6">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nome da Marca</label>
                <input type="text" id="name" name="name"
                    value="<?php echo isset($brand) ? htmlspecialchars($brand['name']) : ''; ?>" required
                    class="w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                    placeholder="Ex: Editora Ave Maria, Paulus...">
            </div>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow transition-colors">
                <i class="fas fa-save mr-2"></i>
                <?php echo $isEdit ? 'Atualizar' : 'Cadastrar'; ?>
            </button>
            <a href="?route=brand/index"
                class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded shadow transition-colors">
                <i class="fas fa-times mr-2"></i>Cancelar
            </a>
        </div>
    </form>
</div>

<?php require 'views/layouts/footer.php'; ?>