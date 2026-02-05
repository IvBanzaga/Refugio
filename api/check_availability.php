<?php
/**
 * API para verificar disponibilidad de camas
 *
 * Endpoint AJAX que verifica si hay camas disponibles para un rango de fechas
 */

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../functions.php';

// Configurar respuesta JSON
header('Content-Type: application/json');

// Verificar autenticación
if (! isset($_SESSION['userId'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

// Obtener parámetros
$fecha_entrada = $_GET['fecha_entrada'] ?? '';
$fecha_salida  = $_GET['fecha_salida'] ?? '';
$numero_camas  = (int) ($_GET['numero_camas'] ?? 0);

// Validar parámetros
if (empty($fecha_entrada) || empty($fecha_salida) || $numero_camas <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros inválidos']);
    exit;
}

// Validar formato de fechas
$entrada = DateTime::createFromFormat('Y-m-d', $fecha_entrada);
$salida  = DateTime::createFromFormat('Y-m-d', $fecha_salida);

if (! $entrada || ! $salida || $entrada >= $salida) {
    http_response_code(400);
    echo json_encode(['error' => 'Fechas inválidas']);
    exit;
}

try {
    // Obtener todas las habitaciones
    $stmt         = $conexionPDO->query("SELECT id, numero_camas FROM Habitaciones");
    $habitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($habitaciones)) {
        echo json_encode([
            'available'      => false,
            'beds_available' => 0,
            'message'        => 'No hay habitaciones configuradas',
        ]);
        exit;
    }

    // Calcular total de camas
    $total_camas = array_sum(array_column($habitaciones, 'numero_camas'));

    // Consulta para obtener camas ocupadas en el rango de fechas
    $sql = "SELECT SUM(numero_camas) as camas_ocupadas
            FROM Reservas
            WHERE estado IN ('aprobada', 'pendiente')
            AND (
                (fecha_entrada <= :fecha_entrada AND fecha_salida > :fecha_entrada)
                OR (fecha_entrada < :fecha_salida AND fecha_salida >= :fecha_salida)
                OR (fecha_entrada >= :fecha_entrada AND fecha_salida <= :fecha_salida)
            )";

    $stmt = $conexionPDO->prepare($sql);
    $stmt->execute([
        'fecha_entrada' => $fecha_entrada,
        'fecha_salida'  => $fecha_salida,
    ]);

    $resultado         = $stmt->fetch(PDO::FETCH_ASSOC);
    $camas_ocupadas    = (int) ($resultado['camas_ocupadas'] ?? 0);
    $camas_disponibles = $total_camas - $camas_ocupadas;

    // Verificar si hay suficientes camas disponibles
    $disponible = $camas_disponibles >= $numero_camas;

    // Preparar respuesta
    $respuesta = [
        'available'      => $disponible,
        'beds_available' => $camas_disponibles,
        'beds_total'     => $total_camas,
        'beds_occupied'  => $camas_ocupadas,
        'beds_requested' => $numero_camas,
    ];

    // Agregar mensaje descriptivo
    if ($disponible) {
        $respuesta['message'] = "Hay {$camas_disponibles} camas disponibles para las fechas seleccionadas.";
    } else {
        $respuesta['message'] = "Solo hay {$camas_disponibles} camas disponibles, pero necesita {$numero_camas}.";
    }

    echo json_encode($respuesta);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error'   => 'Error al verificar disponibilidad',
        'message' => $e->getMessage(),
    ]);
}
