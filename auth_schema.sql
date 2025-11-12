
-- Sample data with hashed passwords (password is "password123" for all)
-- In production, use stronger passwords and proper hashing

-- Insert sample client (username: client1, password: password123)
INSERT INTO clients (client_username, client_password, client_type) VALUES 
  ('client1', '$2y$10$GDH588FxhJiWyrBxgjFAPuQKoVoh8TB2zw17d0USHY/4cyJ8N/zti', 'client'),
  ('Planning', '$2y$10$GDH588FxhJiWyrBxgjFAPuQKoVoh8TB2zw17d0USHY/4cyJ8N/zti', 'department'),
  ('admin', '$2y$10$GDH588FxhJiWyrBxgjFAPuQKoVoh8TB2zw17d0USHY/4cyJ8N/zti', 'govt_worker');

INSERT INTO departments(client_id, department_name) VALUES
  (2, "Planning");

INSERT INTO govt_workers(client_id, govt_worker_role) VALUES
  (3, "Admin");
