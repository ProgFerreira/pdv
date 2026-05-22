let cart = [];
let searchTimeout;
let customerTimeout;
let cartCustomerTimeout;
/** Lista da última busca (para painel de detalhe) */
let posLastList = [];
/** Produto atualmente selecionado no painel */
let posSelectedProduct = null;
/** Cliente selecionado (objeto completo) para exibir endereço */
let posSelectedCustomer = null;
/** Endereço de entrega atual (linha para cupom) */
let posDeliveryAddress = '';
/** Cliente retira no local (não precisa de endereço) */
let posIsPickup = false;

/** Vendas paralelas no PDV (abas Caixa 1, Caixa 2, …) */
let posSessions = [];
let activeSessionIndex = 0;
const POS_SESSIONS_STORAGE_KEY = 'pdv_pos_sessions_v1';
const POS_MAX_SESSIONS = 12;
const POS_TAX_PERCENT = 10;

function createEmptyPosSession(labelNum) {
  return {
    id: 's' + Date.now() + '_' + Math.random().toString(36).slice(2, 8),
    label: 'Caixa ' + labelNum,
    cart: [],
    customerId: null,
    customerName: '',
    customerPhone: '',
    selectedCustomer: null,
    deliveryAddress: '',
    isPickup: false,
    discount: 0,
    surcharge: 0,
    taxEnabled: true,
    channel: 'balcao',
    observation: '',
  };
}

function getActiveSession() {
  return posSessions[activeSessionIndex] || posSessions[0];
}

function saveSessionFromUI() {
  const s = getActiveSession();
  if (!s) return;
  s.cart = cart.slice();
  const nameEl = document.getElementById('pos-cart-customer-name');
  const phoneEl = document.getElementById('pos-cart-customer-phone');
  const idEl = document.getElementById('selected-customer-id');
  const channelEl = document.getElementById('pos-sale-channel');
  const obsEl = document.getElementById('order-observation');
  const discEl = document.getElementById('cart-discount');
  const surEl = document.getElementById('cart-surcharge');
  const taxEl = document.getElementById('cart-tax-enabled');
  s.customerName = nameEl ? nameEl.value.trim() : '';
  s.customerPhone = phoneEl ? phoneEl.value.trim() : '';
  s.customerId = idEl && idEl.value ? parseInt(idEl.value, 10) : null;
  s.selectedCustomer = posSelectedCustomer;
  s.deliveryAddress = posDeliveryAddress;
  s.isPickup = posIsPickup;
  s.channel = channelEl ? channelEl.value : 'balcao';
  s.observation = obsEl ? obsEl.value.trim() : '';
  s.discount = discEl ? parseFloat(discEl.value) || 0 : 0;
  s.surcharge = surEl ? parseFloat(surEl.value) || 0 : 0;
  s.taxEnabled = taxEl ? taxEl.checked : false;
}

function loadSessionToUI(index) {
  const s = posSessions[index];
  if (!s) return;
  cart = s.cart.slice();
  posSelectedCustomer = s.selectedCustomer || null;
  posDeliveryAddress = s.deliveryAddress || '';
  posIsPickup = !!s.isPickup;
  const nameEl = document.getElementById('pos-cart-customer-name');
  const phoneEl = document.getElementById('pos-cart-customer-phone');
  const idEl = document.getElementById('selected-customer-id');
  const channelEl = document.getElementById('pos-sale-channel');
  const obsEl = document.getElementById('order-observation');
  const discEl = document.getElementById('cart-discount');
  const surEl = document.getElementById('cart-surcharge');
  const taxEl = document.getElementById('cart-tax-enabled');
  const topSearch = document.getElementById('customer-search');
  if (nameEl) nameEl.value = s.customerName || '';
  if (phoneEl) phoneEl.value = s.customerPhone || '';
  if (idEl) idEl.value = s.customerId ? String(s.customerId) : '';
  if (topSearch) topSearch.value = s.customerName || '';
  if (channelEl) channelEl.value = s.channel || 'balcao';
  if (obsEl) obsEl.value = s.observation || '';
  if (discEl) discEl.value = (s.discount || 0).toFixed(2);
  if (surEl) surEl.value = (s.surcharge || 0).toFixed(2);
  if (taxEl) taxEl.checked = s.taxEnabled !== false;
  const retiradaChk = document.getElementById('customer-retirada');
  if (retiradaChk) retiradaChk.checked = posIsPickup;
  if (channelEl) {
    posIsPickup = channelEl.value === 'retirada';
    if (retiradaChk) retiradaChk.checked = posIsPickup;
  }
  refreshCustomerAddressUI();
  renderSaleTabs();
  renderCart();
}

function persistPosSessions() {
  try {
    const payload = {
      activeIndex: activeSessionIndex,
      sessions: posSessions.map(function (s) {
        return {
          id: s.id,
          label: s.label,
          cart: s.cart,
          customerId: s.customerId,
          customerName: s.customerName,
          customerPhone: s.customerPhone,
          deliveryAddress: s.deliveryAddress,
          isPickup: s.isPickup,
          discount: s.discount,
          surcharge: s.surcharge,
          taxEnabled: s.taxEnabled,
          channel: s.channel,
          observation: s.observation,
        };
      }),
    };
    sessionStorage.setItem(POS_SESSIONS_STORAGE_KEY, JSON.stringify(payload));
  } catch (e) { /* ignore */ }
}

function loadPosSessionsFromStorage() {
  try {
    const raw = sessionStorage.getItem(POS_SESSIONS_STORAGE_KEY);
    if (!raw) return false;
    const data = JSON.parse(raw);
    if (!data.sessions || !data.sessions.length) return false;
    posSessions = data.sessions.map(function (s, i) {
      const sess = createEmptyPosSession(i + 1);
      sess.id = s.id || sess.id;
      sess.label = s.label || sess.label;
      sess.cart = Array.isArray(s.cart) ? s.cart : [];
      sess.customerId = s.customerId || null;
      sess.customerName = s.customerName || '';
      sess.customerPhone = s.customerPhone || '';
      sess.deliveryAddress = s.deliveryAddress || '';
      sess.isPickup = !!s.isPickup;
      sess.discount = parseFloat(s.discount) || 0;
      sess.surcharge = parseFloat(s.surcharge) || 0;
      sess.taxEnabled = s.taxEnabled !== false;
      sess.channel = s.channel || 'balcao';
      sess.observation = s.observation || '';
      return sess;
    });
    activeSessionIndex = Math.min(
      Math.max(0, parseInt(data.activeIndex, 10) || 0),
      posSessions.length - 1,
    );
    return true;
  } catch (e) {
    return false;
  }
}

function initPosSessions() {
  try {
    if (!loadPosSessionsFromStorage()) {
      posSessions = [createEmptyPosSession(1)];
      activeSessionIndex = 0;
    }
    if (!posSessions.length) {
      posSessions = [createEmptyPosSession(1)];
      activeSessionIndex = 0;
    }
    loadSessionToUI(activeSessionIndex);
  } catch (e) {
    console.error('initPosSessions', e);
    posSessions = [createEmptyPosSession(1)];
    activeSessionIndex = 0;
    cart = posSessions[0].cart;
    renderSaleTabs();
  }
}

