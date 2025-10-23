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
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <title>Acceso a la aplicación</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-5">
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
                <input type="email" class="form-control" id="user" name="user" placeholder="usuario@example.com" required>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Contraseña:</label>
                <input type="password" class="form-control" id="password" name="password" required>
              </div>
              <div class="d-grid">
                <button type="submit" class="btn btn-primary">Acceder</button>
              </div>
            </form>
            <div class="mt-3 text-muted text-center small">
              <p class="mb-0">Usuario de prueba Admin: admin@hostel.com</p>
              <p class="mb-0">Usuario de prueba User: user1@mail.com</p>
              <p class="mb-0">Contraseña para ambos: admin123 o user123</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

</html>
