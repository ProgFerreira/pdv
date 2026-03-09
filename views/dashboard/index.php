<?php require 'views/layouts/header.php'; ?>
<style>
.dashboard-wrap { width: 100%; max-width: 100%; }
.dashboard-wrap .dash-section { margin-bottom: 1.5rem; }
.dashboard-wrap .dash-cards-grid { gap: 1rem; }
.dashboard-wrap .dash-card-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; margin-bottom: 0.5rem; }
.dashboard-wrap .dash-card-value { font-size: 1.35rem; font-weight: 800; line-height: 1.2; }
.dashboard-wrap .dash-card-extra { font-size: 0.7rem; color: #9ca3af; margin-top: 0.5rem; }
.dashboard-wrap .dash-card-icon { width: 2.25rem; height: 2.25rem; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; flex-shrink: 0; }
.dashboard-wrap .dash-main-grid { display: grid; gap: 1.5rem; grid-template-columns: 1fr; min-width: 0; }
@media (min-width: 1024px) { .dashboard-wrap .dash-main-grid { grid-template-columns: 2fr 1fr; } }
.dashboard-wrap .dash-shortcuts { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem; }
.dashboard-wrap .dash-shortcut { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 0.5rem 0.6rem; border-radius: 8px; text-decoration: none; font-size: 0.7rem; font-weight: 600; transition: background-color 0.15s, border-color 0.15s, box-shadow 0.15s; border-width: 1px; border-style: solid; }
.dashboard-wrap .dash-shortcut:hover { box-shadow: 0 2px 6px rgba(0,0,0,0.06); }
.dashboard-wrap .dash-shortcut i { font-size: 1.1rem; margin-bottom: 0.25rem; }
.dashboard-wrap .dash-stock-list { max-height: 220px; overflow-y: auto; }
.dashboard-wrap .dash-stock-item { padding: 0.75rem 1.25rem; border-bottom: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; }
.dashboard-wrap .dash-stock-item:hover { background: #f9fafb; }
.dashboard-wrap .dash-stock-item:last-child { border-bottom: none; }
/* Cabeçalho: período e botão em uma linha, sem misturar */
.dashboard-wrap .dash-header-form { display: flex; flex-wrap: nowrap; align-items: center; gap: 0.75rem; }
.dashboard-wrap .dash-header-form .dash-date-wrap { display: flex; align-items: center; border-radius: 0.5rem; border: 1px solid #e5e7eb; background: #f9fafb; overflow: hidden; flex-shrink: 0; }
.dashboard-wrap .dash-header-form .dash-date-wrap span { padding-left: 0.75rem; color: #6b7280; }
.dashboard-wrap .dash-header-form .dash-date-wrap input { border: 0; background: transparent; padding: 0.5rem 0.75rem; font-size: 0.875rem; color: #374151; width: 8.5rem; }
.dashboard-wrap .dash-header-form .dash-sep { color: #9ca3af; font-size: 0.875rem; flex-shrink: 0; }
.dashboard-wrap .dash-header-form .dash-btn { flex-shrink: 0; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 700; border-radius: 0.5rem; background: #4f46e5; color: #fff; border: none; cursor: pointer; white-space: nowrap; }
.dashboard-wrap .dash-header-form .dash-btn:hover { background: #4338ca; }
@media (max-width: 639px) { .dashboard-wrap header.dash-section { flex-direction: column; align-items: stretch; } .dashboard-wrap .dash-header-form { flex-wrap: wrap; } }
</style>
<div class="dashboard-wrap">
    <?php if (isset($_GET['error']) && $_GET['error'] === 'unauthorized'): ?>
    <div class="dash-section p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 flex items-center gap-3">
        <i class="fas fa-lock"></i>
        <span>Você não tem permissão para acessar essa página.</span>
    </div>
    <?php endif; ?>

    <!-- Cabeçalho: título + filtro de período (uma linha, organizado) -->
    <header class="dash-section flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800 m-0">Painel de Controle</h1>
        <form method="GET" action="<?php echo BASE_URL; ?>" class="dash-header-form">
            <input type="hidden" name="route" value="dashboard/index">
            <div class="dash-date-wrap">
                <span><i class="fas fa-calendar-alt text-sm"></i></span>
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($startDate ?? ''); ?>" aria-label="Data inicial">
            </div>
            <span class="dash-sep">até</span>
            <div class="dash-date-wrap">
                <span><i class="fas fa-calendar-alt text-sm"></i></span>
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($endDate ?? ''); ?>" aria-label="Data final">
            </div>
            <button type="submit" class="dash-btn">Atualizar</button>
        </form>
    </header>

    <!-- Cards de resumo financeiro -->
    <section class="dash-section">
        <div class="dash-cards-grid cards-grid-default">
            <div class="card-standard-metric dash-card border-l-success">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <div class="dash-card-label">Saldo em Caixa</div>
                        <div class="dash-card-value text-gray-800">R$ <?php echo number_format($currentBalance ?? 0, 2, ',', '.'); ?></div>
                        <div class="dash-card-extra flex items-center gap-1.5 mt-1">
                            <span class="w-2 h-2 rounded-full <?php echo $openRegister ? 'bg-green-500' : 'bg-gray-300'; ?>"></span>
                            Caixa <?php echo $openRegister ? 'Aberto' : 'Fechado'; ?>
                        </div>
                    </div>
                    <div class="dash-card-icon bg-green-50 text-green-600 border border-green-100"><i class="fas fa-wallet"></i></div>
                </div>
            </div>
            <div class="card-standard-metric dash-card border-l-primary">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <div class="dash-card-label">Vendas do Período</div>
                        <div class="dash-card-value text-gray-800">R$ <?php echo number_format($periodStats['total'] ?? 0, 2, ',', '.'); ?></div>
                        <div class="dash-card-extra"><?php echo (int)($periodStats['count'] ?? 0); ?> vendas</div>
                    </div>
                    <div class="dash-card-icon bg-indigo-50 text-indigo-600 border border-indigo-100"><i class="fas fa-shopping-cart"></i></div>
                </div>
            </div>
            <div class="card-standard-metric dash-card border-l-danger">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <div class="dash-card-label">Contas a Pagar</div>
                        <div class="dash-card-value text-red-600">R$ <?php echo number_format($pendingPayable ?? 0, 2, ',', '.'); ?></div>
                        <a href="?route=payable/index" class="inline-flex items-center gap-1 mt-2 text-xs font-bold text-red-600 hover:text-red-700">
                            Ver pendentes <i class="fas fa-chevron-right text-[10px]"></i>
                        </a>
                    </div>
                    <div class="dash-card-icon bg-red-50 text-red-600 border border-red-100"><i class="fas fa-arrow-circle-down"></i></div>
                </div>
            </div>
            <div class="card-standard-metric dash-card border-l-warning">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <div class="dash-card-label">Contas a Receber</div>
                        <div class="dash-card-value text-amber-600">R$ <?php echo number_format($pendingReceivable ?? 0, 2, ',', '.'); ?></div>
                        <a href="?route=receivable/index" class="inline-flex items-center gap-1 mt-2 text-xs font-bold text-amber-600 hover:text-amber-700">
                            Ver a receber <i class="fas fa-chevron-right text-[10px]"></i>
                        </a>
                    </div>
                    <div class="dash-card-icon bg-amber-50 text-amber-600 border border-amber-100"><i class="fas fa-arrow-circle-up"></i></div>
                </div>
            </div>
            <?php
            $receivable = (float)($pendingReceivable ?? 0);
            $payable = (float)($pendingPayable ?? 0);
            $resultadoReceberPagar = $receivable - $payable;
            $resultadoPositivo = $resultadoReceberPagar > 0;
            $resultadoNegativo = $resultadoReceberPagar < 0;
            ?>
            <div class="card-standard-metric dash-card border-l-info">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <div class="dash-card-label">Receber − Pagar</div>
                        <div class="dash-card-value <?php echo $resultadoPositivo ? 'text-green-600' : ($resultadoNegativo ? 'text-red-600' : 'text-gray-600'); ?>">
                            R$ <?php echo number_format($resultadoReceberPagar, 2, ',', '.'); ?>
                        </div>
                        <div class="dash-card-extra">
                            <?php echo $resultadoPositivo ? 'Saldo a seu favor' : ($resultadoNegativo ? 'Saldo a pagar' : 'Em equilíbrio'); ?>
                        </div>
                    </div>
                    <div class="dash-card-icon border <?php echo $resultadoPositivo ? 'bg-green-50 text-green-600 border-green-100' : ($resultadoNegativo ? 'bg-red-50 text-red-600 border-red-100' : 'bg-gray-50 text-gray-600 border-gray-200'); ?>">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Gráfico + atalhos e estoque crítico -->
    <section class="dash-section">
        <div class="dash-main-grid">
            <div class="card-standard min-w-0">
                <div class="card-standard-header">
                    <i class="fas fa-chart-line"></i>
                    Desempenho de Vendas
                </div>
                <div class="card-standard-body p-4" style="height: 320px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
            <div class="flex flex-col gap-4 min-w-0">
                <div class="card-standard">
                    <div class="card-standard-header">
                        <i class="fas fa-bolt"></i>
                        Ações Rápidas
                    </div>
                    <div class="card-standard-body">
                        <div class="dash-shortcuts">
                            <a href="?route=pos/index" class="dash-shortcut border bg-indigo-50 text-indigo-700 border-indigo-200 hover:bg-indigo-100 hover:border-indigo-300">
                                <i class="fas fa-cash-register"></i>
                                Nova Venda
                            </a>
                            <a href="?route=stock/create" class="dash-shortcut border bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100 hover:border-gray-300">
                                <i class="fas fa-box-open"></i>
                                Entrada Estoque
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-standard flex-1 flex flex-col min-h-0">
                    <div class="card-standard-header flex justify-between items-center">
                        <i class="fas fa-exclamation-triangle text-amber-500"></i>
                        <span>Estoque Crítico</span>
                        <?php $lowCount = count($lowStock ?? []); ?>
                        <?php if ($lowCount > 0): ?>
                        <span class="px-2 py-0.5 bg-red-100 text-red-600 text-xs font-bold rounded-full"><?php echo $lowCount; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="dash-stock-list flex-1">
                        <?php if (empty($lowStock)): ?>
                        <div class="p-4 text-gray-400 text-center text-sm">Nenhum produto em falta.</div>
                        <?php else: ?>
                        <?php foreach ($lowStock as $p): ?>
                        <div class="dash-stock-item">
                            <span class="text-gray-700 font-medium truncate pr-2"><?php echo htmlspecialchars($p['name'] ?? ''); ?></span>
                            <span class="text-red-600 font-bold text-xs flex-shrink-0"><?php echo (int)($p['stock'] ?? 0); ?> un</span>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('salesChart');
    if (!ctx) return;
    new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chartLabels ?? []); ?>,
            datasets: [{
                label: 'Vendas (R$)',
                data: <?php echo json_encode($chartData ?? []); ?>,
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.08)',
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#6366f1',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(ctx) {
                            var v = ctx.parsed.y || 0;
                            return 'R$ ' + v.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f3f4f6' },
                    ticks: {
                        callback: function(v) { return 'R$ ' + v.toLocaleString('pt-BR'); },
                        font: { size: 10 }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10 } }
                }
            }
        }
    });
});
</script>

<?php require 'views/layouts/footer.php'; ?>
