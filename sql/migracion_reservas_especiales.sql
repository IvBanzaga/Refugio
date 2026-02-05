-- Migración para permitir reservas especiales del admin
-- Fecha: 2025-10-23

USE refugio;

-- Modificar id_usuario para permitir NULL (reservas especiales sin usuario)
ALTER TABLE reservas 
MODIFY COLUMN id_usuario INT NULL COMMENT 'NULL para reservas especiales del admin';

-- Agregar columna observaciones para el motivo de reservas especiales
ALTER TABLE reservas 
ADD COLUMN IF NOT EXISTS observaciones VARCHAR(500) NULL COMMENT 'Motivo/evento para reservas especiales';

-- Actualizar constraint para permitir NULL en id_usuario
ALTER TABLE reservas
DROP FOREIGN KEY IF EXISTS fk_reserva_usuario;

ALTER TABLE reservas
ADD CONSTRAINT fk_reserva_usuario 
FOREIGN KEY (id_usuario) REFERENCES usuarios(id) 
ON DELETE CASCADE;

-- Verificar cambios
SELECT 'Migración completada exitosamente' as Estado;
