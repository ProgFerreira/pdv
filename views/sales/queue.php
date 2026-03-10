<?php
require 'views/layouts/header.php';

/**
 * Formata segundos em "X min" ou "X h Y min" para exibição na fila.
 */
$formatElapsed = function ($seconds) {
    if ($seconds < 0) {
        return '0 min';
    }
    $min = (int) round($seconds / 60);
    if ($min < 60) {
        return $min . ' min';
    }
    $h = (int) floor($min / 60);
    $m = $min % 60;
    return $h . ' h ' . $m . ' min';
};

$baseQuery = ['route' => 'sale/queue', 'date' => $date];
if ($sectorId !== null && $sectorId !== '') {
    $baseQuery['sector_id'] = $sectorId;
}
// Parâmetros para voltar à fila após marcar/desmarcar entregue
$returnParams = ['return_to' => 'queue', 'date' => $date];
if ($sectorId !== null && $sectorId !== '') {
    $returnParams['sector_id'] = $sectorId;
}
?>
<div class="queue-page min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
  <div class="max-w-7xl mx-auto px-4 py-6">
    <!-- Título e filtros -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-amber-500/20 flex items-center justify-center">
          <i class="fas fa-clipboard-list text-2xl text-amber-600"></i>
        </div>
        <div>
          <h1 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tight">Fila de Pedidos</h1>
          <p class="text-sm text-slate-500 mt-0.5">Em preparação, saiu para entrega e entregues — dê baixa nos pedidos</p>
        </div>
      </div>
      <div class="flex items-center gap-2 flex-wrap">
        <a href="<?php echo BASE_URL; ?>?route=sale/index" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-100 font-medium text-sm transition-colors">
          <i class="fas fa-list"></i> Listagem de vendas
        </a>
      </div>
    </div>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'delivered'): ?>
      <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3 shadow-sm">
        <i class="fas fa-check-circle text-xl text-emerald-500"></i>
        <span class="font-medium">Pedido marcado como entregue.</span>
      </div>
    <?php endif; ?>
    <?php if (isset($_GET['success']) && $_GET['success'] === 'delivery_removed'): ?>
      <div class="mb-4 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 flex items-center gap-3 shadow-sm">
        <i class="fas fa-undo text-xl text-amber-500"></i>
        <span class="font-medium">Entrega desmarcada.</span>
      </div>
    <?php endif; ?>
    <?php if (isset($_GET['success']) && $_GET['success'] === 'out_for_delivery'): ?>
      <div class="mb-4 p-4 rounded-xl bg-sky-50 border border-sky-200 text-sky-800 flex items-center gap-3 shadow-sm">
        <i class="fas fa-truck-loading text-xl text-sky-500"></i>
        <span class="font-medium">Pedido marcado como saiu para entrega.</span>
      </div>
    <?php endif; ?>
    <?php if (isset($_GET['success']) && $_GET['success'] === 'out_for_delivery_removed'): ?>
      <div class="mb-4 p-4 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 flex items-center gap-3 shadow-sm">
        <i class="fas fa-undo text-xl text-slate-500"></i>
        <span class="font-medium">Saiu para entrega desmarcado.</span>
      </div>
    <?php endif; ?>

    <!-- Cards de estatísticas: Em preparação | Entrega | Entregue -->
    <?php
    $countPreparation = count($inPreparation);
    $countOutForDelivery = count($outForDelivery);
    $countDelivered = count($delivered);
    ?>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <div class="card-standard-metric p-4 border-l-warning">
        <h6 class="card-metric-label">Em preparação</h6>
        <div class="flex justify-between items-center gap-2">
          <span class="text-lg xl:text-xl font-black text-amber-600"><?php echo $countPreparation; ?></span>
          <i class="fas fa-hourglass-half text-base text-amber-200 flex-shrink-0"></i>
        </div>
      </div>
      <div class="card-standard-metric p-4 border-l-info">
        <h6 class="card-metric-label">Saiu para entrega</h6>
        <div class="flex justify-between items-center gap-2">
          <span class="text-lg xl:text-xl font-black text-sky-600"><?php echo $countOutForDelivery; ?></span>
          <i class="fas fa-truck-loading text-base text-sky-200 flex-shrink-0"></i>
        </div>
      </div>
      <div class="card-standard-metric p-4 border-l-success">
        <h6 class="card-metric-label">Entregue</h6>
        <div class="flex justify-between items-center gap-2">
          <span class="text-lg xl:text-xl font-black text-emerald-600"><?php echo $countDelivered; ?></span>
          <i class="fas fa-check-double text-base text-emerald-200 flex-shrink-0"></i>
        </div>
      </div>
    </div>

    <!-- Filtro data + setor -->
    <div class="mb-6 p-4 rounded-xl bg-white border border-slate-200 shadow-sm">
      <form method="GET" action="<?php echo BASE_URL; ?>" class="flex flex-wrap items-end gap-4">
        <input type="hidden" name="route" value="sale/queue">
        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Data</label>
          <input type="date" name="date" value="<?php echo htmlspecialchars($date, ENT_QUOTES, 'UTF-8'); ?>"
                 class="rounded-lg border-slate-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm font-medium px-3 py-2">
        </div>
        <?php if (isAdmin() && !empty($sectors)): ?>
          <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Setor</label>
            <select name="sector_id" class="rounded-lg border-slate-300 shadow-sm focus:border-amber-500 text-sm font-medium px-3 py-2">
              <option value="all" <?php echo ($sectorId === null || $sectorId === 'all') ? 'selected' : ''; ?>>Todos</option>
              <?php foreach ($sectors as $sec): ?>
                <option value="<?php echo (int) $sec['id']; ?>" <?php echo ($sectorId !== null && (string) $sectorId === (string) $sec['id']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($sec['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        <?php endif; ?>
        <button type="submit" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 px-4 rounded-lg shadow transition-colors text-sm">
          <i class="fas fa-filter"></i> Filtrar
        </button>
      </form>
    </div>

    <!-- Kanban: duas colunas verticais -->
    <div class="kanban-board flex flex-col lg:flex-row gap-4 lg:gap-6 w-full">
      <!-- Coluna Kanban: Em preparação -->
      <div class="kanban-column kanban-column-preparation flex flex-col flex-1 min-w-0 lg:min-w-[320px] rounded-2xl border-2 border-amber-300 bg-amber-50/50 shadow-lg overflow-hidden">
        <div class="kanban-column-header px-4 py-3 bg-amber-500 text-white flex items-center justify-between shrink-0">
          <h2 class="text-base font-bold flex items-center gap-2">
            <i class="fas fa-hourglass-half"></i>
            Em preparação
          </h2>
          <span class="kanban-count px-3 py-1 rounded-full bg-white/25 text-sm font-black"><?php echo count($inPreparation); ?></span>
        </div>
        <div class="kanban-column-cards flex-1 overflow-y-auto p-3 space-y-3 min-h-[280px] max-h-[calc(100vh-320px)]">
          <?php
          $now = time();
          foreach ($inPreparation as $s):
              $created = strtotime($s['created_at']);
              $elapsedSec = $now - $created;
              $elapsedStr = $formatElapsed($elapsedSec);
              $customerName = trim((string)($s['customer_name'] ?? '')) ?: 'Cliente não informado';
              $markOutForDeliveryUrl = BASE_URL . '?' . http_build_query(array_merge(['route' => 'sale/markOutForDelivery', 'id' => $s['id']], $returnParams));
          ?>
            <div class="kanban-card bg-white rounded-xl border-l-4 border-amber-500 shadow-md hover:shadow-lg transition-shadow overflow-hidden">
              <div class="p-4">
                <div class="flex justify-between items-start gap-2 mb-2">
                  <span class="text-xs font-bold text-slate-400 uppercase">#<?php echo (int) $s['id']; ?></span>
                  <span class="px-2 py-0.5 rounded-lg bg-amber-100 text-amber-800 text-xs font-black whitespace-nowrap" title="Tempo desde o pedido">
                    <i class="fas fa-clock mr-1"></i><?php echo $elapsedStr; ?>
                  </span>
                </div>
                <p class="text-sm font-bold text-slate-800 mb-2 truncate" title="<?php echo htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8'); ?></p>
                <div class="flex flex-wrap gap-1 text-xs text-slate-500 mb-3">
                  <?php if (!empty($s['customer_phone'])): ?><span><i class="fas fa-phone-alt mr-1"></i><?php echo htmlspecialchars($s['customer_phone'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                  <?php if (!empty($s['sector_name'])): ?><span class="px-1.5 py-0.5 rounded bg-slate-100"><?php echo htmlspecialchars($s['sector_name'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                  <span><?php echo date('H:i', strtotime($s['created_at'])); ?></span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-slate-100">
                  <span class="text-base font-black text-slate-800">R$ <?php echo number_format((float) $s['total'], 2, ',', '.'); ?></span>
                  <div class="flex items-center gap-1">
                    <a href="<?php echo BASE_URL; ?>?route=sale/view&id=<?php echo (int) $s['id']; ?>" target="_blank" class="p-2 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors" title="Ver pedido"><i class="fas fa-eye"></i></a>
                    <a href="<?php echo htmlspecialchars($markOutForDeliveryUrl, ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex items-center gap-1.5 bg-sky-600 hover:bg-sky-700 text-white font-bold py-2 px-3 rounded-lg text-xs shadow transition-colors" title="Marcar como saiu para entrega"><i class="fas fa-truck-loading"></i> Saiu p/ entrega</a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
          <?php if (empty($inPreparation)): ?>
            <div class="kanban-card-empty flex flex-col items-center justify-center py-10 px-4 rounded-xl border-2 border-dashed border-amber-200 bg-white/80 text-slate-400">
              <i class="fas fa-inbox text-3xl mb-2 opacity-50"></i>
              <p class="text-sm font-medium text-center">Nenhum pedido em preparação</p>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Coluna Kanban: Saiu para entrega -->
      <div class="kanban-column kanban-column-out-for-delivery flex flex-col flex-1 min-w-0 lg:min-w-[320px] rounded-2xl border-2 border-sky-400 bg-sky-50/50 shadow-lg overflow-hidden">
        <div class="kanban-column-header px-4 py-3 bg-sky-600 text-white flex items-center justify-between shrink-0">
          <h2 class="text-base font-bold flex items-center gap-2">
            <i class="fas fa-truck-loading"></i>
            Saiu para entrega
          </h2>
          <span class="kanban-count px-3 py-1 rounded-full bg-white/25 text-sm font-black"><?php echo count($outForDelivery); ?></span>
        </div>
        <div class="kanban-column-cards flex-1 overflow-y-auto p-3 space-y-3 min-h-[280px] max-h-[calc(100vh-320px)]">
          <?php
          $nowOut = time();
          foreach ($outForDelivery as $s):
              $created = strtotime($s['created_at']);
              $outTs = !empty($s['out_for_delivery_at']) ? strtotime($s['out_for_delivery_at']) : $created;
              $elapsedSec = $nowOut - $outTs;
              $elapsedStr = $formatElapsed($elapsedSec);
              $customerName = trim((string)($s['customer_name'] ?? '')) ?: 'Cliente não informado';
              $markDeliveredUrl = BASE_URL . '?' . http_build_query(array_merge(['route' => 'sale/markDelivered', 'id' => $s['id']], $returnParams));
              $unmarkOutUrl = BASE_URL . '?' . http_build_query(array_merge(['route' => 'sale/unmarkOutForDelivery', 'id' => $s['id']], $returnParams));
          ?>
            <div class="kanban-card bg-white rounded-xl border-l-4 border-sky-500 shadow-md hover:shadow-lg transition-shadow overflow-hidden">
              <div class="p-4">
                <div class="flex justify-between items-start gap-2 mb-2">
                  <span class="text-xs font-bold text-slate-400 uppercase">#<?php echo (int) $s['id']; ?></span>
                  <span class="px-2 py-0.5 rounded-lg bg-sky-100 text-sky-800 text-xs font-black whitespace-nowrap" title="Tempo desde que saiu">
                    <i class="fas fa-clock mr-1"></i><?php echo $elapsedStr; ?>
                  </span>
                </div>
                <p class="text-sm font-bold text-slate-800 mb-2 truncate" title="<?php echo htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8'); ?></p>
                <div class="flex flex-wrap gap-1 text-xs text-slate-500 mb-3">
                  <?php if (!empty($s['customer_phone'])): ?><span><i class="fas fa-phone-alt mr-1"></i><?php echo htmlspecialchars($s['customer_phone'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                  <?php if (!empty($s['sector_name'])): ?><span class="px-1.5 py-0.5 rounded bg-slate-100"><?php echo htmlspecialchars($s['sector_name'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                  <span>Saiu às <?php echo date('H:i', $outTs); ?></span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-slate-100">
                  <span class="text-base font-black text-slate-800">R$ <?php echo number_format((float) $s['total'], 2, ',', '.'); ?></span>
                  <div class="flex items-center gap-1 flex-wrap justify-end">
                    <a href="<?php echo BASE_URL; ?>?route=sale/view&id=<?php echo (int) $s['id']; ?>" target="_blank" class="p-2 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors" title="Ver pedido"><i class="fas fa-eye"></i></a>
                    <a href="<?php echo htmlspecialchars($unmarkOutUrl, ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex items-center gap-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-2 px-3 rounded-lg text-xs transition-colors" title="Voltar para preparação"><i class="fas fa-undo"></i> Voltar</a>
                    <a href="<?php echo htmlspecialchars($markDeliveredUrl, ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-3 rounded-lg text-xs shadow transition-colors" title="Marcar como entregue"><i class="fas fa-truck"></i> Entregue</a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
          <?php if (empty($outForDelivery)): ?>
            <div class="kanban-card-empty flex flex-col items-center justify-center py-10 px-4 rounded-xl border-2 border-dashed border-sky-200 bg-white/80 text-slate-400">
              <i class="fas fa-truck-loading text-3xl mb-2 opacity-50"></i>
              <p class="text-sm font-medium text-center">Nenhum pedido saiu para entrega</p>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Coluna Kanban: Entregue -->
      <div class="kanban-column kanban-column-delivered flex flex-col flex-1 min-w-0 lg:min-w-[320px] rounded-2xl border-2 border-emerald-400 bg-emerald-50/50 shadow-lg overflow-hidden">
        <div class="kanban-column-header px-4 py-3 bg-emerald-600 text-white flex items-center justify-between shrink-0">
          <h2 class="text-base font-bold flex items-center gap-2">
            <i class="fas fa-check-double"></i>
            Entregue
          </h2>
          <span class="kanban-count px-3 py-1 rounded-full bg-white/25 text-sm font-black"><?php echo count($delivered); ?></span>
        </div>
        <div class="kanban-column-cards flex-1 overflow-y-auto p-3 space-y-3 min-h-[280px] max-h-[calc(100vh-320px)]">
          <?php
          foreach ($delivered as $s):
              $created = strtotime($s['created_at']);
              $deliveredTs = strtotime($s['delivered_at']);
              $totalSec = $deliveredTs - $created;
              $totalStr = $formatElapsed($totalSec);
              $customerName = trim((string)($s['customer_name'] ?? '')) ?: 'Cliente não informado';
              $unmarkUrl = BASE_URL . '?' . http_build_query(array_merge(['route' => 'sale/unmarkDelivered', 'id' => $s['id']], $returnParams));
          ?>
            <div class="kanban-card bg-white rounded-xl border-l-4 border-emerald-500 shadow-md overflow-hidden">
              <div class="p-4">
                <div class="flex justify-between items-start gap-2 mb-2">
                  <span class="text-xs font-bold text-slate-400 uppercase">#<?php echo (int) $s['id']; ?></span>
                  <span class="px-2 py-0.5 rounded-lg bg-emerald-100 text-emerald-800 text-xs font-bold whitespace-nowrap" title="Tempo até a entrega"><i class="fas fa-stopwatch mr-1"></i><?php echo $totalStr; ?></span>
                </div>
                <p class="text-sm font-bold text-slate-800 mb-2 truncate" title="<?php echo htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8'); ?></p>
                <div class="flex flex-wrap gap-1 text-xs text-slate-500 mb-3">
                  <?php if (!empty($s['customer_phone'])): ?><span><i class="fas fa-phone-alt mr-1"></i><?php echo htmlspecialchars($s['customer_phone'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                  <span>Entregue às <?php echo date('H:i', $deliveredTs); ?></span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-slate-100">
                  <span class="text-base font-black text-slate-800">R$ <?php echo number_format((float) $s['total'], 2, ',', '.'); ?></span>
                  <div class="flex items-center gap-1">
                    <a href="<?php echo BASE_URL; ?>?route=sale/view&id=<?php echo (int) $s['id']; ?>" target="_blank" class="p-2 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors" title="Ver pedido"><i class="fas fa-eye"></i></a>
                    <a href="<?php echo htmlspecialchars($unmarkUrl, ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex items-center gap-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-2 px-3 rounded-lg text-xs transition-colors" title="Desmarcar entrega"><i class="fas fa-undo"></i> Desfazer</a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
          <?php if (empty($delivered)): ?>
            <div class="kanban-card-empty flex flex-col items-center justify-center py-10 px-4 rounded-xl border-2 border-dashed border-emerald-200 bg-white/80 text-slate-400">
              <i class="fas fa-check-double text-3xl mb-2 opacity-50"></i>
              <p class="text-sm font-medium text-center">Nenhum pedido entregue</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  .kanban-board { display: flex; }
  .kanban-column { flex: 1 1 0; }
  .kanban-column-cards { scrollbar-width: thin; }
  .kanban-column-cards::-webkit-scrollbar { width: 6px; }
  .kanban-column-cards::-webkit-scrollbar-track { background: rgba(0,0,0,.05); border-radius: 3px; }
  .kanban-column-cards::-webkit-scrollbar-thumb { background: rgba(0,0,0,.15); border-radius: 3px; }
  @media (max-width: 1023px) {
    .kanban-column { min-height: 280px; }
    .kanban-column-cards { max-height: 400px; }
  }
</style>
<?php require 'views/layouts/footer.php'; ?>
