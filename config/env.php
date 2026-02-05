<?php
/**
 * Carga de Variables de Entorno
 *
 * Este archivo debe ser el primero en cargarse en bootstrap.php
 * para que las variables estén disponibles en app.php y otros archivos
 */

// Cargar variables de entorno desde .env
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorar comentarios y líneas vacías
        if (strpos(trim($line), '#') === 0 || empty(trim($line))) {
            continue;
        }
        // Parsear línea KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key               = trim($key);
            $value             = trim($value);
            if (! empty($key)) {
                $_ENV[$key] = $value;
            }
        }
    }
}
