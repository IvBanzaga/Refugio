<?php
require 'conexion.php';
require 'functions.php';

header('Content-Type: application/json');

// Obtener rango de fechas (próximos 6 meses por defecto)
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-d');
$fecha_fin    = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d', strtotime('+6 months'));

try {
    $fechas_completas = [];

    // Iterar por cada día en el rango
    $current = new DateTime($fecha_inicio);
    $end     = new DateTime($fecha_fin);

    while ($current <= $end) {
        $fecha = $current->format('Y-m-d');

        // Contar camas libres para esta fecha
        $camas_libres = contar_camas_libres_por_fecha($conexionPDO, $fecha);

        // Si no hay camas libres, agregar a la lista
        if ($camas_libres === 0) {
            $fechas_completas[] = $fecha;
        }

        $current->modify('+1 day');
    }

    echo json_encode([
        'exito'            => true,
        'fechas_completas' => $fechas_completas,
    ]);

} catch (Exception $e) {
    echo json_encode([
        'exito'   => false,
        'mensaje' => 'Error al obtener fechas completas',
        'error'   => $e->getMessage(),
    ]);
}
