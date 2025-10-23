<?php
/**
 * Script de Verificación de Configuración MySQL
 *
 * Este script verifica que:
 * - MySQL está accesible
 * - La base de datos existe
 * - Las tablas están creadas
 * - Los datos de prueba están cargados
 * - PDO MySQL funciona correctamente
 *
 * EJECUTAR: php verificar_mysql.php
 */

echo "==============================================\n";
echo "   VERIFICACIÓN DE CONFIGURACIÓN MYSQL\n";
echo "==============================================\n\n";

// Cargar configuración
require_once 'conexion.php';

$errores  = [];
$warnings = [];
$exitos   = [];

// TEST 1: Verificar conexión
echo "TEST 1: Verificando conexión a MySQL...\n";
try {
    if ($conexionPDO) {
        $exitos[] = "✓ Conexión a MySQL establecida correctamente";

        // Obtener versión de MySQL
        $version  = $conexionPDO->query('SELECT VERSION()')->fetchColumn();
        $exitos[] = "✓ Versión de MySQL: $version";
    }
} catch (Exception $e) {
    $errores[] = "✗ Error de conexión: " . $e->getMessage();
}

echo "\n";

// TEST 2: Verificar base de datos
echo "TEST 2: Verificando base de datos 'refugio'...\n";
try {
    $result = $conexionPDO->query("SELECT DATABASE()")->fetchColumn();
    if ($result === 'refugio') {
        $exitos[] = "✓ Base de datos 'refugio' seleccionada correctamente";
    } else {
        $errores[] = "✗ Base de datos incorrecta: $result (esperada: refugio)";
    }
} catch (Exception $e) {
    $errores[] = "✗ Error al verificar BD: " . $e->getMessage();
}

echo "\n";

// TEST 3: Verificar tablas
echo "TEST 3: Verificando tablas del sistema...\n";
$tablas_necesarias = ['usuarios', 'habitaciones', 'camas', 'reservas', 'acompanantes'];
try {
    $stmt              = $conexionPDO->query("SHOW TABLES");
    $tablas_existentes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tablas_necesarias as $tabla) {
        if (in_array($tabla, $tablas_existentes)) {
            $exitos[] = "✓ Tabla '$tabla' existe";
        } else {
            $errores[] = "✗ Tabla '$tabla' NO existe";
        }
    }
} catch (Exception $e) {
    $errores[] = "✗ Error al verificar tablas: " . $e->getMessage();
}

echo "\n";

// TEST 4: Verificar datos de prueba
echo "TEST 4: Verificando datos de prueba...\n";
try {
    // Verificar usuarios
    $stmt           = $conexionPDO->query("SELECT COUNT(*) FROM usuarios");
    $count_usuarios = $stmt->fetchColumn();
    if ($count_usuarios >= 3) {
        $exitos[] = "✓ Usuarios de prueba cargados ($count_usuarios usuarios)";
    } else {
        $warnings[] = "⚠ Solo hay $count_usuarios usuarios (esperados: 3+)";
    }

    // Verificar habitaciones
    $stmt               = $conexionPDO->query("SELECT COUNT(*) FROM habitaciones");
    $count_habitaciones = $stmt->fetchColumn();
    if ($count_habitaciones === '4') {
        $exitos[] = "✓ Habitaciones cargadas correctamente (4 habitaciones)";
    } else {
        $errores[] = "✗ Habitaciones incorrectas (encontradas: $count_habitaciones, esperadas: 4)";
    }

    // Verificar camas
    $stmt        = $conexionPDO->query("SELECT COUNT(*) FROM camas");
    $count_camas = $stmt->fetchColumn();
    if ($count_camas === '26') {
        $exitos[] = "✓ Camas cargadas correctamente (26 camas)";
    } else {
        $errores[] = "✗ Camas incorrectas (encontradas: $count_camas, esperadas: 26)";
    }

} catch (Exception $e) {
    $errores[] = "✗ Error al verificar datos: " . $e->getMessage();
}

echo "\n";

// TEST 5: Verificar usuarios específicos
echo "TEST 5: Verificando usuarios de prueba...\n";
$usuarios_prueba = [
    'admin@hostel.com' => 'admin',
    'user1@mail.com'   => 'user',
    'user2@mail.com'   => 'user',
];

