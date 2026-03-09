<?php
$tab = $tab ?? 'financeiro';
$kpis = $kpis ?? ['total_aportes' => 0, 'total_emprestado' => 0, 'total_doado' => 0, 'total_equipamentos' => 0, 'num_participantes' => 0, 'total_em_aberto' => 0];
$investments = $investments ?? [];
$pessoas = $pessoas ?? [];
$categorias = $categorias ?? [];
$participants = $participants ?? [];
$assets = $assets ?? [];
$assetTotals = $assetTotals ?? ['quantidade' => 0, 'valor_total' => 0];
$assetCategorias = $assetCategorias ?? [];
$participacao = $participacao ?? [];
$participantsList = $participantsList ?? [];
$totals = $totals ?? ['total_valor' => 0, 'quantidade' => 0];
$filters = $filters ?? [
    'start_date' => '', 'end_date' => '', 'tipo' => '', 'estado' => '', 'pessoa' => '',
    'categoria_ativo' => '', 'status' => '', 'forma_pagamento' => '',
];
$assetFilters = $assetFilters ?? ['categoria' => '', 'origem' => '', 'responsavel_id' => ''];

$tiposLabels = [
    'aporte' => 'Aporte',
    'emprestimo' => 'Empréstimo',
    'doacao' => 'Doação',
    'compra' => 'Compra',
    'aporte_socio' => 'Aporte de Sócio',
    'investimento_dinheiro' => 'Investimento em Dinheiro',
];
$estadosLabels = ['novo' => 'Novo', 'usado' => 'Usado'];
$statusLabels = ['em_aberto' => 'Em aberto', 'parcial' => 'Parcial', 'quitado' => 'Quitado', 'vencido' => 'Vencido'];
$origemLabels = ['comprado' => 'Comprado', 'doado' => 'Doado', 'emprestado' => 'Emprestado'];

$baseUrl = BASE_URL ?? '';
$baseQuery = 'route=investment/index';
$exportParams = array_intersect_key($_GET ?? [], array_flip(['start_date', 'end_date', 'tipo', 'pessoa', 'status']));
$exportParams = array_filter($exportParams, function ($v) { return $v !== '' && $v !== null; });
$exportUrl = $baseUrl . '?route=investment/export' . ($exportParams ? '&' . http_build_query($exportParams) : '');
?>
<?php require 'views/layouts/header.php'; ?>

