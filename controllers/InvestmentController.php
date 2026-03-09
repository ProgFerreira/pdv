<?php

namespace App\Controllers;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\InvestmentLoanPayment;
use App\Models\InvestmentAsset;

class InvestmentController
{
    private $model;
    private $participantModel;
    private $loanPaymentModel;
    private $assetModel;

    public function __construct()
    {
        $this->model = new Investment();
        $this->participantModel = new InvestmentParticipant();
        $this->loanPaymentModel = new InvestmentLoanPayment();
        $this->assetModel = new InvestmentAsset();
    }

    /**
     * Página principal: abas Financeiro, Bens, Participação, Relatórios + KPIs.
     */
    public function index()
    {
        $tab = $_GET['tab'] ?? 'financeiro';
        $kpis = $this->model->getKpis();

        $filters = [
            'start_date'      => $_GET['start_date'] ?? '',
            'end_date'        => $_GET['end_date'] ?? '',
            'tipo'            => $_GET['tipo'] ?? '',
            'estado'          => $_GET['estado'] ?? '',
            'pessoa'          => $_GET['pessoa'] ?? '',
            'categoria_ativo' => $_GET['categoria_ativo'] ?? '',
            'status'          => $_GET['status'] ?? '',
            'forma_pagamento' => $_GET['forma_pagamento'] ?? '',
        ];

        $investments = $this->model->getAll($filters);
        foreach ($investments as $k => $inv) {
            if (($inv['tipo'] ?? '') === 'emprestimo') {
                $investments[$k]['saldo_devedor'] = $this->model->getSaldoDevedor((int) $inv['id']);
            } else {
                $investments[$k]['saldo_devedor'] = null;
            }
        }
        $pessoas = $this->model->getPessoasDistinct();
        $categorias = $this->model->getCategoriasDistinct();
        $participants = $this->participantModel->listForSelect();

        $assetFilters = [
            'categoria'       => $_GET['asset_categoria'] ?? '',
            'origem'         => $_GET['asset_origem'] ?? '',
            'responsavel_id' => $_GET['asset_responsavel'] ?? '',
        ];
        $assets = $this->assetModel->getAll($assetFilters);
        $assetTotals = $this->assetModel->getTotals();
        $assetCategorias = $this->assetModel->getCategoriasDistinct();

        $participacao = $this->model->getParticipacaoSocietaria();
        $participantsList = $this->participantModel->getAll();

        $totals = $this->model->getTotals($filters);

        require 'views/investment/index.php';
    }

