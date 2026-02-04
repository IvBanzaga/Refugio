<?php
/**
 * API para obtener disponibilidad total de camas
 * Retorna el número total de camas disponibles para un rango de fechas
 */

require 'conexion.php';
require 'functions.php';

header('Content-Type: application/json');

$fecha_inicio    = $_GET['fecha_inicio'] ?? '';
$fecha_fin       = $_GET['fecha_fin'] ?? '';
$excluir_reserva = isset($_GET['excluir_reserva']) ? intval($_GET['excluir_reserva']) : null;

if (empty($fecha_inicio) || empty($fecha_fin)) {
    echo json_encode(['disponibles' => 0, 'error' => 'Fechas no proporcionadas']);
    exit;
}

try {
    $disponibles = obtener_total_camas_disponibles($conexionPDO, $fecha_inicio, $fecha_fin, $excluir_reserva);
    echo json_encode([
        'disponibles'     => $disponibles,
        'total'           => 26,
        'fecha_inicio'    => $fecha_inicio,
        'fecha_fin'       => $fecha_fin,
        'excluir_reserva' => $excluir_reserva,
    ]);
} catch (Exception $e) {
    echo json_encode(['disponibles' => 0, 'error' => $e->getMessage()]);
}