function sessionHasContent(s) {
  if (!s) return false;
  return (
    (s.cart && s.cart.length > 0) ||
    !!(s.customerName && String(s.customerName).trim()) ||
    !!(s.customerPhone && String(s.customerPhone).trim()) ||
    !!(s.observation && String(s.observation).trim()) ||
    (parseFloat(s.discount) || 0) > 0 ||
    (parseFloat(s.surcharge) || 0) > 0
  );
}

function renumberPosSessionLabels() {
  posSessions.forEach(function (s, i) {
    s.label = 'Caixa ' + (i + 1);
  });
}

function updateExcluirCaixaButton() {
  const btn = document.getElementById('pos-btn-excluir-caixa');
  if (!btn) return;
  const multi = posSessions.length > 1;
  const hasContent = sessionHasContent(getActiveSession());
  if (multi) {
    btn.classList.remove('hidden');
    btn.innerHTML =
      '<i class="fas fa-trash-alt mr-1"></i> Excluir ' +
      escapeHtml(getActiveSession().label || 'caixa');
    btn.title = 'Descartar esta venda em aberto (' + (getActiveSession().label || '') + ')';
  } else if (hasContent) {
    btn.classList.remove('hidden');
    btn.innerHTML = '<i class="fas fa-trash-alt mr-1"></i> Limpar caixa';
    btn.title = 'Esvaziar carrinho e dados desta venda';
  } else {
    btn.classList.add('hidden');
  }
}

function removePosSession(index) {
  if (index === undefined || index === null) {
    index = activeSessionIndex;
  }
  index = parseInt(index, 10);
  if (isNaN(index) || index < 0 || index >= posSessions.length) return;

  saveSessionFromUI();
  const s = posSessions[index];
  const label = s.label || 'Caixa ' + (index + 1);
  const hasContent = sessionHasContent(s);

  if (hasContent) {
    const msg =
      posSessions.length > 1
        ? 'Excluir "' + label + '"? Os itens e dados desta venda serão descartados.'
        : 'Limpar "' + label + '"? O carrinho e os dados desta venda serão apagados.';
    if (!confirm(msg)) return;
  }

  if (posSessions.length > 1) {
    posSessions.splice(index, 1);
    if (activeSessionIndex >= posSessions.length) {
      activeSessionIndex = posSessions.length - 1;
    } else if (index < activeSessionIndex) {
      activeSessionIndex -= 1;
    }
    renumberPosSessionLabels();
  } else {
    posSessions[0] = createEmptyPosSession(1);
    activeSessionIndex = 0;
  }

  loadSessionToUI(activeSessionIndex);
  persistPosSessions();
}

function renderSaleTabs() {
  const wrap = document.getElementById('pos-sale-tabs');
  if (!wrap) return;
  wrap.innerHTML = '';
  const showClose = posSessions.length > 1;
  posSessions.forEach(function (s, i) {
    const tabWrap = document.createElement('div');
    tabWrap.className =
      'pos-sale-tab-wrap' +
      (i === activeSessionIndex ? ' pos-sale-tab-wrap--active' : '');

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'pos-sale-tab';
    btn.textContent = s.label;
    btn.setAttribute('role', 'tab');
    btn.setAttribute('aria-selected', i === activeSessionIndex ? 'true' : 'false');
    btn.addEventListener('click', function (e) {
      if (e.target.closest('.pos-sale-tab-close')) return;
      if (i === activeSessionIndex) return;
      saveSessionFromUI();
      activeSessionIndex = i;
      loadSessionToUI(i);
      persistPosSessions();
    });
    tabWrap.appendChild(btn);

    if (showClose) {
      const closeBtn = document.createElement('button');
      closeBtn.type = 'button';
      closeBtn.className = 'pos-sale-tab-close';
      closeBtn.setAttribute('aria-label', 'Excluir ' + s.label);
      closeBtn.innerHTML = '&times;';
      closeBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        removePosSession(i);
      });
      tabWrap.appendChild(closeBtn);
    }

    wrap.appendChild(tabWrap);
  });
  const addBtn = document.getElementById('pos-btn-nova-venda');
  if (addBtn) {
    addBtn.disabled = posSessions.length >= POS_MAX_SESSIONS;
    addBtn.title =
      posSessions.length >= POS_MAX_SESSIONS
        ? 'Limite de ' + POS_MAX_SESSIONS + ' vendas em aberto'
        : 'Abrir outra venda em paralelo (Caixa 2, 3…)';
    addBtn.style.opacity = addBtn.disabled ? '0.5' : '1';
  }
  const titleSpan = document.querySelector('#pos-sale-title span');
  if (titleSpan && getActiveSession()) {
    titleSpan.textContent = getActiveSession().label;
  }
  updateExcluirCaixaButton();
}

function addPosSession() {
  if (posSessions.length >= POS_MAX_SESSIONS) {
    alert('Limite de ' + POS_MAX_SESSIONS + ' vendas em aberto.');
    return;
  }
  saveSessionFromUI();
  const num = posSessions.length + 1;
  posSessions.push(createEmptyPosSession(num));
  activeSessionIndex = posSessions.length - 1;
  loadSessionToUI(activeSessionIndex);
  persistPosSessions();
}

function getCartTotals() {
  let subtotal = 0;
  let totalProfit = 0;
  let count = 0;
  cart.forEach(function (item) {
    const line = item.price * item.quantity;
    subtotal += line;
    count += item.quantity;
    const cost = parseFloat(item.cost) || 0;
    totalProfit += (item.price - cost) * item.quantity;
  });
  const discEl = document.getElementById('cart-discount');
  const surEl = document.getElementById('cart-surcharge');
  const taxChk = document.getElementById('cart-tax-enabled');
  const discount = window.POS_CAN_DISCOUNT && discEl
    ? parseFloat(discEl.value) || 0
    : 0;
  const surcharge = surEl ? parseFloat(surEl.value) || 0 : 0;
  const taxEnabled = taxChk ? taxChk.checked : false;
  const baseForTax = Math.max(0, subtotal - discount + surcharge);
  const taxAmount = taxEnabled ? baseForTax * (POS_TAX_PERCENT / 100) : 0;
  let total = baseForTax + taxAmount;
  if (total < 0) total = 0;
  return { subtotal, discount, surcharge, taxAmount, total, totalProfit, count, taxEnabled };
}

function posUrl(route) {
  var base = window.POS_BASE_URL || "";
  return (base ? base + "/" : "") + "index.php?route=" + route;
}

function escapeHtml(s) {
  if (s == null || s === "") return "";
  const div = document.createElement("div");
  div.textContent = String(s);
  return div.innerHTML;
}

// Load all products on init
document.addEventListener("DOMContentLoaded", () => {
  initPosSessions();
  searchProducts("");
  document.getElementById("product-search").focus();
  checkCashStatus();
  setupCartPanelListeners();

  // Botão "Adicionar" do painel de produto selecionado
  const addBtn = document.getElementById("product-detail-add-btn");
  const qtyInput = document.getElementById("product-detail-qty");
  if (addBtn && qtyInput) {
    const addFromPanel = () => {
      if (!posSelectedProduct) return;
      const p = posSelectedProduct;
      const qty = parseInt(qtyInput.value, 10) || 1;
      addToCart(p.id, p.name, p.price, p.stock || 999, p.is_gift_card || 0, p.cost_price || 0, qty);
      qtyInput.value = 1;
    };
    addBtn.addEventListener("click", addFromPanel);
    qtyInput.addEventListener("keydown", (e) => { if (e.key === "Enter") { e.preventDefault(); addFromPanel(); } });
  }
});

