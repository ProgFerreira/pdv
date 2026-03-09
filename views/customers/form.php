<?php require 'views/layouts/header.php'; ?>

<div class="w-[95vw] max-w-full mx-auto">
    <div class="card-standard overflow-hidden">
        <div class="card-standard-header">
            <i class="fas fa-user-plus"></i>
            <?php echo $isEdit ? 'Editar Cliente' : 'Novo Cliente'; ?>
        </div>
        <div class="card-standard-body">
            <form method="POST" class="space-y-6">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div class="lg:col-span-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo <span class="text-red-500">*</span></label>
                        <input type="text" name="name" class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50" required value="<?php echo htmlspecialchars($customer['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telefone / WhatsApp</label>
                        <input type="text" name="phone" class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50" value="<?php echo htmlspecialchars($customer['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50" value="<?php echo htmlspecialchars($customer['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="lg:col-span-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Endereço</label>
                        <textarea name="address" class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50" rows="3"><?php echo htmlspecialchars($customer['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100">
                    <a href="?route=customer/index" class="text-gray-600 hover:text-gray-900 font-medium px-4 py-2 border border-gray-300 hover:bg-gray-100 transition">Cancelar</a>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 shadow transition flex items-center gap-2">
                        <i class="fas fa-save"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>
