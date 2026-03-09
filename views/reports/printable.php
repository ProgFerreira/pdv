<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Relatório de Vendas - Impressão</title>
    <link href="<?php echo defined('BASE_URL') ? htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') : ''; ?>public/css/tailwind.css" rel="stylesheet">
    <style>
        @media print {
            body {
                font-size: 12px;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-after: always;
            }
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            background: white;
            color: black;
        }
    </style>
</head>

<body class="p-8 max-w-4xl mx-auto">

    <!-- Header -->
    <div class="text-center border-b-2 border-black pb-4 mb-6">
        <h1 class="text-2xl font-bold uppercase">Relatório de Vendas</h1>
        <p class="mt-1">Período: <strong><?php echo date('d/m/Y', strtotime($startDate)); ?></strong> a
            <strong><?php echo date('d/m/Y', strtotime($endDate)); ?></strong></p>
        <p class="text-sm">Gerado em: <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>

    <!-- Summary -->
    <div class="mb-8">
        <h2 class="text-lg font-bold border-b border-black mb-2 uppercase">Resumo Geral</h2>
        <div class="flex justify-between">
            <span>Vendas Realizadas:</span>
            <span class="font-bold"><?php echo $totals['count']; ?></span>
        </div>
        <div class="flex justify-between text-xl mt-2">
            <span>Valor Total Vendido:</span>
            <span class="font-bold">R$ <?php echo number_format($totals['total'] ?? 0, 2, ',', '.'); ?></span>
        </div>
    </div>

    <!-- Payment Methods -->
    <div class="mb-8">
        <h2 class="text-lg font-bold border-b border-black mb-2 uppercase">Somatória por Meio de Pagamento</h2>
        <table class="w-full text-left">
            <thead>
                <tr class="border-b border-gray-300">
                    <th class="py-1">Método</th>
                    <th class="py-1 text-right">Qtd</th>
                    <th class="py-1 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($methods as $m): ?>
                    <tr>
                        <td class="py-1"><?php echo $m['payment_method']; ?></td>
                        <td class="py-1 text-right"><?php echo $m['count']; ?></td>
                        <td class="py-1 text-right font-bold">R$ <?php echo number_format($m['total'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Products Sold -->
    <div class="mb-8">
        <h2 class="text-lg font-bold border-b border-black mb-2 uppercase">Produtos Vendidos</h2>
        <table class="w-full text-left">
            <thead>
                <tr class="border-b border-gray-300">
                    <th class="py-1">Produto</th>
                    <th class="py-1 text-center">Qtd</th>
                    <th class="py-1 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td class="py-1"><?php echo $p['name']; ?></td>
                        <td class="py-1 text-center"><?php echo $p['qty']; ?></td>
                        <td class="py-1 text-right">R$ <?php echo number_format($p['total'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Actions -->
    <div class="fixed bottom-8 right-8 no-print flex gap-4">
        <button onclick="window.print()"
            class="bg-black text-white px-6 py-3 rounded shadow font-bold hover:bg-gray-800">
            🖨️ IMPRIMIR
        </button>
        <button onclick="window.close()"
            class="bg-gray-200 text-black px-6 py-3 rounded shadow font-bold hover:bg-gray-300">
            FECHAR
        </button>
    </div>

</body>

</html>