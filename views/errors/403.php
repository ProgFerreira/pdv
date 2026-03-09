<?php
$title = 'Acesso negado';
$message = 'Você não tem permissão para acessar esta página.';
if (!defined('BASE_URL')) {
    define('BASE_URL', '/');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - <?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></title>
    <link href="<?php echo htmlspecialchars(BASE_URL ?? '/', ENT_QUOTES, 'UTF-8'); ?>public/css/tailwind.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg p-8 max-w-md w-full text-center">
        <div class="text-6xl text-amber-500 mb-4"><i class="fas fa-lock"></i></div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">403 - Acesso negado</h1>
        <p class="text-gray-600 mb-6"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
        <a href="<?php echo htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8'); ?>?route=dashboard/index" class="inline-block bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Ir para o início</a>
    </div>
</body>
</html>
