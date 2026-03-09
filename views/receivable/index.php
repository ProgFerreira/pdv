<?php require 'views/layouts/header.php'; ?>

<div class="flex flex-col gap-6" x-data="{ showModal: false, selectedReceivable: null }">
    <?php if (!empty($_GET['success'])): ?>
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl flex items-center gap-2">
            <i class="fas fa-check-circle"></i>
            <span><?php echo $_GET['success'] === 'created' ? 'Conta a receber criada com sucesso.' : 'Recebimento registrado.'; ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($_GET['error'])): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl flex items-center gap-2">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo ($_GET['error'] === 'validation') ? 'Preencha descrição e categoria.' : 'Erro ao salvar. Tente novamente.'; ?></span>
        </div>
    <?php endif; ?>
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-800">Contas a Receber</h1>
            <p class="text-sm text-gray-400">Gerencie suas receitas, vendas a prazo e créditos.</p>
        </div>
        <div class="flex gap-2 w-full md:w-auto">
            <button class="btn btn-outline btn-sm shadow-sm rounded-xl" onclick="window.location.reload()">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>

    <!-- Form Novo Lançamento (sempre visível na página) -->
    <div class="card-standard overflow-hidden">
        <div class="card-standard-header">
            <i class="fas fa-hand-holding-usd"></i>
            <span>Novo lançamento de receita</span>
        </div>
        <div class="card-standard-body">
        <p class="text-gray-500 text-xs mb-4">Cadastre suas receitas para manter o fluxo de caixa atualizado.</p>
        <form action="?route=receivable/store" method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>

            <div class="bg-gray-50/50 p-4 rounded-2xl border border-gray-100 space-y-4">
                <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">1. Identificação</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição *</label>
                        <input type="text" name="descricao" required placeholder="Ex: Venda a prazo, Serviço, Receita..."
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                        <select name="cliente_id" class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white">
                            <option value="">Cliente avulso / Não informado</option>
                            <?php foreach ($customers as $c): ?>
                                <option value="<?php echo (int) $c['id']; ?>"><?php echo htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Origem *</label>
                        <select name="origem" class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white">
                            <option value="MANUAL">Manual</option>
                            <option value="PDV">PDV</option>
                            <option value="DELIVERY">Delivery</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Categoria *</label>
                        <select name="categoria_id" required class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white">
                            <option value="">Selecione...</option>
                            <?php foreach ($categoriesReceita as $cat): ?>
                                <option value="<?php echo (int) $cat['id']; ?>"><?php echo htmlspecialchars($cat['nome'], ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nº Documento / Ref</label>
                        <input type="text" name="numero_documento" placeholder="Opcional" maxlength="50"
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50">
                    </div>
                </div>
            </div>

            <div class="bg-gray-50/50 p-4 rounded-2xl border border-gray-100 space-y-4">
                <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">2. Valores e datas</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valor total (R$) *</label>
                        <input type="number" step="0.01" min="0" name="valor_total" required placeholder="0,00"
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 font-bold text-green-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data competência *</label>
                        <input type="date" name="data_competencia" required
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 text-sm"
                            value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data vencimento *</label>
                        <input type="date" name="data_vencimento" required
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 text-sm"
                            value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Forma de recebimento</label>
                        <select name="forma_recebimento" class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white">
                            <option value="">Selecione...</option>
                            <option value="DINHEIRO">Dinheiro</option>
                            <option value="PIX">Pix</option>
                            <option value="CARTAO">Cartão</option>
                            <option value="BOLETO">Boleto</option>
                            <option value="TRANSFERENCIA">Transferência</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Receb. à vista (R$)</label>
                        <input type="number" step="0.01" min="0" name="valor_recebido" value="0" placeholder="0"
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50">
                    </div>
                    <?php if (!empty($accounts)): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Conta para entrada (quando valor recebido &gt; 0)</label>
                        <select name="conta_bancaria_id" class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white">
                            <option value="">— Selecione se houver recebimento à vista —</option>
                            <?php foreach ($accounts as $ac): ?>
                                <option value="<?php echo (int) $ac['id']; ?>"><?php echo htmlspecialchars($ac['nome'], ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-gray-50/50 p-4 rounded-2xl border border-gray-100 space-y-4">
                <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">3. Observações</span>
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
                    <i class="fas fa-save mr-2"></i> Salvar conta a receber
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
            <input type="hidden" name="route" value="receivable/index">

            <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status"
                    class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white text-sm">
                    <option value="">Todos</option>
                    <option value="ABERTO" <?php echo ($filters['status'] === 'ABERTO') ? 'selected' : ''; ?>>Aberto
                    </option>
                    <option value="PARCIAL" <?php echo ($filters['status'] === 'PARCIAL') ? 'selected' : ''; ?>>Parcial
                    </option>
                    <option value="RECEBIDO" <?php echo ($filters['status'] === 'RECEBIDO') ? 'selected' : ''; ?>>Recebido
                    </option>
                    <option value="VENCIDO" <?php echo ($filters['status'] === 'VENCIDO') ? 'selected' : ''; ?>>Vencido
                    </option>
                </select>
            </div>

            <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Origem</label>
                <select name="origem"
                    class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white text-sm">
                    <option value="">Todas</option>
                    <option value="PDV" <?php echo ($filters['origem'] === 'PDV') ? 'selected' : ''; ?>>PDV</option>
                    <option value="DELIVERY" <?php echo ($filters['origem'] === 'DELIVERY') ? 'selected' : ''; ?>>Delivery
                    </option>
                    <option value="MANUAL" <?php echo ($filters['origem'] === 'MANUAL') ? 'selected' : ''; ?>>Manual
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

            <button type="submit" class="btn btn-neutral btn-sm rounded-xl font-bold lg:col-span-4 shadow-sm">Filtrar
                Lançamentos</button>
        </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card-standard overflow-hidden">
        <div class="card-standard-header"><i class="fas fa-list"></i> Listagem de contas a receber</div>
        <div class="overflow-x-auto">
            <table class="table table-compact w-full">
                <thead>
                    <tr class="text-[10px] text-gray-400 uppercase tracking-widest bg-gray-50/50">
                        <th class="py-4 px-6 text-left">Vencimento</th>
                        <th class="text-left">Doc / Descrição / Cliente</th>
                        <th class="text-left">Origem</th>
                        <th class="text-right">Valor Total</th>
                        <th class="text-center">Status</th>
                        <th class="text-right pr-6">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if (empty($list)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-12 text-gray-400 italic">Nenhum registro encontrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($list as $r): ?>
                            <tr class="hover:bg-gray-50/80 transition-colors group">
                                <td class="py-4 px-6 text-left">
                                    <div class="flex flex-col">
                                        <span
                                            class="text-xs font-bold text-gray-700"><?php echo date('d/m/Y', strtotime($r['data_vencimento'])); ?></span>
                                        <span class="text-[9px] text-gray-400 font-medium">Competência:
                                            <?php echo date('m/Y', strtotime($r['data_competencia'])); ?></span>
                                    </div>
                                </td>
                                <td class="text-left">
                                    <div class="flex flex-col">
                                        <?php if (!empty($r['numero_documento'])): ?>
                                            <span
                                                class="text-[9px] bg-green-50 text-green-600 px-1.5 py-0.5 rounded w-fit mb-1 font-black leading-none">
                                                <i
                                                    class="fas fa-file-invoice mr-1 text-[8px]"></i><?php echo $r['numero_documento']; ?>
                                            </span>
                                        <?php endif; ?>
                                        <span
                                            class="text-xs font-bold text-gray-800 leading-tight"><?php echo $r['descricao']; ?></span>
                                        <span
                                            class="text-[10px] text-gray-400 mt-0.5"><?php echo $r['customer_name'] ?? 'Cliente Avulso'; ?></span>
                                    </div>
                                </td>
                                <td class="text-left">
                                    <span
                                        class="px-2 py-1 rounded-lg bg-gray-100 text-gray-600 text-[10px] font-bold"><?php echo $r['origem']; ?></span>
                                </td>
                                <td class="text-right">
                                    <div class="flex flex-col items-end">
                                        <span class="text-xs font-black text-gray-800">R$
                                            <?php echo number_format($r['valor_total'], 2, ',', '.'); ?></span>
                                        <?php if ($r['valor_recebido'] > 0): ?>
                                            <span class="text-[9px] text-green-500 font-bold">Rec: R$
                                                <?php echo number_format($r['valor_recebido'], 2, ',', '.'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-left">
                                    <?php
                                    $statusClass = [
                                        'ABERTO' => 'badge-info',
                                        'PARCIAL' => 'badge-warning',
                                        'RECEBIDO' => 'badge-success',
                                        'VENCIDO' => 'badge-error',
                                        'CANCELADO' => 'badge-ghost'
                                    ];
                                    ?>
                                    <span
                                        class="badge <?php echo $statusClass[$r['status']]; ?> badge-sm text-[10px] font-bold text-white shadow-sm border-none"><?php echo $r['status']; ?></span>
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-1">
                                        <?php if (in_array($r['status'], ['ABERTO', 'PARCIAL', 'VENCIDO'])): ?>
                                            <button
                                                class="btn bg-indigo-600 hover:bg-indigo-700 border-none btn-xs text-white shadow-sm"
                                                @click="selectedReceivable = <?php echo htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8'); ?>; showModal = true"
                                                title="Baixar/Receber">
                                                <i class="fas fa-hand-holding-usd"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button
                                            class="btn btn-ghost btn-xs text-gray-300 hover:text-indigo-600 transition-colors"><i
                                                class="fas fa-eye text-[10px]"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: apenas para Confirmar Recebimento (baixa) -->
    <div class="modal" :class="{ 'modal-open': showModal }" x-show="showModal" x-cloak style="z-index: 9999;">
        <div class="modal-box max-w-2xl rounded-3xl p-0 overflow-hidden relative border border-gray-100 shadow-2xl">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-4 top-4 z-10"
                @click="showModal = false">✕</button>

            <div class="bg-indigo-600 p-6">
                <h3 class="text-xl font-black text-white">Confirmar Recebimento</h3>
            </div>

            <!-- Form Baixa (quando clica em receber na linha) -->
            <template x-if="selectedReceivable">
                <form action="?route=receivable/pay" method="POST" class="flex flex-col gap-4 p-8">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="id" :value="selectedReceivable.id">
                    <div class="p-4 bg-gray-50 rounded-2xl flex flex-col gap-1 border border-gray-100">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Resumo</span>
                        <span class="text-sm font-black text-gray-700" x-text="selectedReceivable.descricao"></span>
                        <span class="text-sm font-bold text-green-600"
                            x-text="'Saldo em Aberto: R$ ' + Number(selectedReceivable.saldo_aberto).toLocaleString('pt-BR', {minimumFractionDigits: 2})"></span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valor Recebido</label>
                        <input type="number" step="0.01" name="amount" required
                            :max="selectedReceivable?.saldo_aberto || 0" :value="selectedReceivable?.saldo_aberto || 0"
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Destino do Recurso</label>
                        <select name="conta_bancaria_id" required
                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white">
                            <?php foreach ($accounts as $ac): ?>
                                <option value="<?php echo $ac['id']; ?>"><?php echo $ac['nome']; ?> (R$
                                    <?php echo number_format($ac['saldo_inicial'], 2, ',', '.'); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Recebimento</label>
                        <select name="forma_recebimento" required
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
                        Recebimento</button>
                </form>
            </template>
        </div>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>