<?php require 'views/layouts/header.php'; ?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">📁 Categorias</h2>
    <a href="<?php echo BASE_URL; ?>?route=category/create"
        class="btn btn-success inline-flex items-center gap-2 no-underline">
        <i class="fas fa-plus"></i> Nova Categoria
    </a>
</div>

<?php if (isset($_GET['error']) && $_GET['error'] === 'constraint'): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        Não é possível excluir esta categoria pois existem produtos vinculados a ela.
    </div>
<?php endif; ?>

<div class="card-standard overflow-hidden">
    <div class="card-standard-header"><i class="fas fa-folder"></i> Listagem de Categorias</div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                    <th scope="col"
                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($categories as $c): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $c['id']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($c['name']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <a href="?route=category/edit&id=<?php echo $c['id']; ?>"
                                    class="inline-flex items-center gap-1 btn btn-outline-primary btn-sm px-2 py-1.5 no-underline"
                                    title="Editar">
                                    <i class="fas fa-edit"></i><span>Editar</span>
                                </a>
                                <a href="?route=category/delete&id=<?php echo $c['id']; ?>"
                                    class="inline-flex items-center gap-1 btn btn-outline-danger btn-sm px-2 py-1.5 no-underline"
                                    title="Excluir" onclick="return confirm('Tem certeza que deseja excluir?');">
                                    <i class="fas fa-trash"></i><span>Excluir</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>