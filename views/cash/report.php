<?php
$storeName = $_ENV['STORE_NAME'] ?? getenv('STORE_NAME') ?: 'LOJA';
$storeCnpj = $_ENV['STORE_CNPJ'] ?? getenv('STORE_CNPJ') ?: '';
$storeAddress = $_ENV['STORE_ADDRESS'] ?? getenv('STORE_ADDRESS') ?: '';
$storePhone = $_ENV['STORE_PHONE'] ?? getenv('STORE_PHONE') ?: '';
$summaryVal = $summary ?? [];
$salesVal = $summaryVal['sales'] ?? 0;
$supplyVal = $summaryVal['supply'] ?? 0;
$bleedVal = $summaryVal['bleed'] ?? 0;
$currentBalance = $summaryVal['current_balance'] ?? 0;
$closingBalance = $register['closing_balance'];
$difference = $closingBalance !== null ? ($closingBalance - $currentBalance) : 0;

$reportLines = [
    ['label' => '(+) Saldo Inicial', 'value' => 'R$ ' . number_format((float)($register['opening_balance'] ?? 0), 2, ',', '.')],
    ['label' => '(+) Vendas', 'value' => 'R$ ' . number_format($salesVal, 2, ',', '.')],
    ['label' => '(+) Entradas (Suprim.)', 'value' => 'R$ ' . number_format($supplyVal, 2, ',', '.')],
    ['label' => '(-) Saídas (Sangria)', 'value' => 'R$ ' . number_format($bleedVal, 2, ',', '.')],
    ['label' => '(=) Saldo Esperado', 'value' => 'R$ ' . number_format($currentBalance, 2, ',', '.')],
    ['label' => '(F) Saldo Informado', 'value' => $closingBalance !== null ? 'R$ ' . number_format((float)$closingBalance, 2, ',', '.') : 'Pendente'],
];
if ($difference != 0) {
    $reportLines[] = ['label' => 'Diferenca', 'value' => 'R$ ' . number_format($difference, 2, ',', '.')];
}

$cashReportPayload = [
    'type' => 'cash_report',
    'store_name' => $storeName,
    'store_cnpj' => $storeCnpj,
    'store_address' => $storeAddress,
    'store_phone' => $storePhone,
    'title' => 'FECHAMENTO DE CAIXA',
    'order_number' => (string)$register['id'],
    'user_name' => $register['user_name'] ?? '',
    'opened_at' => $register['opened_at'] ? date('d/m/Y H:i', strtotime($register['opened_at'])) : '',
    'closed_at' => $register['closed_at'] ? date('d/m/Y H:i', strtotime($register['closed_at'])) : 'Em aberto',
    'lines' => $reportLines,
    'payment_methods' => array_map(function ($pm) {
        return [
            'payment_method' => $pm['payment_method'] ?? '',
            'count' => (int)($pm['count'] ?? 0),
            'total' => (float)($pm['total'] ?? 0),
        ];
    }, $paymentMethods ?? []),
    'notes' => 'Sistema PDV - ' . date('d/m/Y H:i:s'),
];
$printReceiptBase = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
$printReceiptUrl = $printReceiptBase . '/print_receipt.php';
$csrfToken = function_exists('csrf_token') ? csrf_token() : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Fechamento de Caixa #
        <?php echo $register['id']; ?>
    </title>
    <link href="<?php echo defined('BASE_URL') ? htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') : ''; ?>public/css/tailwind.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                font-size: 12px;
                background: white;
            }

            .container {
                max-width: 100% !important;
                border: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }
        }

        body {
            font-family: 'Courier New', Courier, monospace;
        }
    </style>
</head>

