<?php
// Script temporal para generar hashes correctos

echo "Generando hashes para las contraseñas...\n\n";

$admin_pass = 'admin123';
$user_pass  = 'user123';

$admin_hash = password_hash($admin_pass, PASSWORD_BCRYPT);
$user_hash  = password_hash($user_pass, PASSWORD_BCRYPT);

echo "=== HASHES GENERADOS ===\n\n";
echo "admin123:\n$admin_hash\n\n";
echo "user123:\n$user_hash\n\n";

echo "=== SQL PARA ACTUALIZAR ===\n\n";
echo "UPDATE usuarios SET password = '$admin_hash' WHERE email = 'admin@hostel.com';\n";
echo "UPDATE usuarios SET password = '$user_hash' WHERE email = 'user1@mail.com';\n";
echo "UPDATE usuarios SET password = '$user_hash' WHERE email = 'user2@mail.com';\n\n";

echo "=== VERIFICACIÓN ===\n";
echo "Verificando admin123: " . (password_verify($admin_pass, $admin_hash) ? "✓ OK" : "✗ FAIL") . "\n";
echo "Verificando user123: " . (password_verify($user_pass, $user_hash) ? "✓ OK" : "✗ FAIL") . "\n";
