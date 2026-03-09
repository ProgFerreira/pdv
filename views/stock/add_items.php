<?php require 'views/layouts/header.php'; ?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">➕ Adicionar mais produtos à Nota #<?php echo (int) $entry['id']; ?></h2>
        <p class="text-sm text-gray-500">Inclua novos itens nesta entrada de estoque.</p>
    </div>
    <div class="flex gap-3">
        <a href="?route=stock/view&id=<?php echo $entry['id']; ?>"
            class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-lg font-bold transition-colors flex items-center gap-2">
            <i class="fas fa-file-alt"></i> Ver Nota
        </a>
        <a href="?route=stock/index"
            class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-lg font-bold transition-colors flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<?php if (isset($_GET['error'])): ?>
    <div class="mb-6 p-4 rounded-lg flex items-center gap-3 <?php echo $_GET['error'] === 'no_items' ? 'bg-amber-50 border border-amber-200 text-amber-800' : 'bg-red-50 border border-red-200 text-red-800'; ?>">
        <i class="fas <?php echo $_GET['error'] === 'no_items' ? 'fa-exclamation-circle' : 'fa-times-circle'; ?>"></i>
        <span>
            <?php
            if ($_GET['error'] === 'no_items') {
                echo 'Adicione ao menos um produto na tabela antes de enviar.';
            } else {
                echo 'Erro ao adicionar itens. Tente novamente.';
            }
            ?>
        </span>
    </div>
<?php endif; ?>

<!-- Resumo da nota (somente leitura) -->
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    <div class="bg-white p-4 rounded-lg border border-gray-200">
        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Referência</label>
        <p class="font-bold text-gray-800"><?php echo htmlspecialchars($entry['reference'] ?: '—'); ?></p>
    </div>
    <div class="bg-white p-4 rounded-lg border border-gray-200">
        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Fornecedor</label>
        <p class="font-bold text-gray-800"><?php echo htmlspecialchars($entry['supplier'] ?: '—'); ?></p>
    </div>
    <div class="bg-white p-4 rounded-lg border border-gray-200">
        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Data</label>
        <p class="font-bold text-gray-800"><?php echo date('d/m/Y', strtotime($entry['entry_date'])); ?></p>
    </div>
    <div class="bg-white p-4 rounded-lg border border-gray-200">
        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Total atual</label>
        <p class="font-bold text-primary">R$ <?php echo number_format($entry['total_amount'], 2, ',', '.'); ?></p>
    </div>
</div>

