<?php

    /*
    Solicita un usuario y contraseña y comprueba que esta es correcta, si lo es, redirije la aplicación a usuarios.php
*/

    require 'conexion.php';
    require 'functions.php';

    /* TODO: Procesamiento de login. Se usa password_verify para comprobar la contraseña cifrada y session_regenerate_id(true) para evitar robo de sesión. Depuración: puedes poner breakpoint aquí para comprobar los datos recibidos y el resultado de la autenticación. */

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['user']);
    $password = trim($_POST['password']);

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
        $error = 'Por favor, completa la verificación de seguridad';
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
            $error = 'Credenciales inválidas';
        }
    }
    }
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🏔️</text></svg>">
  <title>Acceso a la aplicación</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <style>
    .captcha-container {
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 20px 0;
    }
  </style>
</head>

<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-sm-10 col-md-6 col-lg-5">
        <div class="card shadow">
          <div class="card-header bg-primary text-white text-center">
            <h2>🏔️ Refugio del Club</h2>
            <p class="mb-0">Control de Reservas de Camas</p>
          </div>
          <div class="card-body">
            <?php if (! empty($error)) {
                    echo "<div class='alert alert-danger text-center'>$error</div>";
                }
            ?>
            <form method="post">
              <div class="mb-3">
                <label for="user" class="form-label">Email:</label>
                <input type="email" class="form-control" id="user" name="user" placeholder="correo electronico" required>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Contraseña:</label>
                <input type="password" class="form-control" id="password" name="password" required>
              </div>

              <!-- reCAPTCHA v2 widget -->
              <div class="captcha-container">
                <div class="g-recaptcha" data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"></div>
              </div>

              <div class="d-grid">
                <button type="submit" class="btn btn-primary">Acceder</button>
              </div>
            </form>
            <div class="mt-3 text-muted text-center small">
              <p class="mb-0">Usuario de prueba Admin: admin@hostel.com</p>
              <p class="mb-0">Usuario de prueba User: user1@mail.com</p>
              <p class="mb-0">Contraseña : admin123 o user123</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

</html>
