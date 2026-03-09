<?php require 'views/layouts/header.php'; ?>

<div class="w-[95vw] max-w-full mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">➕ Nova Entrada de Estoque</h2>
    </div>

    <form method="POST" id="entry-form">
        <?php echo csrf_field(); ?>
        <div class="grid grid-cols-1 lg:grid-cols-6 gap-6 mb-6">
            <!-- Info Principal -->
            <div class="lg:col-span-5 card-standard overflow-hidden">
                <div class="card-standard-header"><i class="fas fa-box-open"></i> Informações da Entrada</div>
                <div class="card-standard-body">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Referência (NF/Pedido)</label>
                            <input type="text" name="reference"
                                class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50"
                                placeholder="Ex: NF-12345">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fornecedor (Texto ou Nome)</label>
                            <input type="text" name="supplier"
                                class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50"
                                placeholder="Ex: Distribuidora XYZ">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Empresa Fornecedora (Cadastro)</label>
                            <select name="supplier_id"
                                class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-white">
                                <option value="">Selecione se cadastrado...</option>
                                <?php foreach ($suppliers as $s): ?>
                                    <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data da Entrada</label>
                            <input type="date" name="entry_date"
                                class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50"
                                value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total da Entrada (R$)</label>
                            <input type="text" name="total_amount" id="total_amount" readonly
                                class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 bg-gray-50 font-bold text-primary"
                                value="0,00">
                        </div>

                        <div class="md:col-span-2 p-4 bg-gray-50 border border-dashed border-gray-300">
                            <label class="flex items-center gap-2 cursor-pointer font-bold text-gray-700">
                                <input type="checkbox" name="create_payable" value="1"
                                    class="w-4 h-4 text-primary focus:ring-primary border-gray-300"
                                    onchange="document.getElementById('finance-fields').style.display = this.checked ? 'block' : 'none'">
                                Gerar Lançamento no Contas a Pagar
                            </label>
                            <div id="finance-fields" class="mt-4 hidden animate-fade-in">
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Vencimento da Fatura</label>
                                        <input type="date" name="due_date_payable" value="<?php echo date('Y-m-d'); ?>"
                                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 text-sm">
                                    </div>
                                    <div class="flex items-end">
                                        <p class="text-xs text-gray-400 italic">O valor total da entrada será lançado como uma despesa pendente.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                            <textarea name="notes" rows="2"
                                class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seleção de Produtos -->
            <div class="lg:col-span-1 card-standard overflow-hidden">
                <div class="card-standard-header"><i class="fas fa-plus-circle"></i> Adicionar Produto</div>
                <div class="card-standard-body space-y-4">
                    <div>
                        <div class="flex justify-between items-end mb-1">
                            <label class="block text-sm font-medium text-gray-700">Buscar Produto</label>
                            <a href="?route=product/create" target="_blank"
                                class="text-[10px] font-bold text-primary hover:underline uppercase tracking-tighter">
                                <i class="fas fa-plus-circle"></i> Novo Produto
                            </a>
                        </div>
                        <div class="relative">
                            <input type="text" id="product-search"
                                class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50"
                                placeholder="Nome ou código...">
                            <div id="product-results"
                                class="absolute z-50 left-0 right-0 bg-white border border-gray-200 shadow-lg hidden max-h-60 overflow-y-auto mt-1">
                            </div>
                        </div>
                    </div>
                    <div id="selected-product-info" class="hidden bg-blue-50 p-3 border border-blue-100">
                        <p class="font-bold text-blue-800" id="adding-name"></p>
                        <input type="hidden" id="adding-id">
                        <div class="grid grid-cols-2 gap-2 mt-2">
                            <div>
                                <label class="block text-xs text-blue-600 font-bold mb-1">Qtd</label>
                                <input type="number" id="adding-qty" class="w-full border border-gray-300 p-1 text-sm" value="1">
                            </div>
                            <div>
                                <label class="block text-xs text-blue-600 font-bold mb-1">Custo Unit.</label>
                                <input type="text" id="adding-cost" class="w-full border border-gray-300 p-1 text-sm" placeholder="0,00">
                            </div>
                        </div>
                        <button type="button" onclick="addProductToTable()"
                            class="w-full mt-3 bg-blue-600 text-white font-bold py-2 hover:bg-blue-700 transition">
                            Inserir Item
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Itens -->
        <div class="card-standard overflow-hidden mb-6">
            <div class="card-standard-header"><i class="fas fa-list"></i> Itens da Entrada</div>
            <div class="card-standard-body p-0">
        <table class="min-w-full divide-y divide-gray-200" id="items-table">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produto</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Qtd</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Custo Unit.</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ação</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200" id="items-tbody">
                <!-- Injetado via JS -->
            </tbody>
        </table>
        <div id="empty-state" class="p-8 text-center text-gray-400">
                Nenhum item adicionado ainda. Use o buscador ao lado.
            </div>
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg flex items-center gap-2 transition">
                <i class="fas fa-save"></i> Salvar Entrada
            </button>
            <a href="?route=stock/index"
                class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-8 rounded-lg shadow transition">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
    let searchTimeout;

    document.getElementById('product-search').addEventListener('input', function (e) {
        const term = this.value;
        const results = document.getElementById('product-results');

        if (term.length < 2) {
            results.style.display = 'none';
            return;
        }

        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            fetch(`index.php?route=pos/search&term=${encodeURIComponent(term)}`)
                .then(r => r.json())
                .then(data => {
                    results.innerHTML = '';
                    if (data.length > 0) {
                        results.style.display = 'block';
                        data.forEach(p => {
                            const div = document.createElement('div');
                            div.className = 'p-3 hover:bg-gray-100 cursor-pointer border-b last:border-0';
                            div.innerHTML = `<div class="font-bold">${p.name}</div><div class="text-xs text-gray-500">Custo Atual: R$ ${parseFloat(p.cost_price || 0).toFixed(2)}</div>`;
                            div.onclick = () => selectProduct(p);
                            results.appendChild(div);
                        });
                    } else {
                        results.style.display = 'none';
                    }
                });
        }, 300);
    });

    function selectProduct(p) {
        document.getElementById('adding-name').innerText = p.name;
        document.getElementById('adding-id').value = p.id;
        document.getElementById('adding-cost').value = parseFloat(p.cost_price || 0).toFixed(2);
        document.getElementById('selected-product-info').classList.remove('hidden');
        document.getElementById('product-results').style.display = 'none';
        document.getElementById('product-search').value = '';
        document.getElementById('adding-qty').focus();
    }

    function addProductToTable() {
        const id = document.getElementById('adding-id').value;
        const name = document.getElementById('adding-name').innerText;
        const qty = parseInt(document.getElementById('adding-qty').value);
        const cost = parseFloat(document.getElementById('adding-cost').value.replace(',', '.')) || 0;

        if (!id || qty <= 0) return;

        const tbody = document.getElementById('items-tbody');
        const subtotal = qty * cost;

        const tr = document.createElement('tr');
        tr.className = 'hover:bg-gray-50';
        tr.innerHTML = `
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
            ${name}
            <input type="hidden" name="product_id[]" value="${id}">
        </td>
        <td class="px-6 py-4 text-center">
            <input type="number" name="quantity[]" value="${qty}" class="w-16 border rounded text-center p-1 text-sm" onchange="recalculateTotals()">
        </td>
        <td class="px-6 py-4 text-right">
            <input type="text" name="cost_price[]" value="${cost.toFixed(2)}" class="w-24 border rounded text-right p-1 text-sm" onchange="recalculateTotals()">
        </td>
        <td class="px-6 py-4 text-right text-sm font-bold text-gray-900 item-subtotal">
            R$ ${subtotal.toFixed(2).replace('.', ',')}
        </td>
        <td class="px-6 py-4 text-center">
            <button type="button" onclick="this.closest('tr').remove(); recalculateTotals();" class="text-red-500 hover:text-red-700">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;

        tbody.appendChild(tr);
        document.getElementById('empty-state').style.display = 'none';
        document.getElementById('selected-product-info').classList.add('hidden');
        document.getElementById('product-search').focus();

        recalculateTotals();
    }

    function recalculateTotals() {
        const rows = document.querySelectorAll('#items-tbody tr');
        let total = 0;

        rows.forEach(row => {
            const qty = parseInt(row.querySelector('input[name="quantity[]"]').value) || 0;
            const cost = parseFloat(row.querySelector('input[name="cost_price[]"]').value.replace(',', '.')) || 0;
            const subtotal = qty * cost;

            row.querySelector('.item-subtotal').innerText = 'R$ ' + subtotal.toFixed(2).replace('.', ',');
            total += subtotal;
        });

        document.getElementById('total_amount').value = total.toFixed(2).replace('.', ',');

        if (rows.length === 0) {
            document.getElementById('empty-state').style.display = 'block';
        }
    }
</script>

<?php require 'views/layouts/footer.php'; ?>