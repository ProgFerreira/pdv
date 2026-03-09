<?php require 'views/layouts/header.php'; ?>

<div class="w-[95vw] max-w-full mx-auto">
    <div class="card-standard overflow-hidden">
        <div class="card-standard-header">
            <i class="fas fa-box"></i>
            <?php echo isset($isEdit) ? 'Editar Produto' : 'Novo Produto'; ?>
        </div>
        <div class="card-standard-body">
            <form method="POST"
                    action="<?php echo htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8'); ?>?route=<?php echo isset($isEdit) ? 'product/edit&id=' . (int)($product['id'] ?? 0) : 'product/create'; ?>"
                    enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        <!-- Coluna Esquerda: Dados Principais -->
                        <div class="md:col-span-2 space-y-6">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nome <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="name"
                                    class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50"
                                    required placeholder="Ex: Terço de Madeira"
                                    value="<?php echo htmlspecialchars($product['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Código</label>
                                    <input type="text" name="code"
                                        class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50"
                                        placeholder="Ex: PROD-001"
                                        value="<?php echo htmlspecialchars($product['code'] ?? ''); ?>">
                                    <p class="text-xs text-gray-500 mt-1">Código interno para busca no PDV e listagens.</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">EAN/GTIN (Código de Barras)</label>
                                    <div class="relative">
                                        <input type="text" name="ean"
                                            class="w-full border border-gray-300 p-2 pr-10 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50"
                                            placeholder="Use o leitor ou digite"
                                            value="<?php echo htmlspecialchars($product['ean'] ?? ''); ?>">
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-barcode text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Localização</label>
                                    <input type="text" name="location"
                                        class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50"
                                        placeholder="Ex: Prateleira B1"
                                        value="<?php echo htmlspecialchars($product['location'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Categoria <span
                                            class="text-red-500">*</span></label>
                                    <select name="category_id"
                                        class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50"
                                        required>
                                        <option value="">Selecione...</option>
                                        <?php foreach ($categories as $c): ?>
                                            <option value="<?php echo $c['id']; ?>" <?php echo (isset($product['category_id']) && $product['category_id'] == $c['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                                    <select name="brand_id"
                                        class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50">
                                        <option value="">Selecione...</option>
                                        <?php foreach ($brands as $b): ?>
                                            <option value="<?php echo $b['id']; ?>" <?php echo (isset($product['brand_id']) && $product['brand_id'] == $b['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($b['name'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <?php if (isAdmin()): ?>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Setor do Produto <span
                                            class="text-red-500">*</span></label>
                                    <select name="sector_id"
                                        class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50"
                                        required>
                                        <?php 
                                            $currentId = $product['sector_id'] ?? ($_SESSION['sector_id'] ?? 1);
                                            foreach ($sectors as $s): 
                                        ?>
                                            <option value="<?php echo $s['id']; ?>" <?php echo ($currentId == $s['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">Isso define em qual estoque e PDV este produto
                                        aparecerá.</p>
                                </div>
                            <?php else: ?>
                                <input type="hidden" name="sector_id"
                                    value="<?php echo htmlspecialchars((string)($product['sector_id'] ?? $_SESSION['sector_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                            <?php endif; ?>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                                <textarea name="observations"
                                    class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50"
                                    rows="3"><?php echo htmlspecialchars($product['observations'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>

                            <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 flex flex-col gap-4">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="is_consigned" value="1" 
                                        <?php echo (isset($product['is_consigned']) && $product['is_consigned']) ? 'checked' : ''; ?>
                                        class="w-5 h-5 text-primary rounded border-gray-300 focus:ring-primary">
                                    <span class="text-sm font-bold text-blue-800 uppercase tracking-tight">📦 Este é um Produto Consignado</span>
                                </label>

                                <div>
                                    <label class="block text-[10px] font-black text-blue-400 uppercase mb-1">Fornecedor do Item</label>
                                    <select name="supplier_id" class="w-full border border-gray-300 p-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50">
                                        <option value="">-- Selecione o Fornecedor (Obrigatório se consignado) --</option>
                                        <?php foreach ($suppliers as $sup): ?>
                                            <option value="<?php echo $sup['id']; ?>" 
                                                <?php echo (isset($product['supplier_id']) && $product['supplier_id'] == $sup['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($sup['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="pt-4">
                                <a href="<?php echo htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8'); ?>?route=product/index"
                                    class="text-gray-600 hover:text-gray-900 font-medium underline">Cancelar e
                                    Voltar</a>
                            </div>
                        </div>

                        <!-- Coluna Direita: Foto e Preços -->
                        <div class="bg-gray-50/50 p-6 border border-gray-200">
                            <div class="mb-6 text-center">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Imagem do Produto</label>
                                <div
                                    class="bg-white border border-gray-200 p-2 mb-3 flex items-center justify-center h-48 w-full overflow-hidden relative">
                                    <img id="preview-img"
                                        src="<?php echo htmlspecialchars($product['image'] ?? 'https://via.placeholder.com/200?text=Sem+Foto', ENT_QUOTES, 'UTF-8'); ?>"
                                        class="max-h-full max-w-full object-contain">
                                </div>
                                <label class="block w-full">
                                    <span class="sr-only">Escolher foto</span>
                                    <input type="file" name="image" accept="image/*" onchange="previewImage(this)"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-primary-hover cursor-pointer" />
                                </label>
                            </div>

                            <div class="border-t border-gray-200 my-6"></div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Preço de Venda <span
                                            class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">R$</span>
                                        </div>
                                        <input type="text" name="price"
                                            class="w-full border border-gray-300 pl-10 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 text-lg font-bold text-gray-800"
                                            placeholder="0,00" required value="<?php echo htmlspecialchars($product['price'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Preço de Custo</label>
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">R$</span>
                                        </div>
                                        <input type="text" name="cost_price"
                                            class="w-full border border-gray-300 pl-10 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 text-gray-600"
                                            placeholder="0,00" value="<?php echo htmlspecialchars($product['cost_price'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                </div>

                                <div class="bg-emerald-50 p-4 rounded-lg border border-emerald-100">
                                    <span class="block text-xs font-bold text-emerald-700 uppercase mb-2">📋 Ficha Técnica / Formação de preço</span>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-sm text-gray-700 mb-1">Porção final (g)</label>
                                            <input type="number" name="yield_target_grams" min="0" step="1"
                                                class="w-full border border-gray-300 p-2 rounded text-sm"
                                                placeholder="Ex: 450"
                                                value="<?php echo htmlspecialchars((string)($product['yield_target_grams'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                        <div>
                                            <label class="block text-sm text-gray-700 mb-1">Margem bruta (%)</label>
                                            <input type="text" name="margin_percent"
                                                class="w-full border border-gray-300 p-2 rounded text-sm"
                                                placeholder="65"
                                                value="<?php echo htmlspecialchars((string)($product['margin_percent'] ?? '65'), ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                    </div>
                                    <p class="text-xs text-emerald-600 mt-1">Opcional. Use &quot;Ficha Técnica&quot; na listagem para montar a receita e ver o preço sugerido.</p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Unidade</label>
                                        <select name="unit"
                                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50">
                                            <option value="UN" <?php echo (isset($product['unit']) && $product['unit'] == 'UN') ? 'selected' : ''; ?>>UN</option>
                                            <option value="KG" <?php echo (isset($product['unit']) && $product['unit'] == 'KG') ? 'selected' : ''; ?>>KG</option>
                                            <option value="L" <?php echo (isset($product['unit']) && $product['unit'] == 'L') ? 'selected' : ''; ?>>L</option>
                                            <option value="M" <?php echo (isset($product['unit']) && $product['unit'] == 'M') ? 'selected' : ''; ?>>M</option>
                                            <option value="CX" <?php echo (isset($product['unit']) && $product['unit'] == 'CX') ? 'selected' : ''; ?>>CX</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Estoque Inicial</label>
                                        <input type="number" name="stock"
                                            class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 font-bold"
                                            value="<?php echo htmlspecialchars((string)($product['stock'] ?? '0'), ENT_QUOTES, 'UTF-8'); ?>" required>
                                        <p class="text-[10px] text-amber-600 mt-1 leading-tight font-medium">
                                            ⚠️ Para controle profissional (FIFO/Nota Fiscal), deixe em zero e registre uma **Entrada de Estoque**.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <button type="submit"
                                class="w-full bg-primary hover:bg-primary-hover text-white font-bold py-3 px-4 rounded-lg shadow-lg mt-6 transition-transform transform active:scale-95 flex justify-center items-center gap-2">
                                <i class="fas fa-save"></i> Salvar Produto
                            </button>
                        </div>
                    </div>
                </form>

                <?php if (isset($isEdit) && !empty($batches)): ?>
                    <!-- Seção de Lotes/NF na Edição -->
                    <div class="mt-12 pt-8 border-t-2 border-dashed border-gray-100">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="bg-blue-600 text-white w-10 h-10 rounded-xl flex items-center justify-center shadow-lg shadow-blue-100">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-gray-800 uppercase tracking-tight">🔎 Rastreabilidade de Notas</h3>
                                <p class="text-xs text-gray-400 font-medium">Lotes ativos que compõem o estoque atual deste produto.</p>
                            </div>
                        </div>

                        <div class="bg-white border border-gray-100 overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead class="bg-gray-50/50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Referência / NF</th>
                                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Data Entrada</th>
                                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Fornecedor</th>
                                        <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Saldo no Lote</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <?php foreach ($batches as $bt): ?>
                                        <tr class="hover:bg-blue-50/30 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-700">
                                                #<?php echo htmlspecialchars($bt['nf_reference'] ?: $bt['stock_entry_id']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                                                <?php echo date('d/m/Y', strtotime($bt['entry_date'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo htmlspecialchars($bt['entry_supplier'] ?: '-'); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <span class="bg-blue-50 text-blue-700 font-black px-3 py-1 rounded-full text-xs">
                                                    <?php echo number_format($bt['current_quantity'], 0); ?> un
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('preview-img').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<?php require 'views/layouts/footer.php'; ?>