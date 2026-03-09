<?php require 'views/layouts/header.php'; ?>

<div class="flex flex-col gap-6">
<div>
    <h2 class="text-2xl font-bold text-gray-800">📋 Histórico de ações</h2>
    <p class="text-sm text-gray-500">Logs de login, vendas, cancelamentos, descontos e acessos negados.</p>
</div>

<div class="card-standard">
    <div class="card-standard-header"><i class="fas fa-filter"></i> Filtros</div>
    <div class="card-standard-body">
    <form method="GET" action="<?php echo BASE_URL; ?>" class="flex flex-wrap items-end gap-4">
        <input type="hidden" name="route" value="audit/index">
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">De</label>
            <input type="date" name="start_date" value="<?php echo htmlspecialchars($filters['start_date']); ?>"
                class="rounded-lg border-gray-200 shadow-sm p-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Até</label>
            <input type="date" name="end_date" value="<?php echo htmlspecialchars($filters['end_date']); ?>"
                class="rounded-lg border-gray-200 shadow-sm p-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Usuário</label>
            <select name="user_id" class="rounded-lg border-gray-200 shadow-sm p-2 text-sm min-w-[160px]">
                <option value="">Todos</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?php echo $u['id']; ?>" <?php echo (isset($filters['user_id']) && $filters['user_id'] == $u['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($u['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Ação</label>
            <select name="action" class="rounded-lg border-gray-200 shadow-sm p-2 text-sm min-w-[140px]">
                <option value="">Todas</option>
                <option value="login" <?php echo ($filters['action'] ?? '') === 'login' ? 'selected' : ''; ?>>Login</option>
                <option value="logout" <?php echo ($filters['action'] ?? '') === 'logout' ? 'selected' : ''; ?>>Logout</option>
                <option value="sale_create" <?php echo ($filters['action'] ?? '') === 'sale_create' ? 'selected' : ''; ?>>Venda</option>
                <option value="sale_cancel" <?php echo ($filters['action'] ?? '') === 'sale_cancel' ? 'selected' : ''; ?>>Cancelamento</option>
                <option value="access_denied" <?php echo ($filters['action'] ?? '') === 'access_denied' ? 'selected' : ''; ?>>Acesso negado</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Entidade</label>
            <select name="entity" class="rounded-lg border-gray-200 shadow-sm p-2 text-sm min-w-[120px]">
                <option value="">Todas</option>
                <option value="user" <?php echo ($filters['entity'] ?? '') === 'user' ? 'selected' : ''; ?>>Usuário</option>
                <option value="sale" <?php echo ($filters['entity'] ?? '') === 'sale' ? 'selected' : ''; ?>>Venda</option>
                <option value="route" <?php echo ($filters['entity'] ?? '') === 'route' ? 'selected' : ''; ?>>Rota</option>
            </select>
        </div>
        <button type="submit" class="bg-primary hover:bg-primary-hover text-white font-bold py-2 px-4 rounded-lg shadow transition-colors">
            Filtrar
        </button>
    </form>
    </div>
</div>

<div class="card-standard overflow-hidden">
    <div class="card-standard-header"><i class="fas fa-history"></i> Histórico de ações</div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data/Hora</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ação</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entidade</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detalhes</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">Nenhum registro encontrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log):
                        $actionLabel = [
                            'login' => 'Login',
                            'logout' => 'Logout',
                            'sale_create' => 'Venda',
                            'sale_cancel' => 'Cancelamento',
                            'access_denied' => 'Acesso negado',
                        ][$log['action']] ?? $log['action'];
                        $meta = $log['metadata_json'] ? json_decode($log['metadata_json'], true) : [];
                    ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($log['user_name'] ?? '—'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-xs font-bold
                                    <?php
                                    if ($log['action'] === 'sale_cancel' || $log['action'] === 'access_denied') echo 'bg-red-100 text-red-700';
                                    elseif ($log['action'] === 'sale_create') echo 'bg-green-100 text-green-700';
                                    elseif ($log['action'] === 'login') echo 'bg-blue-100 text-blue-700';
                                    else echo 'bg-gray-100 text-gray-700';
                                    ?>">
                                    <?php echo htmlspecialchars($actionLabel); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($log['entity']); ?>
                                <?php if (!empty($log['entity_id'])): ?>
                                    #<?php echo (int) $log['entity_id']; ?>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" title="<?php echo htmlspecialchars($log['metadata_json'] ?? ''); ?>">
                                <?php
                                if (!empty($meta)):
                                    $parts = [];
                                    if (!empty($meta['route'])) $parts[] = 'Rota: ' . $meta['route'];
                                    if (isset($meta['discount']) && $meta['discount'] > 0) $parts[] = 'Desconto: R$ ' . number_format($meta['discount'], 2, ',', '.');
                                    if (!empty($meta['payment_method'])) $parts[] = $meta['payment_method'];
                                    if (isset($meta['total'])) $parts[] = 'Total: R$ ' . number_format($meta['total'], 2, ',', '.');
                                    echo htmlspecialchars(implode(' · ', $parts));
                                else:
                                    echo '—';
                                endif;
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-400 font-mono">
                                <?php echo htmlspecialchars($log['ip'] ?? '—'); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div><!-- /.flex.flex-col.gap-6 -->

<?php require 'views/layouts/footer.php'; ?>
