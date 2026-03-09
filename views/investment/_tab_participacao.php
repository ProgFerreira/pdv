<?php
// Quadro societário: % = total_aportes_pessoa / total_aportes_geral (apenas aportes)
?>
<h3 class="text-lg font-black text-gray-800 mb-4">Quadro de Participação Societária</h3>
<p class="text-sm text-gray-500 mb-4">Percentual calculado apenas sobre <strong>aportes</strong> (doações e empréstimos não entram).</p>

<div class="overflow-x-auto mb-10">
    <table class="table table-compact w-full max-w-2xl">
        <thead>
            <tr class="text-[10px] text-gray-400 uppercase tracking-widest bg-gray-50/50">
                <th class="py-3 px-3">Participante</th>
                <th class="px-3 text-right">Total aportado</th>
                <th class="px-3 text-right">% Participação</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php if (empty($participacao)): ?>
                <tr>
                    <td colspan="3" class="text-center py-8 text-gray-400">Nenhum aporte registrado ainda. Cadastre registros do tipo &quot;Aporte&quot; na aba Financeiro.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($participacao as $p): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-3 font-bold text-gray-800"><?php echo e($p['pessoa_nome']); ?></td>
                        <td class="px-3 text-right font-bold text-gray-800">R$ <?php echo number_format($p['total_aportado'], 2, ',', '.'); ?></td>
                        <td class="px-3 text-right">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-indigo-100 text-indigo-800"><?php echo number_format($p['percentual'], 1, ',', '.'); ?>%</span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<h3 class="text-lg font-black text-gray-800 mb-4">Participantes cadastrados</h3>
<p class="text-sm text-gray-500 mb-4">Pessoas que podem ser vinculadas a aportes, empréstimos, doações e bens.</p>

<div class="overflow-x-auto">
    <table class="table table-compact w-full">
        <thead>
            <tr class="text-[10px] text-gray-400 uppercase tracking-widest bg-gray-50/50">
                <th class="py-3 px-3">Nome</th>
                <th class="px-3">Contato</th>
                <th class="px-3">Documento</th>
                <th class="text-right px-3">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php if (empty($participantsList)): ?>
                <tr>
                    <td colspan="4" class="text-center py-8 text-gray-400">
                        <p class="mb-2">Nenhum participante cadastrado.</p>
                        <a href="<?php echo e($baseUrl); ?>?route=investment/participantCreate" class="text-indigo-600 font-bold hover:underline">Cadastrar primeiro participante</a>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($participantsList as $p): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-3 font-bold text-gray-800"><?php echo e($p['name'] ?? ''); ?></td>
                        <td class="px-3 text-sm text-gray-600"><?php echo e($p['contact'] ?? '—'); ?></td>
                        <td class="px-3 text-sm text-gray-600"><?php echo e($p['document'] ?? '—'); ?></td>
                        <td class="text-right px-3">
                            <a href="<?php echo e($baseUrl); ?>?route=investment/participantEdit&id=<?php echo (int)($p['id'] ?? 0); ?>" class="btn btn-ghost btn-xs text-gray-400 hover:text-indigo-600" title="Editar"><i class="fas fa-edit"></i></a>
                            <form action="?route=investment/participantDelete" method="POST" class="inline" onsubmit="return confirm('Excluir este participante?');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="id" value="<?php echo (int)($p['id'] ?? 0); ?>">
                                <button type="submit" class="btn btn-ghost btn-xs text-gray-400 hover:text-red-600" title="Excluir"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
