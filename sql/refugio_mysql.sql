-- ============================================
-- ESQUEMA MYSQL - SISTEMA DE CONTROL DE CAMAS
-- Versión alternativa para MySQL/MariaDB
-- ============================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS refugio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE refugio;

-- ============================================
-- TABLAS
-- ============================================

-- 1. Usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    num_socio VARCHAR(20) UNIQUE NOT NULL,
    dni VARCHAR(9) UNIQUE NOT NULL,
    telf VARCHAR(15),
    email VARCHAR(100) UNIQUE NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apellido1 VARCHAR(50) NOT NULL,
    apellido2 VARCHAR(50),
    password VARCHAR(255) NOT NULL,
    foto_perfil VARCHAR(255) DEFAULT NULL,
    rol ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    INDEX idx_email (email),
    INDEX idx_rol (rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Habitaciones
CREATE TABLE habitaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero INT UNIQUE NOT NULL,
    capacidad INT NOT NULL,
    INDEX idx_numero (numero)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Camas
CREATE TABLE camas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL,
    id_habitacion INT NOT NULL,
    estado ENUM('libre', 'pendiente', 'reservada') NOT NULL DEFAULT 'libre',
    CONSTRAINT fk_cama_habitacion FOREIGN KEY (id_habitacion) REFERENCES habitaciones(id) ON DELETE CASCADE,
    CONSTRAINT cama_unica_por_habitacion UNIQUE (numero, id_habitacion),
    INDEX idx_estado (estado),
    INDEX idx_habitacion (id_habitacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Reservas
CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NULL COMMENT 'NULL para reservas especiales del admin',
    id_habitacion INT NULL COMMENT 'NULL para reservas de TODO EL REFUGIO',
    numero_camas TINYINT NOT NULL DEFAULT 1,
    id_cama INT NULL, -- Campo legacy para compatibilidad
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    estado ENUM('pendiente', 'reservada', 'cancelada') NOT NULL DEFAULT 'pendiente',
    observaciones VARCHAR(500) NULL COMMENT 'Motivo/evento para reservas especiales',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reserva_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_reserva_habitacion FOREIGN KEY (id_habitacion) REFERENCES habitaciones(id) ON DELETE CASCADE,
    CONSTRAINT fk_reserva_cama FOREIGN KEY (id_cama) REFERENCES camas(id) ON DELETE SET NULL,
    INDEX idx_estado (estado),
    INDEX idx_usuario (id_usuario),
    INDEX idx_habitacion (id_habitacion),
    INDEX idx_fechas (fecha_inicio, fecha_fin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Relación Reservas-Camas (muchos a muchos)
CREATE TABLE reservas_camas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_reserva INT NOT NULL,
    id_cama INT NOT NULL,
    CONSTRAINT fk_rc_reserva FOREIGN KEY (id_reserva) REFERENCES reservas(id) ON DELETE CASCADE,
    CONSTRAINT fk_rc_cama FOREIGN KEY (id_cama) REFERENCES camas(id) ON DELETE CASCADE,
    CONSTRAINT reserva_cama_unica UNIQUE (id_reserva, id_cama),
    INDEX idx_reserva (id_reserva),
    INDEX idx_cama (id_cama)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Acompañantes
CREATE TABLE acompanantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_reserva INT NOT NULL,
    num_socio VARCHAR(20),
    es_socio BOOLEAN DEFAULT FALSE,
    dni VARCHAR(9) NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apellido1 VARCHAR(50) NOT NULL,
    apellido2 VARCHAR(50),
    actividad VARCHAR(255),
    CONSTRAINT fk_acompanante_reserva FOREIGN KEY (id_reserva) REFERENCES reservas(id) ON DELETE CASCADE,
    INDEX idx_reserva (id_reserva)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DATOS INICIALES
-- ============================================

-- Habitaciones
INSERT INTO habitaciones (numero, capacidad) VALUES 
(1, 4),
(2, 4),
(3, 4),
(4, 14);

-- Camas (26 en total)
INSERT INTO camas (numero, id_habitacion) VALUES
-- Habitación 1
(1, 1), (2, 1), (3, 1), (4, 1),
-- Habitación 2
(1, 2), (2, 2), (3, 2), (4, 2),
-- Habitación 3
(1, 3), (2, 3), (3, 3), (4, 3),
-- Habitación 4 (14 camas)
(1, 4), (2, 4), (3, 4), (4, 4), (5, 4), (6, 4), (7, 4),
(8, 4), (9, 4), (10, 4), (11, 4), (12, 4), (13, 4), (14, 4);

-- Usuario administrador por defecto (password: admin123 - hasheado con bcrypt)
INSERT INTO usuarios (num_socio, dni, telf, email, nombre, apellido1, apellido2, password, rol) VALUES 
('A001', '00000000A', '600000000', 'admin@hostel.com', 'Admin', 'General', 'System', '$2y$12$txTtkpHhMn23dmfotTgiS.S6esy7C37EyYf/g.HKODk8GuUlJvGu.', 'admin');

-- Usuarios de ejemplo (password: user123 - hasheado con bcrypt)
INSERT INTO usuarios (num_socio, dni, telf, email, nombre, apellido1, apellido2, password, rol) VALUES 
('U001', '12345678B', '611111111', 'user1@mail.com', 'Carlos', 'Pérez', 'Gómez', '$2y$12$Y8/XKy8fRpfd.7vPwtmdZ.6SrtR.KJbonuBn3HruA.AiIO998DZJy', 'user'),
('U002', '23456789C', '622222222', 'user2@mail.com', 'Lucía', 'López', 'Martín', '$2y$12$Y8/XKy8fRpfd.7vPwtmdZ.6SrtR.KJbonuBn3HruA.AiIO998DZJy', 'user');

-- Reserva de ejemplo (pendiente)
INSERT INTO reservas (id_usuario, id_cama, fecha_inicio, fecha_fin) VALUES 
(2, 5, '2025-10-25', '2025-10-28');

-- ============================================
-- VISTAS ÚTILES (OPCIONAL)
-- ============================================

-- Vista de disponibilidad de habitaciones
CREATE OR REPLACE VIEW vista_habitaciones AS
SELECT 
    h.id,
    h.numero,
    h.capacidad,
    COUNT(c.id) as total_camas,
    SUM(CASE WHEN c.estado = 'libre' THEN 1 ELSE 0 END) as camas_libres,
    SUM(CASE WHEN c.estado = 'pendiente' THEN 1 ELSE 0 END) as camas_pendientes,
    SUM(CASE WHEN c.estado = 'reservada' THEN 1 ELSE 0 END) as camas_reservadas
FROM habitaciones h
LEFT JOIN camas c ON h.id = c.id_habitacion
GROUP BY h.id, h.numero, h.capacidad;

-- Vista de reservas con información completa
CREATE OR REPLACE VIEW vista_reservas_completas AS
SELECT 
    r.id,
    r.fecha_inicio,
    r.fecha_fin,
    r.estado,
    r.fecha_creacion,
    u.num_socio,
    u.nombre as usuario_nombre,
    u.apellido1 as usuario_apellido1,
    u.email,
    h.numero as habitacion_numero,
    c.numero as cama_numero,
    DATEDIFF(r.fecha_fin, r.fecha_inicio) as dias_estancia
FROM reservas r
JOIN usuarios u ON r.id_usuario = u.id
JOIN camas c ON r.id_cama = c.id
JOIN habitaciones h ON c.id_habitacion = h.id;

-- ============================================
-- INFORMACIÓN
-- ============================================

SELECT 'Base de datos creada correctamente' as mensaje;
SELECT '4 habitaciones con 26 camas en total' as estructura;
SELECT '3 usuarios de prueba creados' as usuarios;
SELECT 'Contraseñas: admin123 y user123' as credenciales;
