<?php require 'views/layouts/header.php'; ?>

<div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">🚚 Gestão de Fornecedores</h2>
        <p class="text-sm text-gray-500">Cadastre e gerencie seus parceiros e fornecedores de produtos.</p>
    </div>
    <a href="?route=supplier/create"
        class="bg-primary hover:bg-primary-hover text-white font-bold py-2.5 px-6 rounded-lg shadow-md flex items-center gap-2 transition-all transform hover:scale-105 border-0">
        <i class="fas fa-plus"></i> Novo Fornecedor
    </a>
</div>

<?php if (isset($_GET['success'])): ?>
    <div
        class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 rounded shadow-sm mb-8 flex items-center gap-3">
        <i class="fas fa-check-circle text-emerald-500 text-xl"></i>
        <span>
            <?php
            if ($_GET['success'] == 'created')
                echo "Fornecedor cadastrado com sucesso!";
            if ($_GET['success'] == 'updated')
                echo "Cadastro atualizado com sucesso.";
            if ($_GET['success'] == 'deleted')
                echo "Fornecedor removido permanentemente.";
            ?>
        </span>
    </div>
<?php endif; ?>

<div class="card-standard overflow-hidden">
    <div class="card-standard-header"><i class="fas fa-truck"></i> Listagem de Fornecedores</div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Nome
                        / Empresa</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Contato</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Telefone</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        E-mail</th>
                    <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($suppliers)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400 italic">
                            Nenhum fornecedor cadastrado até o momento.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($suppliers as $s): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                <?php echo htmlspecialchars($s['name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($s['contact_person'] ?: '-'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                                <?php echo htmlspecialchars($s['phone'] ?: '-'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($s['email'] ?: '-'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-2 text-base">
                                    <a href="?route=supplier/edit&id=<?php echo $s['id']; ?>"
                                        class="inline-flex items-center gap-1 text-amber-600 hover:text-amber-800 bg-amber-50 px-2 py-1.5 rounded-lg transition-colors text-sm"
                                        title="Editar">
                                        <i class="fas fa-edit"></i><span>Editar</span>
                                    </a>
                                    <a href="?route=supplier/delete&id=<?php echo $s['id']; ?>"
                                        onclick="return confirm('Deseja excluir este fornecedor? Produtos vinculados a ele ficarão sem fornecedor.')"
                                        class="inline-flex items-center gap-1 text-red-500 hover:text-red-700 bg-red-50 px-2 py-1.5 rounded-lg transition-colors text-sm"
                                        title="Excluir">
                                        <i class="fas fa-trash"></i><span>Excluir</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>