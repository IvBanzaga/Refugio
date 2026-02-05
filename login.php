<?php
    /**
 * Login - Página de autenticación
 * Actualizado para usar el sistema MVC
 */

    // Cargar dependencias
    require_once __DIR__ . '/conexion.php';
    require_once __DIR__ . '/functions.php';

    // Si ya está autenticado, redirigir según rol
    if (isset($_SESSION['userId'])) {
    if ($_SESSION['rol'] === 'admin') {
        header('Location: viewAdmin.php');
    } else {
        header('Location: viewSocio.php');
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
                header('Location: viewSocio.php');
            } else if ($user['rol'] === 'admin') {
                header('Location: viewAdmin.php');
            }
            exit;
        } else {
            $_SESSION['error'] = 'Credenciales inválidas';
        }
    }

    // Redirigir con PRG pattern
    header('Location: login.php');
    exit;
    }

    // Recuperar mensajes de la sesión
    $error = $_SESSION['error'] ?? '';
    unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Refugio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        body {
            background-color: #f5f5f5;
            padding-top: 40px;
            padding-bottom: 40px;
        }
        .form-signin {
            max-width: 400px;
            padding: 15px;
            margin: 0 auto;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="form-signin">
        <div class="card">
            <div class="card-body p-4">
                <h3 class="text-center mb-4">🏔️ Refugio de Montaña</h3>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="d-flex justify-content-center mb-3">
                        <div class="g-recaptcha" data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"></div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
