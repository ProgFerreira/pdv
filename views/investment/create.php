<?php
$pessoas = $pessoas ?? [];
$participants = $participants ?? [];
$tiposLabels = [
    'aporte' => 'Aporte (capital)',
    'emprestimo' => 'Empréstimo (dívida)',
    'doacao' => 'Doação',
    'aporte_socio' => 'Aporte de Sócio',
    'investimento_dinheiro' => 'Investimento em Dinheiro',
    'compra' => 'Compra',
];
$finalidadeLabels = ['investimento_inicial' => 'Investimento inicial', 'capital_giro' => 'Capital de giro', 'compra_equipamento' => 'Compra de equipamento'];
$estadosLabels = ['novo' => 'Novo', 'usado' => 'Usado'];
$inputClass = 'w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50';
$selectClass = 'w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white';
$baseUrl = BASE_URL ?? '';
?>
<?php require 'views/layouts/header.php'; ?>

<div class="w-[95vw] max-w-full mx-auto">
    <div class="card-standard overflow-hidden">
        <div class="card-standard-header flex items-center justify-between flex-wrap gap-2">
            <span><i class="fas fa-plus mr-2"></i>Novo Registro Financeiro</span>
            <a href="<?php echo e($baseUrl); ?>?route=investment/index&tab=financeiro" class="btn btn-ghost btn-sm text-gray-500 hover:text-gray-700 no-underline">
                <i class="fas fa-arrow-left mr-1"></i> Voltar
            </a>
        </div>
        <div class="card-standard-body">
            <form action="<?php echo e($baseUrl); ?>?route=investment/store" method="POST">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data <span class="text-red-500">*</span></label>
                        <input type="date" name="data" required value="<?php echo date('Y-m-d'); ?>"
                            class="<?php echo $inputClass; ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valor (R$) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0" name="valor" required placeholder="0,00"
                            class="<?php echo $inputClass; ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                        <select name="tipo" required class="<?php echo $selectClass; ?>">
                            <?php foreach ($tiposLabels as $k => $l): ?>
                                <option value="<?php echo e($k); ?>"><?php echo e($l); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Finalidade <span class="text-gray-400 text-xs">(aporte)</span></label>
                        <select name="finalidade" class="<?php echo $selectClass; ?>">
                            <option value="">—</option>
                            <?php foreach ($finalidadeLabels as $k => $l): ?>
                                <option value="<?php echo e($k); ?>"><?php echo e($l); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Participante</label>
                        <select name="participant_id" class="<?php echo $selectClass; ?>">
                            <option value="">— Ou digite abaixo</option>
                            <?php foreach ($participants as $p): ?>
                                <option value="<?php echo (int)($p['id'] ?? 0); ?>"><?php echo e($p['name'] ?? ''); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pessoa (se não escolheu participante)</label>
                        <input type="text" name="pessoa" list="list-pessoas-create" placeholder="Nome livre"
                            class="<?php echo $inputClass; ?>">
                        <datalist id="list-pessoas-create">
                            <?php foreach ($pessoas as $p): ?>
                                <option value="<?php echo e($p); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado (equipamento)</label>
                        <select name="estado" class="<?php echo $selectClass; ?>">
                            <option value="">—</option>
                            <?php foreach ($estadosLabels as $k => $l): ?>
                                <option value="<?php echo htmlspecialchars($k, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($l, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Documento / Nº (nota, recibo, contrato)</label>
                        <input type="text" name="documento_numero" placeholder="Opcional"
                            class="<?php echo $inputClass; ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade</label>
                        <input type="number" min="1" name="quantidade" value="1"
                            class="<?php echo $inputClass; ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Forma de pagamento</label>
                        <select name="forma_pagamento" class="<?php echo $selectClass; ?>">
                            <option value="">—</option>
                            <option value="À vista">À vista</option>
                            <option value="Parcelado">Parcelado</option>
                            <option value="PIX">PIX</option>
                            <option value="Transferência">Transferência</option>
                            <option value="Dinheiro">Dinheiro</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Categoria do ativo</label>
                        <select name="categoria_ativo" class="<?php echo $selectClass; ?>">
                            <option value="">—</option>
                            <option value="Equipamento">Equipamento</option>
                            <option value="Móvel">Móvel</option>
                            <option value="Veículo">Veículo</option>
                            <option value="Dinheiro">Dinheiro</option>
                            <option value="Eletrônico">Eletrônico</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data devolução prevista <span class="text-gray-400 text-xs">(empréstimos)</span></label>
                        <input type="date" name="data_devolucao_prevista"
                            class="<?php echo $inputClass; ?>">
                    </div>
                    <div class="lg:col-span-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Produto / Equipamento (descrição)</label>
                        <input type="text" name="produto" placeholder="Ex: Máquina de café, Mesa, Aporte em dinheiro"
                            class="<?php echo $inputClass; ?>">
                    </div>
                    <div class="lg:col-span-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                        <textarea name="observacoes" rows="3" placeholder="Notas, prazos, condições..."
                            class="<?php echo $inputClass; ?>"></textarea>
                    </div>
                </div>
                <div class="mt-8 pt-6 border-t border-gray-100 flex gap-4">
                    <button type="submit" class="btn bg-indigo-600 hover:bg-indigo-700 border-none text-white rounded-xl shadow-md font-black">
                        <i class="fas fa-save mr-2"></i> Salvar Lançamento
                    </button>
                    <a href="<?php echo e($baseUrl); ?>?route=investment/index&tab=financeiro" class="btn btn-ghost text-gray-600">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>
