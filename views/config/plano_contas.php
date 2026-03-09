<?php require 'views/layouts/header.php'; ?>

<div class="flex flex-col gap-6" x-data="{ showModal: false }">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-gray-800">Plano de Contas</h1>
            <p class="text-sm text-gray-400">Estrutura de categorias para classificação de receitas e despesas.</p>
        </div>
        <button class="btn btn-primary btn-sm rounded-xl shadow-sm" @click="showModal = true">
            <i class="fas fa-plus mr-2"></i> Nova Categoria
        </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table table-compact w-full">
                <thead>
                    <tr class="text-[10px] text-gray-400 uppercase tracking-widest bg-gray-50/50">
                        <th class="py-4">Nome</th>
                        <th>Tipo</th>
                        <th>Superior</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach ($categories as $cat): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-4 px-6 text-sm font-bold text-gray-700">
                                <?php echo $cat['nome']; ?>
                            </td>
                            <td>
                                <span class="badge badge-sm text-[10px] uppercase font-bold text-white border-none
                                    <?php echo match ($cat['tipo']) {
                                        'RECEITA' => 'bg-green-500',
                                        'CUSTO' => 'bg-orange-500',
                                        'DESPESA' => 'bg-red-500',
                                        'INVESTIMENTO' => 'bg-blue-500',
                                        default => 'bg-gray-500'
                                    }; ?>">
                                    <?php echo $cat['tipo']; ?>
                                </span>
                            </td>
                            <td class="text-xs text-gray-400 italic">
                                <?php
                                if ($cat['pai_id']) {
                                    $parent = array_filter($categories, fn($c) => $c['id'] == $cat['pai_id']);
                                    echo reset($parent)['nome'] ?? '-';
                                } else {
                                    echo 'Nível Raiz';
                                }
                                ?>
                            </td>
                            <td>
                                <span
                                    class="badge <?php echo $cat['ativo'] ? 'badge-success' : 'badge-ghost'; ?> badge-xs"></span>
                                <span class="text-[10px] font-medium ml-1">
                                    <?php echo $cat['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                </span>
                            </td>
                            <td class="text-right">
                                <button type="button" class="inline-flex items-center gap-1 btn btn-ghost btn-xs text-gray-300 hover:text-primary" title="Editar"><i class="fas fa-edit"></i><span>Editar</span></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal" :class="{ 'modal-open': showModal }">
    <div class="modal-box rounded-3xl p-8 relative">
        <button class="btn btn-sm btn-circle btn-ghost absolute right-4 top-4" @click="showModal = false">✕</button>
        <h3 class="text-xl font-black text-gray-800 mb-6">Nova Categoria</h3>
        <form action="?route=planoContas/store" method="POST" class="flex flex-col gap-4">
            <?php echo csrf_field(); ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="form-control lg:col-span-2">
                    <label class="label p-1"><span class="label-text font-bold text-gray-500 text-xs">Tipo</span></label>
                    <select name="tipo" required class="w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 bg-white">
                        <option value="RECEITA">RECEITA</option>
                        <option value="CUSTO">CUSTO (CMV)</option>
                        <option value="DESPESA">DESPESA</option>
                        <option value="INVESTIMENTO">INVESTIMENTO</option>
                        <option value="FINANCEIRO">FINANCEIRO</option>
                        <option value="IMPOSTO">IMPOSTO</option>
                    </select>
                </div>
                <div class="form-control lg:col-span-2">
                    <label class="label p-1"><span class="label-text font-bold text-gray-500 text-xs">Nome da Categoria</span></label>
                    <input type="text" name="nome" required
                        class="w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                </div>
                <div class="form-control lg:col-span-2">
                    <label class="label p-1"><span class="label-text font-bold text-gray-500 text-xs">Categoria Superior (Opcional)</span></label>
                    <select name="pai_id" class="w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 bg-white">
                        <option value="">Nenhuma</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>">
                                <?php echo $cat['nome']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-control">
                <label class="label cursor-pointer justify-start gap-4">
                    <input type="checkbox" name="ativo" checked class="checkbox checkbox-primary">
                    <span class="label-text font-bold text-gray-500 text-xs">Categoria Ativa</span>
                </label>
            </div>
            <button type="submit" class="btn btn-primary rounded-xl mt-4 shadow-md font-black">Adicionar
                Categoria</button>
        </form>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>