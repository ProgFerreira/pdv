<?php require 'views/layouts/header.php'; ?>

<div class="flex flex-col gap-6" x-data="{ showModal: false }">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-gray-800">Contas Bancárias / Caixas</h1>
            <p class="text-sm text-gray-400">Gerencie seus bancos, cartões e caixas físicos.</p>
        </div>
        <button class="btn btn-primary btn-sm rounded-xl shadow-sm" @click="showModal = true">
            <i class="fas fa-plus mr-2"></i> Nova Conta
        </button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <?php foreach ($accounts as $acc): ?>
            <div
                class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col gap-4 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <i
                        class="fas <?php echo ($acc['tipo'] === 'DINHEIRO') ? 'fa-wallet' : 'fa-university'; ?> text-8xl"></i>
                </div>
                <div class="flex justify-between items-start">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest">
                            <?php echo $acc['tipo']; ?>
                        </span>
                        <h3 class="text-xl font-bold text-gray-800">
                            <?php echo $acc['nome']; ?>
                        </h3>
                    </div>
                    <span class="badge <?php echo $acc['ativo'] ? 'badge-success' : 'badge-ghost'; ?> badge-xs"></span>
                </div>
                <div class="flex flex-col">
                    <p class="text-[10px] text-gray-400 font-bold uppercase uppercase tracking-tighter">Saldo Atual</p>
                    <span class="text-2xl font-black text-primary">R$
                        <?php echo number_format($acc['saldo_inicial'], 2, ',', '.'); ?>
                    </span>
                </div>
                <div class="flex gap-2">
                    <button class="btn btn-ghost btn-xs text-gray-400 hover:text-primary p-0">Extrato <i
                            class="fas fa-chevron-right text-[8px] ml-1"></i></button>
                    <button class="btn btn-ghost btn-xs text-gray-400 hover:text-primary p-0">Editar <i
                            class="fas fa-pencil-alt text-[8px] ml-1"></i></button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal -->
<div class="modal" :class="{ 'modal-open': showModal }">
    <div class="modal-box rounded-3xl p-8 relative">
        <button class="btn btn-sm btn-circle btn-ghost absolute right-4 top-4" @click="showModal = false">✕</button>
        <h3 class="text-xl font-black text-gray-800 mb-6">Nova Conta / Caixa</h3>
        <form action="?route=account/store" method="POST" class="flex flex-col gap-4">
            <?php echo csrf_field(); ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="form-control lg:col-span-4">
                <label class="label p-1"><span class="label-text font-bold text-gray-500 text-xs">Nome da Conta</span></label>
                <input type="text" name="nome" required
                    class="w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                    placeholder="Ex: Itau - Corrente">
            </div>
            <div class="form-control lg:col-span-1">
                <label class="label p-1"><span class="label-text font-bold text-gray-500 text-xs">Tipo</span></label>
                <select name="tipo" required class="w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 bg-white">
                    <option value="CORRENTE">C. Corrente</option>
                    <option value="POUPANCA">C. Poupança</option>
                    <option value="DINHEIRO">Caixa (Dinheiro)</option>
                    <option value="INVESTIMENTO">Conta Inv.</option>
                    <option value="OUTRO">Outro</option>
                </select>
            </div>
            <div class="form-control lg:col-span-1">
                <label class="label p-1"><span class="label-text font-bold text-gray-500 text-xs">Saldo Inicial</span></label>
                <input type="number" step="0.01" name="saldo_inicial" required
                    class="w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50" value="0.00">
            </div>
            <div class="form-control lg:col-span-6">
                <label class="label cursor-pointer justify-start gap-4">
                    <input type="checkbox" name="ativo" checked class="checkbox checkbox-primary">
                    <span class="label-text font-bold text-gray-500 text-xs">Conta Ativa</span>
                </label>
            </div>
            </div>
            <button type="submit" class="btn btn-primary rounded-xl mt-4 shadow-md font-black italic">Criar
                Conta</button>
        </form>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>