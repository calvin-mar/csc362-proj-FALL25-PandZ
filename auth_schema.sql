/*
  Add these columns and table to your existing database schema
  to support the authentication system.
*/

-- Add password field to clients table
ALTER TABLE clients ADD COLUMN client_password VARCHAR(255) AFTER client_username;
ALTER TABLE departments ADD COLUMN department_password VARCHAR(255) AFTER department_name;

-- If departments table already exists, just add password column
-- ALTER TABLE departments ADD COLUMN department_password VARCHAR(255);

-- Create govt_workers table
CREATE TABLE IF NOT EXISTS govt_workers (
    worker_id INT NOT NULL AUTO_INCREMENT,
    worker_username VARCHAR(255) NOT NULL UNIQUE,
    worker_password VARCHAR(255) NOT NULL,
    worker_name VARCHAR(255) NOT NULL,
    worker_email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (worker_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample data with hashed passwords (password is "password123" for all)
-- In production, use stronger passwords and proper hashing

-- Insert sample client (username: client1, password: password123)
INSERT INTO clients (client_username, client_password) 
VALUES ('client1', '$2y$10$GDH588FxhJiWyrBxgjFAPuQKoVoh8TB2zw17d0USHY/4cyJ8N/zti');
-- Insert sample department (username: Planning, password: password123)
INSERT INTO departments (department_name, department_password) 
VALUES ('Planning', '$2y$10$GDH588FxhJiWyrBxgjFAPuQKoVoh8TB2zw17d0USHY/4cyJ8N/zti');

-- Insert sample government worker (username: admin, password: password123)
INSERT INTO govt_workers (worker_username, worker_password, worker_name, worker_email) 
VALUES ('admin', '$2y$10$GDH588FxhJiWyrBxgjFAPuQKoVoh8TB2zw17d0USHY/4cyJ8N/zti', 'Admin User', 'admin@example.com');
