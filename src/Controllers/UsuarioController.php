<?php
/**
 * UsuarioController - Controlador de Usuarios
 *
 * Maneja todas las acciones relacionadas con usuarios:
 * - Crear
 * - Actualizar
 * - Eliminar
 * - Cambiar contraseña
 * - Exportar (CSV/PDF)
 */

class UsuarioController
{
    private $conexion;

    /**
     * Constructor
     * @param PDO $conexion Conexión a la base de datos
     */
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * Crear nuevo usuario
     */
    public function crearUsuario()
    {
        try {
            // Validar datos requeridos
            $campos_requeridos = ['num_socio', 'dni', 'nombre', 'apellido1', 'email', 'password', 'rol'];
            foreach ($campos_requeridos as $campo) {
                if (empty($_POST[$campo])) {
                    throw new Exception("El campo {$campo} es requerido");
                }
            }

            $num_socio = trim($_POST['num_socio']);
            $dni       = trim($_POST['dni']);
            $nombre    = trim($_POST['nombre']);
            $apellido1 = trim($_POST['apellido1']);
            $apellido2 = trim($_POST['apellido2'] ?? '');
            $email     = trim($_POST['email']);
            $password  = $_POST['password'];
            $rol       = $_POST['rol'];
            $telf      = trim($_POST['telf'] ?? '');

            // Validar email
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email inválido');
            }

            // Validar rol
            if (! in_array($rol, ['admin', 'user'])) {
                throw new Exception('Rol inválido');
            }

            // Verificar que el número de socio no exista
            $stmt = $this->conexion->prepare("SELECT id FROM usuarios WHERE num_socio = :num_socio");
            $stmt->execute([':num_socio' => $num_socio]);
            if ($stmt->fetch()) {
                throw new Exception('El número de socio ya está registrado');
            }

            // Verificar que el email no exista
            $stmt = $this->conexion->prepare("SELECT id FROM usuarios WHERE email = :email");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                throw new Exception('El email ya está registrado');
            }

            // Hash de la contraseña
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insertar usuario
            $stmt = $this->conexion->prepare("
                INSERT INTO usuarios (num_socio, dni, nombre, apellido1, apellido2, email, password, telf, rol)
                VALUES (:num_socio, :dni, :nombre, :apellido1, :apellido2, :email, :password, :telf, :rol)
            ");

            $stmt->execute([
                ':num_socio' => $num_socio,
                ':dni'       => $dni,
                ':nombre'    => $nombre,
                ':apellido1' => $apellido1,
                ':apellido2' => $apellido2,
                ':email'     => $email,
                ':password'  => $password_hash,
                ':telf'      => $telf,
                ':rol'       => $rol,
            ]);

            $_SESSION['mensaje']      = 'Usuario creado exitosamente.';
            $_SESSION['tipo_mensaje'] = 'success';

        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al crear usuario: ' . $e->getMessage();
        }

