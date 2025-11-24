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

/**
 * Listar usuarios con paginación, búsqueda y ordenación
 * @param PDO $conexion
 * @param array $filtros (page, limit, search, order_by, order_dir)
 * @return array
 */
function listar_usuarios_paginado($conexion, $filtros = [])
{
    try {
        $page      = $filtros['page'] ?? 1;
        $limit     = $filtros['limit'] ?? 10;
        $offset    = ($page - 1) * $limit;
        $search    = $filtros['search'] ?? '';
        $order_by  = $filtros['order_by'] ?? 'num_socio';
        $order_dir = $filtros['order_dir'] ?? 'ASC';

        // Validar columnas de ordenación
        $valid_columns = ['num_socio', 'nombre', 'email', 'dni', 'rol'];
        if (! in_array($order_by, $valid_columns)) {
            $order_by = 'num_socio';
        }

        // Validar dirección de ordenación
        $order_dir = strtoupper($order_dir) === 'DESC' ? 'DESC' : 'ASC';

        // Construir query base
        $sql = "SELECT id, num_socio, dni, telf, email, nombre, apellido1, apellido2, rol
                FROM usuarios
                WHERE 1=1";

        $params = [];

        // Agregar búsqueda si existe
        if (! empty($search)) {
            $sql .= " AND (
                nombre LIKE :search
                OR apellido1 LIKE :search
                OR apellido2 LIKE :search
                OR email LIKE :search
                OR dni LIKE :search
                OR num_socio LIKE :search
            )";
            $params[':search'] = '%' . $search . '%';
        }

        // Agregar ordenación
        $sql .= " ORDER BY $order_by $order_dir";

        // Agregar paginación
        $sql .= " LIMIT :limit OFFSET :offset";

        $stmt = $conexion->prepare($sql);

        // Bind parámetros de búsqueda
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        // Bind parámetros de paginación
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al listar usuarios paginados: " . $e->getMessage());
        return [];
    }
}

/**
 * Contar total de usuarios con filtros
 * @param PDO $conexion
 * @param array $filtros (search)
 * @return int
 */
function contar_usuarios($conexion, $filtros = [])
{
    try {
        $search = $filtros['search'] ?? '';

        $sql    = "SELECT COUNT(*) as total FROM usuarios WHERE 1=1";
        $params = [];

        // Agregar búsqueda si existe
        if (! empty($search)) {
            $sql .= " AND (
                nombre LIKE :search
                OR apellido1 LIKE :search
                OR apellido2 LIKE :search
                OR email LIKE :search
                OR dni LIKE :search
                OR num_socio LIKE :search
            )";
            $params[':search'] = '%' . $search . '%';
        }

        $stmt = $conexion->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['total'];
    } catch (PDOException $e) {
        error_log("Error al contar usuarios: " . $e->getMessage());
        return 0;
    }
}

/**
 * Exportar usuarios a CSV
 * @param PDO $conexion
 * @param array $filtros (search, order_by, order_dir)
 * @return void
 */
