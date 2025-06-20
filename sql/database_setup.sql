USE crm_ligeros;

DROP TABLE IF EXISTS attendance_log;
DROP TABLE IF EXISTS user_sessions;
DROP TABLE IF EXISTS members;
DROP TABLE IF EXISTS users;

-- Tabla de usuarios
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    user_level TINYINT DEFAULT 1 COMMENT '1=Usuario, 2=Vocal, 3=Cop, 4=Tresorer, 5=Vice Secretari, 6=Secretari, 7=Darrer Tro, 8=Primer Tro, 9=Admin, 10=Superadmin',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de miembros
CREATE TABLE members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    name VARCHAR(100) NOT NULL,
    surname VARCHAR(100) NOT NULL,
    dni VARCHAR(20) UNIQUE,
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    user_level TINYINT DEFAULT 1,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de registro de asistencia
CREATE TABLE attendance_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    event_name VARCHAR(200) DEFAULT 'Asistencia General',
    scan_datetime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    scanner_user_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    notes TEXT,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (scanner_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de sesiones de usuario
CREATE TABLE user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar usuario superadmin
INSERT INTO users (username, email, password_hash, user_level) VALUES 
('superadmin', 'superadmin@ligeros.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 10);

-- Insertar usuarios adicionales
INSERT INTO users (username, email, password_hash, user_level) VALUES 
('admin', 'admin@ligeros.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 9),
('primertro', 'primertro@ligeros.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 8);

-- Insertar miembros de ejemplo
INSERT INTO members (name, surname, dni, email, user_level) VALUES 
('Usuario', 'Demo', '12345678A', 'demo@ligeros.com', 1),
('María', 'García', '87654321B', 'maria@ligeros.com', 1),
('Carlos', 'López', '11223344C', 'carlos@ligeros.com', 2),
('Ana', 'Fernández', '44332211D', 'ana@ligeros.com', 1),
('Pedro', 'Martínez', '55667788E', 'pedro@ligeros.com', 3);

-- Insertar algunos registros de asistencia de ejemplo
INSERT INTO attendance_log (member_id, event_name, scanner_user_id, ip_address) VALUES 
(1, 'Reunión General', 1, '127.0.0.1'),
(2, 'Reunión General', 1, '127.0.0.1'),
(3, 'Evento Especial', 2, '127.0.0.1'),
(1, 'Asistencia Diaria', 1, '127.0.0.1'),
(4, 'Asistencia Diaria', 1, '127.0.0.1');