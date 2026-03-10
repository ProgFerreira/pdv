<?php require 'views/layouts/header.php'; ?>

<div class="w-[95vw] max-w-full mx-auto">
    <div class="card-standard overflow-hidden">
        <div class="card-standard-header">
            <i class="fas fa-user-plus"></i>
            <?php echo $isEdit ? 'Editar Cliente' : 'Novo Cliente'; ?>
        </div>
        <div class="card-standard-body">
            <form method="POST" class="space-y-6">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div class="lg:col-span-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo <span class="text-red-500">*</span></label>
                        <input type="text" name="name" class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50" required value="<?php echo htmlspecialchars($customer['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telefone / WhatsApp</label>
                        <input type="text" name="phone" class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50" value="<?php echo htmlspecialchars($customer['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50" value="<?php echo htmlspecialchars($customer['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <!-- Endereço a partir do CEP -->
                    <div class="lg:col-span-6">
                        <p class="text-sm text-gray-600 mb-2">Preencha o CEP para buscar o endereço automaticamente.</p>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                        <input type="text" id="cep" name="cep" maxlength="9" placeholder="00000-000" class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50" value="<?php echo htmlspecialchars($customer['cep'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" autocomplete="postal-code">
                        <p id="cep-error" class="mt-1 text-sm text-red-600 hidden">CEP não encontrado. Verifique e tente novamente.</p>
                    </div>
                    <div class="lg:col-span-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Logradouro</label>
                        <input type="text" id="address_street" name="address_street" class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50" value="<?php echo htmlspecialchars($customer['address_street'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                        <input type="text" name="address_number" class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50" placeholder="Nº" value="<?php echo htmlspecialchars($customer['address_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="lg:col-span-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
                        <input type="text" name="address_complement" class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50" placeholder="Apto, bloco..." value="<?php echo htmlspecialchars($customer['address_complement'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>
                        <input type="text" id="address_neighborhood" name="address_neighborhood" class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50" value="<?php echo htmlspecialchars($customer['address_neighborhood'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                        <input type="text" id="address_city" name="address_city" class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50" value="<?php echo htmlspecialchars($customer['address_city'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">UF</label>
                        <input type="text" id="address_state" name="address_state" maxlength="2" class="w-full border border-gray-300 p-2 focus:border-primary focus:ring-1 focus:ring-primary focus:ring-opacity-50 uppercase" placeholder="UF" value="<?php echo htmlspecialchars($customer['address_state'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100">
                    <a href="?route=customer/index" class="text-gray-600 hover:text-gray-900 font-medium px-4 py-2 border border-gray-300 hover:bg-gray-100 transition">Cancelar</a>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 shadow transition flex items-center gap-2">
                        <i class="fas fa-save"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    var cepInput = document.getElementById('cep');
    var cepError = document.getElementById('cep-error');
    if (!cepInput) return;
    cepInput.addEventListener('blur', function() {
        var cep = (this.value || '').replace(/\D/g, '');
        if (cep.length !== 8) {
            if (this.value.trim() !== '') {
                cepError.classList.remove('hidden');
            } else {
                cepError.classList.add('hidden');
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
                    document.getElementById('address_state').value = (data.uf || '').toUpperCase();
                }
            })
            .catch(function() {
                cepError.classList.remove('hidden');
            });
    });
    cepInput.addEventListener('input', function() {
        var v = this.value.replace(/\D/g, '');
        if (v.length > 5) {
            this.value = v.slice(0, 5) + '-' + v.slice(5, 8);
        } else {
            this.value = v;
        }
    });
})();
</script>

<?php require 'views/layouts/footer.php'; ?>
