<?php require 'views/layouts/header.php'; ?>

<div class="flex flex-col gap-6">
<div class="flex flex-col md:flex-row justify-between items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 uppercase tracking-tight">📊 Curva ABC de Produtos</h2>
        <p class="text-sm text-gray-500">Análise de Pareto: Quais itens trazem maior faturamento?</p>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <form class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 items-end">
            <input type="hidden" name="route" value="report/abc_curve">

            <div class="flex-1 min-w-[200px]">
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">🔍 Produto</label>
                <input type="text" name="product_query" placeholder="Nome do produto..."
                    value="<?php echo $_GET['product_query'] ?? ''; ?>"
                    class="w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 text-sm">
            </div>

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
            <a href="?route=report/abc_curve"
                class="btn btn-ghost bg-gray-50 hover:bg-gray-100 text-gray-400 rounded-lg border border-gray-200 transition-colors">
                <i class="fas fa-eraser"></i>
            </a>
        </form>
    </div>
</div>

<!-- Resumo da Curva -->
<div class="cards-grid-default gap-6">
    <div class="bg-white p-6 rounded-xl border-l-4 border-green-500 shadow-sm">
        <h6 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Classe A (80%)</h6>
        <p class="text-2xl font-black text-gray-800">
            <?php echo count(array_filter($products, fn($p) => $p['class'] === 'A')); ?> Itens
        </p>
        <p class="text-[10px] text-gray-400 mt-1 italic">Itens fundamentais que geram a maior parte da receita.</p>
    </div>
    <div class="bg-white p-6 rounded-xl border-l-4 border-blue-500 shadow-sm">
        <h6 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Classe B (15%)</h6>
        <p class="text-2xl font-black text-gray-800">
            <?php echo count(array_filter($products, fn($p) => $p['class'] === 'B')); ?> Itens
        </p>
        <p class="text-[10px] text-gray-400 mt-1 italic">Itens intermediários com demanda e valor médios.</p>
    </div>
    <div class="bg-white p-6 rounded-xl border-l-4 border-orange-400 shadow-sm">
        <h6 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Classe C (5%)</h6>
        <p class="text-2xl font-black text-gray-800">
            <?php echo count(array_filter($products, fn($p) => $p['class'] === 'C')); ?> Itens
        </p>
        <p class="text-[10px] text-gray-400 mt-1 italic">Grande quantidade de itens que geram pouco impacto no total.
        </p>
    </div>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 text-[10px] uppercase font-bold text-gray-500 tracking-widest">
                <tr>
                    <th class="px-6 py-3 text-left">Produto</th>
                    <th class="px-6 py-3 text-right">Faturamento</th>
                    <th class="px-6 py-3 text-right">Participação (%)</th>
                    <th class="px-6 py-3 text-right">Acumulado (%)</th>
                    <th class="px-6 py-3 text-center">Classe</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($products as $p): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <?php echo htmlspecialchars($p['name']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600 font-bold">
                            R$
                            <?php echo number_format($p['revenue'], 2, ',', '.'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500 italic">
                            <?php echo number_format($p['share'], 2, ',', '.'); ?>%
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-400">
                            <?php echo number_format($p['cumulative_share'], 2, ',', '.'); ?>%
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <?php
                            $color = $p['class'] == 'A' ? 'bg-green-100 text-green-700 border-green-200' : ($p['class'] == 'B' ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-orange-100 text-orange-700 border-orange-200');
                            ?>
                            <span class="px-3 py-1 text-xs font-black rounded-full border <?php echo $color; ?>">
                                <?php echo $p['class']; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</div><!-- /.flex.flex-col.gap-6 -->

<?php require 'views/layouts/footer.php'; ?>