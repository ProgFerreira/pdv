-- =============================================================================
-- Migrations PDV: Cliente (endereço) + Retirada no local
-- Execute no phpMyAdmin (aba SQL) ou: mysql -u usuario -p nome_do_banco < este_arquivo.sql
--
-- OPÇÃO A - Se der erro com DELIMITER/procedure, use a OPÇÃO B abaixo.
-- OPÇÃO B - Copie e cole os ALTERs um por vez; se der "Duplicate column", pule.
-- =============================================================================

-- ---------- OPÇÃO A: Tudo de uma vez (ignora coluna que já existe) ----------
DELIMITER //

DROP PROCEDURE IF EXISTS _pdv_add_columns//

CREATE PROCEDURE _pdv_add_columns()
BEGIN
  DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

  ALTER TABLE customers ADD COLUMN cep VARCHAR(10) NULL AFTER email;
  ALTER TABLE customers ADD COLUMN address_street VARCHAR(255) NULL AFTER cep;
  ALTER TABLE customers ADD COLUMN address_number VARCHAR(20) NULL AFTER address_street;
  ALTER TABLE customers ADD COLUMN address_complement VARCHAR(100) NULL AFTER address_number;
  ALTER TABLE customers ADD COLUMN address_neighborhood VARCHAR(100) NULL AFTER address_complement;
  ALTER TABLE customers ADD COLUMN address_city VARCHAR(100) NULL AFTER address_neighborhood;
  ALTER TABLE customers ADD COLUMN address_state VARCHAR(2) NULL AFTER address_city;

  ALTER TABLE sales ADD COLUMN delivery_address TEXT NULL AFTER customer_id;
  ALTER TABLE sales ADD COLUMN is_pickup TINYINT(1) NOT NULL DEFAULT 0 AFTER delivery_address;
END//

DELIMITER ;

CALL _pdv_add_columns();
DROP PROCEDURE IF EXISTS _pdv_add_columns;


-- ---------- OPÇÃO B: ALTERs simples (rode um por vez; ignore "Duplicate column") ----------
/*
ALTER TABLE customers ADD COLUMN cep VARCHAR(10) NULL AFTER email;
ALTER TABLE customers ADD COLUMN address_street VARCHAR(255) NULL AFTER cep;
ALTER TABLE customers ADD COLUMN address_number VARCHAR(20) NULL AFTER address_street;
ALTER TABLE customers ADD COLUMN address_complement VARCHAR(100) NULL AFTER address_number;
ALTER TABLE customers ADD COLUMN address_neighborhood VARCHAR(100) NULL AFTER address_complement;
ALTER TABLE customers ADD COLUMN address_city VARCHAR(100) NULL AFTER address_neighborhood;
ALTER TABLE customers ADD COLUMN address_state VARCHAR(2) NULL AFTER address_city;
ALTER TABLE sales ADD COLUMN delivery_address TEXT NULL AFTER customer_id;
ALTER TABLE sales ADD COLUMN is_pickup TINYINT(1) NOT NULL DEFAULT 0 AFTER delivery_address;
*/
