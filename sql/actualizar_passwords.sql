-- Actualizar contraseñas con hashes correctos
-- Ejecutar: mysql -u root -p refugio < actualizar_passwords.sql

USE refugio;

-- Actualizar admin (password: admin123)
UPDATE usuarios 
SET password = '$2y$12$txTtkpHhMn23dmfotTgiS.S6esy7C37EyYf/g.HKODk8GuUlJvGu.' 
WHERE email = 'admin@hostel.com';

-- Actualizar user1 (password: user123)
UPDATE usuarios 
SET password = '$2y$12$Y8/XKy8fRpfd.7vPwtmdZ.6SrtR.KJbonuBn3HruA.AiIO998DZJy' 
WHERE email = 'user1@mail.com';

-- Actualizar user2 (password: user123)
UPDATE usuarios 
SET password = '$2y$12$Y8/XKy8fRpfd.7vPwtmdZ.6SrtR.KJbonuBn3HruA.AiIO998DZJy' 
WHERE email = 'user2@mail.com';

-- Verificar actualización
SELECT email, rol, LEFT(password, 30) as password_hash FROM usuarios;
