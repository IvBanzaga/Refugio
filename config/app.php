<?php
/**
 * Configuración General de la Aplicación
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de zona horaria
date_default_timezone_set('Atlantic/Canary');

// Configuración de errores (según entorno)
$isProduction = ($_ENV['APP_ENV'] ?? 'development') === 'production';

if ($isProduction) {
    error_reporting(0);
    ini_set('display_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Rutas base
define('BASE_PATH', dirname(__DIR__));
define('CONFIG_PATH', BASE_PATH . '/config');
define('SRC_PATH', BASE_PATH . '/src');
define('VIEWS_PATH', BASE_PATH . '/views');
define('PUBLIC_PATH', BASE_PATH . '/public');

// URL base
define('BASE_URL', $_ENV['BASE_URL'] ?? 'http://localhost/refugio');

// Configuración de subida de archivos
define('UPLOAD_DIR', PUBLIC_PATH . '/uploads');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Configuración de reservas
define('MAX_CAMAS_HABITACION', 26);
define('DIAS_ANTELACION_MINIMA', 0); // Mínimo de días de antelación para reservar

// Roles de usuario
define('ROLE_ADMIN', 'admin');
define('ROLE_USER', 'user');