function export_usuarios_csv($conexion, $filtros = [])
{
    try {
        $search    = $filtros['search'] ?? '';
        $order_by  = $filtros['order_by'] ?? 'num_socio';
        $order_dir = $filtros['order_dir'] ?? 'ASC';

        // Validar columnas de ordenación
        $valid_columns = ['num_socio', 'nombre', 'email', 'dni', 'rol'];
        if (! in_array($order_by, $valid_columns)) {
            $order_by = 'num_socio';
        }

        $order_dir = strtoupper($order_dir) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT num_socio, nombre, apellido1, apellido2, dni, email, telf, rol
                FROM usuarios
                WHERE 1=1";

        $params = [];

        if (! empty($search)) {
            $sql .= " AND (
                nombre LIKE :search
                OR apellido1 LIKE :search
                OR apellido2 LIKE :search
                OR email LIKE :search
                OR dni LIKE :search
                OR num_socio LIKE :search
            )";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY $order_by $order_dir";

        $stmt = $conexion->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Configurar headers para descarga CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="usuarios_' . date('Y-m-d_H-i-s') . '.csv"');

        // Crear output stream
        $output = fopen('php://output', 'w');

        // BOM para UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Escribir encabezados
        fputcsv($output, ['Nº Socio', 'Nombre', 'Apellido 1', 'Apellido 2', 'DNI', 'Email', 'Teléfono', 'Rol'], ';');

        // Escribir datos
        foreach ($usuarios as $usuario) {
            fputcsv($output, [
                $usuario['num_socio'],
                $usuario['nombre'],
                $usuario['apellido1'],
                $usuario['apellido2'],
                $usuario['dni'],
                $usuario['email'],
                $usuario['telf'],
                strtoupper($usuario['rol']),
            ], ';');
        }

        fclose($output);
        exit;
    } catch (PDOException $e) {
        error_log("Error al exportar usuarios CSV: " . $e->getMessage());
        die("Error al exportar CSV");
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
                           SELECT 1
                           FROM reservas_camas rc
                           INNER JOIN reservas r ON rc.id_reserva = r.id
                           WHERE rc.id_cama = c.id
                           AND r.estado IN ('pendiente', 'reservada')
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
 * Contar total de camas en el refugio
 * @param PDO $conexion
 * @return int
 */
function contar_total_camas($conexion)
{
    try {
        $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM camas");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['total'];
    } catch (PDOException $e) {
        error_log("Error al contar total camas: " . $e->getMessage());
        return 0;
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
                SELECT 1
                FROM reservas_camas rc
                INNER JOIN reservas r ON rc.id_reserva = r.id
                WHERE rc.id_cama = c.id
                AND r.estado IN ('pendiente', 'reservada')
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

/**
 * Obtener camas disponibles en una habitación para un rango de fechas
 * @param PDO $conexion
 * @param int $id_habitacion
 * @param string $fecha_inicio
 * @param string $fecha_fin
 * @param int|null $id_reserva_excluir ID de reserva a excluir (para ediciones)
 * @return array
 */
function obtener_camas_disponibles($conexion, $id_habitacion, $fecha_inicio, $fecha_fin, $id_reserva_excluir = null)
{
    try {
        $sql = "
            SELECT id, numero FROM camas
            WHERE id_habitacion = :id_habitacion
            AND id NOT IN (
                SELECT DISTINCT c.id
                FROM camas c
                INNER JOIN reservas_camas rc ON c.id = rc.id_cama
                INNER JOIN reservas r ON rc.id_reserva = r.id
                WHERE c.id_habitacion = :id_habitacion
                AND r.estado IN ('pendiente', 'reservada')
                AND (r.fecha_inicio <= :fecha_fin AND r.fecha_fin >= :fecha_inicio)
        ";

        if ($id_reserva_excluir !== null) {
            $sql .= " AND r.id != :id_reserva_excluir";
        }

        $sql .= "
            )
            ORDER BY numero
        ";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id_habitacion', $id_habitacion, PDO::PARAM_INT);
        $stmt->bindParam(':fecha_inicio', $fecha_inicio);
        $stmt->bindParam(':fecha_fin', $fecha_fin);

        if ($id_reserva_excluir !== null) {
            $stmt->bindParam(':id_reserva_excluir', $id_reserva_excluir, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener camas disponibles: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtener todas las habitaciones
 * @param PDO $conexion
 * @return array
 */
function obtener_todas_habitaciones($conexion)
{
    try {
        $stmt = $conexion->prepare("
            SELECT h.id, h.numero, h.capacidad, COUNT(c.id) as total_camas
            FROM habitaciones h
            LEFT JOIN camas c ON h.id = c.id_habitacion
            GROUP BY h.id, h.numero, h.capacidad
            ORDER BY h.numero
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener habitaciones: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtener habitaciones disponibles con número de camas libres para un período
 * @param PDO $conexion
 * @param string $fecha_inicio
 * @param string $fecha_fin
 * @return array
 */
function obtener_habitaciones_disponibles($conexion, $fecha_inicio, $fecha_fin)
{
    try {
        // Verificar si existe una reserva de "TODO EL REFUGIO" para estas fechas
        $stmt_check = $conexion->prepare("
            SELECT COUNT(*) as total
            FROM reservas
            WHERE id_habitacion IS NULL
            AND estado IN ('pendiente', 'reservada')
            AND (fecha_inicio <= :fecha_fin AND fecha_fin >= :fecha_inicio)
        ");
        $stmt_check->bindParam(':fecha_inicio', $fecha_inicio);
        $stmt_check->bindParam(':fecha_fin', $fecha_fin);
        $stmt_check->execute();
        $resultado_check = $stmt_check->fetch(PDO::FETCH_ASSOC);

        // Si hay una reserva de TODO EL REFUGIO, no hay habitaciones disponibles
        if ($resultado_check['total'] > 0) {
            return [];
        }

        // Consulta corregida: obtener habitaciones y calcular disponibilidad
        $stmt = $conexion->prepare("
            SELECT
                h.id,
                h.numero,
                h.capacidad,
                (SELECT COUNT(*) FROM camas WHERE id_habitacion = h.id) as camas_totales,
                (SELECT COUNT(*) FROM camas WHERE id_habitacion = h.id) -
                (SELECT COUNT(DISTINCT rc.id_cama)
                 FROM reservas_camas rc
                 INNER JOIN reservas r ON rc.id_reserva = r.id
                 INNER JOIN camas c ON rc.id_cama = c.id
                 WHERE c.id_habitacion = h.id
                 AND r.estado IN ('pendiente', 'reservada')
                 AND (r.fecha_inicio <= :fecha_fin AND r.fecha_fin >= :fecha_inicio)
                ) as camas_disponibles
            FROM habitaciones h
            HAVING camas_disponibles > 0
            ORDER BY h.numero
        ");

        $stmt->bindParam(':fecha_inicio', $fecha_inicio);
        $stmt->bindParam(':fecha_fin', $fecha_fin);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener habitaciones disponibles: " . $e->getMessage());
        return [];
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
/**
 * Listar reservas (con filtros, paginación, ordenamiento y búsqueda)
 * @param PDO $conexion
 * @param array $filtros (estado, id_usuario, fecha_inicio, fecha_fin, limit, offset, order_by, order_dir, search)
 * @return array
 */
function listar_reservas($conexion, $filtros = [])
{
    try {
        $sql = "
            SELECT r.id, r.fecha_inicio, r.fecha_fin, r.estado, r.fecha_creacion,
                   r.id_habitacion, r.numero_camas, r.observaciones,
                   u.nombre, u.apellido1, u.apellido2, u.num_socio, u.email,
                   h.numero as habitacion_numero,
                   GROUP_CONCAT(c.numero ORDER BY c.numero SEPARATOR ', ') as camas_numeros
            FROM reservas r
            LEFT JOIN usuarios u ON r.id_usuario = u.id
            LEFT JOIN habitaciones h ON r.id_habitacion = h.id
            LEFT JOIN reservas_camas rc ON r.id = rc.id_reserva
            LEFT JOIN camas c ON rc.id_cama = c.id
            WHERE 1=1
        ";

        $params = [];

        // Filtros básicos
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

        // Búsqueda
        if (! empty($filtros['search'])) {
            $searchTerm = '%' . $filtros['search'] . '%';
            $sql .= " AND (u.nombre LIKE :search OR u.apellido1 LIKE :search OR u.email LIKE :search OR u.num_socio LIKE :search)";
            $params[':search'] = $searchTerm;
        }

        $sql .= " GROUP BY r.id";

        // Ordenamiento
        $allowed_sort_cols = ['fecha_inicio', 'fecha_fin', 'fecha_creacion', 'nombre'];
        $order_by          = in_array($filtros['order_by'] ?? '', $allowed_sort_cols) ? $filtros['order_by'] : 'fecha_creacion';

        // Mapeo especial para columnas de otras tablas
        if ($order_by === 'nombre') {
            $order_by = 'u.nombre';
        } else {
            $order_by = 'r.' . $order_by;
        }

        $order_dir = strtoupper($filtros['order_dir'] ?? '') === 'ASC' ? 'ASC' : 'DESC';
        $sql .= " ORDER BY $order_by $order_dir";

        // Paginación
        if (isset($filtros['limit']) && isset($filtros['offset'])) {
            $sql .= " LIMIT :limit OFFSET :offset";
            // PDO limit/offset must be integers
            $params[':limit']  = (int) $filtros['limit'];
            $params[':offset'] = (int) $filtros['offset'];
        }

        $stmt = $conexion->prepare($sql);

        foreach ($params as $key => $value) {
            if ($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al listar reservas: " . $e->getMessage());
        return [];
    }
}

/**
 * Contar total de reservas con filtros (para paginación)
 * @param PDO $conexion
 * @param array $filtros
 * @return int
 */
function contar_reservas($conexion, $filtros = [])
{
    try {
        $sql = "
            SELECT COUNT(DISTINCT r.id) as total
            FROM reservas r
            LEFT JOIN usuarios u ON r.id_usuario = u.id
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

        if (! empty($filtros['search'])) {
            $searchTerm = '%' . $filtros['search'] . '%';
            $sql .= " AND (u.nombre LIKE :search OR u.apellido1 LIKE :search OR u.email LIKE :search OR u.num_socio LIKE :search)";
            $params[':search'] = $searchTerm;
        }

        $stmt = $conexion->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error al contar reservas: " . $e->getMessage());
        return 0;
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
                   h.numero as habitacion_numero,
                   GROUP_CONCAT(c.numero ORDER BY c.numero SEPARATOR ', ') as camas_numeros
            FROM reservas r
            LEFT JOIN usuarios u ON r.id_usuario = u.id
            JOIN habitaciones h ON r.id_habitacion = h.id
            LEFT JOIN reservas_camas rc ON r.id = rc.id_reserva
            LEFT JOIN camas c ON rc.id_cama = c.id
            WHERE r.id = :id
            GROUP BY r.id
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
 * Crear reserva para un socio (por admin, aprobada automáticamente)
 * @param PDO $conexion
 * @param array $datos
 * @return int|false ID de la reserva creada o false
 */
function crear_reserva_para_socio($conexion, $datos)
{
    try {
        $conexion->beginTransaction();

        // Validar número de camas
        $numero_camas = isset($datos['numero_camas']) ? (int) $datos['numero_camas'] : 1;
        if ($numero_camas < 1) {
            throw new Exception("Debes reservar al menos 1 cama");
        }

        // Buscar camas disponibles en la habitación
        $stmt = $conexion->prepare("
            SELECT id FROM camas
            WHERE id_habitacion = :id_habitacion
            AND id NOT IN (
                SELECT DISTINCT c.id
                FROM camas c
                INNER JOIN reservas_camas rc ON c.id = rc.id_cama
                INNER JOIN reservas r ON rc.id_reserva = r.id
                WHERE c.id_habitacion = :id_habitacion
                AND r.estado IN ('pendiente', 'reservada')
                AND (r.fecha_inicio <= :fecha_fin AND r.fecha_fin >= :fecha_inicio)
            )
            ORDER BY numero
            LIMIT :numero_camas
        ");

        $stmt->bindParam(':id_habitacion', $datos['id_habitacion'], PDO::PARAM_INT);
        $stmt->bindParam(':fecha_inicio', $datos['fecha_inicio']);
        $stmt->bindParam(':fecha_fin', $datos['fecha_fin']);
        $stmt->bindParam(':numero_camas', $numero_camas, PDO::PARAM_INT);
        $stmt->execute();

        $camas_disponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (count($camas_disponibles) < $numero_camas) {
            throw new Exception("No hay suficientes camas disponibles en esta habitación");
        }

        // Crear reserva con estado 'reservada' (aprobada automáticamente por admin)
        $stmt = $conexion->prepare("
            INSERT INTO reservas (id_usuario, id_habitacion, numero_camas, fecha_inicio, fecha_fin, estado)
            VALUES (:id_usuario, :id_habitacion, :numero_camas, :fecha_inicio, :fecha_fin, 'reservada')
        ");

        $stmt->bindParam(':id_usuario', $datos['id_usuario'], PDO::PARAM_INT);
        $stmt->bindParam(':id_habitacion', $datos['id_habitacion'], PDO::PARAM_INT);
        $stmt->bindParam(':numero_camas', $numero_camas, PDO::PARAM_INT);
        $stmt->bindParam(':fecha_inicio', $datos['fecha_inicio']);
        $stmt->bindParam(':fecha_fin', $datos['fecha_fin']);
        $stmt->execute();

        $id_reserva = $conexion->lastInsertId();

        // Crear relación entre reserva y camas asignadas
        $stmt_cama   = $conexion->prepare("INSERT INTO reservas_camas (id_reserva, id_cama) VALUES (:id_reserva, :id_cama)");
        $stmt_update = $conexion->prepare("UPDATE camas SET estado = 'reservada' WHERE id = :id_cama");

        foreach ($camas_disponibles as $id_cama) {
            // Crear relación
            $stmt_cama->bindParam(':id_reserva', $id_reserva, PDO::PARAM_INT);
            $stmt_cama->bindParam(':id_cama', $id_cama, PDO::PARAM_INT);
            $stmt_cama->execute();

            // Actualizar estado de la cama
            $stmt_update->bindParam(':id_cama', $id_cama, PDO::PARAM_INT);
            $stmt_update->execute();
        }

        $conexion->commit();
        return $id_reserva;
    } catch (Exception $e) {
        $conexion->rollBack();
        error_log("Error al crear reserva para socio: " . $e->getMessage());
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
    // Validar número de camas
    $numero_camas = isset($datos['numero_camas']) ? (int) $datos['numero_camas'] : 1;
    if ($numero_camas < 1) {
        throw new Exception("Debes reservar al menos 1 cama");
    }

    // Buscar camas disponibles en la habitación
    $stmt = $conexion->prepare("
        SELECT id FROM camas
        WHERE id_habitacion = :id_habitacion
        AND id NOT IN (
            SELECT DISTINCT c.id
            FROM camas c
            INNER JOIN reservas_camas rc ON c.id = rc.id_cama
            INNER JOIN reservas r ON rc.id_reserva = r.id
            WHERE c.id_habitacion = :id_habitacion
            AND r.estado IN ('pendiente', 'reservada')
            AND (
                (r.fecha_inicio <= :fecha_fin AND r.fecha_fin >= :fecha_inicio)
            )
        )
        ORDER BY numero
        LIMIT :numero_camas
    ");

    $stmt->bindParam(':id_habitacion', $datos['id_habitacion'], PDO::PARAM_INT);
    $stmt->bindParam(':fecha_inicio', $datos['fecha_inicio']);
    $stmt->bindParam(':fecha_fin', $datos['fecha_fin']);
    $stmt->bindParam(':numero_camas', $numero_camas, PDO::PARAM_INT);
    $stmt->execute();

    $camas_disponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($camas_disponibles) < $numero_camas) {
        throw new Exception("No hay suficientes camas disponibles en esta habitación");
    }

    // Insertar reserva
    $stmt = $conexion->prepare("
        INSERT INTO reservas (id_usuario, id_habitacion, numero_camas, fecha_inicio, fecha_fin, estado)
        VALUES (:id_usuario, :id_habitacion, :numero_camas, :fecha_inicio, :fecha_fin, 'pendiente')
    ");

    $stmt->bindParam(':id_usuario', $datos['id_usuario'], PDO::PARAM_INT);
    $stmt->bindParam(':id_habitacion', $datos['id_habitacion'], PDO::PARAM_INT);
    $stmt->bindParam(':numero_camas', $numero_camas, PDO::PARAM_INT);
    $stmt->bindParam(':fecha_inicio', $datos['fecha_inicio']);
    $stmt->bindParam(':fecha_fin', $datos['fecha_fin']);

    $stmt->execute();
    $id_reserva = $conexion->lastInsertId();

    // Crear relación entre reserva y camas asignadas
    $stmt_cama = $conexion->prepare("
        INSERT INTO reservas_camas (id_reserva, id_cama) VALUES (:id_reserva, :id_cama)
    ");

    // Actualizar estado de las camas asignadas
    $stmt_update = $conexion->prepare("UPDATE camas SET estado = 'pendiente' WHERE id = :id_cama");

    foreach ($camas_disponibles as $id_cama) {
        // Crear relación
        $stmt_cama->bindParam(':id_reserva', $id_reserva, PDO::PARAM_INT);
        $stmt_cama->bindParam(':id_cama', $id_cama, PDO::PARAM_INT);
        $stmt_cama->execute();

        // Actualizar estado de la cama
        $stmt_update->bindParam(':id_cama', $id_cama, PDO::PARAM_INT);
        $stmt_update->execute();
    }

    return $id_reserva;
}

/**
 * Crear reserva especial (solo admin) para eventos
 * @param PDO $conexion
 * @param array $datos (motivo, fecha_inicio, fecha_fin, id_habitacion, numero_camas)
 * @return int|false ID de la reserva creada o false
 */
function crear_reserva_especial_admin($conexion, $datos)
{
    try {
        $conexion->beginTransaction();

        // Validar número de camas
        $numero_camas = (int) $datos['numero_camas'];
        if ($numero_camas < 1) {
            throw new Exception("Debes reservar al menos 1 cama");
        }

        // Buscar camas disponibles en la habitación
        $stmt = $conexion->prepare("
            SELECT id FROM camas
            WHERE id_habitacion = :id_habitacion
            AND estado = 'libre'
            AND id NOT IN (
                SELECT DISTINCT c.id
                FROM camas c
                INNER JOIN reservas_camas rc ON c.id = rc.id_cama
                INNER JOIN reservas r ON rc.id_reserva = r.id
                WHERE c.id_habitacion = :id_habitacion
                AND r.estado IN ('pendiente', 'reservada')
                AND (
                    (r.fecha_inicio <= :fecha_fin AND r.fecha_fin >= :fecha_inicio)
                )
            )
            ORDER BY numero
            LIMIT :numero_camas
        ");

        $stmt->bindParam(':id_habitacion', $datos['id_habitacion'], PDO::PARAM_INT);
        $stmt->bindParam(':fecha_inicio', $datos['fecha_inicio']);
        $stmt->bindParam(':fecha_fin', $datos['fecha_fin']);
        $stmt->bindParam(':numero_camas', $numero_camas, PDO::PARAM_INT);
        $stmt->execute();

        $camas_disponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (count($camas_disponibles) < $numero_camas) {
            throw new Exception("No hay suficientes camas disponibles en esta habitación");
        }

        // Insertar reserva especial (id_usuario = NULL, estado = 'reservada' directamente)
        $stmt = $conexion->prepare("
            INSERT INTO reservas (id_usuario, id_habitacion, numero_camas, fecha_inicio, fecha_fin, estado, observaciones)
            VALUES (NULL, :id_habitacion, :numero_camas, :fecha_inicio, :fecha_fin, 'reservada', :motivo)
        ");

        $stmt->bindParam(':id_habitacion', $datos['id_habitacion'], PDO::PARAM_INT);
        $stmt->bindParam(':numero_camas', $numero_camas, PDO::PARAM_INT);
        $stmt->bindParam(':fecha_inicio', $datos['fecha_inicio']);
        $stmt->bindParam(':fecha_fin', $datos['fecha_fin']);
        $stmt->bindParam(':motivo', $datos['motivo']);

        $stmt->execute();
        $id_reserva = $conexion->lastInsertId();

        // Crear relación entre reserva y camas asignadas
        $stmt_cama = $conexion->prepare("
            INSERT INTO reservas_camas (id_reserva, id_cama) VALUES (:id_reserva, :id_cama)
        ");

        // Actualizar estado de las camas asignadas
        $stmt_update = $conexion->prepare("UPDATE camas SET estado = 'reservada' WHERE id = :id_cama");

        foreach ($camas_disponibles as $id_cama) {
            // Crear relación
            $stmt_cama->bindParam(':id_reserva', $id_reserva, PDO::PARAM_INT);
            $stmt_cama->bindParam(':id_cama', $id_cama, PDO::PARAM_INT);
            $stmt_cama->execute();

            // Actualizar estado de la cama
            $stmt_update->bindParam(':id_cama', $id_cama, PDO::PARAM_INT);
            $stmt_update->execute();
        }

        $conexion->commit();
        return $id_reserva;
    } catch (Exception $e) {
        $conexion->rollBack();
        error_log("Error al crear reserva especial: " . $e->getMessage());
        return false;
    }
}

/**
 * Crear reserva especial para TODO EL REFUGIO (todas las habitaciones)
 * @param PDO $conexion
 * @param array $datos (motivo, fecha_inicio, fecha_fin, numero_camas - ignorado para todo el refugio)
 * @return bool
 */
function crear_reserva_todo_refugio($conexion, $datos)
{
    try {
        $conexion->beginTransaction();

        // Primero, contar el total de camas del refugio
        $stmt_total = $conexion->prepare("SELECT COUNT(*) as total FROM camas");
        $stmt_total->execute();
        $total_camas_refugio = (int) $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];

        // Obtener TODAS las camas disponibles del refugio para las fechas seleccionadas
        $stmt_camas = $conexion->prepare("
            SELECT c.id
            FROM camas c
            WHERE c.estado = 'libre'
            AND c.id NOT IN (
                SELECT DISTINCT rc.id_cama
                FROM reservas_camas rc
                INNER JOIN reservas r ON rc.id_reserva = r.id
                WHERE r.estado IN ('pendiente', 'reservada')
                AND (r.fecha_inicio <= :fecha_fin AND r.fecha_fin >= :fecha_inicio)
            )
            ORDER BY c.id_habitacion, c.numero
        ");

        $stmt_camas->bindParam(':fecha_inicio', $datos['fecha_inicio']);
        $stmt_camas->bindParam(':fecha_fin', $datos['fecha_fin']);
        $stmt_camas->execute();

        $camas_disponibles = $stmt_camas->fetchAll(PDO::FETCH_COLUMN);

        if (empty($camas_disponibles)) {
            throw new Exception("No hay camas disponibles en las fechas seleccionadas");
        }

        $total_camas_disponibles = count($camas_disponibles);

        // VERIFICAR QUE TODAS LAS CAMAS DEL REFUGIO ESTÉN DISPONIBLES
        if ($total_camas_disponibles < $total_camas_refugio) {
            throw new Exception("No se puede reservar TODO EL REFUGIO. Solo hay {$total_camas_disponibles} de {$total_camas_refugio} camas disponibles. Todas las camas deben estar libres.");
        }

        $total_camas = $total_camas_disponibles;

        // Crear UNA SOLA reserva con id_habitacion = NULL para "TODO EL REFUGIO"
        $stmt_reserva = $conexion->prepare("
            INSERT INTO reservas (id_usuario, id_habitacion, numero_camas, fecha_inicio, fecha_fin, estado, observaciones)
            VALUES (NULL, NULL, :numero_camas, :fecha_inicio, :fecha_fin, 'reservada', :motivo)
        ");

        $motivo_completo = "TODO EL REFUGIO - " . $datos['motivo'];
        $stmt_reserva->bindParam(':numero_camas', $total_camas, PDO::PARAM_INT);
        $stmt_reserva->bindParam(':fecha_inicio', $datos['fecha_inicio']);
        $stmt_reserva->bindParam(':fecha_fin', $datos['fecha_fin']);
        $stmt_reserva->bindParam(':motivo', $motivo_completo);
        $stmt_reserva->execute();

        $id_reserva = $conexion->lastInsertId();

        // Crear relación entre reserva y TODAS las camas disponibles
        $stmt_cama = $conexion->prepare("
            INSERT INTO reservas_camas (id_reserva, id_cama) VALUES (:id_reserva, :id_cama)
        ");

        // Actualizar estado de las camas asignadas
        $stmt_update = $conexion->prepare("UPDATE camas SET estado = 'reservada' WHERE id = :id_cama");

        foreach ($camas_disponibles as $id_cama) {
            // Crear relación
            $stmt_cama->bindParam(':id_reserva', $id_reserva, PDO::PARAM_INT);
            $stmt_cama->bindParam(':id_cama', $id_cama, PDO::PARAM_INT);
            $stmt_cama->execute();

            // Actualizar estado de la cama
            $stmt_update->bindParam(':id_cama', $id_cama, PDO::PARAM_INT);
            $stmt_update->execute();
        }

        $conexion->commit();
        error_log("Reserva TODO EL REFUGIO creada: {$total_camas} camas totales");
        return true;
    } catch (Exception $e) {
        $conexion->rollBack();
        error_log("Error al crear reserva todo el refugio: " . $e->getMessage());
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

        // Obtener las camas de la reserva
        $stmt = $conexion->prepare("
            SELECT id_cama FROM reservas_camas WHERE id_reserva = :id
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $camas = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($camas)) {
            $conexion->rollBack();
            return false;
        }

        // Actualizar estado de la reserva
        $stmt = $conexion->prepare("UPDATE reservas SET estado = :estado WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':estado', $estado);
        $stmt->execute();

        // Actualizar estado de las camas
        $estado_cama = 'libre';
        if ($estado === 'reservada') {
            $estado_cama = 'reservada';
        } elseif ($estado === 'pendiente') {
            $estado_cama = 'pendiente';
        }

        $stmt_update = $conexion->prepare("UPDATE camas SET estado = :estado WHERE id = :id_cama");
        $stmt_update->bindParam(':estado', $estado_cama);

        foreach ($camas as $id_cama) {
            $stmt_update->bindParam(':id_cama', $id_cama, PDO::PARAM_INT);
            $stmt_update->execute();
        }

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
/**
 * Editar una reserva de usuario (solo reservas pendientes)
 * @param PDO $conexion
 * @param int $id_reserva
 * @param string $fecha_inicio
 * @param string $fecha_fin
 * @param int $id_habitacion
 * @param int $id_reserva
 * @param string $fecha_inicio
 * @param string $fecha_fin
 * @param int|null $id_habitacion
 * @param int $numero_camas
 * @return bool
 */
/**
 * Editar una reserva de usuario (solo reservas pendientes)
 * @param PDO $conexion
 * @param int $id_reserva
 * @param string $fecha_inicio
 * @param string $fecha_fin
 * @param int $id_habitacion
 * @param int $numero_camas
 * @return bool
 */
function editar_reserva_usuario($conexion, $id_reserva, $fecha_inicio, $fecha_fin, $id_habitacion, $numero_camas)
{
    // Actualizar datos básicos de la reserva
    $stmt = $conexion->prepare("
        UPDATE reservas
        SET fecha_inicio = :fecha_inicio,
            fecha_fin = :fecha_fin,
            id_habitacion = :id_habitacion,
            numero_camas = :numero_camas
        WHERE id = :id
    ");
    $stmt->bindParam(':fecha_inicio', $fecha_inicio);
    $stmt->bindParam(':fecha_fin', $fecha_fin);
    $stmt->bindParam(':id_habitacion', $id_habitacion, PDO::PARAM_INT);
    $stmt->bindParam(':numero_camas', $numero_camas, PDO::PARAM_INT);
    $stmt->bindParam(':id', $id_reserva, PDO::PARAM_INT);
    $stmt->execute();

    // Eliminar asignaciones anteriores de camas
    $stmt = $conexion->prepare("DELETE FROM reservas_camas WHERE id_reserva = :id_reserva");
    $stmt->bindParam(':id_reserva', $id_reserva, PDO::PARAM_INT);
    $stmt->execute();

    // Obtener camas disponibles de la nueva habitación
    $camas_disponibles = obtener_camas_disponibles($conexion, $id_habitacion, $fecha_inicio, $fecha_fin, $id_reserva);

    if (count($camas_disponibles) < $numero_camas) {
        throw new Exception("No hay suficientes camas disponibles en la habitación seleccionada");
    }

    // Asignar nuevas camas
    $stmt = $conexion->prepare("INSERT INTO reservas_camas (id_reserva, id_cama) VALUES (:id_reserva, :id_cama)");
    for ($i = 0; $i < $numero_camas; $i++) {
        $stmt->bindParam(':id_reserva', $id_reserva, PDO::PARAM_INT);
        $stmt->bindParam(':id_cama', $camas_disponibles[$i]['id'], PDO::PARAM_INT);
        $stmt->execute();
    }

    return true;
}

function editar_reserva_admin($conexion, $id_reserva, $fecha_inicio, $fecha_fin, $id_habitacion, $numero_camas)
{
    try {
        $conexion->beginTransaction();

        // Obtener datos actuales de la reserva
        $stmt = $conexion->prepare("SELECT id_habitacion, id_usuario FROM reservas WHERE id = :id");
        $stmt->bindParam(':id', $id_reserva, PDO::PARAM_INT);
        $stmt->execute();
        $reserva_actual = $stmt->fetch(PDO::FETCH_ASSOC);

        if (! $reserva_actual) {
            throw new Exception("Reserva no encontrada");
        }

        $es_todo_refugio = empty($reserva_actual['id_habitacion']);

        // Actualizar datos básicos de la reserva
        if ($es_todo_refugio) {
            // TODO EL REFUGIO: solo actualizar fechas
            $stmt = $conexion->prepare("
                UPDATE reservas
                SET fecha_inicio = :fecha_inicio,
                    fecha_fin = :fecha_fin
                WHERE id = :id
            ");
            $stmt->bindParam(':fecha_inicio', $fecha_inicio);
            $stmt->bindParam(':fecha_fin', $fecha_fin);
            $stmt->bindParam(':id', $id_reserva, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            // Habitación específica: actualizar todo
            $stmt = $conexion->prepare("
                UPDATE reservas
                SET fecha_inicio = :fecha_inicio,
                    fecha_fin = :fecha_fin,
                    id_habitacion = :id_habitacion,
                    numero_camas = :numero_camas
                WHERE id = :id
            ");
            $stmt->bindParam(':fecha_inicio', $fecha_inicio);
            $stmt->bindParam(':fecha_fin', $fecha_fin);
            $stmt->bindParam(':id_habitacion', $id_habitacion, PDO::PARAM_INT);
            $stmt->bindParam(':numero_camas', $numero_camas, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id_reserva, PDO::PARAM_INT);
            $stmt->execute();

            // Actualizar asignación de camas (solo si cambió habitación o número de camas)
            // Primero eliminar asignaciones anteriores
            $stmt = $conexion->prepare("DELETE FROM reservas_camas WHERE id_reserva = :id_reserva");
            $stmt->bindParam(':id_reserva', $id_reserva, PDO::PARAM_INT);
            $stmt->execute();

            // Obtener camas disponibles de la nueva habitación
            $camas_disponibles = obtener_camas_disponibles($conexion, $id_habitacion, $fecha_inicio, $fecha_fin, $id_reserva);

            if (count($camas_disponibles) < $numero_camas) {
                throw new Exception("No hay suficientes camas disponibles en la habitación seleccionada");
            }

            // Asignar nuevas camas
            $stmt = $conexion->prepare("INSERT INTO reservas_camas (id_reserva, id_cama) VALUES (:id_reserva, :id_cama)");
            for ($i = 0; $i < $numero_camas; $i++) {
                $stmt->bindParam(':id_reserva', $id_reserva, PDO::PARAM_INT);
                $stmt->bindParam(':id_cama', $camas_disponibles[$i]['id'], PDO::PARAM_INT);
                $stmt->execute();
            }
        }

        $conexion->commit();
        return true;
    } catch (Exception $e) {
        $conexion->rollBack();
        error_log("Error al editar reserva: " . $e->getMessage());
        return false;
    }
}

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
