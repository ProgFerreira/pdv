<?php
// Página pública: pedido pelo link. Layout simples sem sidebar.
require dirname(__DIR__) . '/layouts/header.php';
?>
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Fazer pedido</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Produtos</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <?php foreach ($products as $p): ?>
                <div class="border border-gray-200 rounded-lg p-3 flex items-center justify-between gap-2 bg-white">
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
                    <label for="guest_name" class="block text-sm font-medium text-gray-700">Nome *</label>
                    <input type="text" id="guest_name" name="guest_name" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-primary focus:ring-primary">
                </div>
                <div>
                    <label for="guest_phone" class="block text-sm font-medium text-gray-700">Telefone *</label>
                    <input type="text" id="guest_phone" name="guest_phone" required placeholder="(00) 00000-0000" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-primary focus:ring-primary">
                </div>
                <div>
                    <label for="guest_email" class="block text-sm font-medium text-gray-700">E-mail</label>
                    <input type="email" id="guest_email" name="guest_email" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-primary focus:ring-primary">
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_pickup" name="is_pickup" value="1" class="rounded border-gray-300 text-primary focus:ring-primary">
                    <label for="is_pickup" class="text-sm font-medium text-gray-700">Retirada no local</label>
                </div>
                <div id="delivery-field">
                    <label for="delivery_address" class="block text-sm font-medium text-gray-700">Endereço de entrega</label>
                    <textarea id="delivery_address" name="delivery_address" rows="2" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-primary focus:ring-primary" placeholder="Rua, número, bairro, cidade..."></textarea>
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

    orderForm.addEventListener('submit', function(e) {
        e.preventDefault();
        if (cart.length === 0) {
            formMessage.textContent = 'Adicione ao menos um item ao pedido.';
            formMessage.className = 'mt-3 text-red-600';
            formMessage.classList.remove('hidden');
            return;
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
