let cart = [];
let searchTimeout;
let customerTimeout;
/** Lista da última busca (para painel de detalhe) */
let posLastList = [];
/** Produto atualmente selecionado no painel */
let posSelectedProduct = null;

function escapeHtml(s) {
  if (s == null || s === "") return "";
  const div = document.createElement("div");
  div.textContent = String(s);
  return div.innerHTML;
}

// Load all products on init
document.addEventListener("DOMContentLoaded", () => {
  searchProducts("");
  document.getElementById("product-search").focus();
  checkCashStatus();

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
      fetch(`index.php?route=customer/search&term=${encodeURIComponent(term)}`)
        .then((r) => r.json())
        .then((data) => {
          list.innerHTML = "";
          if (data.length > 0) {
            list.style.display = "block";
            data.forEach((c) => {
              const item = document.createElement("a");
              item.className =
                "list-group-item list-group-item-action cursor-pointer";
              item.innerHTML = `<strong>${c.name}</strong> <small class="text-muted">| ${c.phone || ""}</small>`;
              item.onclick = () => selectCustomer(c.id, c.name);
              list.appendChild(item);
            });
          } else {
            list.style.display = "none";
          }
        });
    }, 300);
  });

function selectCustomer(id, name) {
  document.getElementById("selected-customer-id").value = id;
  document.getElementById("customer-search").value = name;
  document.getElementById("customer-search").classList.add("is-valid");
  document.getElementById("customer-list").style.display = "none";
}

document
  .getElementById("product-search")
  .addEventListener("input", function (e) {
    const term = this.value;
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      searchProducts(term);
    }, 300); // 300ms debounce
  });

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

function searchProducts(term) {
  const list = document.getElementById("product-list");

  // Skeleton loading enquanto busca
  if (term.length > 0) {
    let skeleton = '<div class="grid grid-cols-5 gap-1.5">';
    for (let i = 0; i < 12; i++) {
      skeleton += '<div class="animate-pulse rounded p-0.5 min-w-0"><div class="w-8 h-8 mx-auto bg-gray-200 rounded mb-0.5"></div><div class="h-1.5 bg-gray-200 rounded w-full"></div><div class="h-1.5 bg-gray-200 rounded w-2/3 mt-0.5 mx-auto"></div></div>';
    }
    skeleton += '</div>';
    list.innerHTML = skeleton;
  }

  fetch(`index.php?route=pos/search&term=${encodeURIComponent(term)}`)
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
  tbody.innerHTML = "";
  let total = 0;
  let totalProfit = 0;
  let count = 0;

  cart.forEach((item, index) => {
    const subtotal = item.price * item.quantity;
    const cost = parseFloat(item.cost) || 0;
    const profit = (item.price - cost) * item.quantity;
    total += subtotal;
    totalProfit += profit;
    count += item.quantity;

    const profitClass = profit >= 0 ? "text-green-600" : "text-red-600";
    const profitText = "R$ " + profit.toFixed(2).replace(".", ",");

    tbody.innerHTML += `
        <tr class="hover:bg-gray-50">
            <td class="px-3 py-2 text-truncate max-w-[150px] truncate" title="${item.name}">${item.name}</td>
            <td class="px-3 py-2 whitespace-nowrap">
                <div class="flex items-center justify-center border rounded-md">
                    <button class="px-2 py-1 hover:bg-gray-100" onclick="updateQty(${index}, -1)">-</button>
                    <span class="px-2 font-medium">${item.quantity}</span>
                    <button class="px-2 py-1 hover:bg-gray-100" onclick="updateQty(${index}, 1)">+</button>
                </div>
            </td>
            <td class="px-3 py-2 text-right font-medium text-gray-700">R$ ${subtotal.toFixed(2).replace(".", ",")}</td>
            <td class="px-3 py-2 text-right font-medium ${profitClass}">${profitText}</td>
            <td class="px-3 py-2 text-right">
                <button class="text-red-400 hover:text-red-600 transition-colors" onclick="removeFromCart(${index})">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        </tr>`;
  });

  document.getElementById("cart-total").innerText = total
    .toFixed(2)
    .replace(".", ",");
  const profitEl = document.getElementById("cart-profit");
  if (profitEl) {
    profitEl.textContent = "R$ " + totalProfit.toFixed(2).replace(".", ",");
    profitEl.className = "text-sm font-semibold " + (totalProfit >= 0 ? "text-green-600" : "text-red-600");
  }
  document.getElementById("cart-count").innerText = count + " itens";
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
function openPaymentModal() {
  if (cart.length === 0) {
    alert("Carrinho vazio!");
    return;
  }
  const total = cart.reduce((acc, item) => acc + item.price * item.quantity, 0);
  document.getElementById("modal-total").value =
    "R$ " + total.toFixed(2).replace(".", ",");
  // Store original total in dataset for easier recalc
  document.getElementById("modal-total").dataset.original = total;

  document.getElementById("discount-value").value = "";
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
  const originalTotal =
    parseFloat(document.getElementById("modal-total").dataset.original) || 0;
  const discount =
    parseFloat(document.getElementById("discount-value").value) || 0;

  let newTotal = originalTotal - discount;
  if (newTotal < 0) newTotal = 0;

  document.getElementById("modal-total").value =
    "R$ " + newTotal.toFixed(2).replace(".", ",");

  // Auto update paid amount to match new total
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

  const originalTotal =
    parseFloat(document.getElementById("modal-total").dataset.original) || 0;
  const discount =
    parseFloat(document.getElementById("discount-value").value) || 0;
  let total = originalTotal - discount;
  if (total < 0) total = 0;

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

  fetch(`?route=giftcard/check&code=${code}`)
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
  const originalTotal =
    parseFloat(document.getElementById("modal-total").dataset.original) || 0;
  const discount =
    parseFloat(document.getElementById("discount-value").value) || 0;
  let total = originalTotal - discount;
  if (total < 0) total = 0;

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
  const originalTotal = cart.reduce(
    (acc, item) => acc + item.price * item.quantity,
    0,
  );
  const discount = window.POS_CAN_DISCOUNT
    ? parseFloat(document.getElementById("discount-value").value) || 0
    : 0;
  let total = originalTotal - discount;
  if (total < 0) total = 0;

  const method = document.getElementById("payment-method").value;
  let paid = parseFloat(document.getElementById("amount-paid").value) || 0;
  const customerId =
    document.getElementById("selected-customer-id").value || null;

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

  const customerNameEl = document.getElementById("customer-search");
  const customerName = customerNameEl ? customerNameEl.value.trim() : "";

  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const csrfToken = csrfMeta ? csrfMeta.getAttribute("content") : "";
  const data = {
    cart: cart,
    paymentMethod: method,
    amountPaid: paid,
    change: change,
    customerId: customerId,
    customerName: customerName,
    discount: discount,
    giftCardId: document.getElementById("gift-card-id").value || null,
    csrf_token: csrfToken,
  };

  fetch("index.php?route=pos/checkout", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  })
    .then((r) => r.json())
    .then((res) => {
      if (res.success) {
        window.location.href = `index.php?route=pos/receipt&id=${res.saleId}`;
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
  fetch("index.php?route=cash/status")
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

  fetch("index.php?route=cash/open", {
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
