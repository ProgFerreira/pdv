<?php
$investment = $investment ?? null;
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
$dataVal = $investment && !empty($investment['data']) ? $investment['data'] : date('Y-m-d');
$valorVal = $investment ? (float) $investment['valor'] : '';
$pessoaVal = $investment ? ($investment['pessoa'] ?? '') : '';
$participantIdVal = $investment ? ($investment['participant_id'] ?? '') : '';
$produtoVal = $investment ? ($investment['produto'] ?? '') : '';
$tipoVal = $investment ? ($investment['tipo'] ?? 'aporte') : 'aporte';
$finalidadeVal = $investment ? ($investment['finalidade'] ?? '') : '';
$estadoVal = $investment ? ($investment['estado'] ?? '') : '';
$documentoVal = $investment ? ($investment['documento_numero'] ?? '') : '';
$quantidadeVal = $investment ? (int) ($investment['quantidade'] ?? 1) : 1;
$dataDevolucaoVal = $investment && !empty($investment['data_devolucao_prevista']) ? $investment['data_devolucao_prevista'] : '';
$formaPagamentoVal = $investment ? ($investment['forma_pagamento'] ?? '') : '';
$categoriaAtivoVal = $investment ? ($investment['categoria_ativo'] ?? '') : '';
$observacoesVal = $investment ? ($investment['observacoes'] ?? '') : '';
$inputClass = 'w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50';
$selectClass = 'w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white';
$baseUrl = BASE_URL ?? '';
?>
<?php require 'views/layouts/header.php'; ?>

