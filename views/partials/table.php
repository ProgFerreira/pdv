<?php
/**
 * Partial: Tabela reutilizável.
 * Variáveis esperadas: $headers (array de strings), $rows (array de arrays de células), $actions (opcional, callable(row) ou false)
 */
$headers = $headers ?? [];
$rows = $rows ?? [];
$actions = $actions ?? null;
$tableClass = $tableClass ?? 'ui-table ui-table-hover';
?>
<div class="ui-table-wrap">
    <table class="<?php echo e($tableClass); ?>">
        <thead>
            <tr>
                <?php foreach ($headers as $th): ?>
                    <th><?php echo e($th); ?></th>
                <?php endforeach; ?>
                <?php if ($actions !== null && $actions !== false): ?>
                    <th class="text-right">Ações</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <?php foreach ($row as $cell): ?>
                        <td><?php echo is_array($cell) && isset($cell['html']) ? $cell['html'] : e((string) $cell); ?></td>
                    <?php endforeach; ?>
                    <?php if ($actions !== null && $actions !== false && is_callable($actions)): ?>
                        <td class="text-right"><?php echo $actions($row); ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
