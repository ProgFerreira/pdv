<?php require 'views/layouts/header.php'; ?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800 uppercase tracking-tight">📥 Importação de Produtos</h2>
    <p class="text-sm text-gray-500 mt-1">Importe produtos em lote através de planilha Excel ou CSV</p>
</div>

<?php if (isset($_GET['success']) && $_GET['success'] == 'imported'): ?>
    <?php 
    $imported = $_GET['imported'] ?? 0;
    $errors = $_GET['errors'] ?? 0;
    $errorMessages = $_SESSION['import_errors'] ?? [];
    unset($_SESSION['import_errors']); // Limpar após exibir
    ?>
    
    <div class="mb-6">
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 rounded shadow-sm mb-4 flex items-center gap-3">
            <i class="fas fa-check-circle text-emerald-500 text-xl"></i>
            <div>
                <p class="font-bold">Importação concluída!</p>
                <p class="text-sm">
                    <?php echo "{$imported} produto(s) importado(s) com sucesso."; ?>
                    <?php if ($errors > 0): ?>
                        <span class="text-red-600 font-bold"><?php echo "{$errors} erro(s) encontrado(s)."; ?></span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        
        <?php if (!empty($errorMessages)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 rounded shadow-sm">
                <div class="p-4">
                    <h3 class="font-bold text-red-800 mb-3 flex items-center gap-2">
                        <i class="fas fa-exclamation-triangle"></i> Detalhes dos Erros:
                    </h3>
                    <div class="max-h-96 overflow-y-auto">
                        <ul class="space-y-1 text-sm text-red-700">
                            <?php foreach ($errorMessages as $errorMsg): ?>
                                <li class="flex items-start gap-2">
                                    <span class="text-red-500 mt-0.5">•</span>
                                    <span><?php echo htmlspecialchars($errorMsg); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded shadow-sm mb-6 flex items-center gap-3">
        <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
        <span><?php echo htmlspecialchars($error); ?></span>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-6 gap-6">
    <!-- Formulário de Importação -->
    <div class="lg:col-span-5">
        <div class="card-standard overflow-hidden">
            <div class="card-standard-header"><i class="fas fa-file-upload"></i> Upload de Planilha</div>
            <div class="card-standard-body">
                <form method="POST" enctype="multipart/form-data" action="?route=import/products">
                    <?php echo csrf_field(); ?>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Selecione o arquivo (.xls, .xlsx ou .csv)
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-primary transition-colors">
                            <div class="space-y-1 text-center">
                                <i class="fas fa-file-excel text-4xl text-gray-400 mb-2"></i>
                                <div class="flex text-sm text-gray-600">
                                    <label for="file" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-primary-hover focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                                        <span>Clique para selecionar</span>
                                        <input id="file" name="file" type="file" accept=".xls,.xlsx,.csv" class="sr-only" required>
                                    </label>
                                    <p class="pl-1">ou arraste o arquivo aqui</p>
                                </div>
                                <p class="text-xs text-gray-500">Formatos aceitos: .xls, .xlsx, .csv</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <h4 class="font-bold text-blue-800 mb-2 flex items-center gap-2">
                            <i class="fas fa-info-circle"></i> Instruções
                        </h4>
                        <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
                            <li>Baixe o modelo de planilha antes de importar</li>
                            <li>Mantenha os cabeçalhos exatamente como no modelo</li>
                            <li>Campos obrigatórios: <strong>nome</strong>, <strong>categoria</strong>, <strong>preco</strong></li>
                            <li>Categorias, Marcas e Fornecedores devem existir no sistema (use os nomes exatos)</li>
                            <li>Use ponto (.) ou vírgula (,) para valores decimais</li>
                            <li>Para consignado, use "Sim" ou "Não"</li>
                            <li>Linhas vazias serão ignoradas automaticamente</li>
                        </ul>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-primary hover:bg-primary-hover text-white font-bold py-3 px-6 rounded-lg shadow-md flex items-center justify-center gap-2 transition-all">
                            <i class="fas fa-upload"></i> Importar Produtos
                        </button>
                        <a href="?route=import/downloadTemplate" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg shadow-md flex items-center gap-2 transition-all">
                            <i class="fas fa-download"></i> Baixar Modelo
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Informações e Referências -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Estrutura da Planilha -->
        <div class="card-standard overflow-hidden">
            <div class="card-standard-header"><i class="fas fa-table"></i> Estrutura da Planilha</div>
            <div class="card-standard-body">
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-gray-600">nome</span>
                        <span class="text-red-500 font-bold">Obrigatório</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">codigo</span>
                        <span class="text-gray-400">Opcional</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">categoria</span>
                        <span class="text-red-500 font-bold">Obrigatório</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">marca</span>
                        <span class="text-gray-400">Opcional</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">preco</span>
                        <span class="text-red-500 font-bold">Obrigatório</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">custo</span>
                        <span class="text-gray-400">Opcional</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">estoque</span>
                        <span class="text-gray-400">Opcional</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">unidade</span>
                        <span class="text-gray-400">Opcional</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">localizacao</span>
                        <span class="text-gray-400">Opcional</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">ean</span>
                        <span class="text-gray-400">Opcional</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">observacoes</span>
                        <span class="text-gray-400">Opcional</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">fornecedor</span>
                        <span class="text-gray-400">Opcional</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">consignado</span>
                        <span class="text-gray-400">Opcional</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Referências Disponíveis -->
        <div class="card-standard overflow-hidden">
            <div class="card-standard-header"><i class="fas fa-list"></i> Referências Disponíveis</div>
            <div class="card-standard-body space-y-3 text-xs">
                <div>
                    <span class="font-bold text-gray-600">Categorias:</span>
                    <div class="mt-1 flex flex-wrap gap-1">
                        <?php foreach (array_slice($categories, 0, 5) as $cat): ?>
                            <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded"><?php echo htmlspecialchars($cat['name']); ?></span>
                        <?php endforeach; ?>
                        <?php if (count($categories) > 5): ?>
                            <span class="px-2 py-1 bg-gray-100 text-gray-500 rounded">+<?php echo count($categories) - 5; ?> mais</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!empty($brands)): ?>
                <div>
                    <span class="font-bold text-gray-600">Marcas:</span>
                    <div class="mt-1 flex flex-wrap gap-1">
                        <?php foreach (array_slice($brands, 0, 5) as $brand): ?>
                            <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded"><?php echo htmlspecialchars($brand['name']); ?></span>
                        <?php endforeach; ?>
                        <?php if (count($brands) > 5): ?>
                            <span class="px-2 py-1 bg-gray-100 text-gray-500 rounded">+<?php echo count($brands) - 5; ?> mais</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($suppliers)): ?>
                <div>
                    <span class="font-bold text-gray-600">Fornecedores:</span>
                    <div class="mt-1 flex flex-wrap gap-1">
                        <?php foreach (array_slice($suppliers, 0, 5) as $supp): ?>
                            <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded"><?php echo htmlspecialchars($supp['name']); ?></span>
                        <?php endforeach; ?>
                        <?php if (count($suppliers) > 5): ?>
                            <span class="px-2 py-1 bg-gray-100 text-gray-500 rounded">+<?php echo count($suppliers) - 5; ?> mais</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>
