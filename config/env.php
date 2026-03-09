<?php
/**
 * Carrega variáveis do arquivo .env para o ambiente (putenv + $_ENV).
 * Em desenvolvimento: se existir .env.local, é carregado depois e sobrescreve .env
 * (permite manter .env com credenciais Hostinger e usar .env.local no PC).
 * Usa vlucas/phpdotenv quando disponível (Composer), senão parser simples.
 */
$root = dirname(__DIR__);
$envFile = $root . DIRECTORY_SEPARATOR . '.env';
if (!is_file($envFile)) {
    return;
}

$loadEnvFile = static function (string $file, bool $overwrite = false): void {
    if (!is_file($file)) {
        return;
    }
    if (class_exists(\Dotenv\Dotenv::class)) {
        $dir = dirname($file);
        $name = basename($file);
        $dotenv = $overwrite
            ? \Dotenv\Dotenv::createMutable($dir, $name)
            : \Dotenv\Dotenv::createImmutable($dir, $name);
        $dotenv->safeLoad();
        return;
    }
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if ($key === '') {
            continue;
        }
        $value = trim($value, '"\'');
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
};

$loadEnvFile($envFile);

// Em desenvolvimento, .env.local sobrescreve .env (não versionar .env.local)
$envLocal = $root . DIRECTORY_SEPARATOR . '.env.local';
if (is_file($envLocal)) {
    $loadEnvFile($envLocal, true);
}

/**
 * Garante $_ENV preenchido (em muitos hosts compartilhados getenv() não retorna vars do putenv).
 * Lê os arquivos .env novamente só para popular $_ENV.
 */
$fillEnv = static function (string $file): void {
    if (!is_file($file)) {
        return;
    }
    $lines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if ($key !== '') {
            $value = trim($value, '"\'');
            $_ENV[$key] = $value;
        }
    }
};
$fillEnv($envFile);
if (is_file($envLocal)) {
    $fillEnv($envLocal);
}
