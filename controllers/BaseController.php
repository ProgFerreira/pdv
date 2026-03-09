<?php

namespace App\Controllers;

/**
 * BaseController: render, redirect e flash para padronizar respostas.
 */
abstract class BaseController
{
    /** Caminho base das views (raiz do projeto) */
    private static function viewsPath(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;
    }

    /**
     * Renderiza uma view com dados.
     * @param string $view Nome da view sem extensão (ex: 'dashboard/index', 'auth/login')
     * @param array<string, mixed> $data Variáveis a extrair na view
     */
    protected function render(string $view, array $data = []): void
    {
        $path = self::viewsPath() . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';
        if (!is_file($path)) {
            throw new \RuntimeException('View não encontrada: ' . $view);
        }
        extract($data, EXTR_SKIP);
        require $path;
    }

    /**
     * Redireciona para uma URL ou rota.
     * @param string $url URL completa ou rota (ex: 'dashboard/index' vira ?route=dashboard/index)
     */
    protected function redirect(string $url): never
    {
        if (strpos($url, 'http') === 0 || strpos($url, '/') === 0) {
            header('Location: ' . $url);
        } else {
            $base = defined('BASE_URL') ? BASE_URL : '/';
            header('Location: ' . $base . '?route=' . $url);
        }
        exit;
    }

    /**
     * Define mensagem flash (sucesso, erro, aviso).
     */
    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }
}
