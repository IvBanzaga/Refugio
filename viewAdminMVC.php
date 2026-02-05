<?php
/**
 * Vista Principal del Administrador - Versión MVC
 *
 * Este archivo reemplaza viewAdmin.php usando el nuevo sistema de vistas
 */

// Inicialización
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cargar bootstrap que incluye todo lo necesario
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/functions.php';

// Funciones helper para parsear datos de No Socios
function parsear_datos_no_socio($observaciones)
{
    if (empty($observaciones)) {
        return null;
    }

    // Formato NUEVO: NO_SOCIO|nombre|DNI:xxx|Tel:xxx|Email:xxx|Grupo:xxx|||ACTIVIDAD:xxx
    if (strpos($observaciones, 'NO_SOCIO|') === 0) {
        $partes           = explode('|||ACTIVIDAD:', $observaciones);
        $datos_personales = $partes[0];
        $actividad        = isset($partes[1]) ? $partes[1] : '';

        // Extraer solo el nombre (segunda parte antes del primer |)
        $campos = explode('|', $datos_personales);
        $nombre = isset($campos[1]) ? $campos[1] : 'No Socio';

        // Extraer grupo de montañeros
        $grupo = '';
        foreach ($campos as $campo) {
            if (strpos($campo, 'Grupo:') === 0) {
                $grupo = str_replace('Grupo:', '', $campo);
                break;
            }
        }

        // Determinar montañero
        $montanero = 'Otro';
        if (! empty($grupo)) {
            if ($grupo === 'Grupo de Montañeros de Tenerife') {
                $montanero = 'GMT';
            } else {
                $montanero = $grupo;
            }
        }

        return [
            'es_no_socio'     => true,
            'nombre'          => $nombre,
            'actividad'       => $actividad,
            'grupo'           => $grupo,
            'montanero'       => $montanero,
            'datos_completos' => $observaciones,
        ];
    }

    // Formato ANTIGUO: NO SOCIO: nombre | DNI: xxx | Tel: xxx | Email: xxx | Grupo: xxx | Actividad: xxx
    if (strpos($observaciones, 'NO SOCIO:') === 0) {
        $partes          = explode(' | ', $observaciones);
        $nombre_completo = str_replace('NO SOCIO: ', '', $partes[0]);

        // Buscar la actividad
        $actividad = '';
        foreach ($partes as $parte) {
            if (strpos($parte, 'Actividad:') === 0) {
                $actividad = trim(str_replace('Actividad:', '', $parte));
                break;
            }
        }

        return [
            'es_no_socio'     => true,
            'nombre'          => $nombre_completo,
            'actividad'       => $actividad,
            'datos_completos' => $observaciones,
        ];
    }

    return null;
}

function mostrar_usuario_reserva($reserva)
{
    // Si tiene nombre de usuario, es un socio
    if (! empty($reserva['nombre'])) {
        return [
            'display'   => htmlspecialchars($reserva['nombre'] . ' ' . $reserva['apellido1']) . '<br><small class="text-muted">' . htmlspecialchars($reserva['email'] ?? '') . ' | <i class="bi bi-telephone"></i> ' . htmlspecialchars($reserva['telf'] ?? '') . '</small>',
            'email'     => htmlspecialchars($reserva['email'] ?? ''),
            'actividad' => htmlspecialchars($reserva['observaciones'] ?? '-'),
            'montanero' => 'GMT',
        ];
    }

    // Si no tiene nombre, puede ser no socio o reserva especial
    $datos_no_socio = parsear_datos_no_socio($reserva['observaciones']);

    if ($datos_no_socio) {
        return [
            'display'   => '🎫 NO SOCIO: ' . htmlspecialchars($datos_no_socio['nombre']),
            'email'     => '',
            'actividad' => htmlspecialchars($datos_no_socio['actividad']),
            'montanero' => htmlspecialchars($datos_no_socio['montanero'] ?? 'Otro'),
        ];
    }

    // Es una reserva especial
    $montanero_especial = '-';
    $motivo_display     = $reserva['observaciones'] ?? '-';

    if (! empty($reserva['observaciones']) && strpos($reserva['observaciones'], '|Grupo:') !== false) {
        $partes         = explode('|Grupo:', $reserva['observaciones']);
        $motivo_display = $partes[0];
        $grupo          = isset($partes[1]) ? $partes[1] : '';

        if ($grupo === 'Grupo de Montañeros de Tenerife') {
            $montanero_especial = 'GMT';
        } elseif (! empty($grupo)) {
            $montanero_especial = $grupo;
        } else {
            $montanero_especial = 'Otro';
        }
    }

    return [
        'display'   => '🎫 ESPECIAL: ' . htmlspecialchars($motivo_display),
        'email'     => '',
        'actividad' => htmlspecialchars($motivo_display),
        'montanero' => htmlspecialchars($montanero_especial),
    ];
}

