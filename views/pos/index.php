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
/* Colunas: carrinho mais largo para ver itens completos; produtos ocupam o restante */
@media (min-width: 1024px) {
  .pos-container.grid { grid-template-columns: minmax(0, 1fr) minmax(400px, 44%) !important; gap: 10px; }
  .pos-col-cart { flex: 0 0 auto; display: flex; flex-direction: column; min-height: 0; min-width: 400px; }
  .pos-col-cart .pos-cart-table-wrap { flex: 1 1 auto; min-height: 120px; max-height: none; overflow-y: auto; }
  .pos-col-cart table { font-size: 0.8125rem; table-layout: auto; width: 100%; }
  .pos-col-cart th.pos-cart-col-item,
  .pos-col-cart td.pos-cart-col-item { min-width: 9rem; max-width: none; white-space: normal; word-break: break-word; vertical-align: middle; line-height: 1.3; }
  .pos-col-cart th.pos-cart-col-qty,
  .pos-col-cart td.pos-cart-col-qty { width: 5.5rem; white-space: nowrap; }
  .pos-col-cart th.pos-cart-col-sub,
  .pos-col-cart td.pos-cart-col-sub { width: 5.5rem; white-space: nowrap; text-align: right; }
  .pos-col-cart th.pos-cart-col-del,
  .pos-col-cart td.pos-cart-col-del { width: 2rem; }
  #product-list { grid-template-columns: repeat(4, minmax(0, 1fr)) !important; }
}
@media (min-width: 1400px) {
  .pos-container.grid { grid-template-columns: minmax(0, 1fr) minmax(440px, 46%) !important; }
  #product-list { grid-template-columns: repeat(5, minmax(0, 1fr)) !important; }
}
/* Lista de produtos (mobile/tablet) */
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
/* Abas Produtos / Bebidas / Sobremesas */
.pos-tabs .pos-tab { outline: none; }
.pos-tabs .pos-tab:focus-visible { box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.5); }
/* Abas de vendas paralelas (Caixa 1, Caixa 2, + Abrir novo) */
.pos-sale-header { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; padding: 8px 10px 0; border-bottom: 1px solid #f1f5f9; flex-shrink: 0; min-height: 44px; }
.pos-sale-tabs { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; flex: 1; min-width: 0; }
.pos-sale-tab { padding: 6px 14px; border-radius: 8px 8px 0 0; font-size: 0.8125rem; font-weight: 600; border: 1px solid var(--color-gray-200, #e2e8f0); background: #fff; color: var(--color-gray-800, #1e293b); cursor: pointer; transition: background 0.15s, color 0.15s, border-color 0.15s; }
.pos-sale-tab:hover:not(.pos-sale-tab--active) { background: var(--color-gray-50, #f8fafc); }
.pos-sale-tab--active { background: var(--color-primary, #4f46e5); color: #fff; border-color: var(--color-primary, #4f46e5); }
.pos-sale-tab-new { border-style: dashed; border-color: var(--color-primary, #4f46e5); color: var(--color-primary, #4f46e5); background: #fff; }
.pos-sale-tab-new:hover { background: var(--color-secondary, #eef2ff); }
.pos-sale-tab-wrap { display: inline-flex; align-items: stretch; border-radius: 8px 8px 0 0; overflow: hidden; border: 1px solid var(--color-gray-200, #e2e8f0); }
.pos-sale-tab-wrap.pos-sale-tab-wrap--active { border-color: var(--color-primary, #4f46e5); }
.pos-sale-tab-wrap--active .pos-sale-tab { background: var(--color-primary, #4f46e5); color: #fff; border: none; }
.pos-sale-tab-wrap .pos-sale-tab { border: none; border-radius: 0; }
.pos-sale-tab-close { display: inline-flex; align-items: center; justify-content: center; width: 1.75rem; padding: 0 4px; border: none; border-left: 1px solid rgba(0,0,0,0.08); background: #fff; color: #94a3b8; font-size: 1rem; line-height: 1; cursor: pointer; }
.pos-sale-tab-wrap--active .pos-sale-tab-close { background: var(--color-primary-hover, #4338ca); color: #fff; border-left-color: rgba(255,255,255,0.25); }
.pos-sale-tab-close:hover { background: #fee2e2; color: #dc2626; }
.pos-sale-tab-wrap--active .pos-sale-tab-close:hover { background: #b91c1c; color: #fff; }
#pos-btn-excluir-caixa { font-size: 0.75rem; }
.pos-cart-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem 1rem; color: #94a3b8; text-align: center; min-height: 120px; }
.pos-cart-empty i { font-size: 2.5rem; margin-bottom: 0.5rem; opacity: 0.5; }
.pos-cart-summary-row { display: flex; justify-content: space-between; align-items: center; font-size: 0.8125rem; margin-bottom: 0.35rem; }
.pos-cart-summary-row input[type="number"] { width: 5rem; text-align: right; font-size: 0.8125rem; padding: 0.25rem 0.5rem; border: 1px solid #e2e8f0; border-radius: 6px; }
.pos-cart-customer-label { font-size: 0.65rem; font-weight: 600; letter-spacing: 0.05em; color: #94a3b8; text-transform: uppercase; margin-bottom: 0.35rem; }
.pos-cart-field-wrap { position: relative; margin-bottom: 0.5rem; }
.pos-cart-field-wrap .fa-user, .pos-cart-field-wrap .fa-phone { position: absolute; left: 0.65rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.875rem; pointer-events: none; }
.pos-cart-field-wrap input { width: 100%; padding: 0.5rem 0.5rem 0.5rem 2rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.8125rem; }
.pos-cart-field-wrap input:focus { outline: none; border-color: var(--color-primary, #4f46e5); box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2); }
#pos-cart-customer-list { position: absolute; left: 0; right: 0; top: 100%; margin-top: 4px; z-index: 40; }
/* Cliente no topo da tela — bloco duplicado no carrinho fica oculto (campos permanecem no DOM) */
.pos-cart-customer-block { display: none !important; }
.pos-col-cart .pos-cart-table-wrap { background: #fafbfc; }
.pos-col-cart #pos-cart-table tbody tr { background: #fff; }
.pos-col-cart #pos-cart-table tbody tr + tr { border-top: 1px solid #f1f5f9; }
</style>
<!-- PDV: 3 colunas ocupam 90% da tela (90vw x 90vh) -->
<div class="pos-container grid grid-cols-1 lg:grid-cols-2 gap-4 overflow-hidden min-h-0">
    <!-- Coluna 1: Produtos (busca + cards pequenos) -->
    <div class="pos-col-products flex flex-col min-h-0 flex-1 min-w-0 overflow-hidden bg-white rounded-lg shadow-sm border border-gray-200">
        <!-- Linha 1: Caixa + Campo Cliente + Campo Busca Produto (só as caixas de texto na mesma linha) -->
        <div class="pos-search-row p-2 flex-shrink-0 flex items-center gap-2 flex-nowrap">
            <button type="button" id="pos-btn-abrir-caixa" class="hidden flex-shrink-0 items-center gap-2 rounded-lg bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 text-sm shadow transition"
                onclick="document.getElementById('openCashModal').classList.remove('hidden'); document.getElementById('opening-amount').focus();"
                title="Abrir caixa e informar valor inicial">
                <i class="fas fa-cash-register"></i>
                <span>Abrir caixa</span>
            </button>
            <span id="pos-caixa-aberto-label" class="hidden flex-shrink-0 items-center gap-2 rounded-lg bg-green-600 text-white font-bold py-2 px-4 text-sm shadow">
                <i class="fas fa-check-circle"></i>
                <span>Caixa aberto.</span>
            </span>
            <div class="pos-field-wrap border border-gray-200 rounded-md bg-gray-50 focus-within:bg-white focus-within:border-indigo-400 transition-colors flex-1 min-w-0 relative">
                <i class="fas fa-user text-gray-400" aria-hidden="true"></i>
                <input type="text" id="customer-search" class="text-sm border-0 bg-transparent focus:ring-0 w-full" placeholder="Telefone ou nome do cliente" title="Busca rápida (também no painel do carrinho)" autocomplete="off">
                <div id="customer-list" class="absolute left-0 right-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-xl z-50 hidden max-h-48 overflow-y-auto text-sm"></div>
            </div>
            <button type="button" id="btn-novo-cliente-pdv" class="flex-shrink-0 rounded-md border border-emerald-500 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 px-3 py-2 text-xs font-semibold transition" title="Cadastrar novo cliente">
                <i class="fas fa-user-plus mr-1"></i> Novo
            </button>
            <div class="pos-field-wrap border border-gray-200 rounded-md bg-gray-50 focus-within:bg-white focus-within:border-indigo-400 transition-colors flex-1 min-w-0">
                <i class="fas fa-search text-indigo-500" aria-hidden="true"></i>
                <input type="text" id="product-search" class="text-sm border-0 bg-transparent outline-none focus:ring-0 w-full" placeholder="Buscar produto... (/) " autofocus>
            </div>
        </div>
        <!-- Linha 2: Retirada + Endereço de entrega (separado das caixas de busca) -->
        <div class="px-2 pb-2 flex-shrink-0 flex items-center gap-3 flex-wrap border-b border-gray-100">
            <div id="customer-retirada-wrap" class="flex items-center gap-2 text-xs flex-shrink-0">
                <label class="inline-flex items-center gap-2 cursor-pointer select-none py-1">
                    <input type="checkbox" id="customer-retirada" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    <span class="text-gray-600"><i class="fas fa-store text-emerald-500 mr-1"></i> Retirada no local</span>
                </label>
            </div>
            <div id="customer-address-wrap" class="hidden flex-1 min-w-0 flex items-center gap-2 text-xs">
                <span class="text-gray-500 whitespace-nowrap"><i class="fas fa-map-marker-alt text-sky-500 mr-1"></i> Entrega:</span>
                <span id="customer-address-text" class="flex-1 min-w-0 truncate text-gray-700"></span>
                <button type="button" id="btn-edit-address" class="flex-shrink-0 text-sky-600 hover:text-sky-800 font-medium" title="Alterar endereço de entrega">Editar</button>
            </div>
            <div id="customer-no-address-wrap" class="hidden flex-shrink-0">
                <button type="button" id="btn-add-address" class="text-xs text-sky-600 hover:text-sky-800 font-medium flex items-center gap-1 whitespace-nowrap" title="Informar endereço de entrega para imprimir no cupom">
                    <i class="fas fa-map-marker-alt"></i> Informar endereço de entrega
                </button>
            </div>
        </div>
        <!-- Abas: Produtos, Bebidas, Sobremesas -->
        <div class="pos-tabs flex-shrink-0 flex border-b border-gray-200 bg-gray-50 px-2 gap-0" role="tablist">
            <?php
            $posTabs = $posTabs ?? [
                ['label' => 'Produtos', 'category_id' => null],
                ['label' => 'Bebidas', 'category_id' => null],
                ['label' => 'Sobremesas', 'category_id' => null],
            ];
            foreach ($posTabs as $i => $tab):
                $catId = isset($tab['category_id']) && $tab['category_id'] !== null && $tab['category_id'] !== '' ? (int) $tab['category_id'] : '';
                $active = $i === 0;
            ?>
            <button type="button" class="pos-tab px-4 py-2.5 text-sm font-semibold border-b-2 transition-colors whitespace-nowrap <?php echo $active ? 'border-primary text-primary bg-white -mb-px' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'; ?>" data-category-id="<?php echo $catId === '' ? '' : (int) $catId; ?>" role="tab" aria-selected="<?php echo $active ? 'true' : 'false'; ?>">
                <?php echo htmlspecialchars($tab['label'], ENT_QUOTES, 'UTF-8'); ?>
            </button>
            <?php endforeach; ?>
        </div>
        <div id="product-list" class="flex-1 min-h-0 overflow-y-auto p-1.5">
            <div class="pos-list-placeholder text-center text-gray-400 py-6 text-xs">Digite para buscar produtos...</div>
        </div>
    </div>

    <!-- Coluna 2: Abas de venda + Carrinho + Cliente + Totais -->
    <div class="flex flex-col min-h-0 min-w-0 overflow-hidden bg-white rounded-lg shadow-sm border border-gray-200 pos-col-cart">
        <div class="pos-sale-header">
            <div id="pos-sale-tabs" class="pos-sale-tabs" role="tablist" aria-label="Vendas em andamento">
                <button type="button" class="pos-sale-tab pos-sale-tab--active" data-session-index="0" role="tab" aria-selected="true">Caixa 1</button>
            </div>
            <button type="button" id="pos-btn-nova-venda" class="pos-sale-tab pos-sale-tab-new flex-shrink-0 whitespace-nowrap" title="Abrir outra venda em paralelo (Caixa 2, Caixa 3…)">
                + Abrir novo
            </button>
        </div>
        <div class="px-3 py-2 border-b border-gray-100 flex-shrink-0 flex items-center justify-between gap-2">
            <h5 id="pos-sale-title" class="font-bold text-gray-800 text-base flex items-center gap-2 m-0 min-w-0">
                <i class="fas fa-shopping-cart text-primary"></i> <span>Caixa 1</span>
            </h5>
            <div class="flex items-center gap-2 flex-shrink-0">
                <button type="button" id="pos-btn-excluir-caixa" class="hidden btn btn-ghost text-red-600 hover:bg-red-50 border border-red-200 rounded-lg px-2 py-1 font-semibold" title="Descartar esta venda em aberto">
                    <i class="fas fa-trash-alt mr-1"></i> Excluir caixa
                </button>
                <span id="cart-count" class="bg-primary text-white text-[10px] font-bold px-1.5 py-0.5 rounded hidden">0 itens</span>
            </div>
        </div>
        <div class="px-3 pb-2 flex-shrink-0">
            <select id="pos-sale-channel" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-medium text-gray-800 bg-white focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-30" title="Canal da venda">
                <option value="balcao">Balcão</option>
                <option value="entrega">Entrega</option>
                <option value="retirada">Retirada</option>
            </select>
        </div>
        <div class="pos-cart-customer-block px-3 pb-2 flex-shrink-0 border-b border-gray-100" aria-hidden="true">
            <p class="pos-cart-customer-label">Cliente (opcional)</p>
            <div class="pos-cart-field-wrap">
                <i class="fas fa-user" aria-hidden="true"></i>
                <input type="text" id="pos-cart-customer-name" placeholder="Buscar ou digitar nome..." autocomplete="off">
                <input type="hidden" id="selected-customer-id">
                <div id="pos-cart-customer-list" class="bg-white border border-gray-200 rounded-lg shadow-xl hidden max-h-40 overflow-y-auto text-sm"></div>
            </div>
            <div class="pos-cart-field-wrap mb-0">
                <i class="fas fa-phone" aria-hidden="true"></i>
                <input type="text" id="pos-cart-customer-phone" placeholder="Telefone (opcional)" autocomplete="tel">
            </div>
            <button type="button" id="btn-novo-cliente-cart" class="mt-1 text-xs text-emerald-600 hover:text-emerald-800 font-semibold">
                <i class="fas fa-user-plus mr-1"></i> Novo cliente
            </button>
        </div>
        <div class="pos-cart-table-wrap flex-1 min-h-0 overflow-y-auto relative">
            <div id="pos-cart-empty" class="pos-cart-empty">
                <i class="fas fa-shopping-cart"></i>
                <p class="font-medium text-gray-500">Carrinho vazio</p>
                <p class="text-xs">Clique nos produtos para adicionar</p>
            </div>
            <table class="w-full text-left hidden" id="pos-cart-table">
                <thead class="bg-gray-50 text-gray-500 uppercase sticky top-0 text-[11px]">
                    <tr>
                        <th class="pos-cart-col-item px-3 py-2">Produto</th>
                        <th class="pos-cart-col-qty px-2 py-2 text-center">Qtd</th>
                        <th class="pos-cart-col-sub px-3 py-2 text-right">Subtotal</th>
                        <th class="pos-cart-col-del px-1 py-2"></th>
                    </tr>
                </thead>
                <tbody id="cart-table-body" class="divide-y divide-gray-100"></tbody>
            </table>
        </div>
        <div class="px-2 py-1.5 border-t border-gray-100 flex-shrink-0">
            <label for="order-observation" class="block text-xs font-medium text-gray-500 mb-0.5"><i class="fas fa-sticky-note text-amber-500 mr-1"></i> Observação</label>
            <input type="text" id="order-observation" class="w-full text-xs border border-gray-200 rounded-md px-2 py-1.5 focus:ring-1 focus:ring-primary focus:border-primary" placeholder="Ex: sem farofa, ponto da carne..." maxlength="500">
        </div>
        <div class="p-3 border-t border-gray-200 flex-shrink-0 bg-gray-50 space-y-1">
            <div class="pos-cart-summary-row">
                <span class="text-gray-600">Subtotal</span>
                <span id="cart-subtotal" class="font-semibold text-gray-800">R$ 0,00</span>
            </div>
            <?php if (!empty($canDiscount)): ?>
            <div class="pos-cart-summary-row">
                <span class="text-gray-600">Desconto</span>
                <input type="number" id="cart-discount" value="0" min="0" step="0.01" placeholder="0,00" title="Desconto em reais">
            </div>
            <?php else: ?>
            <input type="hidden" id="cart-discount" value="0">
            <?php endif; ?>
            <div class="pos-cart-summary-row">
                <span class="text-gray-600">Acréscimo</span>
                <input type="number" id="cart-surcharge" value="0" min="0" step="0.01" placeholder="0,00" title="Acréscimo em reais">
            </div>
            <div class="pos-cart-summary-row">
                <label class="inline-flex items-center gap-2 cursor-pointer text-gray-600">
                    <input type="checkbox" id="cart-tax-enabled" class="rounded border-gray-300 text-primary focus:ring-primary" checked>
                    <span>Taxa (10%)</span>
                </label>
                <span id="cart-tax-amount" class="font-semibold text-gray-800">R$ 0,00</span>
            </div>
            <div class="flex justify-between items-center pt-2 border-t border-gray-200 mt-2">
                <span class="text-gray-700 font-bold">Total</span>
                <span class="text-xl font-bold text-gray-900">R$ <span id="cart-total">0,00</span></span>
            </div>
            <span id="cart-profit" class="hidden" aria-hidden="true">R$ 0,00</span>
            <button type="button" onclick="openPaymentModal()" class="btn btn-primary w-full mt-2 py-2.5 text-sm font-bold shadow">
                <i class="fas fa-check-circle mr-1"></i> Finalizar Venda (F2)
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

<!-- Modal Endereço de Entrega (CEP → ViaCEP → número e complemento) -->
<div id="addressModal" class="fixed inset-0 z-[60] hidden" aria-labelledby="address-modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm" onclick="closeAddressModal()"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full border-t-4 border-sky-600 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4" id="address-modal-title">
                    <i class="fas fa-map-marker-alt text-sky-600 mr-2"></i> Endereço de entrega
                </h3>
                <p class="text-sm text-gray-500 mb-4">Preencha o CEP para buscar. Depois informe número e complemento.</p>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                        <input type="text" id="addr-cep" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" placeholder="00000-000" maxlength="9" autocomplete="postal-code">
                        <p id="addr-cep-msg" class="text-xs mt-1 min-h-[1rem]"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Logradouro</label>
                        <input type="text" id="addr-street" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm bg-gray-50" readonly>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                            <input type="text" id="addr-number" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" placeholder="123">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
                            <input type="text" id="addr-complement" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" placeholder="Apto, sala...">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>
                        <input type="text" id="addr-neighborhood" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm bg-gray-50" readonly>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                            <input type="text" id="addr-city" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm bg-gray-50" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">UF</label>
                            <input type="text" id="addr-state" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm bg-gray-50 text-center" readonly maxlength="2">
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" onclick="closeAddressModal()" class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg">Cancelar</button>
                    <button type="button" id="btn-save-address" class="px-4 py-2 text-sm font-bold text-white bg-sky-600 hover:bg-sky-700 rounded-lg shadow transition">
                        <i class="fas fa-save mr-1"></i> Salvar endereço
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo Cliente (cadastro rápido no PDV com endereço) -->
<div id="newCustomerModal" class="fixed inset-0 z-[60] hidden" aria-labelledby="new-customer-modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm" onclick="closeNewCustomerModal()"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full border-t-4 border-emerald-600 p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-bold text-gray-900 mb-4" id="new-customer-modal-title">
                    <i class="fas fa-user-plus text-emerald-600 mr-2"></i> Novo cliente
                </h3>
                <p class="text-sm text-gray-500 mb-4">Cadastre o cliente e o endereço de entrega para imprimir no cupom.</p>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome <span class="text-red-500">*</span></label>
                        <input type="text" id="new-customer-name" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" placeholder="Nome completo" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telefone <span class="text-red-500">*</span></label>
                        <input type="text" id="new-customer-phone" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" placeholder="(11) 99999-9999" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">E-mail (opcional)</label>
                        <input type="email" id="new-customer-email" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" placeholder="email@exemplo.com">
                    </div>
                    <div class="border-t border-gray-200 pt-3 mt-3">
                        <p class="text-xs font-semibold text-gray-600 mb-2">Endereço de entrega (opcional)</p>
                        <div class="space-y-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-0.5">CEP</label>
                                <input type="text" id="new-addr-cep" class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-sm" placeholder="00000-000" maxlength="9">
                                <p id="new-addr-cep-msg" class="text-xs mt-0.5 min-h-[1rem] text-red-600"></p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-0.5">Logradouro</label>
                                <input type="text" id="new-addr-street" class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-sm bg-gray-50" readonly>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-0.5">Número</label>
                                    <input type="text" id="new-addr-number" class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-sm" placeholder="123">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-0.5">Complemento</label>
                                    <input type="text" id="new-addr-complement" class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-sm" placeholder="Apto">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-0.5">Bairro</label>
                                <input type="text" id="new-addr-neighborhood" class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-sm bg-gray-50" readonly>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <div class="col-span-2">
                                    <label class="block text-xs font-medium text-gray-600 mb-0.5">Cidade</label>
                                    <input type="text" id="new-addr-city" class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-sm bg-gray-50" readonly>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-0.5">UF</label>
                                    <input type="text" id="new-addr-state" class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-sm bg-gray-50 text-center" readonly maxlength="2">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" onclick="closeNewCustomerModal()" class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg">Cancelar</button>
                    <button type="button" id="btn-save-new-customer" class="px-4 py-2 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow transition">
                        <i class="fas fa-check mr-1"></i> Cadastrar e usar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.POS_CAN_DISCOUNT = <?php echo !empty($canDiscount) ? 'true' : 'false'; ?>;
window.POS_BASE_URL = <?php echo json_encode(defined('BASE_URL') ? rtrim(BASE_URL, '/') : ''); ?>;
</script>
<script src="<?php echo htmlspecialchars(BASE_URL ?? '', ENT_QUOTES, 'UTF-8'); ?>public/js/pos.js?v=<?php echo (int) @filemtime(__DIR__ . '/../../public/js/pos.js'); ?>"></script>

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

            if (typeof posSessions !== 'undefined' && posSessions[0]) {
                posSessions[0].cart = cart.slice();
            }
            renderCart();
            if (typeof persistPosSessions === 'function') persistPosSessions();

            alert('Venda #<?php echo $editSale['id']; ?> carregada para edição!');
        });
    </script>
<?php endif; ?>

<?php require 'views/layouts/footer.php'; ?>