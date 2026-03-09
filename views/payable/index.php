<?php require 'views/layouts/header.php'; ?>

<div class="flex flex-col gap-6" x-data="{ showModal: false, selectedPayable: null }">
    <?php if (!empty($_GET['success'])): ?>
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl flex items-center gap-2">
            <i class="fas fa-check-circle"></i>
            <span><?php echo $_GET['success'] === '1' ? 'Conta a pagar salva com sucesso.' : 'Pagamento registrado.'; ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($_GET['error'])): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl flex items-center gap-2">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php
                $err = $_GET['error'];
                echo $err === 'categoria' ? 'Selecione uma categoria.' : ($err === 'descricao' ? 'Informe a descrição.' : 'Erro ao salvar. Tente novamente.');
            ?></span>
        </div>
    <?php endif; ?>
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-800">Contas a Pagar</h1>
            <p class="text-sm text-gray-400">Gerencie suas despesas e pagamentos a fornecedores. <span class="text-gray-300" title="Competência: para DRE e relatórios por período. Vencimento/pagamento: para fluxo de caixa.">Competência × Caixa</span></p>
        </div>
        <div class="flex gap-2 w-full md:w-auto">
            <button type="button"
                class="inline-flex items-center gap-2 btn btn-ghost bg-gray-50 hover:bg-gray-100 text-gray-400 btn-sm shadow-sm rounded-xl border border-gray-100"
                onclick="window.location.reload()">
                <i class="fas fa-sync-alt"></i><span>Atualizar</span>
            </button>
        </div>
    </div>

    <!-- Form Novo Lançamento (sempre visível na página) -->
    <div class="card-standard overflow-hidden">
        <div class="card-standard-header">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>Novo lançamento de despesa</span>
        </div>
        <div class="card-standard-body">
        <p class="text-gray-500 text-xs mb-4">Preencha os detalhes da conta para controle financeiro.</p>
        <form action="?route=payable/store" method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>

            <div class="bg-gray-50/50 p-4 rounded-2xl border border-gray-100 space-y-4">
                <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">1. Identificação</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div class="lg:col-span-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição *</label>
                        <input type="text" name="descricao" required placeholder="Ex: Aluguel, Luz, Fornecedor..."
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50">
                    </div>
                    <div class="lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fornecedor</label>
                        <select name="supplier_id" class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white">
                            <option value="">Selecione...</option>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?php echo (int) $s['id']; ?>"><?php echo htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Categoria (Plano de Contas) *</label>
                        <select name="categoria_id" required class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white">
                            <option value="">Selecione...</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo (int) $cat['id']; ?>"><?php echo htmlspecialchars($cat['nome'], ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nº Documento / NF</label>
                        <input type="text" name="numero_documento" placeholder="Opcional" maxlength="50"
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50">
                    </div>
                </div>
            </div>

            <div class="bg-gray-50/50 p-4 rounded-2xl border border-gray-100 space-y-4">
                <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">2. Valores e datas</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div class="lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valor total (R$) *</label>
                        <input type="number" step="0.01" min="0" name="valor_total" required placeholder="0,00"
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 font-bold text-indigo-600">
                    </div>
                    <div class="lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data competência *</label>
                        <input type="date" name="data_competencia" required
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 text-sm"
                            value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data vencimento *</label>
                        <input type="date" name="data_vencimento" required
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 text-sm"
                            value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Forma de pagamento</label>
                        <select name="forma_pagamento" class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white">
                            <option value="">Selecione...</option>
                            <option value="DINHEIRO">Dinheiro</option>
                            <option value="PIX">Pix</option>
                            <option value="CARTAO">Cartão</option>
                            <option value="BOLETO">Boleto</option>
                            <option value="TRANSFERENCIA">Transferência</option>
                        </select>
                    </div>
                    <div class="lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pagamento à vista (R$)</label>
                        <input type="number" step="0.01" min="0" name="valor_pago" value="0" placeholder="0"
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50">
                        <p class="text-[10px] text-gray-400 mt-1">Se preenchido, informe a conta abaixo.</p>
                    </div>
                    <?php
                    $contasPag = $financialAccounts ?? [];
                    if (!empty($contasPag)):
                    ?>
                    <div class="lg:col-span-1" id="conta-pagamento-wrap">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Conta para saída (quando valor pago &gt; 0)</label>
                        <select name="conta_bancaria_id" class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white">
                            <option value="">— Selecione se houver pagamento à vista —</option>
                            <?php foreach ($contasPag as $ac): ?>
                                <option value="<?php echo (int) $ac['id']; ?>"><?php echo htmlspecialchars($ac['nome'], ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-gray-50/50 p-4 rounded-2xl border border-gray-100 space-y-4">
                <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">3. Recorrência e parcelamento</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 items-end">
                    <div class="lg:col-span-1 flex items-center gap-2">
                        <input type="checkbox" name="recorrente" value="1" class="checkbox checkbox-sm rounded border-gray-300">
                        <span class="text-sm font-medium text-gray-700">Recorrente</span>
                    </div>
                    <div class="lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Regra</label>
                        <select name="regra_recorrencia" class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white text-sm">
                            <option value="">—</option>
                            <option value="mensal">Mensal</option>
                            <option value="semanal">Semanal</option>
                        </select>
                    </div>
                    <div class="lg:col-span-1 flex items-center gap-2">
                        <input type="checkbox" name="parcelado" value="1" class="checkbox checkbox-sm rounded border-gray-300">
                        <span class="text-sm font-medium text-gray-700">Parcelado</span>
                    </div>
                    <div class="lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Qtd. parcelas</label>
                        <input type="number" name="qtd_parcelas" min="1" value="1" class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 text-sm">
                    </div>
                </div>
            </div>

            <div class="bg-gray-50/50 p-4 rounded-2xl border border-gray-100 space-y-4">
                <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">4. Observações</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div class="lg:col-span-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                        <textarea name="observacoes" rows="3" placeholder="Observações internas..."
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50"></textarea>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary flex-1 rounded-xl shadow-lg font-black">
                    <i class="fas fa-save mr-2"></i> Salvar conta a pagar
                </button>
            </div>
        </form>
        </div>
    </div>

    <!-- Filters -->
    <div class="card-standard">
        <div class="card-standard-header"><i class="fas fa-filter"></i> Filtros</div>
        <div class="card-standard-body">
        <form class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 items-end">
            <input type="hidden" name="route" value="payable/index">

            <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status"
                    class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white text-sm">
                    <option value="">Todos</option>
                    <option value="ABERTO" <?php echo ($filters['status'] === 'ABERTO') ? 'selected' : ''; ?>>Aberto
                    </option>
                    <option value="PARCIAL" <?php echo ($filters['status'] === 'PARCIAL') ? 'selected' : ''; ?>>Parcial
                    </option>
                    <option value="PAGO" <?php echo ($filters['status'] === 'PAGO') ? 'selected' : ''; ?>>Pago</option>
                    <option value="VENCIDO" <?php echo ($filters['status'] === 'VENCIDO') ? 'selected' : ''; ?>>Vencido
                    </option>
                </select>
            </div>

            <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Início</label>
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($filters['start_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white text-sm">
            </div>

            <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Fim</label>
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($filters['end_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white text-sm">
            </div>

            <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                <select name="categoria_id"
                    class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white text-sm">
                    <option value="">Todas</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($filters['categoria_id'] == $cat['id']) ? 'selected' : ''; ?>><?php echo e($cat['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-neutral btn-sm rounded-lg">Filtrar</button>
        </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card-standard overflow-hidden">
        <div class="card-standard-header"><i class="fas fa-list"></i> Listagem de contas a pagar</div>
        <div class="overflow-x-auto">
            <table class="table table-compact w-full">
                <thead>
                    <tr class="text-[10px] text-gray-400 uppercase tracking-widest bg-gray-50/50">
                        <th class="py-4 px-6 text-left">Vencimento</th>
                        <th class="text-left">Doc / Descrição / Fornecedor</th>
                        <th class="text-left">Categoria</th>
                        <th class="text-right">Valor Total</th>
                        <th class="text-center">Status</th>
                        <th class="text-right pr-6">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if (empty($payables)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-12 text-gray-400 italic">Nenhum registro encontrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payables as $p): ?>
                            <tr class="hover:bg-gray-50/80 transition-colors group">
                                <td class="py-4 px-6 text-left">
                                    <div class="flex flex-col">
                                        <span
                                            class="text-xs font-bold text-gray-700"><?php echo date('d/m/Y', strtotime($p['data_vencimento'])); ?></span>
                                        <span class="text-[9px] text-gray-400 font-medium">Competência:
                                            <?php echo date('m/Y', strtotime($p['data_competencia'])); ?></span>
                                    </div>
                                </td>
                                <td class="text-left">
                                    <div class="flex flex-col">
                                        <?php if (!empty($p['numero_documento'])): ?>
                                            <span
                                                class="text-[9px] bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded w-fit mb-1 font-black leading-none">
                                                <i
                                                    class="fas fa-file-invoice mr-1 text-[8px]"></i><?php echo e($p['numero_documento']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <span
                                            class="text-xs font-bold text-gray-800 leading-tight"><?php echo e($p['descricao']); ?></span>
                                        <span
                                            class="text-[10px] text-gray-400 mt-0.5"><?php echo e($p['supplier_name'] ?? 'Sem Fornecedor'); ?></span>
                                    </div>
                                </td>
                                <td class="text-left">
                                    <span
                                        class="px-2 py-1 rounded-lg bg-gray-100 text-gray-600 text-[10px] font-bold"><?php echo e($p['categoria_nome']); ?></span>
                                </td>
                                <td class="text-right">
                                    <div class="flex flex-col items-end">
                                        <span class="text-xs font-black text-gray-800">R$
                                            <?php echo number_format($p['valor_total'], 2, ',', '.'); ?></span>
                                        <?php if ($p['valor_pago'] > 0): ?>
                                            <span class="text-[9px] text-green-500 font-bold">Pago: R$
                                                <?php echo number_format($p['valor_pago'], 2, ',', '.'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $statusClass = [
                                        'ABERTO' => 'badge-info',
                                        'PARCIAL' => 'badge-warning',
                                        'PAGO' => 'badge-success',
                                        'VENCIDO' => 'badge-error',
                                        'CANCELADO' => 'badge-ghost'
                                    ];
                                    ?>
                                    <span
                                        class="badge <?php echo $statusClass[$p['status']]; ?> badge-sm text-[10px] font-bold text-white shadow-sm border-none"><?php echo e($p['status']); ?></span>
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-1">
                                        <?php if (in_array($p['status'], ['ABERTO', 'PARCIAL', 'VENCIDO'])): ?>
                                            <button type="button"
                                                class="inline-flex items-center gap-1 btn bg-indigo-600 hover:bg-indigo-700 btn-xs text-white border-none shadow-sm"
                                                @click="selectedPayable = <?php echo htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8'); ?>; showModal = true"
                                                title="Baixar/Pagar">
                                                <i class="fas fa-check"></i><span>Pagar</span>
                                            </button>
                                        <?php endif; ?>
                                        <button type="button"
                                            class="inline-flex items-center gap-1 btn btn-ghost btn-xs text-gray-300 hover:text-indigo-600 transition-colors" title="Editar"><i class="fas fa-edit text-[10px]"></i><span>Editar</span></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: apenas para Realizar Pagamento (baixa) -->
    <div class="modal" :class="{ 'modal-open': showModal }" x-show="showModal" x-cloak style="z-index: 9999;">
        <div class="modal-box max-w-2xl rounded-3xl p-0 overflow-hidden relative border border-gray-100 shadow-2xl">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-4 top-4 z-10"
                @click="showModal = false">✕</button>

            <div class="bg-indigo-600 p-6">
                <h3 class="text-xl font-black text-white">Realizar Pagamento</h3>
            </div>

            <!-- Form Baixa (quando clica em pagar na linha) -->
            <template x-if="selectedPayable">
                <form action="?route=payable/pay" method="POST" class="flex flex-col gap-4 p-8">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="id" :value="selectedPayable.id">
                    <div class="p-4 bg-gray-50 rounded-2xl flex flex-col gap-1 border border-gray-100">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Resumo</span>
                        <span class="text-sm font-black text-gray-700" x-text="selectedPayable.descricao"></span>
                        <span class="text-sm font-bold text-red-500"
                            x-text="'Saldo em Aberto: R$ ' + Number(selectedPayable.saldo_aberto).toLocaleString('pt-BR', {minimumFractionDigits: 2})"></span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valor do Pagamento</label>
                        <input type="number" step="0.01" name="amount" required
                            :max="selectedPayable?.saldo_aberto || 0" :value="selectedPayable?.saldo_aberto || 0"
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Conta de Saída</label>
                        <select name="conta_bancaria_id" required
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white">
                            <?php $accs = $financialAccounts ?? []; ?>
                            <?php foreach ($accs as $ac): ?>
                                <option value="<?php echo $ac['id']; ?>"><?php echo e($ac['nome']); ?> (R$
                                    <?php echo number_format($ac['saldo_inicial'], 2, ',', '.'); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Pagamento</label>
                        <select name="forma_pagamento" required
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white">
                            <option value="Dinheiro">Dinheiro</option>
                            <option value="Pix">Pix</option>
                            <option value="Cartão de Crédito">Cartão de Crédito</option>
                            <option value="Cartão de Débito">Cartão de Débito</option>
                            <option value="Boleto">Boleto</option>
                            <option value="Transferência">Transferência</option>
                        </select>
                    </div>
                    <button type="submit"
                        class="btn btn-success text-white rounded-xl mt-4 shadow-md font-black">Confirmar
                        Pagamento</button>
                </form>
            </template>
        </div>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>