<?php require 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>📊 Fechamento de Caixa</h2>
    <form class="d-flex align-items-center">
        <a href="?route=report/daily&date=<?php echo date('Y-m-d', strtotime($date . ' -1 day')); ?>"
            class="btn btn-ghost bg-white hover:bg-gray-100 text-gray-400 btn-sm rounded-lg border border-gray-100 shadow-sm me-2">
            <i class="fas fa-chevron-left"></i>
        </a>
        <input type="date" name="date" class="rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50" value="<?php echo $date; ?>" onchange="this.form.submit()">
        <a href="?route=report/daily&date=<?php echo date('Y-m-d', strtotime($date . ' +1 day')); ?>"
            class="btn btn-ghost bg-white hover:bg-gray-100 text-gray-400 btn-sm rounded-lg border border-gray-200 shadow-sm ms-2 me-2">
            <i class="fas fa-chevron-right"></i>
        </a>
        <input type="hidden" name="route" value="report/daily">
        <button type="submit"
            class="btn btn-primary rounded-lg shadow-md font-black transition-all active:scale-95 btn-sm">Filtrar</button>
    </form>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white shadow-sm">
            <div class="card-body">
                <h5>Total Vendido</h5>
                <h2>R$ <?php echo number_format($summary['total'] ?? 0, 2, ',', '.'); ?></h2>
                <small><?php echo $summary['count']; ?> vendas</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white shadow-sm">
            <div class="card-body">
                <h5>Ticket Médio</h5>
                <?php
                $avg = $summary['count'] > 0 ? $summary['total'] / $summary['count'] : 0;
                ?>
                <h2>R$ <?php echo number_format($avg, 2, ',', '.'); ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-header">Por Forma de Pagamento</div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php foreach ($byMethod as $m): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo $m['payment_method']; ?>
                            <span>
                                <span class="badge bg-secondary rounded-pill me-2"><?php echo $m['count']; ?></span>
                                R$ <?php echo number_format($m['total'], 2, ',', '.'); ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-header">Últimas Vendas</div>
            <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-sm table-striped mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Hora</th>
                            <th>Vendedor</th>
                            <th class="text-end">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sales as $s): ?>
                            <tr>
                                <td><?php echo $s['id']; ?></td>
                                <td><?php echo date('H:i', strtotime($s['created_at'])); ?></td>
                                <td><?php echo $s['user_name']; ?></td>
                                <td class="text-end">R$ <?php echo number_format($s['total'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>