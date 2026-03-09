<?php
$isEdit = isset($card);
$title = $isEdit ? 'Editar Vale Presente' : 'Novo Vale Presente';
$action = $isEdit ? '?route=giftcard/update' : '?route=giftcard/store';
?>

<?php require 'views/layouts/header.php'; ?>

<div class="w-[95vw] max-w-full mx-auto">
    <div class="card-standard overflow-hidden">
        <div class="card-standard-header">
            <i class="fas fa-gift"></i>
            <?php echo $title; ?>
        </div>
        <div class="card-standard-body">
            <form action="<?php echo htmlspecialchars($action, ENT_QUOTES, 'UTF-8'); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?php echo $card['id']; ?>">
                <?php endif; ?>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div class="lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Código do Vale (Número)</label>
                        <div class="flex gap-2">
                            <input type="text" name="code" id="gc_code" placeholder="Ex: VALE123 (Deixe branco para gerar auto)"
                                value="<?php echo htmlspecialchars($isEdit ? $card['code'] : '', ENT_QUOTES, 'UTF-8'); ?>"
                                <?php echo $isEdit ? 'readonly' : ''; ?>
                                class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 <?php echo $isEdit ? 'bg-gray-100' : ''; ?>">
                            <?php if (!$isEdit): ?>
                            <button type="button" onclick="generateCode()"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 border border-gray-300">
                                <i class="fas fa-random"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Este é o número que o cliente usará na hora do pagamento.</p>
                    </div>

                    <div class="lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cliente Vinculado</label>
                        <select name="customer_id"
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white">
                            <option value="">-- Selecione o Cliente (Opcional) --</option>
                            <?php foreach ($customers as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo ($isEdit && $card['customer_id'] == $c['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['name']); ?> (<?php echo $c['phone']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valor do Vale (R$)</label>
                        <input type="text" name="initial_value" required placeholder="0,00"
                            value="<?php echo $isEdit ? number_format($card['initial_value'], 2, ',', '.') : ''; ?>"
                            <?php echo $isEdit ? 'readonly' : ''; ?>
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 text-xl font-bold text-primary <?php echo $isEdit ? 'bg-gray-50' : ''; ?>">
                        <?php if ($isEdit): ?>
                            <p class="text-xs text-amber-600 mt-1"><i class="fas fa-info-circle"></i> O valor não pode ser editado após a emissão.</p>
                        <?php endif; ?>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data de Expiração (Opcional)</label>
                        <input type="date" name="expiry_date"
                            value="<?php echo ($isEdit && $card['expiry_date']) ? date('Y-m-d', strtotime($card['expiry_date'])) : ''; ?>"
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white">
                    </div>
                </div>

                <div class="mt-8 flex gap-4">
                    <button type="submit"
                        class="bg-primary hover:bg-primary-hover text-white font-bold py-2 px-8 shadow transition flex items-center gap-2">
                        <i class="fas fa-save mr-2"></i> <?php echo $isEdit ? 'Salvar Alterações' : 'Emitir Vale'; ?>
                    </button>
                    <a href="?route=giftcard/index"
                        class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-8 shadow transition">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function generateCode() {
        const code = Math.random().toString(36).substring(2, 10).toUpperCase();
        document.getElementById('gc_code').value = code;
    }
</script>

<?php require 'views/layouts/footer.php'; ?>