// Customer Search
document
  .getElementById("customer-search")
  .addEventListener("input", function (e) {
    const term = this.value;
    const list = document.getElementById("customer-list");

    if (term.length < 2) {
      list.style.display = "none";
      return;
    }

    clearTimeout(customerTimeout);
    customerTimeout = setTimeout(() => {
      fetch(posUrl("customer/search") + "&term=" + encodeURIComponent(term))
        .then((r) => r.json())
        .then((data) => {
          list.innerHTML = "";
          if (data.length > 0) {
            list.style.display = "block";
            data.forEach((c) => {
              const item = document.createElement("a");
              item.className =
                "list-group-item list-group-item-action cursor-pointer block px-3 py-2 hover:bg-gray-100 border-b border-gray-100 last:border-0";
              item.innerHTML = `<strong>${escapeHtml(c.name)}</strong> <small class="text-gray-500">| ${escapeHtml(c.phone || "")}</small>`;
              item.onclick = () => selectCustomer(c);
              list.appendChild(item);
            });
          } else {
            list.style.display = "block";
            const addItem = document.createElement("a");
            addItem.className = "block px-3 py-2 hover:bg-emerald-50 border-b border-gray-100 text-emerald-700 font-medium";
            addItem.innerHTML = "<i class=\"fas fa-plus-circle mr-2\"></i> Cadastrar novo cliente";
            addItem.onclick = () => openNewCustomerModal(term);
            list.appendChild(addItem);
          }
        });
    }, 300);
  });

function buildDeliveryLine(c) {
  if (!c) return "";
  const parts = [
    (c.address_street || "").trim(),
    (c.address_number || "").trim(),
    (c.address_complement || "").trim(),
    (c.address_neighborhood || "").trim(),
    (c.address_city || "").trim(),
    (c.address_state || "").trim(),
  ].filter(Boolean);
  if (parts.length === 0) return (c.address || "").trim();
  let line = parts.join(", ");
  const cep = (c.cep || "").trim();
  if (cep) line += " - CEP: " + cep;
  return line;
}

function refreshCustomerAddressUI() {
  const wrap = document.getElementById("customer-address-wrap");
  const noWrap = document.getElementById("customer-no-address-wrap");
  const textEl = document.getElementById("customer-address-text");
  if (!wrap || !noWrap || !textEl) return;
  if (posIsPickup) {
    wrap.classList.add("hidden");
    noWrap.classList.add("hidden");
    return;
  }
  const line = posDeliveryAddress || (posSelectedCustomer ? buildDeliveryLine(posSelectedCustomer) : "");
  if (line) {
    wrap.classList.remove("hidden");
    noWrap.classList.add("hidden");
    textEl.textContent = line;
  } else if (posSelectedCustomer) {
    wrap.classList.add("hidden");
    noWrap.classList.remove("hidden");
  } else {
    wrap.classList.add("hidden");
    noWrap.classList.add("hidden");
  }
}

function selectCustomer(c) {
  const id = typeof c === "object" ? c.id : c;
  const name = typeof c === "object" ? c.name : (arguments[1] || "");
  const phone = typeof c === "object" ? (c.phone || "") : "";
  const idEl = document.getElementById("selected-customer-id");
  if (idEl) idEl.value = id;
  const topSearch = document.getElementById("customer-search");
  const cartName = document.getElementById("pos-cart-customer-name");
  const cartPhone = document.getElementById("pos-cart-customer-phone");
  if (topSearch) {
    topSearch.value = name;
    topSearch.classList.add("is-valid");
  }
  if (cartName) cartName.value = name;
  if (cartPhone) cartPhone.value = phone;
  const list = document.getElementById("customer-list");
  const cartList = document.getElementById("pos-cart-customer-list");
  if (list) list.style.display = "none";
  if (cartList) cartList.classList.add("hidden");
  posSelectedCustomer = typeof c === "object" ? c : null;
  posDeliveryAddress = posSelectedCustomer ? buildDeliveryLine(posSelectedCustomer) : "";
  refreshCustomerAddressUI();
  saveSessionFromUI();
  persistPosSessions();
}

// Endereço de entrega: abrir modal para informar/editar
function openAddressModal() {
  if (!posSelectedCustomer) return;
  const modal = document.getElementById("addressModal");
  if (!modal) return;
  const c = posSelectedCustomer;
  document.getElementById("addr-cep").value = (c.cep || "").trim();
  document.getElementById("addr-street").value = (c.address_street || "").trim();
  document.getElementById("addr-number").value = (c.address_number || "").trim();
  document.getElementById("addr-complement").value = (c.address_complement || "").trim();
  document.getElementById("addr-neighborhood").value = (c.address_neighborhood || "").trim();
  document.getElementById("addr-city").value = (c.address_city || "").trim();
  document.getElementById("addr-state").value = (c.address_state || "").trim();
  document.getElementById("addr-cep-msg").textContent = "";
  modal.classList.remove("hidden");
  setTimeout(function () {
    document.getElementById("addr-cep").focus();
  }, 100);
}

function closeAddressModal() {
  const modal = document.getElementById("addressModal");
  if (modal) modal.classList.add("hidden");
}

function fetchViaCep(cep, callback) {
  cep = (cep || "").replace(/\D/g, "");
  if (cep.length !== 8) {
    if (typeof callback === "function") callback(null, "CEP deve ter 8 dígitos");
    return;
  }
  const msgEl = document.getElementById("addr-cep-msg");
  if (msgEl) msgEl.textContent = "Buscando...";
  fetch("https://viacep.com.br/ws/" + cep + "/json/")
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (data.erro) {
        if (msgEl) msgEl.textContent = "CEP não encontrado";
        if (typeof callback === "function") callback(null, "CEP não encontrado");
        return;
      }
      if (msgEl) msgEl.textContent = "";
      if (typeof callback === "function") callback(data);
    })
    .catch(function () {
      if (msgEl) msgEl.textContent = "Erro ao buscar CEP";
      if (typeof callback === "function") callback(null, "Erro ao buscar CEP");
    });
}

