<?php
/**
 * Archivo de Configuración del Sistema
 * Copiar este archivo como config.php y ajustar los valores
 */

// ============================================
// CONFIGURACIÓN DE BASE DE DATOS
// ============================================

// Tipo de base de datos: 'pgsql' o 'mysql'
define('DB_TYPE', 'pgsql'); // Cambiar a 'mysql' si usas MySQL

// PostgreSQL
define('DB_HOST_PGSQL', 'localhost');
define('DB_PORT_PGSQL', '5432');
define('DB_NAME_PGSQL', 'refugio');
define('DB_USER_PGSQL', 'postgres');
define('DB_PASS_PGSQL', '123456');

// MySQL
define('DB_HOST_MYSQL', 'localhost');
define('DB_PORT_MYSQL', '3306');
define('DB_NAME_MYSQL', 'refugio');
define('DB_USER_MYSQL', 'root');
define('DB_PASS_MYSQL', '123456');

// ============================================
// CONFIGURACIÓN DE SESIÓN
// ============================================

define('SESSION_LIFETIME', 7200); // 2 horas en segundos
define('SESSION_NAME', 'REFUGIO_SESSION');

// ============================================
// CONFIGURACIÓN DE LA APLICACIÓN
// ============================================

define('APP_NAME', 'Refugio del Club');
define('APP_VERSION', '1.0.0');
define('APP_TIMEZONE', 'Europe/Madrid');

// Modo de depuración
define('DEBUG_MODE', true); // Cambiar a false en producción

// ============================================
// CONFIGURACIÓN DE RESERVAS
// ============================================

define('TOTAL_CAMAS', 26);
define('DIAS_MAX_RESERVA', 14);  // Máximo de días por reserva
define('DIAS_ANTICIPACION', 90); // Días de anticipación para reservar

// ============================================
// FUNCIONES AUXILIARES
// ============================================

function get_db_config()
{
    if (DB_TYPE === 'pgsql') {
        return [
            'dsn'      => 'pgsql:host=' . DB_HOST_PGSQL . ';port=' . DB_PORT_PGSQL . ';dbname=' . DB_NAME_PGSQL,
            'username' => DB_USER_PGSQL,
            'password' => DB_PASS_PGSQL,
        ];
    } else {
        return [
            'dsn'      => 'mysql:host=' . DB_HOST_MYSQL . ';port=' . DB_PORT_MYSQL . ';dbname=' . DB_NAME_MYSQL . ';charset=utf8mb4',
            'username' => DB_USER_MYSQL,
            'password' => DB_PASS_MYSQL,
        ];
    }
}
