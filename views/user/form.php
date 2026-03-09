<?php require 'views/layouts/header.php'; ?>

<div class="w-[95vw] max-w-full mx-auto">
    <div class="card-standard overflow-hidden">
        <div class="card-standard-header">
            <i class="fas fa-user-cog"></i>
            <?php echo isset($user) ? 'Editar Usuário' : 'Novo Usuário'; ?>
        </div>
        <div class="card-standard-body">
            <form method="POST">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div class="lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo</label>
                        <input type="text" name="name" required
                            value="<?php echo isset($user) ? htmlspecialchars($user['name']) : ''; ?>"
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50">
                    </div>
                    <div class="lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome de Usuário (Login)</label>
                        <input type="text" name="username" required
                            value="<?php echo isset($user) ? htmlspecialchars($user['username']) : ''; ?>"
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50">
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Senha
                            <?php echo isset($user) ? '(Deixe em branco para manter)' : ''; ?>
                        </label>
                        <input type="password" name="password" <?php echo isset($user) ? '' : 'required'; ?>
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50">
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cargo / Função (grupo)</label>
                        <select name="role" id="user_role" onchange="toggleSectorField()"
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white">
                            <option value="cashier" <?php echo (isset($user) && $user['role'] == 'cashier') ? 'selected' : ''; ?>>Caixa</option>
                            <option value="admin" <?php echo (isset($user) && $user['role'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                        </select>
                        <?php if (hasPermission('permission_manage')): ?>
                            <p class="text-xs text-gray-500 mt-1">As telas permitidas dependem do grupo. Configure em
                                <a href="<?php echo BASE_URL; ?>?route=permission/index" class="text-primary hover:underline">Permissões por grupo</a>.
                            </p>
                        <?php endif; ?>
                    </div>

                    <div id="sector_container" class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Setor Responsável</label>
                        <select name="sector_id"
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white">
                            <option value="">-- Administrador Global --</option>
                            <?php foreach ($sectors as $s): ?>
                                <option value="<?php echo $s['id']; ?>" <?php echo (isset($user) && $user['sector_id'] == $s['id']) ? 'selected' : ''; ?>>
                                    <?php echo $s['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Geralmente usado para limitar o acesso de Caixas apenas ao seu setor.</p>
                    </div>

                    <div class="flex items-center lg:col-span-6">
                        <input type="checkbox" name="active" id="active" value="1" <?php echo (!isset($user) || $user['active']) ? 'checked' : ''; ?> class="border-gray-300 text-primary focus:ring-primary h-4 w-4">
                        <label for="active" class="ml-2 block text-sm text-gray-700">Usuário Ativo</label>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-200 flex flex-wrap items-center gap-3">
                    <button type="submit"
                        class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white font-bold py-2.5 px-6 border-0 cursor-pointer">
                        <i class="fas fa-save"></i> Salvar
                    </button>
                    <a href="?route=user/index"
                        class="inline-flex items-center gap-2 bg-gray-500 hover:bg-gray-600 text-white font-bold py-2.5 px-6 no-underline">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleSectorField() {
        const role = document.getElementById('user_role').value;
        const sectorContainer = document.getElementById('sector_container');
    }
</script>

<?php require 'views/layouts/footer.php'; ?>
