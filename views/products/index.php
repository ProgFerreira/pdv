<?php require 'views/layouts/header.php'; ?>

<div class="w-full max-w-full flex flex-col gap-6 px-4 sm:px-6 lg:px-8 mx-auto">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">📦 Produtos</h2>
        <a href="<?php echo BASE_URL; ?>?route=product/create"
            class="btn btn-primary rounded-lg shadow-md font-black transition-all active:scale-95 flex items-center gap-2">
            <i class="fas fa-plus"></i> Novo Produto
        </a>
    </div>

    <div class="card-standard">
        <div class="card-standard-header"><i class="fas fa-filter"></i> Filtros</div>
        <div class="card-standard-body">
        <form method="GET" action="<?php echo BASE_URL; ?>"
            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 items-end">
            <input type="hidden" name="route" value="product/index">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Produto</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($filters['name'] ?? ''); ?>"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary p-2 border"
                    placeholder="Buscar por nome...">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                <select name="category_id"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary p-2 border">
                    <option value="">Todas as Categorias</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo (isset($filters['category_id']) && $filters['category_id'] == $c['id']) ? 'selected' : ''; ?>>
                            <?php echo $c['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if (isAdmin()): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Setor</label>
                    <select name="sector_id"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary p-2 border">
                        <option value="all" <?php echo ($filters['sector_id'] === 'all') ? 'selected' : ''; ?>>📍 Todos os
                            Setores
                        </option>
                        <?php foreach ($allSectors as $s): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo ($filters['sector_id'] != 'all' && $filters['sector_id'] == $s['id']) ? 'selected' : ''; ?>>
                                <?php echo $s['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Código</label>
                <input type="text" name="code" value="<?php echo htmlspecialchars($filters['code'] ?? ''); ?>"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary p-2 border"
                    placeholder="Buscar por código...">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">EAN (Cód. Barras)</label>
                <input type="text" name="ean" value="<?php echo htmlspecialchars($filters['ean'] ?? ''); ?>"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary p-2 border"
                    placeholder="EAN...">
            </div>

            <div class="flex gap-2">
                <button type="submit"
                    class="btn bg-indigo-600 hover:bg-indigo-700 text-white border-none rounded-xl shadow-md font-black transition-all active:scale-95">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="?route=product/index"
                    class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2 px-3 rounded border border-gray-300 transition-colors"
                    title="Limpar Filtros">
                    <i class="fas fa-eraser"></i><span>Limpar filtros</span>
                </a>
            </div>
        </form>
        </div>
    </div>

    <!-- Cards de Resumo -->
    <?php
    $totalProducts = count($products);
    $totalWithMargin = 0;
    $totalMargin = 0;
    $avgMargin = 0;
    $highMargin = 0; // >= 30%
    $mediumMargin = 0; // 15% a 29%
    $lowMargin = 0; // 1% a 14%
    $negativeMargin = 0; // < 0%
    $totalEstoqueGeral = 0;
    $totalCustoVendidoGeral = 0;  // custo do que foi vendido (para bater com Total vendido R$)
    $totalLucroVendidoGeral = 0;  // lucro sobre o que foi vendido
    $totalVendidoGeral = 0;

    foreach ($products as $p) {
        // Totais baseados em VENDAS (para os cards Total vendido / Custo / Lucro baterem)
        $totalVendidoGeral += (float)($p['sold_revenue'] ?? 0);
        $totalCustoVendidoGeral += (float)($p['sold_cost'] ?? 0);
        $totalLucroVendidoGeral += (float)($p['sold_revenue'] ?? 0) - (float)($p['sold_cost'] ?? 0);
        // Estoque (apenas para uso interno se precisar)
        $totalEstoqueGeral += $p['price'] * $p['stock'];

        // Calcular margens
        if (!empty($p['cost_price']) && $p['price'] > 0) {
            $margin = (($p['price'] - $p['cost_price']) / $p['price']) * 100;
            $totalMargin += $margin;
            $totalWithMargin++;

            if ($margin >= 30) {
                $highMargin++;
            } elseif ($margin >= 15) {
                $mediumMargin++;
            } elseif ($margin > 0) {
                $lowMargin++;
            } else {
                $negativeMargin++;
            }
        }
    }

    if ($totalWithMargin > 0) {
        $avgMargin = $totalMargin / $totalWithMargin;
    }
    ?>

    <div class="cards-grid-default gap-4">
        <div class="card-standard-metric p-4 border-l-primary">
            <div class="flex items-center justify-between">
                <div>
                    <p class="card-metric-label">Total Produtos</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo $totalProducts; ?></p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-box text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="card-standard-metric p-4 border-l-success">
            <div class="flex items-center justify-between">
                <div>
                    <p class="card-metric-label">Margem Média</p>
                    <p
                        class="text-2xl font-bold <?php echo $avgMargin >= 30 ? 'text-green-600' : ($avgMargin >= 15 ? 'text-yellow-600' : 'text-red-600'); ?> mt-1">
                        <?php echo $avgMargin > 0 ? number_format($avgMargin, 1, ',', '.') . '%' : '-'; ?>
                    </p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-percentage text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="card-standard-metric p-4 border-l-success">
            <div class="flex items-center justify-between">
                <div>
                    <p class="card-metric-label">Alta Margem (≥30%)</p>
                    <p class="text-2xl font-bold text-green-600 mt-1"><?php echo $highMargin; ?></p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-arrow-up text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="card-standard-metric p-4 border-l-warning">
            <div class="flex items-center justify-between">
                <div>
                    <p class="card-metric-label">Média Margem (15-29%)</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1"><?php echo $mediumMargin; ?></p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <i class="fas fa-minus text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="card-standard-metric p-4 border-l-danger">
            <div class="flex items-center justify-between">
                <div>
                    <p class="card-metric-label">Baixa/Negativa</p>
                    <p class="text-2xl font-bold text-red-600 mt-1"><?php echo $lowMargin + $negativeMargin; ?></p>
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <i class="fas fa-arrow-down text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="card-standard-metric p-4 border-l-primary">
            <div class="flex items-center justify-between">
                <div>
                    <p class="card-metric-label">Total vendido R$</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1">R$
                        <?php echo number_format($totalVendidoGeral, 2, ',', '.'); ?>
                    </p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-hand-holding-usd text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="card-standard-metric p-4 border-l-warning">
            <div class="flex items-center justify-between">
                <div>
                    <p class="card-metric-label">Total Custo R$</p>
                    <p class="text-2xl font-bold text-orange-600 mt-1">R$
                        <?php echo number_format($totalCustoVendidoGeral, 2, ',', '.'); ?>
                    </p>
                </div>
                <div class="bg-orange-100 p-3 rounded-full">
                    <i class="fas fa-coins text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="card-standard-metric p-4 border-l-success">
            <div class="flex items-center justify-between">
                <div>
                    <p class="card-metric-label">Lucro Total R$</p>
                    <p class="text-2xl font-bold <?php echo $totalLucroVendidoGeral >= 0 ? 'text-green-600' : 'text-red-600'; ?> mt-1">R$
                        <?php echo number_format($totalLucroVendidoGeral, 2, ',', '.'); ?>
                    </p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-chart-line text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card-standard overflow-hidden">
        <div class="card-standard-header"><i class="fas fa-box"></i> Listagem de Produtos</div>
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200" style="min-width: 100%;">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-14">
                            Img
                        </th>
                        <th scope="col"
                            class="px-1 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
                            ID</th>
                        <th scope="col"
                            class="px-1 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                            Código</th>
                        <th scope="col"
                            class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[180px]">
                            Nome</th>
                        <th scope="col"
                            class="px-1 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-28">
                            Fornecedor</th>
                        <th scope="col"
                            class="px-1 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">
                            Categoria
                        </th>
                        <th scope="col"
                            class="px-1 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                            Marca
                        </th>
                        <th scope="col"
                            class="px-1 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                            Preço
                        </th>
                        <th scope="col"
                            class="px-1 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">
                            Margem
                        </th>
                        <th scope="col"
                            class="px-1 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-18">
                            Estoque
                        </th>
                        <th scope="col"
                            class="px-1 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                            Qtd. vendida
                        </th>
                        <th scope="col"
                            class="px-1 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-22">
                            Total vendido R$
                        </th>
                        <th scope="col"
                            class="px-1 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-22">
                            Custo vendido R$
                        </th>
                        <th scope="col"
                            class="px-1 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-22">
                            Lucro R$
                        </th>
                        <?php if (isAdmin()): ?>
                            <th scope="col"
                                class="px-1 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-18">
                                Setor
                            </th>
                        <?php endif; ?>
                        <th scope="col"
                            class="px-1 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-18">
                            Local
                        </th>
                        <th scope="col"
                            class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[240px]">
                            Ações
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($products as $p): ?>
                        <tr
                            class="<?php echo $p['active'] ? 'hover:bg-gray-50' : 'bg-red-50 text-gray-500'; ?> transition-colors">
                            <td class="px-2 py-3 whitespace-nowrap align-middle">
                                <div class="w-10 h-10 min-w-[2.5rem] min-h-[2.5rem] rounded overflow-hidden bg-gray-100 border border-gray-200 flex items-center justify-center flex-shrink-0">
                                <?php if (!empty($p['image'])): ?>
                                    <?php $imgSrc = (strpos($p['image'], '/') === 0 || strpos($p['image'], 'http') === 0) ? $p['image'] : (BASE_URL . ltrim($p['image'], '/')); ?>
                                    <img src="<?php echo htmlspecialchars($imgSrc, ENT_QUOTES, 'UTF-8'); ?>"
                                        alt="" class="w-full h-full object-cover" style="max-width:40px;max-height:40px;">
                                <?php else: ?>
                                    <i class="fas fa-image text-gray-400 text-sm"></i>
                                <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-1 py-3 whitespace-nowrap text-xs text-gray-500"><?php echo $p['id']; ?></td>
                            <td class="px-1 py-3 whitespace-nowrap text-xs text-gray-600 font-medium">
                                <?php echo htmlspecialchars($p['code'] ?? '-'); ?>
                            </td>
                            <td class="px-2 py-3">
                                <div class="flex flex-col">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($p['name']); ?>
                                    </div>
                                    <?php if ($p['is_consigned']): ?>
                                        <span
                                            class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-black bg-blue-100 text-blue-700 uppercase mt-1 w-fit">
                                            📦 Consignado
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($p['ean'])): ?>
                                    <div class="text-[10px] text-gray-500 flex items-center gap-1 mt-1"><i
                                            class="fas fa-barcode"></i>
                                        <?php echo $p['ean']; ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-1 py-3 whitespace-nowrap text-xs text-gray-500">
                                <?php echo htmlspecialchars($p['supplier_name'] ?? '-'); ?>
                            </td>
                            <td class="px-1 py-3 whitespace-nowrap text-xs text-gray-500">
                                <?php echo $p['category_name']; ?>
                            </td>
                            <td class="px-1 py-3 whitespace-nowrap text-xs text-gray-500">
                                <?php echo $p['brand_name'] ?? '-'; ?>
                            </td>
                            <td class="px-1 py-3 whitespace-nowrap">
                                <div class="text-xs font-bold text-gray-900">R$
                                    <?php echo number_format($p['price'], 2, ',', '.'); ?>
                                </div>
                                <?php if (!empty($p['cost_price'])): ?>
                                    <div class="text-[10px] text-gray-400" title="Custo">C:
                                        <?php echo number_format($p['cost_price'], 2, ',', '.'); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-1 py-3 whitespace-nowrap">
                                <?php
                                $margin = 0;
                                if (!empty($p['cost_price']) && $p['price'] > 0) {
                                    $margin = (($p['price'] - $p['cost_price']) / $p['price']) * 100;
                                }
                                $marginColor = $margin >= 30 ? 'text-green-700 bg-green-100' : ($margin >= 15 ? 'text-yellow-700 bg-yellow-100' : ($margin > 0 ? 'text-orange-700 bg-orange-100' : 'text-red-700 bg-red-100'));
                                ?>
                                <span
                                    class="px-1.5 py-0.5 inline-flex text-xs font-bold rounded-full <?php echo $marginColor; ?>"
                                    title="Margem de Lucro">
                                    <?php echo $margin > 0 ? number_format($margin, 1, ',', '.') . '%' : '-'; ?>
                                </span>
                            </td>
                            <td class="px-1 py-3 whitespace-nowrap">
                                <span
                                    class="px-1.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $p['stock'] < 5 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                                    <?php echo $p['stock']; ?>     <?php echo $p['unit']; ?>
                                </span>
                            </td>
                            <td class="px-1 py-3 whitespace-nowrap text-center text-xs">
                                <span class="px-1.5 py-0.5 inline-flex font-semibold rounded-full bg-indigo-100 text-indigo-800" title="Quantidade vendida (vendas concluídas)">
                                    <?php echo (int)($p['qty_sold'] ?? 0); ?>
                                </span>
                            </td>
                            <td class="px-1 py-3 whitespace-nowrap text-right text-xs font-bold text-gray-900">
                                <?php
                                $totalVendido = (float)($p['sold_revenue'] ?? 0);
                                echo 'R$ ' . number_format($totalVendido, 2, ',', '.');
                                ?>
                            </td>
                            <td class="px-1 py-3 whitespace-nowrap text-right text-xs text-gray-600">
                                <?php
                                $custoVendido = (float)($p['sold_cost'] ?? 0);
                                echo 'R$ ' . number_format($custoVendido, 2, ',', '.');
                                ?>
                            </td>
                            <td class="px-1 py-3 whitespace-nowrap text-right text-xs font-semibold <?php
                                $lucroVendido = (float)($p['sold_revenue'] ?? 0) - (float)($p['sold_cost'] ?? 0);
                                echo $lucroVendido >= 0 ? 'text-green-700' : 'text-red-700';
                            ?>">
                                <?php echo 'R$ ' . number_format($lucroVendido, 2, ',', '.'); ?>
                            </td>
                            <?php if (isAdmin()): ?>
                                <td class="px-1 py-3 whitespace-nowrap text-xs">
                                    <span
                                        class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-gray-100 text-gray-600 uppercase border border-gray-200">
                                        <?php echo htmlspecialchars($p['sector_name'] ?? 'Loja'); ?>
                                    </span>
                                </td>
                            <?php endif; ?>
                            <td class="px-1 py-3 whitespace-nowrap text-xs text-gray-500">
                                <?php echo $p['location'] ?? '-'; ?>
                            </td>
                            <td class="px-3 py-3 whitespace-nowrap text-right align-middle">
                                <div class="flex justify-end gap-2">
                                    <?php if (function_exists('hasPermission') && hasPermission('product')): ?>
                                    <a href="?route=technicalSheet/view&product_id=<?php echo (int)$p['id']; ?>"
                                        class="text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 p-2 rounded-lg transition-colors"
                                        title="Ficha Técnica">
                                        <i class="fas fa-clipboard-list"></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="?route=product/edit&id=<?php echo $p['id']; ?>"
                                        class="text-blue-700 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 border border-blue-200 p-2 rounded-lg transition-colors"
                                        title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?route=product/toggle&id=<?php echo $p['id']; ?>"
                                        class="text-amber-700 hover:text-amber-900 bg-amber-50 hover:bg-amber-100 border border-amber-200 p-2 rounded-lg transition-colors"
                                        title="<?php echo $p['active'] ? 'Desativar' : 'Ativar'; ?>">
                                        <i class="fas fa-power-off"></i>
                                    </a>
                                    <a href="?route=product/delete&id=<?php echo $p['id']; ?>"
                                        class="text-red-700 hover:text-red-900 bg-red-50 hover:bg-red-100 border border-red-200 p-2 rounded-lg transition-colors"
                                        title="Excluir" onclick="return confirm('Tem certeza que deseja excluir?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>