<?php require 'views/layouts/header.php'; ?>

<div class="w-full max-w-full flex flex-col gap-6 px-4 sm:px-6 lg:px-8 mx-auto">
    <div class="flex justify-between items-center flex-wrap gap-2">
        <div>
            <a href="<?php echo BASE_URL; ?>?route=product/index" class="text-sm text-gray-600 hover:text-gray-800 mb-1 inline-block">
                <i class="fas fa-arrow-left mr-1"></i> Voltar aos Produtos
            </a>
            <h2 class="text-2xl font-bold text-gray-800">📋 Ficha Técnica: <?php echo e($product['name']); ?></h2>
        </div>
        <a href="<?php echo BASE_URL; ?>?route=product/edit&id=<?php echo (int)$product['id']; ?>"
            class="btn bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg no-underline">
            <i class="fas fa-box mr-2"></i> Editar Produto
        </a>
    </div>

    <?php $flash = get_flash(); if ($flash): ?>
        <div class="rounded-lg px-4 py-3 <?php echo $flash['type'] === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'; ?>">
            <?php echo e($flash['message']); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="card-standard">
                <div class="card-standard-header">Dados do produto</div>
                <div class="card-standard-body grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500">Porção final (g):</span> <?php echo (int)($product['yield_target_grams'] ?? 0) ?: '—'; ?></div>
                    <div><span class="text-gray-500">Margem (%):</span> <?php echo number_format((float)($marginPercent ?? 65), 1, ',', ''); ?>%</div>
                </div>
            </div>

            <?php if (!empty($sheet['notes'])): ?>
                <div class="card-standard">
                    <div class="card-standard-header">Anotações</div>
                    <div class="card-standard-body text-sm text-gray-700"><?php echo nl2br(e($sheet['notes'])); ?></div>
                </div>
            <?php endif; ?>

            <div class="card-standard overflow-hidden">
                <div class="card-standard-header flex justify-between items-center flex-wrap gap-2">
                    <span><i class="fas fa-list mr-2"></i>Itens da ficha</span>
                    <a href="<?php echo BASE_URL; ?>?route=technicalSheet/itemForm&product_id=<?php echo (int)$product['id']; ?>&sheet_id=<?php echo (int)$sheet['id']; ?>"
                        class="btn btn-primary btn-sm rounded-lg no-underline">
                        <i class="fas fa-plus mr-1"></i> Adicionar item
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Insumo</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Classif.</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Bruto</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Líquido</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Rend.%</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Custo un.</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Custo total</th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($items as $row): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 text-sm font-medium text-gray-900"><?php echo e($row['ingredient_name']); ?></td>
                                    <td class="px-3 py-2 text-sm text-gray-600"><?php echo e($row['item_classification'] ?? '—'); ?></td>
                                    <td class="px-3 py-2 text-sm text-right text-gray-700"><?php echo number_format((float)$row['item_qty_brut'], 2, ',', '.'); ?> <?php echo e($row['item_unit']); ?></td>
                                    <td class="px-3 py-2 text-sm text-right text-gray-700"><?php echo $row['item_qty_net'] !== null ? number_format((float)$row['item_qty_net'], 2, ',', '.') : '—'; ?></td>
                                    <td class="px-3 py-2 text-sm text-right text-gray-700"><?php echo $row['item_yield_percent'] !== null ? number_format((float)$row['item_yield_percent'], 1, ',', '') . '%' : '—'; ?></td>
                                    <td class="px-3 py-2 text-sm text-right text-gray-700"><?php echo money($row['item_cost_per_unit'] ?? 0); ?></td>
                                    <td class="px-3 py-2 text-sm text-right font-medium text-gray-900"><?php echo money($row['item_total_cost'] ?? 0); ?></td>
                                    <td class="px-3 py-2 text-center">
                                        <a href="<?php echo BASE_URL; ?>?route=technicalSheet/itemEdit&product_id=<?php echo (int)$product['id']; ?>&sheet_id=<?php echo (int)$sheet['id']; ?>&item_id=<?php echo (int)$row['id']; ?>"
                                            class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 text-sm mr-2" title="Editar"><i class="fas fa-edit"></i><span>Editar</span></a>
                                        <a href="<?php echo BASE_URL; ?>?route=technicalSheet/itemDelete&item_id=<?php echo (int)$row['id']; ?>&product_id=<?php echo (int)$product['id']; ?>"
                                            class="inline-flex items-center gap-1 text-red-600 hover:text-red-800 text-sm"
                                            onclick="return confirm('Remover este item da ficha?');" title="Remover"><i class="fas fa-trash"></i><span>Remover</span></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (empty($items)): ?>
                    <div class="p-6 text-center text-gray-500">Nenhum item na ficha. Clique em &quot;Adicionar item&quot; para montar a receita.</div>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <div class="card-standard sticky top-4">
                <div class="card-standard-header">Totais e preço sugerido</div>
                <div class="card-standard-body space-y-4">
                    <div>
                        <span class="text-gray-600 text-sm">Custo total da ficha</span>
                        <div class="text-xl font-bold text-gray-900"><?php echo money($totalCost); ?></div>
                    </div>
                    <div>
                        <span class="text-gray-600 text-sm">Margem bruta</span>
                        <div class="text-lg font-semibold text-gray-800"><?php echo number_format((float)$marginPercent, 1, ',', ''); ?>%</div>
                    </div>
                    <div class="pt-3 border-t border-gray-200">
                        <span class="text-gray-600 text-sm">Preço sugerido (venda)</span>
                        <div class="text-2xl font-bold text-green-700"><?php echo money($suggestedPrice); ?></div>
                        <p class="text-xs text-gray-500 mt-1">Baseado no custo total e na margem do produto. Você pode usar este valor no cadastro do produto.</p>
                    </div>
                    <?php
                    $totalProfit = (float)$suggestedPrice - (float)$totalCost;
                    $profitPercent = (float)$suggestedPrice > 0 ? round(($totalProfit / (float)$suggestedPrice) * 100, 1) : 0;
                    ?>
                    <div class="pt-3 border-t border-gray-200">
                        <span class="text-gray-600 text-sm">Lucro total</span>
                        <div class="text-xl font-bold <?php echo $totalProfit >= 0 ? 'text-green-700' : 'text-red-600'; ?>">
                            <?php echo money($totalProfit); ?>
                            <span class="text-base font-semibold text-gray-600">(<?php echo number_format($profitPercent, 1, ',', ''); ?>%)</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Diferença entre preço sugerido e custo total da ficha.</p>
                    </div>
                </div>
            </div>

            <form method="POST" action="<?php echo BASE_URL; ?>?route=technicalSheet/updateNotes" class="mt-4 card-standard">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="sheet_id" value="<?php echo (int)$sheet['id']; ?>">
                <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                <div class="card-standard-header">Anotações da ficha</div>
                <div class="card-standard-body">
                    <textarea name="notes" rows="3" class="w-full rounded-md border border-gray-300 p-2 text-sm"><?php echo e($sheet['notes'] ?? ''); ?></textarea>
                    <button type="submit" class="mt-2 btn btn-outline-primary btn-sm">Salvar anotações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>