try {
    foreach ($usuarios_prueba as $email => $rol_esperado) {
        $stmt = $conexionPDO->prepare("SELECT email, rol, password FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            if ($usuario['rol'] === $rol_esperado) {
                $exitos[] = "✓ Usuario $email existe con rol correcto ($rol_esperado)";

                // Verificar que la contraseña está hasheada
                if (strlen($usuario['password']) === 60 && substr($usuario['password'], 0, 4) === '$2y$') {
                    $exitos[] = "  └─ Contraseña hasheada correctamente (bcrypt)";
                } else {
                    $errores[] = "  └─ ✗ Contraseña NO está hasheada correctamente";
                }
            } else {
                $errores[] = "✗ Usuario $email tiene rol incorrecto (esperado: $rol_esperado, actual: {$usuario['rol']})";
            }
        } else {
            $errores[] = "✗ Usuario $email NO existe";
        }
    }
} catch (Exception $e) {
    $errores[] = "✗ Error al verificar usuarios: " . $e->getMessage();
}

echo "\n";

// TEST 6: Verificar estructura de columnas
echo "TEST 6: Verificando estructura de tablas...\n";
try {
    // Verificar columnas de usuarios
    $stmt     = $conexionPDO->query("DESCRIBE usuarios");
    $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $columnas_necesarias = ['id', 'num_socio', 'dni', 'email', 'password', 'rol'];
    $faltan              = array_diff($columnas_necesarias, $columnas);

    if (empty($faltan)) {
        $exitos[] = "✓ Estructura de tabla 'usuarios' correcta";
    } else {
        $errores[] = "✗ Faltan columnas en 'usuarios': " . implode(', ', $faltan);
    }

} catch (Exception $e) {
    $errores[] = "✗ Error al verificar estructura: " . $e->getMessage();
}

echo "\n";

// TEST 7: Verificar permisos
echo "TEST 7: Verificando permisos...\n";
try {
    // Intentar hacer un INSERT de prueba
    $conexionPDO->beginTransaction();
    $stmt = $conexionPDO->prepare("INSERT INTO habitaciones (numero, capacidad) VALUES (999, 1)");
    $stmt->execute();
    $conexionPDO->rollBack(); // Deshacer cambios

    $exitos[] = "✓ Permisos de escritura correctos";
} catch (Exception $e) {
    $warnings[] = "⚠ Posible problema de permisos: " . $e->getMessage();
}

echo "\n\n";

// RESUMEN
echo "==============================================\n";
echo "              RESUMEN DE TESTS\n";
echo "==============================================\n\n";

if (! empty($exitos)) {
    echo "✅ ÉXITOS (" . count($exitos) . "):\n";
    foreach ($exitos as $exito) {
        echo "   $exito\n";
    }
    echo "\n";
}

if (! empty($warnings)) {
    echo "⚠️  ADVERTENCIAS (" . count($warnings) . "):\n";
    foreach ($warnings as $warning) {
        echo "   $warning\n";
    }
    echo "\n";
}

if (! empty($errores)) {
    echo "❌ ERRORES (" . count($errores) . "):\n";
    foreach ($errores as $error) {
        echo "   $error\n";
    }
    echo "\n";
}

// CONCLUSIÓN
echo "==============================================\n";
if (empty($errores)) {
    echo "🎉 ¡CONFIGURACIÓN CORRECTA!\n";
    echo "==============================================\n\n";
    echo "Tu sistema está listo para usar.\n";
    echo "Puedes iniciar el servidor con: php -S localhost:8000\n";
    echo "Y acceder a: http://localhost:8000\n\n";
    echo "Credenciales de prueba:\n";
    echo "  Admin: admin@hostel.com / admin123\n";
    echo "  User:  user1@mail.com / user123\n\n";
} else {
    echo "⚠️  SE ENCONTRARON ERRORES\n";
    echo "==============================================\n\n";
    echo "Por favor, revisa los errores arriba.\n";
    echo "Consulta MIGRACION_MYSQL.md para más ayuda.\n\n";

    if (strpos(implode('', $errores), 'Tabla') !== false) {
        echo "💡 Sugerencia: Parece que faltan tablas.\n";
        echo "   Ejecuta: mysql -u root -p refugio < sql\\refugio_mysql.sql\n\n";
    }

    if (strpos(implode('', $errores), 'conexión') !== false) {
        echo "💡 Sugerencia: Problema de conexión.\n";
        echo "   1. Verifica que MySQL esté ejecutándose\n";
        echo "   2. Revisa usuario y contraseña en conexion.php\n\n";
    }
}

echo "==============================================\n";
