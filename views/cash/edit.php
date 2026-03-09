<?php require 'views/layouts/header.php'; ?>

<div class="mb-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-gray-800 uppercase tracking-tight">✏️ Editar Caixa #<?php echo $register['id']; ?></h2>
        <a href="?route=cash/history" class="text-gray-600 hover:text-gray-800 bg-gray-100 px-4 py-2 rounded-lg transition-colors">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<?php if (isset($_GET['error'])): ?>
    <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded shadow-sm mb-6 flex items-center gap-3">
        <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
        <span>
            <?php
            if ($_GET['error'] == 'update_failed')
                echo "Erro ao atualizar o caixa. Verifique os dados e tente novamente.";
            else
                echo "Ocorreu um erro ao processar sua solicitação.";
            ?>
        </span>
    </div>
<?php endif; ?>

<form method="POST" action="?route=cash/update" class="bg-white shadow-lg rounded-lg p-6 border border-gray-200 max-w-2xl">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="id" value="<?php echo (int) $register['id']; ?>">

    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Saldo Inicial (Abertura)</label>
        <div class="relative rounded-md shadow-sm">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <span class="text-gray-500 sm:text-sm">R$</span>
            </div>
            <input type="text" name="opening_balance" required
                class="block w-full rounded-md border-gray-300 pl-10 focus:border-primary focus:ring-primary sm:text-lg py-3"
                placeholder="0,00" 
                value="<?php echo number_format($register['opening_balance'], 2, ',', ''); ?>">
        </div>
    </div>

    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Saldo Final (Fechamento)</label>
        <div class="relative rounded-md shadow-sm">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <span class="text-gray-500 sm:text-sm">R$</span>
            </div>
            <input type="text" name="closing_balance"
                class="block w-full rounded-md border-gray-300 pl-10 focus:border-primary focus:ring-primary sm:text-lg py-3"
                placeholder="0,00" 
                value="<?php echo $register['closing_balance'] ? number_format($register['closing_balance'], 2, ',', '') : ''; ?>">
        </div>
        <p class="text-xs text-gray-500 mt-1">Deixe em branco se o caixa ainda não foi fechado.</p>
    </div>

    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
        <textarea name="notes" rows="4"
            class="w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"><?php echo htmlspecialchars($register['notes'] ?? ''); ?></textarea>
    </div>

    <div class="flex gap-3">
        <a href="?route=cash/history"
            class="w-full bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 rounded-lg text-center transition-colors">
            Cancelar
        </a>
        <button type="submit"
            class="w-full bg-primary hover:bg-primary-hover text-white font-bold py-3 rounded-lg transition-colors">
            <i class="fas fa-save mr-2"></i> Salvar Alterações
        </button>
    </div>
</form>

<?php require 'views/layouts/footer.php'; ?>
