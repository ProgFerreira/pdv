<?php require 'views/layouts/header.php'; ?>

<div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 uppercase tracking-tight">🏆 Ranking de Mais Vendidos</h2>
        <p class="text-sm text-gray-500">Descubra os produtos campeões de saída e faturamento.</p>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <form class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="route" value="report/best_sellers">

            <div class="w-40">
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">🏷️ Categoria</label>
                <select name="category_id" class="w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 bg-white text-sm">
                    <option value="">Todas</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo (($_GET['category_id'] ?? '') == $c['id']) ? 'selected' : ''; ?>>
                            <?php echo $c['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="w-40">
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">🏢 Marca</label>
                <select name="brand_id" class="w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 bg-white text-sm">
                    <option value="">Todas</option>
                    <?php foreach ($brands as $b): ?>
                        <option value="<?php echo $b['id']; ?>" <?php echo (($_GET['brand_id'] ?? '') == $b['id']) ? 'selected' : ''; ?>>
                            <?php echo $b['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">📅 De</label>
                <input type="date" name="start_date" class="rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 text-sm"
                    value="<?php echo $startDate; ?>">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">📅 Até</label>
                <input type="date" name="end_date" class="rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 text-sm"
                    value="<?php echo $endDate; ?>">
            </div>

            <button type="submit"
                class="btn btn-primary rounded-lg shadow-md font-black transition-all active:scale-95">
                <i class="fas fa-filter"></i> Filtrar
            </button>
            <a href="?route=report/best_sellers"
                class="btn btn-ghost bg-gray-50 hover:bg-gray-100 text-gray-400 rounded-lg border border-gray-200 transition-colors">
                <i class="fas fa-eraser"></i>
            </a>
        </form>
    </div>
</div>

<?php if (!empty($products)): ?>
    <!-- Podium Top 3 -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 items-end mb-12">
        <!-- 2nd Place -->
        <?php if (isset($products[1])): ?>
            <div
                class="order-2 md:order-1 bg-white p-6 rounded-2xl shadow-md border-t-8 border-gray-300 text-center transform hover:scale-105 transition-transform">
                <div class="relative inline-block mb-4">
                    <img src="<?php echo $products[1]['image'] ?: 'public/images/no-product.png'; ?>"
                        class="w-20 h-20 rounded-full object-cover border-4 border-gray-100 mx-auto shadow-sm">
                    <span
                        class="absolute -top-2 -right-2 bg-gray-400 text-white text-xs font-black w-8 h-8 flex items-center justify-center rounded-full border-2 border-white shadow-sm">2º</span>
                </div>
                <h3 class="text-sm font-black text-gray-800 uppercase line-clamp-1">
                    <?php echo htmlspecialchars($products[1]['name']); ?>
                </h3>
                <p class="text-2xl font-black text-primary mt-1">
                    <?php echo number_format($products[1]['total_qty'], 0); ?> <small
                        class="text-[10px] font-normal opacity-60">unids</small>
                </p>
                <div class="mt-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest bg-gray-50 py-1 rounded">R$
                    <?php echo number_format($products[1]['total_revenue'], 2, ',', '.'); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- 1st Place -->
        <?php if (isset($products[0])): ?>
            <div
                class="order-1 md:order-2 bg-white p-8 rounded-2xl shadow-xl border-t-8 border-yellow-400 text-center transform scale-110 z-10">
                <div class="relative inline-block mb-4">
                    <img src="<?php echo $products[0]['image'] ?: 'public/images/no-product.png'; ?>"
                        class="w-24 h-24 rounded-full object-cover border-4 border-yellow-100 mx-auto shadow-sm">
                    <span
                        class="absolute -top-2 -right-2 bg-yellow-400 text-white text-base font-black w-10 h-10 flex items-center justify-center rounded-full border-2 border-white shadow-md animate-bounce">1º</span>
                </div>
                <h3 class="text-base font-black text-gray-900 uppercase line-clamp-2 leading-tight">
                    <?php echo htmlspecialchars($products[0]['name']); ?>
                </h3>
                <p class="text-3xl font-black text-yellow-600 mt-2">
                    <?php echo number_format($products[0]['total_qty'], 0); ?> <small
                        class="text-xs font-normal opacity-60 uppercase">Vendas</small>
                </p>
                <div
                    class="mt-6 text-xs font-black text-gray-500 uppercase tracking-widest bg-yellow-50 py-2 rounded-full border border-yellow-100">
                    Faturamento: R$
                    <?php echo number_format($products[0]['total_revenue'], 2, ',', '.'); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- 3rd Place -->
        <?php if (isset($products[2])): ?>
            <div
                class="order-3 bg-white p-6 rounded-2xl shadow-md border-t-8 border-orange-300 text-center transform hover:scale-105 transition-transform">
                <div class="relative inline-block mb-4">
                    <img src="<?php echo $products[2]['image'] ?: 'public/images/no-product.png'; ?>"
                        class="w-20 h-20 rounded-full object-cover border-4 border-gray-100 mx-auto shadow-sm">
                    <span
                        class="absolute -top-2 -right-2 bg-orange-400 text-white text-xs font-black w-8 h-8 flex items-center justify-center rounded-full border-2 border-white shadow-sm">3º</span>
                </div>
                <h3 class="text-sm font-black text-gray-800 uppercase line-clamp-1">
                    <?php echo htmlspecialchars($products[2]['name']); ?>
                </h3>
                <p class="text-2xl font-black text-primary mt-1">
                    <?php echo number_format($products[2]['total_qty'], 0); ?> <small
                        class="text-[10px] font-normal opacity-60">unids</small>
                </p>
                <div class="mt-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest bg-gray-50 py-1 rounded">R$
                    <?php echo number_format($products[2]['total_revenue'], 2, ',', '.'); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Full Ranking Table -->
    <div class="bg-white shadow-md rounded-xl overflow-hidden border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 text-[10px] uppercase font-bold text-gray-500 tracking-widest">
                    <tr>
                        <th class="px-6 py-4 text-center w-16">Pos</th>
                        <th class="px-6 py-4 text-left">Produto</th>
                        <th class="px-6 py-4 text-right">Qtd Vendida</th>
                        <th class="px-6 py-4 text-right">Média Valor</th>
                        <th class="px-6 py-4 text-right">Total Faturado</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php foreach ($products as $index => $p): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-xs font-black text-gray-400">#
                                    <?php echo $index + 1; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <img src="<?php echo $p['image'] ?: 'public/images/no-product.png'; ?>"
                                        class="w-8 h-8 rounded border border-gray-200 object-cover">
                                    <span class="text-sm font-bold text-gray-800">
                                        <?php echo htmlspecialchars($p['name']); ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-black text-primary">
                                    <?php echo number_format($p['total_qty'], 0); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-400 italic">
                                R$
                                <?php echo number_format($p['avg_price'], 2, ',', '.'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-700">
                                R$
                                <?php echo number_format($p['total_revenue'], 2, ',', '.'); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="bg-white p-12 rounded-2xl shadow-sm border border-dashed border-gray-300 text-center">
        <i class="fas fa-ghost text-4xl text-gray-200 mb-4"></i>
        <h3 class="text-lg font-bold text-gray-400 uppercase tracking-widest">Nenhuma venda encontrada</h3>
        <p class="text-sm text-gray-400 mt-1">Tente ajustar os filtros para ver outros períodos.</p>
    </div>
<?php endif; ?>

<?php require 'views/layouts/footer.php'; ?>