<div class="w-[95vw] max-w-full mx-auto">
    <div class="card-standard overflow-hidden">
        <div class="card-standard-header flex items-center justify-between flex-wrap gap-2">
            <span><i class="fas fa-edit mr-2"></i>Alterar Registro Financeiro</span>
            <a href="<?php echo e($baseUrl); ?>?route=investment/index&tab=financeiro" class="btn btn-ghost btn-sm text-gray-500 hover:text-gray-700 no-underline">
                <i class="fas fa-arrow-left mr-1"></i> Voltar
            </a>
        </div>
        <div class="card-standard-body">
            <form action="<?php echo e($baseUrl); ?>?route=investment/update" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="id" value="<?php echo (int) ($investment['id'] ?? 0); ?>">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data <span class="text-red-500">*</span></label>
                        <input type="date" name="data" required value="<?php echo e($dataVal); ?>" class="<?php echo $inputClass; ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valor (R$) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0" name="valor" required value="<?php echo $valorVal !== '' ? number_format($valorVal, 2, '.', '') : ''; ?>" placeholder="0,00" class="<?php echo $inputClass; ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                        <select name="tipo" required class="<?php echo $selectClass; ?>">
                            <?php foreach ($tiposLabels as $k => $l): ?>
                                <option value="<?php echo e($k); ?>" <?php echo ($tipoVal === $k) ? 'selected' : ''; ?>><?php echo e($l); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Finalidade</label>
                        <select name="finalidade" class="<?php echo $selectClass; ?>">
                            <option value="">—</option>
                            <?php foreach ($finalidadeLabels as $k => $l): ?>
                                <option value="<?php echo e($k); ?>" <?php echo ($finalidadeVal === $k) ? 'selected' : ''; ?>><?php echo e($l); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Participante</label>
                        <select name="participant_id" class="<?php echo $selectClass; ?>">
                            <option value="">—</option>
                            <?php foreach ($participants as $p): ?>
                                <option value="<?php echo (int)($p['id'] ?? 0); ?>" <?php echo ($participantIdVal == ($p['id'] ?? '')) ? 'selected' : ''; ?>><?php echo e($p['name'] ?? ''); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pessoa (nome livre)</label>
                        <input type="text" name="pessoa" list="list-pessoas-edit" value="<?php echo e($pessoaVal); ?>" class="<?php echo $inputClass; ?>">
                        <datalist id="list-pessoas-edit">
                            <?php foreach ($pessoas as $p): ?><option value="<?php echo e($p); ?>"><?php endforeach; ?>
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado (equipamento)</label>
                        <select name="estado" class="<?php echo $selectClass; ?>">
                            <option value="">—</option>
                            <?php foreach ($estadosLabels as $k => $l): ?>
                                <option value="<?php echo htmlspecialchars($k, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($estadoVal === $k) ? 'selected' : ''; ?>><?php echo htmlspecialchars($l, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Documento / Nº (nota, recibo, contrato)</label>
                        <input type="text" name="documento_numero" placeholder="Opcional"
                            value="<?php echo e($documentoVal); ?>"
                            class="<?php echo $inputClass; ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade</label>
                        <input type="number" min="1" name="quantidade" value="<?php echo $quantidadeVal; ?>"
                            class="<?php echo $inputClass; ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Forma de pagamento</label>
                        <select name="forma_pagamento" class="<?php echo $selectClass; ?>">
                            <option value="">—</option>
                            <option value="À vista" <?php echo $formaPagamentoVal === 'À vista' ? 'selected' : ''; ?>>À vista</option>
                            <option value="Parcelado" <?php echo $formaPagamentoVal === 'Parcelado' ? 'selected' : ''; ?>>Parcelado</option>
                            <option value="PIX" <?php echo $formaPagamentoVal === 'PIX' ? 'selected' : ''; ?>>PIX</option>
                            <option value="Transferência" <?php echo $formaPagamentoVal === 'Transferência' ? 'selected' : ''; ?>>Transferência</option>
                            <option value="Dinheiro" <?php echo $formaPagamentoVal === 'Dinheiro' ? 'selected' : ''; ?>>Dinheiro</option>
                            <option value="Outro" <?php echo $formaPagamentoVal === 'Outro' ? 'selected' : ''; ?>>Outro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Categoria do ativo</label>
                        <select name="categoria_ativo" class="<?php echo $selectClass; ?>">
                            <option value="">—</option>
                            <option value="Equipamento" <?php echo $categoriaAtivoVal === 'Equipamento' ? 'selected' : ''; ?>>Equipamento</option>
                            <option value="Móvel" <?php echo $categoriaAtivoVal === 'Móvel' ? 'selected' : ''; ?>>Móvel</option>
                            <option value="Veículo" <?php echo $categoriaAtivoVal === 'Veículo' ? 'selected' : ''; ?>>Veículo</option>
                            <option value="Dinheiro" <?php echo $categoriaAtivoVal === 'Dinheiro' ? 'selected' : ''; ?>>Dinheiro</option>
                            <option value="Eletrônico" <?php echo $categoriaAtivoVal === 'Eletrônico' ? 'selected' : ''; ?>>Eletrônico</option>
                            <option value="Outro" <?php echo $categoriaAtivoVal === 'Outro' ? 'selected' : ''; ?>>Outro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data devolução prevista <span class="text-gray-400 text-xs">(empréstimos)</span></label>
                        <input type="date" name="data_devolucao_prevista" value="<?php echo e($dataDevolucaoVal); ?>"
                            class="<?php echo $inputClass; ?>">
                    </div>
                    <div class="lg:col-span-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Produto / Equipamento (descrição)</label>
                        <input type="text" name="produto"
                            value="<?php echo e($produtoVal); ?>"
                            placeholder="Ex: Máquina de café, Mesa, Aporte em dinheiro"
                            class="<?php echo $inputClass; ?>">
                    </div>
                    <div class="lg:col-span-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                        <textarea name="observacoes" rows="3" placeholder="Notas, prazos, condições..."
                            class="<?php echo $inputClass; ?>"><?php echo e($observacoesVal); ?></textarea>
                    </div>
                </div>
                <div class="mt-8 pt-6 border-t border-gray-100 flex gap-4">
                    <button type="submit" class="btn bg-indigo-600 hover:bg-indigo-700 border-none text-white rounded-xl shadow-md font-black">
                        <i class="fas fa-save mr-2"></i> Salvar Alterações
                    </button>
                    <a href="<?php echo e($baseUrl); ?>?route=investment/index&tab=financeiro" class="btn btn-ghost text-gray-600">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>