// Comprobación de autenticación y rol
if (! isset($_SESSION['userId']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}
session_regenerate_id(true);

// Recuperar mensajes de la sesión (patrón PRG)
$mensaje      = $_SESSION['mensaje'] ?? '';
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'success';
unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);

$accion = isset($_POST['accion']) ? $_POST['accion'] : (isset($_GET['accion']) ? $_GET['accion'] : 'dashboard');

// Procesar exportación de usuarios (GET)
if ($accion === 'export_usuarios_csv' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $search = $_GET['search'] ?? '';
    $sort   = $_GET['sort'] ?? 'num_socio';
    $dir    = $_GET['dir'] ?? 'ASC';

    export_usuarios_csv($conexionPDO, [
        'search'    => $search,
        'order_by'  => $sort,
        'order_dir' => $dir,
    ]);
    exit;
}

if ($accion === 'export_usuarios_pdf' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $search = $_GET['search'] ?? '';
    $sort   = $_GET['sort'] ?? 'num_socio';
    $dir    = $_GET['dir'] ?? 'ASC';

    export_usuarios_pdf($conexionPDO, [
        'search'    => $search,
        'order_by'  => $sort,
        'order_dir' => $dir,
    ]);
    exit;
}

// Cargar controladores
require_once __DIR__ . '/src/Controllers/ReservaController.php';
require_once __DIR__ . '/src/Controllers/UsuarioController.php';

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ! empty($accion)) {
    $reservaController = new ReservaController($conexionPDO);
    $usuarioController = new UsuarioController($conexionPDO);

    // Acciones de Reservas
    if ($accion === 'crear_reserva_socio') {
        $reservaController->crearReservaSocio();
    } elseif ($accion === 'crear_reserva_no_socio') {
        $reservaController->crearReservaNoSocio();
    } elseif ($accion === 'crear_reserva_especial') {
        $reservaController->crearReservaEspecial();
    } elseif ($accion === 'aprobar_reserva') {
        $reservaController->aprobarReserva();
    } elseif ($accion === 'rechazar_reserva') {
        $reservaController->rechazarReserva();
    } elseif ($accion === 'cancelar_reserva_admin') {
        $reservaController->cancelarReserva();
    } elseif ($accion === 'editar_reserva') {
        $reservaController->editarReserva();
    } elseif ($accion === 'eliminar_reservas_canceladas') {
        $reservaController->eliminarReservasCanceladas();
    }
    // Acciones de Usuarios
    elseif ($accion === 'crear_usuario') {
        $usuarioController->crearUsuario();
    } elseif ($accion === 'actualizar_usuario') {
        $usuarioController->actualizarUsuario();
    } elseif ($accion === 'eliminar_usuario') {
        $usuarioController->eliminarUsuario();
    } elseif ($accion === 'cambiar_contrasena') {
        $usuarioController->cambiarContrasena();
    }
    // Si llegamos aquí, la acción no fue manejada
    exit;
}

// ===== CARGAR DATOS PARA LAS VISTAS =====

// Datos comunes
$usuario_actual = [
    'nombre' => $_SESSION['user'] ?? 'Admin',
    'email'  => $_SESSION['email'] ?? '',
    'rol'    => $_SESSION['rol'] ?? 'admin',
];