function saveAddressFromModal() {
  if (!posSelectedCustomer) return;
  const customerId = posSelectedCustomer.id;
  const cep = (document.getElementById("addr-cep").value || "").replace(/\D/g, "");
  const addressStreet = (document.getElementById("addr-street").value || "").trim();
  const addressNumber = (document.getElementById("addr-number").value || "").trim();
  const addressComplement = (document.getElementById("addr-complement").value || "").trim();
  const addressNeighborhood = (document.getElementById("addr-neighborhood").value || "").trim();
  const addressCity = (document.getElementById("addr-city").value || "").trim();
  const addressState = (document.getElementById("addr-state").value || "").trim();
  if (!addressStreet && !cep) {
    alert("Informe o CEP para buscar o endereço.");
    return;
  }
  const btn = document.getElementById("btn-save-address");
  if (btn) btn.disabled = true;
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const csrfToken = csrfMeta ? csrfMeta.getAttribute("content") : "";
  fetch(posUrl("customer/updateAddress"), {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      csrf_token: csrfToken,
      customer_id: customerId,
      cep: cep || null,
      address_street: addressStreet || null,
      address_number: addressNumber || null,
      address_complement: addressComplement || null,
      address_neighborhood: addressNeighborhood || null,
      address_city: addressCity || null,
      address_state: addressState || null,
    }),
  })
    .then(function (r) { return r.json(); })
    .then(function (res) {
      if (res.success && res.delivery_address !== undefined) {
        posDeliveryAddress = res.delivery_address;
        if (posSelectedCustomer) {
          posSelectedCustomer.address_street = addressStreet || null;
          posSelectedCustomer.address_number = addressNumber || null;
          posSelectedCustomer.address_complement = addressComplement || null;
          posSelectedCustomer.address_neighborhood = addressNeighborhood || null;
          posSelectedCustomer.address_city = addressCity || null;
          posSelectedCustomer.address_state = addressState || null;
          posSelectedCustomer.cep = cep || null;
        }
        refreshCustomerAddressUI();
        closeAddressModal();
      } else {
        alert(res.message || "Erro ao salvar endereço.");
      }
    })
    .catch(function () {
      alert("Erro de conexão ao salvar endereço.");
    })
    .finally(function () {
      if (btn) btn.disabled = false;
    });
}

(function setupRetiradaCheckbox() {
  const chk = document.getElementById("customer-retirada");
  if (chk) {
    chk.addEventListener("change", function () {
      posIsPickup = this.checked;
      refreshCustomerAddressUI();
    });
  }
})();

(function setupAddressModal() {
  const btnAdd = document.getElementById("btn-add-address");
  const btnEdit = document.getElementById("btn-edit-address");
  const btnSave = document.getElementById("btn-save-address");
  const addrCep = document.getElementById("addr-cep");
  if (btnAdd) btnAdd.addEventListener("click", openAddressModal);
  if (btnEdit) btnEdit.addEventListener("click", openAddressModal);
  if (btnSave) btnSave.addEventListener("click", saveAddressFromModal);
  if (addrCep) {
    addrCep.addEventListener("blur", function () {
      const cep = this.value.replace(/\D/g, "");
      if (cep.length === 8) fetchViaCep(cep, function (data) {
        if (!data) return;
        document.getElementById("addr-street").value = data.logradouro || "";
        document.getElementById("addr-neighborhood").value = data.bairro || "";
        document.getElementById("addr-city").value = data.localidade || "";
        document.getElementById("addr-state").value = data.uf || "";
      });
    });
    addrCep.addEventListener("input", function () {
      let v = this.value.replace(/\D/g, "");
      if (v.length > 8) v = v.slice(0, 8);
      if (v.length > 5) this.value = v.slice(0, 5) + "-" + v.slice(5);
      else this.value = v;
    });
  }
})();

function openNewCustomerModal(term) {
  const modal = document.getElementById("newCustomerModal");
  const list = document.getElementById("customer-list");
  if (modal) modal.classList.remove("hidden");
  if (list) list.style.display = "none";
  document.getElementById("new-customer-name").value = "";
  document.getElementById("new-customer-phone").value = "";
  document.getElementById("new-customer-email").value = "";
  document.getElementById("new-addr-cep").value = "";
  document.getElementById("new-addr-street").value = "";
  document.getElementById("new-addr-number").value = "";
  document.getElementById("new-addr-complement").value = "";
  document.getElementById("new-addr-neighborhood").value = "";
  document.getElementById("new-addr-city").value = "";
  document.getElementById("new-addr-state").value = "";
  document.getElementById("new-addr-cep-msg").textContent = "";
  term = (term || "").trim();
  var digitsOnly = term.replace(/\D/g, "");
  if (digitsOnly.length >= 8 && digitsOnly.length <= 11) {
    document.getElementById("new-customer-phone").value = term;
    document.getElementById("new-customer-name").focus();
  } else if (term.length >= 2) {
    document.getElementById("new-customer-name").value = term;
    document.getElementById("new-customer-phone").focus();
  } else {
    document.getElementById("new-customer-name").focus();
  }
}

function closeNewCustomerModal() {
  const modal = document.getElementById("newCustomerModal");
  if (modal) modal.classList.add("hidden");
}

function saveNewCustomer() {
  var name = (document.getElementById("new-customer-name").value || "").trim();
  var phone = (document.getElementById("new-customer-phone").value || "").trim();
  if (!name) {
    alert("Informe o nome do cliente.");
    return;
  }
  if (!phone) {
    alert("Informe o telefone do cliente.");
    return;
  }
  var cep = (document.getElementById("new-addr-cep").value || "").replace(/\D/g, "");
  var payload = {
    csrf_token: document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute("content") : "",
    name: name,
    phone: phone,
    email: (document.getElementById("new-customer-email").value || "").trim() || null,
    cep: cep || null,
    address_street: (document.getElementById("new-addr-street").value || "").trim() || null,
    address_number: (document.getElementById("new-addr-number").value || "").trim() || null,
    address_complement: (document.getElementById("new-addr-complement").value || "").trim() || null,
    address_neighborhood: (document.getElementById("new-addr-neighborhood").value || "").trim() || null,
    address_city: (document.getElementById("new-addr-city").value || "").trim() || null,
    address_state: (document.getElementById("new-addr-state").value || "").trim() || null,
  };
  var btn = document.getElementById("btn-save-new-customer");
  if (btn) btn.disabled = true;
  fetch(posUrl("customer/storeFromPos"), {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload),
  })
    .then(function (r) {
      if (!r.ok) {
        return r.text().then(function (txt) {
          var err = new Error("HTTP " + r.status);
          err.status = r.status;
          err.body = txt;
          throw err;
        });
      }
      return r.json();
    })
    .then(function (res) {
      if (res.success && res.customer) {
        selectCustomer(res.customer);
        closeNewCustomerModal();
      } else {
        alert(res.message || "Erro ao cadastrar cliente.");
      }
    })
    .catch(function (err) {
      var msg = "Erro ao cadastrar cliente.";
      if (err && err.body) {
        if (err.body.length < 200) msg = err.body;
        else if (err.status === 403) msg = "Acesso negado. Faça login novamente.";
        else if (err.status === 404) msg = "Rota não encontrada. Verifique se a URL do sistema está correta.";
      } else if (err && err.message) msg = err.message;
      alert(msg);
    })
    .finally(function () {
      if (btn) btn.disabled = false;
    });
}

