<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php if (isset($_SESSION['user_id']) && function_exists('csrf_token')): ?>
  <meta name="csrf-token" content="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
  <?php endif; ?>
  <title>PDV - Artigos Religiosos</title>

  <!-- CSS buildado (Tailwind + tema); sem CDN para consistência e menor peso -->
  <link href="<?php echo BASE_URL; ?>public/css/tailwind.css" rel="stylesheet">
  <link href="<?php echo BASE_URL; ?>public/css/sistema-premium.css" rel="stylesheet">
  <link href="<?php echo BASE_URL; ?>public/css/exported_styles.css" rel="stylesheet">

  <!-- Google Fonts: Plus Jakarta Sans -->
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet">

  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

  <!-- Alpine.js 3.14 (garante @click.outside para fechar dropdowns) -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

  <style>
    [x-cloak] { display: none !important; }
    body { font-weight: 400 !important; }
    body * { font-weight: 400; }
    .js-dropdown-panel { display: none !important; }
    .js-dropdown-panel.is-open { display: block !important; }
  </style>
</head>

<body class="layout-modern text-gray-800 antialiased min-h-screen flex flex-col overflow-x-hidden">

  <?php
  $flashPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'flash.php';
  if (is_file($flashPath)) {
      include $flashPath;
  }
  ?>

  <?php
  $currentSectorName = $currentSectorName ?? 'Indefinido';
  $allSectors = $allSectors ?? [];
  $currentSectorId = $currentSectorId ?? 1;
  ?>
  <?php if (isset($_SESSION['user_id'])): ?>
    <?php $GLOBALS['layout_has_sidebar'] = true; ?>
    <div id="wrapper" class="flex flex-1 min-h-0 w-full <?php echo !empty($is_pos_page) ? 'pos-page' : ''; ?>">
      <div class="sidebar-overlay no-print" id="sidebar-overlay" aria-hidden="true"></div>
      <aside id="sidebar" class="bg-dark flex flex-col">
        <div class="sidebar-header flex items-center justify-between px-3 py-3">
          <a href="<?php echo BASE_URL; ?>" class="no-underline flex items-center gap-2 text-white font-bold">
            <i class="fas fa-bolt"></i>
            <span>PDV PREMIUM</span>
          </a>
          <button type="button" id="sidebar-toggle" class="p-1 border-0 bg-transparent text-white opacity-75 hover:opacity-100" title="Recolher/Expandir menu" aria-label="Menu">
            <i class="fas fa-bars"></i>
          </button>
        </div>
        <small class="block px-3 pb-2 text-white opacity-75"><?php echo htmlspecialchars($currentSectorName, ENT_QUOTES, 'UTF-8'); ?></small>
        <?php if (isAdmin() && !empty($allSectors)): ?>
          <div class="px-3 pb-2">
            <a href="?route=auth/switchSector&id=all" class="nav-link py-1 text-sm block <?php echo ($currentSectorId === 'all') ? 'text-white font-bold' : 'text-white opacity-75'; ?>">🌐 Visão Global</a>
            <?php foreach ($allSectors as $sec): ?>
              <?php $secActive = ($currentSectorId != 'all' && (string)($sec['id'] ?? '') === (string)$currentSectorId); ?>
              <a href="?route=auth/switchSector&id=<?php echo (int)($sec['id'] ?? 0); ?>" class="nav-link py-1 text-sm block <?php echo $secActive ? 'text-white font-bold' : 'text-white opacity-75'; ?>"><?php echo htmlspecialchars($sec['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <nav class="nav flex flex-col py-2">
          <?php if (hasPermission('dashboard')): ?><a href="<?php echo BASE_URL; ?>?route=dashboard/index" class="nav-link"><i class="fas fa-tachometer-alt mr-2"></i>Dashboard</a><?php endif; ?>
          <?php if (hasPermission('pos')): ?><a href="<?php echo BASE_URL; ?>?route=pos/index" class="nav-link"><i class="fas fa-cash-register mr-2"></i>PDV</a><?php endif; ?>
          <?php if (hasPermission('sale_view')): ?><a href="<?php echo BASE_URL; ?>?route=sale/index" class="nav-link"><i class="fas fa-shopping-cart mr-2"></i>Vendas</a><?php endif; ?>
          <?php if (hasPermission('receivable')): ?><a href="<?php echo BASE_URL; ?>?route=receivable/index" class="nav-link"><i class="fas fa-hand-holding-usd mr-2"></i>Contas a Receber</a><?php endif; ?>
          <a href="?route=payable/index" class="nav-link"><i class="fas fa-file-invoice-dollar mr-2"></i>Contas a Pagar</a>
          <?php if (hasPermission('cash')): ?><a href="?route=cash/history" class="nav-link"><i class="fas fa-chart-line mr-2"></i>Fluxo de Caixa</a><?php endif; ?>
          <?php if (hasPermission('investment_manage')): ?><a href="<?php echo BASE_URL; ?>?route=investment/index" class="nav-link"><i class="fas fa-coins mr-2"></i>Investimentos</a><?php endif; ?>
          <?php if (hasPermission('customer')): ?><a href="<?php echo BASE_URL; ?>?route=customer/index" class="nav-link"><i class="fas fa-users mr-2"></i>Clientes</a><?php endif; ?>
          <?php if (hasPermission('product')): ?><a href="<?php echo BASE_URL; ?>?route=product/index" class="nav-link"><i class="fas fa-box mr-2"></i>Produtos</a><?php endif; ?>
          <?php if (hasPermission('product')): ?><a href="<?php echo BASE_URL; ?>?route=ingredient/index" class="nav-link"><i class="fas fa-flask mr-2"></i>Insumos (Ficha Técnica)</a><?php endif; ?>
          <?php if (hasPermission('stock')): ?><a href="<?php echo BASE_URL; ?>?route=stock/index" class="nav-link"><i class="fas fa-warehouse mr-2"></i>Estoque</a><?php endif; ?>
          <?php if (hasPermission('category')): ?><a href="<?php echo BASE_URL; ?>?route=category/index" class="nav-link"><i class="fas fa-tags mr-2"></i>Categorias</a><?php endif; ?>
          <?php if (hasPermission('brand')): ?><a href="<?php echo BASE_URL; ?>?route=brand/index" class="nav-link"><i class="fas fa-copyright mr-2"></i>Marcas</a><?php endif; ?>
          <?php if (hasPermission('giftcard')): ?><a href="<?php echo BASE_URL; ?>?route=giftcard/index" class="nav-link"><i class="fas fa-gift mr-2"></i>Vales Presente</a><?php endif; ?>
          <?php if (hasPermission('report')): ?><a href="<?php echo BASE_URL; ?>?route=report/index" class="nav-link"><i class="fas fa-chart-bar mr-2"></i>Relatórios</a><?php endif; ?>
          <?php if (hasPermission('product')): ?><a href="<?php echo BASE_URL; ?>?route=supplier/index" class="nav-link"><i class="fas fa-truck mr-2"></i>Fornecedores</a><a href="<?php echo BASE_URL; ?>?route=import/products" class="nav-link"><i class="fas fa-file-import mr-2"></i>Importar Produtos</a><a href="<?php echo BASE_URL; ?>?route=label/index" class="nav-link"><i class="fas fa-tag mr-2"></i>Etiqueta 15x10</a><?php endif; ?>
          <?php if (hasPermission('user')): ?><a href="<?php echo BASE_URL; ?>?route=user/index" class="nav-link"><i class="fas fa-user-cog mr-2"></i>Usuários</a><?php endif; ?>
          <?php if (hasPermission('permission_manage')): ?><a href="<?php echo BASE_URL; ?>?route=permission/index" class="nav-link"><i class="fas fa-key mr-2"></i>Permissões</a><?php endif; ?>
          <?php if (hasPermission('audit')): ?><a href="<?php echo BASE_URL; ?>?route=audit/index" class="nav-link"><i class="fas fa-history mr-2"></i>Histórico de ações</a><?php endif; ?>
        </nav>
        <?php if (hasPermission('cash')): $cashModelHeader = new \App\Models\CashRegister(); $openRegHeader = $cashModelHeader->getOpenRegister($_SESSION['user_id']); ?>
          <div class="px-3 py-2 border-t border-gray-600 space-y-1">
            <a href="<?php echo $openRegHeader ? '?route=cash/close' : '?route=cash/history'; ?>" class="nav-link py-2 text-sm flex items-center gap-2 <?php echo $openRegHeader ? 'text-green-400 font-bold' : 'text-red-400 hover:text-red-300'; ?>">
              <i class="fas fa-cash-register"></i>
              <span>Caixa <?php echo $openRegHeader ? 'Aberto' : 'Fechado'; ?></span>
            </a>
            <?php if (!$openRegHeader): ?>
              <a href="<?php echo BASE_URL; ?>?route=pos/index" class="nav-link py-1 text-sm flex items-center gap-2 text-red-400 hover:text-red-300">
                <i class="fas fa-lock-open"></i>
                <span>Abrir caixa</span>
              </a>
            <?php endif; ?>
          </div>
        <?php endif; ?>
        <div class="px-3 py-2 mt-auto border-t border-gray-600">
          <span class="text-white opacity-75 text-sm block"><?php echo e($_SESSION['user_name'] ?? 'Usuário'); ?></span>
          <a href="<?php echo BASE_URL; ?>?route=auth/logout" class="nav-link py-1 text-red-400 hover:text-red-300 text-sm"><i class="fas fa-sign-out-alt mr-2"></i>Sair</a>
        </div>
      </aside>
      <div id="page-content" class="flex flex-col min-h-0 flex-1 overflow-auto">
        <header class="md:hidden no-print border-b border-gray-200 bg-white px-4 py-2 flex items-center gap-2">
          <button type="button" onclick="history.back();" class="p-2 rounded border border-gray-300 text-gray-600 hover:bg-gray-50" aria-label="Voltar" title="Voltar"><i class="fas fa-arrow-left"></i></button>
          <button type="button" id="sidebar-open-btn" class="p-2 rounded border border-gray-300 text-gray-600 hover:bg-gray-50" aria-label="Abrir menu"><i class="fas fa-bars"></i></button>
          <span class="font-bold text-gray-800">PDV</span>
        </header>
        <main class="flex-1 <?php echo !empty($is_pos_page) ? 'pos-main p-0 max-w-none w-full min-h-0 flex items-center justify-center' : 'main-content-90 px-4 sm:px-6 py-6'; ?>">
  <?php else: ?>
        <main class="flex-1 main-content-90 px-4 sm:px-6 py-6">
  <?php endif; ?>