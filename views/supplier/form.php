<?php
$isEdit = isset($supplier);
$title = $isEdit ? 'Editar Fornecedor' : 'Novo Fornecedor';
$action = $isEdit ? '?route=supplier/update' : '?route=supplier/store';
?>

<?php require 'views/layouts/header.php'; ?>

<div class="w-[95vw] max-w-full mx-auto">
    <div class="card-standard overflow-hidden">
        <div class="card-standard-header">
            <i class="fas fa-truck"></i>
            <?php echo $title; ?>
        </div>
        <div class="card-standard-body">
            <form action="<?php echo htmlspecialchars($action, ENT_QUOTES, 'UTF-8'); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?php echo $supplier['id']; ?>">
                <?php endif; ?>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div class="lg:col-span-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Empresa / Fornecedor *</label>
                        <input type="text" name="name" required placeholder="Ex: Distribuidora de Alimentos S/A"
                            value="<?php echo $isEdit ? htmlspecialchars($supplier['name']) : ''; ?>"
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50">
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pessoa de Contato</label>
                        <input type="text" name="contact_person" placeholder="Ex: João Silva"
                            value="<?php echo $isEdit ? htmlspecialchars($supplier['contact_person']) : ''; ?>"
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50">
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telefone / WhatsApp</label>
                        <input type="text" name="phone" placeholder="(00) 00000-0000"
                            value="<?php echo $isEdit ? htmlspecialchars($supplier['phone']) : ''; ?>"
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50">
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                        <input type="email" name="email" placeholder="vendas@fornecedor.com.br"
                            value="<?php echo $isEdit ? htmlspecialchars($supplier['email']) : ''; ?>"
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50">
                    </div>
                    <div class="lg:col-span-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Endereço Completo</label>
                        <textarea name="address" rows="3" placeholder="Rua, Número, Bairro, Cidade - UF"
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50"><?php echo $isEdit ? htmlspecialchars($supplier['address']) : ''; ?></textarea>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-100 flex gap-4">
                    <button type="submit"
                        class="bg-primary hover:bg-primary-hover text-white font-bold py-3 px-10 shadow-lg transition flex items-center gap-2">
                        <i class="fas fa-save mr-2"></i>
                        <?php echo $isEdit ? 'Salvar Alterações' : 'Cadastrar Fornecedor'; ?>
                    </button>
                    <a href="?route=supplier/index"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-3 px-10 transition">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>
