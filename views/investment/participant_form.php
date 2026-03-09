<?php
$participant = $participant ?? null;
$isEdit = !empty($participant);
$baseUrl = BASE_URL ?? '';
?>
<?php require 'views/layouts/header.php'; ?>

<div class="max-w-2xl">
    <div class="card-standard overflow-hidden">
        <div class="card-standard-header flex items-center justify-between flex-wrap gap-2">
            <span><i class="fas fa-user mr-2"></i><?php echo $isEdit ? 'Editar participante' : 'Novo participante'; ?></span>
            <a href="<?php echo e($baseUrl); ?>?route=investment/index&tab=participacao" class="btn btn-ghost btn-sm text-gray-500 hover:text-gray-700 no-underline"><i class="fas fa-arrow-left mr-1"></i> Voltar</a>
        </div>
        <div class="card-standard-body">
            <form action="<?php echo e($baseUrl); ?>?route=<?php echo $isEdit ? 'investment/participantUpdate' : 'investment/participantStore'; ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php if ($isEdit): ?><input type="hidden" name="id" value="<?php echo (int)($participant['id'] ?? 0); ?>"><?php endif; ?>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Nome <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required value="<?php echo e($participant['name'] ?? ''); ?>" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Contato</label>
                        <input type="text" name="contact" value="<?php echo e($participant['contact'] ?? ''); ?>" placeholder="Telefone, e-mail" class="w-full border border-gray-300 rounded-lg p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Documento</label>
                        <input type="text" name="document" value="<?php echo e($participant['document'] ?? ''); ?>" placeholder="CPF/CNPJ (opcional)" class="w-full border border-gray-300 rounded-lg p-2">
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t flex gap-3">
                    <button type="submit" class="btn bg-indigo-600 hover:bg-indigo-700 border-none text-white rounded-xl font-bold"><i class="fas fa-save mr-2"></i> Salvar</button>
                    <a href="<?php echo e($baseUrl); ?>?route=investment/index&tab=participacao" class="btn btn-ghost text-gray-600">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>
