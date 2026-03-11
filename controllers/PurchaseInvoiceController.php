<?php

namespace App\Controllers;

use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use App\Models\User;

/**
 * Notas Fiscais de Compras: CRUD com upload de imagem ou PDF.
 * Campos: fornecedor, telefone, data, arquivo, status, valor, data pagamento, pago por quem.
 */
class PurchaseInvoiceController
{
    private const UPLOAD_DIR = 'public/uploads/notas_fiscais';
    private const ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
    private const ALLOWED_MIME = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf'
    ];
    private const MAX_SIZE = 10 * 1024 * 1024; // 10 MB

    private PurchaseInvoice $model;

    public function __construct()
    {
        $this->model = new PurchaseInvoice();
    }

    /**
     * Valida e armazena upload de imagem ou PDF. Retorna ['path' => ..., 'original_name' => ...] ou null.
     */
    private function handleUpload(string $inputName): ?array
    {
        if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $file = $_FILES[$inputName];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXT, true) || $file['size'] > self::MAX_SIZE) {
            return null;
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            return null;
        }
        if (!is_dir(self::UPLOAD_DIR)) {
            @mkdir(self::UPLOAD_DIR, 0755, true);
        }
        $safeName = bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = self::UPLOAD_DIR . '/' . $safeName;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return null;
        }
        return ['path' => $dest, 'original_name' => $file['name']];
    }

    private function parsePostData(bool $isUpdate = false): array
    {
        $valor = str_replace(',', '.', (string) ($_POST['valor'] ?? '0'));
        $data = [
            'supplier_id' => !empty($_POST['supplier_id']) ? (int) $_POST['supplier_id'] : null,
            'fornecedor_nome' => trim($_POST['fornecedor_nome'] ?? ''),
            'telefone' => trim($_POST['telefone'] ?? '') ?: null,
            'data_emissao' => $_POST['data_emissao'] ?? date('Y-m-d'),
            'status' => $_POST['status'] ?? 'PENDENTE',
            'valor' => (float) $valor,
            'data_pagamento' => !empty($_POST['data_pagamento']) ? $_POST['data_pagamento'] : null,
            'pago_por_user_id' => !empty($_POST['pago_por_user_id']) ? (int) $_POST['pago_por_user_id'] : null,
            'observacoes' => trim($_POST['observacoes'] ?? '') ?: null,
        ];
        if (!$isUpdate) {
            $upload = $this->handleUpload('arquivo');
            $data['arquivo_path'] = $upload['path'] ?? null;
            $data['arquivo_nome_original'] = $upload['original_name'] ?? null;
        }
        return $data;
    }

    public function index(): void
    {
        $filters = [
            'status' => $_GET['status'] ?? null,
            'supplier_id' => $_GET['supplier_id'] ?? null,
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null,
        ];
        $items = $this->model->getAll($filters);
        $suppliers = (new Supplier())->getAll();
        require 'views/purchase_invoice/index.php';
    }

    public function create(): void
    {
        $suppliers = (new Supplier())->getAll();
        $users = (new User())->getAll();
        require 'views/purchase_invoice/form.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?route=purchase_invoice/index');
            exit;
        }
        $data = $this->parsePostData(false);
        if (empty($data['fornecedor_nome'])) {
            header('Location: ' . BASE_URL . '?route=purchase_invoice/create&error=fornecedor');
            exit;
        }
        try {
            $id = $this->model->create($data);
            header('Location: ' . BASE_URL . '?route=purchase_invoice/show&id=' . $id . '&success=created');
        } catch (\Throwable $e) {
            header('Location: ' . BASE_URL . '?route=purchase_invoice/create&error=1');
        }
        exit;
    }

    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $item = $this->model->getById($id);
        if (!$item) {
            header('Location: ' . BASE_URL . '?route=purchase_invoice/index');
            exit;
        }
        require 'views/purchase_invoice/show.php';
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $item = $this->model->getById($id);
        if (!$item) {
            header('Location: ' . BASE_URL . '?route=purchase_invoice/index');
            exit;
        }
        $suppliers = (new Supplier())->getAll();
        $users = (new User())->getAll();
        require 'views/purchase_invoice/form.php';
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?route=purchase_invoice/index');
            exit;
        }
        $id = (int) ($_POST['id'] ?? 0);
        $item = $this->model->getById($id);
        if (!$item) {
            header('Location: ' . BASE_URL . '?route=purchase_invoice/index');
            exit;
        }
        $data = $this->parsePostData(true);
        $data['data_emissao'] = $_POST['data_emissao'] ?? $item['data_emissao'];
        $data['fornecedor_nome'] = trim($_POST['fornecedor_nome'] ?? '') ?: $item['fornecedor_nome'];
        if (empty($data['fornecedor_nome'])) {
            header('Location: ' . BASE_URL . '?route=purchase_invoice/edit&id=' . $id . '&error=fornecedor');
            exit;
        }
        $upload = $this->handleUpload('arquivo');
        if ($upload) {
            $data['arquivo_path'] = $upload['path'];
            $data['arquivo_nome_original'] = $upload['original_name'];
        }
        try {
            $this->model->update($id, $data);
            header('Location: ' . BASE_URL . '?route=purchase_invoice/show&id=' . $id . '&success=updated');
        } catch (\Throwable $e) {
            header('Location: ' . BASE_URL . '?route=purchase_invoice/edit&id=' . $id . '&error=1');
        }
        exit;
    }

    public function delete(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $this->model->delete($id);
        header('Location: ' . BASE_URL . '?route=purchase_invoice/index&success=deleted');
        exit;
    }

    /** Download ou exibição do arquivo (imagem/PDF) */
    public function download(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $item = $this->model->getById($id);
        if (!$item || empty($item['arquivo_path']) || !is_file($item['arquivo_path'])) {
            http_response_code(404);
            echo 'Arquivo não encontrado.';
            return;
        }
        $path = $item['arquivo_path'];
        $name = $item['arquivo_nome_original'] ?? basename($path);
        $mime = mime_content_type($path) ?: 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . str_replace('"', '\\"', $name) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
}
