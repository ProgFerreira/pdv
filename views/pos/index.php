<?php
$is_pos_page = true; // layout usa main sem padding para PDV ocupar 90% da tela
require 'views/layouts/header.php';
?>
<style>
/* PDV: container das 3 colunas ocupa 90% da viewport */
.pos-main .pos-container { width: 90vw; height: 90vh; max-width: 90vw; max-height: 90vh; }
@media (max-width: 1023px) {
  .pos-main .pos-container { width: 100%; max-width: 100%; height: 100%; min-height: 0; max-height: none; }
  /* Tablet: 2 colunas para cards mais largos que no desktop */
  #product-list { grid-template-columns: repeat(2, 1fr) !important; gap: 8px; }
}
@media (max-width: 768px) {
  .pos-main .pos-container { min-height: 280px; }
  .pos-main { padding: 0; align-items: stretch; }
  /* Mobile: 2 cards por linha; conteúdo centralizado (imagem 50x50 em cima, texto abaixo) */
  #product-list { grid-template-columns: repeat(2, 1fr) !important; gap: 6px; }
  #product-list .product-card > div { min-height: 0; padding: 6px 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; }
  #product-list .product-card > div .pos-card-img { order: -1; width: 50px; height: 50px; min-width: 50px; min-height: 50px; max-height: 50px; margin: 0 auto 6px; flex-shrink: 0; }
  #product-list .product-card > div > div.w-full { flex: 1; min-width: 0; padding-bottom: 0; text-align: center; }
  #product-list .product-card h3 { -webkit-line-clamp: 1; line-clamp: 1; font-size: 0.75rem; text-align: center; }
  #product-list .pos-card-img img { width: 100%; height: 100%; object-fit: cover; }
}
/* Colunas: mais espaço para produtos, carrinho e fechamento mais estreitos */
@media (min-width: 1024px) {
  .pos-container.grid { grid-template-columns: 1fr 290px !important; gap: 8px; }
  .pos-col-cart { flex: 0 0 auto; display: flex; flex-direction: column; min-height: 0; }
  .pos-col-cart .pos-cart-table-wrap { max-height: 45vh; overflow-y: auto; flex-shrink: 0; }
  .pos-col-cart table { font-size: 10px; table-layout: fixed; }
  .pos-col-cart td:first-child { max-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
}
/* Lista de produtos: 5 colunas, cards mais estreitos */
#product-list { display: grid !important; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 6px; align-content: start; }
#product-list .product-card { min-width: 0; }
#product-list .product-card > div { min-height: 210px; display: flex; flex-direction: column; align-items: center; overflow: hidden; padding-bottom: 12px; box-sizing: border-box; }
#product-list .pos-card-img { width: 100px; height: 85px; min-width: 100px; min-height: 100px; flex-shrink: 0; margin: 8px auto; border-radius: 6px; overflow: hidden; background: #f9fafb; border: 1px solid #f3f4f6; display: flex; align-items: center; justify-content: center; }
#product-list .pos-card-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
#product-list .pos-card-img i { font-size: 24px; }
#product-list .product-card h3 { -webkit-line-clamp: 2; line-clamp: 2; display: -webkit-box; -webkit-box-orient: vertical; overflow: hidden; }
#product-list .product-card > div > div.w-full { padding-bottom: 6px; box-sizing: border-box; }
#product-list .pos-list-placeholder,
#product-list .pos-list-full { grid-column: 1 / -1; }
/* Campos Cliente e Busca: uma linha, ícones alinhados */
.pos-search-row { display: flex; flex-direction: row; flex-wrap: nowrap; gap: 0.5rem; align-items: stretch; overflow: visible; }
.pos-field-wrap { flex: 1; min-width: 0; min-height: 2.5rem; display: flex; align-items: center; position: relative; overflow: visible; }
.pos-field-wrap input { width: 100%; min-height: 2.25rem; padding-left: 2rem; padding-right: 0.5rem; padding-top: 0.375rem; padding-bottom: 0.375rem; box-sizing: border-box; border: none; background: transparent; }
.pos-field-wrap .fa-user,
.pos-field-wrap .fa-search { position: absolute; left: 0.5rem; top: 50%; transform: translateY(-50%); margin: 0; width: 1rem; height: 1rem; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; pointer-events: none; }
/* Espaço entre busca e lista de cards */
#product-list { margin-top: 0.5rem; }
/* Margem lateral na coluna de produtos (esquerda e direita) */
.pos-col-products { padding-left: 0.75rem; padding-right: 0.75rem; box-sizing: border-box; }
#product-list { padding-left: 0.5rem; padding-right: 0.5rem; }
/* Caixa fechado = vermelho, Caixa aberto = verde (garantido por inline para evitar cache) */
#pos-btn-abrir-caixa { background-color: #dc2626 !important; color: #fff !important; }
#pos-btn-abrir-caixa:hover { background-color: #b91c1c !important; }
#pos-caixa-aberto-label { background-color: #16a34a !important; color: #fff !important; }
</style>
<!-- PDV: 3 colunas ocupam 90% da tela (90vw x 90vh) -->
<div class="pos-container grid grid-cols-1 lg:grid-cols-2 gap-4 overflow-hidden min-h-0">
    <!-- Coluna 1: Produtos (busca + cards pequenos) -->
    <div class="pos-col-products flex flex-col min-h-0 flex-1 min-w-0 overflow-hidden bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="pos-search-row p-2 flex-shrink-0 flex items-center gap-2 flex-wrap">
            <!-- Botão Abrir caixa (vermelho quando fechado) / Caixa aberto (verde) -->
            <button type="button" id="pos-btn-abrir-caixa" class="hidden flex flex-shrink-0 items-center gap-2 rounded-lg bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 text-sm shadow transition"
                onclick="document.getElementById('openCashModal').classList.remove('hidden'); document.getElementById('opening-amount').focus();"
                title="Abrir caixa e informar valor inicial">
                <i class="fas fa-cash-register"></i>
                <span>Caixa fechado ok.</span>
            </button>
            <span id="pos-caixa-aberto-label" class="hidden flex flex-shrink-0 items-center gap-2 rounded-lg bg-green-600 text-white font-bold py-2 px-4 text-sm shadow">
                <i class="fas fa-check-circle"></i>
                <span>Caixa aberto.</span>
            </span>
            <div class="pos-field-wrap border border-gray-200 rounded-md bg-gray-50 focus-within:bg-white focus-within:border-indigo-400 transition-colors flex-1 min-w-0">
                <i class="fas fa-user text-gray-400" aria-hidden="true"></i>
                <input type="text" id="customer-search" class="text-sm border-0 bg-transparent focus:ring-0" placeholder="Nome do cliente (imprime no cupom)" title="Digite o nome do cliente para aparecer no cupom térmico">
                <input type="hidden" id="selected-customer-id">
                <div id="customer-list" class="absolute left-0 right-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-xl z-50 hidden max-h-48 overflow-y-auto text-sm"></div>
            </div>
            <div class="pos-field-wrap border-2 border-transparent rounded-md bg-gray-50 focus-within:bg-white focus-within:border-indigo-400 transition-colors flex-1 min-w-0">
                <i class="fas fa-search text-indigo-500" aria-hidden="true"></i>
                <input type="text" id="product-search" class="text-sm border-0 bg-transparent outline-none focus:ring-0" placeholder="Buscar produto... (/) " autofocus>
            </div>
        </div>
        <div id="product-list" class="flex-1 min-h-0 overflow-y-auto p-1.5">
            <div class="pos-list-placeholder text-center text-gray-400 py-6 text-xs">Digite para buscar produtos...</div>
        </div>
    </div>

    <!-- Coluna 2: Carrinho + Total + Finalizar -->
    <div class="flex flex-col min-h-0 min-w-0 overflow-hidden bg-white rounded-lg shadow-sm border border-gray-200 pos-col-cart">
        <div class="p-2 bg-gray-50 border-b border-gray-200 flex justify-between items-center flex-shrink-0">
            <h5 class="font-bold text-gray-700 text-sm flex items-center gap-1"><i class="fas fa-shopping-cart text-indigo-600"></i> Carrinho</h5>
            <span id="cart-count" class="bg-indigo-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded">0 itens</span>
        </div>
        <div class="pos-cart-table-wrap flex-1 min-h-0 overflow-y-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 text-gray-500 uppercase sticky top-0">
                    <tr><th class="px-2 py-1">Item</th><th class="px-1 py-1 text-center">Qtd</th><th class="px-2 py-1 text-right">Subtotal</th><th class="px-2 py-1 text-right">Lucro</th><th class="w-6"></th></tr>
                </thead>
                <tbody id="cart-table-body" class="divide-y divide-gray-100"></tbody>
            </table>
        </div>
        <!-- Total + Finalizar logo abaixo do carrinho -->
        <div class="p-3 border-t border-gray-200 flex-shrink-0 bg-gray-50">
            <div class="flex justify-between items-center mb-1">
                <span class="text-gray-600 text-sm font-medium">Lucro total</span>
                <span id="cart-profit" class="text-sm font-semibold text-green-600">R$ 0,00</span>
            </div>
            <div class="flex justify-between items-center mb-2">
                <span class="text-gray-600 text-sm font-medium">Total</span>
                <span class="text-xl font-bold text-gray-800">R$ <span id="cart-total">0,00</span></span>
            </div>
            <button onclick="openPaymentModal()" class="w-full py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold shadow transition flex justify-center items-center gap-1">
                <i class="fas fa-check-circle"></i> Finalizar Venda (F2)
            </button>
        </div>
    </div>
</div>

<!-- Custom Tailwind Modal -->
<div id="paymentModalContainer" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-filter backdrop-blur-sm"
        onclick="closePaymentModal()"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <!-- Modal Panel -->
            <div
                class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">

                <!-- Header -->
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 border-b border-gray-100">
                    <h3 class="text-xl font-semibold leading-6 text-gray-900" id="modal-title">
                        <i class="fas fa-wallet text-green-600 mr-2"></i> Pagamento
                    </h3>
                </div>

                <!-- Body -->
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total da Venda</label>
                            <input type="text" id="modal-total"
                                class="w-full bg-gray-100 text-gray-800 text-2xl font-bold p-3 rounded-md border-transparent focus:ring-0 text-center"
                                readonly>
                        </div>

                        <?php if (!empty($canDiscount)): ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Desconto (R$)</label>
                                <input type="number" id="discount-value"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50 p-2"
                                    placeholder="0.00" min="0" step="0.01" onkeyup="updateTotalWithDiscount()">
                            </div>
                        <?php else: ?>
                            <input type="hidden" id="discount-value" value="0">
                        <?php endif; ?>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Pagamento (F2)</label>
                            <select id="payment-method"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2"
                                onchange="toggleChangeField()">
                                <option value="Dinheiro">💵 Dinheiro</option>
                                <option value="Cartão de Crédito">💳 Cartão de Crédito</option>
                                <option value="Cartão de Débito">💳 Cartão de Débito</option>
                                <option value="PIX">💠 PIX</option>
                                <option value="A Prazo">⏳ A Prazo (Fiado)</option>
                                <option value="Vale Presente">🎁 Vale Presente</option>
                            </select>
                        </div>

                        <div id="gift-card-row" style="display: none;">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Código do Vale</label>
                            <div class="flex gap-2">
                                <input type="text" id="gift-card-code"
                                    class="w-full border-gray-300 rounded-md shadow-sm p-2"
                                    placeholder="Digite o código...">
                                <button type="button" onclick="verifyGiftCard()"
                                    class="bg-blue-500 text-white px-3 py-2 rounded shadow hover:bg-blue-600 transition">Verificar</button>
                            </div>
                            <div id="gift-card-info" class="text-xs mt-1 font-bold"></div>
                            <input type="hidden" id="gift-card-id">
                        </div>

                        <div id="change-row">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Valor Pago</label>
                            <input type="number" id="amount-paid"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 text-xl p-2"
                                placeholder="0.00" step="0.01" onkeyup="calculateChange()">

                            <label class="block text-sm font-medium text-gray-700 mt-3 mb-1">Troco</label>
                            <input type="text" id="change-value"
                                class="w-full bg-gray-100 text-green-600 font-bold text-xl p-3 rounded-md text-center"
                                readonly value="R$ 0,00">
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="button" id="process-btn" onclick="processCheckout()"
                        class="btn btn-success text-white border-none rounded-lg shadow-md font-black transition-all active:scale-95 btn-sm px-6">
                        Confirmar (Enter)
                    </button>
                    <button type="button" onclick="closePaymentModal()"
                        class="btn btn-ghost bg-white hover:bg-gray-50 text-gray-600 rounded-xl border border-gray-200 transition-all font-bold btn-sm shadow-sm">
                        Cancelar (Esc)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Open Cash Modal (Tailwind) -->
<div id="openCashModal" class="fixed inset-0 z-[60] hidden" aria-labelledby="modal-title" role="dialog"
    aria-modal="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="fixed inset-0 bg-gray-900 bg-opacity-90 transition-opacity backdrop-blur-sm"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div
                class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-2xl transition-all sm:w-full sm:max-w-md border-t-4 border-blue-600">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 mb-4">
                            <i class="fas fa-cash-register text-3xl text-blue-600"></i>
                        </div>
                        <h3 class="text-2xl font-bold leading-6 text-gray-900 mb-2" id="modal-title">Abrir Caixa</h3>
                        <p class="text-sm text-gray-500 mb-6">O caixa está fechado. Informe o valor inicial (fundo de
                            troco) para iniciar as vendas.</p>

                        <div class="text-left">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Valor de Abertura (R$)</label>
                            <input type="text" id="opening-amount"
                                class="w-full text-center text-3xl font-bold text-gray-800 border-2 border-gray-300 rounded-lg py-3 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="0,00" value="0,00">
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                    <button type="button" onclick="openRegister()"
                        class="btn btn-primary border-none rounded-lg px-6 py-3 text-sm font-black shadow-lg transition-all active:scale-95">
                        <i class="fas fa-check mr-2"></i> Abrir Caixa
                    </button>
                    <a href="?route=dashboard/index"
                        class="btn btn-ghost bg-white hover:bg-gray-50 text-gray-600 rounded-xl border border-gray-200 transition-all font-bold btn-sm shadow-sm">
                        Voltar ao Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>window.POS_CAN_DISCOUNT = <?php echo !empty($canDiscount) ? 'true' : 'false'; ?>;</script>
<script src="public/js/pos.js"></script>

<?php if (isset($editSale) && $editSale): ?>
    <script>
        // Load existing sale data into cart
        document.addEventListener('DOMContentLoaded', function () {
            const saleData = <?php echo json_encode($editSale); ?>;

            // Clear cart first
            cart = [];

            // Load items from sale
            saleData.items.forEach(item => {
                cart.push({
                    id: item.product_id,
                    name: item.product_name,
                    price: parseFloat(item.unit_price),
                    quantity: parseInt(item.quantity),
                    stock: 999,
                    cost: parseFloat(item.cost_price) || 0
                });
            });

            // Load customer if exists
            <?php if ($editSale['customer_id']): ?>
                document.getElementById('selected-customer-id').value = '<?php echo $editSale['customer_id']; ?>';
                document.getElementById('customer-search').value = '<?php echo addslashes($editSale['customer_name'] ?? ''); ?>';
                document.getElementById('customer-search').classList.add('is-valid');
            <?php endif; ?>

            // Render the cart
            renderCart();

            // Show notification
            alert('Venda #<?php echo $editSale['id']; ?> carregada para edição!');
        });
    </script>
<?php endif; ?>

<?php require 'views/layouts/footer.php'; ?>