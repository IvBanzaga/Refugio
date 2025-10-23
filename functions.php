<?php

/* ==================================================
   FUNCIONES DE AUTENTICACIÓN
   ================================================== */

/**
 * Comprobar usuario por email
 * @param PDO $conexion
 * @param string $email
 * @return array|false
 */
function comprobar_username($conexion, $email)
{
    try {
        $stmt = $conexion->prepare("SELECT id, email, password, rol, nombre, apellido1 FROM usuarios WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ? $user : false;
    } catch (PDOException $e) {
        error_log("Error al comprobar usuario: " . $e->getMessage());
        return false;
    }
}

/* ==================================================
   FUNCIONES DE USUARIOS
   ================================================== */

/**
 * Listar todos los usuarios
 * @param PDO $conexion
 * @return array
 */
function listar_usuarios($conexion)
{
    try {
        $stmt = $conexion->query("SELECT id, num_socio, dni, telf, email, nombre, apellido1, apellido2, rol FROM usuarios ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al listar usuarios: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtener usuario por ID
 * @param PDO $conexion
 * @param int $id
 * @return array|false
 */
function obtener_usuario($conexion, $id)
{
    try {
        $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener usuario: " . $e->getMessage());
        return false;
    }
}

/**
 * Crear nuevo usuario
 * @param PDO $conexion
 * @param array $datos
 * @return bool
 */
function crear_usuario($conexion, $datos)
{
    try {
        $password_hash = password_hash($datos['password'], PASSWORD_BCRYPT);

        $stmt = $conexion->prepare("
            INSERT INTO usuarios (num_socio, dni, telf, email, nombre, apellido1, apellido2, password, rol)
            VALUES (:num_socio, :dni, :telf, :email, :nombre, :apellido1, :apellido2, :password, :rol)
        ");

        $stmt->bindParam(':num_socio', $datos['num_socio']);
        $stmt->bindParam(':dni', $datos['dni']);
        $stmt->bindParam(':telf', $datos['telf']);
        $stmt->bindParam(':email', $datos['email']);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':apellido1', $datos['apellido1']);
        $stmt->bindParam(':apellido2', $datos['apellido2']);
        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':rol', $datos['rol']);

        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error al crear usuario: " . $e->getMessage());
        return false;
    }
}

/**
 * Actualizar usuario
 * @param PDO $conexion
 * @param int $id
 * @param array $datos
 * @return bool
 */
function actualizar_usuario($conexion, $id, $datos)
{
    try {
        if (! empty($datos['password'])) {
            $password_hash = password_hash($datos['password'], PASSWORD_BCRYPT);
            $stmt          = $conexion->prepare("
                UPDATE usuarios
                SET num_socio = :num_socio, dni = :dni, telf = :telf, email = :email,
                    nombre = :nombre, apellido1 = :apellido1, apellido2 = :apellido2,
                    password = :password, rol = :rol
                WHERE id = :id
            ");
            $stmt->bindParam(':password', $password_hash);
        } else {
            $stmt = $conexion->prepare("
                UPDATE usuarios
                SET num_socio = :num_socio, dni = :dni, telf = :telf, email = :email,
                    nombre = :nombre, apellido1 = :apellido1, apellido2 = :apellido2, rol = :rol
                WHERE id = :id
            ");
        }

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':num_socio', $datos['num_socio']);
        $stmt->bindParam(':dni', $datos['dni']);
        $stmt->bindParam(':telf', $datos['telf']);
        $stmt->bindParam(':email', $datos['email']);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':apellido1', $datos['apellido1']);
        $stmt->bindParam(':apellido2', $datos['apellido2']);
        $stmt->bindParam(':rol', $datos['rol']);

        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error al actualizar usuario: " . $e->getMessage());
        return false;
    }
}

/**
 * Eliminar usuario
 * @param PDO $conexion
 * @param int $id
 * @return bool
 */
function eliminar_usuario($conexion, $id)
{
    try {
        $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error al eliminar usuario: " . $e->getMessage());
        return false;
    }
}

/* ==================================================
   FUNCIONES DE HABITACIONES Y CAMAS
   ================================================== */

/**
 * Listar habitaciones con sus camas
 * @param PDO $conexion
 * @return array
 */
function listar_habitaciones($conexion)
{
    try {
        $stmt = $conexion->query("
            SELECT h.id, h.numero, h.capacidad,
                   COUNT(c.id) as total_camas,
                   COUNT(CASE WHEN c.estado = 'libre' THEN 1 END) as camas_libres
            FROM habitaciones h
            LEFT JOIN camas c ON h.id = c.id_habitacion
            GROUP BY h.id, h.numero, h.capacidad
            ORDER BY h.numero
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al listar habitaciones: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtener disponibilidad de camas por rango de fechas
 * @param PDO $conexion
 * @param string $fecha_inicio
 * @param string $fecha_fin
 * @return array
 */
function obtener_disponibilidad($conexion, $fecha_inicio, $fecha_fin)
{
    try {
        $stmt = $conexion->prepare("
            SELECT c.id, c.numero, c.id_habitacion, h.numero as habitacion_numero,
                   CASE
                       WHEN EXISTS (
                           SELECT 1 FROM reservas r
                           WHERE r.id_cama = c.id
                           AND r.estado != 'cancelada'
                           AND (r.fecha_inicio <= :fecha_fin AND r.fecha_fin >= :fecha_inicio)
                       ) THEN 'ocupada'
                       ELSE 'libre'
                   END as disponibilidad
            FROM camas c
            JOIN habitaciones h ON c.id_habitacion = h.id
            ORDER BY h.numero, c.numero
        ");

        $stmt->bindParam(':fecha_inicio', $fecha_inicio);
        $stmt->bindParam(':fecha_fin', $fecha_fin);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener disponibilidad: " . $e->getMessage());
        return [];
    }
}

/**
 * Contar camas libres por fecha
 * @param PDO $conexion
 * @param string $fecha
 * @return int
 */
function contar_camas_libres_por_fecha($conexion, $fecha)
{
    try {
        $stmt = $conexion->prepare("
            SELECT COUNT(*) as libres
            FROM camas c
            WHERE NOT EXISTS (
                SELECT 1 FROM reservas r
                WHERE r.id_cama = c.id
                AND r.estado != 'cancelada'
                AND :fecha BETWEEN r.fecha_inicio AND r.fecha_fin
            )
        ");

        $stmt->bindParam(':fecha', $fecha);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) $result['libres'];
    } catch (PDOException $e) {
        error_log("Error al contar camas libres: " . $e->getMessage());
        return 0;
    }
}

/* ==================================================
   FUNCIONES DE RESERVAS
   ================================================== */

/**
 * Listar reservas (con filtros opcionales)
 * @param PDO $conexion
 * @param array $filtros (estado, id_usuario, fecha_inicio, fecha_fin)
 * @return array
 */
function listar_reservas($conexion, $filtros = [])
{
    try {
        $sql = "
            SELECT r.id, r.fecha_inicio, r.fecha_fin, r.estado, r.fecha_creacion,
                   u.nombre, u.apellido1, u.apellido2, u.num_socio, u.email,
                   c.numero as cama_numero, h.numero as habitacion_numero
            FROM reservas r
            JOIN usuarios u ON r.id_usuario = u.id
            JOIN camas c ON r.id_cama = c.id
            JOIN habitaciones h ON c.id_habitacion = h.id
            WHERE 1=1
        ";

        $params = [];

        if (isset($filtros['estado'])) {
            $sql .= " AND r.estado = :estado";
            $params[':estado'] = $filtros['estado'];
        }

        if (isset($filtros['id_usuario'])) {
            $sql .= " AND r.id_usuario = :id_usuario";
            $params[':id_usuario'] = $filtros['id_usuario'];
        }

        if (isset($filtros['fecha_inicio'])) {
            $sql .= " AND r.fecha_inicio >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }

        if (isset($filtros['fecha_fin'])) {
            $sql .= " AND r.fecha_fin <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }

        $sql .= " ORDER BY r.fecha_creacion DESC";

        $stmt = $conexion->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al listar reservas: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtener reserva por ID con acompañantes
 * @param PDO $conexion
 * @param int $id
 * @return array|false
 */
function obtener_reserva($conexion, $id)
{
    try {
        $stmt = $conexion->prepare("
            SELECT r.*,
                   u.nombre as usuario_nombre, u.apellido1 as usuario_apellido1,
                   u.num_socio, u.email,
                   c.numero as cama_numero, h.numero as habitacion_numero
            FROM reservas r
            JOIN usuarios u ON r.id_usuario = u.id
            JOIN camas c ON r.id_cama = c.id
            JOIN habitaciones h ON c.id_habitacion = h.id
            WHERE r.id = :id
        ");

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($reserva) {
            // Obtener acompañantes
            $reserva['acompanantes'] = obtener_acompanantes($conexion, $id);
        }

        return $reserva;
    } catch (PDOException $e) {
        error_log("Error al obtener reserva: " . $e->getMessage());
        return false;
    }
}

/**
 * Crear nueva reserva
 * @param PDO $conexion
 * @param array $datos
 * @return int|false ID de la reserva creada o false
 */
function crear_reserva($conexion, $datos)
{
    try {
        $conexion->beginTransaction();

        // Insertar reserva
        $stmt = $conexion->prepare("
            INSERT INTO reservas (id_usuario, id_cama, fecha_inicio, fecha_fin, estado)
            VALUES (:id_usuario, :id_cama, :fecha_inicio, :fecha_fin, 'pendiente')
        ");

        $stmt->bindParam(':id_usuario', $datos['id_usuario'], PDO::PARAM_INT);
        $stmt->bindParam(':id_cama', $datos['id_cama'], PDO::PARAM_INT);
        $stmt->bindParam(':fecha_inicio', $datos['fecha_inicio']);
        $stmt->bindParam(':fecha_fin', $datos['fecha_fin']);

        $stmt->execute();
        $id_reserva = $conexion->lastInsertId();

        // Actualizar estado de la cama
        $stmt = $conexion->prepare("UPDATE camas SET estado = 'pendiente' WHERE id = :id_cama");
        $stmt->bindParam(':id_cama', $datos['id_cama'], PDO::PARAM_INT);
        $stmt->execute();

        $conexion->commit();
        return $id_reserva;
    } catch (PDOException $e) {
        $conexion->rollBack();
        error_log("Error al crear reserva: " . $e->getMessage());
        return false;
    }
}

/**
 * Actualizar estado de reserva
 * @param PDO $conexion
 * @param int $id
 * @param string $estado ('pendiente', 'reservada', 'cancelada')
 * @return bool
 */
function actualizar_estado_reserva($conexion, $id, $estado)
{
    try {
        $conexion->beginTransaction();

        // Obtener id_cama de la reserva
        $stmt = $conexion->prepare("SELECT id_cama FROM reservas WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

        if (! $reserva) {
            $conexion->rollBack();
            return false;
        }

        // Actualizar estado de la reserva
        $stmt = $conexion->prepare("UPDATE reservas SET estado = :estado WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':estado', $estado);
        $stmt->execute();

        // Actualizar estado de la cama
        $estado_cama = 'libre';
        if ($estado === 'reservada') {
            $estado_cama = 'reservada';
        } elseif ($estado === 'pendiente') {
            $estado_cama = 'pendiente';
        }

        $stmt = $conexion->prepare("UPDATE camas SET estado = :estado WHERE id = :id_cama");
        $stmt->bindParam(':id_cama', $reserva['id_cama'], PDO::PARAM_INT);
        $stmt->bindParam(':estado', $estado_cama);
        $stmt->execute();

        $conexion->commit();
        return true;
    } catch (PDOException $e) {
        $conexion->rollBack();
        error_log("Error al actualizar estado de reserva: " . $e->getMessage());
        return false;
    }
}

/**
 * Cancelar reserva
 * @param PDO $conexion
 * @param int $id
 * @return bool
 */
function cancelar_reserva($conexion, $id)
{
    return actualizar_estado_reserva($conexion, $id, 'cancelada');
}

/* ==================================================
   FUNCIONES DE ACOMPAÑANTES
   ================================================== */

/**
 * Obtener acompañantes de una reserva
 * @param PDO $conexion
 * @param int $id_reserva
 * @return array
 */
function obtener_acompanantes($conexion, $id_reserva)
{
    try {
        $stmt = $conexion->prepare("
            SELECT * FROM acompanantes
            WHERE id_reserva = :id_reserva
            ORDER BY id
        ");

        $stmt->bindParam(':id_reserva', $id_reserva, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener acompañantes: " . $e->getMessage());
        return [];
    }
}

/**
 * Agregar acompañante a una reserva
 * @param PDO $conexion
 * @param int $id_reserva
 * @param array $datos
 * @return bool
 */
function agregar_acompanante($conexion, $id_reserva, $datos)
{
    try {
        $stmt = $conexion->prepare("
            INSERT INTO acompanantes (id_reserva, num_socio, es_socio, dni, nombre, apellido1, apellido2, actividad)
            VALUES (:id_reserva, :num_socio, :es_socio, :dni, :nombre, :apellido1, :apellido2, :actividad)
        ");

        $stmt->bindParam(':id_reserva', $id_reserva, PDO::PARAM_INT);
        $stmt->bindParam(':num_socio', $datos['num_socio']);
        $stmt->bindParam(':es_socio', $datos['es_socio'], PDO::PARAM_BOOL);
        $stmt->bindParam(':dni', $datos['dni']);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':apellido1', $datos['apellido1']);
        $stmt->bindParam(':apellido2', $datos['apellido2']);
        $stmt->bindParam(':actividad', $datos['actividad']);

        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error al agregar acompañante: " . $e->getMessage());
        return false;
    }
}

/**
 * Eliminar acompañante
 * @param PDO $conexion
 * @param int $id
 * @return bool
 */
function eliminar_acompanante($conexion, $id)
{
    try {
        $stmt = $conexion->prepare("DELETE FROM acompanantes WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error al eliminar acompañante: " . $e->getMessage());
        return false;
    }
}

/* ==================================================
   FUNCIONES AUXILIARES
   ================================================== */

/**
 * Sanitizar entrada de datos
 * @param string $data
 * @return string
 */
function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Formatear fecha para mostrar
 * @param string $fecha
 * @return string
 */
function formatear_fecha($fecha)
{
    $timestamp = strtotime($fecha);
    return date('d/m/Y', $timestamp);
}

/**
 * Verificar si una fecha está en el rango
 * @param string $fecha
 * @param string $inicio
 * @param string $fin
 * @return bool
 */
function fecha_en_rango($fecha, $inicio, $fin)
{
    return ($fecha >= $inicio && $fecha <= $fin);
}

/* ==================================================
   FUNCIONES DE FOTOS DE PERFIL
   ================================================== */

/**
 * Validar imagen subida
 * @param array $file Array de $_FILES
 * @return array ['valido' => bool, 'mensaje' => string]
 */
function validar_imagen($file)
{
    $max_size               = 5 * 1024 * 1024; // 5MB
    $formatos_permitidos    = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif'];

    // Verificar si hay errores en la subida
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valido' => false, 'mensaje' => 'Error al subir el archivo'];
    }

    // Verificar tamaño
    if ($file['size'] > $max_size) {
        return ['valido' => false, 'mensaje' => 'El archivo es demasiado grande (máximo 5MB)'];
    }

    // Verificar tipo MIME
    if (! in_array($file['type'], $formatos_permitidos)) {
        return ['valido' => false, 'mensaje' => 'Formato no permitido. Solo JPG, PNG o GIF'];
    }

    // Verificar extensión
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (! in_array($extension, $extensiones_permitidas)) {
        return ['valido' => false, 'mensaje' => 'Extensión no permitida'];
    }

    // Verificar que sea realmente una imagen
    $info_imagen = getimagesize($file['tmp_name']);
    if ($info_imagen === false) {
        return ['valido' => false, 'mensaje' => 'El archivo no es una imagen válida'];
    }

    return ['valido' => true, 'mensaje' => 'Imagen válida', 'extension' => $extension];
}

/**
 * Subir foto de perfil
 * @param PDO $conexion
 * @param int $id_usuario
 * @param array $file Array de $_FILES
 * @return array ['exito' => bool, 'mensaje' => string, 'ruta' => string|null]
 */
function subir_foto_perfil($conexion, $id_usuario, $file)
{
    // Validar imagen
    $validacion = validar_imagen($file);
    if (! $validacion['valido']) {
        return ['exito' => false, 'mensaje' => $validacion['mensaje'], 'ruta' => null];
    }

    // Crear directorio si no existe
    $directorio = __DIR__ . '/uploads/perfiles/';
    if (! file_exists($directorio)) {
        mkdir($directorio, 0755, true);
    }

    // Obtener foto actual para eliminarla después
    $foto_anterior = obtener_foto_perfil($conexion, $id_usuario);

    // Generar nombre único
    $extension      = $validacion['extension'];
    $nombre_archivo = 'perfil_' . $id_usuario . '_' . time() . '.' . $extension;
    $ruta_completa  = $directorio . $nombre_archivo;
    $ruta_bd        = 'uploads/perfiles/' . $nombre_archivo;

    // Mover archivo
    if (! move_uploaded_file($file['tmp_name'], $ruta_completa)) {
        return ['exito' => false, 'mensaje' => 'Error al guardar la imagen', 'ruta' => null];
    }

    // Actualizar base de datos
    try {
        $stmt = $conexion->prepare("UPDATE usuarios SET foto_perfil = :foto WHERE id = :id");
        $stmt->bindParam(':foto', $ruta_bd);
        $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();

        // Eliminar foto anterior si existe
        if ($foto_anterior && file_exists(__DIR__ . '/' . $foto_anterior)) {
            unlink(__DIR__ . '/' . $foto_anterior);
        }

        return ['exito' => true, 'mensaje' => 'Foto actualizada correctamente', 'ruta' => $ruta_bd];
    } catch (PDOException $e) {
        // Si falla la BD, eliminar archivo subido
        if (file_exists($ruta_completa)) {
            unlink($ruta_completa);
        }
        error_log("Error al actualizar foto de perfil: " . $e->getMessage());
        return ['exito' => false, 'mensaje' => 'Error al guardar en la base de datos', 'ruta' => null];
    }
}

/**
 * Obtener ruta de foto de perfil
 * @param PDO $conexion
 * @param int $id_usuario
 * @return string|null
 */
function obtener_foto_perfil($conexion, $id_usuario)
{
    try {
        $stmt = $conexion->prepare("SELECT foto_perfil FROM usuarios WHERE id = :id");
        $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado['foto_perfil'] : null;
    } catch (PDOException $e) {
        error_log("Error al obtener foto de perfil: " . $e->getMessage());
        return null;
    }
}

/**
 * Eliminar foto de perfil
 * @param PDO $conexion
 * @param int $id_usuario
 * @return array ['exito' => bool, 'mensaje' => string]
 */
function eliminar_foto_perfil($conexion, $id_usuario)
{
    // Obtener ruta de la foto actual
    $foto = obtener_foto_perfil($conexion, $id_usuario);

    if (! $foto) {
        return ['exito' => false, 'mensaje' => 'No hay foto para eliminar'];
    }

    // Eliminar archivo físico
    $ruta_completa = __DIR__ . '/' . $foto;
    if (file_exists($ruta_completa)) {
        unlink($ruta_completa);
    }

    // Actualizar base de datos
    try {
        $stmt = $conexion->prepare("UPDATE usuarios SET foto_perfil = NULL WHERE id = :id");
        $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        return ['exito' => true, 'mensaje' => 'Foto eliminada correctamente'];
    } catch (PDOException $e) {
        error_log("Error al eliminar foto de perfil: " . $e->getMessage());
        return ['exito' => false, 'mensaje' => 'Error al actualizar la base de datos'];
    }
}

/**
 * Obtener información completa del usuario
 * @param PDO $conexion
 * @param int $id_usuario
 * @return array|false
 */
function obtener_info_usuario($conexion, $id_usuario)
{
    try {
        $stmt = $conexion->prepare("SELECT id, num_socio, dni, telf, email, nombre, apellido1, apellido2, foto_perfil, rol FROM usuarios WHERE id = :id");
        $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener info de usuario: " . $e->getMessage());
        return false;
    }
}

/**
 * Actualizar email y teléfono del usuario
 * @param PDO $conexion
 * @param int $id_usuario
 * @param string $email
 * @param string $telf
 * @return array ['exito' => bool, 'mensaje' => string]
 */
function actualizar_perfil_usuario($conexion, $id_usuario, $email, $telf)
{
    try {
        // Verificar que el email no esté en uso por otro usuario
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = :email AND id != :id");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->fetch()) {
            return ['exito' => false, 'mensaje' => 'El email ya está en uso por otro usuario'];
        }

        // Actualizar datos
        $stmt = $conexion->prepare("UPDATE usuarios SET email = :email, telf = :telf WHERE id = :id");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telf', $telf);
        $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();

        return ['exito' => true, 'mensaje' => 'Perfil actualizado correctamente'];
    } catch (PDOException $e) {
        error_log("Error al actualizar perfil: " . $e->getMessage());
        return ['exito' => false, 'mensaje' => 'Error al actualizar el perfil'];
    }
}
