<?php

namespace App\Controllers;

use App\Models\TechnicalSheet;
use App\Models\Ingredient;
use App\Models\Product;

/**
 * Ficha Técnica: visualizar/editar itens por produto, custo total e preço sugerido.
 */
class TechnicalSheetController extends BaseController
{
    /**
     * Exibe a ficha técnica do produto (lista itens, totais, preço sugerido).
     * GET product_id=
     */
    public function view()
    {
        $productId = (int) ($_GET['product_id'] ?? 0);
        if ($productId <= 0) {
            $this->flash('error', 'Produto inválido.');
            $this->redirect('product/index');
        }

        $productModel = new Product();
        $product = $productModel->getById($productId);
        if (!$product) {
            $this->flash('error', 'Produto não encontrado.');
            $this->redirect('product/index');
        }

        $sheetModel = new TechnicalSheet();
        $sheetId = $sheetModel->getOrCreateForProduct($productId);
        if ($sheetId === false) {
            $this->flash('error', 'Erro ao criar/abrir ficha técnica.');
            $this->redirect('product/index');
        }

        $items = $sheetModel->getItems($sheetId);
        $totalCost = $sheetModel->getTotalCost($sheetId);
        $marginPercent = (float) ($product['margin_percent'] ?? 65.0);
        $suggestedPrice = calc_suggested_price($totalCost, $marginPercent);

        $sheet = $sheetModel->getById($sheetId);

        $this->render('sheets/view', [
            'product' => $product,
            'sheet' => $sheet,
            'items' => $items,
            'totalCost' => $totalCost,
            'marginPercent' => $marginPercent,
            'suggestedPrice' => $suggestedPrice,
        ]);
    }

    /**
     * Formulário para adicionar item à ficha.
     * GET product_id= & sheet_id=
     */
    public function itemForm()
    {
        $productId = (int) ($_GET['product_id'] ?? 0);
        $sheetId = (int) ($_GET['sheet_id'] ?? 0);
        if ($productId <= 0 || $sheetId <= 0) {
            $this->redirect('product/index');
        }

        $product = (new Product())->getById($productId);
        $sheet = (new TechnicalSheet())->getById($sheetId);
        if (!$product || !$sheet || (int) $sheet['product_id'] !== $productId) {
            $this->flash('error', 'Ficha técnica não encontrada.');
            $this->redirect('product/index');
        }

        $ingredientModel = new Ingredient();
        $ingredients = $ingredientModel->getAll(true);

        $this->render('sheets/item_form', [
            'product' => $product,
            'sheet' => $sheet,
            'ingredients' => $ingredients,
            'error' => null,
        ]);
    }

    /**
     * Formulário para editar item da ficha.
     * GET product_id= & sheet_id= & item_id=
     */
    public function itemEdit()
    {
        $productId = (int) ($_GET['product_id'] ?? 0);
        $sheetId = (int) ($_GET['sheet_id'] ?? 0);
        $itemId = (int) ($_GET['item_id'] ?? 0);
        if ($productId <= 0 || $sheetId <= 0 || $itemId <= 0) {
            $this->redirect('product/index');
        }

        $product = (new Product())->getById($productId);
        $sheet = (new TechnicalSheet())->getById($sheetId);
        if (!$product || !$sheet || (int) $sheet['product_id'] !== $productId) {
            $this->flash('error', 'Ficha técnica não encontrada.');
            $this->redirect('product/index');
        }

        $sheetModel = new TechnicalSheet();
        $item = $sheetModel->getItemById($itemId, $sheetId);
        if (!$item) {
            $this->flash('error', 'Item não encontrado.');
            header('Location: ' . BASE_URL . '?route=technicalSheet/view&product_id=' . $productId);
            exit;
        }

        $ingredients = (new Ingredient())->getAll(true);

        $this->render('sheets/item_form', [
            'product' => $product,
            'sheet' => $sheet,
            'ingredients' => $ingredients,
            'item' => $item,
            'error' => null,
        ]);
    }

