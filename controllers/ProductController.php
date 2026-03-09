<?php

namespace App\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Sector;
use App\Models\Supplier;

class ProductController extends BaseController
{
    /** Valida (extensão, MIME, tamanho) e armazena upload de imagem de produto. Retorna path ou null. */
    private function validateAndStoreProductImage(): ?string
    {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxSize = 5 * 1024 * 1024; // 5 MB
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt, true) || $_FILES['image']['size'] > $maxSize) {
            return null;
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
        finfo_close($finfo);
        $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mime, $allowedMime, true)) {
            return null;
        }
        $newName = bin2hex(random_bytes(8)) . '.' . $ext;
        $dir = 'public/uploads/products';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $dest = $dir . '/' . $newName;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
            return null;
        }
        return $dest;
    }

    public function index()
    {
        $productModel = new Product();
        $categoryModel = new Category();

        $filters = [
            'name' => $_GET['name'] ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'ean' => $_GET['ean'] ?? '',
            'code' => $_GET['code'] ?? '',
            'sector_id' => $_GET['sector_id'] ?? ($_SESSION['sector_id'] ?? 1)
        ];

        $products = $productModel->getAll($filters);
        $categories = $categoryModel->getAll();
        $sectorModel = new Sector();
        $allSectors = $sectorModel->getAll();

        $this->render('products/index', [
            'products' => $products,
            'categories' => $categories,
            'allSectors' => $allSectors,
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        $productModel = new Product();
        $product = []; // Inicializa para evitar erros na view

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $imagePath = $this->validateAndStoreProductImage();

            $data = [
                'name' => $_POST['name'],
                'code' => $_POST['code'] ?? null,
                'category_id' => $_POST['category_id'],
                'brand_id' => $_POST['brand_id'] ?? null,
                'price' => str_replace(',', '.', $_POST['price']),
                'cost_price' => str_replace(',', '.', $_POST['cost_price']),
                'stock' => $_POST['stock'],
                'unit' => $_POST['unit'],
                'location' => $_POST['location'],
                'ean' => $_POST['ean'],
                'observations' => $_POST['observations'],
                'image' => $imagePath,
                'sector_id' => $_POST['sector_id'] ?? ($_SESSION['sector_id'] ?? 1),
                'is_gift_card' => $_POST['is_gift_card'] ?? 0,
                'is_consigned' => $_POST['is_consigned'] ?? 0,
                'supplier_id' => $_POST['supplier_id'] ?? null,
                'yield_target_grams' => $_POST['yield_target_grams'] ?? null,
                'margin_percent' => $_POST['margin_percent'] ?? ''
            ];

            if ($productModel->create($data)) {
                $this->flash('success', 'Produto cadastrado com sucesso.');
                $this->redirect('product/index');
            }
            $error = 'Erro ao cadastrar produto.';
        }

        $categories = $productModel->getCategories();
        $brands = $productModel->getBrands();
        $sectorModel = new Sector();
        $sectors = $sectorModel->getAll();

        $supplierModel = new Supplier();
        $suppliers = $supplierModel->getAll();

        $this->render('products/create', [
            'product' => $product,
            'categories' => $categories,
            'brands' => $brands,
            'sectors' => $sectors,
            'suppliers' => $suppliers,
            'error' => $error ?? null,
        ]);
    }

    public function edit()
    {
        $id = $_GET['id'] ?? 0;
        $productModel = new Product();
        $product = $productModel->getById($id);

        if (!$product) {
            header('Location: ' . BASE_URL . '?route=product/index');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $imagePath = $this->validateAndStoreProductImage() ?? $product['image'] ?? null;

            $data = [
                'name' => $_POST['name'],
                'code' => $_POST['code'] ?? null,
                'category_id' => $_POST['category_id'],
                'brand_id' => $_POST['brand_id'] ?? null,
                'price' => str_replace(',', '.', $_POST['price']),
                'cost_price' => str_replace(',', '.', $_POST['cost_price']),
                'stock' => $_POST['stock'],
                'unit' => $_POST['unit'],
                'location' => $_POST['location'],
                'ean' => $_POST['ean'],
                'observations' => $_POST['observations'],
                'image' => $imagePath,
                'sector_id' => $_POST['sector_id'] ?? null,
                'is_consigned' => $_POST['is_consigned'] ?? 0,
                'supplier_id' => $_POST['supplier_id'] ?? null,
                'yield_target_grams' => $_POST['yield_target_grams'] ?? null,
                'margin_percent' => $_POST['margin_percent'] ?? ''
            ];

            if ($productModel->update($id, $data)) {
                header('Location: ' . BASE_URL . '?route=product/index');
                exit;
            } else {
                $error = "Erro ao atualizar produto.";
            }
        }

        $categories = $productModel->getCategories();
        $brands = $productModel->getBrands();
        $sectorModel = new Sector();
        $sectors = $sectorModel->getAll();

        $supplierModel = new Supplier();
        $suppliers = $supplierModel->getAll();

        $batches = $productModel->getBatches($id);

        $isEdit = true;
        require 'views/products/create.php';
    }

    public function delete()
    {
        $id = $_GET['id'] ?? 0;
        $productModel = new Product();
        $productModel->delete($id);
        header('Location: ' . BASE_URL . '?route=product/index');
        exit;
    }

    public function toggle()
    {
        $id = $_GET['id'] ?? 0;
        $productModel = new Product();
        $productModel->toggleActive($id);
        header('Location: ' . BASE_URL . '?route=product/index');
        exit;
    }
}