<!-- Itens já existentes na nota -->
<div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200 mb-6">
    <div class="p-4 bg-gray-50 border-b border-gray-200">
        <h4 class="font-bold text-gray-700">🛒 Itens já nesta entrada</h4>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produto</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Qtd</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Custo Unit.</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($entry['items'] as $item): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-700"><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td class="px-6 py-4 text-center text-sm text-gray-600"><?php echo (int) $item['quantity']; ?></td>
                        <td class="px-6 py-4 text-right text-sm text-gray-600">R$ <?php echo number_format($item['cost_price'], 2, ',', '.'); ?></td>
                        <td class="px-6 py-4 text-right text-sm font-bold text-gray-800">R$ <?php echo number_format($item['quantity'] * $item['cost_price'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<form method="POST" id="add-items-form" action="?route=stock/addItems">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="id" value="<?php echo (int) $entry['id']; ?>">
    <div class="grid grid-cols-1 lg:grid-cols-6 gap-6 mb-6">
        <div class="lg:col-span-5 bg-white shadow-md rounded-lg p-6 border border-gray-200">
            <h3 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2">Novos itens a incluir</h3>
            <p class="text-sm text-gray-500 mb-4">Use o painel à direita para buscar e adicionar produtos à tabela abaixo. Em seguida, clique em &quot;Adicionar itens à nota&quot;.</p>
            <div id="new-items-total" class="text-right text-lg font-bold text-primary hidden">
                Total dos novos itens: <span id="new-items-total-value">0,00</span>
            </div>
        </div>
        <div class="lg:col-span-1 bg-white shadow-md rounded-lg p-6 border border-gray-200">
            <h3 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2">Adicionar Produto</h3>
            <div class="space-y-4">
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
                            class="w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                            placeholder="Nome ou código...">
                        <div id="product-results"
                            class="absolute z-50 left-0 right-0 bg-white border border-gray-200 rounded-md shadow-lg hidden max-h-60 overflow-y-auto mt-1">
                        </div>
                    </div>
                </div>
                <div id="selected-product-info" class="hidden bg-blue-50 p-3 rounded-lg border border-blue-100">
                    <p class="font-bold text-blue-800" id="adding-name"></p>
                    <input type="hidden" id="adding-id">
                    <div class="grid grid-cols-2 gap-2 mt-2">
                        <div>
                            <label class="text-xs text-blue-600 font-bold uppercase">Qtd</label>
                            <input type="number" id="adding-qty" class="w-full border-blue-200 rounded p-1 text-sm" value="1">
                        </div>
                        <div>
                            <label class="text-xs text-blue-600 font-bold uppercase">Custo Unit.</label>
                            <input type="text" id="adding-cost" class="w-full border-blue-200 rounded p-1 text-sm" placeholder="0,00">
                        </div>
                    </div>
                    <button type="button" onclick="addProductToTable()"
                        class="w-full mt-3 bg-blue-600 text-white font-bold py-2 rounded shadow hover:bg-blue-700 transition">
                        Inserir Item
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200 mb-6">
        <div class="p-4 bg-gray-50 border-b border-gray-200">
            <h4 class="font-bold text-gray-700">Itens a adicionar</h4>
        </div>
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
            <tbody class="divide-y divide-gray-200" id="items-tbody"></tbody>
        </table>
        <div id="empty-state" class="p-8 text-center text-gray-400">
            Nenhum item adicionado ainda. Use o buscador ao lado.
        </div>
    </div>

    <div class="flex gap-4">
        <button type="submit" id="submit-btn" disabled
            class="bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-3 px-8 rounded-lg shadow-lg flex items-center gap-2 transition">
            <i class="fas fa-plus-circle"></i> Adicionar itens à nota
        </button>
        <a href="?route=stock/view&id=<?php echo $entry['id']; ?>"
            class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-8 rounded-lg shadow transition">
            Cancelar
        </a>
    </div>
</form>

<script>
    let searchTimeout;

    document.getElementById('product-search').addEventListener('input', function () {
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
                            div.innerHTML = `<div class="font-bold">${escapeHtml(p.name)}</div><div class="text-xs text-gray-500">Custo Atual: R$ ${parseFloat(p.cost_price || 0).toFixed(2)}</div>`;
                            div.onclick = () => selectProduct(p);
                            results.appendChild(div);
                        });
                    } else {
                        results.style.display = 'none';
                    }
                });
        }, 300);
    });

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function selectProduct(p) {
        document.getElementById('adding-name').textContent = p.name;
        document.getElementById('adding-id').value = p.id;
        document.getElementById('adding-cost').value = parseFloat(p.cost_price || 0).toFixed(2);
        document.getElementById('selected-product-info').classList.remove('hidden');
        document.getElementById('product-results').style.display = 'none';
        document.getElementById('product-search').value = '';
        document.getElementById('adding-qty').focus();
    }

    function addProductToTable() {
        const id = document.getElementById('adding-id').value;
        const name = document.getElementById('adding-name').textContent;
        const qty = parseInt(document.getElementById('adding-qty').value, 10);
        const cost = parseFloat(document.getElementById('adding-cost').value.replace(',', '.')) || 0;
        if (!id || qty <= 0) return;

        const tbody = document.getElementById('items-tbody');
        const subtotal = qty * cost;
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-gray-50';
        tr.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${escapeHtml(name)}
                <input type="hidden" name="product_id[]" value="${escapeHtml(String(id))}">
            </td>
            <td class="px-6 py-4 text-center">
                <input type="number" name="quantity[]" value="${qty}" class="w-16 border rounded text-center p-1 text-sm" min="1" onchange="recalculateTotals()">
            </td>
            <td class="px-6 py-4 text-right">
                <input type="text" name="cost_price[]" value="${cost.toFixed(2)}" class="w-24 border rounded text-right p-1 text-sm" onchange="recalculateTotals()">
            </td>
            <td class="px-6 py-4 text-right text-sm font-bold text-gray-900 item-subtotal">R$ ${subtotal.toFixed(2).replace('.', ',')}</td>
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
            const qty = parseInt(row.querySelector('input[name="quantity[]"]').value, 10) || 0;
            const cost = parseFloat(row.querySelector('input[name="cost_price[]"]').value.replace(',', '.')) || 0;
            const subtotal = qty * cost;
            const subEl = row.querySelector('.item-subtotal');
            if (subEl) subEl.textContent = 'R$ ' + subtotal.toFixed(2).replace('.', ',');
            total += subtotal;
        });
        const totalEl = document.getElementById('new-items-total');
        const valueEl = document.getElementById('new-items-total-value');
        const submitBtn = document.getElementById('submit-btn');
        if (rows.length > 0) {
            totalEl.classList.remove('hidden');
            if (valueEl) valueEl.textContent = total.toFixed(2).replace('.', ',');
            submitBtn.disabled = false;
        } else {
            totalEl.classList.add('hidden');
            if (valueEl) valueEl.textContent = '0,00';
            submitBtn.disabled = true;
        }
        const emptyState = document.getElementById('empty-state');
        emptyState.style.display = rows.length === 0 ? 'block' : 'none';
    }

    document.getElementById('add-items-form').addEventListener('submit', function (e) {
        const rows = document.querySelectorAll('#items-tbody tr');
        if (rows.length === 0) {
            e.preventDefault();
            alert('Adicione ao menos um produto antes de enviar.');
        }
    });
</script>

<?php require 'views/layouts/footer.php'; ?>
