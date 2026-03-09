<?php require 'views/layouts/header.php'; ?>

<div class="mb-6">
    <div class="flex justify-between items-start flex-wrap gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">🔐 Permissões por grupo</h2>
            <p class="text-sm text-gray-500 mt-1">Defina quais telas cada grupo pode acessar. Usuários herdam as permissões do seu grupo (cargo).</p>
        </div>
        <a href="<?php echo BASE_URL; ?>?route=user/index"
            class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-lg shadow transition flex items-center gap-2">
            <i class="fas fa-users"></i> Usuários e grupos
        </a>
    </div>
</div>

<?php if (isset($_GET['success']) && $_GET['success'] === 'updated'): ?>
    <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 flex items-center gap-3">
        <i class="fas fa-check-circle"></i>
        <span>Permissões salvas. Peça aos usuários que façam logout e login novamente para aplicar.</span>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'failed'): ?>
    <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 flex items-center gap-3">
        <i class="fas fa-exclamation-circle"></i>
        <span>Erro ao salvar. Tente novamente.</span>
    </div>
<?php endif; ?>

<!-- Resumo: usuários por grupo -->
<div class="card-standard mb-6">
    <div class="card-standard-header"><i class="fas fa-users"></i> Usuários por grupo</div>
    <div class="card-standard-body">
    <div class="flex flex-wrap gap-4">
        <?php foreach ($roles as $roleKey => $roleName):
            $list = $usersByRole[$roleKey] ?? [];
            $count = count($list);
        ?>
            <div class="flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-50 border border-gray-100">
                <span class="font-bold text-gray-700"><?php echo htmlspecialchars($roleName); ?></span>
                <span class="text-gray-500 text-sm"><?php echo $count; ?> usuário(s)</span>
                <?php if ($count > 0): ?>
                    <span class="text-xs text-gray-400">
                        <?php echo htmlspecialchars(implode(', ', array_slice(array_column($list, 'name'), 0, 3))); ?>
                        <?php if ($count > 3): ?>…<?php endif; ?>
                    </span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    </div>
</div>

<?php
$byModule = [];
foreach ($permissions as $p) {
    $mod = $modules[$p['key']] ?? 'Outros';
    if (!isset($byModule[$mod])) $byModule[$mod] = [];
    $byModule[$mod][] = $p;
}
ksort($byModule);
?>

<form method="POST" action="<?php echo htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8'); ?>?route=permission/update">
    <?php echo csrf_field(); ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
        <?php foreach ($roles as $roleKey => $roleName): ?>
            <div class="card-standard overflow-hidden">
                <div class="card-standard-header flex justify-between items-center">
                    <span><?php echo htmlspecialchars($roleName); ?></span>
                    <div class="flex gap-2">
                        <button type="button" class="text-xs font-bold text-primary hover:underline select-all-perms" data-role="<?php echo htmlspecialchars($roleKey); ?>">Marcar todas</button>
                        <span class="text-gray-300">|</span>
                        <button type="button" class="text-xs font-bold text-gray-500 hover:underline deselect-all-perms" data-role="<?php echo htmlspecialchars($roleKey); ?>">Desmarcar</button>
                    </div>
                </div>
                <div class="p-6 max-h-[480px] overflow-y-auto">
                    <?php foreach ($byModule as $modName => $perms): ?>
                        <div class="mb-6">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2"><?php echo htmlspecialchars($modName); ?></h4>
                            <div class="space-y-2">
                                <?php foreach ($perms as $p):
                                    $checked = in_array((int) $p['id'], $rolePerms[$roleKey] ?? [], true);
                                ?>
                                    <label class="flex items-start gap-2 p-2 rounded-lg hover:bg-gray-50 cursor-pointer group">
                                        <input type="checkbox" name="perms[<?php echo htmlspecialchars($roleKey); ?>][]"
                                            value="<?php echo (int) $p['id']; ?>"
                                            class="perm-cb mt-1 rounded border-gray-300 text-primary focus:ring-primary"
                                            data-role="<?php echo htmlspecialchars($roleKey); ?>"
                                            <?php echo $checked ? ' checked' : ''; ?>>
                                        <div>
                                            <span class="text-sm font-medium text-gray-800 group-hover:text-gray-900"><?php echo htmlspecialchars($p['name']); ?></span>
                                            <?php if (!empty($p['description'])): ?>
                                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($p['description']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-6 flex gap-4">
        <button type="submit" class="bg-primary hover:bg-primary-hover text-white font-bold py-3 px-8 rounded-lg shadow transition flex items-center gap-2">
            <i class="fas fa-save"></i> Salvar permissões
        </button>
        <a href="<?php echo BASE_URL; ?>?route=user/index" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 px-8 rounded-lg transition">
            Cancelar
        </a>
    </div>
</form>

<script>
document.querySelectorAll('.select-all-perms').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var role = this.dataset.role;
        document.querySelectorAll('.perm-cb[data-role="' + role + '"]').forEach(function (cb) { cb.checked = true; });
    });
});
document.querySelectorAll('.deselect-all-perms').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var role = this.dataset.role;
        document.querySelectorAll('.perm-cb[data-role="' + role + '"]').forEach(function (cb) { cb.checked = false; });
    });
});
</script>

<?php require 'views/layouts/footer.php'; ?>
