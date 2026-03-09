<?php
/**
 * Partial: Alert/Toast reutilizável.
 * Variáveis: $type = 'info' (success|error|warning|info), $message, $dismissible = false
 */
$type = $type ?? 'info';
$message = $message ?? '';
$dismissible = $dismissible ?? false;
$alertClass = 'ui-alert ui-alert-' . (in_array($type, ['success', 'danger', 'warning', 'info'], true) ? $type : 'info');
?>
<div class="<?php echo e($alertClass); ?>" role="alert">
    <span><?php echo e($message); ?></span>
    <?php if ($dismissible): ?>
        <button type="button" class="btn-close ml-auto" onclick="this.parentElement.remove()" aria-label="Fechar">&times;</button>
    <?php endif; ?>
</div>
