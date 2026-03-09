<?php require 'views/layouts/header.php'; ?>

<div class="w-full max-w-full flex flex-col gap-6 px-4 sm:px-6 lg:px-8 mx-auto">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">🥗 Insumos (Ficha Técnica)</h2>
        <a href="<?php echo BASE_URL; ?>?route=ingredient/create"
            class="btn btn-primary rounded-lg shadow-md font-black transition-all active:scale-95 flex items-center gap-2">
            <i class="fas fa-plus"></i> Novo Insumo
        </a>
    </div>

    <?php $flash = get_flash(); if ($flash): ?>
        <div class="rounded-lg px-4 py-3 <?php echo $flash['type'] === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'; ?>">
            <?php echo e($flash['message']); ?>
        </div>
    <?php endif; ?>

    <div class="card-standard overflow-hidden">
        <div class="card-standard-header"><i class="fas fa-list"></i> Listagem de Insumos</div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unidade</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Custo/un.</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ativo</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($ingredients as $i): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-600"><?php echo e($i['code']); ?></td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900"><?php echo e($i['name']); ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600"><?php echo e($i['unit']); ?></td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900"><?php echo money($i['cost_per_unit'] ?? 0); ?></td>
                            <td class="px-4 py-3 text-center">
                                <?php if ($i['active'] ?? 1): ?>
                                    <span class="text-green-600 text-sm">Sim</span>
                                <?php else: ?>
                                    <span class="text-gray-400 text-sm">Não</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="<?php echo BASE_URL; ?>?route=ingredient/edit&id=<?php echo (int)$i['id']; ?>"
                                    class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 mr-3 text-sm" title="Editar"><i class="fas fa-edit"></i><span>Editar</span></a>
                                <a href="<?php echo BASE_URL; ?>?route=ingredient/toggle&id=<?php echo (int)$i['id']; ?>"
                                    class="inline-flex items-center gap-1 text-gray-600 hover:text-gray-800 mr-3 text-sm" title="Ativar/Desativar"><i class="fas fa-toggle-on"></i><span>Ativar/Desativar</span></a>
                                <a href="<?php echo BASE_URL; ?>?route=ingredient/delete&id=<?php echo (int)$i['id']; ?>"
                                    class="inline-flex items-center gap-1 text-red-600 hover:text-red-800 text-sm"
                                    onclick="return confirm('Excluir este insumo?');" title="Excluir"><i class="fas fa-trash"></i><span>Excluir</span></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (empty($ingredients)): ?>
            <div class="p-6 text-center text-gray-500">Nenhum insumo cadastrado. Cadastre insumos para montar fichas técnicas.</div>
        <?php endif; ?>
    </div>

    <div class="flex gap-2">
        <a href="<?php echo BASE_URL; ?>?route=product/index" class="btn bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg">
            <i class="fas fa-box mr-2"></i> Voltar aos Produtos
        </a>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>