(function setupNewCustomerModal() {
  var btnSave = document.getElementById("btn-save-new-customer");
  var btnNovo = document.getElementById("btn-novo-cliente-pdv");
  var newCep = document.getElementById("new-addr-cep");
  if (btnSave) btnSave.addEventListener("click", saveNewCustomer);
  if (btnNovo) btnNovo.addEventListener("click", function () { openNewCustomerModal(""); });
  if (newCep) {
    newCep.addEventListener("blur", function () {
      var cep = this.value.replace(/\D/g, "");
      if (cep.length !== 8) return;
      var msgEl = document.getElementById("new-addr-cep-msg");
      if (msgEl) msgEl.textContent = "Buscando...";
      fetch("https://viacep.com.br/ws/" + cep + "/json/")
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (msgEl) msgEl.textContent = "";
          if (data.erro) {
            if (msgEl) msgEl.textContent = "CEP não encontrado";
            return;
          }
          document.getElementById("new-addr-street").value = data.logradouro || "";
          document.getElementById("new-addr-neighborhood").value = data.bairro || "";
          document.getElementById("new-addr-city").value = data.localidade || "";
          document.getElementById("new-addr-state").value = data.uf || "";
        })
        .catch(function () {
          if (msgEl) msgEl.textContent = "Erro ao buscar CEP";
        });
    });
    newCep.addEventListener("input", function () {
      var v = this.value.replace(/\D/g, "");
      if (v.length > 8) v = v.slice(0, 8);
      this.value = v.length > 5 ? v.slice(0, 5) + "-" + v.slice(5) : v;
    });
  }
})();

document
  .getElementById("product-search")
  .addEventListener("input", function (e) {
    const term = this.value;
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      searchProducts(term);
    }, 300); // 300ms debounce
  });

// Abas PDV: Produtos, Bebidas, Sobremesas — ao clicar, filtra produtos por categoria
(function () {
  const tabContainer = document.querySelector(".pos-tabs");
  if (!tabContainer) return;
  // Event delegation: garante que clique na aba (ou em filho) dispare o filtro
  tabContainer.addEventListener("click", function (e) {
    const tab = e.target && e.target.closest && e.target.closest(".pos-tab");
    if (!tab) return;
    var raw = tab.getAttribute("data-category-id");
    var categoryId = (raw !== null && raw !== undefined && String(raw).trim() !== "" && String(raw).trim() !== "0")
      ? String(raw).trim()
      : "";
    var tabs = tabContainer.querySelectorAll(".pos-tab");
    tabs.forEach(function (t) {
      t.classList.remove("border-primary", "text-primary", "bg-white", "-mb-px");
      t.classList.add("border-transparent", "text-gray-500");
      t.setAttribute("aria-selected", "false");
    });
    tab.classList.add("border-primary", "text-primary", "bg-white", "-mb-px");
    tab.classList.remove("border-transparent", "text-gray-500");
    tab.setAttribute("aria-selected", "true");
    var term = (document.getElementById("product-search") || {}).value || "";
    searchProducts(term, categoryId);
  });
})();

// Atalhos: / busca, Enter adiciona primeiro produto, F2 finaliza, Esc limpa/fecha
document.addEventListener("keydown", function (e) {
  const productSearch = document.getElementById("product-search");
  const paymentModal = document.getElementById("paymentModalContainer");
  const modalOpen = paymentModal && !paymentModal.classList.contains("hidden");
  const target = e.target;
  const isAmountInput = target && (target.id === "amount-paid" || target.id === "discount-value" || target.id === "opening-amount");

  if (e.key === "/") {
    e.preventDefault();
    if (!modalOpen && productSearch) productSearch.focus();
    return;
  }
  if (e.key === "Escape") {
    if (modalOpen) {
      closePaymentModal();
    } else {
      if (productSearch) {
        productSearch.value = "";
        productSearch.focus();
        searchProducts("");
      }
      const customerList = document.getElementById("customer-list");
      if (customerList) customerList.style.display = "none";
    }
    e.preventDefault();
    return;
  }
  if (e.key === "F2") {
    e.preventDefault();
    if (!modalOpen && cart.length > 0) openPaymentModal();
    return;
  }
  if (e.key === "Enter" && !isAmountInput) {
    const firstCard = document.querySelector("#product-list .product-card");
    if (firstCard && target && target.id === "product-search") {
      e.preventDefault();
      firstCard.click();
    }
    return;
  }
  if (e.key === "F3") {
    e.preventDefault();
    if (productSearch) productSearch.focus();
    return;
  }
  if (e.key === "F4") {
    e.preventDefault();
    document.getElementById("customer-search").focus();
    return;
  }
  if (e.key === "F10") {
    e.preventDefault();
    if (modalOpen) {
      if (window.POS_CAN_DISCOUNT) document.getElementById("discount-value").focus();
      else document.getElementById("amount-paid").focus();
    } else {
      openPaymentModal();
    }
  }
});

/**
 * Busca produtos no PDV. categoryIdOpt: opcional; se não passado, lê da aba ativa no DOM.
 */
function searchProducts(term, categoryIdOpt) {
  const list = document.getElementById("product-list");
  if (!list) return;

  var categoryId = "";
  if (categoryIdOpt !== undefined && categoryIdOpt !== null) {
    categoryId = String(categoryIdOpt).trim();
  } else {
    var activeTab = document.querySelector(".pos-tab[aria-selected='true']") || document.querySelector(".pos-tab");
    if (activeTab) {
      var d = activeTab.getAttribute("data-category-id");
      if (d !== null && d !== "") categoryId = String(d).trim();
    }
  }
  if (categoryId === "0") categoryId = "";

  // Skeleton loading enquanto busca
  if (term.length > 0) {
    let skeleton = '<div class="grid grid-cols-5 gap-1.5">';
    for (let i = 0; i < 12; i++) {
      skeleton += '<div class="animate-pulse rounded p-0.5 min-w-0"><div class="w-8 h-8 mx-auto bg-gray-200 rounded mb-0.5"></div><div class="h-1.5 bg-gray-200 rounded w-full"></div><div class="h-1.5 bg-gray-200 rounded w-2/3 mt-0.5 mx-auto"></div></div>';
    }
    skeleton += '</div>';
    list.innerHTML = skeleton;
  }

  let url = posUrl("pos/search") + "&term=" + encodeURIComponent(term);
  if (categoryId !== "") url += "&category_id=" + encodeURIComponent(categoryId);
  fetch(url)
    .then((r) => {
      if (!r.ok) throw new Error("Network response was not ok");
      return r.json();
    })
    .then((data) => {
      list.innerHTML = "";
      posLastList = data || [];

      if (data.length === 0) {
        list.innerHTML =
          '<div class="pos-list-full text-center py-12">' +
          '<p class="text-gray-500 mb-4">Nenhum produto encontrado.</p>' +
          '<a href="?route=product/create" class="btn btn-primary btn-sm">Cadastrar produto</a>' +
          '</div>';
        return;
      }

      data.forEach((p) => {
        const raw = (p.image || "").trim();
        const imageSrc = raw.startsWith("http") ? raw : (raw ? (raw.startsWith("/") ? raw : "/" + raw) : "");
        const imageHtml = `
                    <div class="pos-card-img">
                        ${imageSrc ? `<img src="${escapeHtml(imageSrc)}" alt="" loading="lazy">` : `<i class="fas fa-box text-gray-200"></i>`}
                    </div>`;

        const codeHtml = p.code ? `<span class="text-gray-400 font-mono text-[6px] block truncate">#${escapeHtml(p.code)}</span>` : '';

        const card = `
                    <div class="group product-card min-w-0">
                        <div class="bg-white border border-gray-100 rounded p-0.5 cursor-pointer shadow-sm hover:shadow hover:border-indigo-200 transition-all flex flex-col items-center text-center relative overflow-hidden h-full" 
                             onclick="selectProduct(${p.id}); addToCart(${p.id}, '${p.name.replace(/'/g, "\\'")}', ${p.price}, ${p.stock}, ${p.is_gift_card || 0}, ${parseFloat(p.cost_price) || 0})">
                            ${p.stock <= 0 ? '<div class="absolute top-0.5 right-0.5 bg-red-500 text-white text-[5px] font-bold px-0.5 py-0.5 rounded z-10">SEM EST.</div>' : ""}
                            ${imageHtml}
                            <div class="w-full mt-0.5 min-w-0 px-0.5">
                                ${codeHtml}
                                <h3 class="text-[8px] font-semibold text-gray-700 line-clamp-2 leading-tight" title="${p.name}">${p.name}</h3>
                                <div class="mt-0.5">
                                    <span class="text-[8px] font-bold text-indigo-600 block">R$ ${parseFloat(p.price).toFixed(2).replace(".", ",")}</span>
                                    <span class="text-[6px] text-gray-400">${p.stock}</span>
                                </div>
                            </div>
                            <div class="absolute inset-0 bg-indigo-600 opacity-0 group-hover:opacity-[0.02] transition-opacity pointer-events-none"></div>
                        </div>
                    </div>`;
        list.innerHTML += card;
      });
    })
    .catch((err) => {
      console.error("Erro na busca:", err);
      list.innerHTML = `<div class="pos-list-full text-center text-red-600 py-12">Erro ao buscar produtos.<br><small>${escapeHtml(err.message)}</small></div>`;
    });
}

