<?php require 'views/layouts/header.php'; ?>

<div class="mb-6">
    <a href="?route=payable/index" class="text-primary hover:text-primary-hover text-sm font-medium mb-2 inline-block">
        <i class="fas fa-arrow-left mr-1"></i> Voltar para a lista
    </a>
    <h2 class="text-2xl font-bold text-gray-800">🆕 Nova Despesa / Conta a Pagar</h2>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200 max-w-2xl mx-auto">
    <form action="?route=payable/create" method="POST" class="p-6 space-y-4">
        <?php echo csrf_field(); ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Descrição da Despesa *</label>
                <input type="text" name="description" required
                    class="w-full border-gray-300 rounded-md shadow-sm p-2 focus:border-primary focus:ring-primary"
                    placeholder="Ex: Aluguel, Compra de Mercadoria, luz...">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Valor Total (R$) *</label>
                <input type="number" step="0.01" name="total_amount" required
                    class="w-full border-gray-300 rounded-md shadow-sm p-2 focus:border-primary focus:ring-primary"
                    placeholder="0.00">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data de Vencimento *</label>
                <input type="date" name="due_date" required value="<?php echo date('Y-m-d'); ?>"
                    class="w-full border-gray-300 rounded-md shadow-sm p-2 focus:border-primary focus:ring-primary">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Fornecedor (Opcional)</label>
                <select name="supplier_id"
                    class="w-full border-gray-300 rounded-md shadow-sm p-2 focus:border-primary focus:ring-primary">
                    <option value="">Selecione um fornecedor</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?php echo $s['id']; ?>">
                            <?php echo htmlspecialchars($s['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                <textarea name="notes" rows="3"
                    class="w-full border-gray-300 rounded-md shadow-sm p-2 focus:border-primary focus:ring-primary"
                    placeholder="Detalhes adicionais..."></textarea>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
            <a href="?route=payable/index"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Cancelar</a>
            <button type="submit"
                class="px-4 py-2 bg-primary text-white rounded-md text-sm font-medium hover:bg-primary-hover transition shadow-sm">
                Salvar Despesa
            </button>
        </div>
    </form>
</div>

<?php require 'views/layouts/footer.php'; ?>