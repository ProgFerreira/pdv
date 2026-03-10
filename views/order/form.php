<?php
// Página pública: pedido pelo link. Layout simples sem sidebar; menos espaço no topo.
$is_order_form_page = true;
require dirname(__DIR__) . '/layouts/header.php';
?>
<div class="max-w-4xl mx-auto pt-1">
    <header class="text-center py-2 mb-3 border-b border-gray-200 bg-gray-50 rounded-lg px-4">
        <?php $logoOrder = (defined('BASE_URL') ? BASE_URL : '') . 'public/assets/logo-alianca-galeteria.png'; ?>
        <img src="<?php echo htmlspecialchars($logoOrder, ENT_QUOTES, 'UTF-8'); ?>" alt="Aliança Galeteria" class="mx-auto h-12 w-auto object-contain mb-1.5 max-w-[120px]">
        <p class="text-lg font-bold text-gray-900">GALETERIA ALIANÇA</p>
        <p class="text-gray-700 text-sm mt-0.5"><i class="fas fa-phone-alt mr-1"></i> Telefone: <a href="tel:+5511932101000" class="font-semibold text-primary hover:underline">(11) 93210-1000</a></p>
    </header>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Fazer pedido</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Produtos</h2>
            <?php
            $orderTabs = $orderTabs ?? [
                ['label' => 'Produtos', 'category_id' => null],
                ['label' => 'Bebidas', 'category_id' => null],
                ['label' => 'Sobremesas', 'category_id' => null],
            ];
            ?>
            <div class="order-tabs flex border-b border-gray-200 mb-3 gap-0" role="tablist">
                <?php foreach ($orderTabs as $i => $tab): ?>
                <?php
                $catId = isset($tab['category_id']) && $tab['category_id'] !== null && $tab['category_id'] !== '' ? (int) $tab['category_id'] : '';
                $active = $i === 0;
                ?>
                <button type="button" class="order-tab px-4 py-2.5 text-sm font-semibold border-b-2 transition-colors <?php echo $active ? 'border-primary text-primary bg-white -mb-px' : 'border-transparent text-gray-500 hover:text-gray-700'; ?>" data-category-id="<?php echo $catId === '' ? '' : (int) $catId; ?>" role="tab" aria-selected="<?php echo $active ? 'true' : 'false'; ?>">
                    <?php echo htmlspecialchars($tab['label'], ENT_QUOTES, 'UTF-8'); ?>
                </button>
                <?php endforeach; ?>
            </div>
            <div id="order-product-list" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <?php foreach ($products as $p): ?>
                <?php $pCatId = isset($p['category_id']) && $p['category_id'] !== null && $p['category_id'] !== '' ? (int) $p['category_id'] : ''; ?>
                <div class="order-product-card border border-gray-200 rounded-lg p-3 flex items-center justify-between gap-2 bg-white" data-category-id="<?php echo $pCatId === '' ? '' : (int) $pCatId; ?>">
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-gray-900 truncate"><?php echo htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="text-sm text-gray-600">R$ <?php echo number_format((float) $p['price'], 2, ',', '.'); ?></p>
                    </div>
                    <button type="button" class="order-add-btn flex-shrink-0 px-3 py-1.5 bg-primary text-white text-sm font-medium rounded hover:opacity-90"
                            data-id="<?php echo (int) $p['id']; ?>"
                            data-name="<?php echo htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-price="<?php echo htmlspecialchars((string) $p['price'], ENT_QUOTES, 'UTF-8'); ?>">
                        <i class="fas fa-plus mr-1"></i> Adicionar
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (empty($products)): ?>
            <p class="text-gray-500">Nenhum produto disponível no momento.</p>
            <?php endif; ?>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Seu pedido</h2>
            <div id="cart-area" class="border border-gray-200 rounded-lg p-4 bg-gray-50 mb-4">
                <ul id="cart-list" class="space-y-2 text-sm"></ul>
                <p id="cart-empty" class="text-gray-500">Carrinho vazio. Adicione produtos acima.</p>
                <p id="cart-total" class="mt-3 font-bold text-gray-900 hidden"></p>
            </div>

            <form id="order-form" action="<?php echo htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8'); ?>?route=order/submit" method="POST" class="space-y-4">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="items_json" id="items-json" value="[]">

                <div>
                    <label for="guest_phone" class="block text-sm font-medium text-gray-700">Telefone *</label>
                    <input type="text" id="guest_phone" name="guest_phone" required placeholder="(00) 00000-0000" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-primary focus:ring-primary" autocomplete="tel">
                    <p id="phone-lookup-hint" class="mt-1 text-xs text-gray-500">Digite seu telefone para preencher nome e endereço automaticamente (se já for cliente).</p>
                </div>
                <div>
                    <label for="guest_name" class="block text-sm font-medium text-gray-700">Nome *</label>
                    <input type="text" id="guest_name" name="guest_name" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-primary focus:ring-primary">
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_pickup" name="is_pickup" value="1" class="rounded border-gray-300 text-primary focus:ring-primary">
                    <label for="is_pickup" class="text-sm font-medium text-gray-700">Retirada no local</label>
                </div>
                <div id="delivery-field" class="space-y-3">
                    <div>
                        <label for="cep" class="block text-sm font-medium text-gray-700">CEP *</label>
                        <input type="text" id="cep" name="cep" maxlength="9" placeholder="00000-000" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-primary focus:ring-primary" autocomplete="postal-code">
                        <p id="cep-error" class="mt-1 text-sm text-red-600 hidden">CEP não encontrado. Verifique e tente novamente.</p>
                    </div>
                    <div>
                        <label for="address_street" class="block text-sm font-medium text-gray-700">Logradouro</label>
                        <input type="text" id="address_street" readonly class="mt-1 block w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-gray-700">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="address_number" class="block text-sm font-medium text-gray-700">Número *</label>
                            <input type="text" id="address_number" name="address_number" placeholder="Somente número" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-primary focus:ring-primary">
                        </div>
                        <div>
                            <label for="address_complement" class="block text-sm font-medium text-gray-700">Complemento</label>
                            <input type="text" id="address_complement" name="address_complement" placeholder="Apto, bloco..." class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-primary focus:ring-primary">
                        </div>
                    </div>
                    <div>
                        <label for="address_neighborhood" class="block text-sm font-medium text-gray-700">Bairro</label>
                        <input type="text" id="address_neighborhood" readonly class="mt-1 block w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-gray-700">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="address_city" class="block text-sm font-medium text-gray-700">Cidade</label>
                            <input type="text" id="address_city" readonly class="mt-1 block w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-gray-700">
                        </div>
                        <div>
                            <label for="address_state" class="block text-sm font-medium text-gray-700">UF</label>
                            <input type="text" id="address_state" readonly maxlength="2" class="mt-1 block w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-gray-700">
                        </div>
                    </div>
                    <input type="hidden" name="delivery_address" id="delivery_address" value="">
                </div>
                <div>
                    <label for="observation" class="block text-sm font-medium text-gray-700">Observação</label>
                    <input type="text" id="observation" name="observation" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm" placeholder="Ex: sem cebola">
                </div>

                <button type="submit" id="submit-btn" disabled class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    Enviar pedido
                </button>
            </form>
            <div id="form-message" class="mt-3 hidden"></div>
        </div>
    </div>
