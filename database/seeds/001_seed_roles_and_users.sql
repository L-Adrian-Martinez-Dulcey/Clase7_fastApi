INSERT INTO roles (name, description) VALUES
('Administrador', 'Control total del sistema.'),
('Coordinador', 'Administra eventos y logística.'),
('Empleado', 'Consulta tareas y actualiza estados.'),
('Proveedor', 'Consulta información limitada de su participación.');

INSERT INTO usuarios (name, email, password_hash, role_id, status, login_attempts, locked_until) VALUES
('Administrador EVORIA', 'admin@evoria.com', '$2y$10$M3FX8vB5L5JHxv0Gg6t7Vu7I0mpH3JTjPrlVwthS8mN.Rns1ES2T6', 1, 'activo', 0, NULL),
('Coordinador EVORIA', 'coord@evoria.com', '$2y$10$M3FX8vB5L5JHxv0Gg6t7Vu7I0mpH3JTjPrlVwthS8mN.Rns1ES2T6', 2, 'activo', 0, NULL),
('Empleado Logístico', 'empleado@evoria.com', '$2y$10$M3FX8vB5L5JHxv0Gg6t7Vu7I0mpH3JTjPrlVwthS8mN.Rns1ES2T6', 3, 'activo', 0, NULL),
('Proveedor Principal', 'proveedor@evoria.com', '$2y$10$M3FX8vB5L5JHxv0Gg6t7Vu7I0mpH3JTjPrlVwthS8mN.Rns1ES2T6', 4, 'activo', 0, NULL);