<body class="bg-gray-100 p-4 sm:p-8">

    <!-- Actions (Top) -->
    <div class="max-w-md mx-auto mb-6 no-print flex flex-wrap gap-4">
        <button onclick="window.print()"
            class="flex-1 bg-primary hover:bg-primary-hover text-white px-6 py-3 rounded-lg shadow-md font-bold flex items-center justify-center gap-2">
            <i class="fas fa-print"></i> IMPRIMIR (tela)
        </button>
        <button type="button" id="btn-print-thermal"
            class="flex-1 bg-gray-700 hover:bg-gray-800 text-white px-6 py-3 rounded-lg shadow-md font-bold flex items-center justify-center gap-2">
            <i class="fas fa-receipt"></i> Impressora térmica
        </button>
        <a href="?route=dashboard/index"
            class="flex-1 bg-white hover:bg-gray-50 text-gray-700 px-6 py-3 rounded-lg shadow-md font-bold border border-gray-300 flex items-center justify-center gap-2 text-center">
            <i class="fas fa-arrow-left"></i> VOLTAR
        </a>
        <p id="print-thermal-msg" class="w-full text-sm text-center min-h-[1.25rem]"></p>
    </div>

    <div class="max-w-md mx-auto bg-white p-6 border border-gray-300 shadow-sm container">
        <!-- Header -->
        <div class="text-center border-b-2 border-black pb-4 mb-4">
            <h1 class="text-xl font-bold uppercase">Fechamento de Caixa</h1>
            <p class="text-sm">Operador: <strong>
                    <?php echo $register['user_name']; ?>
                </strong></p>
            <p class="text-xs">ID: #
                <?php echo $register['id']; ?>
            </p>
        </div>

        <!-- Timestamps -->
        <div class="mb-4 text-xs">
            <div class="flex justify-between">
                <span>Abertura:</span>
                <span>
                    <?php echo date('d/m/Y H:i', strtotime($register['opened_at'])); ?>
                </span>
            </div>
            <div class="flex justify-between">
                <span>Fechamento:</span>
                <span>
                    <?php echo $register['closed_at'] ? date('d/m/Y H:i', strtotime($register['closed_at'])) : '<em>Em aberto</em>'; ?>
                </span>
            </div>
        </div>

        <!-- Summary -->
        <div class="border-b border-gray-300 pb-2 mb-4">
            <h2 class="font-bold uppercase text-sm mb-2">Resumo Financeiro</h2>
            <div class="flex justify-between text-sm">
                <span>(+) Saldo Inicial:</span>
                <span>R$
                    <?php echo number_format($register['opening_balance'], 2, ',', '.'); ?>
                </span>
            </div>
            <div class="flex justify-between text-sm">
                <span>(+) Vendas:</span>
                <span>R$
                    <?php echo number_format($summary['sales'] ?? 0, 2, ',', '.'); ?>
                </span>
            </div>
            <div class="flex justify-between text-sm">
                <span>(+) Entradas (Suprim.):</span>
                <span>R$
                    <?php echo number_format($summary['supply'] ?? 0, 2, ',', '.'); ?>
                </span>
            </div>
            <div class="flex justify-between text-sm">
                <span>(-) Saídas (Sangria):</span>
                <span>R$
                    <?php echo number_format($summary['bleed'] ?? 0, 2, ',', '.'); ?>
                </span>
            </div>
            <div class="flex justify-between font-bold text-base mt-2 pt-2 border-t border-black">
                <span>(=) Saldo Esperado:</span>
                <span>R$
                    <?php echo number_format($summary['current_balance'], 2, ',', '.'); ?>
                </span>
            </div>
            <div class="flex justify-between text-sm mt-1">
                <span>(F) Saldo Informado:</span>
                <span>
                    <?php echo $register['closing_balance'] !== null ? 'R$ ' . number_format($register['closing_balance'], 2, ',', '.') : '<span class="italic text-gray-400">Pendente</span>'; ?>
                </span>
            </div>

            <?php
            $difference = $register['closing_balance'] - $summary['current_balance'];
            if ($difference != 0):
                ?>
                <div
                    class="flex justify-between text-sm font-bold <?php echo $difference > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                    <span>Diferença:</span>
                    <span>R$
                        <?php echo number_format($difference, 2, ',', '.'); ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Payment Methods -->
        <div class="border-b border-gray-300 pb-2 mb-4">
            <h2 class="font-bold uppercase text-sm mb-2">Vendas por Pagamento</h2>
            <?php 
            $totalGeralVendas = 0;
            foreach ($paymentMethods as $pm): 
                $totalGeralVendas += $pm['total'];
            ?>
                <div class="flex justify-between text-xs mb-1">
                    <span>
                        <?php echo $pm['payment_method']; ?> (
                        <?php echo $pm['count']; ?>x):
                    </span>
                    <span>R$
                        <?php echo number_format($pm['total'], 2, ',', '.'); ?>
                    </span>
                </div>
            <?php endforeach; ?>
            <div class="flex justify-between font-bold text-sm mt-2 pt-2 border-t border-gray-400">
                <span>Total Geral:</span>
                <span>R$
                    <?php echo number_format($totalGeralVendas, 2, ',', '.'); ?>
                </span>
            </div>
        </div>

        <!-- Notes -->
        <?php if (!empty($register['notes'])): ?>
            <div class="mb-4">
                <h2 class="font-bold uppercase text-sm mb-1">Observações:</h2>
                <p class="text-xs italic">
                    <?php echo nl2br(htmlspecialchars($register['notes'])); ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="text-center text-[10px] text-gray-400 mt-8">
            <p>Gerado pelo Sistema PDV -
                <?php echo date('d/m/Y H:i:s'); ?>
            </p>
        </div>
    </div>

    <!-- Actions (barra fixa inferior - sempre visível) -->
    <div class="fixed bottom-8 left-4 right-4 no-print flex flex-wrap justify-center gap-3 sm:left-auto sm:right-8 sm:justify-end">
        <button onclick="window.print()"
            class="bg-primary hover:bg-primary-hover text-white px-6 py-3 rounded shadow-lg font-bold transition-transform hover:scale-105 flex items-center gap-2">
            <i class="fas fa-print"></i> IMPRIMIR (tela)
        </button>
        <button type="button" id="btn-print-thermal-bottom"
            class="bg-gray-700 hover:bg-gray-800 text-white px-6 py-3 rounded shadow-lg font-bold flex items-center gap-2">
            <i class="fas fa-receipt"></i> Impressora térmica
        </button>
        <a href="?route=dashboard/index"
            class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded shadow font-bold border border-gray-300 flex items-center justify-center">
            VOLTAR
        </a>
    </div>

    <script>
    (function() {
        var payload = <?php echo json_encode($cashReportPayload, JSON_UNESCAPED_UNICODE); ?>;
        var url = <?php echo json_encode($printReceiptUrl, JSON_UNESCAPED_SLASHES); ?>;
        var csrfToken = <?php echo json_encode($csrfToken, JSON_UNESCAPED_UNICODE); ?>;
        var msg = document.getElementById('print-thermal-msg');
        payload.csrf_token = csrfToken;
        function doPrint() {
            var btns = document.querySelectorAll('#btn-print-thermal, #btn-print-thermal-bottom');
            btns.forEach(function(b) { b.disabled = true; });
            if (msg) { msg.textContent = 'Enviando para impressora...'; msg.style.color = ''; }
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            }).then(function(r) {
                var status = r.status;
                return r.text().then(function(text) {
                    var t = (text && text.trim()) || '';
                    var start = t.indexOf('{');
                    if (start !== -1) {
                        var jsonStr = t.substring(start);
                        try { return JSON.parse(jsonStr); } catch (e1) {
                            var lastBrace = t.lastIndexOf('}');
                            if (lastBrace > start) {
                                try { return JSON.parse(t.substring(start, lastBrace + 1)); } catch (e2) {}
                            }
                            return { ok: false, error: 'JSON invalido.' };
                        }
                    }
                    if (status === 403) {
                        return { ok: false, error: 'Sessao ou token invalido. Tente novamente.' };
                    }
                    if (status === 302 || status === 301) {
                        return { ok: false, error: 'Sessao expirada. Faca login novamente.' };
                    }
                    if (status === 500 && start !== -1) {
                        var obj = null;
                        try { obj = JSON.parse(t.substring(start)); } catch (e) {}
                        if (obj && typeof obj.error === 'string') {
                            return { ok: false, error: obj.error };
                        }
                    }
                    return { ok: false, error: 'Resposta invalida (status ' + status + '). Verifique storage/logs/print.log' };
                });
            }).then(function(data) {
                if (msg) {
                    var fallback = document.getElementById('btn-print-screen-fallback');
                    if (fallback) fallback.remove();
                    if (data && data.ok) {
                        msg.textContent = 'Enviado para impressora.';
                        msg.style.color = 'green';
                    } else {
                        var err = data && data.error ? data.error : 'Erro ao imprimir.';
                        msg.textContent = err;
                        msg.style.color = 'red';
                        if (err.indexOf('Imprimir (tela)') !== -1) {
                            var btnScreen = document.createElement('button');
                            btnScreen.type = 'button';
                            btnScreen.id = 'btn-print-screen-fallback';
                            btnScreen.className = 'mt-2 block w-full bg-primary hover:bg-primary-hover text-white px-4 py-2 rounded font-bold';
                            btnScreen.textContent = 'Imprimir (tela)';
                            btnScreen.onclick = function() { window.print(); };
                            msg.parentNode.appendChild(btnScreen);
                        }
                    }
                }
            }).catch(function(e) {
                if (msg) {
                    msg.textContent = 'Erro: ' + (e.message || 'sem conexao');
                    msg.style.color = 'red';
                }
            }).finally(function() {
                document.querySelectorAll('#btn-print-thermal, #btn-print-thermal-bottom').forEach(function(b) { b.disabled = false; });
            });
        }
        var btnTop = document.getElementById('btn-print-thermal');
        var btnBottom = document.getElementById('btn-print-thermal-bottom');
        if (btnTop) btnTop.addEventListener('click', doPrint);
        if (btnBottom) btnBottom.addEventListener('click', doPrint);
    })();
    </script>

</body>

</html>