-- ============================================
-- ENUMS
-- ============================================

-- Roles del usuario
CREATE TYPE rol_usuario AS ENUM ('admin', 'user');

-- Estado de la cama
CREATE TYPE estado_cama AS ENUM ('libre', 'pendiente', 'reservada');

-- Estado de la reserva
CREATE TYPE estado_reserva AS ENUM ('pendiente', 'reservada', 'cancelada');

-- ============================================
-- TABLAS
-- ============================================

-- 1. Usuarios
CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    num_socio VARCHAR(20) UNIQUE NOT NULL,
    dni VARCHAR(9) UNIQUE NOT NULL,
    telf VARCHAR(15),
    email VARCHAR(100) UNIQUE NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apellido1 VARCHAR(50) NOT NULL,
    apellido2 VARCHAR(50),
    password VARCHAR(255) NOT NULL,
    rol rol_usuario NOT NULL DEFAULT 'user'
);

-- 2. Habitaciones
CREATE TABLE habitaciones (
    id SERIAL PRIMARY KEY,
    numero INT UNIQUE NOT NULL,
    capacidad INT NOT NULL
);

-- 3. Camas
CREATE TABLE camas (
    id SERIAL PRIMARY KEY,
    numero INT NOT NULL,
    id_habitacion INT NOT NULL REFERENCES habitaciones(id) ON DELETE CASCADE,
    estado estado_cama NOT NULL DEFAULT 'libre',
    CONSTRAINT cama_unica_por_habitacion UNIQUE (numero, id_habitacion)
);

-- 4. Reservas
CREATE TABLE reservas (
    id SERIAL PRIMARY KEY,
    id_usuario INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    id_cama INT NOT NULL REFERENCES camas(id) ON DELETE CASCADE,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    estado estado_reserva NOT NULL DEFAULT 'pendiente',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT reserva_unica_cama_periodo UNIQUE (id_cama, fecha_inicio, fecha_fin)
);

-- ============================================
-- DATOS INICIALES
-- ============================================

-- Habitaciones
INSERT INTO habitaciones (numero, capacidad)
VALUES 
(1, 4),
(2, 4),
(3, 4),
(4, 14);

-- Camas (26 en total)
INSERT INTO camas (numero, id_habitacion)
VALUES
(1, 1), (2, 1), (3, 1), (4, 1),
(1, 2), (2, 2), (3, 2), (4, 2),
(1, 3), (2, 3), (3, 3), (4, 3);

INSERT INTO camas (numero, id_habitacion)
SELECT i, 4 FROM generate_series(1, 14) AS i;

-- Tabla para gestionar acompañantes de cada reserva
CREATE TABLE acompanantes (
    id SERIAL PRIMARY KEY,
    id_reserva INT NOT NULL REFERENCES reservas(id) ON DELETE CASCADE,
    num_socio VARCHAR(20),
    es_socio BOOLEAN DEFAULT FALSE,
    dni VARCHAR(9) NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apellido1 VARCHAR(50) NOT NULL,
    apellido2 VARCHAR(50),
    actividad VARCHAR(255)
);

-- ============================================
-- DATOS INICIALES
-- ============================================

-- Usuario administrador por defecto (password: admin123 - hasheado con bcrypt)
INSERT INTO usuarios (num_socio, dni, telf, email, nombre, apellido1, apellido2, password, rol)
VALUES ('A001', '00000000A', '600000000', 'admin@hostel.com', 'Admin', 'General', 'System', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Usuarios de ejemplo (password: user123 - hasheado con bcrypt)
INSERT INTO usuarios (num_socio, dni, telf, email, nombre, apellido1, apellido2, password, rol)
VALUES 
('U001', '12345678B', '611111111', 'user1@mail.com', 'Carlos', 'Pérez', 'Gómez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('U002', '23456789C', '622222222', 'user2@mail.com', 'Lucía', 'López', 'Martín', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Reserva de ejemplo (pendiente)
INSERT INTO reservas (id_usuario, id_cama, fecha_inicio, fecha_fin)
VALUES (2, 5, '2025-10-25', '2025-10-28');