</div>

<script>
(function() {
    var cart = [];
    var cartList = document.getElementById('cart-list');
    var cartEmpty = document.getElementById('cart-empty');
    var cartTotal = document.getElementById('cart-total');
    var itemsJson = document.getElementById('items-json');
    var submitBtn = document.getElementById('submit-btn');
    var formMessage = document.getElementById('form-message');
    var orderForm = document.getElementById('order-form');
    var deliveryField = document.getElementById('delivery-field');

    function updateCart() {
        cartList.innerHTML = '';
        if (cart.length === 0) {
            cartEmpty.classList.remove('hidden');
            cartTotal.classList.add('hidden');
            submitBtn.disabled = true;
            itemsJson.value = '[]';
            return;
        }
        cartEmpty.classList.add('hidden');
        var total = 0;
        cart.forEach(function(item, idx) {
            var sub = item.price * item.quantity;
            total += sub;
            var li = document.createElement('li');
            li.className = 'flex justify-between items-center';
            li.innerHTML = '<span>' + escapeHtml(item.name) + ' x ' + item.quantity + '</span>' +
                '<span>R$ ' + sub.toFixed(2).replace('.', ',') + '</span>' +
                ' <button type="button" class="cart-remove text-red-600 hover:underline ml-2" data-idx="' + idx + '">Remover</button>';
            cartList.appendChild(li);
        });
        cartTotal.textContent = 'Total: R$ ' + total.toFixed(2).replace('.', ',');
        cartTotal.classList.remove('hidden');
        submitBtn.disabled = false;
        itemsJson.value = JSON.stringify(cart.map(function(i) {
            return { product_id: i.id, quantity: i.quantity, unit_price: i.price };
        }));
    }

    function escapeHtml(s) {
        var div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
    }

    document.querySelectorAll('.order-add-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = parseInt(this.getAttribute('data-id'), 10);
            var name = this.getAttribute('data-name');
            var price = parseFloat(this.getAttribute('data-price'));
            var found = cart.find(function(i) { return i.id === id; });
            if (found) {
                found.quantity += 1;
            } else {
                cart.push({ id: id, name: name, price: price, quantity: 1 });
            }
            updateCart();
        });
    });

    cartList.addEventListener('click', function(e) {
        var r = e.target.closest('.cart-remove');
        if (r) {
            var idx = parseInt(r.getAttribute('data-idx'), 10);
            cart.splice(idx, 1);
            updateCart();
        }
    });

    document.getElementById('is_pickup').addEventListener('change', function() {
        deliveryField.style.display = this.checked ? 'none' : 'block';
    });

    // Busca cliente por telefone e preenche nome e endereço
    var guestPhone = document.getElementById('guest_phone');
    var phoneHint = document.getElementById('phone-lookup-hint');
    var lookupBase = orderForm.action.split('?')[0] + '?route=order/lookupByPhone';
    function maskPhone(v) {
        v = (v || '').replace(/\D/g, '');
        if (v.length > 10) {
            return '(' + v.slice(0, 2) + ') ' + v.slice(2, 7) + '-' + v.slice(7, 11);
        }
        if (v.length > 6) {
            return '(' + v.slice(0, 2) + ') ' + v.slice(2, 6) + '-' + v.slice(6);
        }
        if (v.length > 2) {
            return '(' + v.slice(0, 2) + ') ' + v.slice(2);
        }
        return v;
    }
    guestPhone.addEventListener('input', function() {
        this.value = maskPhone(this.value);
    });
    guestPhone.addEventListener('blur', function() {
        var phone = (this.value || '').replace(/\D/g, '');
        if (phone.length < 8) {
            phoneHint.textContent = 'Digite seu telefone para preencher nome e endereço automaticamente (se já for cliente).';
            phoneHint.className = 'mt-1 text-xs text-gray-500';
            return;
        }
        fetch(lookupBase + '&phone=' + encodeURIComponent(this.value))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success && data.customer) {
                    var c = data.customer;
                    document.getElementById('guest_name').value = c.name || '';
                    document.getElementById('cep').value = c.cep || '';
                    document.getElementById('address_street').value = c.address_street || '';
                    document.getElementById('address_number').value = c.address_number || '';
                    document.getElementById('address_complement').value = c.address_complement || '';
                    document.getElementById('address_neighborhood').value = c.address_neighborhood || '';
                    document.getElementById('address_city').value = c.address_city || '';
                    document.getElementById('address_state').value = c.address_state || '';
                    phoneHint.textContent = 'Dados preenchidos com o cadastro do cliente.';
                    phoneHint.className = 'mt-1 text-xs text-green-600';
                } else {
                    phoneHint.textContent = 'Cliente não encontrado. Preencha os dados manualmente.';
                    phoneHint.className = 'mt-1 text-xs text-gray-500';
                }
            })
            .catch(function() {
                phoneHint.textContent = 'Digite seu telefone para preencher nome e endereço automaticamente (se já for cliente).';
                phoneHint.className = 'mt-1 text-xs text-gray-500';
            });
    });

    // ViaCEP: autocomplete de endereço ao digitar CEP (somente números, 8 dígitos)
    var cepInput = document.getElementById('cep');
    var cepError = document.getElementById('cep-error');
    cepInput.addEventListener('blur', function() {
        var cep = (this.value || '').replace(/\D/g, '');
        if (cep.length !== 8) {
            if (this.value.trim() !== '') {
                cepError.classList.remove('hidden');
            } else {
                cepError.classList.add('hidden');
                document.getElementById('address_street').value = '';
                document.getElementById('address_neighborhood').value = '';
                document.getElementById('address_city').value = '';
                document.getElementById('address_state').value = '';
            }
            return;
        }
        cepError.classList.add('hidden');
        fetch('https://viacep.com.br/ws/' + cep + '/json/')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.erro) {
                    cepError.classList.remove('hidden');
                    document.getElementById('address_street').value = '';
                    document.getElementById('address_neighborhood').value = '';
                    document.getElementById('address_city').value = '';
                    document.getElementById('address_state').value = '';
                } else {
                    document.getElementById('address_street').value = data.logradouro || '';
                    document.getElementById('address_neighborhood').value = data.bairro || '';
                    document.getElementById('address_city').value = data.localidade || '';
                    document.getElementById('address_state').value = data.uf || '';
                }
            })
            .catch(function() {
                cepError.classList.remove('hidden');
            });
    });
    // Máscara CEP: 00000-000
    cepInput.addEventListener('input', function() {
        var v = this.value.replace(/\D/g, '');
        if (v.length > 5) {
            this.value = v.slice(0, 5) + '-' + v.slice(5, 8);
        } else {
            this.value = v;
        }
    });

    // Abas por categoria: ao clicar, mostra apenas produtos da categoria
    (function() {
        var tabContainer = document.querySelector('.order-tabs');
        var cards = document.querySelectorAll('.order-product-card');
        if (!tabContainer || !cards.length) return;
        var tabs = tabContainer.querySelectorAll('.order-tab');
        function filterByCategory(categoryId) {
            cards.forEach(function(card) {
                var cardCat = card.getAttribute('data-category-id');
                var show = (categoryId === '' || categoryId === null) ? true : (String(cardCat) === String(categoryId));
                card.style.display = show ? '' : 'none';
            });
        }
        tabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                var catId = this.getAttribute('data-category-id');
                if (catId === null) catId = '';
                tabs.forEach(function(t) {
                    t.classList.remove('border-primary', 'text-primary', 'bg-white', '-mb-px');
                    t.classList.add('border-transparent', 'text-gray-500');
                    t.setAttribute('aria-selected', 'false');
                });
                this.classList.add('border-primary', 'text-primary', 'bg-white', '-mb-px');
                this.classList.remove('border-transparent', 'text-gray-500');
                this.setAttribute('aria-selected', 'true');
                filterByCategory(catId);
            });
        });
        filterByCategory('');
    })();

    function buildDeliveryAddress() {
        var street = document.getElementById('address_street').value.trim();
        var number = document.getElementById('address_number').value.trim();
        var complement = document.getElementById('address_complement').value.trim();
        var neighborhood = document.getElementById('address_neighborhood').value.trim();
        var city = document.getElementById('address_city').value.trim();
        var state = document.getElementById('address_state').value.trim();
        var cep = document.getElementById('cep').value.trim();
        var parts = [street, number, complement, neighborhood, city, state, cep].filter(Boolean);
        return parts.join(', ');
    }

    orderForm.addEventListener('submit', function(e) {
        e.preventDefault();
        if (cart.length === 0) {
            formMessage.textContent = 'Adicione ao menos um item ao pedido.';
            formMessage.className = 'mt-3 text-red-600';
            formMessage.classList.remove('hidden');
            return;
        }
        if (!document.getElementById('is_pickup').checked) {
            var cep = (document.getElementById('cep').value || '').replace(/\D/g, '');
            var num = document.getElementById('address_number').value.trim();
            if (cep.length !== 8) {
                formMessage.textContent = 'Informe um CEP válido (8 dígitos).';
                formMessage.className = 'mt-3 text-red-600';
                formMessage.classList.remove('hidden');
                return;
            }
            if (!num) {
                formMessage.textContent = 'Informe o número do endereço.';
                formMessage.className = 'mt-3 text-red-600';
                formMessage.classList.remove('hidden');
                return;
            }
            document.getElementById('delivery_address').value = buildDeliveryAddress();
        }
        formMessage.classList.add('hidden');
        submitBtn.disabled = true;
        var formData = new FormData(orderForm);
        fetch(orderForm.action, {
            method: 'POST',
            body: formData
        }).then(function(r) { return r.json(); }).then(function(data) {
            if (data.success) {
                formMessage.textContent = data.message || 'Pedido enviado com sucesso!';
                formMessage.className = 'mt-3 text-green-600';
                formMessage.classList.remove('hidden');
                cart = [];
                updateCart();
                orderForm.reset();
            } else {
                formMessage.textContent = data.message || 'Erro ao enviar. Tente novamente.';
                formMessage.className = 'mt-3 text-red-600';
                formMessage.classList.remove('hidden');
                submitBtn.disabled = false;
            }
        }).catch(function() {
            formMessage.textContent = 'Erro de conexão. Tente novamente.';
            formMessage.className = 'mt-3 text-red-600';
            formMessage.classList.remove('hidden');
            submitBtn.disabled = false;
        });
    });
})();
</script>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
