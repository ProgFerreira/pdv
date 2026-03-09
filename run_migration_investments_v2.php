<?php
/**
 * Migration 004: Módulo financeiro-societário (participantes, loan_payments, assets, colunas em investimentos).
 * Uso: php run_migration_investments_v2.php
 */
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/database.php';

$migrationsDir = __DIR__ . '/database/migrations';
$file = $migrationsDir . '/004_investimentos_modulo_financeiro.sql';

if (!is_file($file)) {
    fwrite(STDERR, "Arquivo não encontrado: $file\n");
    exit(1);
}

try {
    // 1) investment_participants
    $stmt = $pdo->query("SHOW TABLES LIKE 'investment_participants'");
    $participantsExists = $stmt->rowCount() > 0;
    if ($participantsExists) {
        $stmt = $pdo->query("SHOW COLUMNS FROM investment_participants LIKE 'name'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("DROP TABLE investment_participants");
            $participantsExists = false;
        }
    }
    if (!$participantsExists) {
        $createParticipants = <<<'SQL'
CREATE TABLE investment_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    contact VARCHAR(255) DEFAULT NULL,
    document VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_deleted (deleted_at),
    INDEX idx_name (name(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;
        $pdo->exec($createParticipants);
        echo "Tabela investment_participants criada.\n";
    } else {
        echo "Tabela investment_participants já existe.\n";
    }

    // investment_loan_payments
    $stmt = $pdo->query("SHOW TABLES LIKE 'investment_loan_payments'");
    if ($stmt->rowCount() === 0) {
        $createLoanPayments = <<<'SQL'
CREATE TABLE investment_loan_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    investimento_id INT NOT NULL,
    data_pagamento DATE NOT NULL,
    valor_pago DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    forma_pagamento VARCHAR(50) DEFAULT NULL,
    comprovante VARCHAR(255) DEFAULT NULL,
    observacao TEXT DEFAULT NULL,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_investimento (investimento_id),
    FOREIGN KEY (investimento_id) REFERENCES investimentos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;
        $pdo->exec($createLoanPayments);
        echo "Tabela investment_loan_payments criada.\n";
    } else {
        echo "Tabela investment_loan_payments já existe.\n";
    }

    // investment_assets
    $stmt = $pdo->query("SHOW TABLES LIKE 'investment_assets'");
    if ($stmt->rowCount() === 0) {
        $createAssets = <<<'SQL'
CREATE TABLE investment_assets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    categoria VARCHAR(100) DEFAULT NULL,
    valor_estimado DECIMAL(12,2) DEFAULT NULL,
    data_entrada DATE NOT NULL,
    responsavel_id INT DEFAULT NULL,
    origem VARCHAR(50) NOT NULL DEFAULT 'comprado',
    localizacao VARCHAR(100) DEFAULT NULL,
    observacoes TEXT DEFAULT NULL,
    comprovante_arquivo VARCHAR(255) DEFAULT NULL,
    vida_util_meses INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_deleted (deleted_at),
    INDEX idx_categoria (categoria(50)),
    INDEX idx_origem (origem),
    INDEX idx_responsavel (responsavel_id),
    FOREIGN KEY (responsavel_id) REFERENCES investment_participants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;
        $pdo->exec($createAssets);
        echo "Tabela investment_assets criada.\n";
    } else {
        echo "Tabela investment_assets já existe.\n";
    }

    // Colunas extras em investimentos (só adicionar se não existir)
    $stmt = $pdo->query("SHOW COLUMNS FROM investimentos");
    $cols = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $cols[$row['Field']] = true;
    }

    $alters = [
        'participant_id' => "ADD COLUMN participant_id INT NULL AFTER pessoa",
        'finalidade'     => "ADD COLUMN finalidade VARCHAR(50) NULL COMMENT 'investimento_inicial, capital_giro, compra_equipamento' AFTER tipo",
        'status'         => "ADD COLUMN status VARCHAR(20) NULL COMMENT 'em_aberto, parcial, quitado, vencido' AFTER finalidade",
        'taxa_juros'     => "ADD COLUMN taxa_juros DECIMAL(8,4) NULL AFTER status",
        'tipo_juros'     => "ADD COLUMN tipo_juros VARCHAR(20) NULL COMMENT 'simples, composto' AFTER taxa_juros",
        'data_inicio'    => "ADD COLUMN data_inicio DATE NULL AFTER data_devolucao_prevista",
        'comprovante_arquivo' => "ADD COLUMN comprovante_arquivo VARCHAR(255) NULL AFTER observacoes",
        'created_by'     => "ADD COLUMN created_by INT NULL AFTER categoria_ativo",
        'updated_by'     => "ADD COLUMN updated_by INT NULL AFTER created_by",
        'updated_at'     => "ADD COLUMN updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP AFTER updated_by",
        'deleted_at'     => "ADD COLUMN deleted_at TIMESTAMP NULL AFTER updated_at",
    ];

    foreach ($alters as $col => $add) {
        if (empty($cols[$col])) {
            $pdo->exec("ALTER TABLE investimentos " . $add);
            echo "Coluna investimentos.$col adicionada.\n";
            $cols[$col] = true;
        }
    }

    if (!empty($cols['participant_id'])) {
        $stmt = $pdo->query("SHOW CREATE TABLE investimentos");
        $create = $stmt->fetch(PDO::FETCH_ASSOC)['Create Table'] ?? '';
        if (strpos($create, 'fk_investimentos_participant') === false) {
            try {
                $pdo->exec("ALTER TABLE investimentos ADD CONSTRAINT fk_investimentos_participant FOREIGN KEY (participant_id) REFERENCES investment_participants(id) ON DELETE SET NULL");
                echo "FK investimentos.participant_id criada.\n";
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate') === false && strpos($e->getMessage(), 'already exists') === false) {
                    // índice pode já existir
                }
            }
        }
    }

    echo "Migration 004 concluída.\n";
} catch (PDOException $e) {
    fwrite(STDERR, "Erro: " . $e->getMessage() . "\n");
    exit(1);
} catch (Throwable $e) {
    fwrite(STDERR, "Erro: " . $e->getMessage() . "\n");
    exit(1);
}
