<?php
/**
 * Partial: Input reutilizável.
 * Variáveis: $name, $label, $type = 'text', $value = '', $error = null, $attributes = [] (placeholder, required, etc.)
 */
$name = $name ?? '';
$label = $label ?? '';
$type = $type ?? 'text';
$value = $value ?? '';
$error = $error ?? null;
$attributes = $attributes ?? [];
$id = $id ?? 'input-' . preg_replace('/[^a-z0-9_]/', '-', $name);
$inputClass = 'ui-input' . ($error ? ' is-invalid' : '');
$attrStr = '';
foreach ($attributes as $k => $v) {
    $attrStr .= ' ' . e($k) . '="' . e($v) . '"';
}
?>
<div class="mb-4">
    <?php if ($label !== ''): ?>
        <label for="<?php echo e($id); ?>" class="ui-label"><?php echo e($label); ?></label>
    <?php endif; ?>
    <input type="<?php echo e($type); ?>"
           id="<?php echo e($id); ?>"
           name="<?php echo e($name); ?>"
           value="<?php echo e($value); ?>"
           class="<?php echo e($inputClass); ?>"
           <?php echo $attrStr; ?>>
    <?php if ($error): ?>
        <p class="ui-error"><?php echo e($error); ?></p>
    <?php endif; ?>
</div>
