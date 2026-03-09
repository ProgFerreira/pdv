<?php require 'views/layouts/header.php'; ?>

<div class="flex flex-col gap-8">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-gray-800">Fluxo de Caixa</h1>
            <p class="text-sm text-gray-400">Análise de movimentações reais e previsões futuras.</p>
        </div>
        <div class="bg-white p-2 rounded-xl shadow-sm border border-gray-100">
            <form class="flex items-center gap-2">
                <input type="hidden" name="route" value="cashFlow/index">
                <input type="date" name="start_date" value="<?php echo $filters['start_date']; ?>"
                    class="rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 text-sm">
                <span class="text-xs text-gray-400">até</span>
                <input type="date" name="end_date" value="<?php echo $filters['end_date']; ?>"
                    class="rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 text-sm">
                <button type="submit"
                    class="btn bg-indigo-600 hover:bg-indigo-700 text-white border-none rounded-xl shadow-md font-black transition-all active:scale-95 btn-xs">Filtrar</button>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="cards-grid-default gap-6">
        <?php
        $realizedIn = array_reduce(array_filter($realized, fn($m) => $m['tipo'] === 'ENTRADA'), fn($c, $i) => $c + $i['valor'], 0);
        $realizedOut = array_reduce(array_filter($realized, fn($m) => $m['tipo'] === 'SAIDA'), fn($c, $i) => $c + $i['valor'], 0);
        $forecastIn = array_reduce($forecastedR, fn($c, $i) => $c + $i['saldo_aberto'], 0);
        $forecastOut = array_reduce($forecastedP, fn($c, $i) => $c + $i['saldo_aberto'], 0);
        ?>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Realizado (Efetivado)</p>
            <div class="flex justify-between items-end">
                <h2
                    class="text-2xl font-black <?php echo ($realizedIn - $realizedOut) >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                    R$
                    <?php echo number_format($realizedIn - $realizedOut, 2, ',', '.'); ?>
                </h2>
                <div class="text-right">
                    <p class="text-[10px] text-green-500 font-bold">+ R$
                        <?php echo number_format($realizedIn, 2, ',', '.'); ?>
                    </p>
                    <p class="text-[10px] text-red-500 font-bold">- R$
                        <?php echo number_format($realizedOut, 2, ',', '.'); ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Previsto (Pendentes)</p>
            <div class="flex justify-between items-end">
                <h2
                    class="text-2xl font-black <?php echo ($forecastIn - $forecastOut) >= 0 ? 'text-indigo-600' : 'text-orange-600'; ?>">
                    R$
                    <?php echo number_format($forecastIn - $forecastOut, 2, ',', '.'); ?>
                </h2>
                <div class="text-right">
                    <p class="text-[10px] text-indigo-500 font-bold">+ R$
                        <?php echo number_format($forecastIn, 2, ',', '.'); ?>
                    </p>
                    <p class="text-[10px] text-orange-500 font-bold">- R$
                        <?php echo number_format($forecastOut, 2, ',', '.'); ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-700">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Saldo Final Projetado</p>
            <h2 class="text-2xl font-black text-white">
                R$
                <?php echo number_format(($realizedIn - $realizedOut) + ($forecastIn - $forecastOut), 2, ',', '.'); ?>
            </h2>
            <p class="text-[9px] text-gray-500 mt-2 italic">* Considera saldo atual + previsões do período.</p>
        </div>
    </div>

    <!-- Detailed View -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-8">
        <!-- Realized Table -->
        <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 bg-gray-50/50 border-b border-gray-100">
                <h3 class="font-bold text-gray-700 text-sm">📅 Movimentações Realizadas</h3>
            </div>
            <div class="max-h-[400px] overflow-y-auto">
                <table class="table table-compact w-full">
                    <tbody class="divide-y divide-gray-50">
                        <?php foreach ($realized as $m): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="text-[10px] font-bold text-gray-400">
                                    <?php echo date('d/m', strtotime($m['created_at'])); ?>
                                </td>
                                <td class="text-xs font-medium text-gray-700">
                                    <?php echo $m['descricao']; ?>
                                </td>
                                <td
                                    class="text-right font-black text-xs <?php echo $m['tipo'] === 'ENTRADA' ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo $m['tipo'] === 'ENTRADA' ? '+' : '-'; ?> R$
                                    <?php echo number_format($m['valor'], 2, ',', '.'); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Forecasted Table -->
        <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 bg-gray-50/50 border-b border-gray-100">
                <h3 class="font-bold text-gray-700 text-sm">🔮 Projeções Futuras (Vencimentos)</h3>
            </div>
            <div class="max-h-[400px] overflow-y-auto">
                <table class="table table-compact w-full">
                    <tbody class="divide-y divide-gray-50">
                        <?php
                        $mergedForecast = array_merge($forecastedR, $forecastedP);
                        usort($mergedForecast, fn($a, $b) => strcmp($a['data_vencimento'], $b['data_vencimento']));
                        foreach ($mergedForecast as $f):
                            $isReceivable = isset($f['cliente_id']);
                            ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="text-[10px] font-bold text-gray-400">
                                    <?php echo date('d/m', strtotime($f['data_vencimento'])); ?>
                                </td>
                                <td class="text-xs font-medium text-gray-700">
                                    <?php echo $f['descricao']; ?>
                                </td>
                                <td
                                    class="text-right font-black text-xs <?php echo $isReceivable ? 'text-blue-500' : 'text-orange-500'; ?>">
                                    <?php echo $isReceivable ? '+' : '-'; ?> R$
                                    <?php echo number_format($f['saldo_aberto'], 2, ',', '.'); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>