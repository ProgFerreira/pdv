<?php
$pageTitle = 'Galeteria - Acesso';
require 'views/layouts/header.php';

// Foto da galeteria: exibir se existir em public/assets/ (logo-galeteria.jpg ou logo-galeteria.png)
$assetsDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets';
$logoJpg = $assetsDir . DIRECTORY_SEPARATOR . 'logo-galeteria.jpg';
$logoPng = $assetsDir . DIRECTORY_SEPARATOR . 'logo-galeteria.png';
$logoUrl = null;
if (is_file($logoJpg)) {
    $logoUrl = (defined('BASE_URL') ? BASE_URL : '') . 'public/assets/logo-galeteria.jpg';
} elseif (is_file($logoPng)) {
    $logoUrl = (defined('BASE_URL') ? BASE_URL : '') . 'public/assets/logo-galeteria.png';
}
?>

<div class="min-h-[80vh] flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <?php if ($logoUrl): ?>
            <img src="<?php echo htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Galeteria" class="mx-auto rounded-xl shadow-lg object-cover w-32 h-32 sm:w-40 sm:h-40 border-2 border-gray-200">
        <?php else: ?>
            <i class="fas fa-store text-6xl text-primary mb-4"></i>
        <?php endif; ?>
        <h1 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Galeteria
        </h1>
        <p class="mt-2 text-center text-sm text-gray-600">
            Acesso ao Sistema
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <?php if(isset($error)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4" role="alert">
                    <p class="text-sm text-red-700"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            <?php endif; ?>

            <form class="space-y-6" action="<?php echo htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8'); ?>?route=auth/login" method="POST">
                <?php echo csrf_field(); ?>
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700"> Usuário </label>
                    <div class="mt-1">
                        <input id="username" name="username" type="text" required class="w-full rounded-md border border-gray-300 p-2 shadow-sm placeholder-gray-400 focus:outline-none focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 sm:text-sm">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700"> Senha </label>
                    <div class="mt-1">
                        <input id="password" name="password" type="password" required class="w-full rounded-md border border-gray-300 p-2 shadow-sm placeholder-gray-400 focus:outline-none focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 sm:text-sm">
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition duration-150">
                        Entrar <i class="fas fa-sign-in-alt ml-2 mt-1"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>
