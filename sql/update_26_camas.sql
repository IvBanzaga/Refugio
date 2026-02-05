-- Script para configurar 1 habitación con 26 camas y el resto con 0 camas
-- La habitación 1 será la default con todas las camas

-- Paso 1: Eliminar todas las camas existentes
DELETE FROM reservas_camas;
DELETE FROM camas;

-- Paso 2: Actualizar las habitaciones
UPDATE habitaciones SET capacidad = 26 WHERE id = 1;
UPDATE habitaciones SET capacidad = 0 WHERE id IN (2, 3, 4);

-- Paso 3: Crear las 26 camas en la habitación 1
INSERT INTO camas (id_habitacion, numero, estado) VALUES
(1, 1, 'libre'),
(1, 2, 'libre'),
(1, 3, 'libre'),
(1, 4, 'libre'),
(1, 5, 'libre'),
(1, 6, 'libre'),
(1, 7, 'libre'),
(1, 8, 'libre'),
(1, 9, 'libre'),
(1, 10, 'libre'),
(1, 11, 'libre'),
(1, 12, 'libre'),
(1, 13, 'libre'),
(1, 14, 'libre'),
(1, 15, 'libre'),
(1, 16, 'libre'),
(1, 17, 'libre'),
(1, 18, 'libre'),
(1, 19, 'libre'),
(1, 20, 'libre'),
(1, 21, 'libre'),
(1, 22, 'libre'),
(1, 23, 'libre'),
(1, 24, 'libre'),
(1, 25, 'libre'),
(1, 26, 'libre');

-- Verificar
SELECT h.id, h.numero, h.capacidad, COUNT(c.id) as camas_creadas
FROM habitaciones h
LEFT JOIN camas c ON h.id = c.id_habitacion
GROUP BY h.id
ORDER BY h.id;
