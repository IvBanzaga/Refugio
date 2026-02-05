<?php
/**
 * Login - Página de autenticación
 * Actualizado para usar el sistema MVC
 */

// Cargar bootstrap
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/functions.php';

// Si ya está autenticado, redirigir según rol
if (isset($_SESSION['userId'])) {
    if ($_SESSION['rol'] === 'admin') {
        redirect('viewAdminMVC.php');
    } else {
        redirect('viewSocioMVC.php');
    }
}

/* TODO: Procesamiento de login. Se usa password_verify para comprobar la contraseña cifrada y session_regenerate_id(true) para evitar robo de sesión. Depuración: puedes poner breakpoint aquí para comprobar los datos recibidos y el resultado de la autenticación. */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? $_POST['user'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validar reCAPTCHA v2
    $captcha_valid = false;
    if (isset($_POST['g-recaptcha-response']) && ! empty($_POST['g-recaptcha-response'])) {
        $captcha_response = $_POST['g-recaptcha-response'];
        $secret_key       = '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe'; // Clave de prueba de Google

        $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
        $data       = [
            'secret'   => $secret_key,
            'response' => $captcha_response,
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ],
        ];

        $context = stream_context_create($options);
        $result  = @file_get_contents($verify_url, false, $context);

        if ($result !== false) {
            $response_data = json_decode($result);
            $captcha_valid = isset($response_data->success) && $response_data->success === true;
        }
    }

    if (! $captcha_valid) {
        $_SESSION['error'] = 'Por favor, completa la verificación de seguridad';
    } else {
        $user = comprobar_username($conexionPDO, $email);

        /* TODO: Verificación segura de contraseña y gestión de sesión. Depuración: breakpoint útil para comprobar el array $user y el resultado de password_verify. */
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true); // Justificación: previene ataques de fijación de sesión
            $_SESSION['userId'] = $user['id'];
            $_SESSION['user']   = htmlspecialchars($user['nombre'] . ' ' . $user['apellido1']);
            $_SESSION['email']  = htmlspecialchars($email);
            $_SESSION['rol']    = $user['rol'];

            // Guardar fecha de última visita por rol en cookie y el id de usuario
            setcookie('ultima_visita_' . $user['rol'], date('Y-m-d H:i:s'), time() + 365 * 24 * 3600, '/', '', false, true);
            setcookie('ultima_visita_' . $user['rol'] . '_id', $user['id'], time() + 365 * 24 * 3600, '/', '', false, true);

            /* TODO: Redirección según rol. Depuración: breakpoint útil para comprobar el valor de $user['rol']. */
            if ($user['rol'] === 'user') {
                redirect('viewSocioMVC.php');
            } else if ($user['rol'] === 'admin') {
                redirect('viewAdminMVC.php');
            }
            exit;
        } else {
            $_SESSION['error'] = 'Credenciales inválidas';
        }
    }

    // Redirigir con PRG pattern
    redirect('login.php');
}

// Cargar la vista de login
include VIEWS_PATH . '/auth/login.php';
