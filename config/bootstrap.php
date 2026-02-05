<?php
/**
 * Bootstrap - Inicialización de la aplicación
 *
 * Carga todas las configuraciones y dependencias necesarias
 */

// Cargar configuraciones
require_once __DIR__ . '/app.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/email.php';

// Cargar helpers
require_once SRC_PATH . '/Helpers/functions.php';

// Autoload de Composer
require_once BASE_PATH . '/vendor/autoload.php';

/**
 * Función helper para cargar vistas
 *
 * @param string $view Ruta relativa de la vista desde views/
 * @param array $data Datos a pasar a la vista
 */
function view($view, $data = [])
{
    extract($data);
    $viewPath = VIEWS_PATH . '/' . $view . '.php';

    if (file_exists($viewPath)) {
        require $viewPath;
    } else {
        throw new Exception("Vista no encontrada: {$view}");
    }
}

/**
 * Función helper para redireccionar
 *
 * @param string $url URL relativa o absoluta
 * @param int $statusCode Código de estado HTTP
 */
function redirect($url, $statusCode = 302)
{
    if (! preg_match('/^https?:\/\//', $url)) {
        $url = BASE_URL . '/' . ltrim($url, '/');
    }
    header("Location: {$url}", true, $statusCode);
    exit;
}

/**
 * Función helper para obtener datos POST de forma segura
 *
 * @param string $key Clave del dato
 * @param mixed $default Valor por defecto
 * @return mixed
 */
function post($key, $default = null)
{
    return $_POST[$key] ?? $default;
}

/**
 * Función helper para obtener datos GET de forma segura
 *
 * @param string $key Clave del dato
 * @param mixed $default Valor por defecto
 * @return mixed
 */
function get($key, $default = null)
{
    return $_GET[$key] ?? $default;
}

/**
 * Función helper para verificar si el usuario está autenticado
 *
 * @return bool
 */
function isAuthenticated()
{
    return isset($_SESSION['usuario_id']);
}

/**
 * Función helper para verificar si el usuario es admin
 *
 * @return bool
 */
function isAdmin()
{
    return isset($_SESSION['rol']) && $_SESSION['rol'] === ROLE_ADMIN;
}

/**
 * Función helper para requerir autenticación
 */
function requireAuth()
{
    if (! isAuthenticated()) {
        redirect('login.php');
    }
}

/**
 * Función helper para requerir rol de admin
 */
function requireAdmin()
{
    requireAuth();
    if (! isAdmin()) {
        redirect('index.php');
    }
}
