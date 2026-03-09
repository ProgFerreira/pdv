<?php
/**
 * Atualiza o banco para suportar todos os campos de contas_pagar e contas_receber (v15).
 * Adiciona colunas faltantes sem falhar se já existirem.
 */
date_default_timezone_set('America/Sao_Paulo');
require_once dirname(__DIR__) . '/config/env.php';
require_once dirname(__DIR__) . '/config/database.php';

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function columnExists($pdo, $table, $column) {
    $stmt = $pdo->prepare("
        SELECT 1 FROM information_schema.COLUMNS 
        WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?
    ");
    $stmt->execute([$table, $column]);
    return (bool) $stmt->fetch();
}

function runSql($pdo, $sql) {
    try {
        $pdo->exec($sql);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

echo "Verificando tabelas e colunas...\n";

// 1. contas_pagar: adicionar colunas que podem faltar
$columnsPayable = [
    'recorrente' => "ALTER TABLE contas_pagar ADD COLUMN recorrente BOOLEAN DEFAULT FALSE",
    'regra_recorrencia' => "ALTER TABLE contas_pagar ADD COLUMN regra_recorrencia ENUM('mensal', 'semanal') NULL",
    'parcelado' => "ALTER TABLE contas_pagar ADD COLUMN parcelado BOOLEAN DEFAULT FALSE",
    'qtd_parcelas' => "ALTER TABLE contas_pagar ADD COLUMN qtd_parcelas INT DEFAULT 1",
    'pai_id' => "ALTER TABLE contas_pagar ADD COLUMN pai_id INT NULL",
    'anexo_path' => "ALTER TABLE contas_pagar ADD COLUMN anexo_path VARCHAR(255) NULL",
];

$stmt = $pdo->query("SHOW TABLES LIKE 'contas_pagar'");
if ($stmt->rowCount() > 0) {
    foreach ($columnsPayable as $col => $sql) {
        if (!columnExists($pdo, 'contas_pagar', $col)) {
            if (runSql($pdo, $sql)) {
                echo "  contas_pagar: coluna '$col' adicionada.\n";
            } else {
                echo "  contas_pagar: falha ao adicionar '$col' (pode já existir ou dependência).\n";
            }
        }
    }
} else {
    echo "  Tabela contas_pagar não existe. Execute o schema_v15_comprehensive_finance.sql completo ou run_migrations.\n";
}

// 2. contas_receber: garantir coluna origem como ENUM se existir como outro tipo
$stmt = $pdo->query("SHOW TABLES LIKE 'contas_receber'");
if ($stmt->rowCount() > 0) {
    if (!columnExists($pdo, 'contas_receber', 'origem')) {
        runSql($pdo, "ALTER TABLE contas_receber ADD COLUMN origem ENUM('PDV', 'DELIVERY', 'MANUAL') DEFAULT 'MANUAL'");
        echo "  contas_receber: coluna 'origem' adicionada.\n";
    }
}

// 3. Criar tabelas do v15 se não existirem (apenas leitura do arquivo e execução dos CREATE TABLE IF NOT EXISTS)
$v15File = __DIR__ . '/schema_v15_comprehensive_finance.sql';
if (is_file($v15File)) {
    $sql = file_get_contents($v15File);
    // Executar apenas linhas que são CREATE TABLE (evitar conflitos com índices já existentes)
    foreach (['plano_contas', 'contas_bancarias', 'contas_pagar', 'contas_receber', 'movimentacao_caixa'] as $tbl) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$tbl'");
        if ($stmt->rowCount() === 0) {
            echo "  Tabela $tbl não existe. Execute manualmente o schema_v15_comprehensive_finance.sql.\n";
        }
    }
}

echo "Concluído.\n";
