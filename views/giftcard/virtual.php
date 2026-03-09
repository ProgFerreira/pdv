<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sua Vale Presente - Loja Religiosa</title>
    <link href="<?php echo defined('BASE_URL') ? htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') : ''; ?>public/css/tailwind.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- html2canvas for image generation -->
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .gift-card-gradient {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .animate-float { animation: float 3s ease-in-out infinite; }
        
        /* Ensure no border-radius artifacts in the capture */
        #capture { border-radius: 24px; overflow: hidden; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col items-center justify-center p-4">

    <div class="max-w-md w-full">
        <!-- Capture Area (The actual Image) -->
        <div id="capture" class="gift-card-gradient p-8 shadow-2xl relative overflow-hidden text-white border-4 border-white/20 aspect-[1.6/1]">
            <!-- Decorative circle -->
            <div class="absolute -top-12 -right-12 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-12 -left-12 w-48 h-48 bg-black/10 rounded-full blur-2xl"></div>

            <div class="relative z-10">
                <div class="flex justify-between items-start mb-12">
                    <div>
                        <p class="text-white/70 text-sm font-medium uppercase tracking-widest mb-1">Presente Especial</p>
                        <h1 class="text-3xl font-bold italic">Vale Presente</h1>
                    </div>
                    <div class="bg-white/20 p-3 rounded-2xl border border-white/30">
                        <i class="fas fa-cross text-2xl"></i>
                    </div>
                </div>

                <div class="mb-12">
                    <p class="text-white/60 text-xs uppercase tracking-tighter mb-2">Saldo Disponível</p>
                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl font-light opacity-80">R$</span>
                        <span class="text-5xl font-bold tracking-tight">
                            <?php echo number_format($card['balance'], 2, ',', '.'); ?>
                        </span>
                    </div>
                </div>

                <div class="glass-effect rounded-2xl p-4 flex justify-between items-center">
                    <div>
                        <p class="text-white/50 text-[10px] uppercase font-bold tracking-widest mb-1">Código do Vale</p>
                        <p class="text-xl font-mono tracking-[0.2em] font-bold">
                            <?php echo htmlspecialchars($card['code']); ?>
                        </p>
                    </div>
                    <div class="opacity-50 text-[10px] text-right">
                        LOJA<br>RELIGIOSA
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-8 grid grid-cols-2 gap-4 no-print">
            <button onclick="shareAsImage()" id="shareBtn" class="flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg transition-transform active:scale-95">
                <i class="fab fa-whatsapp"></i> Mandar Imagem
            </button>
            <button onclick="downloadImage()" class="flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover text-white font-bold py-3 px-4 rounded-xl shadow-lg transition-transform active:scale-95">
                <i class="fas fa-download"></i> Baixar Cartão
            </button>
        </div>

        <!-- Instructions -->
        <div class="mt-8 bg-white rounded-2xl p-6 shadow-sm border border-gray-100 no-print">
            <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-info-circle text-primary"></i>
                Instruções:
            </h3>
            <ul class="space-y-3 text-sm text-gray-600">
                <li class="flex gap-3">
                    <span class="bg-primary/10 text-primary w-5 h-5 rounded-full flex items-center justify-center font-bold text-[10px]">1</span>
                    Apresente a imagem acima em nossa loja.
                </li>
                <li class="flex gap-3">
                    <span class="bg-primary/10 text-primary w-5 h-5 rounded-full flex items-center justify-center font-bold text-[10px]">2</span>
                    Válido para produtos de todos os setores.
                </li>
            </ul>
        </div>
    </div>

    <script>
        async function generateBlob() {
            const capture = document.getElementById('capture');
            // Desativar animação flutuante temporariamente para o print sair certo
            capture.style.animation = 'none';
            const canvas = await html2canvas(capture, {
                backgroundColor: null,
                scale: 3, // Alta definição
                logging: false,
                useCORS: true
            });
            capture.style.animation = 'float 3s ease-in-out infinite';
            
            return new Promise(resolve => canvas.toBlob(resolve, 'image/png'));
        }

        async function downloadImage() {
            const blob = await generateBlob();
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'Vale_Presente_LojaReligiosa.png';
            a.click();
            URL.revokeObjectURL(url);
        }

        async function shareAsImage() {
            const shareBtn = document.getElementById('shareBtn');
            const originalText = shareBtn.innerHTML;
            shareBtn.disabled = true;
            shareBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Gerando...';

            try {
                const blob = await generateBlob();
                const file = new File([blob], 'vale_presente.png', { type: 'image/png' });
                
                if (navigator.share && navigator.canShare({ files: [file] })) {
                    await navigator.share({
                        files: [file],
                        title: 'Meu Vale Presente',
                        text: 'Olha sÃ³ o Vale Presente que eu recebi! 🎁'
                    });
                } else {
                    // Fallback para download se o navegador nÃ£o suportar share de arquivo
                    downloadImage();
                    alert('Seu navegador nÃ£o suporta o compartilhamento direto de imagem. O download foi iniciado para que vocÃª anexe manualmente. 😉');
                }
            } catch (err) {
                console.error(err);
                alert('Erro ao gerar imagem.');
            } finally {
                shareBtn.disabled = false;
                shareBtn.innerHTML = originalText;
            }
        }
    </script>
</body>
</html>