// Dashboard: cargar datos
if ($accion === 'dashboard') {
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

    // Obtener reservas pendientes
    $reservas_pendientes = listar_reservas($conexionPDO, ['estado' => 'pendiente']) ?? [];

    // Contar reservas aprobadas y canceladas
    $reservas_aprobadas_count  = contar_reservas($conexionPDO, ['estado' => 'reservada']);
    $reservas_canceladas_count = contar_reservas($conexionPDO, ['estado' => 'cancelada']);

    // Cargar vista
    include VIEWS_PATH . '/admin/dashboard.php';
    exit;
}

// Usuarios: cargar datos
if ($accion === 'usuarios' || $accion === 'editar_usuario') {
    $page_usuarios      = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $search_usuarios    = $_GET['search'] ?? '';
    $sort_usuarios      = $_GET['sort'] ?? 'num_socio';
    $order_dir_usuarios = $_GET['dir'] ?? 'ASC';
    $limit_usuarios     = 20;
    $offset_usuarios    = ($page_usuarios - 1) * $limit_usuarios;

    // Obtener usuarios con filtros
    $usuarios = listar_usuarios($conexionPDO, [
        'search'    => $search_usuarios,
        'order_by'  => $sort_usuarios,
        'order_dir' => $order_dir_usuarios,
        'limit'     => $limit_usuarios,
        'offset'    => $offset_usuarios,
    ]);

    // Contar total de usuarios
    $total_usuarios   = contar_usuarios($conexionPDO, ['search' => $search_usuarios]);
    $paginas_usuarios = ceil($total_usuarios / $limit_usuarios);

    // Si es editar, cargar datos del usuario
    $usuario_editar = null;
    if ($accion === 'editar_usuario' && isset($_GET['id'])) {
        $usuario_editar = obtener_usuario($conexionPDO, $_GET['id']);
    }

    // Cargar vista
    include VIEWS_PATH . '/admin/usuarios.php';
    exit;
}

// Reservas: cargar datos
if ($accion === 'reservas') {
    $tab       = $_GET['tab'] ?? 'pendientes';
    $page      = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $search    = $_GET['search'] ?? '';
    $sort      = $_GET['sort'] ?? 'fecha_inicio';
    $order_dir = $_GET['dir'] ?? 'ASC';
    $limit     = 20;
    $offset    = ($page - 1) * $limit;

    // Obtener totales
    $total_pendientes = contar_reservas($conexionPDO, ['estado' => 'pendiente']);
    $total_aprobadas  = contar_reservas($conexionPDO, ['estado' => 'reservada']);
    $total_canceladas = contar_reservas($conexionPDO, ['estado' => 'cancelada']);

    // Obtener reservas según el tab activo
    if ($tab === 'pendientes') {
        $reservas_pendientes = listar_reservas($conexionPDO, [
            'estado'    => 'pendiente',
            'search'    => $search,
            'order_by'  => $sort,
            'order_dir' => $order_dir,
            'limit'     => $limit,
            'offset'    => $offset,
        ]);
        $paginas_pendientes = ceil($total_pendientes / $limit);
    } elseif ($tab === 'aprobadas') {
        $reservas_aprobadas = listar_reservas($conexionPDO, [
            'estado'    => 'reservada',
            'search'    => $search,
            'order_by'  => $sort,
            'order_dir' => $order_dir,
            'limit'     => $limit,
            'offset'    => $offset,
        ]);
        $paginas_aprobadas = ceil($total_aprobadas / $limit);
    } else {
        $reservas_canceladas = listar_reservas($conexionPDO, [
            'estado'    => 'cancelada',
            'search'    => $search,
            'order_by'  => $sort,
            'order_dir' => $order_dir,
            'limit'     => $limit,
            'offset'    => $offset,
        ]);
        $paginas_canceladas = ceil($total_canceladas / $limit);
    }

    // Cargar vista
    include VIEWS_PATH . '/admin/reservas.php';
    exit;
}

// Si no es ninguna acción específica, redirigir al dashboard
header('Location: ?accion=dashboard');
exit;