function selectProduct(productId) {
  const p = posLastList.find((x) => x.id == productId);
  const emptyEl = document.getElementById("product-detail-empty");
  const contentEl = document.getElementById("product-detail-content");
  if (!emptyEl || !contentEl) return;
  if (!p) {
    emptyEl.classList.remove("hidden");
    contentEl.classList.add("hidden");
    posSelectedProduct = null;
    return;
  }
  posSelectedProduct = p;
  emptyEl.classList.add("hidden");
  contentEl.classList.remove("hidden");

  const imgEl = document.getElementById("product-detail-image");
  const codeEl = document.getElementById("product-detail-code");
  const nameEl = document.getElementById("product-detail-name");
  const priceEl = document.getElementById("product-detail-price");
  const stockEl = document.getElementById("product-detail-stock");
  const qtyEl = document.getElementById("product-detail-qty");
  if (imgEl) {
    imgEl.innerHTML = p.image
      ? '<img src="' + escapeHtml(p.image) + '" class="w-full h-full object-cover" alt="">'
      : '<i class="fas fa-image text-gray-300 text-sm"></i>';
  }
  if (codeEl) codeEl.textContent = p.code ? "#" + p.code : "";
  if (nameEl) nameEl.textContent = p.name;
  if (priceEl) priceEl.textContent = "R$ " + parseFloat(p.price).toFixed(2).replace(".", ",");
  if (stockEl) stockEl.textContent = "Estoque: " + (p.stock != null ? p.stock : "-");
  if (qtyEl) {
    qtyEl.value = 1;
    qtyEl.max = p.stock != null && p.stock > 0 ? p.stock : 9999;
  }
}

function addToCart(id, name, price, stock, isGiftCard = 0, cost = 0, qtyOverride = null) {
  const qty = qtyOverride != null && qtyOverride > 0 ? parseInt(qtyOverride, 10) : 1;
  let finalPrice = parseFloat(price);
  const finalCost = parseFloat(cost) || 0;
  if (isGiftCard) {
    const value = prompt("Digite o valor do Vale Presente:", "0.00");
    if (value === null || isNaN(parseFloat(value)) || parseFloat(value) <= 0)
      return;
    finalPrice = parseFloat(value);
  }

  const existing = cart.find((i) => i.id === id && i.price === finalPrice);
  if (existing) {
    if (!isGiftCard && existing.quantity + qty > stock) {
      alert("Estoque insuficiente!");
      return;
    }
    existing.quantity += qty;
  } else {
    if (!isGiftCard && stock <= 0) {
      alert("Sem estoque!");
      return;
    }
    cart.push({ id, name, price: finalPrice, quantity: qty, stock, cost: finalCost });
  }
  renderCart();

  if (qtyOverride == null) {
    const searchInput = document.getElementById("product-search");
    if (searchInput) {
      searchInput.value = "";
      searchInput.focus();
      searchProducts("");
    }
  }
}

// ... (previous code above remains mostly similar, specifically renderCart needs class updates)

function renderCart() {
  const tbody = document.getElementById("cart-table-body");
  const emptyEl = document.getElementById("pos-cart-empty");
  const tableEl = document.getElementById("pos-cart-table");
  const countEl = document.getElementById("cart-count");
  if (!tbody) return;
  tbody.innerHTML = "";

  cart.forEach((item, index) => {
    const subtotal = item.price * item.quantity;
    tbody.innerHTML += `
        <tr class="hover:bg-gray-50">
            <td class="px-2 py-1.5 truncate max-w-[120px]" title="${escapeHtml(item.name)}">${escapeHtml(item.name)}</td>
            <td class="px-1 py-1.5 whitespace-nowrap">
                <div class="flex items-center justify-center border rounded-md text-[10px]">
                    <button type="button" class="px-1.5 py-0.5 hover:bg-gray-100" onclick="updateQty(${index}, -1)">-</button>
                    <span class="px-1 font-medium">${item.quantity}</span>
                    <button type="button" class="px-1.5 py-0.5 hover:bg-gray-100" onclick="updateQty(${index}, 1)">+</button>
                </div>
            </td>
            <td class="px-2 py-1.5 text-right font-medium text-gray-700">R$ ${subtotal.toFixed(2).replace(".", ",")}</td>
            <td class="px-1 py-1.5 text-right">
                <button type="button" class="text-red-400 hover:text-red-600" onclick="removeFromCart(${index})" aria-label="Remover">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        </tr>`;
  });

  const totals = getCartTotals();
  const fmt = (n) => "R$ " + n.toFixed(2).replace(".", ",");
  const subEl = document.getElementById("cart-subtotal");
  const taxAmtEl = document.getElementById("cart-tax-amount");
  const totalEl = document.getElementById("cart-total");
  const profitEl = document.getElementById("cart-profit");
  if (subEl) subEl.textContent = fmt(totals.subtotal);
  if (taxAmtEl) taxAmtEl.textContent = fmt(totals.taxAmount);
  if (totalEl) totalEl.innerText = totals.total.toFixed(2).replace(".", ",");
  if (profitEl) profitEl.textContent = fmt(totals.totalProfit);
  if (countEl) {
    if (totals.count > 0) {
      countEl.classList.remove("hidden");
      countEl.innerText = totals.count + " itens";
    } else {
      countEl.classList.add("hidden");
    }
  }
  if (emptyEl && tableEl) {
    if (cart.length === 0) {
      emptyEl.classList.remove("hidden");
      tableEl.classList.add("hidden");
    } else {
      emptyEl.classList.add("hidden");
      tableEl.classList.remove("hidden");
    }
  }
  saveSessionFromUI();
  persistPosSessions();
  updateExcluirCaixaButton();
}

