<?php

    require 'conexion.php';
    require 'functions.php';

    /* TODO: Comprobación de autenticación y rol. Se usa session_regenerate_id(true) para evitar robo de sesión (fijación de sesión). Depuración: puedes poner breakpoint aquí para comprobar el estado de $_SESSION. */
    if (! isset($_SESSION['userId']) || $_SESSION['rol'] !== 'admin') {
        header('Location: login.php');
        exit;
    }
    session_regenerate_id(true); // Justificación: previene ataques de fijación de sesión

    $mensaje      = '';
    $tipo_mensaje = 'success';
    $accion       = isset($_POST['accion']) ? $_POST['accion'] : (isset($_GET['accion']) ? $_GET['accion'] : 'dashboard');

    /* TODO: Procesar acciones del panel admin. Todas las acciones usan POST para mayor seguridad.
       Depuración: breakpoint útil para ver los datos recibidos por POST. */

    // Procesar acciones de usuarios
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        switch ($accion) {
            case 'crear_usuario':
                $datos = [
                    'num_socio' => sanitize_input($_POST['num_socio']),
                    'dni'       => sanitize_input($_POST['dni']),
                    'telf'      => sanitize_input($_POST['telf']),
                    'email'     => sanitize_input($_POST['email']),
                    'nombre'    => sanitize_input($_POST['nombre']),
                    'apellido1' => sanitize_input($_POST['apellido1']),
                    'apellido2' => sanitize_input($_POST['apellido2']),
                    'password'  => $_POST['password'],
                    'rol'       => sanitize_input($_POST['rol']),
                ];

                if (crear_usuario($conexionPDO, $datos)) {
                    $mensaje = "Usuario creado exitosamente";
                } else {
                    $mensaje      = "Error al crear el usuario";
                    $tipo_mensaje = 'danger';
                }
                $accion = 'usuarios';
                break;

            case 'actualizar_usuario':
                $id    = (int) $_POST['id'];
                $datos = [
                    'num_socio' => sanitize_input($_POST['num_socio']),
                    'dni'       => sanitize_input($_POST['dni']),
                    'telf'      => sanitize_input($_POST['telf']),
                    'email'     => sanitize_input($_POST['email']),
                    'nombre'    => sanitize_input($_POST['nombre']),
                    'apellido1' => sanitize_input($_POST['apellido1']),
                    'apellido2' => sanitize_input($_POST['apellido2']),
                    'password'  => $_POST['password'],
                    'rol'       => sanitize_input($_POST['rol']),
                ];

                if (actualizar_usuario($conexionPDO, $id, $datos)) {
                    $mensaje = "Usuario actualizado exitosamente";
                } else {
                    $mensaje      = "Error al actualizar el usuario";
                    $tipo_mensaje = 'danger';
                }
                $accion = 'usuarios';
                break;

            case 'eliminar_usuario':
                $id = (int) $_POST['id'];
                if (eliminar_usuario($conexionPDO, $id)) {
                    $mensaje = "Usuario eliminado exitosamente";
                } else {
                    $mensaje      = "Error al eliminar el usuario";
                    $tipo_mensaje = 'danger';
                }
                $accion = 'usuarios';
                break;

            case 'aprobar_reserva':
                $id = (int) $_POST['id'];
                if (actualizar_estado_reserva($conexionPDO, $id, 'reservada')) {
                    $mensaje = "Reserva aprobada exitosamente";
                } else {
                    $mensaje      = "Error al aprobar la reserva";
                    $tipo_mensaje = 'danger';
                }
                $accion = 'reservas';
                break;

            case 'rechazar_reserva':
                $id = (int) $_POST['id'];
                if (cancelar_reserva($conexionPDO, $id)) {
                    $mensaje = "Reserva rechazada exitosamente";
                } else {
                    $mensaje      = "Error al rechazar la reserva";
                    $tipo_mensaje = 'danger';
                }
                $accion = 'reservas';
                break;

            case 'cancelar_reserva_admin':
                $id = (int) $_POST['id'];
                if (cancelar_reserva($conexionPDO, $id)) {
                    $mensaje = "Reserva cancelada exitosamente";
                } else {
                    $mensaje      = "Error al cancelar la reserva";
                    $tipo_mensaje = 'danger';
                }
                $accion = 'reservas';
                break;

            case 'editar_reserva_admin':
                $id_reserva    = (int) $_POST['id_reserva'];
                $fecha_inicio  = sanitize_input($_POST['fecha_inicio']);
                $fecha_fin     = sanitize_input($_POST['fecha_fin']);
                $id_habitacion = isset($_POST['id_habitacion']) && $_POST['id_habitacion'] !== '' ? (int) $_POST['id_habitacion'] : null;
                $numero_camas  = isset($_POST['numero_camas']) && $_POST['numero_camas'] !== '' ? (int) $_POST['numero_camas'] : 0;

                // Validar fechas
                if ($fecha_inicio >= $fecha_fin) {
                    $mensaje      = "La fecha de inicio debe ser anterior a la fecha de fin";
                    $tipo_mensaje = 'danger';
                } else {
                    if (editar_reserva_admin($conexionPDO, $id_reserva, $fecha_inicio, $fecha_fin, $id_habitacion, $numero_camas)) {
                        $mensaje = "Reserva actualizada exitosamente";
                    } else {
                        $mensaje      = "Error al actualizar la reserva. Verifica que haya camas disponibles.";
                        $tipo_mensaje = 'danger';
                    }
                }
                $accion = 'reservas';
                break;

            case 'crear_reserva_especial':
                $datos = [
                    'motivo'        => sanitize_input($_POST['motivo']),
                    'fecha_inicio'  => sanitize_input($_POST['fecha_inicio']),
                    'fecha_fin'     => sanitize_input($_POST['fecha_fin']),
                    'id_habitacion' => (int) $_POST['id_habitacion'],
                    'numero_camas'  => (int) $_POST['numero_camas'],
                ];

                // Validar fechas
                if ($datos['fecha_inicio'] >= $datos['fecha_fin']) {
                    $mensaje      = "La fecha de inicio debe ser anterior a la fecha de fin";
                    $tipo_mensaje = 'danger';
                } elseif ($datos['numero_camas'] < 1) {
                    $mensaje      = "Debe seleccionar al menos 1 cama";
                    $tipo_mensaje = 'danger';
                } else {
                    // Si id_habitacion es 0, es "Todo el Refugio"
                    if ($datos['id_habitacion'] === 0) {
                        $resultado = crear_reserva_todo_refugio($conexionPDO, $datos);
                        if ($resultado) {
                            $mensaje = "Reserva especial creada para TODO EL REFUGIO: " . htmlspecialchars($datos['motivo']);
                        } else {
                            $mensaje      = "Error al crear la reserva para todo el refugio. Verifica que haya camas disponibles.";
                            $tipo_mensaje = 'danger';
                        }
                    } else {
                        // Crear reserva especial para habitación individual
                        if (crear_reserva_especial_admin($conexionPDO, $datos)) {
                            $mensaje = "Reserva especial creada exitosamente: " . htmlspecialchars($datos['motivo']);
                        } else {
                            $mensaje      = "Error al crear la reserva especial. Verifica que haya camas disponibles.";
                            $tipo_mensaje = 'danger';
                        }
                    }
                }
                $accion = 'reservas';
                break;

            case 'crear_reserva_socio':
                try {
                    $id_usuario    = (int) $_POST['id_usuario'];
                    $id_habitacion = (int) $_POST['id_habitacion'];
                    $numero_camas  = (int) $_POST['numero_camas'];
                    $fecha_inicio  = sanitize_input($_POST['fecha_inicio']);
                    $fecha_fin     = sanitize_input($_POST['fecha_fin']);

                    // Validar fechas
                    if ($fecha_inicio >= $fecha_fin) {
                        throw new Exception("La fecha de inicio debe ser anterior a la fecha de fin");
                    }

                    // Validar número de camas
                    if ($numero_camas < 1) {
                        throw new Exception("Debe seleccionar al menos 1 cama");
                    }

                    // Crear reserva para el socio (aprobada automáticamente)
                    $datos_reserva = [
                        'id_usuario'    => $id_usuario,
                        'id_habitacion' => $id_habitacion,
                        'numero_camas'  => $numero_camas,
                        'fecha_inicio'  => $fecha_inicio,
                        'fecha_fin'     => $fecha_fin,
                    ];

                    $id_reserva = crear_reserva_para_socio($conexionPDO, $datos_reserva);

                    if ($id_reserva) {
                        // Obtener nombre del socio
                        $stmt = $conexionPDO->prepare("SELECT nombre, apellido1 FROM usuarios WHERE id = :id");
                        $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
                        $stmt->execute();
                        $socio = $stmt->fetch(PDO::FETCH_ASSOC);

                        $mensaje = "Reserva creada y aprobada automáticamente para {$socio['nombre']} {$socio['apellido1']}";
                    } else {
                        throw new Exception("No hay suficientes camas disponibles");
                    }
                } catch (Exception $e) {
                    $mensaje      = "Error al crear reserva: " . $e->getMessage();
                    $tipo_mensaje = 'danger';
                }
                $accion = 'reservas';
                break;
        }
    }

    // Obtener datos según la acción
    $usuarios            = [];
    $reservas_pendientes = [];
    $reservas_aprobadas  = [];
    $habitaciones        = [];
    $usuario_editar      = null;

    if ($accion === 'usuarios' || $accion === 'editar_usuario') {
        $usuarios = listar_usuarios($conexionPDO);

        if ($accion === 'editar_usuario' && isset($_GET['id'])) {
            $usuario_editar = obtener_usuario($conexionPDO, (int) $_GET['id']);
        }
    } elseif ($accion === 'reservas') {
        $reservas_pendientes = listar_reservas($conexionPDO, ['estado' => 'pendiente']);
        $reservas_aprobadas  = listar_reservas($conexionPDO, ['estado' => 'reservada']);
        $reservas_canceladas = listar_reservas($conexionPDO, ['estado' => 'cancelada']);
    } elseif ($accion === 'dashboard') {
        $reservas_pendientes = listar_reservas($conexionPDO, ['estado' => 'pendiente']);
        $habitaciones        = listar_habitaciones($conexionPDO);

        // Obtener mes y año actual o seleccionado para el calendario
        $mes_actual  = isset($_GET['mes']) ? (int) $_GET['mes'] : (int) date('n');
        $anio_actual = isset($_GET['anio']) ? (int) $_GET['anio'] : (int) date('Y');

        // Calcular mes anterior y siguiente
        $mes_anterior  = $mes_actual - 1;
        $anio_anterior = $anio_actual;
        if ($mes_anterior < 1) {
            $mes_anterior = 12;
            $anio_anterior--;
        }

        $mes_siguiente  = $mes_actual + 1;
        $anio_siguiente = $anio_actual;
        if ($mes_siguiente > 12) {
            $mes_siguiente = 1;
            $anio_siguiente++;
        }

        // Obtener días del mes
        $primer_dia        = mktime(0, 0, 0, $mes_actual, 1, $anio_actual);
        $dias_en_mes       = date('t', $primer_dia);
        $dia_semana_inicio = date('N', $primer_dia); // 1 = Lunes, 7 = Domingo
    }

    // Función para obtener el mes en español
    function mes_espanol($mes)
    {
        $meses = [
            1 => 'Enero', 2       => 'Febrero', 3  => 'Marzo', 4      => 'Abril',
            5 => 'Mayo', 6        => 'Junio', 7    => 'Julio', 8      => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
        return $meses[(int) $mes];
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador - Refugio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1e3a8a 0%, #3b82f6 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255,255,255,0.1);
            border-left: 3px solid #fff;
        }
        .card-stat {
            border-left: 4px solid;
            transition: transform 0.2s;
        }
        .card-stat:hover {
            transform: translateY(-5px);
        }
        .card-stat.primary {
            border-color: #3b82f6;
        }
        .card-stat.success {
            border-color: #10b981;
        }
        .card-stat.warning {
            border-color: #f59e0b;
        }
        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
        }
        /* Estilos del calendario */
        .calendario {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
        }
        .dia-calendario {
            aspect-ratio: 1;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            background: white;
            min-height: 80px;
        }
        .dia-calendario:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        .dia-calendario.vacio {
            background: #f9fafb;
            cursor: default;
            border-color: transparent;
        }
        .dia-calendario.vacio:hover {
            transform: none;
            box-shadow: none;
        }
        .dia-calendario.pasado {
            background: #f3f4f6;
            color: #9ca3af;
            cursor: default;
        }
        .dia-calendario.pasado:hover {
            transform: none;
            box-shadow: none;
        }
        .dia-calendario .numero-dia {
            font-weight: bold;
            font-size: 1.2em;
            margin-bottom: 8px;
        }
        .dia-calendario .info-reservas {
            font-size: 0.75em;
            margin-top: 5px;
        }
        .dia-calendario .badge {
            font-size: 0.65em;
            padding: 2px 6px;
            margin: 2px 0;
            display: block;
            width: fit-content;
        }
        .dia-calendario.con-pendientes {
            border-color: #fbbf24;
            background: #fffbeb;
        }
        .dia-calendario.con-aprobadas {
            border-color: #10b981;
            background: #ecfdf5;
        }
        .dia-calendario.mixto {
            border-color: #3b82f6;
            background: linear-gradient(135deg, #fffbeb 50%, #ecfdf5 50%);
        }
        .dia-semana {
            text-align: center;
            font-weight: bold;
            padding: 12px;
            background: #f3f4f6;
            border-radius: 10px;
            color: #4b5563;
        }
        .nav-calendario {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .nav-calendario h4 {
            margin: 0;
            color: #1f2937;
            font-weight: 600;
        }
        .leyenda-calendario {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            padding: 15px;
            background: #f9fafb;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        .leyenda-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .leyenda-color {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            border: 2px solid;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-3 text-white border-bottom">
                    <h4><i class="bi bi-house-heart-fill"></i> Refugio</h4>
                    <small>Panel Administrador</small>
                    <div class="mt-2">
                        <small><?php echo htmlspecialchars($_SESSION['user']) ?></small>
                    </div>
                </div>
                <nav class="nav flex-column mt-3">
                    <a class="nav-link                                                                                                                                                                                                                                                                                                                                                       <?php echo $accion === 'dashboard' ? 'active' : '' ?>" href="?accion=dashboard">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a class="nav-link                                                                                                                                                                                                                                                                                                                                                       <?php echo $accion === 'usuarios' || $accion === 'editar_usuario' ? 'active' : '' ?>" href="?accion=usuarios">
                        <i class="bi bi-people-fill"></i> Usuarios
                    </a>
                    <a class="nav-link                                                                                                                                                                                                                                                                                                                                                       <?php echo $accion === 'reservas' ? 'active' : '' ?>" href="?accion=reservas">
                        <i class="bi bi-calendar-check"></i> Reservas
                    </a>
                    <hr class="text-white">
                    <a class="nav-link" href="logout.php">
                        <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                    </a>
                </nav>
            </div>

            <!-- Contenido principal -->
            <div class="col-md-10 p-4">
                <?php if (! empty($mensaje)): ?>
                    <div class="alert alert-<?php echo $tipo_mensaje ?> alert-dismissible fade show" role="alert">
                        <?php echo $mensaje ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($accion === 'dashboard'): ?>
                    <!-- Dashboard -->
                    <h2 class="mb-4">Dashboard</h2>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card card-stat primary shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted">Total Habitaciones</h6>
                                            <h2><?php echo count($habitaciones) ?></h2>
                                        </div>
                                        <i class="bi bi-door-open fs-1 text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-stat warning shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted">Reservas Pendientes</h6>
                                            <h2><?php echo count($reservas_pendientes) ?></h2>
                                        </div>
                                        <i class="bi bi-hourglass-split fs-1 text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-stat success shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted">Total Camas</h6>
                                            <h2>26</h2>
                                        </div>
                                        <i class="bi bi-grid-3x3-gap-fill fs-1 text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calendario de Reservas -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="bi bi-calendar3"></i> Calendario de Reservas</h5>
                        </div>
                        <div class="card-body">
                            <!-- Navegación del calendario -->
                            <div class="nav-calendario">
                                <a href="?accion=dashboard&mes=<?php echo $mes_anterior ?>&anio=<?php echo $anio_anterior ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-chevron-left"></i> Anterior
                                </a>
                                <h4><?php echo mes_espanol($mes_actual) . ' ' . $anio_actual ?></h4>
                                <a href="?accion=dashboard&mes=<?php echo $mes_siguiente ?>&anio=<?php echo $anio_siguiente ?>" class="btn btn-outline-primary">
                                    Siguiente <i class="bi bi-chevron-right"></i>
                                </a>
                            </div>

                            <!-- Leyenda -->
                            <div class="leyenda-calendario">
                                <div class="leyenda-item">
                                    <div class="leyenda-color" style="background: #fffbeb; border-color: #fbbf24;"></div>
                                    <span>Con reservas pendientes</span>
                                </div>
                                <div class="leyenda-item">
                                    <div class="leyenda-color" style="background: #ecfdf5; border-color: #10b981;"></div>
                                    <span>Con reservas aprobadas</span>
                                </div>
                                <div class="leyenda-item">
                                    <div class="leyenda-color" style="background: linear-gradient(135deg, #fffbeb 50%, #ecfdf5 50%); border-color: #3b82f6;"></div>
                                    <span>Mixto (pendientes y aprobadas)</span>
                                </div>
                                <div class="leyenda-item">
                                    <div class="leyenda-color" style="background: white; border-color: #e5e7eb;"></div>
                                    <span>Sin reservas</span>
                                </div>
                            </div>

                            <!-- Días de la semana -->
                            <div class="calendario mb-2">
                                <div class="dia-semana">L</div>
                                <div class="dia-semana">M</div>
                                <div class="dia-semana">X</div>
                                <div class="dia-semana">J</div>
                                <div class="dia-semana">V</div>
                                <div class="dia-semana">S</div>
                                <div class="dia-semana">D</div>
                            </div>

                            <!-- Días del mes -->
                            <div class="calendario">
                                <?php
                                    // Celdas vacías antes del primer día
                                    for ($i = 1; $i < $dia_semana_inicio; $i++) {
                                        echo '<div class="dia-calendario vacio"></div>';
                                    }

                                    // Días del mes
                                    $hoy = date('Y-m-d');
                                    for ($dia = 1; $dia <= $dias_en_mes; $dia++) {
                                        $fecha_actual = sprintf('%04d-%02d-%02d', $anio_actual, $mes_actual, $dia);
                                        $es_pasado    = $fecha_actual < $hoy;

                                        // Contar reservas para este día
                                        $stmt = $conexionPDO->prepare("
                                            SELECT estado, COUNT(*) as total
                                            FROM reservas
                                            WHERE :fecha BETWEEN fecha_inicio AND fecha_fin
                                            GROUP BY estado
                                        ");
                                        $stmt->bindParam(':fecha', $fecha_actual);
                                        $stmt->execute();
                                        $reservas_dia = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                        $pendientes = 0;
                                        $aprobadas  = 0;
                                        foreach ($reservas_dia as $r) {
                                            if ($r['estado'] === 'pendiente') {
                                                $pendientes = $r['total'];
                                            }

                                            if ($r['estado'] === 'reservada') {
                                                $aprobadas = $r['total'];
                                            }

                                        }

                                        // Contar camas libres para este día
                                        $camas_libres   = contar_camas_libres_por_fecha($conexionPDO, $fecha_actual);
                                        $total_camas    = contar_total_camas($conexionPDO);
                                        $camas_ocupadas = $total_camas - $camas_libres;

                                        // Determinar clase CSS
                                        $clase = 'dia-calendario';
                                        if ($es_pasado) {
                                            $clase .= ' pasado';
                                        } elseif ($pendientes > 0 && $aprobadas > 0) {
                                            $clase .= ' mixto';
                                        } elseif ($pendientes > 0) {
                                            $clase .= ' con-pendientes';
                                        } elseif ($aprobadas > 0) {
                                            $clase .= ' con-aprobadas';
                                        }

                                        echo "<div class='$clase'>";
                                        echo "<div class='numero-dia'>$dia</div>";

                                        if (! $es_pasado) {
                                            echo "<div class='info-reservas'>";

                                            // Mostrar camas disponibles
                                            if ($camas_libres === 0) {
                                                echo "<div class='camas-info text-danger mb-1'><strong>Completo</strong></div>";
                                            } else {
                                                $color_camas = $camas_libres < 5 ? 'text-warning' : 'text-success';
                                                echo "<div class='camas-info {$color_camas} mb-1'><i class='bi bi-door-open'></i> <strong>{$camas_libres}/{$total_camas}</strong> libres</div>";
                                            }

                                            // Mostrar reservas pendientes y aprobadas
                                            if ($pendientes > 0) {
                                                echo "<span class='badge bg-warning text-dark'>$pendientes pendiente" . ($pendientes > 1 ? 's' : '') . "</span> ";
                                            }
                                            if ($aprobadas > 0) {
                                                echo "<span class='badge bg-success'>$aprobadas aprobada" . ($aprobadas > 1 ? 's' : '') . "</span>";
                                            }

                                            echo "</div>";
                                        }

                                        echo '</div>';
                                    }
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Reservas pendientes de aprobación -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-warning text-white">
                            <h5 class="mb-0"><i class="bi bi-clock-history"></i> Reservas Pendientes de Aprobación</h5>
                        </div>
                        <div class="card-body">
                            <?php if (count($reservas_pendientes) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Solicitante</th>
                                                <th>Habitación</th>
                                                <th>Camas</th>
                                                <th>Fecha Entrada</th>
                                                <th>Fecha Salida</th>
                                                <th>Solicitado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($reservas_pendientes as $reserva): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($reserva['nombre'] . ' ' . $reserva['apellido1']) ?></strong><br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($reserva['email']) ?></small>
                                                    </td>
                                                    <td><?php echo $reserva['habitacion_numero'] ?></td>
                                                    <td><?php echo $reserva['camas_numeros'] ?? $reserva['numero_camas'] . ' camas' ?></td>
                                                    <td><?php echo formatear_fecha($reserva['fecha_inicio']) ?></td>
                                                    <td><?php echo formatear_fecha($reserva['fecha_fin']) ?></td>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($reserva['fecha_creacion'])) ?></td>
                                                    <td>
                                                        <form method="post" class="d-inline">
                                                            <input type="hidden" name="accion" value="aprobar_reserva">
                                                            <input type="hidden" name="id" value="<?php echo $reserva['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('¿Aprobar esta reserva?')">
                                                                <i class="bi bi-check-circle"></i> Aprobar
                                                            </button>
                                                        </form>
                                                        <form method="post" class="d-inline">
                                                            <input type="hidden" name="accion" value="rechazar_reserva">
                                                            <input type="hidden" name="id" value="<?php echo $reserva['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Rechazar esta reserva?')">
                                                                <i class="bi bi-x-circle"></i> Rechazar
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center py-4">No hay reservas pendientes</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Estado de habitaciones -->
                    <div class="card shadow-sm mt-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-building"></i> Estado de Habitaciones</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($habitaciones as $hab): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6>Habitación                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <?php echo $hab['numero'] ?></h6>
                                                <div class="progress mb-2">
                                                    <?php
                                                        $porcentaje = ($hab['camas_libres'] / $hab['total_camas']) * 100;
                                                        $color      = $porcentaje > 50 ? 'success' : ($porcentaje > 20 ? 'warning' : 'danger');
                                                    ?>
                                                    <div class="progress-bar bg-<?php echo $color ?>" style="width:<?php echo $porcentaje ?>%"></div>
                                                </div>
                                                <small><?php echo $hab['camas_libres'] ?> de<?php echo $hab['total_camas'] ?> camas libres</small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                <?php elseif ($accion === 'usuarios' || $accion === 'editar_usuario'): ?>
                    <!-- Gestión de Usuarios -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-people-fill"></i> Gestión de Usuarios</h2>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
                            <i class="bi bi-person-plus-fill"></i> Nuevo Usuario
                        </button>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nº Socio</th>
                                            <th>Nombre</th>
                                            <th>DNI</th>
                                            <th>Email</th>
                                            <th>Teléfono</th>
                                            <th>Rol</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($usuarios as $usuario): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($usuario['num_socio']) ?></td>
                                                <td><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido1']) ?></td>
                                                <td><?php echo htmlspecialchars($usuario['dni']) ?></td>
                                                <td><?php echo htmlspecialchars($usuario['email']) ?></td>
                                                <td><?php echo htmlspecialchars($usuario['telf']) ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $usuario['rol'] === 'admin' ? 'danger' : 'primary' ?>">
                                                        <?php echo strtoupper($usuario['rol']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="?accion=editar_usuario&id=<?php echo $usuario['id'] ?>" class="btn btn-sm btn-warning">
                                                        <i class="bi bi-pencil-fill"></i>
                                                    </a>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="accion" value="eliminar_usuario">
                                                        <input type="hidden" name="id" value="<?php echo $usuario['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este usuario?')">
                                                            <i class="bi bi-trash-fill"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Crear/Editar Usuario -->
                    <div class="modal fade" id="modalCrearUsuario" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <?php echo $usuario_editar ? 'Editar Usuario' : 'Nuevo Usuario' ?>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="post">
                                    <div class="modal-body">
                                        <input type="hidden" name="accion" value="<?php echo $usuario_editar ? 'actualizar_usuario' : 'crear_usuario' ?>">
                                        <?php if ($usuario_editar): ?>
                                            <input type="hidden" name="id" value="<?php echo $usuario_editar['id'] ?>">
                                        <?php endif; ?>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Nº Socio *</label>
                                                <input type="text" name="num_socio" class="form-control"
                                                       value="<?php echo $usuario_editar ? htmlspecialchars($usuario_editar['num_socio']) : '' ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">DNI *</label>
                                                <input type="text" name="dni" class="form-control"
                                                       value="<?php echo $usuario_editar ? htmlspecialchars($usuario_editar['dni']) : '' ?>" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Nombre *</label>
                                                <input type="text" name="nombre" class="form-control"
                                                       value="<?php echo $usuario_editar ? htmlspecialchars($usuario_editar['nombre']) : '' ?>" required>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Apellido 1 *</label>
                                                <input type="text" name="apellido1" class="form-control"
                                                       value="<?php echo $usuario_editar ? htmlspecialchars($usuario_editar['apellido1']) : '' ?>" required>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Apellido 2</label>
                                                <input type="text" name="apellido2" class="form-control"
                                                       value="<?php echo $usuario_editar ? htmlspecialchars($usuario_editar['apellido2']) : '' ?>">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Email *</label>
                                                <input type="email" name="email" class="form-control"
                                                       value="<?php echo $usuario_editar ? htmlspecialchars($usuario_editar['email']) : '' ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Teléfono</label>
                                                <input type="text" name="telf" class="form-control"
                                                       value="<?php echo $usuario_editar ? htmlspecialchars($usuario_editar['telf']) : '' ?>">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Contraseña                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              <?php echo $usuario_editar ? '' : '*' ?></label>
                                                <input type="password" name="password" class="form-control"
                                                       <?php echo $usuario_editar ? '' : 'required' ?>>
                                                <?php if ($usuario_editar): ?>
                                                    <small class="text-muted">Dejar en blanco para mantener la actual</small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Rol *</label>
                                                <select name="rol" class="form-select" required>
                                                    <option value="user"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         <?php echo($usuario_editar && $usuario_editar['rol'] === 'user') ? 'selected' : '' ?>>User</option>
                                                    <option value="admin"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  <?php echo($usuario_editar && $usuario_editar['rol'] === 'admin') ? 'selected' : '' ?>>Admin</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary">
                                            <?php echo $usuario_editar ? 'Actualizar' : 'Crear' ?>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <?php if ($usuario_editar): ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                new bootstrap.Modal(document.getElementById('modalCrearUsuario')).show();
                            });
                        </script>
                    <?php endif; ?>

                <?php elseif ($accion === 'reservas'): ?>
                    <!-- Gestión de Reservas -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-calendar-check"></i> Gestión de Reservas</h2>
                        <div>
                            <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#modalReservaSocio">
                                <i class="bi bi-person-plus"></i> Reserva para Socio
                            </button>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalReservaEspecial">
                                <i class="bi bi-calendar-event"></i> Reserva Especial
                            </button>
                        </div>
                    </div>

                    <!-- Reservas Pendientes -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-warning text-white">
                            <h5 class="mb-0">Reservas Pendientes</h5>
                        </div>
                        <div class="card-body">
                            <?php if (count($reservas_pendientes) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Usuario</th>
                                                <th>Habitación</th>
                                                <th>Camas</th>
                                                <th>Entrada</th>
                                                <th>Salida</th>
                                                <th>Días</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($reservas_pendientes as $reserva):
                                                    $dias            = (strtotime($reserva['fecha_fin']) - strtotime($reserva['fecha_inicio'])) / 86400;
                                                    $es_especial     = empty($reserva['nombre']);                        // Si no hay usuario, es reserva especial
                                                    $es_todo_refugio = $es_especial && empty($reserva['id_habitacion']); // NULL = TODO EL REFUGIO
                                                ?>
										                                                <tr>
										                                                    <td><?php echo $reserva['id'] ?></td>
										                                                    <td>
										                                                        <?php if ($es_especial): ?>
										                                                            <strong class="text-primary"><i class="bi bi-calendar-event"></i> RESERVA ESPECIAL</strong><br>
										                                                            <small class="text-muted"><?php echo htmlspecialchars($reserva['observaciones']) ?></small>
										                                                        <?php else: ?>
				                                                            <strong><?php echo htmlspecialchars($reserva['nombre'] . ' ' . $reserva['apellido1']) ?></strong><br>
				                                                            <small class="text-muted"><?php echo htmlspecialchars($reserva['num_socio']) ?></small>
				                                                        <?php endif; ?>
				                                                    </td>
				                                                    <td>
				                                                        <?php if ($es_todo_refugio): ?>
				                                                            <strong class="text-success"><i class="bi bi-building"></i> TODO</strong>
				                                                        <?php else: ?>
				                                                            Hab.<?php echo $reserva['habitacion_numero'] ?>
				                                                        <?php endif; ?>
				                                                    </td>
				                                                    <td>Cama				                                                            				                                                            				                                                            				                                                            				                                                            				                                                            				                                                            			                                                            		                                                             <?php echo $reserva['camas_numeros'] ?? $reserva['numero_camas'] . ' camas' ?></td>
				                                                    <td><?php echo formatear_fecha($reserva['fecha_inicio']) ?></td>
				                                                    <td><?php echo formatear_fecha($reserva['fecha_fin']) ?></td>
				                                                    <td><?php echo $dias ?> días</td>
				                                                    <td>
				                                                        <div class="btn-group" role="group">
				                                                            <form method="post" class="d-inline">
				                                                                <input type="hidden" name="accion" value="aprobar_reserva">
				                                                                <input type="hidden" name="id" value="<?php echo $reserva['id'] ?>">
				                                                                <button type="submit" class="btn btn-sm btn-success">
				                                                                    <i class="bi bi-check-lg"></i> Aprobar
				                                                                </button>
				                                                            </form>
				                                                            <form method="post" class="d-inline">
				                                                                <input type="hidden" name="accion" value="rechazar_reserva">
				                                                                <input type="hidden" name="id" value="<?php echo $reserva['id'] ?>">
				                                                                <button type="submit" class="btn btn-sm btn-danger">
				                                                                    <i class="bi bi-x-lg"></i> Rechazar
				                                                                </button>
				                                                            </form>
				                                                        </div>
				                                                    </td>
				                                                </tr>
				                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-center text-muted py-4">No hay reservas pendientes</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Reservas Aprobadas -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Reservas Aprobadas</h5>
                        </div>
                        <div class="card-body">
                            <?php if (count($reservas_aprobadas) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Usuario</th>
                                                <th>Habitación</th>
                                                <th>Camas</th>
                                                <th>Entrada</th>
                                                <th>Salida</th>
                                                <th>Días</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($reservas_aprobadas as $reserva):
                                                    $dias            = (strtotime($reserva['fecha_fin']) - strtotime($reserva['fecha_inicio'])) / 86400;
                                                    $es_especial     = empty($reserva['nombre']);                        // Si no hay usuario, es reserva especial
                                                    $es_todo_refugio = $es_especial && empty($reserva['id_habitacion']); // NULL = TODO EL REFUGIO
                                                ?>
										                                                <tr>
										                                                    <td><?php echo $reserva['id'] ?></td>
										                                                    <td>
										                                                        <?php if ($es_especial): ?>
										                                                            <strong class="text-primary"><i class="bi bi-calendar-event"></i> RESERVA ESPECIAL</strong><br>
										                                                            <small class="text-muted"><?php echo htmlspecialchars($reserva['observaciones']) ?></small>
										                                                        <?php else: ?>
				                                                            <strong><?php echo htmlspecialchars($reserva['nombre'] . ' ' . $reserva['apellido1']) ?></strong><br>
				                                                            <small class="text-muted"><?php echo htmlspecialchars($reserva['num_socio']) ?></small>
				                                                        <?php endif; ?>
				                                                    </td>
				                                                    <td>
				                                                        <?php if ($es_todo_refugio): ?>
				                                                            <strong class="text-success"><i class="bi bi-building"></i> TODO</strong>
				                                                        <?php else: ?>
				                                                            Hab.<?php echo $reserva['habitacion_numero'] ?>
				                                                        <?php endif; ?>
				                                                    </td>
				                                                    <td>Cama				                                                            				                                                            				                                                            				                                                            				                                                            				                                                            				                                                            			                                                            		                                                             <?php echo $reserva['camas_numeros'] ?? $reserva['numero_camas'] . ' camas' ?></td>
				                                                    <td><?php echo formatear_fecha($reserva['fecha_inicio']) ?></td>
				                                                    <td><?php echo formatear_fecha($reserva['fecha_fin']) ?></td>
				                                                    <td><?php echo $dias ?> días</td>
				                                                    <td>
				                                                        <button type="button" class="btn btn-sm btn-primary me-1" title="Editar reserva" onclick="editarReserva(<?php echo htmlspecialchars(json_encode($reserva)) ?>)">
				                                                            <i class="bi bi-pencil"></i>
				                                                        </button>
				                                                        <form method="post" class="d-inline" onsubmit="return confirm('¿Estás seguro de cancelar esta reserva?');">
				                                                            <input type="hidden" name="accion" value="cancelar_reserva_admin">
				                                                            <input type="hidden" name="id" value="<?php echo $reserva['id'] ?>">
				                                                            <button type="submit" class="btn btn-sm btn-danger" title="Cancelar reserva">
				                                                                <i class="bi bi-x-circle"></i>
				                                                            </button>
				                                                        </form>
				                                                    </td>
				                                                </tr>
				                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-center text-muted py-4">No hay reservas aprobadas</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Reservas Canceladas -->
                    <div class="card shadow-sm mt-4">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">Reservas Canceladas</h5>
                        </div>
                        <div class="card-body">
                            <?php if (count($reservas_canceladas) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Usuario</th>
                                                <th>Habitación</th>
                                                <th>Camas</th>
                                                <th>Entrada</th>
                                                <th>Salida</th>
                                                <th>Días</th>
                                                <th>Cancelada</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($reservas_canceladas as $reserva):
                                                    $dias            = (strtotime($reserva['fecha_fin']) - strtotime($reserva['fecha_inicio'])) / 86400;
                                                    $es_especial     = empty($reserva['nombre']);                        // Si no hay usuario, es reserva especial
                                                    $es_todo_refugio = $es_especial && empty($reserva['id_habitacion']); // NULL = TODO EL REFUGIO
                                                ?>
					                                                <tr class="table-secondary">
					                                                    <td><?php echo $reserva['id'] ?></td>
					                                                    <td>
					                                                        <?php if ($es_especial): ?>
					                                                            <strong class="text-primary"><i class="bi bi-calendar-event"></i> RESERVA ESPECIAL</strong><br>
					                                                            <small class="text-muted"><?php echo htmlspecialchars($reserva['observaciones']) ?></small>
					                                                        <?php else: ?>
                                                            <strong><?php echo htmlspecialchars($reserva['nombre'] . ' ' . $reserva['apellido1']) ?></strong><br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($reserva['num_socio']) ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($es_todo_refugio): ?>
                                                            <strong class="text-success"><i class="bi bi-building"></i> TODO</strong>
                                                        <?php else: ?>
                                                            Hab.<?php echo $reserva['habitacion_numero'] ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo $reserva['camas_numeros'] ?? $reserva['numero_camas'] . ' camas' ?></td>
                                                    <td><?php echo formatear_fecha($reserva['fecha_inicio']) ?></td>
                                                    <td><?php echo formatear_fecha($reserva['fecha_fin']) ?></td>
                                                    <td><?php echo $dias ?> días</td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <?php echo date('d/m/Y', strtotime($reserva['fecha_creacion'])) ?>
                                                        </small>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-center text-muted py-4">No hay reservas canceladas</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal para Crear Reserva para Socio -->
    <div class="modal fade" id="modalReservaSocio" tabindex="-1" aria-labelledby="modalReservaSocioLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalReservaSocioLabel">
                        <i class="bi bi-person-plus"></i> Crear Reserva para Socio
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" id="formReservaSocio">
                    <input type="hidden" name="accion" value="crear_reserva_socio">
                    <div class="modal-body">
                        <div class="alert alert-success">
                            <i class="bi bi-info-circle"></i> Las reservas creadas para socios se aprueban automáticamente.
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Seleccionar Socio *</label>
                            <select class="form-select" name="id_usuario" required id="selectSocio">
                                <option value="">Seleccione un socio</option>
                                <?php
                                $stmt = $conexionPDO->prepare("SELECT id, num_socio, nombre, apellido1, apellido2 FROM usuarios WHERE rol = 'user' ORDER BY num_socio");
                                $stmt->execute();
                                $socios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($socios as $socio): ?>
                                    <option value="<?php echo $socio['id'] ?>">
                                        <?php echo $socio['num_socio'] ?> - <?php echo $socio['nombre'] ?> <?php echo $socio['apellido1'] ?> <?php echo $socio['apellido2'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Inicio *</label>
                                <input type="date" class="form-control" name="fecha_inicio" required
                                       id="fechaInicioSocio" min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Fin *</label>
                                <input type="date" class="form-control" name="fecha_fin" required
                                       id="fechaFinSocio" min="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Habitación *</label>
                            <select class="form-select" name="id_habitacion" required id="selectHabitacionSocio">
                                <option value="">Seleccione una habitación</option>
                                <?php
                                $habitaciones = obtener_todas_habitaciones($conexionPDO);
                                foreach ($habitaciones as $hab): ?>
                                    <option value="<?php echo $hab['id'] ?>" data-max-camas="<?php echo $hab['capacidad'] ?>">
                                        Habitación <?php echo $hab['numero'] ?> (Capacidad: <?php echo $hab['capacidad'] ?> camas)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Número de Camas *</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-outline-secondary" onclick="cambiarCamasSocio(-1)">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" class="form-control text-center" name="numero_camas"
                                       id="numeroCamasSocio" value="1" min="1" required readonly>
                                <button type="button" class="btn btn-outline-secondary" onclick="cambiarCamasSocio(1)">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                            <small class="text-muted" id="infoCamasSocio">Selecciona una habitación primero</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Crear Reserva
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Crear Reserva Especial -->
    <div class="modal fade" id="modalReservaEspecial" tabindex="-1" aria-labelledby="modalReservaEspecialLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalReservaEspecialLabel">
                        <i class="bi bi-calendar-event"></i> Crear Reserva Especial
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" id="formReservaEspecial">
                    <input type="hidden" name="accion" value="crear_reserva_especial">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Las reservas especiales son para eventos y se aprueban automáticamente.
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Motivo/Evento *</label>
                            <input type="text" class="form-control" name="motivo" required
                                   placeholder="Ej: Evento especial, Mantenimiento, etc.">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Inicio *</label>
                                <input type="date" class="form-control" name="fecha_inicio" required
                                       id="fechaInicioEspecial" min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Fin *</label>
                                <input type="date" class="form-control" name="fecha_fin" required
                                       id="fechaFinEspecial" min="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Habitación *</label>
                            <select class="form-select" name="id_habitacion" required id="selectHabitacionEspecial">
                                <option value="">Seleccione una habitación</option>
                                <option value="0" data-max-camas="<?php
                                                                      $habitaciones        = obtener_todas_habitaciones($conexionPDO);
                                                                      $total_camas_refugio = array_sum(array_column($habitaciones, 'capacidad'));
                                                                  echo $total_camas_refugio;
                                                                  ?>">
                                    <strong>🏠 TODO EL REFUGIO</strong> (<?php echo $total_camas_refugio ?> camas totales)
                                </option>
                                <?php foreach ($habitaciones as $hab): ?>
                                    <option value="<?php echo $hab['id'] ?>" data-max-camas="<?php echo $hab['capacidad'] ?>">
                                        Habitación                                                                                                                                                                                                                                                                <?php echo $hab['numero'] ?> (Capacidad:<?php echo $hab['capacidad'] ?> camas)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Número de Camas *</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-outline-secondary" onclick="cambiarCamasEspecial(-1)">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" class="form-control text-center" name="numero_camas"
                                       id="numeroCamasEspecial" value="1" min="1" required readonly>
                                <button type="button" class="btn btn-outline-secondary" onclick="cambiarCamasEspecial(1)">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                            <small class="text-muted" id="infoCamasEspecial">Selecciona una habitación primero</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Crear Reserva
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Control de número de camas para reserva especial
        let maxCamasEspecial = 1;
        let esTodoElRefugio = false;

        document.getElementById('selectHabitacionEspecial').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const inputCamas = document.getElementById('numeroCamasEspecial');
            const infoCamas = document.getElementById('infoCamasEspecial');
            const controlCamas = inputCamas.parentElement;

            maxCamasEspecial = parseInt(selectedOption.dataset.maxCamas) || 1;
            esTodoElRefugio = (this.value === '0');

            if (esTodoElRefugio) {
                // Si es todo el refugio, ocultar control de camas
                controlCamas.style.display = 'none';
                inputCamas.value = maxCamasEspecial;
                inputCamas.removeAttribute('required');
                infoCamas.innerHTML = '<strong class="text-success"><i class="bi bi-building"></i> Se reservarán TODAS las camas disponibles del refugio (' + maxCamasEspecial + ' camas)</strong>';
            } else {
                // Si es habitación individual, mostrar control
                controlCamas.style.display = 'flex';
                inputCamas.setAttribute('required', 'required');
                inputCamas.max = maxCamasEspecial;
                inputCamas.value = 1;
                infoCamas.textContent = `Máximo ${maxCamasEspecial} camas disponibles en esta habitación`;
            }
        });

        function cambiarCamasEspecial(cambio) {
            const input = document.getElementById('numeroCamasEspecial');
            const habitacionSelect = document.getElementById('selectHabitacionEspecial');

            if (!habitacionSelect.value) {
                alert('Primero selecciona una habitación');
                return;
            }

            let nuevoValor = parseInt(input.value) + cambio;

            if (nuevoValor < 1) {
                nuevoValor = 1;
            } else if (nuevoValor > maxCamasEspecial) {
                nuevoValor = maxCamasEspecial;
            }

            input.value = nuevoValor;
        }

        // Validar que fecha fin sea posterior a fecha inicio
        document.getElementById('fechaFinEspecial').addEventListener('change', function() {
            const fechaInicio = document.getElementById('fechaInicioEspecial').value;
            const fechaFin = this.value;

            if (fechaInicio && fechaFin && fechaFin <= fechaInicio) {
                alert('La fecha de fin debe ser posterior a la fecha de inicio');
                this.value = '';
            }
        });
    </script>

    <!-- Modal para Editar Reserva -->
    <div class="modal fade" id="modalEditarReserva" tabindex="-1" aria-labelledby="modalEditarReservaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalEditarReservaLabel">
                        <i class="bi bi-pencil"></i> Editar Reserva
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" id="formEditarReserva">
                    <input type="hidden" name="accion" value="editar_reserva_admin">
                    <input type="hidden" name="id_reserva" id="editIdReserva">
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> Los cambios afectarán inmediatamente a la reserva aprobada.
                        </div>

                        <!-- Usuario/Motivo (solo lectura) -->
                        <div class="mb-3">
                            <label class="form-label">Usuario/Motivo</label>
                            <input type="text" class="form-control" id="editUsuario" readonly>
                        </div>

                        <!-- Fechas -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Inicio *</label>
                                <input type="date" class="form-control" name="fecha_inicio" required
                                       id="editFechaInicio" min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Fin *</label>
                                <input type="date" class="form-control" name="fecha_fin" required
                                       id="editFechaFin" min="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>

                        <!-- Habitación (solo para reservas con habitación específica) -->
                        <div class="mb-3" id="editHabitacionContainer">
                            <label class="form-label">Habitación</label>
                            <select class="form-select" name="id_habitacion" id="editHabitacion">
                                <option value="">Seleccione una habitación</option>
                                <?php
                                    $habitaciones = obtener_todas_habitaciones($conexionPDO);
                                foreach ($habitaciones as $hab): ?>
                                    <option value="<?php echo $hab['id'] ?>" data-max-camas="<?php echo $hab['capacidad'] ?>">
                                        Habitación                                                                                                       <?php echo $hab['numero'] ?> (Capacidad:<?php echo $hab['capacidad'] ?> camas)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Número de camas -->
                        <div class="mb-3" id="editCamasContainer">
                            <label class="form-label">Número de Camas *</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-outline-secondary" onclick="cambiarCamasEditar(-1)">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" class="form-control text-center" name="numero_camas"
                                       id="editNumeroCamas" value="1" min="1" required readonly>
                                <button type="button" class="btn btn-outline-secondary" onclick="cambiarCamasEditar(1)">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                            <small class="text-muted" id="editInfoCamas"></small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Variables para el modal de editar
        let maxCamasEditar = 1;
        let esTodoElRefugioEditar = false;

        function editarReserva(reserva) {
            // Abrir modal
            const modal = new bootstrap.Modal(document.getElementById('modalEditarReserva'));

            // Rellenar datos
            document.getElementById('editIdReserva').value = reserva.id;
            document.getElementById('editFechaInicio').value = reserva.fecha_inicio;
            document.getElementById('editFechaFin').value = reserva.fecha_fin;
            document.getElementById('editNumeroCamas').value = reserva.numero_camas;

            // Usuario/Motivo (detectar si es especial)
            const esEspecial = !reserva.nombre;
            const esTodoRefugio = esEspecial && !reserva.id_habitacion;
            esTodoElRefugioEditar = esTodoRefugio;

            if (esEspecial) {
                document.getElementById('editUsuario').value = '🎫 RESERVA ESPECIAL: ' + (reserva.observaciones || 'Sin motivo');
            } else {
                document.getElementById('editUsuario').value = reserva.nombre + ' ' + reserva.apellido1 + ' (' + reserva.num_socio + ')';
            }

            // Habitación
            const habitacionContainer = document.getElementById('editHabitacionContainer');
            const camasContainer = document.getElementById('editCamasContainer');
            const editHabitacion = document.getElementById('editHabitacion');

            if (esTodoRefugio) {
                // TODO EL REFUGIO: ocultar habitación y camas
                habitacionContainer.style.display = 'none';
                camasContainer.style.display = 'none';
                editHabitacion.removeAttribute('required');
                document.getElementById('editNumeroCamas').removeAttribute('required');
            } else {
                // Habitación específica
                habitacionContainer.style.display = 'block';
                camasContainer.style.display = 'block';
                editHabitacion.setAttribute('required', 'required');
                document.getElementById('editNumeroCamas').setAttribute('required', 'required');

                if (reserva.id_habitacion) {
                    editHabitacion.value = reserva.id_habitacion;
                    const selectedOption = editHabitacion.options[editHabitacion.selectedIndex];
                    maxCamasEditar = parseInt(selectedOption.dataset.maxCamas) || 1;
                    document.getElementById('editInfoCamas').textContent = `Máximo ${maxCamasEditar} camas disponibles`;
                }
            }

            modal.show();
        }

        function cambiarCamasEditar(cambio) {
            const input = document.getElementById('editNumeroCamas');
            let nuevoValor = parseInt(input.value) + cambio;

            if (nuevoValor < 1) {
                nuevoValor = 1;
            } else if (nuevoValor > maxCamasEditar) {
                nuevoValor = maxCamasEditar;
            }

            input.value = nuevoValor;
        }

        // Actualizar max camas cuando cambie habitación en edición
        document.getElementById('editHabitacion').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            maxCamasEditar = parseInt(selectedOption.dataset.maxCamas) || 1;
            document.getElementById('editNumeroCamas').max = maxCamasEditar;
            document.getElementById('editNumeroCamas').value = Math.min(document.getElementById('editNumeroCamas').value, maxCamasEditar);
            document.getElementById('editInfoCamas').textContent = `Máximo ${maxCamasEditar} camas disponibles`;
        });

        // Validar fechas en edición
        document.getElementById('editFechaFin').addEventListener('change', function() {
            const fechaInicio = document.getElementById('editFechaInicio').value;
            const fechaFin = this.value;

            if (fechaInicio && fechaFin && fechaFin <= fechaInicio) {
                alert('La fecha de fin debe ser posterior a la fecha de inicio');
                this.value = '';
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
