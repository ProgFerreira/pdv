-- Pedidos pelo link (cliente faz pedido em URL pública; operador converte em venda)
-- Execute no phpMyAdmin ou: mysql -u usuario -p nome_do_banco < 013_customer_orders.sql

CREATE TABLE IF NOT EXISTS customer_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NULL UNIQUE COMMENT 'Opcional: token do link (ex. UUID)',
    customer_id INT NULL,
    guest_name VARCHAR(100) NULL,
    guest_phone VARCHAR(20) NULL,
    guest_email VARCHAR(100) NULL,
    delivery_address TEXT NULL,
    is_pickup TINYINT(1) NOT NULL DEFAULT 0,
    observation TEXT NULL,
    total DECIMAL(10, 2) NOT NULL DEFAULT 0,
    status ENUM('pending', 'converted', 'cancelled') NOT NULL DEFAULT 'pending',
    sale_id INT NULL,
    sector_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (sale_id) REFERENCES sales(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS customer_order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (customer_order_id) REFERENCES customer_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