function updateQty(index, change) {
  const item = cart[index];
  if (change === 1 && item.quantity >= item.stock) {
    alert("Limite de estoque atingido!");
    return;
  }
  item.quantity += change;
  if (item.quantity <= 0) {
    cart.splice(index, 1);
  }
  renderCart();
}

function removeFromCart(index) {
  cart.splice(index, 1);
  renderCart();
}

// Modal Logic (Vanilla JS for Tailwind)
function setupCartPanelListeners() {
  const btnNovaVenda = document.getElementById('pos-btn-nova-venda');
  if (btnNovaVenda) {
    btnNovaVenda.addEventListener('click', addPosSession);
  }
  const btnExcluir = document.getElementById('pos-btn-excluir-caixa');
  if (btnExcluir) {
    btnExcluir.addEventListener('click', function () {
      removePosSession(activeSessionIndex);
    });
  }
  const disc = document.getElementById("cart-discount");
  const sur = document.getElementById("cart-surcharge");
  const tax = document.getElementById("cart-tax-enabled");
  const channel = document.getElementById("pos-sale-channel");
  const cartName = document.getElementById("pos-cart-customer-name");
  const obs = document.getElementById("order-observation");
  [disc, sur, tax, obs].forEach(function (el) {
    if (el) {
      el.addEventListener("input", renderCart);
      el.addEventListener("change", renderCart);
    }
  });
  if (channel) {
    channel.addEventListener("change", function () {
      posIsPickup = channel.value === "retirada";
      const retiradaChk = document.getElementById("customer-retirada");
      if (retiradaChk) retiradaChk.checked = posIsPickup;
      refreshCustomerAddressUI();
      saveSessionFromUI();
      persistPosSessions();
    });
  }
  if (cartName) {
    cartName.addEventListener("input", function () {
      const top = document.getElementById("customer-search");
      if (top) top.value = this.value;
      bindCartCustomerSearch(this);
    });
  }
  const btnCartNovo = document.getElementById("btn-novo-cliente-cart");
  if (btnCartNovo) {
    btnCartNovo.addEventListener("click", function () {
      openNewCustomerModal(
        (document.getElementById("pos-cart-customer-name") || {}).value || "",
      );
    });
  }
}

function bindCartCustomerSearch(inputEl) {
  const term = inputEl.value;
  const list = document.getElementById("pos-cart-customer-list");
  if (!list) return;
  if (term.length < 2) {
    list.classList.add("hidden");
    return;
  }
  clearTimeout(cartCustomerTimeout);
  cartCustomerTimeout = setTimeout(function () {
    fetch(posUrl("customer/search") + "&term=" + encodeURIComponent(term))
      .then(function (r) { return r.json(); })
      .then(function (data) {
        list.innerHTML = "";
        list.classList.remove("hidden");
        if (data.length > 0) {
          data.forEach(function (c) {
            const item = document.createElement("button");
            item.type = "button";
            item.className =
              "block w-full text-left px-3 py-2 hover:bg-gray-100 border-b border-gray-100 last:border-0";
            item.innerHTML =
              "<strong>" +
              escapeHtml(c.name) +
              "</strong> <small class=\"text-gray-500\">| " +
              escapeHtml(c.phone || "") +
              "</small>";
            item.onclick = function () { selectCustomer(c); };
            list.appendChild(item);
          });
        } else {
          const addItem = document.createElement("button");
          addItem.type = "button";
          addItem.className =
            "block w-full text-left px-3 py-2 hover:bg-emerald-50 text-emerald-700 font-medium";
          addItem.innerHTML =
            "<i class=\"fas fa-plus-circle mr-2\"></i> Cadastrar novo cliente";
          addItem.onclick = function () { openNewCustomerModal(term); };
          list.appendChild(addItem);
        }
      });
  }, 300);
}

function openPaymentModal() {
  if (cart.length === 0) {
    alert("Carrinho vazio!");
    return;
  }
  const totals = getCartTotals();
  const total = totals.total;
  document.getElementById("modal-total").value =
    "R$ " + total.toFixed(2).replace(".", ",");
  document.getElementById("modal-total").dataset.original = total;
  document.getElementById("modal-total").dataset.subtotal = totals.subtotal;

  const modalDisc = document.getElementById("discount-value");
  if (modalDisc) modalDisc.value = totals.discount > 0 ? totals.discount.toFixed(2) : "";
  document.getElementById("amount-paid").value = total.toFixed(2);
  document.getElementById("change-value").value = "R$ 0,00";

  toggleChangeField();

  const modal = document.getElementById("paymentModalContainer");
  modal.classList.remove("hidden");

  setTimeout(() => {
    if (window.POS_CAN_DISCOUNT)
      document.getElementById("discount-value").focus();
    else document.getElementById("amount-paid").focus();
  }, 100);
}

function updateTotalWithDiscount() {
  const discInput = document.getElementById("discount-value");
  const cartDisc = document.getElementById("cart-discount");
  if (cartDisc && discInput) {
    cartDisc.value = (parseFloat(discInput.value) || 0).toFixed(2);
  }
  const totals = getCartTotals();
  const newTotal = totals.total;
  document.getElementById("modal-total").value =
    "R$ " + newTotal.toFixed(2).replace(".", ",");
  document.getElementById("modal-total").dataset.original = newTotal;

  const method = document.getElementById("payment-method").value;
  if (method !== "A Prazo") {
    document.getElementById("amount-paid").value = newTotal.toFixed(2);
  }

  toggleChangeField();
  calculateChange();
}

function closePaymentModal() {
  const modal = document.getElementById("paymentModalContainer");
  modal.classList.add("hidden");
}

// Close modal on Escape key
document.addEventListener("keydown", function (e) {
  if (e.key === "Escape") {
    closePaymentModal();
  }
  if (e.key === "Enter") {
    const modal = document.getElementById("paymentModalContainer");
    if (!modal.classList.contains("hidden")) {
      processCheckout();
    }
  }
});

function toggleChangeField() {
  const method = document.getElementById("payment-method").value;
  const isMoney = method === "Dinheiro";

  document.getElementById("amount-paid").disabled =
    !isMoney && method !== "Vale Presente";
  document.getElementById("gift-card-row").style.display =
    method === "Vale Presente" ? "block" : "none";

  let total = getCartTotals().total;

  if (!isMoney && method !== "A Prazo" && method !== "Vale Presente") {
    document.getElementById("change-row").style.display = "none";
    document.getElementById("amount-paid").value = total.toFixed(2);
  } else {
    document.getElementById("change-row").style.display = "block";
    if (method === "A Prazo") {
      document.getElementById("amount-paid").value = "0.00";
      document.getElementById("amount-paid").focus();
    }
    if (method === "Vale Presente") {
      document.getElementById("amount-paid").value = "0.00";
      document.getElementById("gift-card-code").focus();
    }
  }
}

