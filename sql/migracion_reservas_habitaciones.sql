-- Migración: Cambiar sistema de reservas de camas individuales a habitaciones con número de camas
-- Fecha: 23 de octubre de 2025

-- 1. Agregar nuevas columnas a la tabla reservas
ALTER TABLE reservas 
ADD COLUMN id_habitacion INT NULL AFTER id_usuario,
ADD COLUMN numero_camas TINYINT NULL AFTER id_habitacion;

-- 2. Hacer nullable la columna id_cama para compatibilidad
ALTER TABLE reservas 
MODIFY COLUMN id_cama INT NULL;

-- 3. Agregar foreign key para id_habitacion
ALTER TABLE reservas
ADD CONSTRAINT fk_reserva_habitacion 
FOREIGN KEY (id_habitacion) REFERENCES habitaciones(id) ON DELETE CASCADE;

-- 4. Agregar índice para mejorar consultas
ALTER TABLE reservas
ADD INDEX idx_habitacion (id_habitacion);

-- 5. Crear tabla intermedia para relación muchos a muchos
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

-- 6. Migrar datos existentes (opcional - si ya hay reservas)
-- Actualizar las reservas existentes para que tengan id_habitacion basado en la cama
UPDATE reservas r
INNER JOIN camas c ON r.id_cama = c.id
SET r.id_habitacion = c.id_habitacion,
    r.numero_camas = 1
WHERE r.id_habitacion IS NULL;

-- 7. Migrar relaciones a la tabla reservas_camas
INSERT INTO reservas_camas (id_reserva, id_cama)
SELECT id, id_cama 
FROM reservas 
WHERE id_cama IS NOT NULL;

-- 8. Verificar migración
SELECT 
    r.id,
    r.id_usuario,
    r.id_habitacion,
    r.numero_camas,
    r.id_cama,
    r.fecha_inicio,
    r.fecha_fin,
    r.estado
FROM reservas r
ORDER BY r.id DESC
LIMIT 10;
