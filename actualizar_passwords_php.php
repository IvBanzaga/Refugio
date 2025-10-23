<?php
require_once 'conexion.php';

echo "=== ACTUALIZACIÓN DE CONTRASEÑAS ===\n\n";

try {
    // Hashes correctos generados
    $admin_hash = '$2y$12$txTtkpHhMn23dmfotTgiS.S6esy7C37EyYf/g.HKODk8GuUlJvGu.';
    $user_hash  = '$2y$12$Y8/XKy8fRpfd.7vPwtmdZ.6SrtR.KJbonuBn3HruA.AiIO998DZJy';

    // Actualizar admin
    $stmt  = $conexionPDO->prepare("UPDATE usuarios SET password = :password WHERE email = :email");
    $email = 'admin@hostel.com';
    $stmt->bindParam(':password', $admin_hash);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    echo "✓ Admin actualizado (admin@hostel.com - password: admin123)\n";

    // Actualizar user1
    $email = 'user1@mail.com';
    $stmt->bindParam(':password', $user_hash);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    echo "✓ User1 actualizado (user1@mail.com - password: user123)\n";

    // Actualizar user2
    $email = 'user2@mail.com';
    $stmt->bindParam(':password', $user_hash);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    echo "✓ User2 actualizado (user2@mail.com - password: user123)\n";

    echo "\n=== VERIFICACIÓN ===\n";

    // Verificar las contraseñas
    $stmt     = $conexionPDO->query("SELECT email, rol, password FROM usuarios ORDER BY id");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($usuarios as $user) {
        $pass_correcta = ($user['rol'] === 'admin') ? 'admin123' : 'user123';
        $verifica      = password_verify($pass_correcta, $user['password']);
        $icono         = $verifica ? '✓' : '✗';

        echo "\n$icono {$user['email']} (rol: {$user['rol']})\n";
        echo "  Password esperada: $pass_correcta\n";
        echo "  Verifica: " . ($verifica ? "OK" : "FAIL") . "\n";
    }

    echo "\n=== CREDENCIALES PARA LOGIN ===\n";
    echo "\nADMINISTRADOR:\n";
    echo "  Email: admin@hostel.com\n";
    echo "  Password: admin123\n";
    echo "\nUSUARIO 1:\n";
    echo "  Email: user1@mail.com\n";
    echo "  Password: user123\n";
    echo "\nUSUARIO 2:\n";
    echo "  Email: user2@mail.com\n";
    echo "  Password: user123\n";

    echo "\n✅ ¡Actualización completada! Ahora puedes hacer login.\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