        redirect('viewAdminMVC.php?accion=usuarios');
    }

    /**
     * Actualizar usuario existente
     */
    public function actualizarUsuario()
    {
        try {
            $id = (int) $_POST['id'];

            // Validar datos requeridos
            $campos_requeridos = ['num_socio', 'dni', 'nombre', 'apellido1', 'email', 'rol'];
            foreach ($campos_requeridos as $campo) {
                if (empty($_POST[$campo])) {
                    throw new Exception("El campo {$campo} es requerido");
                }
            }

            $num_socio = trim($_POST['num_socio']);
            $dni       = trim($_POST['dni']);
            $nombre    = trim($_POST['nombre']);
            $apellido1 = trim($_POST['apellido1']);
            $apellido2 = trim($_POST['apellido2'] ?? '');
            $email     = trim($_POST['email']);
            $rol       = $_POST['rol'];
            $telf      = trim($_POST['telf'] ?? '');
            $password  = $_POST['password'] ?? '';

            // Validar email
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email inválido');
            }

            // Validar rol
            if (! in_array($rol, ['admin', 'user'])) {
                throw new Exception('Rol inválido');
            }

            // Verificar que el usuario exista
            $stmt = $this->conexion->prepare("SELECT * FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $usuario_actual = $stmt->fetch(PDO::FETCH_ASSOC);

            if (! $usuario_actual) {
                throw new Exception('Usuario no encontrado');
            }

            // Proteger usuario admin principal
            if ($usuario_actual['email'] === 'admin@hostel.com' && $email !== 'admin@hostel.com') {
                throw new Exception('No se puede modificar el email del administrador principal');
            }

            // Verificar que el número de socio no esté duplicado
            $stmt = $this->conexion->prepare("
                SELECT id FROM usuarios
                WHERE num_socio = :num_socio AND id != :id
            ");
            $stmt->execute([
                ':num_socio' => $num_socio,
                ':id'        => $id,
            ]);
            if ($stmt->fetch()) {
                throw new Exception('El número de socio ya está registrado');
            }

            // Verificar que el email no esté duplicado
            $stmt = $this->conexion->prepare("
                SELECT id FROM usuarios
                WHERE email = :email AND id != :id
            ");
            $stmt->execute([
                ':email' => $email,
                ':id'    => $id,
            ]);
            if ($stmt->fetch()) {
                throw new Exception('El email ya está registrado');
            }

            // Preparar actualización
            if (! empty($password)) {
                // Si se proporciona contraseña, actualizarla
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $this->conexion->prepare("
                    UPDATE usuarios
                    SET num_socio = :num_socio,
                        dni = :dni,
                        nombre = :nombre,
                        apellido1 = :apellido1,
                        apellido2 = :apellido2,
                        email = :email,
                        password = :password,
                        telf = :telf,
                        rol = :rol
                    WHERE id = :id
                ");

                $stmt->execute([
                    ':num_socio' => $num_socio,
                    ':dni'       => $dni,
                    ':nombre'    => $nombre,
                    ':apellido1' => $apellido1,
                    ':apellido2' => $apellido2,
                    ':email'     => $email,
                    ':password'  => $password_hash,
                    ':telf'      => $telf,
                    ':rol'       => $rol,
                    ':id'        => $id,
                ]);
            } else {
                // Si no se proporciona contraseña, no actualizarla
                $stmt = $this->conexion->prepare("
                    UPDATE usuarios
                    SET num_socio = :num_socio,
                        dni = :dni,
                        nombre = :nombre,
                        apellido1 = :apellido1,
                        apellido2 = :apellido2,
                        email = :email,
                        telf = :telf,
                        rol = :rol
                    WHERE id = :id
                ");

                $stmt->execute([
                    ':num_socio' => $num_socio,
                    ':dni'       => $dni,
                    ':nombre'    => $nombre,
                    ':apellido1' => $apellido1,
                    ':apellido2' => $apellido2,
                    ':email'     => $email,
                    ':telf'      => $telf,
                    ':rol'       => $rol,
                    ':id'        => $id,
                ]);
            }

            $_SESSION['mensaje']      = 'Usuario actualizado exitosamente.';
            $_SESSION['tipo_mensaje'] = 'success';

        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al actualizar usuario: ' . $e->getMessage();
        }

        redirect('viewAdminMVC.php?accion=usuarios');
    }

    /**
     * Eliminar usuario
     */
    public function eliminarUsuario()
    {
        try {
            $id = (int) $_POST['id'];

            // Verificar que el usuario exista
            $stmt = $this->conexion->prepare("SELECT * FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (! $usuario) {
                throw new Exception('Usuario no encontrado');
            }

            // Proteger usuario admin principal
            if ($usuario['email'] === 'admin@hostel.com') {
                throw new Exception('No se puede eliminar el administrador principal');
            }

            // Verificar si tiene reservas activas
            $stmt = $this->conexion->prepare("
                SELECT COUNT(*) as total
                FROM reservas
                WHERE id_usuario = :id AND estado IN ('pendiente', 'reservada')
            ");
            $stmt->execute([':id' => $id]);
            $reservas_activas = $stmt->fetchColumn();

            if ($reservas_activas > 0) {
                throw new Exception('No se puede eliminar el usuario porque tiene reservas activas');
            }

            // Eliminar usuario
            $stmt = $this->conexion->prepare("DELETE FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $id]);

            $_SESSION['mensaje']      = 'Usuario eliminado exitosamente.';
            $_SESSION['tipo_mensaje'] = 'success';

        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al eliminar usuario: ' . $e->getMessage();
        }

        redirect('viewAdminMVC.php?accion=usuarios');
    }

    /**
     * Cambiar contraseña del usuario actual
     */
    public function cambiarContrasena()
    {
        try {
            $id                    = $_SESSION['userId'];
            $password_actual       = $_POST['password_actual'];
            $password_nueva        = $_POST['password_nueva'];
            $password_confirmacion = $_POST['password_confirmacion'];

            // Validar que las contraseñas coincidan
            if ($password_nueva !== $password_confirmacion) {
                throw new Exception('Las contraseñas nuevas no coinciden');
            }

            // Validar longitud mínima
            if (strlen($password_nueva) < 6) {
                throw new Exception('La contraseña debe tener al menos 6 caracteres');
            }

            // Obtener usuario actual
            $stmt = $this->conexion->prepare("SELECT password FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (! $usuario) {
                throw new Exception('Usuario no encontrado');
            }

            // Verificar contraseña actual
            if (! password_verify($password_actual, $usuario['password'])) {
                throw new Exception('La contraseña actual es incorrecta');
            }

            // Actualizar contraseña
            $password_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
            $stmt          = $this->conexion->prepare("
                UPDATE usuarios
                SET password = :password
                WHERE id = :id
            ");
            $stmt->execute([
                ':password' => $password_hash,
                ':id'       => $id,
            ]);

            $_SESSION['mensaje']      = 'Contraseña actualizada exitosamente.';
            $_SESSION['tipo_mensaje'] = 'success';

        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al cambiar contraseña: ' . $e->getMessage();
        }

        // Redirigir según el rol
        if ($_SESSION['rol'] === 'admin') {
            redirect('viewAdminMVC.php?accion=perfil');
        } else {
            redirect('viewSocioMVC.php?accion=perfil');
        }
    }

    /**
     * Actualizar perfil del usuario actual
     */
    public function actualizarPerfil()
    {
        try {
            $id = $_SESSION['userId'];

            // Campos permitidos para edición de perfil
            $nombre    = trim($_POST['nombre']);
            $apellido1 = trim($_POST['apellido1']);
            $apellido2 = trim($_POST['apellido2'] ?? '');
            $telf      = trim($_POST['telf'] ?? '');
            $email     = trim($_POST['email']);

            // Validar email
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email inválido');
            }

            // Verificar que el email no esté duplicado
            $stmt = $this->conexion->prepare("
                SELECT id FROM usuarios
                WHERE email = :email AND id != :id
            ");
            $stmt->execute([
                ':email' => $email,
                ':id'    => $id,
            ]);
            if ($stmt->fetch()) {
                throw new Exception('El email ya está registrado');
            }

            // Actualizar perfil
            $stmt = $this->conexion->prepare("
                UPDATE usuarios
                SET nombre = :nombre,
                    apellido1 = :apellido1,
                    apellido2 = :apellido2,
                    email = :email,
                    telf = :telf
                WHERE id = :id
            ");

            $stmt->execute([
                ':nombre'    => $nombre,
                ':apellido1' => $apellido1,
                ':apellido2' => $apellido2,
                ':email'     => $email,
                ':telf'      => $telf,
                ':id'        => $id,
            ]);

            // Actualizar sesión
            $_SESSION['user']  = htmlspecialchars($nombre . ' ' . $apellido1);
            $_SESSION['email'] = htmlspecialchars($email);

            $_SESSION['mensaje']      = 'Perfil actualizado exitosamente.';
            $_SESSION['tipo_mensaje'] = 'success';

        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al actualizar perfil: ' . $e->getMessage();
        }

        // Redirigir según el rol
        if ($_SESSION['rol'] === 'admin') {
            redirect('viewAdminMVC.php?accion=perfil');
        } else {
            redirect('viewSocioMVC.php?accion=perfil');
        }
    }
}