function verifyGiftCard() {
  const code = document.getElementById("gift-card-code").value;
  if (!code) return;

  fetch(posUrl("giftcard/check") + "&code=" + encodeURIComponent(code))
    .then((r) => r.json())
    .then((data) => {
      const info = document.getElementById("gift-card-info");
      if (data.success) {
        info.innerText = `Saldo: R$ ${data.balance.toFixed(2)}`;
        info.className = "text-xs mt-1 font-bold text-green-600";
        document.getElementById("gift-card-id").value = data.id;

        // Sugerir o valor total ou o saldo disponível
        const total = parseFloat(
          document
            .getElementById("modal-total")
            .value.replace("R$ ", "")
            .replace(",", "."),
        );
        const suggested = Math.min(total, data.balance);
        document.getElementById("amount-paid").value = suggested.toFixed(2);
      } else {
        info.innerText = data.message;
        info.className = "text-xs mt-1 font-bold text-red-600";
        document.getElementById("gift-card-id").value = "";
      }
    });
}

function calculateChange() {
  let total = getCartTotals().total;

  const paid = parseFloat(document.getElementById("amount-paid").value) || 0;
  const change = paid - total;

  if (change >= 0) {
    document.getElementById("change-value").value =
      "R$ " + change.toFixed(2).replace(".", ",");
    document.getElementById("change-value").classList.remove("text-danger");
    document.getElementById("change-value").classList.add("text-success");
  } else {
    document.getElementById("change-value").value =
      "Falta R$ " + Math.abs(change).toFixed(2).replace(".", ",");
    document.getElementById("change-value").classList.add("text-danger");
    document.getElementById("change-value").classList.remove("text-success");
  }
}

function processCheckout() {
  saveSessionFromUI();
  const totals = getCartTotals();
  const originalTotal = totals.subtotal;
  const discount = window.POS_CAN_DISCOUNT
    ? parseFloat(document.getElementById("discount-value").value) ||
      totals.discount ||
      0
    : 0;
  const surcharge = totals.surcharge;
  const taxAmount = totals.taxAmount;
  let total = totals.total;
  if (total < 0) total = 0;

  const method = document.getElementById("payment-method").value;
  let paid = parseFloat(document.getElementById("amount-paid").value) || 0;
  const idEl = document.getElementById("selected-customer-id");
  const customerId = idEl && idEl.value ? idEl.value : null;

  if (
    method === "Vale Presente" &&
    !document.getElementById("gift-card-id").value
  ) {
    alert("Você deve validar um Vale Presente antes de finalizar!");
    return;
  }

  if (method === "Dinheiro" && paid < total) {
    alert("Valor pago insuficiente!");
    return;
  }

  if (
    method !== "Dinheiro" &&
    method !== "A Prazo" &&
    method !== "Vale Presente"
  ) {
    paid = total;
  }

  // Na venda a prazo ou vale, a lógica de troco/entrada muda
  const change =
    method === "Dinheiro" || method === "A Prazo" || method === "Vale Presente"
      ? paid - total
      : 0;

  if (method === "Vale Presente" && paid < total) {
    alert("O saldo do Vale não cobre o total da venda!");
    return;
  }

  if (method === "Dinheiro" && change < 0) {
    alert("Valor pago insuficiente!");
    return;
  }

  const cartNameEl = document.getElementById("pos-cart-customer-name");
  const cartPhoneEl = document.getElementById("pos-cart-customer-phone");
  const customerName = cartNameEl
    ? cartNameEl.value.trim()
    : (document.getElementById("customer-search") || {}).value.trim();
  const customerPhone = cartPhoneEl ? cartPhoneEl.value.trim() : "";

  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const csrfToken = csrfMeta ? csrfMeta.getAttribute("content") : "";
  const obsEl = document.getElementById("order-observation");
  const observation = obsEl ? (obsEl.value || "").trim() : "";
  const channelEl = document.getElementById("pos-sale-channel");
  const saleChannel = channelEl ? channelEl.value : "balcao";
  const data = {
    cart: cart,
    paymentMethod: method,
    amountPaid: paid,
    change: change,
    customerId: customerId,
    customerName: customerName,
    customerPhone: customerPhone || null,
    isPickup: posIsPickup || saleChannel === "retirada",
    deliveryAddress: posIsPickup || saleChannel === "retirada" ? null : (posDeliveryAddress || null),
    discount: discount,
    surcharge: surcharge,
    taxAmount: taxAmount,
    saleChannel: saleChannel,
    giftCardId: document.getElementById("gift-card-id").value || null,
    observation: observation || null,
    csrf_token: csrfToken,
  };

  fetch(posUrl("pos/checkout"), {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  })
    .then((r) => r.json())
    .then((res) => {
      if (res.success) {
        if (posSessions.length > 1) {
          posSessions.splice(activeSessionIndex, 1);
          activeSessionIndex = Math.min(activeSessionIndex, posSessions.length - 1);
          posSessions.forEach(function (s, i) {
            s.label = "Caixa " + (i + 1);
          });
          loadSessionToUI(activeSessionIndex);
          persistPosSessions();
          window.location.href = `index.php?route=pos/receipt&id=${res.saleId}`;
        } else {
          posSessions = [createEmptyPosSession(1)];
          activeSessionIndex = 0;
          sessionStorage.removeItem(POS_SESSIONS_STORAGE_KEY);
          window.location.href = `index.php?route=pos/receipt&id=${res.saleId}`;
        }
      } else {
        alert("Erro: " + res.message);
      }
    })
    .catch((err) => {
      alert("Erro de conexão ao processar venda.");
      console.error(err);
    });
}

function checkCashStatus() {
  fetch(posUrl("cash/status"))
    .then((r) => r.json())
    .then((data) => {
      const btnAbrir = document.getElementById("pos-btn-abrir-caixa");
      const lblAberto = document.getElementById("pos-caixa-aberto-label");
      if (data.isOpen) {
        if (btnAbrir) btnAbrir.classList.add("hidden");
        if (lblAberto) lblAberto.classList.remove("hidden");
      } else {
        if (btnAbrir) btnAbrir.classList.remove("hidden");
        if (lblAberto) lblAberto.classList.add("hidden");
        const modal = document.getElementById("openCashModal");
        if (modal) modal.classList.remove("hidden");
        setTimeout(
          () => {
            const amountEl = document.getElementById("opening-amount");
            if (amountEl) amountEl.focus();
          },
          100,
        );
      }
    })
    .catch(() => {
      const btnAbrir = document.getElementById("pos-btn-abrir-caixa");
      if (btnAbrir) btnAbrir.classList.remove("hidden");
    });
}

function openRegister() {
  const amount = document.getElementById("opening-amount").value;
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";

  fetch(posUrl("cash/open"), {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "csrf_token=" + encodeURIComponent(csrf) + "&amount=" + encodeURIComponent(amount),
  })
    .then((r) => r.json())
    .then((res) => {
      if (res.success) {
        const modal = document.getElementById("openCashModal");
        if (modal) modal.classList.add("hidden");
        const btnAbrir = document.getElementById("pos-btn-abrir-caixa");
        const lblAberto = document.getElementById("pos-caixa-aberto-label");
        if (btnAbrir) btnAbrir.classList.add("hidden");
        if (lblAberto) lblAberto.classList.remove("hidden");
      } else {
        alert("Erro: " + (res.message || "Não foi possível abrir o caixa."));
      }
    });
}
