-- Cole no phpMyAdmin (aba SQL) e execute.
-- Se der "Duplicate column name", essa coluna já existe: apague só essa linha e execute de novo.

ALTER TABLE customers ADD COLUMN cep VARCHAR(10) NULL AFTER email;
ALTER TABLE customers ADD COLUMN address_street VARCHAR(255) NULL AFTER cep;
ALTER TABLE customers ADD COLUMN address_number VARCHAR(20) NULL AFTER address_street;
ALTER TABLE customers ADD COLUMN address_complement VARCHAR(100) NULL AFTER address_number;
ALTER TABLE customers ADD COLUMN address_neighborhood VARCHAR(100) NULL AFTER address_complement;
ALTER TABLE customers ADD COLUMN address_city VARCHAR(100) NULL AFTER address_neighborhood;
ALTER TABLE customers ADD COLUMN address_state VARCHAR(2) NULL AFTER address_city;
ALTER TABLE sales ADD COLUMN delivery_address TEXT NULL AFTER customer_id;
ALTER TABLE sales ADD COLUMN is_pickup TINYINT(1) NOT NULL DEFAULT 0 AFTER delivery_address;
