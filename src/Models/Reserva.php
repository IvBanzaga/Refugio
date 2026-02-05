<?php
namespace Models;

use PDO;

/**
 * Modelo Reserva
 *
 * Gestiona las operaciones CRUD de reservas
 */
class Reserva
{

    private static function getDb()
    {
        global $conexionPDO;
        return $conexionPDO;
    }

    /**
     * Obtener todas las reservas con datos del usuario
     *
     * @param array $filtros
     * @return array
     */
    public static function all($filtros = [])
    {
        $db = self::getDb();

        $sql = "
            SELECT r.*,
                   u.nombre, u.apellido1, u.apellido2, u.num_socio, u.email, u.telf,
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

        // Filtros
        if (isset($filtros['estado'])) {
            $sql               .= " AND r.estado = :estado";
            $params[':estado']  = $filtros['estado'];
        }

        if (isset($filtros['id_usuario'])) {
            $sql                   .= " AND r.id_usuario = :id_usuario";
            $params[':id_usuario']  = $filtros['id_usuario'];
        }

        if (isset($filtros['fecha_desde'])) {
            $sql                    .= " AND r.fecha_inicio >= :fecha_desde";
            $params[':fecha_desde']  = $filtros['fecha_desde'];
        }

        if (isset($filtros['fecha_hasta'])) {
            $sql                    .= " AND r.fecha_fin <= :fecha_hasta";
            $params[':fecha_hasta']  = $filtros['fecha_hasta'];
        }

        $sql .= " GROUP BY r.id ORDER BY r.fecha_creacion DESC";

        // Paginación
        if (isset($filtros['limit']) && isset($filtros['offset'])) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $db->prepare($sql);

        foreach ($params as $key => $value) {
            if ($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, (int) $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }

        if (isset($filtros['limit']) && isset($filtros['offset'])) {
            $stmt->bindValue(':limit', (int) $filtros['limit'], PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $filtros['offset'], PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar reserva por ID
     *
     * @param int $id
     * @return array|null
     */
    public static function find($id)
    {
        $db = self::getDb();

        $sql = "
            SELECT r.*,
                   u.nombre, u.apellido1, u.apellido2, u.email, u.telf,
                   GROUP_CONCAT(c.numero ORDER BY c.numero) as camas_numeros
            FROM reservas r
            LEFT JOIN usuarios u ON r.id_usuario = u.id
            LEFT JOIN reservas_camas rc ON r.id = rc.id_reserva
            LEFT JOIN camas c ON rc.id_cama = c.id
            WHERE r.id = :id
            GROUP BY r.id
        ";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Crear nueva reserva
     *
     * @param array $data
     * @return int|false ID de la reserva o false
     */
    public static function create($data)
    {
        $db = self::getDb();

        $sql = "INSERT INTO reservas (
            id_usuario, id_habitacion, fecha_inicio, fecha_fin,
            numero_camas, estado, observaciones, fecha_creacion
        ) VALUES (
            :id_usuario, :id_habitacion, :fecha_inicio, :fecha_fin,
            :numero_camas, :estado, :observaciones, NOW()
        )";

        $stmt = $db->prepare($sql);

        $result = $stmt->execute([
            ':id_usuario'    => $data['id_usuario'] ?? null,
            ':id_habitacion' => $data['id_habitacion'],
            ':fecha_inicio'  => $data['fecha_inicio'],
            ':fecha_fin'     => $data['fecha_fin'],
            ':numero_camas'  => $data['numero_camas'],
            ':estado'        => $data['estado'] ?? 'pendiente',
            ':observaciones' => $data['observaciones'] ?? '',
        ]);

        return $result ? $db->lastInsertId() : false;
    }

    /**
     * Actualizar reserva
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public static function update($id, $data)
    {
        $db = self::getDb();

        $fields = [];
        $params = [':id' => $id];

        $allowedFields = ['id_usuario', 'id_habitacion', 'fecha_inicio', 'fecha_fin',
            'numero_camas', 'estado', 'observaciones'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[]          = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql  = "UPDATE reservas SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $db->prepare($sql);

        return $stmt->execute($params);
    }

    /**
     * Eliminar reserva
     *
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        $db = self::getDb();

        // Primero eliminar las camas asociadas
        $stmt = $db->prepare("DELETE FROM reservas_camas WHERE id_reserva = :id");
        $stmt->execute([':id' => $id]);

        // Luego eliminar la reserva
        $stmt = $db->prepare("DELETE FROM reservas WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Cambiar estado de reserva
     *
     * @param int $id
     * @param string $estado
     * @return bool
     */
    public static function cambiarEstado($id, $estado)
    {
        return self::update($id, ['estado' => $estado]);
    }

    /**
     * Obtener reservas por estado
     *
     * @param string $estado
     * @param array $filtros adicionales
     * @return array
     */
    public static function byEstado($estado, $filtros = [])
    {
        $filtros['estado'] = $estado;
        return self::all($filtros);
    }

    /**
     * Contar reservas
     *
     * @param array $filtros
     * @return int
     */
    public static function count($filtros = [])
    {
        $db     = self::getDb();
        $sql    = "SELECT COUNT(*) as total FROM reservas WHERE 1=1";
        $params = [];

        if (isset($filtros['estado'])) {
            $sql               .= " AND estado = :estado";
            $params[':estado']  = $filtros['estado'];
        }

        if (isset($filtros['id_usuario'])) {
            $sql                   .= " AND id_usuario = :id_usuario";
            $params[':id_usuario']  = $filtros['id_usuario'];
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /**
     * Asignar camas a una reserva
     *
     * @param int $idReserva
     * @param array $idsCamas Array de IDs de camas
     * @return bool
     */
    public static function asignarCamas($idReserva, $idsCamas)
    {
        $db = self::getDb();

        try {
            $db->beginTransaction();

            // Eliminar camas previas
            $stmt = $db->prepare("DELETE FROM reservas_camas WHERE id_reserva = :id_reserva");
            $stmt->execute([':id_reserva' => $idReserva]);

            // Insertar nuevas camas
            $stmt = $db->prepare("INSERT INTO reservas_camas (id_reserva, id_cama) VALUES (:id_reserva, :id_cama)");

            foreach ($idsCamas as $idCama) {
                $stmt->execute([
                    ':id_reserva' => $idReserva,
                    ':id_cama'    => $idCama,
                ]);
            }

            $db->commit();
            return true;

        } catch (\Exception $e) {
            $db->rollBack();
            error_log("Error al asignar camas: " . $e->getMessage());
            return false;
        }
    }
}
