-- ============================================
-- ACTUALIZACIÓN: Agregar campo foto_perfil
-- Fecha: 23 de octubre de 2025
-- Versión: 1.1.0
-- ============================================

USE refugio;

-- Agregar columna foto_perfil a la tabla usuarios
ALTER TABLE usuarios 
ADD COLUMN foto_perfil VARCHAR(255) DEFAULT NULL 
AFTER password;

-- Verificar que se agregó correctamente
DESCRIBE usuarios;

-- Mensaje de confirmación
SELECT 'Columna foto_perfil agregada correctamente a la tabla usuarios' AS resultado;
SELECT 'Los usuarios ahora pueden subir fotos de perfil desde su panel' AS funcionalidad;