    /**
     * POST: atualizar item da ficha.
     */
    public function itemUpdate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('product/index');
        }

        $sheetId = (int) ($_POST['sheet_id'] ?? 0);
        $productId = (int) ($_POST['product_id'] ?? 0);
        $itemId = (int) ($_POST['item_id'] ?? 0);
        if ($sheetId <= 0 || $productId <= 0 || $itemId <= 0) {
            $this->flash('error', 'Dados inválidos.');
            $this->redirect('product/index');
        }

        $sheetModel = new TechnicalSheet();
        $sheet = $sheetModel->getById($sheetId);
        if (!$sheet || (int) $sheet['product_id'] !== $productId) {
            $this->flash('error', 'Ficha técnica não encontrada.');
            $this->redirect('product/index');
        }

        $item = $sheetModel->getItemById($itemId, $sheetId);
        if (!$item) {
            $this->flash('error', 'Item não encontrado.');
            header('Location: ' . BASE_URL . '?route=technicalSheet/view&product_id=' . $productId);
            exit;
        }

        $data = [
            'ingredient_id' => $_POST['ingredient_id'] ?? 0,
            'item_classification' => trim($_POST['item_classification'] ?? ''),
            'item_qty_brut' => str_replace(',', '.', $_POST['item_qty_brut'] ?? '0'),
            'item_qty_net' => trim($_POST['item_qty_net'] ?? '') !== '' ? str_replace(',', '.', $_POST['item_qty_net']) : null,
            'item_unit' => $_POST['item_unit'] ?? 'g',
            'item_yield_percent' => trim($_POST['item_yield_percent'] ?? '') !== '' ? str_replace(',', '.', $_POST['item_yield_percent']) : null,
        ];

        $err = $this->validateItem($data);
        if (!empty($err)) {
            $this->flash('error', implode(' ', $err));
            header('Location: ' . BASE_URL . '?route=technicalSheet/itemEdit&product_id=' . $productId . '&sheet_id=' . $sheetId . '&item_id=' . $itemId);
            exit;
        }

        if ($sheetModel->updateItem($itemId, $sheetId, $data)) {
            $this->flash('success', 'Item atualizado na ficha técnica.');
        } else {
            $this->flash('error', 'Erro ao atualizar item.');
        }
        header('Location: ' . BASE_URL . '?route=technicalSheet/view&product_id=' . $productId);
        exit;
    }

    /**
     * POST: adicionar item à ficha.
     */
    public function itemAdd()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('product/index');
        }

        $sheetId = (int) ($_POST['sheet_id'] ?? 0);
        $productId = (int) ($_POST['product_id'] ?? 0);
        if ($sheetId <= 0 || $productId <= 0) {
            $this->flash('error', 'Dados inválidos.');
            $this->redirect('product/index');
        }

        $sheetModel = new TechnicalSheet();
        $sheet = $sheetModel->getById($sheetId);
        if (!$sheet || (int) $sheet['product_id'] !== $productId) {
            $this->flash('error', 'Ficha técnica não encontrada.');
            $this->redirect('product/index');
        }

        $data = [
            'ingredient_id' => $_POST['ingredient_id'] ?? 0,
            'item_classification' => trim($_POST['item_classification'] ?? ''),
            'item_qty_brut' => str_replace(',', '.', $_POST['item_qty_brut'] ?? '0'),
            'item_qty_net' => trim($_POST['item_qty_net'] ?? '') !== '' ? str_replace(',', '.', $_POST['item_qty_net']) : null,
            'item_unit' => $_POST['item_unit'] ?? 'g',
            'item_yield_percent' => trim($_POST['item_yield_percent'] ?? '') !== '' ? str_replace(',', '.', $_POST['item_yield_percent']) : null,
        ];

        $err = $this->validateItem($data);
        if (!empty($err)) {
            $this->flash('error', implode(' ', $err));
            header('Location: ' . BASE_URL . '?route=technicalSheet/itemForm&product_id=' . $productId . '&sheet_id=' . $sheetId);
            exit;
        }

        if ($sheetModel->addItem($sheetId, $data)) {
            $this->flash('success', 'Item adicionado à ficha técnica.');
        } else {
            $this->flash('error', 'Erro ao adicionar item. Verifique o insumo.');
        }
        header('Location: ' . BASE_URL . '?route=technicalSheet/view&product_id=' . $productId);
        exit;
    }

    /**
     * Remove item da ficha.
     */
    public function itemDelete()
    {
        $itemId = (int) ($_GET['item_id'] ?? 0);
        $productId = (int) ($_GET['product_id'] ?? 0);
        if ($itemId <= 0 || $productId <= 0) {
            $this->redirect('product/index');
        }

        $sheetModel = new TechnicalSheet();
        $sheet = $sheetModel->getByProductId($productId);
        if (!$sheet) {
            $this->redirect('product/index');
        }

        $sheetModel->deleteItem($itemId, (int) $sheet['id']);
        $this->flash('success', 'Item removido da ficha.');
        header('Location: ' . BASE_URL . '?route=technicalSheet/view&product_id=' . $productId);
        exit;
    }

    /**
     * Atualiza notas da ficha (AJAX ou POST).
     */
    public function updateNotes()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('product/index');
        }
        $sheetId = (int) ($_POST['sheet_id'] ?? 0);
        $productId = (int) ($_POST['product_id'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        if ($sheetId <= 0 || $productId <= 0) {
            $this->flash('error', 'Dados inválidos.');
            $this->redirect('product/index');
        }
        $sheetModel = new TechnicalSheet();
        $sheet = $sheetModel->getById($sheetId);
        if (!$sheet || (int) $sheet['product_id'] !== $productId) {
            $this->redirect('product/index');
        }
        $sheetModel->updateNotes($sheetId, $notes === '' ? null : $notes);
        $this->flash('success', 'Anotações salvas.');
        header('Location: ' . BASE_URL . '?route=technicalSheet/view&product_id=' . $productId);
        exit;
    }

    /** @return array<int, string> */
    private function validateItem(array $data): array
    {
        $err = [];
        if (empty($data['ingredient_id']) || (int) $data['ingredient_id'] <= 0) {
            $err[] = 'Selecione um insumo.';
        }
        $qty = (float) ($data['item_qty_brut'] ?? 0);
        if ($qty <= 0) {
            $err[] = 'Quantidade bruta deve ser maior que zero.';
        }
        if (!in_array($data['item_unit'] ?? '', ['g', 'kg', 'ml', 'l', 'un'], true)) {
            $err[] = 'Unidade inválida.';
        }
        return $err;
    }
}
