<?php require 'views/layouts/header.php'; ?>

<div class="flex justify-between items-center mb-6 flex-wrap gap-4">
    <h2 class="text-2xl font-bold text-gray-800">👥 Gestão de Usuários</h2>
    <div class="flex gap-3">
        <?php if (hasPermission('permission_manage')): ?>
            <a href="<?php echo BASE_URL; ?>?route=permission/index"
                class="bg-amber-100 hover:bg-amber-200 text-amber-800 font-bold py-2 px-4 rounded shadow flex items-center gap-2 transition-colors">
                <i class="fas fa-key"></i> Permissões por grupo
            </a>
        <?php endif; ?>
        <a href="<?php echo BASE_URL; ?>?route=user/create"
            class="bg-primary hover:bg-primary-hover text-white font-bold py-2 px-4 rounded shadow flex items-center gap-2 transition-colors">
            <i class="fas fa-user-plus"></i> Novo Usuário
        </a>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
        <span class="block sm:inline">Operação realizada com sucesso!</span>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error']) && $_GET['error'] == 'cannot_delete_self'): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
        <span class="block sm:inline">Você não pode excluir seu próprio usuário.</span>
    </div>
<?php endif; ?>

<div class="card-standard overflow-hidden">
    <div class="card-standard-header"><i class="fas fa-user-friends"></i> Listagem de Usuários</div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuário</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cargo</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($users as $u): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <?php echo htmlspecialchars($u['name']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo htmlspecialchars($u['username']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php if ($u['role'] == 'admin'): ?>
                                <span
                                    class="bg-purple-100 text-purple-800 text-xs font-bold px-2 py-1 rounded-full">Administrador</span>
                            <?php else: ?>
                                <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2 py-1 rounded-full">Caixa</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <?php if ($u['active']): ?>
                                <span class="text-green-500"><i class="fas fa-check-circle"></i> Ativo</span>
                            <?php else: ?>
                                <span class="text-red-500"><i class="fas fa-times-circle"></i> Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <div class="flex justify-center gap-2">
                                <a href="?route=user/edit&id=<?php echo $u['id']; ?>"
                                    class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-900 px-2 py-1 rounded text-sm"><i class="fas fa-edit"></i><span>Editar</span></a>
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                    <a href="?route=user/delete&id=<?php echo $u['id']; ?>"
                                        class="inline-flex items-center gap-1 text-red-600 hover:text-red-900 px-2 py-1 rounded text-sm"
                                        onclick="return confirm('Tem certeza que deseja excluir este usuário?')"><i class="fas fa-trash"></i><span>Excluir</span></a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>