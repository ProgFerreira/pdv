<?php
/**
 * Executa a migration 002_investimentos_controle.sql
 * Se a tabela já existir com estrutura antiga (data_aquisicao, descricao), converte para a nova (data, pessoa, produto, tipo, estado).
 * Uso: php run_migration_investments.php
 */
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/database.php';

$file = __DIR__ . '/database/migrations/002_investimentos_controle.sql';
if (!is_file($file)) {
    fwrite(STDERR, "Arquivo não encontrado: $file\n");
    exit(1);
}

try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'investimentos'");
    $tableExists = $stmt->rowCount() > 0;

    if (!$tableExists) {
        // Criar tabela do zero
        $pdo->exec('DROP TABLE IF EXISTS investimentos');
        $sql = file_get_contents($file);
        $start = strpos($sql, 'CREATE TABLE');
        $createSql = $start !== false ? trim(substr($sql, $start)) : '';
        if ($createSql === '') {
            throw new RuntimeException('CREATE TABLE não encontrado no arquivo.');
        }
        $pdo->exec($createSql);
        echo "Tabela investimentos criada com sucesso.\n";
        exit(0);
    }

    // Tabela existe: verificar colunas e ajustar para a nova estrutura
    $stmt = $pdo->query("SHOW COLUMNS FROM investimentos");
    $columns = [];
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        $columns[$row['Field']] = true;
    }

    $alter = [];

    // Coluna de data: se tem data_aquisicao e não tem data, renomear/criar
    if (empty($columns['data']) && !empty($columns['data_aquisicao'])) {
        $pdo->exec("ALTER TABLE investimentos CHANGE COLUMN data_aquisicao `data` DATE NOT NULL COMMENT 'Data do lançamento'");
        echo "Coluna data_aquisicao renomeada para data.\n";
    } elseif (empty($columns['data'])) {
        $pdo->exec("ALTER TABLE investimentos ADD COLUMN `data` DATE NOT NULL DEFAULT (CURRENT_DATE) COMMENT 'Data do lançamento' AFTER id");
        echo "Coluna data adicionada.\n";
    }

    if (empty($columns['pessoa'])) {
        $pdo->exec("ALTER TABLE investimentos ADD COLUMN pessoa VARCHAR(255) DEFAULT NULL COMMENT 'Sócio, doador' AFTER \`data\`");
        echo "Coluna pessoa adicionada.\n";
    }
    if (empty($columns['produto'])) {
        $pdo->exec("ALTER TABLE investimentos ADD COLUMN produto VARCHAR(255) DEFAULT NULL COMMENT 'Descrição do produto/equipamento' AFTER valor");
        echo "Coluna produto adicionada.\n";
    }
    if (empty($columns['tipo'])) {
        $pdo->exec("ALTER TABLE investimentos ADD COLUMN tipo VARCHAR(50) NOT NULL DEFAULT 'compra' COMMENT 'compra, doacao, emprestimo, aporte_socio, investimento_dinheiro' AFTER produto");
        echo "Coluna tipo adicionada.\n";
    }
    if (empty($columns['estado'])) {
        $pdo->exec("ALTER TABLE investimentos ADD COLUMN estado VARCHAR(20) DEFAULT NULL COMMENT 'novo, usado' AFTER tipo");
        echo "Coluna estado adicionada.\n";
    }
    if (empty($columns['created_at'])) {
        $pdo->exec("ALTER TABLE investimentos ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        echo "Coluna created_at adicionada.\n";
    }

    // Migration 003: campos extras
    if (empty($columns['observacoes'])) {
        $pdo->exec("ALTER TABLE investimentos ADD COLUMN observacoes TEXT NULL COMMENT 'Notas livres, prazos, condições' AFTER estado");
        echo "Coluna observacoes adicionada.\n";
    }
    if (empty($columns['documento_numero'])) {
        $pdo->exec("ALTER TABLE investimentos ADD COLUMN documento_numero VARCHAR(100) NULL COMMENT 'Nº nota, recibo, contrato' AFTER observacoes");
        echo "Coluna documento_numero adicionada.\n";
    }
    if (empty($columns['quantidade'])) {
        $pdo->exec("ALTER TABLE investimentos ADD COLUMN quantidade INT NOT NULL DEFAULT 1 COMMENT 'Número de unidades' AFTER documento_numero");
        echo "Coluna quantidade adicionada.\n";
    }
    if (empty($columns['data_devolucao_prevista'])) {
        $pdo->exec("ALTER TABLE investimentos ADD COLUMN data_devolucao_prevista DATE NULL COMMENT 'Para empréstimos' AFTER quantidade");
        echo "Coluna data_devolucao_prevista adicionada.\n";
    }
    if (empty($columns['forma_pagamento'])) {
        $pdo->exec("ALTER TABLE investimentos ADD COLUMN forma_pagamento VARCHAR(50) NULL COMMENT 'À vista, PIX, etc' AFTER data_devolucao_prevista");
        echo "Coluna forma_pagamento adicionada.\n";
    }
    if (empty($columns['categoria_ativo'])) {
        $pdo->exec("ALTER TABLE investimentos ADD COLUMN categoria_ativo VARCHAR(50) NULL COMMENT 'Equipamento, Móvel, etc' AFTER forma_pagamento");
        echo "Coluna categoria_ativo adicionada.\n";
    }
    // Índice para filtro por categoria (só tenta criar se não existir)
    try {
        $pdo->exec("CREATE INDEX idx_categoria_ativo ON investimentos (categoria_ativo)");
        echo "Índice idx_categoria_ativo criado.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key') === false && strpos($e->getMessage(), 'already exists') === false) {
            throw $e;
        }
    }

    // Se tinha descricao e agora tem produto, copiar descricao para produto (uma vez)
    $stmt = $pdo->query("SHOW COLUMNS FROM investimentos LIKE 'descricao'");
    if ($stmt->rowCount() > 0) {
        $pdo->exec("UPDATE investimentos SET produto = descricao WHERE produto IS NULL OR produto = ''");
        echo "Dados de descricao copiados para produto.\n";
    }

    echo "Migration 002_investimentos_controle concluída. Estrutura atualizada.\n";
} catch (PDOException $e) {
    fwrite(STDERR, "Erro: " . $e->getMessage() . "\n");
    exit(1);
} catch (Throwable $e) {
    fwrite(STDERR, "Erro: " . $e->getMessage() . "\n");
    exit(1);
}
