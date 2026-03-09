<?php
/**
 * Etiqueta genérica 15x10 cm para impressora Foguete Box (térmica).
 * Dizeres customizáveis; conteúdo será definido depois.
 */
$linha1 = isset($_GET['l1']) ? trim((string) $_GET['l1']) : '';
$linha2 = isset($_GET['l2']) ? trim((string) $_GET['l2']) : '';
$linha3 = isset($_GET['l3']) ? trim((string) $_GET['l3']) : '';
$linha4 = isset($_GET['l4']) ? trim((string) $_GET['l4']) : '';
$linha5 = isset($_GET['l5']) ? trim((string) $_GET['l5']) : '';
?>
<?php require 'views/layouts/header.php'; ?>

<div class="w-full max-w-full flex flex-col gap-6 px-4 sm:px-6 lg:px-8 mx-auto">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">🏷️ Etiqueta 15x10 (Foguete Box)</h2>
    </div>

    <div class="card-standard max-w-2xl">
        <div class="card-standard-header"><i class="fas fa-edit"></i> Dizeres da etiqueta</div>
        <div class="card-standard-body">
            <p class="text-sm text-gray-600 mb-4">Preencha as linhas abaixo. Ao imprimir, selecione a impressora <strong>Foguete Box</strong> e use etiquetas 15cm x 10cm.</p>
            <form method="GET" action="" id="form-etiqueta" class="space-y-3">
                <input type="hidden" name="route" value="label/index">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Linha 1</label>
                    <input type="text" name="l1" value="<?php echo htmlspecialchars($linha1, ENT_QUOTES, 'UTF-8'); ?>"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary p-2 border"
                        placeholder="Ex: Nome ou título">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Linha 2</label>
                    <input type="text" name="l2" value="<?php echo htmlspecialchars($linha2, ENT_QUOTES, 'UTF-8'); ?>"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary p-2 border"
                        placeholder="Ex: Detalhe ou subtítulo">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Linha 3</label>
                    <input type="text" name="l3" value="<?php echo htmlspecialchars($linha3, ENT_QUOTES, 'UTF-8'); ?>"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary p-2 border"
                        placeholder="Opcional">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Linha 4</label>
                    <input type="text" name="l4" value="<?php echo htmlspecialchars($linha4, ENT_QUOTES, 'UTF-8'); ?>"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary p-2 border"
                        placeholder="Opcional">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Linha 5</label>
                    <input type="text" name="l5" value="<?php echo htmlspecialchars($linha5, ENT_QUOTES, 'UTF-8'); ?>"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary p-2 border"
                        placeholder="Opcional">
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="submit" class="btn bg-indigo-600 hover:bg-indigo-700 text-white border-none rounded-xl shadow-md font-bold">
                        <i class="fas fa-eye"></i> Atualizar preview
                    </button>
                    <button type="button" id="btn-print-label" class="btn bg-emerald-600 hover:bg-emerald-700 text-white border-none rounded-xl shadow-md font-bold">
                        <i class="fas fa-print"></i> Imprimir na Foguete Box
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Preview da etiqueta 15cm x 10cm (150mm x 100mm) -->
    <div class="card-standard max-w-2xl">
        <div class="card-standard-header"><i class="fas fa-tag"></i> Preview (15 x 10 cm)</div>
        <div class="card-standard-body flex justify-center p-6 bg-gray-100">
            <div id="etiqueta-15x10" class="etiqueta-box bg-white border-2 border-dashed border-gray-300 overflow-hidden"
                style="width: 150mm; height: 100mm; max-width: 100%; box-sizing: border-box;">
                <div class="etiqueta-inner p-4 h-full flex flex-col justify-center text-center gap-1" style="font-size: clamp(10px, 2.5vw, 18px);">
                    <?php if ($linha1 !== ''): ?>
                        <div class="font-bold"><?php echo htmlspecialchars($linha1, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>
                    <?php if ($linha2 !== ''): ?>
                        <div><?php echo htmlspecialchars($linha2, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>
                    <?php if ($linha3 !== ''): ?>
                        <div class="text-sm"><?php echo htmlspecialchars($linha3, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>
                    <?php if ($linha4 !== ''): ?>
                        <div class="text-sm"><?php echo htmlspecialchars($linha4, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>
                    <?php if ($linha5 !== ''): ?>
                        <div class="text-sm"><?php echo htmlspecialchars($linha5, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>
                    <?php if ($linha1 === '' && $linha2 === '' && $linha3 === '' && $linha4 === '' && $linha5 === ''): ?>
                        <div class="text-gray-400 italic">Preencha os dizeres acima e clique em Atualizar preview.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Área só para impressão: uma página = uma etiqueta 15x10 -->
    <div id="print-area" class="hidden">
        <div class="etiqueta-print-page">
            <div class="etiqueta-print-inner">
                <?php if ($linha1 !== ''): ?>
                    <div class="ep-line ep-line1"><?php echo htmlspecialchars($linha1, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <?php if ($linha2 !== ''): ?>
                    <div class="ep-line ep-line2"><?php echo htmlspecialchars($linha2, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <?php if ($linha3 !== ''): ?>
                    <div class="ep-line ep-line3"><?php echo htmlspecialchars($linha3, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <?php if ($linha4 !== ''): ?>
                    <div class="ep-line ep-line4"><?php echo htmlspecialchars($linha4, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <?php if ($linha5 !== ''): ?>
                    <div class="ep-line ep-line5"><?php echo htmlspecialchars($linha5, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* Impressão: uma página = uma etiqueta 15cm x 10cm (Foguete Box) */
@media print {
    body * { visibility: hidden; }
    #print-area { display: block !important; visibility: visible !important; position: fixed; left: 0; top: 0; width: 100%; height: 100%; margin: 0; padding: 0; }
    #print-area, #print-area * { visibility: visible; }
    .etiqueta-print-page {
        width: 150mm;
        height: 100mm;
        margin: 0;
        padding: 8mm;
        box-sizing: border-box;
        display: flex;
        align-items: center;
        justify-content: center;
        page-break-after: always;
    }
    .etiqueta-print-inner {
        text-align: center;
        width: 100%;
        font-family: Arial, sans-serif;
    }
    .ep-line { margin: 2mm 0; }
    .ep-line1 { font-size: 14pt; font-weight: bold; }
    .ep-line2 { font-size: 12pt; }
    .ep-line3, .ep-line4, .ep-line5 { font-size: 10pt; }
    @page { size: 150mm 100mm; margin: 0; }
}
</style>

<script>
document.getElementById('btn-print-label').addEventListener('click', function() {
    var hasContent = document.querySelector('.etiqueta-print-inner .ep-line');
    if (!hasContent) {
        alert('Preencha pelo menos uma linha e clique em "Atualizar preview" antes de imprimir.');
        return;
    }
    window.print();
});
</script>

<?php require 'views/layouts/footer.php'; ?>
