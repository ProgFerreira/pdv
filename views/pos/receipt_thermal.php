<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Recibo #
        <?php echo $sale['id']; ?>
    </title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Courier+Prime&display=swap');

        /* Reset e Configurações Gerais para Impressão */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier Prime', 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            background: #fff;
            width: 100%;
        }

        /* Container do Recibo - Ajustável para 58mm ou 80mm */
        .receipt {
            width: 72mm;
            /* Padrão 80mm (com margem) - Ajustar se for 58mm */
            margin: 0 auto;
            padding: 2mm 0;
        }

        /* Utilitários */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 1mm 0;
        }

        /* Seções */
        .header,
        .footer {
            margin-bottom: 2mm;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table th,
        .items-table td {
            text-align: left;
            padding: 1px 0;
            vertical-align: top;
        }

        .items-table td.qty {
            width: 10%;
            text-align: center;
        }

        .items-table td.total {
            text-align: right;
        }

        /* Totais */
        .totals {
            margin-top: 2mm;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
        }

        /* Impressão: reduzir papel em branco quando usar Ctrl+P no navegador */
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }
            body {
                margin: 0;
                padding: 0;
                height: auto;
                min-height: 0;
            }
            .receipt {
                width: 72mm;
                margin: 0 auto;
                padding: 0 2mm;
                page-break-after: avoid;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<?php
$storeName = $_ENV['STORE_NAME'] ?? getenv('STORE_NAME') ?: 'LOJA RELIGIOSA';
$storeCnpj = $_ENV['STORE_CNPJ'] ?? getenv('STORE_CNPJ') ?: '00.000.000/0001-00';
$storeAddress = $_ENV['STORE_ADDRESS'] ?? getenv('STORE_ADDRESS') ?: 'Rua Exemplo, 123 - Centro';
$storePhone = $_ENV['STORE_PHONE'] ?? getenv('STORE_PHONE') ?: 'Tel: (11) 99999-9999';
$deliveryAddress = trim((string)($sale['delivery_address'] ?? ''));
$customerPhone = trim((string)($sale['customer_phone'] ?? ''));
$isPickup = !empty($sale['is_pickup']);
$receiptPayload = [
    'store_name' => $storeName,
    'store_cnpj' => $storeCnpj,
    'store_address' => $storeAddress,
    'store_phone' => $storePhone,
    'title' => 'CUPOM NAO FISCAL',
    'order_number' => str_pad((string) ($sale['id'] ?? ''), 6, '0', STR_PAD_LEFT),
    'customer_name' => trim((string)($sale['customer_name'] ?? '')),
    'customer_phone' => $customerPhone,
    'delivery_address' => $deliveryAddress,
    'is_pickup' => $isPickup,
    'datetime' => date('d/m/Y H:i', strtotime($sale['created_at'] ?? 'now')),
    'payment_method' => $sale['payment_method'] ?? '',
    'total' => (float) ($sale['total'] ?? 0),
    'amount_paid' => (float) ($sale['amount_paid'] ?? 0),
    'change_amount' => (float) ($sale['change_amount'] ?? 0),
    'discount_amount' => (float) ($sale['discount_amount'] ?? 0),
    'items' => array_map(function ($item) {
        return [
            'desc' => $item['product_name'] ?? '',
            'qty' => (int) ($item['quantity'] ?? 1),
            'unit' => (float) ($item['unit_price'] ?? 0),
        ];
    }, $sale['items'] ?? []),
    'notes' => 'Obrigado pela preferencia!',
];
$printReceiptBase = (defined('BASE_URL') ? rtrim(BASE_URL, '/') : '');
$printReceiptUrl = $printReceiptBase . '/print_receipt.php';
$csrfToken = function_exists('csrf_token') ? csrf_token() : '';
?>
<body onload="window.print()">

    <div class="no-print" style="max-width:72mm;margin:0 auto 12px;padding:8px;text-align:center;">
        <p style="margin-bottom:6px;font-size:12px;font-weight:bold;">Impressora térmica (recomendado)</p>
        <p style="margin-bottom:8px;font-size:11px;color:#333;">Use o botão abaixo para imprimir só o cupom, sem sobra de papel.</p>
        <button type="button" id="btn-print-thermal" style="padding:8px 16px;cursor:pointer;font-weight:bold;">
            Imprimir na impressora
        </button>
        <p id="print-thermal-msg" style="font-size:11px;margin-top:6px;min-height:18px;"></p>
        <p style="margin-top:10px;font-size:10px;color:#666;">Se usar Ctrl+P (Imprimir do navegador), desmarque &quot;Cabeçalhos e rodapés&quot; e use papel 80mm.</p>
    </div>

    <div class="receipt">
        <!-- Cabeçalho -->
        <div class="header text-center">
            <h2 style="font-size: 14px; margin-bottom: 2px;"><?php echo htmlspecialchars($storeName, ENT_QUOTES, 'UTF-8'); ?></h2>
            <p><?php echo htmlspecialchars($storeCnpj, ENT_QUOTES, 'UTF-8'); ?></p>
            <p><?php echo htmlspecialchars($storeAddress, ENT_QUOTES, 'UTF-8'); ?></p>
            <p><?php echo htmlspecialchars($storePhone, ENT_QUOTES, 'UTF-8'); ?></p>
            <div class="divider"></div>
            <p class="uppercase">CUPOM NÃO FISCAL</p>
            <p>Venda: #
                <?php echo str_pad($sale['id'], 6, '0', STR_PAD_LEFT); ?>
            </p>
            <p>Data:
                <?php echo date('d/m/Y H:i', strtotime($sale['created_at'])); ?>
            </p>
        </div>

        <!-- Itens -->
        <div class="divider"></div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>ITEM</th>
                    <th class="qty">QTD</th>
                    <th class="total">VALOR</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sale['items'] as $item): ?>
                    <tr>
                        <td colspan="3">
                            <?php echo $item['product_name']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-left: 5px;">
                            <small>Un: R$
                                <?php echo number_format($item['unit_price'], 2, ',', '.'); ?>
                            </small>
                        </td>
                        <td class="qty">x
                            <?php echo $item['quantity']; ?>
                        </td>
                        <td class="total">R$
                            <?php echo number_format($item['subtotal'], 2, ',', '.'); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="divider"></div>

        <!-- Totais -->
        <div class="totals">
            <?php if ($sale['discount_amount'] > 0): ?>
                <div class="totals-row">
                    <span>Subtotal:</span>
                    <span>R$
                        <?php echo number_format($sale['total'] + $sale['discount_amount'], 2, ',', '.'); ?>
                    </span>
                </div>
                <div class="totals-row">
                    <span>Desconto:</span>
                    <span>- R$
                        <?php echo number_format($sale['discount_amount'], 2, ',', '.'); ?>
                    </span>
                </div>
            <?php endif; ?>

            <div class="totals-row" style="font-size: 14px; font-weight: bold; margin: 2mm 0;">
                <span>TOTAL A PAGAR:</span>
                <span>R$
                    <?php echo number_format($sale['total'], 2, ',', '.'); ?>
                </span>
            </div>

            <div class="divider"></div>

            <div class="totals-row">
                <span>Forma Pagto:</span>
                <span>
                    <?php echo $sale['payment_method']; ?>
                </span>
            </div>
            <div class="totals-row">
                <span>Valor Pago:</span>
                <span>R$
                    <?php echo number_format($sale['amount_paid'], 2, ',', '.'); ?>
                </span>
            </div>
            <div class="totals-row">
                <span>Troco:</span>
                <span>R$
                    <?php echo number_format($sale['change_amount'], 2, ',', '.'); ?>
                </span>
            </div>
        </div>

        <!-- Rodapé -->
        <div class="divider"></div>
        <div class="footer text-center">
            <?php
            $receiptCustomerName = trim((string)($sale['customer_name'] ?? ''));
            if ($receiptCustomerName !== ''): ?>
                <p>Cliente: <?php echo htmlspecialchars($receiptCustomerName); ?></p>
                <?php if ($customerPhone !== ''): ?>
                    <p>Tel: <?php echo htmlspecialchars($customerPhone); ?></p>
                <?php endif; ?>
            <?php else: ?>
                <p>Consumidor Final</p>
            <?php endif; ?>
            <?php if ($isPickup): ?>
                <p class="text-xs font-medium">Cliente retira</p>
            <?php elseif ($deliveryAddress !== ''): ?>
                <p class="text-xs">Entrega: <?php echo htmlspecialchars($deliveryAddress); ?></p>
            <?php endif; ?>

            <br>
            <p>Obrigado pela preferência!</p>
            <p><small>Sistema PDV v1.0</small></p>
        </div>
    </div>

    <script>
    (function() {
        var payload = <?php echo json_encode($receiptPayload, JSON_UNESCAPED_UNICODE); ?>;
        var url = <?php echo json_encode($printReceiptUrl, JSON_UNESCAPED_SLASHES); ?>;
        var csrfToken = <?php echo json_encode($csrfToken, JSON_UNESCAPED_UNICODE); ?>;
        var btn = document.getElementById('btn-print-thermal');
        var msg = document.getElementById('print-thermal-msg');
        if (btn && msg) {
            payload.csrf_token = csrfToken;
            btn.addEventListener('click', function() {
                btn.disabled = true;
                msg.textContent = 'Enviando para impressora...';
                msg.style.color = '';
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
                                return { ok: false, error: 'JSON inválido. Trecho: ' + jsonStr.substring(0, 100).replace(/\s+/g, ' ') };
                            }
                        }
                        if (status === 403) {
                            return { ok: false, error: 'Sessão ou token inválido. Feche esta janela, abra o cupom de novo pela tela do PDV e clique em "Imprimir na impressora".' };
                        }
                        if (status === 302 || status === 301) {
                            return { ok: false, error: 'Sessão expirada. Abra o PDV de novo, abra o cupom e tente imprimir.' };
                        }
                        if (status === 500 && start !== -1) {
                            var obj = null;
                            try { obj = JSON.parse(t.substring(start)); } catch (e) {}
                            if (obj && typeof obj.error === 'string') {
                                return { ok: false, error: obj.error };
                            }
                        }
                        return { ok: false, error: 'Resposta inválida (status ' + status + '). Verifique storage/logs/print.log' };
                    });
                }).then(function(data) {
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
                            btnScreen.style.cssText = 'margin-top:8px;padding:8px 16px;cursor:pointer;font-weight:bold;width:100%;';
                            btnScreen.textContent = 'Imprimir (tela)';
                            btnScreen.onclick = function() { window.print(); };
                            msg.parentNode.appendChild(btnScreen);
                        }
                    }
                }).catch(function(e) {
                    msg.textContent = 'Erro: ' + (e.message || 'sem conexão');
                    msg.style.color = 'red';
                }).finally(function() {
                    btn.disabled = false;
                });
            });
        }
    })();
    </script>

</body>

</html>