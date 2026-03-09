<?php

namespace App\Controllers;

use App\Models\Ingredient;

/**
 * CRUD de Insumos (matérias-primas) para Ficha Técnica.
 */
class IngredientController extends BaseController
{
    public function index()
    {
        $model = new Ingredient();
        $ingredients = $model->getAll(false);
        $this->render('ingredients/index', [
            'ingredients' => $ingredients,
        ]);
    }

    public function create()
    {
        $ingredient = [];
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->collectPost();
            $errors = $this->validate($data);
            if (!empty($errors)) {
                $error = implode(' ', $errors);
            } else {
                $model = new Ingredient();
                if ($model->create($data)) {
                    $this->flash('success', 'Insumo cadastrado com sucesso.');
                    $this->redirect('ingredient/index');
                }
                $error = 'Erro ao cadastrar insumo.';
            }
        }

        $this->render('ingredients/form', [
            'ingredient' => $ingredient,
            'error' => $error,
            'isEdit' => false,
        ]);
    }

    public function edit()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $model = new Ingredient();
        $ingredient = $model->getById($id);
        if (!$ingredient) {
            $this->redirect('ingredient/index');
        }

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->collectPost();
            $errors = $this->validate($data);
            if (!empty($errors)) {
                $error = implode(' ', $errors);
            } else {
                if ($model->update($id, $data)) {
                    $this->flash('success', 'Insumo atualizado com sucesso.');
                    $this->redirect('ingredient/index');
                }
                $error = 'Erro ao atualizar insumo.';
            }
        }

        $this->render('ingredients/form', [
            'ingredient' => $ingredient,
            'error' => $error,
            'isEdit' => true,
        ]);
    }

    public function delete()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $model = new Ingredient();
        if ($model->isUsedInSheets($id)) {
            $this->flash('error', 'Este insumo não pode ser excluído pois está sendo usado em uma ou mais fichas técnicas. Desative-o em vez de excluir.');
            $this->redirect('ingredient/index');
        }
        if ($model->delete($id)) {
            $this->flash('success', 'Insumo removido.');
        } else {
            $this->flash('error', 'Erro ao remover insumo.');
        }
        $this->redirect('ingredient/index');
    }

    public function toggle()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $model = new Ingredient();
        $model->toggleActive($id);
        $this->flash('success', 'Status alterado.');
        $this->redirect('ingredient/index');
    }

    private function collectPost(): array
    {
        return [
            'code' => trim($_POST['code'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'unit' => $_POST['unit'] ?? 'kg',
            'cost_per_unit' => str_replace(',', '.', $_POST['cost_per_unit'] ?? '0'),
            'active' => isset($_POST['active']) ? 1 : 0,
        ];
    }

    /** @return array<int, string> */
    private function validate(array $data): array
    {
        $err = [];
        if (empty($data['name'])) {
            $err[] = 'Nome é obrigatório.';
        }
        if (!in_array($data['unit'], ['kg', 'g', 'l', 'ml', 'un'], true)) {
            $err[] = 'Unidade inválida.';
        }
        if (isset($data['cost_per_unit']) && (float) $data['cost_per_unit'] < 0) {
            $err[] = 'Custo por unidade não pode ser negativo.';
        }
        return $err;
    }
}
