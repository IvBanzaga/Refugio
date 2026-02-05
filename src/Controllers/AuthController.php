<?php
namespace Controllers;

use Models\Usuario;

/**
 * Controlador de Autenticación
 *
 * Gestiona login, logout y verificación de sesiones
 */
class AuthController
{

    /**
     * Mostrar formulario de login
     */
    public function showLogin()
    {
        // Si ya está autenticado, redirigir al dashboard
        if (isAuthenticated()) {
            $this->redirectToDashboard();
        }

        view('auth/login');
    }

    /**
     * Procesar login
     */
    public function login()
    {
        $email    = post('email');
        $password = post('password');

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Email y contraseña son requeridos';
            redirect('login.php');
            return;
        }

        // Intentar autenticar
        $usuario = Usuario::authenticate($email, $password);

        if (! $usuario) {
            $_SESSION['error'] = 'Credenciales incorrectas';
            redirect('login.php');
            return;
        }

        // Crear sesión
        $this->createSession($usuario);

        // Redirigir según rol
        $this->redirectToDashboard();
    }

    /**
     * Cerrar sesión
     */
    public function logout()
    {
        // Destruir todas las variables de sesión
        $_SESSION = [];

        // Destruir la cookie de sesión
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Destruir la sesión
        session_destroy();

        // Redirigir al login
        redirect('login.php');
    }

    /**
     * Verificar si el usuario está autenticado (middleware)
     */
    public function requireAuth()
    {
        if (! isAuthenticated()) {
            $_SESSION['error'] = 'Debe iniciar sesión para acceder';
            redirect('login.php');
        }
    }

    /**
     * Verificar si el usuario es admin (middleware)
     */
    public function requireAdmin()
    {
        $this->requireAuth();

        if (! isAdmin()) {
            $_SESSION['error'] = 'No tiene permisos para acceder a esta sección';
            redirect('index.php');
        }
    }

    /**
     * Crear sesión de usuario
     *
     * @param array $usuario
     */
    private function createSession($usuario)
    {
        // Regenerar ID de sesión por seguridad
        session_regenerate_id(true);

        // Guardar datos del usuario en sesión
        $_SESSION['usuario_id']     = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'] . ' ' . $usuario['apellido1'];
        $_SESSION['usuario_email']  = $usuario['email'];
        $_SESSION['rol']            = $usuario['rol'];
        $_SESSION['num_socio']      = $usuario['num_socio'] ?? null;
        $_SESSION['foto']           = $usuario['foto'] ?? null;
        $_SESSION['login_time']     = time();
    }

    /**
     * Redirigir al dashboard según rol
     */
    private function redirectToDashboard()
    {
        if (isAdmin()) {
            redirect('viewAdmin.php');
        } else {
            redirect('viewSocio.php');
        }
    }

    /**
     * Obtener usuario autenticado actual
     *
     * @return array|null
     */
    public static function user()
    {
        if (! isAuthenticated()) {
            return null;
        }

        return Usuario::find($_SESSION['usuario_id']);
    }

    /**
     * Cambiar contraseña
     *
     * @param int $userId
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     */
    public function changePassword($userId, $currentPassword, $newPassword)
    {
        $usuario = Usuario::find($userId);

        if (! $usuario) {
            return false;
        }

        // Verificar contraseña actual
        if (! password_verify($currentPassword, $usuario['password'])) {
            $_SESSION['error'] = 'La contraseña actual es incorrecta';
            return false;
        }

        // Validar nueva contraseña
        if (strlen($newPassword) < 6) {
            $_SESSION['error'] = 'La nueva contraseña debe tener al menos 6 caracteres';
            return false;
        }

        // Actualizar contraseña
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        if (Usuario::update($userId, ['password' => $hashedPassword])) {
            $_SESSION['mensaje'] = 'Contraseña actualizada correctamente';
            return true;
        }

        $_SESSION['error'] = 'Error al actualizar la contraseña';
        return false;
    }
}
