<?php
/**
 * Vista Principal del Socio - Versión MVC
 *
 * Este archivo reemplaza viewSocio.php usando el nuevo sistema de vistas
 */

// Inicialización
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cargar bootstrap que incluye todo lo necesario
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/functions.php';

// Comprobación de autenticación y rol
if (! isset($_SESSION['userId']) || $_SESSION['rol'] !== 'user') {
    header('Location: login.php');
    exit;
}
session_regenerate_id(true);

// Recuperar mensajes de la sesión (patrón PRG)
$mensaje      = $_SESSION['mensaje'] ?? '';
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'success';
unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);

$accion = isset($_POST['accion']) ? $_POST['accion'] : (isset($_GET['accion']) ? $_GET['accion'] : 'calendario');

// Cargar controladores
require_once __DIR__ . '/src/Controllers/ReservaController.php';
require_once __DIR__ . '/src/Controllers/UsuarioController.php';

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ! empty($accion)) {
    $reservaController = new ReservaController($conexionPDO);
    $usuarioController = new UsuarioController($conexionPDO);

    // Acciones de Reservas
    if ($accion === 'crear_reserva') {
        $reservaController->crearReservaSocio();
    } elseif ($accion === 'cancelar_reserva') {
        $reservaController->cancelarReserva();
    } elseif ($accion === 'editar_reserva') {
        $reservaController->editarReserva();
    }
    // Acciones de Usuario
    elseif ($accion === 'actualizar_perfil') {
        $usuarioController->actualizarPerfil();
    } elseif ($accion === 'cambiar_contrasena') {
        $usuarioController->cambiarContrasena();
    }
    // Si llegamos aquí, la acción no fue manejada
    exit;
}

// ===== CARGAR DATOS PARA LAS VISTAS =====

// Datos del usuario actual
$usuario_actual = obtener_usuario($conexionPDO, $_SESSION['userId']);

// Calendario: cargar datos de disponibilidad
if ($accion === 'calendario') {
    // Obtener mes y año actual o de parámetros
    $mes_actual  = isset($_GET['mes']) ? (int) $_GET['mes'] : date('n');
    $anio_actual = isset($_GET['anio']) ? (int) $_GET['anio'] : date('Y');

    // Calcular mes anterior y siguiente
    $fecha_actual   = mktime(0, 0, 0, $mes_actual, 1, $anio_actual);
    $mes_anterior   = date('n', strtotime('-1 month', $fecha_actual));
    $anio_anterior  = date('Y', strtotime('-1 month', $fecha_actual));
    $mes_siguiente  = date('n', strtotime('+1 month', $fecha_actual));
    $anio_siguiente = date('Y', strtotime('+1 month', $fecha_actual));

    // Obtener primer día del mes y días en el mes
    $primer_dia        = mktime(0, 0, 0, $mes_actual, 1, $anio_actual);
    $dia_semana_inicio = date('N', $primer_dia); // 1=Lunes, 7=Domingo
    $dias_en_mes       = cal_days_in_month(CAL_GREGORIAN, $mes_actual, $anio_actual);

    // Cargar vista
    include VIEWS_PATH . '/socio/calendario.php';
    exit;
}

// Mis Reservas: cargar listado de reservas del usuario
if ($accion === 'mis_reservas') {
    // Obtener todas las reservas del usuario
    $mis_reservas = listar_reservas($conexionPDO, ['id_usuario' => $_SESSION['userId']]) ?? [];

    // Cargar vista
    include VIEWS_PATH . '/socio/mis-reservas.php';
    exit;
}

// Nueva Reserva: mostrar formulario
if ($accion === 'nueva_reserva') {
    $habitaciones          = listar_habitaciones($conexionPDO) ?? [];
    $fecha_preseleccionada = $_GET['fecha'] ?? '';

    // Cargar vista
    include VIEWS_PATH . '/socio/nueva-reserva.php';
    exit;
}

// Perfil: mostrar datos del usuario
if ($accion === 'perfil') {
    $usuario = $usuario_actual; // Usar datos ya cargados

    // Cargar vista
    include VIEWS_PATH . '/socio/perfil.php';
    exit;
}

// Si no es ninguna acción específica, redirigir al calendario
header('Location: ?accion=calendario');
exit;
