<?php
namespace Models;

use PDO;

/**
 * Modelo Habitacion
 *
 * Gestiona las operaciones de habitaciones y camas
 */
class Habitacion
{

    private static function getDb()
    {
        global $conexionPDO;
        return $conexionPDO;
    }

    /**
     * Obtener todas las habitaciones
     *
     * @return array
     */
    public static function all()
    {
        $db   = self::getDb();
        $stmt = $db->query("SELECT * FROM habitaciones ORDER BY numero ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar habitación por ID
     *
     * @param int $id
     * @return array|null
     */
    public static function find($id)
    {
        $db   = self::getDb();
        $stmt = $db->prepare("SELECT * FROM habitaciones WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Obtener camas de una habitación
     *
     * @param int $idHabitacion
     * @return array
     */
    public static function getCamas($idHabitacion)
    {
        $db   = self::getDb();
        $stmt = $db->prepare("
            SELECT * FROM camas
            WHERE id_habitacion = :id_habitacion
            ORDER BY numero ASC
        ");
        $stmt->execute([':id_habitacion' => $idHabitacion]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener camas disponibles para un rango de fechas
     *
     * @param int $idHabitacion
     * @param string $fechaInicio
     * @param string $fechaFin
     * @param int|null $idReservaExcluir Para excluir una reserva (al editar)
     * @return array
     */
    public static function getCamasDisponibles($idHabitacion, $fechaInicio, $fechaFin, $idReservaExcluir = null)
    {
        $db = self::getDb();

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

        if ($idReservaExcluir !== null) {
            $sql .= " AND r.id != :id_reserva_excluir";
        }

        $sql .= "
            )
            ORDER BY numero ASC
        ";

        $params = [
            ':id_habitacion' => $idHabitacion,
            ':fecha_inicio'  => $fechaInicio,
            ':fecha_fin'     => $fechaFin,
        ];

        if ($idReservaExcluir !== null) {
            $params[':id_reserva_excluir'] = $idReservaExcluir;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Contar camas disponibles totales
     *
     * @param string $fechaInicio
     * @param string $fechaFin
     * @param int|null $idReservaExcluir
     * @return int
     */
    public static function contarCamasDisponibles($fechaInicio, $fechaFin, $idReservaExcluir = null)
    {
        $db = self::getDb();

        $sql = "
            SELECT COUNT(*) as disponibles
            FROM camas c
            WHERE id NOT IN (
                SELECT DISTINCT rc.id_cama
                FROM reservas_camas rc
                INNER JOIN reservas r ON rc.id_reserva = r.id
                WHERE r.estado IN ('pendiente', 'reservada')
                AND (r.fecha_inicio <= :fecha_fin AND r.fecha_fin >= :fecha_inicio)
        ";

        if ($idReservaExcluir !== null) {
            $sql .= " AND r.id != :id_reserva_excluir";
        }

        $sql .= ")";

        $params = [
            ':fecha_inicio' => $fechaInicio,
            ':fecha_fin'    => $fechaFin,
        ];

        if ($idReservaExcluir !== null) {
            $params[':id_reserva_excluir'] = $idReservaExcluir;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['disponibles'];
    }

    /**
     * Obtener fechas completas (sin disponibilidad)
     *
     * @param string $mesInicio Formato: YYYY-MM
     * @param int $meses Número de meses a consultar
     * @return array Array de fechas en formato YYYY-MM-DD
     */
    public static function getFechasCompletas($mesInicio, $meses = 3)
    {
        $db = self::getDb();

        // Calcular rango de fechas
        $fechaInicio = $mesInicio . '-01';
        $fechaFin    = date('Y-m-t', strtotime($fechaInicio . ' +' . ($meses - 1) . ' months'));

        $sql = "
            SELECT DISTINCT DATE(r.fecha_inicio) as fecha
            FROM reservas r
            WHERE r.estado IN ('pendiente', 'reservada')
            AND r.fecha_inicio BETWEEN :fecha_inicio AND :fecha_fin
            AND (
                SELECT COUNT(DISTINCT rc.id_cama)
                FROM reservas_camas rc
                INNER JOIN reservas r2 ON rc.id_reserva = r2.id
                WHERE r2.estado IN ('pendiente', 'reservada')
                AND DATE(r.fecha_inicio) BETWEEN r2.fecha_inicio AND r2.fecha_fin
            ) >= (SELECT COUNT(*) FROM camas)
            ORDER BY fecha ASC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':fecha_inicio' => $fechaInicio,
            ':fecha_fin'    => $fechaFin,
        ]);

        $fechas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fechas[] = $row['fecha'];
        }

        return $fechas;
    }
}
