<?php
$flash = get_flash();
if ($flash):
    $type = $flash['type'] ?? 'info';
    $message = $flash['message'] ?? '';
    $toastClass = 'toast toast-' . (in_array($type, ['success', 'error', 'warning', 'info'], true) ? $type : 'info');
?>
<div class="toast-container" role="alert" aria-live="polite">
  <div class="<?php echo htmlspecialchars($toastClass, ENT_QUOTES, 'UTF-8'); ?>">
    <span><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></span>
  </div>
</div>
<?php endif; ?>
