<?php
require 'conexion.php';
require 'functions.php';

// Verificar que sea una petición AJAX
if (! isset($_GET['fecha_inicio']) || ! isset($_GET['fecha_fin'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros incompletos']);
    exit;
}

$fecha_inicio = $_GET['fecha_inicio'];
$fecha_fin    = $_GET['fecha_fin'];

// Validar fechas
if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio) ||
    ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fin)) {
    http_response_code(400);
    echo json_encode(['error' => 'Formato de fecha inválido']);
    exit;
}

// Obtener habitaciones disponibles con número de camas libres
$habitaciones_disponibles = obtener_habitaciones_disponibles($conexionPDO, $fecha_inicio, $fecha_fin);

// Devolver JSON
header('Content-Type: application/json');
echo json_encode($habitaciones_disponibles);
