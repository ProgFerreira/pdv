<?php

namespace App\Controllers;

/**
 * Etiqueta genérica 15x10 cm para impressora Foguete Box.
 * Não está vinculada a produto nem venda; os dizeres são definidos pelo usuário.
 */
class LabelController extends BaseController
{
    public function index(): void
    {
        $this->render('labels/index');
    }
}
