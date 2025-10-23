<?php
require_once 'conexion.php';
require_once 'functions.php';

// Verificar sesión
if (! isset($_SESSION['user_id'])) {
    echo json_encode(['exito' => false, 'mensaje' => 'No autorizado']);
    exit;
}

$id_usuario = $_SESSION['user_id'];

// Procesar según la acción
$accion = $_POST['accion'] ?? '';

if ($accion === 'subir') {
    // Verificar que se haya subido un archivo
    if (! isset($_FILES['foto']) || $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE) {
        echo json_encode(['exito' => false, 'mensaje' => 'No se seleccionó ningún archivo']);
        exit;
    }

    $resultado = subir_foto_perfil($conexionPDO, $id_usuario, $_FILES['foto']);
    echo json_encode($resultado);

} elseif ($accion === 'eliminar') {
    $resultado = eliminar_foto_perfil($conexionPDO, $id_usuario);
    echo json_encode($resultado);

} else {
    echo json_encode(['exito' => false, 'mensaje' => 'Acción no válida']);
}