    public function create()
    {
        $pessoas = $this->model->getPessoasDistinct();
        $participants = $this->participantModel->listForSelect();
        require 'views/investment/create.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?route=investment/index');
            exit;
        }
        $data = $this->buildInvestmentDataFromPost();
        $tiposValidos = ['aporte', 'emprestimo', 'doacao', 'compra', 'aporte_socio', 'investimento_dinheiro'];
        if (!in_array($data['tipo'], $tiposValidos, true)) {
            $data['tipo'] = 'aporte';
        }
        $estadosValidos = ['novo', 'usado'];
        if ($data['estado'] !== null && !in_array($data['estado'], $estadosValidos, true)) {
            $data['estado'] = null;
        }
        $qty = (int) ($data['quantidade'] ?? 1);
        $data['quantidade'] = $qty < 1 ? 1 : $qty;
        if (isset($_SESSION['user_id'])) {
            $data['created_by'] = (int) $_SESSION['user_id'];
        }
        if ($this->model->create($data)) {
            header('Location: ' . BASE_URL . '?route=investment/index&tab=financeiro&success=1');
        } else {
            header('Location: ' . BASE_URL . '?route=investment/index&tab=financeiro&error=1');
        }
        exit;
    }

    public function edit()
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . '?route=investment/index&error=1');
            exit;
        }
        $investment = $this->model->getById($id);
        if (!$investment) {
            header('Location: ' . BASE_URL . '?route=investment/index&error=1');
            exit;
        }
        $pessoas = $this->model->getPessoasDistinct();
        $participants = $this->participantModel->listForSelect();
        require 'views/investment/edit.php';
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?route=investment/index');
            exit;
        }
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . '?route=investment/index&error=1');
            exit;
        }
        $data = $this->buildInvestmentDataFromPost();
        $tiposValidos = ['aporte', 'emprestimo', 'doacao', 'compra', 'aporte_socio', 'investimento_dinheiro'];
        if (!in_array($data['tipo'], $tiposValidos, true)) {
            $data['tipo'] = 'aporte';
        }
        $estadosValidos = ['novo', 'usado'];
        if ($data['estado'] !== null && !in_array($data['estado'], $estadosValidos, true)) {
            $data['estado'] = null;
        }
        $qty = (int) ($data['quantidade'] ?? 1);
        $data['quantidade'] = $qty < 1 ? 1 : $qty;
        if (isset($_SESSION['user_id'])) {
            $data['updated_by'] = (int) $_SESSION['user_id'];
        }
        if ($this->model->update($id, $data)) {
            header('Location: ' . BASE_URL . '?route=investment/index&tab=financeiro&success=updated');
        } else {
            header('Location: ' . BASE_URL . '?route=investment/index&tab=financeiro&error=1');
        }
        exit;
    }

    public function delete()
    {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id > 0 && $this->model->delete($id)) {
            header('Location: ' . BASE_URL . '?route=investment/index&tab=financeiro&success=deleted');
        } else {
            header('Location: ' . BASE_URL . '?route=investment/index&tab=financeiro&error=1');
        }
        exit;
    }

    /** Detalhe do empréstimo: resumo + pagamentos + form registrar pagamento */
    public function show()
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . '?route=investment/index&tab=financeiro&error=1');
            exit;
        }
        $investment = $this->model->getById($id);
        if (!$investment || ($investment['tipo'] ?? '') !== 'emprestimo') {
            header('Location: ' . BASE_URL . '?route=investment/index&tab=financeiro&error=1');
            exit;
        }
        $payments = $this->loanPaymentModel->getByInvestimentoId($id);
        $totalPago = $this->loanPaymentModel->getTotalPago($id);
        $saldoDevedor = $this->model->getSaldoDevedor($id);
        require 'views/investment/show_loan.php';
    }

    public function paymentStore()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?route=investment/index');
            exit;
        }
        $investimentoId = (int) ($_POST['investimento_id'] ?? 0);
        if ($investimentoId <= 0) {
            header('Location: ' . BASE_URL . '?route=investment/index&tab=financeiro&error=1');
            exit;
        }
        $data = [
            'investimento_id'  => $investimentoId,
            'data_pagamento'   => $_POST['data_pagamento'] ?? date('Y-m-d'),
            'valor_pago'       => (float) str_replace([',', ' '], ['.', ''], $_POST['valor_pago'] ?? 0),
            'forma_pagamento'  => trim($_POST['forma_pagamento'] ?? '') ?: null,
            'comprovante'      => trim($_POST['comprovante'] ?? '') ?: null,
            'observacao'       => trim($_POST['observacao'] ?? '') ?: null,
            'created_by'       => isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null,
        ];
        if ($data['valor_pago'] <= 0) {
            header('Location: ' . BASE_URL . '?route=investment/show&id=' . $investimentoId . '&error=valor');
            exit;
        }
        if ($this->loanPaymentModel->create($data)) {
            header('Location: ' . BASE_URL . '?route=investment/show&id=' . $investimentoId . '&success=payment');
        } else {
            header('Location: ' . BASE_URL . '?route=investment/show&id=' . $investimentoId . '&error=1');
        }
        exit;
    }

    // ---- Participantes ----
    public function participants()
    {
        $participants = $this->participantModel->getAll();
        $kpis = $this->model->getKpis();
        require 'views/investment/participants.php';
    }

    public function participantCreate()
    {
        require 'views/investment/participant_form.php';
    }

    public function participantStore()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?route=investment/participants');
            exit;
        }
        $data = [
            'name'    => trim($_POST['name'] ?? ''),
            'contact' => trim($_POST['contact'] ?? '') ?: null,
            'document'=> trim($_POST['document'] ?? '') ?: null,
        ];
        if ($data['name'] === '') {
            header('Location: ' . BASE_URL . '?route=investment/participantCreate&error=name');
            exit;
        }
        if ($this->participantModel->create($data)) {
            header('Location: ' . BASE_URL . '?route=investment/index&tab=participacao&success=participant');
        } else {
            header('Location: ' . BASE_URL . '?route=investment/participantCreate&error=1');
        }
        exit;
    }

    public function participantEdit()
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . '?route=investment/index&tab=participantes');
            exit;
        }
        $participant = $this->participantModel->getById($id);
        if (!$participant) {
            header('Location: ' . BASE_URL . '?route=investment/index&tab=participantes&error=1');
            exit;
        }
        require 'views/investment/participant_form.php';
    }

    public function participantUpdate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?route=investment/participants');
            exit;
        }
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . '?route=investment/index&tab=participantes');
            exit;
        }
        $data = [
            'name'    => trim($_POST['name'] ?? ''),
            'contact' => trim($_POST['contact'] ?? '') ?: null,
            'document'=> trim($_POST['document'] ?? '') ?: null,
        ];
        if ($data['name'] === '') {
            header('Location: ' . BASE_URL . '?route=investment/participantEdit&id=' . $id . '&error=name');
            exit;
        }
        if ($this->participantModel->update($id, $data)) {
            header('Location: ' . BASE_URL . '?route=investment/index&tab=participacao&success=participant_updated');
        } else {
            header('Location: ' . BASE_URL . '?route=investment/participantEdit&id=' . $id . '&error=1');
        }
        exit;
    }

    public function participantDelete()
    {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id > 0 && $this->participantModel->delete($id)) {
            header('Location: ' . BASE_URL . '?route=investment/index&tab=participacao&success=participant_deleted');
        } else {
            header('Location: ' . BASE_URL . '?route=investment/index&tab=participacao&error=1');
        }
        exit;
    }

    // ---- Bens/Equipamentos ----
    public function assetCreate()
    {
        $participants = $this->participantModel->listForSelect();
        require 'views/investment/asset_form.php';
    }

    public function assetStore()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?route=investment/index&tab=bens');
            exit;
        }
        $data = [
            'descricao'          => trim($_POST['descricao'] ?? ''),
            'categoria'         => trim($_POST['categoria'] ?? '') ?: null,
            'valor_estimado'     => isset($_POST['valor_estimado']) ? (float) str_replace([',', ' '], ['.', ''], $_POST['valor_estimado']) : null,
            'data_entrada'       => $_POST['data_entrada'] ?? date('Y-m-d'),
            'responsavel_id'    => !empty($_POST['responsavel_id']) ? (int) $_POST['responsavel_id'] : null,
            'origem'             => in_array($_POST['origem'] ?? '', ['comprado', 'doado', 'emprestado'], true) ? $_POST['origem'] : 'comprado',
            'localizacao'       => trim($_POST['localizacao'] ?? '') ?: null,
            'observacoes'       => trim($_POST['observacoes'] ?? '') ?: null,
            'vida_util_meses'    => !empty($_POST['vida_util_meses']) ? (int) $_POST['vida_util_meses'] : null,
        ];
        if ($data['descricao'] === '') {
            header('Location: ' . BASE_URL . '?route=investment/assetCreate&error=desc');
            exit;
        }
        if ($this->assetModel->create($data)) {
            header('Location: ' . BASE_URL . '?route=investment/index&tab=bens&success=asset');
        } else {
            header('Location: ' . BASE_URL . '?route=investment/assetCreate&error=1');
        }
        exit;
    }

    public function assetEdit()
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . '?route=investment/index&tab=bens');
            exit;
        }
        $asset = $this->assetModel->getById($id);
        if (!$asset) {
            header('Location: ' . BASE_URL . '?route=investment/index&tab=bens&error=1');
            exit;
        }
        $participants = $this->participantModel->listForSelect();
        require 'views/investment/asset_form.php';
    }

    public function assetUpdate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?route=investment/index&tab=bens');
            exit;
        }
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . '?route=investment/index&tab=bens');
            exit;
        }
        $data = [
            'descricao'          => trim($_POST['descricao'] ?? ''),
            'categoria'         => trim($_POST['categoria'] ?? '') ?: null,
            'valor_estimado'     => isset($_POST['valor_estimado']) ? (float) str_replace([',', ' '], ['.', ''], $_POST['valor_estimado']) : null,
            'data_entrada'       => $_POST['data_entrada'] ?? date('Y-m-d'),
            'responsavel_id'    => !empty($_POST['responsavel_id']) ? (int) $_POST['responsavel_id'] : null,
            'origem'             => in_array($_POST['origem'] ?? '', ['comprado', 'doado', 'emprestado'], true) ? $_POST['origem'] : 'comprado',
            'localizacao'       => trim($_POST['localizacao'] ?? '') ?: null,
            'observacoes'       => trim($_POST['observacoes'] ?? '') ?: null,
            'vida_util_meses'    => !empty($_POST['vida_util_meses']) ? (int) $_POST['vida_util_meses'] : null,
        ];
        if ($data['descricao'] === '') {
            header('Location: ' . BASE_URL . '?route=investment/assetEdit&id=' . $id . '&error=desc');
            exit;
        }
        if ($this->assetModel->update($id, $data)) {
            header('Location: ' . BASE_URL . '?route=investment/index&tab=bens&success=asset_updated');
        } else {
            header('Location: ' . BASE_URL . '?route=investment/assetEdit&id=' . $id . '&error=1');
        }
        exit;
    }

    public function assetDelete()
    {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id > 0 && $this->assetModel->delete($id)) {
            header('Location: ' . BASE_URL . '?route=investment/index&tab=bens&success=asset_deleted');
        } else {
            header('Location: ' . BASE_URL . '?route=investment/index&tab=bens&error=1');
        }
        exit;
    }

    /** Exportar CSV da listagem financeira (filtros atuais) */
    public function export()
    {
        $filters = [
            'start_date'      => $_GET['start_date'] ?? '',
            'end_date'        => $_GET['end_date'] ?? '',
            'tipo'            => $_GET['tipo'] ?? '',
            'pessoa'          => $_GET['pessoa'] ?? '',
            'status'          => $_GET['status'] ?? '',
        ];
        $investments = $this->model->getAll($filters);
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="investimentos_' . date('Y-m-d_H-i') . '.csv"');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($out, ['Data', 'Pessoa', 'Tipo', 'Descrição', 'Valor', 'Forma pagamento', 'Status', 'Observações'], ';');
        foreach ($investments as $i) {
            fputcsv($out, [
                $i['data'] ?? '',
                $i['pessoa'] ?? '',
                $i['tipo'] ?? '',
                $i['produto'] ?? '',
                str_replace('.', ',', (string) ($i['valor'] ?? 0)),
                $i['forma_pagamento'] ?? '',
                $i['status'] ?? '',
                $i['observacoes'] ?? '',
            ], ';');
        }
        fclose($out);
        exit;
    }

    private function buildInvestmentDataFromPost(): array
    {
        $dataDevol = trim($_POST['data_devolucao_prevista'] ?? '');
        return [
            'data'                    => $_POST['data'] ?? date('Y-m-d'),
            'pessoa'                  => trim($_POST['pessoa'] ?? '') ?: null,
            'participant_id'          => !empty($_POST['participant_id']) ? (int) $_POST['participant_id'] : null,
            'valor'                   => (float) str_replace([',', ' '], ['.', ''], $_POST['valor'] ?? 0),
            'produto'                 => trim($_POST['produto'] ?? '') ?: null,
            'tipo'                    => $_POST['tipo'] ?? 'aporte',
            'estado'                  => trim($_POST['estado'] ?? '') ?: null,
            'finalidade'              => trim($_POST['finalidade'] ?? '') ?: null,
            'status'                  => trim($_POST['status'] ?? '') ?: null,
            'observacoes'             => trim($_POST['observacoes'] ?? '') ?: null,
            'documento_numero'        => trim($_POST['documento_numero'] ?? '') ?: null,
            'quantidade'              => (int) ($_POST['quantidade'] ?? 1),
            'data_devolucao_prevista' => $dataDevol !== '' ? $dataDevol : null,
            'forma_pagamento'         => trim($_POST['forma_pagamento'] ?? '') ?: null,
            'categoria_ativo'         => trim($_POST['categoria_ativo'] ?? '') ?: null,
        ];
    }
}
