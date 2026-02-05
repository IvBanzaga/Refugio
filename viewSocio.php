<?php
    require 'conexion.php';
    require 'functions.php';

    // Mostrar errores para depuración
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Comprobar autenticación y rol
    if (! isset($_SESSION['userId']) || ($_SESSION['rol'] ?? '') !== 'user') {
    header('Location: login.php');
    exit;
    }
    session_regenerate_id(true);

    // Obtener información del usuario
    $usuario_info        = obtener_info_usuario($conexionPDO, $_SESSION['userId']);
    $foto_perfil_sidebar = $usuario_info['foto_perfil'] ?? null;

    // Inicializar variables de mensaje
    $mensaje      = '';
    $tipo_mensaje = 'success';

    // Recuperar mensajes de sesión (Post-Redirect-Get)
    if (isset($_SESSION['mensaje'])) {
    $mensaje      = $_SESSION['mensaje'];
    $tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'success';
    unset($_SESSION['mensaje']);
    unset($_SESSION['tipo_mensaje']);
    }

    $accion = $_POST['accion'] ?? $_GET['accion'] ?? 'calendario';

    // Procesar acciones POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($accion) {
        case 'crear_reserva':
            try {
                $numero_camas = isset($_POST['numero_camas']) ? (int) $_POST['numero_camas'] : 0;
                if ($numero_camas < 1) {
                    throw new Exception("Debes reservar al menos 1 cama");
                }

                $acompanantes = $_POST['acompanantes'] ?? [];
                if (! is_array($acompanantes)) {
                    $acompanantes = [];
                }

                $num_acompanantes        = count($acompanantes);
                $acompanantes_requeridos = $numero_camas - 1;

                if ($num_acompanantes != $acompanantes_requeridos) {
                    throw new Exception("Debes agregar exactamente $acompanantes_requeridos acompañante(s) para $numero_camas cama(s)");
                }

                $datos_reserva = [
                    'id_usuario'    => $_SESSION['userId'],
                    'id_habitacion' => 1,
                    'numero_camas'  => $numero_camas,
                    'fecha_inicio'  => $_POST['fecha_inicio'] ?? '',
                    'fecha_fin'     => $_POST['fecha_fin'] ?? '',
                    'actividad'     => $_POST['actividad'] ?? '',
                ];

                // crear_reserva() maneja su propia transacción
                $id_reserva = crear_reserva($conexionPDO, $datos_reserva);
                if (! $id_reserva) {
                    throw new Exception("Error al crear la reserva");
                }

                // Agregar acompañantes después de crear la reserva
                foreach ($acompanantes as $acomp) {
                    if (! empty($acomp['dni'])) {
                        $datos_acomp = [
                            'num_socio' => $acomp['num_socio'] ?? null,
                            'es_socio'  => isset($acomp['es_socio']) && $acomp['es_socio'] === 'si',
                            'dni'       => $acomp['dni'] ?? '',
                            'nombre'    => $acomp['nombre'] ?? '',
                            'apellido1' => $acomp['apellido1'] ?? '',
                            'apellido2' => $acomp['apellido2'] ?? null,
                            'actividad' => $_POST['actividad'] ?? null,
                        ];
                        agregar_acompanante($conexionPDO, $id_reserva, $datos_acomp);
                    }
                }

                $mensaje = "Reserva creada exitosamente. Pendiente de aprobación por el administrador.";

                // Enviar notificación por email al administrador
                try {
                    require_once __DIR__ . '/api/email_notificaciones.php';

                    // Obtener datos completos del socio
                    $datosSocio = obtener_info_usuario($conexionPDO, $_SESSION['userId']);

                    // Preparar datos de la reserva para el email
                    $datosReservaEmail = [
                        'id'           => $id_reserva,
                        'fecha_inicio' => $datos_reserva['fecha_inicio'],
                        'fecha_fin'    => $datos_reserva['fecha_fin'],
                        'numero_camas' => $numero_camas,
                        'actividad'    => $datos_reserva['actividad'],
                    ];

                    // Intentar enviar el email (no falla si el email no se envía)
                    notificar_admin_nueva_reserva($datosReservaEmail, $datosSocio);
                } catch (Exception $emailError) {
                    // Log del error pero no interrumpir el flujo
                    error_log("Error al enviar email de notificación: " . $emailError->getMessage());
                }

                // Guardar mensaje en sesión y redirigir (Post-Redirect-Get)
                $_SESSION['mensaje']      = $mensaje;
                $_SESSION['tipo_mensaje'] = 'success';
                header('Location: viewSocio.php?accion=mis_reservas');
                exit;

            } catch (Exception $e) {
                $mensaje                  = "Error al crear la reserva: " . $e->getMessage();
                $tipo_mensaje             = 'danger';
                $_SESSION['mensaje']      = $mensaje;
                $_SESSION['tipo_mensaje'] = $tipo_mensaje;
                header('Location: viewSocio.php?accion=mis_reservas');
                exit;
            }
            break;

        case 'cancelar_reserva':
            $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
            if ($id && cancelar_reserva($conexionPDO, $id)) {
                $_SESSION['mensaje']      = "Reserva cancelada exitosamente";
                $_SESSION['tipo_mensaje'] = 'success';
            } else {
                $_SESSION['mensaje']      = "Error al cancelar la reserva";
                $_SESSION['tipo_mensaje'] = 'danger';
            }
            header('Location: viewSocio.php?accion=mis_reservas');
            exit;
            break;

        case 'editar_reserva_usuario':
            try {
                $conexionPDO->beginTransaction();

                $id_reserva    = isset($_POST['id_reserva']) ? (int) $_POST['id_reserva'] : 0;
                $fecha_inicio  = $_POST['fecha_inicio'] ?? '';
                $fecha_fin     = $_POST['fecha_fin'] ?? '';
                $id_habitacion = 1;
                $numero_camas  = isset($_POST['numero_camas']) ? (int) $_POST['numero_camas'] : 0;

                $reserva_actual = obtener_reserva($conexionPDO, $id_reserva);
                if (! $reserva_actual || $reserva_actual['id_usuario'] != $_SESSION['userId']) {
                    throw new Exception("No tienes permiso para editar esta reserva");
                }

                if ($reserva_actual['estado'] !== 'pendiente') {
                    throw new Exception("Solo puedes editar reservas pendientes");
                }

                if ($fecha_inicio > $fecha_fin) {
                    throw new Exception("La fecha de fin debe ser igual o posterior a la fecha de inicio");
                }

                // Validar acompañantes si el número de camas cambió
                $acompanantes = $_POST['acompanantes'] ?? [];
                if (! is_array($acompanantes)) {
                    $acompanantes = [];
                }

                $num_acompanantes        = count($acompanantes);
                $acompanantes_requeridos = $numero_camas - 1;

                if ($num_acompanantes != $acompanantes_requeridos) {
                    throw new Exception("Debes agregar exactamente $acompanantes_requeridos acompañante(s) para $numero_camas cama(s)");
                }

                if (! editar_reserva_usuario($conexionPDO, $id_reserva, $fecha_inicio, $fecha_fin, $id_habitacion, $numero_camas)) {
                    throw new Exception("No hay suficientes camas disponibles");
                }

                // Eliminar acompañantes anteriores
                $stmt = $conexionPDO->prepare("DELETE FROM acompanantes WHERE id_reserva = :id_reserva");
                $stmt->bindParam(':id_reserva', $id_reserva, PDO::PARAM_INT);
                $stmt->execute();

                // Agregar nuevos acompañantes
                foreach ($acompanantes as $acomp) {
                    if (! empty($acomp['dni'])) {
                        $datos_acomp = [
                            'num_socio' => $acomp['num_socio'] ?? null,
                            'es_socio'  => isset($acomp['es_socio']) && $acomp['es_socio'] === 'si',
                            'dni'       => $acomp['dni'] ?? '',
                            'nombre'    => $acomp['nombre'] ?? '',
                            'apellido1' => $acomp['apellido1'] ?? '',
                            'apellido2' => $acomp['apellido2'] ?? null,
                        ];
                        agregar_acompanante($conexionPDO, $id_reserva, $datos_acomp);
                    }
                }

                $conexionPDO->commit();
                $_SESSION['mensaje']      = "Reserva actualizada exitosamente";
                $_SESSION['tipo_mensaje'] = 'success';
                header('Location: viewSocio.php?accion=mis_reservas');
                exit;
            } catch (Exception $e) {
                if ($conexionPDO->inTransaction()) {
                    $conexionPDO->rollBack();
                }
                $_SESSION['mensaje']      = "Error al editar reserva: " . $e->getMessage();
                $_SESSION['tipo_mensaje'] = 'danger';
                header('Location: viewSocio.php?accion=mis_reservas');
                exit;
            }
            break;

        case 'actualizar_perfil':
            $email = $_POST['email'] ?? '';
            $telf  = $_POST['telf'] ?? '';

            $resultado = actualizar_perfil_usuario($conexionPDO, $_SESSION['userId'], $email, $telf);
            if ($resultado['exito'] ?? false) {
                $_SESSION['mensaje']      = $resultado['mensaje'] ?? 'Perfil actualizado';
                $_SESSION['tipo_mensaje'] = 'success';
                $_SESSION['email']        = htmlspecialchars($email);
            } else {
                $_SESSION['mensaje']      = $resultado['mensaje'] ?? 'Error al actualizar perfil';
                $_SESSION['tipo_mensaje'] = 'danger';
            }
            header('Location: viewSocio.php?accion=perfil');
            exit;
            break;

        case 'cambiar_password':
            try {
                $password_actual    = $_POST['password_actual'] ?? '';
                $password_nueva     = $_POST['password_nueva'] ?? '';
                $password_confirmar = $_POST['password_confirmar'] ?? '';

                if (empty($password_actual) || empty($password_nueva) || empty($password_confirmar)) {
                    throw new Exception("Todos los campos son obligatorios");
                }

                if ($password_nueva !== $password_confirmar) {
                    throw new Exception("Las contraseñas nuevas no coinciden");
                }

                if (strlen($password_nueva) < 6) {
                    throw new Exception("La contraseña debe tener al menos 6 caracteres");
                }

                // Verificar contraseña actual
                $stmt = $conexionPDO->prepare("SELECT password FROM usuarios WHERE id = ?");
                $stmt->execute([$_SESSION['userId']]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                if (! $usuario || ! password_verify($password_actual, $usuario['password'])) {
                    throw new Exception("La contraseña actual es incorrecta");
                }

                // Actualizar contraseña
                $password_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
                $stmt          = $conexionPDO->prepare("UPDATE usuarios SET password = ? WHERE id = ?");

                if ($stmt->execute([$password_hash, $_SESSION['userId']])) {
                    $_SESSION['mensaje']      = "Contraseña actualizada exitosamente";
                    $_SESSION['tipo_mensaje'] = 'success';
                } else {
                    throw new Exception("Error al actualizar la contraseña");
                }
            } catch (Exception $e) {
                $_SESSION['mensaje']      = $e->getMessage();
                $_SESSION['tipo_mensaje'] = 'danger';
            }
            header('Location: viewSocio.php?accion=perfil');
            exit;
            break;
    }
    }

    // Obtener datos según acción
    $mis_reservas = $accion === 'mis_reservas' ? listar_reservas($conexionPDO, ['id_usuario' => $_SESSION['userId']]) ?? [] : [];
    $habitaciones = $accion === 'nueva_reserva' ? listar_habitaciones($conexionPDO) ?? [] : [];

    // Función para mes en español
    function mes_espanol($mes)
    {
    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4       => 'Abril', 5    => 'Mayo', 6       => 'Junio',
        7 => 'Julio', 8 => 'Agosto', 9  => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];
    return $meses[(int) $mes] ?? '';
    }

    // Mes y año actual o seleccionado
    $mes_actual  = isset($_GET['mes']) ? (int) $_GET['mes'] : (int) date('n');
    $anio_actual = isset($_GET['anio']) ? (int) $_GET['anio'] : (int) date('Y');

    // Mes anterior/siguiente
    $mes_anterior  = $mes_actual - 1;
    $anio_anterior = $anio_actual;
    if ($mes_anterior < 1) {$mes_anterior = 12;
    $anio_anterior--;}

    $mes_siguiente  = $mes_actual + 1;
    $anio_siguiente = $anio_actual;
    if ($mes_siguiente > 12) {$mes_siguiente = 1;
    $anio_siguiente++;}

    // Días del mes
    $primer_dia        = mktime(0, 0, 0, $mes_actual, 1, $anio_actual);
    $dias_en_mes       = date('t', $primer_dia);
    $dia_semana_inicio = date('N', $primer_dia); // 1=Lunes,7=Domingo

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🏔️</text></svg>">
    <title>Panel Usuario - Refugio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_green.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #15803d 0%, #22c55e 100%);
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 3px solid #fff;
        }

        .calendario {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }

        .dia-calendario {
            aspect-ratio: 1;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 8px;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            background: white;
        }

        .dia-calendario:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .dia-calendario.vacio {
            background: #f8f9fa;
            cursor: default;
        }

        .dia-calendario.vacio:hover {
            transform: none;
            box-shadow: none;
        }

        .dia-calendario.pasado {
            background: #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
        }

        .dia-calendario.pasado:hover {
            transform: none;
        }

        .dia-calendario.seleccionado {
            background: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }

        .dia-calendario .numero-dia {
            font-weight: bold;
            font-size: 1.1em;
        }

        .dia-calendario .camas-disponibles {
            font-size: 0.95em;
            margin-top: 5px;
            font-weight: 600;
        }

        .dia-calendario .camas-disponibles .texto-libres {
            color: #155724;
            font-weight: 700;
            font-size: 1.1em;
        }

        .dia-calendario.lleno {
            background: #dc3545;
            color: white;
            cursor: not-allowed;
        }

        .dia-calendario.pocas-camas {
            background: #ffc107;
        }

        .dia-calendario.mi-reserva-aprobada {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            border: 3px solid #0d6efd;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
        }

        .dia-calendario.mi-reserva-aprobada:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(13, 110, 253, 0.6);
        }

        .dia-calendario.mi-reserva-pendiente {
            background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);
            color: white;
            border: 3px dashed #0dcaf0;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(13, 202, 240, 0.4);
        }

        .dia-calendario.mi-reserva-pendiente:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(13, 202, 240, 0.6);
        }

        .dia-semana {
            text-align: center;
            font-weight: bold;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .acompanante-row {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        /* Estilos para Flatpickr - días completos en rojo (solo visual, no bloqueados) */
        .flatpickr-day.dia-completo {
            background-color: #dc3545 !important;
            color: white !important;
            border-color: #dc3545 !important;
            cursor: pointer !important;
            position: relative;
        }

        .flatpickr-day.dia-completo:hover {
            background-color: #c82333 !important;
            border-color: #bd2130 !important;
            transform: scale(1.05);
        }

        .flatpickr-day.dia-completo.today {
            background-color: #e74a3b !important;
            border: 2px solid #fff !important;
        }

        /* Agregar icono de advertencia a días completos */
        .flatpickr-day.dia-completo::after {
            content: '⚠';
            position: absolute;
            top: 2px;
            right: 2px;
            font-size: 10px;
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
                    <small>Panel Usuario</small>
                    <div class="mt-3 text-center">
                        <?php if ($foto_perfil_sidebar && file_exists(__DIR__ . '/' . $foto_perfil_sidebar)): ?>
                            <img src="<?php echo htmlspecialchars($foto_perfil_sidebar) ?>"
                                 alt="Foto de perfil"
                                 class="img-fluid rounded-circle mb-2"
                                 style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #fff;">
                        <?php else: ?>
                            <div class="bg-light text-dark rounded-circle d-inline-flex align-items-center justify-content-center mb-2"
                                 style="width: 80px; height: 80px; font-size: 40px;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                        <?php endif; ?>
                        <div class="mt-2">
                            <small class="fw-bold"><?php echo htmlspecialchars($_SESSION['user']) ?></small>
                        </div>
                    </div>
                </div>
                <nav class="nav flex-column mt-3">
                    <a class="nav-link                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           <?php echo $accion === 'calendario' ? 'active' : '' ?>" href="?accion=calendario">
                        <i class="bi bi-calendar3"></i> Calendario
                    </a>
                    <a class="nav-link                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           <?php echo $accion === 'nueva_reserva' ? 'active' : '' ?>" href="?accion=nueva_reserva">
                        <i class="bi bi-plus-circle-fill"></i> Nueva Reserva
                    </a>
                    <a class="nav-link                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           <?php echo $accion === 'mis_reservas' ? 'active' : '' ?>" href="?accion=mis_reservas">
                        <i class="bi bi-list-check"></i> Mis Reservas
                    </a>
                    <a class="nav-link                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           <?php echo $accion === 'perfil' ? 'active' : '' ?>" href="?accion=perfil">
                        <i class="bi bi-person-circle"></i> Mi Perfil
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

                <?php if ($accion === 'calendario'): ?>
                    <!-- Calendario de Disponibilidad -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-calendar3"></i> Calendario de Disponibilidad</h2>
                        <a href="?accion=nueva_reserva" class="btn btn-success">
                            <i class="bi bi-plus-lg"></i> Nueva Reserva
                        </a>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="?accion=calendario&mes=<?php echo $mes_anterior ?>&anio=<?php echo $anio_anterior ?>"
                                    class="btn btn-sm btn-light">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                                <h5 class="mb-0"><?php echo mes_espanol($mes_actual) ?> <?php echo $anio_actual ?></h5>
                                <a href="?accion=calendario&mes=<?php echo $mes_siguiente ?>&anio=<?php echo $anio_siguiente ?>"
                                    class="btn btn-sm btn-light">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Leyenda -->
                            <div class="mb-3 d-flex gap-3 flex-wrap">
                                <span><span class="badge" style="background:#22c55e">●</span> Muchas camas disponibles</span>
                                <span><span class="badge bg-warning">●</span> Pocas camas disponibles</span>
                                <span><span class="badge bg-danger">●</span> Sin camas disponibles</span>
                                <span><span class="badge bg-primary">●</span> Mi reserva aprobada</span>
                                <span><span class="badge bg-info">●</span> Mi reserva pendiente</span>
                                <span><span class="badge bg-secondary">●</span> Día pasado</span>
                            </div>

                            <!-- Calendario -->
                            <div class="calendario">
                                <div class="dia-semana">Lun</div>
                                <div class="dia-semana">Mar</div>
                                <div class="dia-semana">Mié</div>
                                <div class="dia-semana">Jue</div>
                                <div class="dia-semana">Vie</div>
                                <div class="dia-semana">Sáb</div>
                                <div class="dia-semana">Dom</div>

                                <?php
                                    // Espacios vacíos antes del primer día
                                for ($i = 1; $i < $dia_semana_inicio; $i++): ?>
                                    <div class="dia-calendario vacio"></div>
                                <?php endfor; ?>

                                <?php
                                    // Días del mes
                                    for ($dia = 1; $dia <= $dias_en_mes; $dia++):
                                        $fecha     = sprintf('%04d-%02d-%02d', $anio_actual, $mes_actual, $dia);
                                        $hoy       = date('Y-m-d');
                                        $es_pasado = $fecha < $hoy;

                                        // Verificar si el usuario tiene reserva en esta fecha
                                        $stmt_mis_reservas = $conexionPDO->prepare("
																																													                                            SELECT r.id, r.estado, h.numero as habitacion,
																																													                                                   GROUP_CONCAT(c.numero ORDER BY c.numero SEPARATOR ', ') as camas
																																													                                            FROM reservas r
																																													                                            JOIN habitaciones h ON r.id_habitacion = h.id
																																													                                            LEFT JOIN reservas_camas rc ON r.id = rc.id_reserva
																																													                                            LEFT JOIN camas c ON rc.id_cama = c.id
																																													                                            WHERE r.id_usuario = :id_usuario
																																													                                            AND :fecha BETWEEN r.fecha_inicio AND r.fecha_fin
																																													                                            AND r.estado IN ('pendiente', 'reservada')
																																													                                            GROUP BY r.id, r.estado, h.numero
																																													                                        ");
                                        $stmt_mis_reservas->bindParam(':id_usuario', $_SESSION['userId'], PDO::PARAM_INT);
                                        $stmt_mis_reservas->bindParam(':fecha', $fecha);
                                        $stmt_mis_reservas->execute();
                                        $mi_reserva = $stmt_mis_reservas->fetch(PDO::FETCH_ASSOC);

                                        // Contar total de reservas aprobadas en esta fecha
                                        $stmt_total_reservas = $conexionPDO->prepare("
																																													                                            SELECT COUNT(*) as total
																																													                                            FROM reservas
																																													                                            WHERE :fecha BETWEEN fecha_inicio AND fecha_fin
																																													                                            AND estado = 'reservada'
																																													                                        ");
                                        $stmt_total_reservas->bindParam(':fecha', $fecha);
                                        $stmt_total_reservas->execute();
                                        $total_reservas_aprobadas = $stmt_total_reservas->fetchColumn();

                                        // Obtener camas disponibles
                                        $camas_libres = contar_camas_libres_por_fecha($conexionPDO, $fecha);
                                        $total_camas  = 26;

                                        // Determinar clase CSS
                                        $clase      = 'dia-calendario';
                                        $info_extra = '';

                                        if ($es_pasado) {
                                            $clase .= ' pasado';
                                        } elseif ($mi_reserva) {
                                        // Usuario tiene reserva en esta fecha
                                        if ($mi_reserva['estado'] === 'reservada') {
                                            $clase      .= ' mi-reserva-aprobada';
                                            $info_extra  = "Hab. {$mi_reserva['habitacion']}, Camas {$mi_reserva['camas']}";
                                        } else {
                                            $clase      .= ' mi-reserva-pendiente';
                                            $info_extra  = "Pendiente - Hab. {$mi_reserva['habitacion']}, Camas {$mi_reserva['camas']}";
                                        }
                                    } elseif ($camas_libres === 0) {
                                        $clase .= ' lleno';
                                    } elseif ($camas_libres < 5) {
                                        $clase .= ' pocas-camas';
                                    }
                                ?>
                                    <div class="<?php echo $clase ?>" data-fecha="<?php echo $fecha ?>"
                                        <?php if ($mi_reserva): ?>
                                        title="<?php echo $info_extra ?>"
                                        <?php elseif (! $es_pasado && $camas_libres > 0): ?>
                                        onclick="irAReserva('<?php echo $fecha ?>')" style="cursor: pointer;"
                                        title="Click para reservar este día"
                                        <?php endif; ?>>
                                        <div class="numero-dia"><?php echo $dia ?></div>
                                        <?php if (! $es_pasado): ?>
                                            <?php if ($mi_reserva): ?>
                                                <div class="camas-disponibles">
                                                    <small><strong><?php echo $info_extra ?></strong></small>
                                                </div>
                                            <?php else: ?>
                                                <div class="camas-disponibles">
                                                    <span class="texto-libres"><?php echo $camas_libres ?>/<?php echo $total_camas ?></span>
                                                    <small class="d-block">libres</small>
                                                    <?php if ($total_reservas_aprobadas > 0): ?>
                                                        <small class="d-block text-muted"><?php echo $total_reservas_aprobadas ?> reserva<?php echo $total_reservas_aprobadas > 1 ? 's' : '' ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>

                <?php elseif ($accion === 'nueva_reserva'): ?>
                    <!-- Formulario Nueva Reserva -->
                    <h2 class="mb-4"><i class="bi bi-plus-circle-fill"></i> Nueva Reserva</h2>

                    <form method="post" id="formReserva">
                        <input type="hidden" name="accion" value="crear_reserva">
                        <input type="hidden" name="numero_camas" id="hiddenNumCamas" value="1">

                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Fechas y Habitación</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Fecha de Entrada *</label>
                                        <input type="text" name="fecha_inicio" id="fecha_inicio" class="form-control"
                                            required readonly placeholder="Selecciona fecha..."
                                            value="<?php echo isset($_GET['fecha_inicio']) ? htmlspecialchars($_GET['fecha_inicio']) : '' ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Fecha de Salida *</label>
                                        <input type="text" name="fecha_fin" id="fecha_fin" class="form-control"
                                            required readonly placeholder="Selecciona fecha..."
                                            value="<?php echo isset($_GET['fecha_fin']) ? htmlspecialchars($_GET['fecha_fin']) : '' ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Número de Camas *</label>
                                        <div class="input-group">
                                            <button type="button" class="btn btn-outline-secondary" id="btnDecrementar"
                                                onclick="cambiarNumeroCamas(-1)" disabled>
                                                <i class="bi bi-dash-lg"></i>
                                            </button>
                                            <input type="text" class="form-control text-center fw-bold"
                                                id="displayNumeroCamas" value="1" readonly>
                                            <button type="button" class="btn btn-outline-secondary" id="btnIncrementar"
                                                onclick="cambiarNumeroCamas(1)" disabled>
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted mt-2 d-block" id="infoAcompanantes">
                                            Selecciona las fechas para ver disponibilidad
                                        </small>
                                    </div>
                                </div>
                                <input type="hidden" name="id_habitacion" id="hiddenHabitacion" value="1">
                            </div>
                        </div>

                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Actividad a Realizar *</h5>
                            </div>
                            <div class="card-body">
                                <textarea name="actividad" class="form-control" rows="3" required
                                    placeholder="Describe la actividad que realizarás durante tu estancia..."></textarea>
                            </div>
                        </div>

                        <div class="card shadow-sm mb-4" id="cardAcompanantes" style="display: none;">
                            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Acompañantes <span id="acompanantesRequeridos" class="badge bg-light text-dark ms-2"></span></h5>
                                <button type="button" class="btn btn-sm btn-light" onclick="agregarAcompanante()" id="btnAgregarAcompanante">
                                    <i class="bi bi-person-plus-fill"></i> Agregar Acompañante
                                </button>
                            </div>
                            <div class="card-body" id="acompanantesContainer">
                                <p class="text-muted">Debes agregar los acompañantes requeridos según el número de camas seleccionado.</p>
                            </div>
                        </div>

                        <div class="card shadow-sm">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Comentarios</h5>
                            </div>
                            <div class="card-body">
                                <textarea name="comentarios" class="form-control" rows="3"
                                    placeholder="Añade cualquier comentario o solicitud especial..."></textarea>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle-fill"></i> Crear Reserva
                            </button>
                            <a href="?accion=calendario" class="btn btn-secondary btn-lg">Cancelar</a>
                        </div>
                    </form>

                <?php elseif ($accion === 'mis_reservas'): ?>
                    <!-- Mis Reservas -->
                    <h2 class="mb-4"><i class="bi bi-list-check"></i> Mis Reservas</h2>

                    <?php
                        $pendientes = array_filter($mis_reservas, fn($r) => $r['estado'] === 'pendiente');
                        $aprobadas  = array_filter($mis_reservas, fn($r) => $r['estado'] === 'reservada');
                        $canceladas = array_filter($mis_reservas, fn($r) => $r['estado'] === 'cancelada');
                    ?>

                    <!-- Reservas Pendientes -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-warning">
                            <h5 class="mb-0"><i class="bi bi-hourglass-split"></i> Pendientes de Aprobación</h5>
                        </div>
                        <div class="card-body">
                            <?php if (count($pendientes) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nº Camas</th>
                                                <th>Fecha Entrada</th>
                                                <th>Fecha Salida</th>
                                                <th>Solicitado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pendientes as $reserva): ?>
                                                <tr>
                                                    <td><?php echo $reserva['numero_camas'] ?> cama(s)</td>
                                                    <td><?php echo formatear_fecha($reserva['fecha_inicio']) ?></td>
                                                    <td><?php echo formatear_fecha($reserva['fecha_fin']) ?></td>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($reserva['fecha_creacion'])) ?></td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-primary me-1"
                                                            onclick="editarReservaUsuario(<?php echo htmlspecialchars(json_encode($reserva)) ?>)">
                                                            <i class="bi bi-pencil"></i> Editar
                                                        </button>
                                                        <form method="post" class="d-inline">
                                                            <input type="hidden" name="accion" value="cancelar_reserva">
                                                            <input type="hidden" name="id" value="<?php echo $reserva['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger"
                                                                onclick="return confirm('¿Cancelar esta reserva?')">
                                                                <i class="bi bi-x-circle"></i> Cancelar
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-center text-muted py-3">No tienes reservas pendientes</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Reservas Aprobadas -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-check-circle-fill"></i> Reservas Aprobadas</h5>
                        </div>
                        <div class="card-body">
                            <?php if (count($aprobadas) > 0): ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> Puedes editar o anular tus reservas aprobadas que aún no han comenzado. La anulación no se puede deshacer.
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nº Camas</th>
                                                <th>Fecha Entrada</th>
                                                <th>Fecha Salida</th>
                                                <th>Días</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($aprobadas as $reserva):
                                                    $dias         = (strtotime($reserva['fecha_fin']) - strtotime($reserva['fecha_inicio'])) / 86400;
                                                    $puede_editar = strtotime($reserva['fecha_inicio']) > strtotime(date('Y-m-d'));
                                            ?>
					                                                <tr>
					                                                    <td><?php echo $reserva['numero_camas'] ?> cama(s)</td>
					                                                    <td><?php echo formatear_fecha($reserva['fecha_inicio']) ?></td>
					                                                    <td><?php echo formatear_fecha($reserva['fecha_fin']) ?></td>
					                                                    <td><?php echo $dias ?> día<?php echo $dias > 1 ? 's' : '' ?></td>
					                                                    <td>
					                                                        <?php if ($puede_editar): ?>
					                                                            <button type="button" class="btn btn-sm btn-primary me-1"
					                                                                onclick="editarReservaUsuario(<?php echo htmlspecialchars(json_encode($reserva)) ?>)">
					                                                                <i class="bi bi-pencil"></i> Editar
					                                                            </button>
					                                                        <?php endif; ?>
                                                        <form method="post" class="d-inline" onsubmit="return confirmarAnulacion()">
                                                            <input type="hidden" name="accion" value="cancelar_reserva">
                                                            <input type="hidden" name="id" value="<?php echo $reserva['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger">
                                                                <i class="bi bi-x-circle"></i> Anular
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-center text-muted py-3">No tienes reservas aprobadas</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Reservas Canceladas -->
                    <?php if (count($canceladas) > 0): ?>
                        <div class="card shadow-sm">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0"><i class="bi bi-x-circle-fill"></i> Reservas Canceladas</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nº Camas</th>
                                                <th>Fecha Entrada</th>
                                                <th>Fecha Salida</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($canceladas as $reserva): ?>
                                                <tr>
                                                    <td><?php echo $reserva['numero_camas'] ?> cama(s)</td>
                                                    <td><?php echo formatear_fecha($reserva['fecha_inicio']) ?></td>
                                                    <td><?php echo formatear_fecha($reserva['fecha_fin']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- SECCIÓN: Mi Perfil -->
                <?php elseif ($accion === 'perfil'):
                        $usuario     = obtener_info_usuario($conexionPDO, $_SESSION['userId']);
                        $foto_perfil = $usuario['foto_perfil'] ?? null;
                ?>
																					                    <h2><i class="bi bi-person-circle"></i> Mi Perfil</h2>
																					                    <hr>

																					                    <div class="row">
																					                        <!-- Foto de Perfil -->
																					                        <div class="col-md-4">
																					                            <div class="card shadow-sm">
																					                                <div class="card-header bg-primary text-white">
																					                                    <h5 class="mb-0"><i class="bi bi-camera-fill"></i> Foto de Perfil</h5>
																					                                </div>
																					                                <div class="card-body text-center">
																					                                    <div id="fotoPerfilContainer" class="mb-3">
																					                                        <?php if ($foto_perfil && file_exists(__DIR__ . '/' . $foto_perfil)): ?>
																					                                            <img src="<?php echo htmlspecialchars($foto_perfil) ?>" alt="Foto de perfil" class="img-fluid rounded-circle" style="width: 200px; height: 200px; object-fit: cover; border: 4px solid #0d6efd;">
																					                                        <?php else: ?>
                                            <div class="bg-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 200px; height: 200px; font-size: 80px;">
                                                <i class="bi bi-person-fill"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <form id="formFotoPerfil" enctype="multipart/form-data">
                                        <input type="hidden" name="accion" value="subir">
                                        <div class="mb-3">
                                            <label for="inputFoto" class="btn btn-primary">
                                                <i class="bi bi-upload"></i> Seleccionar Foto
                                            </label>
                                            <input type="file" class="d-none" id="inputFoto" name="foto" accept="image/jpeg,image/jpg,image/png,image/gif">
                                        </div>
                                        <small class="text-muted d-block mb-2">Formatos: JPG, PNG, GIF (Máx. 5MB)</small>
                                        <?php if ($foto_perfil): ?>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="eliminarFoto()">
                                                <i class="bi bi-trash"></i> Eliminar Foto
                                            </button>
                                        <?php endif; ?>
                                    </form>

                                    <div id="mensajeFoto" class="mt-3"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Información del Usuario -->
                        <div class="col-md-8">
                            <div class="card shadow-sm">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="bi bi-info-circle-fill"></i> Información Personal</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" id="formActualizarPerfil">
                                        <input type="hidden" name="accion" value="actualizar_perfil">

                                        <!-- Información NO editable -->
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="fw-bold">Número de Socio:</label>
                                                <p class="mb-0 text-muted"><?php echo htmlspecialchars($usuario['num_socio']) ?></p>
                                                <small class="text-muted">No editable</small>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="fw-bold">DNI:</label>
                                                <p class="mb-0 text-muted"><?php echo htmlspecialchars($usuario['dni']) ?></p>
                                                <small class="text-muted">No editable</small>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <label class="fw-bold">Nombre Completo:</label>
                                                <p class="mb-0 text-muted">
                                                    <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido1'] . ' ' . ($usuario['apellido2'] ?? '')) ?>
                                                </p>
                                                <small class="text-muted">No editable</small>
                                            </div>
                                        </div>

                                        <hr>

                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="text-primary mb-0"><i class="bi bi-pencil-fill"></i> Datos de Contacto</h6>
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnEditarPerfil" onclick="habilitarEdicion()">
                                                <i class="bi bi-pencil"></i> Editar
                                            </button>
                                        </div>

                                        <!-- Información EDITABLE (inicialmente deshabilitada) -->
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Email: <span class="text-danger">*</span></label>
                                                <input type="email"
                                                    class="form-control campo-editable"
                                                    id="inputEmail"
                                                    name="email"
                                                    value="<?php echo htmlspecialchars($usuario['email']) ?>"
                                                    readonly
                                                    required>
                                                <small class="text-muted">Usado para iniciar sesión</small>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Teléfono:</label>
                                                <input type="tel"
                                                    class="form-control campo-editable"
                                                    id="inputTelefono"
                                                    name="telf"
                                                    value="<?php echo htmlspecialchars($usuario['telf'] ?? '') ?>"
                                                    placeholder="Ej: 600123456"
                                                    pattern="[0-9]{9,15}"
                                                    readonly>
                                                <small class="text-muted">9-15 dígitos</small>
                                            </div>
                                        </div>

                                        <div id="botonesEdicion" class="d-none">
                                            <div class="row gap-2">
                                                <div class="col-md-6">
                                                    <button type="submit" class="btn btn-success w-100">
                                                        <i class="bi bi-check-circle"></i> Guardar Cambios
                                                    </button>
                                                </div>
                                                <div class="col-md-6">
                                                    <button type="button" class="btn btn-secondary w-100" onclick="cancelarEdicion()">
                                                        <i class="bi bi-x-circle"></i> Cancelar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="alert alert-info mt-3 mb-0">
                                            <i class="bi bi-info-circle"></i>
                                            <strong>Nota:</strong> Haz clic en "Editar" para modificar tu email o teléfono. Para cambiar otros datos, contacta con el administrador.
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Cambiar Contraseña -->
                            <div class="card shadow-sm mt-4">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0"><i class="bi bi-shield-lock-fill"></i> Cambiar Contraseña</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" id="formCambiarPassword">
                                        <input type="hidden" name="accion" value="cambiar_password">

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Contraseña Actual: <span class="text-danger">*</span></label>
                                            <input type="password"
                                                class="form-control"
                                                name="password_actual"
                                                required
                                                placeholder="Ingresa tu contraseña actual">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Nueva Contraseña: <span class="text-danger">*</span></label>
                                            <input type="password"
                                                class="form-control"
                                                name="password_nueva"
                                                id="password_nueva"
                                                required
                                                minlength="6"
                                                placeholder="Mínimo 6 caracteres">
                                            <small class="text-muted">La contraseña debe tener al menos 6 caracteres</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Confirmar Nueva Contraseña: <span class="text-danger">*</span></label>
                                            <input type="password"
                                                class="form-control"
                                                name="password_confirmar"
                                                id="password_confirmar"
                                                required
                                                minlength="6"
                                                placeholder="Repite la nueva contraseña">
                                        </div>

                                        <button type="submit" class="btn btn-warning">
                                            <i class="bi bi-shield-check"></i> Cambiar Contraseña
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Reserva Usuario -->
    <div class="modal fade" id="modalEditarReservaUsuario" tabindex="-1" aria-labelledby="modalEditarReservaUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalEditarReservaUsuarioLabel">
                        <i class="bi bi-pencil"></i> Editar Mi Reserva
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" id="formEditarReservaUsuario">
                    <input type="hidden" name="accion" value="editar_reserva_usuario">
                    <input type="hidden" name="id_reserva" id="editIdReservaUsuario">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Solo puedes editar reservas pendientes de aprobación.
                        </div>

                        <!-- Fechas -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Inicio *</label>
                                <input type="text" class="form-control" name="fecha_inicio" required
                                       id="editFechaInicioUsuario" readonly placeholder="Selecciona fecha...">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Fin *</label>
                                <input type="text" class="form-control" name="fecha_fin" required
                                       id="editFechaFinUsuario" readonly placeholder="Selecciona fecha...">
                            </div>
                        </div>

                        <!-- Habitación (oculto, siempre habitación 1) -->
                        <input type="hidden" name="id_habitacion" id="editHabitacionUsuario" value="1">

                        <!-- Número de camas -->
                        <div class="mb-3">
                            <label class="form-label">Número de Camas *</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-outline-secondary" onclick="cambiarCamasEditUsuario(-1)">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" class="form-control text-center" name="numero_camas"
                                       id="editNumeroCamasUsuario" value="1" min="1" required readonly>
                                <button type="button" class="btn btn-outline-secondary" onclick="cambiarCamasEditUsuario(1)">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                            <small class="text-muted" id="editInfoCamasUsuario"></small>
                            <small class="text-muted mt-2 d-block" id="editInfoAcompanantesUsuario">
                                Selecciona las fechas y camas para gestionar acompañantes
                            </small>
                        </div>

                        <!-- Sección de Acompañantes -->
                        <div id="cardAcompanantesEditar" style="display: none;">
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">
                                    <i class="bi bi-people-fill"></i> Acompañantes
                                    <span id="acompanantesRequeridosEditar" class="badge bg-secondary ms-2"></span>
                                </h6>
                                <button type="button" class="btn btn-sm btn-success" onclick="agregarAcompananteEditar()" id="btnAgregarAcompananteEditar">
                                    <i class="bi bi-person-plus-fill"></i> Agregar Acompañante
                                </button>
                            </div>
                            <div id="acompanantesContainerEditar">
                                <!-- Los acompañantes se agregarán dinámicamente aquí -->
                            </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script>
        let contadorAcompanantes = 0;

        // Control de número de camas y acompañantes
        let acompanantesActuales = 0;
        let acompanantesRequeridos = 0;

        function agregarAcompanante() {
            const numeroCamas = parseInt(document.getElementById('hiddenNumCamas').value) || 0;
            acompanantesRequeridos = numeroCamas - 1;

            if (acompanantesActuales >= acompanantesRequeridos) {
                alert(`Solo puedes agregar ${acompanantesRequeridos} acompañante(s) para ${numeroCamas} cama(s).`);
                return;
            }

            contadorAcompanantes++;
            acompanantesActuales++;

            const container = document.getElementById('acompanantesContainer');
            const badge = document.getElementById('acompanantesRequeridos');
            const btnAgregar = document.getElementById('btnAgregarAcompanante');

            // Actualizar badge
            badge.textContent = `${acompanantesActuales}/${acompanantesRequeridos} agregados`;

            // Deshabilitar botón si se alcanzó el límite
            if (acompanantesActuales >= acompanantesRequeridos) {
                btnAgregar.disabled = true;
            }

            const html = `
                <div class="acompanante-row border-bottom pb-3 mb-3" id="acompanante-${contadorAcompanantes}">
                    <div class="d-flex justify-content-between mb-2">
                        <h6 class="text-success"><i class="bi bi-person"></i> Acompañante #${acompanantesActuales}</h6>
                        <button type="button" class="btn btn-sm btn-danger" onclick="eliminarAcompanante(${contadorAcompanantes})">
                            <i class="bi bi-trash"></i> Eliminar
                        </button>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <label class="form-label">¿Es socio?</label>
                            <select name="acompanantes[${contadorAcompanantes}][es_socio]" class="form-select"
                                    onchange="toggleNumSocio(${contadorAcompanantes})">
                                <option value="no">No</option>
                                <option value="si">Sí</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2" id="numSocioDiv-${contadorAcompanantes}" style="display:none">
                            <label class="form-label">Nº Socio</label>
                            <input type="text" name="acompanantes[${contadorAcompanantes}][num_socio]" class="form-control">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">DNI *</label>
                            <input type="text" name="acompanantes[${contadorAcompanantes}][dni]" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="acompanantes[${contadorAcompanantes}][nombre]" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Apellido 1 *</label>
                            <input type="text" name="acompanantes[${contadorAcompanantes}][apellido1]" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Apellido 2</label>
                            <input type="text" name="acompanantes[${contadorAcompanantes}][apellido2]" class="form-control">
                        </div>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', html);
        }

        function eliminarAcompanante(id) {
            document.getElementById(`acompanante-${id}`).remove();
            acompanantesActuales--;

            const badge = document.getElementById('acompanantesRequeridos');
            const btnAgregar = document.getElementById('btnAgregarAcompanante');
            const numeroCamas = parseInt(document.getElementById('hiddenNumCamas').value) || 0;
            acompanantesRequeridos = numeroCamas - 1;

            // Actualizar badge
            badge.textContent = `${acompanantesActuales}/${acompanantesRequeridos} agregados`;

            // Habilitar botón si no se ha alcanzado el límite
            if (acompanantesActuales < acompanantesRequeridos) {
                btnAgregar.disabled = false;
            }

            // Actualizar el contenedor si no hay acompañantes
            if (acompanantesActuales === 0) {
                document.getElementById('acompanantesContainer').innerHTML =
                    `<p class="text-info"><i class="bi bi-info-circle"></i> Debes agregar exactamente ${acompanantesRequeridos} acompañante(s).</p>`;
            }
        }

        function toggleNumSocio(id) {
            const select = document.querySelector(`select[name="acompanantes[${id}][es_socio]"]`);
            const div = document.getElementById(`numSocioDiv-${id}`);
            div.style.display = select.value === 'si' ? 'block' : 'none';
        }

        // Variables para control de camas y habitación
        let camasDisponiblesMax = 0;
        let numeroCamasActual = 1;
        let fechasCompletasGlobal = [];

        // Inicializar Flatpickr para fechas con días completos en rojo
        window.addEventListener('DOMContentLoaded', function() {
            // Cargar fechas completas
            fetch('fechas_completas.php')
                .then(response => response.json())
                .then(data => {
                    if (data.exito) {
                        fechasCompletasGlobal = data.fechas_completas;

                        // Inicializar Flatpickr para fecha de entrada
                        const fpInicio = flatpickr("#fecha_inicio", {
                            locale: "es",
                            dateFormat: "Y-m-d",
                            minDate: "today",
                            onDayCreate: function(dObj, dStr, fp, dayElem) {
                                const fecha = dayElem.dateObj.toISOString().split('T')[0];
                                if (fechasCompletasGlobal.includes(fecha)) {
                                    dayElem.classList.add('dia-completo');
                                    dayElem.setAttribute('title', '⚠️ Día completo - Sin camas disponibles');
                                }
                            },
                            onChange: function(selectedDates, dateStr, instance) {
                                actualizarDisponibilidad();

                                // Actualizar fecha mínima de salida (puede ser el mismo día)
                                if (selectedDates.length > 0) {
                                    fpFin.set('minDate', selectedDates[0]);
                                }
                            }
                        });

                        // Inicializar Flatpickr para fecha de salida
                        const fpFin = flatpickr("#fecha_fin", {
                            locale: "es",
                            dateFormat: "Y-m-d",
                            minDate: "today",
                            onDayCreate: function(dObj, dStr, fp, dayElem) {
                                const fecha = dayElem.dateObj.toISOString().split('T')[0];
                                if (fechasCompletasGlobal.includes(fecha)) {
                                    dayElem.classList.add('dia-completo');
                                    dayElem.setAttribute('title', '⚠️ Día completo - Sin camas disponibles');
                                }
                            },
                            onChange: function(selectedDates, dateStr, instance) {
                                actualizarDisponibilidad();
                            }
                        });

                        // Si hay fechas pre-cargadas desde URL, establecerlas
                        const fechaInicio = document.getElementById('fecha_inicio');
                        const fechaFin = document.getElementById('fecha_fin');

                        if (fechaInicio && fechaInicio.value) {
                            fpInicio.setDate(fechaInicio.value);
                        }
                        if (fechaFin && fechaFin.value) {
                            fpFin.setDate(fechaFin.value);
                        }

                        // Auto-cargar disponibilidad si las fechas están pre-cargadas
                        if (fechaInicio && fechaFin && fechaInicio.value && fechaFin.value) {
                            actualizarDisponibilidad();
                        }

                        // Inicializar Flatpickr para modal de edición
                        window.fpEditInicio = flatpickr("#editFechaInicioUsuario", {
                            locale: "es",
                            dateFormat: "Y-m-d",
                            minDate: "today",
                            onDayCreate: function(dObj, dStr, fp, dayElem) {
                                const fecha = dayElem.dateObj.toISOString().split('T')[0];
                                if (fechasCompletasGlobal.includes(fecha)) {
                                    dayElem.classList.add('dia-completo');
                                    dayElem.setAttribute('title', '⚠️ Día completo - Sin camas disponibles');
                                }
                            },
                            onChange: function(selectedDates, dateStr, instance) {
                                // Actualizar fecha mínima de salida (puede ser el mismo día)
                                if (selectedDates.length > 0) {
                                    window.fpEditFin.set('minDate', selectedDates[0]);

                                    // Verificar disponibilidad si ambas fechas están seleccionadas
                                    const fechaFin = document.getElementById('editFechaFinUsuario').value;
                                    const idReserva = document.getElementById('editIdReservaUsuario').value;
                                    if (fechaFin && idReserva) {
                                        verificarDisponibilidadEdicion(dateStr, fechaFin, idReserva);
                                    }
                                }
                            }
                        });

                        window.fpEditFin = flatpickr("#editFechaFinUsuario", {
                            locale: "es",
                            dateFormat: "Y-m-d",
                            minDate: "today",
                            onDayCreate: function(dObj, dStr, fp, dayElem) {
                                const fecha = dayElem.dateObj.toISOString().split('T')[0];
                                if (fechasCompletasGlobal.includes(fecha)) {
                                    dayElem.classList.add('dia-completo');
                                    dayElem.setAttribute('title', '⚠️ Día completo - Sin camas disponibles');
                                }
                            },
                            onChange: function(selectedDates, dateStr, instance) {
                                // Verificar disponibilidad cuando cambia la fecha fin
                                const fechaInicio = document.getElementById('editFechaInicioUsuario').value;
                                const idReserva = document.getElementById('editIdReservaUsuario').value;
                                if (fechaInicio && idReserva) {
                                    verificarDisponibilidadEdicion(fechaInicio, dateStr, idReserva);
                                }
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error al cargar fechas completas:', error);
                });
        });

        // Funciones para disponibilidad y validación
        function actualizarDisponibilidad() {
            const fechaInicioEl = document.getElementById('fecha_inicio');
            const fechaFinEl = document.getElementById('fecha_fin');

            if (!fechaInicioEl || !fechaFinEl) return;

            const fechaInicio = fechaInicioEl.value;
            const fechaFin = fechaFinEl.value;

            if (fechaInicio && fechaFin) {
                // Validar que fecha_fin sea igual o posterior a fecha_inicio
                if (fechaFin < fechaInicio) {
                    alert('La fecha de salida debe ser igual o posterior a la fecha de entrada');
                    return;
                }

                // Obtener total de camas disponibles
                fetch(`disponibilidad_total.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`)
                    .then(response => response.json())
                    .then(data => {
                        camasDisponiblesMax = data.disponibles || 0;
                        numeroCamasActual = 1;

                        // Resetear valores
                        document.getElementById('btnDecrementar').disabled = true;
                        document.getElementById('displayNumeroCamas').value = '1';
                        document.getElementById('hiddenNumCamas').value = '1';

                        // Habilitar/deshabilitar botón incrementar
                        document.getElementById('btnIncrementar').disabled = camasDisponiblesMax <= 1;

                        // Actualizar info
                        const infoEl = document.getElementById('infoAcompanantes');
                        if (camasDisponiblesMax > 0) {
                            const camasRestantes = camasDisponiblesMax - 1; // Restamos 1 porque ya está usando 1 cama por defecto
                            infoEl.innerHTML = `<i class="bi bi-check-circle-fill"></i> Disponibles: ${camasRestantes} cama${camasRestantes !== 1 ? 's' : ''} (estás usando 1)<br><i class="bi bi-info-circle"></i> Solo para ti (sin acompañantes)`;
                            infoEl.className = 'text-success mt-2 d-block';
                        } else {
                            infoEl.innerHTML = '<i class="bi bi-x-circle-fill"></i> No hay camas disponibles para estas fechas';
                            infoEl.className = 'text-danger mt-2 d-block';
                            document.getElementById('btnIncrementar').disabled = true;
                        }

                        // Resetear acompañantes
                        document.getElementById('cardAcompanantes').style.display = 'none';
                        document.getElementById('acompanantesContainer').innerHTML = '';
                        acompanantesActuales = 0;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('infoAcompanantes').innerHTML = 'Error al cargar disponibilidad';
                        document.getElementById('infoAcompanantes').className = 'text-danger mt-2 d-block';
                    });
            }
        }

        function cambiarNumeroCamas(incremento) {
            const nuevoValor = numeroCamasActual + incremento;

            if (nuevoValor >= 1 && nuevoValor <= camasDisponiblesMax) {
                numeroCamasActual = nuevoValor;

                // Actualizar displays
                document.getElementById('displayNumeroCamas').value = numeroCamasActual;
                document.getElementById('hiddenNumCamas').value = numeroCamasActual;

                // Actualizar botones
                document.getElementById('btnDecrementar').disabled = numeroCamasActual <= 1;
                document.getElementById('btnIncrementar').disabled = numeroCamasActual >= camasDisponiblesMax;

                // Actualizar info y acompañantes
                actualizarInfoAcompanantes();
                actualizarAcompanantes();
            }
        }

        function actualizarInfoAcompanantes() {
            const infoElement = document.getElementById('infoAcompanantes');
            const acompanantesRequeridos = numeroCamasActual - 1;
            const camasRestantes = camasDisponiblesMax - numeroCamasActual;

            // Mensaje de disponibilidad mostrando camas restantes
            let mensajeDisponibilidad = `<i class="bi bi-check-circle-fill"></i> Disponibles: ${camasRestantes} cama${camasRestantes !== 1 ? 's' : ''} (estás usando ${numeroCamasActual})`;

            if (numeroCamasActual === 1) {
                infoElement.innerHTML = mensajeDisponibilidad + '<br><i class="bi bi-info-circle"></i> Solo para ti (sin acompañantes)';
                infoElement.className = 'text-success mt-2 d-block';
            } else {
                infoElement.innerHTML = mensajeDisponibilidad + `<br><i class="bi bi-exclamation-circle"></i> Debes agregar <strong>${acompanantesRequeridos} acompañante(s)</strong>`;
                infoElement.className = 'text-success mt-2 d-block';
            }
        }

        function actualizarAcompanantes() {
            const cardAcompanantes = document.getElementById('cardAcompanantes');
            const container = document.getElementById('acompanantesContainer');
            const badge = document.getElementById('acompanantesRequeridos');
            const btnAgregar = document.getElementById('btnAgregarAcompanante');

            acompanantesRequeridos = numeroCamasActual - 1;

            if (numeroCamasActual === 1) {
                // Sin acompañantes
                cardAcompanantes.style.display = 'none';
                container.innerHTML = '';
                acompanantesActuales = 0;
            } else {
                // Mostrar sección de acompañantes
                cardAcompanantes.style.display = 'block';
                badge.textContent = `${acompanantesRequeridos} requerido(s)`;
                container.innerHTML = `<p class="text-info"><i class="bi bi-info-circle"></i> Debes agregar exactamente ${acompanantesRequeridos} acompañante(s).</p>`;
                acompanantesActuales = 0;

                // Controlar botón de agregar
                btnAgregar.disabled = false;
            }
        }

        // Funciones para gestión de foto de perfil
        const inputFoto = document.getElementById('inputFoto');
        if (inputFoto) {
            inputFoto.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const formData = new FormData(document.getElementById('formFotoPerfil'));
                    formData.append('foto', this.files[0]);

                    mostrarMensajeFoto('Subiendo foto...', 'info');

                    fetch('subir_foto.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.exito) {
                                mostrarMensajeFoto(data.mensaje, 'success');
                                setTimeout(() => location.reload(), 1000);
                            } else {
                                mostrarMensajeFoto(data.mensaje, 'danger');
                            }
                        })
                        .catch(error => {
                            mostrarMensajeFoto('Error al subir la foto', 'danger');
                        });
                }
            });
        }

        function eliminarFoto() {
            if (!confirm('¿Estás seguro de que deseas eliminar tu foto de perfil?')) {
                return;
            }

            const formData = new FormData();
            formData.append('accion', 'eliminar');

            mostrarMensajeFoto('Eliminando foto...', 'info');

            fetch('subir_foto.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exito) {
                        mostrarMensajeFoto(data.mensaje, 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        mostrarMensajeFoto(data.mensaje, 'danger');
                    }
                })
                .catch(error => {
                    mostrarMensajeFoto('Error al eliminar la foto', 'danger');
                });
        }

        function mostrarMensajeFoto(mensaje, tipo) {
            const mensajeDiv = document.getElementById('mensajeFoto');
            mensajeDiv.innerHTML = `<div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
        }

        // Funciones para edición de perfil
        let emailOriginal = '';
        let telefonoOriginal = '';

        // Validación de cambio de contraseña
        document.getElementById('formCambiarPassword')?.addEventListener('submit', function(e) {
            const passwordNueva = document.getElementById('password_nueva').value;
            const passwordConfirmar = document.getElementById('password_confirmar').value;

            if (passwordNueva !== passwordConfirmar) {
                e.preventDefault();
                alert('❌ Las contraseñas nuevas no coinciden. Por favor, verifica e intenta de nuevo.');
                return false;
            }

            if (passwordNueva.length < 6) {
                e.preventDefault();
                alert('❌ La contraseña debe tener al menos 6 caracteres.');
                return false;
            }

            return confirm('¿Estás seguro de que deseas cambiar tu contraseña?');
        });

        function habilitarEdicion() {
            const inputEmail = document.getElementById('inputEmail');
            const inputTelefono = document.getElementById('inputTelefono');
            const btnEditar = document.getElementById('btnEditarPerfil');
            const botonesEdicion = document.getElementById('botonesEdicion');

            // Guardar valores originales
            emailOriginal = inputEmail.value;
            telefonoOriginal = inputTelefono.value;

            // Habilitar campos
            inputEmail.removeAttribute('readonly');
            inputTelefono.removeAttribute('readonly');
            inputEmail.classList.add('border-primary');
            inputTelefono.classList.add('border-primary');
            inputEmail.focus();

            // Mostrar/ocultar botones usando clases de Bootstrap
            btnEditar.classList.add('d-none');
            botonesEdicion.classList.remove('d-none');
        }

        function cancelarEdicion() {
            const inputEmail = document.getElementById('inputEmail');
            const inputTelefono = document.getElementById('inputTelefono');
            const btnEditar = document.getElementById('btnEditarPerfil');
            const botonesEdicion = document.getElementById('botonesEdicion');

            // Restaurar valores originales
            inputEmail.value = emailOriginal;
            inputTelefono.value = telefonoOriginal;

            // Deshabilitar campos
            inputEmail.setAttribute('readonly', 'readonly');
            inputTelefono.setAttribute('readonly', 'readonly');
            inputEmail.classList.remove('border-primary');
            inputTelefono.classList.remove('border-primary');

            // Mostrar/ocultar botones usando clases de Bootstrap
            btnEditar.classList.remove('d-none');
            botonesEdicion.classList.add('d-none');
        }

        // Función para confirmar anulación de reserva
        function confirmarAnulacion() {
            return confirm('⚠️ ¿Estás seguro de que deseas anular esta reserva?\n\nEsta acción no se puede deshacer y la reserva será cancelada de forma permanente.');
        }

        // Función para editar reserva de usuario
        function editarReservaUsuario(reserva) {
            const modal = new bootstrap.Modal(document.getElementById('modalEditarReservaUsuario'));

            // Rellenar datos
            document.getElementById('editIdReservaUsuario').value = reserva.id;

            // Usar Flatpickr para establecer fechas
            if (window.fpEditInicio) {
                window.fpEditInicio.setDate(reserva.fecha_inicio);
            }
            if (window.fpEditFin) {
                window.fpEditFin.setDate(reserva.fecha_fin);
            }

            document.getElementById('editHabitacionUsuario').value = 1; // Habitación default
            document.getElementById('editNumeroCamasUsuario').value = reserva.numero_camas;

            // Limpiar acompañantes anteriores
            document.getElementById('acompanantesContainerEditar').innerHTML = '';
            acompanantesActualesEditar = 0;
            contadorAcompanantesEditar = 0;

            // Consultar disponibilidad real para las fechas de la reserva
            verificarDisponibilidadEdicion(reserva.fecha_inicio, reserva.fecha_fin, reserva.id);

            // Actualizar info de acompañantes
            actualizarInfoAcompanantesEditar();

            modal.show();
        }

        // Función para verificar disponibilidad en el modal de edición
        function verificarDisponibilidadEdicion(fechaInicio, fechaFin, idReserva) {
            if (fechaInicio && fechaFin) {
                // Consultar disponibilidad excluyendo la reserva actual
                fetch(`disponibilidad_total.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&excluir_reserva=${idReserva}`)
                    .then(response => response.json())
                    .then(data => {
                        maxCamasEditUsuario = data.disponibles || 0;

                        // Actualizar el texto de información
                        const camasActuales = parseInt(document.getElementById('editNumeroCamasUsuario').value) || 1;
                        const camasRestantes = maxCamasEditUsuario - camasActuales + 1;

                        if (maxCamasEditUsuario > 0) {
                            document.getElementById('editInfoCamasUsuario').textContent =
                                `Disponibles: ${camasRestantes} camas (estás usando ${camasActuales})`;
                            document.getElementById('editInfoCamasUsuario').className = 'text-success';
                        } else {
                            document.getElementById('editInfoCamasUsuario').textContent =
                                'No hay camas disponibles para estas fechas';
                            document.getElementById('editInfoCamasUsuario').className = 'text-danger';
                        }
                    })
                    .catch(error => {
                        console.error('Error al verificar disponibilidad:', error);
                        document.getElementById('editInfoCamasUsuario').textContent =
                            'Error al cargar disponibilidad';
                        document.getElementById('editInfoCamasUsuario').className = 'text-danger';
                    });
            }
        }

        // Variables para edición de usuario
        let maxCamasEditUsuario = 26; // Capacidad fija de habitación 1
        let contadorAcompanantesEditar = 0;
        let acompanantesActualesEditar = 0;

        function cambiarCamasEditUsuario(cambio) {
            const input = document.getElementById('editNumeroCamasUsuario');
            let nuevoValor = parseInt(input.value) + cambio;

            if (nuevoValor < 1) {
                nuevoValor = 1;
            } else if (nuevoValor > maxCamasEditUsuario) {
                nuevoValor = maxCamasEditUsuario;
            }

            input.value = nuevoValor;

            // Actualizar disponibilidad mostrada
            const camasRestantes = maxCamasEditUsuario - nuevoValor + 1;
            const infoElement = document.getElementById('editInfoCamasUsuario');

            if (camasRestantes >= 0) {
                infoElement.textContent = `Disponibles: ${camasRestantes} camas (estás usando ${nuevoValor})`;
                infoElement.className = 'text-success';
            } else {
                infoElement.textContent = `No hay suficientes camas disponibles`;
                infoElement.className = 'text-danger';
            }

            actualizarInfoAcompanantesEditar();
        }

        function actualizarInfoAcompanantesEditar() {
            const numeroCamas = parseInt(document.getElementById('editNumeroCamasUsuario').value) || 0;
            const acompanantesRequeridos = numeroCamas - 1;
            const cardAcompanantes = document.getElementById('cardAcompanantesEditar');
            const infoAcompanantes = document.getElementById('editInfoAcompanantesUsuario');

            if (acompanantesRequeridos > 0) {
                cardAcompanantes.style.display = 'block';
                const badge = document.getElementById('acompanantesRequeridosEditar');
                badge.textContent = `${acompanantesActualesEditar}/${acompanantesRequeridos} agregados`;

                // Actualizar info
                const diferencia = acompanantesRequeridos - acompanantesActualesEditar;
                if (diferencia > 0) {
                    infoAcompanantes.innerHTML = `Debes agregar ${diferencia} acompañante(s)`;
                    infoAcompanantes.className = 'text-danger mt-2 d-block';
                } else {
                    infoAcompanantes.innerHTML = `Todos los acompañantes agregados`;
                    infoAcompanantes.className = 'text-success mt-2 d-block';
                }

                // Controlar botón agregar
                const btnAgregar = document.getElementById('btnAgregarAcompananteEditar');
                btnAgregar.disabled = acompanantesActualesEditar >= acompanantesRequeridos;
            } else {
                cardAcompanantes.style.display = 'none';
                // Limpiar acompañantes si solo hay 1 cama
                document.getElementById('acompanantesContainerEditar').innerHTML = '';
                acompanantesActualesEditar = 0;
                contadorAcompanantesEditar = 0;
                infoAcompanantes.innerHTML = 'Una cama no requiere acompañantes';
                infoAcompanantes.className = 'text-muted mt-2 d-block';
            }
        }

        function agregarAcompananteEditar() {
            const numeroCamas = parseInt(document.getElementById('editNumeroCamasUsuario').value) || 0;
            const acompanantesRequeridos = numeroCamas - 1;

            if (acompanantesActualesEditar >= acompanantesRequeridos) {
                alert(`Solo puedes agregar ${acompanantesRequeridos} acompañante(s) para ${numeroCamas} cama(s).`);
                return;
            }

            contadorAcompanantesEditar++;
            acompanantesActualesEditar++;

            const container = document.getElementById('acompanantesContainerEditar');

            const html = `
                <div class="acompanante-row border-bottom pb-3 mb-3" id="acompanante-editar-${contadorAcompanantesEditar}">
                    <div class="d-flex justify-content-between mb-2">
                        <h6 class="text-success"><i class="bi bi-person"></i> Acompañante #${acompanantesActualesEditar}</h6>
                        <button type="button" class="btn btn-sm btn-danger" onclick="eliminarAcompananteEditar(${contadorAcompanantesEditar})">
                            <i class="bi bi-trash"></i> Eliminar
                        </button>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <label class="form-label">¿Es socio?</label>
                            <select name="acompanantes[${contadorAcompanantesEditar}][es_socio]" class="form-select"
                                    onchange="toggleNumSocioEditar(${contadorAcompanantesEditar})">
                                <option value="no">No</option>
                                <option value="si">Sí</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2" id="numSocioDivEditar-${contadorAcompanantesEditar}" style="display:none">
                            <label class="form-label">Nº Socio</label>
                            <input type="text" name="acompanantes[${contadorAcompanantesEditar}][num_socio]" class="form-control">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">DNI *</label>
                            <input type="text" name="acompanantes[${contadorAcompanantesEditar}][dni]" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="acompanantes[${contadorAcompanantesEditar}][nombre]" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Apellido 1 *</label>
                            <input type="text" name="acompanantes[${contadorAcompanantesEditar}][apellido1]" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Apellido 2</label>
                            <input type="text" name="acompanantes[${contadorAcompanantesEditar}][apellido2]" class="form-control">
                        </div>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', html);
            actualizarInfoAcompanantesEditar();
        }

        function eliminarAcompananteEditar(id) {
            document.getElementById(`acompanante-editar-${id}`).remove();
            acompanantesActualesEditar--;
            actualizarInfoAcompanantesEditar();
        }

        function toggleNumSocioEditar(id) {
            const select = document.querySelector(`select[name="acompanantes[${id}][es_socio]"]`);
            const div = document.getElementById(`numSocioDivEditar-${id}`);
            div.style.display = select.value === 'si' ? 'block' : 'none';
        }

        // Actualizar max camas cuando cambie habitación - COMENTADO (habitación 1 siempre)
        /*
        if (document.getElementById('editHabitacionUsuario')) {
            document.getElementById('editHabitacionUsuario').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                maxCamasEditUsuario = parseInt(selectedOption.dataset.maxCamas) || 1;
                document.getElementById('editNumeroCamasUsuario').max = maxCamasEditUsuario;
                document.getElementById('editNumeroCamasUsuario').value = Math.min(document.getElementById('editNumeroCamasUsuario').value, maxCamasEditUsuario);
                document.getElementById('editInfoCamasUsuario').textContent = `Máximo ${maxCamasEditUsuario} camas disponibles`;
            });
        }
        */

        // Validación del formulario de edición de reserva
        const formEditarReservaUsuario = document.getElementById('formEditarReservaUsuario');
        if (formEditarReservaUsuario) {
            formEditarReservaUsuario.addEventListener('submit', function(e) {
                const numeroCamas = parseInt(document.getElementById('editNumeroCamasUsuario').value) || 0;
                const acompanantesRequeridos = numeroCamas - 1;

                if (acompanantesRequeridos > 0 && acompanantesActualesEditar !== acompanantesRequeridos) {
                    e.preventDefault();
                    alert(`Debes agregar exactamente ${acompanantesRequeridos} acompañante(s) para ${numeroCamas} cama(s)`);
                    return false;
                }
            });
        }

        // Listener para cuando se cierra el modal de edición
        const modalEditarReservaUsuario = document.getElementById('modalEditarReservaUsuario');
        if (modalEditarReservaUsuario) {
            modalEditarReservaUsuario.addEventListener('hidden.bs.modal', function () {
                // Limpiar acompañantes al cerrar el modal
                document.getElementById('acompanantesContainerEditar').innerHTML = '';
                acompanantesActualesEditar = 0;
                contadorAcompanantesEditar = 0;
            });
        }

        // Función para ir a reserva con fecha preseleccionada
        function irAReserva(fecha) {
            // Calcular fecha de salida (siguiente día)
            const fechaObj = new Date(fecha + 'T00:00:00');
            fechaObj.setDate(fechaObj.getDate() + 1);
            const fechaSalida = fechaObj.toISOString().split('T')[0];

            // Redirigir a nueva reserva con fechas
            window.location.href = `viewSocio.php?accion=nueva_reserva&fecha_inicio=${fecha}&fecha_fin=${fechaSalida}`;
        }
    </script>
</body>

</html>
