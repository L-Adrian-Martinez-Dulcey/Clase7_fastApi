CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    status ENUM('activo','inactivo','bloqueado') DEFAULT 'activo',
    login_attempts INT DEFAULT 0,
    locked_until DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE INDEX idx_usuarios_email ON usuarios(email);
CREATE INDEX idx_usuarios_role_id ON usuarios(role_id);

CREATE TABLE eventos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    hora TIME NULL,
    ubicacion VARCHAR(200) NULL,
    tipo VARCHAR(80) NULL,
    estado ENUM('Planificación','Confirmado','En ejecución','Finalizado','Cancelado') NOT NULL DEFAULT 'Planificación',
    responsable_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (responsable_id) REFERENCES usuarios(id)
);

CREATE TABLE recursos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(120) NOT NULL,
    tipo VARCHAR(80) NOT NULL,
    descripcion TEXT,
    costo DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    cantidad_total INT NOT NULL DEFAULT 0,
    cantidad_disponible INT NOT NULL DEFAULT 0,
    disponibilidad ENUM('disponible','ocupado','mantenimiento','inactivo') DEFAULT 'disponible',
    estado ENUM('activo','inactivo') DEFAULT 'activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE inventario (
    id INT PRIMARY KEY AUTO_INCREMENT,
    recurso_id INT NOT NULL,
    cantidad_total INT NOT NULL DEFAULT 0,
    cantidad_disponible INT NOT NULL DEFAULT 0,
    cantidad_asignada INT NOT NULL DEFAULT 0,
    estado ENUM('normal','bajo','crítico') DEFAULT 'normal',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (recurso_id) REFERENCES recursos(id)
);

CREATE TABLE evento_recursos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    evento_id INT NOT NULL,
    recurso_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    fecha_asignacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('asignado','devuelto','pendiente') DEFAULT 'pendiente',
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    FOREIGN KEY (recurso_id) REFERENCES recursos(id)
);

CREATE TABLE cronogramas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    evento_id INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    responsable_id INT NULL,
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME NOT NULL,
    estado ENUM('pendiente','en_progreso','completado','cancelado') DEFAULT 'pendiente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    FOREIGN KEY (responsable_id) REFERENCES usuarios(id)
);

CREATE TABLE presupuestos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    evento_id INT NOT NULL,
    subtotal_recursos DECIMAL(12,2) DEFAULT 0.00,
    costos_adicionales DECIMAL(12,2) DEFAULT 0.00,
    total DECIMAL(12,2) DEFAULT 0.00,
    moneda VARCHAR(10) DEFAULT 'CLP',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE
);

CREATE TABLE alertas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    evento_id INT NULL,
    tipo VARCHAR(80) NOT NULL,
    mensaje TEXT NOT NULL,
    severidad ENUM('baja','media','alta','critica') DEFAULT 'media',
    estado ENUM('pendiente','resuelta') DEFAULT 'pendiente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE SET NULL
);

CREATE TABLE auditoria (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NULL,
    accion VARCHAR(120) NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    ip VARCHAR(45) NULL,
    descripcion TEXT,
    resultado ENUM('exito','error') DEFAULT 'exito',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE INDEX idx_eventos_evento_estado ON eventos(estado);
CREATE INDEX idx_recursos_disponibilidad ON recursos(disponibilidad);
CREATE INDEX idx_alertas_estado ON alertas(estado);
CREATE INDEX idx_auditoria_usuario ON auditoria(usuario_id);
