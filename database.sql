CREATE DATABASE IF NOT EXISTS kanban_db CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE kanban_db;

-- Tabela principal de pedidos/cards
CREATE TABLE IF NOT EXISTS cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('todo', 'doing', 'done') NOT NULL DEFAULT 'todo',
    production_steps JSON,
    lamination_details JSON,
    shipping_status ENUM('none', 'pending', 'assigned') NOT NULL DEFAULT 'none',
    shipping_lane_id INT NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Tabela para os destinos e motoristas
CREATE TABLE IF NOT EXISTS `shipping_lanes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `destination` VARCHAR(255) NOT NULL,
    `driver` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- Inserir alguns dados de exemplo para teste
INSERT INTO cards (title, description, status, production_steps, lamination_details, shipping_status) VALUES
('Pedido #1001', 'Fabricar 100 peças do modelo X.', 'todo', '{}', '{}', 'none'),
('Pedido #1002', 'Montagem de 50 unidades do produto Y.', 'todo', '{}', '{}', 'none'),
('Pedido #1003', 'Inspeção de qualidade do lote Z.', 'doing', '{\"pintura\": true, \"laminacao\": false, \"pastilhamento\": false}', '{}', 'none'),
('Pedido #1004', 'Embalar e enviar o pedido A.', 'done', '{}', '{}', 'pending'),
('Pedido #1005', 'Pedido B finalizado.', 'done', '{}', '{}', 'none');

-- Inserir alguns dados de exemplo para os destinos
INSERT INTO `shipping_lanes` (destination, driver) VALUES
('São Paulo - Capital', 'João Silva'),
('Rio de Janeiro - Capital', 'Maria Oliveira'),
('Belo Horizonte - MG', 'Carlos Pereira');