<div class="flex flex-col gap-6">
    <!-- Header + Ações rápidas -->
    <div class="flex justify-between items-center flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-800">Controle de Investimentos</h1>
            <p class="text-sm text-gray-400">Financeiro recebido, bens/equipamentos, participação societária e relatórios.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <?php if ($tab === 'financeiro'): ?>
                <a href="<?php echo e($baseUrl); ?>?route=investment/create" class="btn bg-indigo-600 hover:bg-indigo-700 border-none btn-sm text-white rounded-xl shadow-md font-black no-underline">
                    <i class="fas fa-plus mr-2"></i> Novo Registro
                </a>
                <a href="<?php echo e($exportUrl); ?>" class="btn bg-gray-600 hover:bg-gray-700 border-none btn-sm text-white rounded-xl font-black no-underline">
                    <i class="fas fa-file-export mr-2"></i> Exportar
                </a>
            <?php elseif ($tab === 'bens'): ?>
                <a href="<?php echo e($baseUrl); ?>?route=investment/assetCreate" class="btn bg-indigo-600 hover:bg-indigo-700 border-none btn-sm text-white rounded-xl shadow-md font-black no-underline">
                    <i class="fas fa-plus mr-2"></i> Novo Bem/Equipamento
                </a>
            <?php elseif ($tab === 'participacao'): ?>
                <a href="<?php echo e($baseUrl); ?>?route=investment/participantCreate" class="btn bg-indigo-600 hover:bg-indigo-700 border-none btn-sm text-white rounded-xl shadow-md font-black no-underline">
                    <i class="fas fa-user-plus mr-2"></i> Novo Participante
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 flex items-center gap-3">
            <i class="fas fa-check-circle text-green-600"></i>
            <span><?php
                if ($_GET['success'] === 'deleted') echo 'Lançamento excluído.';
                elseif ($_GET['success'] === 'updated') echo 'Lançamento alterado com sucesso.';
                elseif ($_GET['success'] === 'participant') echo 'Participante cadastrado.';
                elseif ($_GET['success'] === 'participant_updated') echo 'Participante atualizado.';
                elseif ($_GET['success'] === 'participant_deleted') echo 'Participante excluído.';
                elseif ($_GET['success'] === 'asset') echo 'Bem/equipamento cadastrado.';
                elseif ($_GET['success'] === 'asset_updated') echo 'Bem/equipamento atualizado.';
                elseif ($_GET['success'] === 'asset_deleted') echo 'Bem/equipamento excluído.';
                else echo 'Registro salvo com sucesso.';
            ?></span>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 flex items-center gap-3">
            <i class="fas fa-exclamation-circle text-red-600"></i>
            <span>Não foi possível concluir a ação. Tente novamente.</span>
        </div>
    <?php endif; ?>

    <!-- KPIs (6 cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <div class="card-standard-metric p-4 border-l-4 border-green-500 flex justify-between items-center rounded-xl shadow-sm bg-white">
            <div>
                <p class="card-metric-label text-[10px] uppercase text-gray-500">💰 Total Investido (Aportes)</p>
                <h2 class="text-xl font-black text-gray-800">R$ <?php echo number_format($kpis['total_aportes'], 2, ',', '.'); ?></h2>
            </div>
        </div>
        <div class="card-standard-metric p-4 border-l-4 border-blue-500 flex justify-between items-center rounded-xl shadow-sm bg-white">
            <div>
                <p class="card-metric-label text-[10px] uppercase text-gray-500">🏦 Total Emprestado</p>
                <h2 class="text-xl font-black text-gray-800">R$ <?php echo number_format($kpis['total_emprestado'], 2, ',', '.'); ?></h2>
            </div>
        </div>
        <div class="card-standard-metric p-4 border-l-4 border-purple-500 flex justify-between items-center rounded-xl shadow-sm bg-white">
            <div>
                <p class="card-metric-label text-[10px] uppercase text-gray-500">🎁 Total Doado</p>
                <h2 class="text-xl font-black text-gray-800">R$ <?php echo number_format($kpis['total_doado'], 2, ',', '.'); ?></h2>
            </div>
        </div>
        <div class="card-standard-metric p-4 border-l-4 border-amber-500 flex justify-between items-center rounded-xl shadow-sm bg-white">
            <div>
                <p class="card-metric-label text-[10px] uppercase text-gray-500">📦 Valor Equipamentos</p>
                <h2 class="text-xl font-black text-gray-800">R$ <?php echo number_format($kpis['total_equipamentos'], 2, ',', '.'); ?></h2>
            </div>
        </div>
        <div class="card-standard-metric p-4 border-l-4 border-indigo-500 flex justify-between items-center rounded-xl shadow-sm bg-white">
            <div>
                <p class="card-metric-label text-[10px] uppercase text-gray-500">👥 Participantes</p>
                <h2 class="text-xl font-black text-gray-800"><?php echo (int) $kpis['num_participantes']; ?></h2>
            </div>
        </div>
        <div class="card-standard-metric p-4 border-l-4 border-red-500 flex justify-between items-center rounded-xl shadow-sm bg-white">
            <div>
                <p class="card-metric-label text-[10px] uppercase text-gray-500">⏳ Dívida em Aberto</p>
                <h2 class="text-xl font-black text-red-600">R$ <?php echo number_format($kpis['total_em_aberto'], 2, ',', '.'); ?></h2>
            </div>
        </div>
    </div>

    <!-- Abas -->
    <div class="card-standard overflow-hidden">
        <div class="flex border-b border-gray-200 overflow-x-auto">
            <a href="?<?php echo $baseQuery; ?>&tab=financeiro" class="px-4 py-3 text-sm font-bold whitespace-nowrap border-b-2 <?php echo $tab === 'financeiro' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'; ?>">
                Financeiro Recebido
            </a>
            <a href="?<?php echo $baseQuery; ?>&tab=bens" class="px-4 py-3 text-sm font-bold whitespace-nowrap border-b-2 <?php echo $tab === 'bens' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'; ?>">
                Bens / Equipamentos
            </a>
            <a href="?<?php echo $baseQuery; ?>&tab=participacao" class="px-4 py-3 text-sm font-bold whitespace-nowrap border-b-2 <?php echo $tab === 'participacao' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'; ?>">
                Participação Societária
            </a>
            <a href="?<?php echo $baseQuery; ?>&tab=relatorios" class="px-4 py-3 text-sm font-bold whitespace-nowrap border-b-2 <?php echo $tab === 'relatorios' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'; ?>">
                Relatórios
            </a>
        </div>

        <div class="card-standard-body">
            <?php if ($tab === 'financeiro'): ?>
                <?php require __DIR__ . '/_tab_financeiro.php'; ?>
            <?php elseif ($tab === 'bens'): ?>
                <?php require __DIR__ . '/_tab_bens.php'; ?>
            <?php elseif ($tab === 'participacao'): ?>
                <?php require __DIR__ . '/_tab_participacao.php'; ?>
            <?php elseif ($tab === 'relatorios'): ?>
                <?php require __DIR__ . '/_tab_relatorios.php'; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>
