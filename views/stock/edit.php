<?php require 'views/layouts/header.php'; ?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">⚙️ Editar Cabeçalho da Entrada #
        <?php echo $entry['id']; ?>
    </h2>
    <p class="text-sm text-gray-500">Corrija informações administrativas desta nota fiscal.</p>
</div>

<?php if (isset($_GET['error'])): ?>
    <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded shadow-sm mb-8 flex items-center gap-3">
        <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
        <span>Erro ao salvar as alterações. Tente novamente.</span>
    </div>
<?php endif; ?>

<div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100 max-w-2xl px-8 py-10">
    <form action="?route=stock/update" method="POST" class="space-y-6">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="id" value="<?php echo (int) $entry['id']; ?>">

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Nota Fiscal /
                    Referência</label>
                <input type="text" name="reference" value="<?php echo htmlspecialchars($entry['reference']); ?>"
                    class="w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Fornecedor</label>
                <input type="text" name="supplier" value="<?php echo htmlspecialchars($entry['supplier']); ?>"
                    class="w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Data de
                    Entrada</label>
                <input type="date" name="entry_date"
                    value="<?php echo date('Y-m-d', strtotime($entry['entry_date'])); ?>"
                    class="w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Observações
                    Internas</label>
                <textarea name="notes" rows="3"
                    class="w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"><?php echo htmlspecialchars($entry['notes']); ?></textarea>
            </div>
        </div>

        <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 flex items-start gap-3 mt-8">
            <i class="fas fa-info-circle text-blue-500 mt-1"></i>
            <p class="text-xs text-blue-800 leading-relaxed">
                <strong>Atenção:</strong> Por segurança, os itens e quantidades desta nota não podem ser editados
                individualmente. Se precisar corrigir as quantidades, exclua a entrada e registre uma nova nota correta.
            </p>
        </div>

        <div class="flex flex-wrap gap-4 pt-6 mt-6 border-t border-gray-50">
            <button type="submit"
                class="bg-primary hover:bg-primary-hover text-white font-bold py-3 px-10 rounded-xl shadow-lg transition-all transform hover:scale-105">
                <i class="fas fa-save mr-2"></i> Salvar Alterações
            </button>
            <a href="?route=stock/addItems&id=<?php echo $entry['id']; ?>"
                class="bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-bold py-3 px-10 rounded-xl transition-all flex items-center gap-2">
                <i class="fas fa-plus-circle"></i> Adicionar itens
            </a>
            <a href="?route=stock/view&id=<?php echo $entry['id']; ?>"
                class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-3 px-10 rounded-xl transition-all">
                Cancelar
            </a>
        </div>
    </form>
</div>

<?php require 'views/layouts/footer.php'; ?>