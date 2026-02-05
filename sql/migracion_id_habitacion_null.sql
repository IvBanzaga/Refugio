-- Migración para permitir id_habitacion NULL (para reservas "TODO EL REFUGIO")
-- Fecha: 2025-10-23

USE refugio;

-- Modificar id_habitacion para permitir NULL
ALTER TABLE reservas 
MODIFY COLUMN id_habitacion INT NULL COMMENT 'NULL para reservas de TODO EL REFUGIO';

-- Actualizar constraint para permitir NULL
ALTER TABLE reservas
DROP FOREIGN KEY IF EXISTS fk_reserva_habitacion;

ALTER TABLE reservas
ADD CONSTRAINT fk_reserva_habitacion 
FOREIGN KEY (id_habitacion) REFERENCES habitaciones(id) 
ON DELETE CASCADE;

-- Verificar cambios
SELECT 'Migración completada - id_habitacion puede ser NULL' as Estado;
