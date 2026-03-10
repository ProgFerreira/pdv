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
          <p class="text-sm text-slate-500 mt-0.5">Em preparação e entregues — dê baixa nos pedidos</p>
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

    <!-- Duas colunas: Em preparação | Entregue -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
      <!-- Coluna 1: Em preparação -->
      <div class="flex flex-col">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-amber-500 animate-pulse"></span>
            Em preparação
            <span class="ml-2 px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-sm font-bold"><?php echo count($inPreparation); ?></span>
          </h2>
        </div>
        <div class="flex-1 space-y-4 min-h-[200px]">
          <?php
          $now = time();
          foreach ($inPreparation as $s):
              $created = strtotime($s['created_at']);
              $elapsedSec = $now - $created;
              $elapsedStr = $formatElapsed($elapsedSec);
              $customerName = trim((string)($s['customer_name'] ?? '')) ?: 'Cliente não informado';
              $markUrl = BASE_URL . '?' . http_build_query(array_merge(['route' => 'sale/markDelivered', 'id' => $s['id']], $returnParams));
          ?>
            <div class="queue-card queue-card-preparation bg-white rounded-xl border-2 border-amber-200 shadow-md hover:shadow-lg transition-shadow overflow-hidden">
              <div class="p-4 sm:p-5">
                <div class="flex justify-between items-start gap-2 mb-3">
                  <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pedido #<?php echo (int) $s['id']; ?></span>
                    <p class="text-base font-bold text-slate-800 mt-0.5"><?php echo htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8'); ?></p>
                  </div>
                  <span class="px-2.5 py-1 rounded-lg bg-amber-100 text-amber-800 text-sm font-black whitespace-nowrap" title="Tempo desde a gravação do pedido">
                    <i class="fas fa-clock mr-1"></i><?php echo $elapsedStr; ?>
                  </span>
                </div>
                <div class="flex flex-wrap gap-2 text-xs text-slate-500 mb-4">
                  <?php if (!empty($s['customer_phone'])): ?>
                    <span><i class="fas fa-phone-alt mr-1"></i><?php echo htmlspecialchars($s['customer_phone'], ENT_QUOTES, 'UTF-8'); ?></span>
                  <?php endif; ?>
                  <?php if (!empty($s['sector_name'])): ?>
                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600"><?php echo htmlspecialchars($s['sector_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                  <?php endif; ?>
                  <span><?php echo date('H:i', strtotime($s['created_at'])); ?></span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-lg font-black text-slate-800">R$ <?php echo number_format((float) $s['total'], 2, ',', '.'); ?></span>
                  <div class="flex items-center gap-2">
                    <a href="<?php echo BASE_URL; ?>?route=sale/view&id=<?php echo (int) $s['id']; ?>" target="_blank" class="text-slate-500 hover:text-slate-700 p-2 rounded-lg hover:bg-slate-100 transition-colors" title="Ver pedido">
                      <i class="fas fa-eye"></i>
                    </a>
                    <a href="<?php echo htmlspecialchars($markUrl, ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-4 rounded-lg shadow transition-colors text-sm" title="Marcar como entregue">
                      <i class="fas fa-truck"></i> Entregue
                    </a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
          <?php if (empty($inPreparation)): ?>
            <div class="queue-card bg-white rounded-xl border-2 border-dashed border-slate-200 p-8 text-center text-slate-400">
              <i class="fas fa-inbox text-4xl mb-2 opacity-50"></i>
              <p class="font-medium">Nenhum pedido em preparação nesta data.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Coluna 2: Entregue -->
      <div class="flex flex-col">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
            Entregue
            <span class="ml-2 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-sm font-bold"><?php echo count($delivered); ?></span>
          </h2>
        </div>
        <div class="flex-1 space-y-4 min-h-[200px]">
          <?php
          foreach ($delivered as $s):
              $created = strtotime($s['created_at']);
              $deliveredTs = strtotime($s['delivered_at']);
              $totalSec = $deliveredTs - $created;
              $totalStr = $formatElapsed($totalSec);
              $customerName = trim((string)($s['customer_name'] ?? '')) ?: 'Cliente não informado';
              $unmarkUrl = BASE_URL . '?' . http_build_query(array_merge(['route' => 'sale/unmarkDelivered', 'id' => $s['id']], $returnParams));
          ?>
            <div class="queue-card queue-card-delivered bg-white rounded-xl border-2 border-emerald-200 shadow-md overflow-hidden">
              <div class="p-4 sm:p-5">
                <div class="flex justify-between items-start gap-2 mb-3">
                  <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pedido #<?php echo (int) $s['id']; ?></span>
                    <p class="text-base font-bold text-slate-800 mt-0.5"><?php echo htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8'); ?></p>
                  </div>
                  <span class="px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800 text-sm font-bold whitespace-nowrap" title="Tempo da gravação até a entrega">
                    <i class="fas fa-stopwatch mr-1"></i><?php echo $totalStr; ?>
                  </span>
                </div>
                <div class="flex flex-wrap gap-2 text-xs text-slate-500 mb-4">
                  <?php if (!empty($s['customer_phone'])): ?>
                    <span><i class="fas fa-phone-alt mr-1"></i><?php echo htmlspecialchars($s['customer_phone'], ENT_QUOTES, 'UTF-8'); ?></span>
                  <?php endif; ?>
                  <span>Entregue às <?php echo date('H:i', $deliveredTs); ?></span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-lg font-black text-slate-800">R$ <?php echo number_format((float) $s['total'], 2, ',', '.'); ?></span>
                  <div class="flex items-center gap-2">
                    <a href="<?php echo BASE_URL; ?>?route=sale/view&id=<?php echo (int) $s['id']; ?>" target="_blank" class="text-slate-500 hover:text-slate-700 p-2 rounded-lg hover:bg-slate-100 transition-colors" title="Ver pedido">
                      <i class="fas fa-eye"></i>
                    </a>
                    <a href="<?php echo htmlspecialchars($unmarkUrl, ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex items-center gap-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-2 px-3 rounded-lg text-sm transition-colors" title="Desmarcar entrega">
                      <i class="fas fa-undo"></i> Desfazer
                    </a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
          <?php if (empty($delivered)): ?>
            <div class="queue-card bg-white rounded-xl border-2 border-dashed border-slate-200 p-8 text-center text-slate-400">
              <i class="fas fa-check-double text-4xl mb-2 opacity-50"></i>
              <p class="font-medium">Nenhum pedido entregue nesta data.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  .queue-card-preparation { border-left: 4px solid #f59e0b; }
  .queue-card-delivered  { border-left: 4px solid #10b981; }
  @media (min-width: 1024px) {
    .queue-page .grid { align-items: start; }
  }
</style>
<?php require 'views/layouts/footer.php'; ?>
