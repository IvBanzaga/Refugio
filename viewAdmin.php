<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require 'conexion.php';
    require 'functions.php';

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

        // Extraer campos específicos
        $campos = explode('|', $datos_personales);
        $nombre = isset($campos[1]) ? $campos[1] : 'No Socio';

        // Extraer email, teléfono, DNI
        $email    = '';
        $telefono = '';
        $dni      = '';
        $grupo    = '';

        foreach ($campos as $campo) {
            if (strpos($campo, 'Email:') === 0) {
                $email = str_replace('Email:', '', $campo);
            } elseif (strpos($campo, 'Tel:') === 0) {
                $telefono = str_replace('Tel:', '', $campo);
            } elseif (strpos($campo, 'DNI:') === 0) {
                $dni = str_replace('DNI:', '', $campo);
            } elseif (strpos($campo, 'Grupo:') === 0) {
                $grupo = str_replace('Grupo:', '', $campo);
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
            'email'           => $email,
            'telefono'        => $telefono,
            'dni'             => $dni,
            'actividad'       => $actividad,
            'grupo'           => $grupo,
            'montanero'       => $montanero,
            'datos_completos' => $observaciones,
        ];
    }

    // Formato ANTIGUO: NO SOCIO: nombre | DNI: xxx | Tel: xxx | Email: xxx | Grupo: xxx | Actividad: xxx
    // Formato ESPECIAL: NO SOCIO: Nombre|DNI:xxx|Telf:xxx|Email:xxx|Actividad:xxx
    if (strpos($observaciones, 'NO SOCIO:') === 0) {
        // Nuevo formato con pipes sin espacios (para reservas especiales)
        if (strpos($observaciones, '|') !== false && strpos($observaciones, ' | ') === false) {
            $partes          = explode('|', $observaciones);
            $nombre_completo = str_replace('NO SOCIO: ', '', $partes[0]);

            $email     = '';
            $telefono  = '';
            $dni       = '';
            $actividad = '';

            foreach ($partes as $parte) {
                if (strpos($parte, 'Email:') === 0) {
                    $email = trim(str_replace('Email:', '', $parte));
                } elseif (strpos($parte, 'Telf:') === 0 || strpos($parte, 'Tel:') === 0) {
                    $telefono = trim(str_replace(['Telf:', 'Tel:'], '', $parte));
                } elseif (strpos($parte, 'DNI:') === 0) {
                    $dni = trim(str_replace('DNI:', '', $parte));
                } elseif (strpos($parte, 'Actividad:') === 0) {
                    $actividad = trim(str_replace('Actividad:', '', $parte));
                }
            }

            return [
                'es_no_socio'     => true,
                'nombre'          => $nombre_completo,
                'email'           => $email,
                'telefono'        => $telefono,
                'dni'             => $dni,
                'actividad'       => $actividad,
                'grupo'           => '',
                'montanero'       => 'Otro',
                'datos_completos' => $observaciones,
            ];
        }

        // Formato antiguo con espacios y barras verticales
        $partes = explode(' | ', $observaciones);

        // Primera parte es "NO SOCIO: nombre"
        $nombre_completo = str_replace('NO SOCIO: ', '', $partes[0]);

        // Buscar la actividad (última parte que empieza con "Actividad:")
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
        // Construir información de contacto
        $info_parts = [];
        if (! empty($datos_no_socio['email'])) {
            $info_parts[] = htmlspecialchars($datos_no_socio['email']);
        }
        if (! empty($datos_no_socio['telefono'])) {
            $info_parts[] = '<i class="bi bi-telephone"></i> ' . htmlspecialchars($datos_no_socio['telefono']);
        }

        $info_line = ! empty($info_parts) ? '<br><small class="text-muted">' . implode(' | ', $info_parts) . '</small>' : '';

        return [
            'display'   => '🎫 No Socio: ' . htmlspecialchars($datos_no_socio['nombre']) . $info_line,
            'email'     => htmlspecialchars($datos_no_socio['email'] ?? ''),
            'actividad' => htmlspecialchars($datos_no_socio['actividad']),
            'montanero' => htmlspecialchars($datos_no_socio['montanero'] ?? 'Otro'),
        ];
    }

    // Es una reserva especial (TODO EL REFUGIO, etc)
    // Verificar si tiene información de grupo
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

    /* TODO: Comprobación de autenticación y rol. Se usa session_regenerate_id(true) para evitar robo de sesión (fijación de sesión). Depuración: puedes poner breakpoint aquí para comprobar el estado de $_SESSION. */
    if (! isset($_SESSION['userId']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit;
    }
    session_regenerate_id(true); // Justificación: previene ataques de fijación de sesión

    // Recuperar mensajes de la sesión (patrón PRG - Post-Redirect-Get)
    $mensaje      = $_SESSION['mensaje'] ?? '';
    $tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'success';
    unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);

    $accion = isset($_POST['accion']) ? $_POST['accion'] : (isset($_GET['accion']) ? $_GET['accion'] : 'dashboard');

    /* TODO: Procesar acciones del panel admin. Todas las acciones usan POST para mayor seguridad.
       Depuración: breakpoint útil para ver los datos recibidos por POST. */

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
    // La función export_usuarios_csv hace exit, nunca llegará aquí
    }

    // Procesar exportación de usuarios a PDF (GET)
    if ($accion === 'export_usuarios_pdf' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $search = $_GET['search'] ?? '';
    $sort   = $_GET['sort'] ?? 'num_socio';
    $dir    = $_GET['dir'] ?? 'ASC';

    export_usuarios_pdf($conexionPDO, [
        'search'    => $search,
        'order_by'  => $sort,
        'order_dir' => $dir,
    ]);
    // La función export_usuarios_pdf hace exit, nunca llegará aquí
    }

    // AJAX: Verificar disponibilidad de camas
    if (isset($_GET['ajax']) && $_GET['ajax'] === 'verificar_disponibilidad') {
    header('Content-Type: application/json');

    $id_habitacion = 1; // Habitación default
    $fecha_inicio  = $_GET['fecha_inicio'] ?? '';
    $fecha_fin     = $_GET['fecha_fin'] ?? '';

    if (empty($fecha_inicio) || empty($fecha_fin)) {
        echo json_encode(['error' => 'Fechas requeridas']);
        exit;
    }

    $camas_disponibles = obtener_camas_disponibles($conexionPDO, $id_habitacion, $fecha_inicio, $fecha_fin);
    $total_disponibles = count($camas_disponibles);

    echo json_encode([
        'success'         => true,
        'disponibles'     => $total_disponibles,
        'capacidad_total' => 26,
    ]);
    exit;
    }

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
                $_SESSION['mensaje']      = "Usuario creado exitosamente";
                $_SESSION['tipo_mensaje'] = 'success';
            } else {
                $_SESSION['mensaje']      = "Error al crear el usuario";
                $_SESSION['tipo_mensaje'] = 'danger';
            }
            header('Location: viewAdmin.php?accion=usuarios');
            exit;
            break;

        case 'actualizar_usuario':
            $id = (int) $_POST['id'];

            // Proteger al usuario admin principal
            $usuario_actual = obtener_usuario($conexionPDO, $id);
            if ($usuario_actual && $usuario_actual['email'] === 'admin@hostel.com') {
                $_SESSION['mensaje']      = "No se puede modificar el usuario administrador principal";
                $_SESSION['tipo_mensaje'] = 'danger';
                header('Location: viewAdmin.php?accion=usuarios');
                exit;
            }

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
                $_SESSION['mensaje']      = "Usuario actualizado exitosamente";
                $_SESSION['tipo_mensaje'] = 'success';
            } else {
                $_SESSION['mensaje']      = "Error al actualizar el usuario";
                $_SESSION['tipo_mensaje'] = 'danger';
            }
            header('Location: viewAdmin.php?accion=usuarios');
            exit;
            break;

        case 'eliminar_usuario':
            $id = (int) $_POST['id'];

            // Proteger al usuario admin principal
            $usuario_actual = obtener_usuario($conexionPDO, $id);
            if ($usuario_actual && $usuario_actual['email'] === 'admin@hostel.com') {
                $_SESSION['mensaje']      = "No se puede eliminar el usuario administrador principal";
                $_SESSION['tipo_mensaje'] = 'danger';
                header('Location: viewAdmin.php?accion=usuarios');
                exit;
            }

            if (eliminar_usuario($conexionPDO, $id)) {
                $_SESSION['mensaje']      = "Usuario eliminado exitosamente";
                $_SESSION['tipo_mensaje'] = 'success';
            } else {
                $_SESSION['mensaje']      = "Error al eliminar el usuario";
                $_SESSION['tipo_mensaje'] = 'danger';
            }
            header('Location: viewAdmin.php?accion=usuarios');
            exit;
            break;

        case 'aprobar_reserva':
            $id = (int) $_POST['id'];
            if (actualizar_estado_reserva($conexionPDO, $id, 'reservada')) {
                $mensaje = "Reserva aprobada exitosamente";

                // Enviar notificación por email al socio
                try {
                    require_once __DIR__ . '/api/email_notificaciones.php';

                    // Obtener datos completos de la reserva y el socio
                    $reserva = obtener_reserva($conexionPDO, $id);

                    if ($reserva && ! empty($reserva['email'])) {
                        $datosSocio = [
                            'nombre'    => $reserva['usuario_nombre'],
                            'apellido1' => $reserva['usuario_apellido1'],
                            'apellido2' => '',
                            'email'     => $reserva['email'],
                        ];

                        $datosReserva = [
                            'id'           => $reserva['id'],
                            'fecha_inicio' => $reserva['fecha_inicio'],
                            'fecha_fin'    => $reserva['fecha_fin'],
                            'numero_camas' => $reserva['numero_camas'],
                            'actividad'    => $reserva['observaciones'] ?? '',
                        ];

                        notificar_socio_reserva_aprobada($datosReserva, $datosSocio);
                    }
                } catch (Exception $emailError) {
                    error_log("Error al enviar email de aprobación: " . $emailError->getMessage());
                }
                $_SESSION['mensaje']      = $mensaje;
                $_SESSION['tipo_mensaje'] = 'success';
            } else {
                $_SESSION['mensaje']      = "Error al aprobar la reserva";
                $_SESSION['tipo_mensaje'] = 'danger';
            }
            header('Location: viewAdmin.php?accion=reservas');
            exit;
            break;

        case 'rechazar_reserva':
            $id = (int) $_POST['id'];

            // Obtener datos antes de cancelar
            $reserva = obtener_reserva($conexionPDO, $id);

            if (cancelar_reserva($conexionPDO, $id)) {
                $mensaje = "Reserva rechazada exitosamente";

                // Enviar notificación por email al socio
                try {
                    require_once __DIR__ . '/api/email_notificaciones.php';

                    if ($reserva && ! empty($reserva['email'])) {
                        $datosSocio = [
                            'nombre'    => $reserva['usuario_nombre'],
                            'apellido1' => $reserva['usuario_apellido1'],
                            'apellido2' => '',
                            'email'     => $reserva['email'],
                        ];

                        $datosReserva = [
                            'id'           => $reserva['id'],
                            'fecha_inicio' => $reserva['fecha_inicio'],
                            'fecha_fin'    => $reserva['fecha_fin'],
                            'numero_camas' => $reserva['numero_camas'],
                        ];

                        notificar_socio_reserva_cancelada(
                            $datosReserva,
                            $datosSocio,
                            'La solicitud de reserva ha sido rechazada por el administrador'
                        );
                    }
                } catch (Exception $emailError) {
                    error_log("Error al enviar email de rechazo: " . $emailError->getMessage());
                }
                $_SESSION['mensaje']      = $mensaje;
                $_SESSION['tipo_mensaje'] = 'success';
            } else {
                $_SESSION['mensaje']      = "Error al rechazar la reserva";
                $_SESSION['tipo_mensaje'] = 'danger';
            }
            header('Location: viewAdmin.php?accion=reservas&tab=canceladas');
            exit;
            break;

        case 'cancelar_reserva_admin':
            $id = (int) $_POST['id'];

            // Obtener datos antes de cancelar
            $reserva = obtener_reserva($conexionPDO, $id);

            if (cancelar_reserva($conexionPDO, $id)) {
                $mensaje = "Reserva cancelada exitosamente";

                // Enviar notificación por email al socio
                try {
                    require_once __DIR__ . '/api/email_notificaciones.php';

                    if ($reserva && ! empty($reserva['email'])) {
                        $datosSocio = [
                            'nombre'    => $reserva['usuario_nombre'],
                            'apellido1' => $reserva['usuario_apellido1'],
                            'apellido2' => '',
                            'email'     => $reserva['email'],
                        ];

                        $datosReserva = [
                            'id'           => $reserva['id'],
                            'fecha_inicio' => $reserva['fecha_inicio'],
                            'fecha_fin'    => $reserva['fecha_fin'],
                            'numero_camas' => $reserva['numero_camas'],
                        ];

                        $motivo = isset($_POST['motivo']) ? $_POST['motivo'] : 'La reserva ha sido cancelada por el administrador';
                        notificar_socio_reserva_cancelada($datosReserva, $datosSocio, $motivo);
                    }
                } catch (Exception $emailError) {
                    error_log("Error al enviar email de cancelación: " . $emailError->getMessage());
                }
                $_SESSION['mensaje']      = $mensaje;
                $_SESSION['tipo_mensaje'] = 'success';
            } else {
                $_SESSION['mensaje']      = "Error al cancelar la reserva";
                $_SESSION['tipo_mensaje'] = 'danger';
            }
            header('Location: viewAdmin.php?accion=reservas&tab=canceladas');
            exit;
            break;

        case 'eliminar_reservas_canceladas':
            $ids = $_POST['ids_reservas'] ?? [];
            if (! empty($ids) && is_array($ids)) {
                $eliminadas = 0;
                try {
                    $conexionPDO->beginTransaction();

                    foreach ($ids as $id) {
                        $id = (int) $id;
                        // Verificar que la reserva esté cancelada
                        $stmt = $conexionPDO->prepare("SELECT estado FROM reservas WHERE id = :id");
                        $stmt->execute([':id' => $id]);
                        $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($reserva && $reserva['estado'] === 'cancelada') {
                            // Eliminar acompañantes
                            $stmt = $conexionPDO->prepare("DELETE FROM acompanantes WHERE id_reserva = :id");
                            $stmt->execute([':id' => $id]);

                            // Eliminar camas asignadas
                            $stmt = $conexionPDO->prepare("DELETE FROM reservas_camas WHERE id_reserva = :id");
                            $stmt->execute([':id' => $id]);

                            // Eliminar reserva
                            $stmt = $conexionPDO->prepare("DELETE FROM reservas WHERE id = :id");
                            $stmt->execute([':id' => $id]);

                            $eliminadas++;
                        }
                    }

                    $conexionPDO->commit();
                    $_SESSION['mensaje']      = "$eliminadas reserva(s) eliminada(s) exitosamente";
                    $_SESSION['tipo_mensaje'] = 'success';
                } catch (Exception $e) {
                    $conexionPDO->rollBack();
                    $_SESSION['mensaje']      = "Error al eliminar reservas: " . $e->getMessage();
                    $_SESSION['tipo_mensaje'] = 'danger';
                }
            } else {
                $_SESSION['mensaje']      = "No se seleccionaron reservas para eliminar";
                $_SESSION['tipo_mensaje'] = 'warning';
            }
            header('Location: viewAdmin.php?accion=reservas&tab=canceladas');
            exit;
            break;

        case 'editar_reserva_admin':
            $id_habitacion = 1; // Habitación default
            $id_reserva    = (int) $_POST['id_reserva'];
            $fecha_inicio  = sanitize_input($_POST['fecha_inicio']);
            $fecha_fin     = sanitize_input($_POST['fecha_fin']);
            $numero_camas  = isset($_POST['numero_camas']) && $_POST['numero_camas'] !== '' ? (int) $_POST['numero_camas'] : 0;
            $actividad     = sanitize_input($_POST['actividad'] ?? '');

            // Validar fechas
            if ($fecha_inicio > $fecha_fin) {
                $_SESSION['mensaje']      = "La fecha de fin debe ser igual o posterior a la fecha de inicio";
                $_SESSION['tipo_mensaje'] = 'danger';
            } else {
                try {
                    if (editar_reserva_admin($conexionPDO, $id_reserva, $fecha_inicio, $fecha_fin, $id_habitacion, $numero_camas)) {
                        // Actualizar actividad en observaciones
                        if (! empty($actividad)) {
                            $stmt = $conexionPDO->prepare("UPDATE reservas SET observaciones = :actividad WHERE id = :id");
                            $stmt->execute([':actividad' => $actividad, ':id' => $id_reserva]);
                        }

                        // Eliminar acompañantes antiguos y agregar nuevos
                        $stmt = $conexionPDO->prepare("DELETE FROM acompanantes WHERE id_reserva = :id_reserva");
                        $stmt->execute([':id_reserva' => $id_reserva]);

                        // Procesar acompañantes si existen
                        if (isset($_POST['acompanantes']) && is_array($_POST['acompanantes'])) {
                            foreach ($_POST['acompanantes'] as $acomp) {
                                if (! empty($acomp['dni']) && ! empty($acomp['nombre']) && ! empty($acomp['apellido1'])) {
                                    $stmt_acomp = $conexionPDO->prepare("
                                        INSERT INTO acompanantes (id_reserva, num_socio, es_socio, dni, nombre, apellido1, apellido2)
                                        VALUES (:id_reserva, :num_socio, :es_socio, :dni, :nombre, :apellido1, :apellido2)
                                    ");
                                    $stmt_acomp->execute([
                                        ':id_reserva' => $id_reserva,
                                        ':num_socio'  => $acomp['num_socio'] ?? null,
                                        ':es_socio'   => isset($acomp['es_socio']) ? 1 : 0,
                                        ':dni'        => sanitize_input($acomp['dni']),
                                        ':nombre'     => sanitize_input($acomp['nombre']),
                                        ':apellido1'  => sanitize_input($acomp['apellido1']),
                                        ':apellido2'  => sanitize_input($acomp['apellido2'] ?? ''),
                                    ]);
                                }
                            }
                        }

                        $_SESSION['mensaje']      = "Reserva actualizada exitosamente";
                        $_SESSION['tipo_mensaje'] = 'success';
                    } else {
                        $_SESSION['mensaje']      = "Error al actualizar la reserva. Verifica que haya camas disponibles.";
                        $_SESSION['tipo_mensaje'] = 'danger';
                    }
                } catch (Exception $e) {
                    $_SESSION['mensaje']      = "Error al actualizar la reserva: " . $e->getMessage();
                    $_SESSION['tipo_mensaje'] = 'danger';
                }
            }
            header("Location: viewAdmin.php?accion=reservas&tab=aprobadas");
            exit;
            break;

        case 'crear_reserva_especial':
            $id_habitacion = 1; // Habitación default

            $datos = [
                'motivo'        => sanitize_input($_POST['motivo']),
                'fecha_inicio'  => sanitize_input($_POST['fecha_inicio']),
                'fecha_fin'     => sanitize_input($_POST['fecha_fin']),
                'id_habitacion' => $id_habitacion,
                'numero_camas'  => (int) $_POST['numero_camas'],
            ];

            // Determinar el tipo de reserva y procesar
            $motivo_completo     = $datos['motivo'];
            $id_usuario_especial = null;

            // 1. Grupo de Montañeros de Tenerife
            if (isset($_POST['pertenece_grupo_tenerife_especial']) && $_POST['pertenece_grupo_tenerife_especial'] === 'on') {
                $motivo_completo .= '|Grupo:Grupo de Montañeros de Tenerife';
            }
            // 2. Asignar a un socio específico
            elseif (isset($_POST['id_usuario_especial']) && ! empty($_POST['id_usuario_especial'])) {
                $id_usuario_especial = (int) $_POST['id_usuario_especial'];
            }
            // 3. Otro grupo o asociación
            elseif (isset($_POST['otroGrupoEspecial']) || ! empty($_POST['grupo_personalizado_especial'])) {
                $grupo_nombre  = ! empty($_POST['grupo_personalizado_especial'])
                    ? sanitize_input($_POST['grupo_personalizado_especial'])
                    : 'Otro';
                $motivo_completo .= '|Grupo:' . $grupo_nombre;
            }
            // 4. No socio
            elseif (isset($_POST['asignarNoSocioEspecial']) && ! empty($_POST['nosocio_nombre'])) {
                $noSocioData = [
                    'nombre' => sanitize_input($_POST['nosocio_nombre']),
                    'dni'    => sanitize_input($_POST['nosocio_dni'] ?? ''),
                    'telf'   => sanitize_input($_POST['nosocio_telf'] ?? ''),
                    'email'  => sanitize_input($_POST['nosocio_email'] ?? ''),
                ];

                // Formato: NO SOCIO: Nombre|DNI:xxx|Telf:xxx|Email:xxx
                $motivo_completo = 'NO SOCIO: ' . $noSocioData['nombre'];
                if (! empty($noSocioData['dni'])) {
                    $motivo_completo .= '|DNI:' . $noSocioData['dni'];
                }
                if (! empty($noSocioData['telf'])) {
                    $motivo_completo .= '|Telf:' . $noSocioData['telf'];
                }
                if (! empty($noSocioData['email'])) {
                    $motivo_completo .= '|Email:' . $noSocioData['email'];
                }
                if (! empty($datos['motivo'])) {
                    $motivo_completo .= '|Actividad:' . $datos['motivo'];
                }
            }

            // Validar fechas
            if ($datos['fecha_inicio'] > $datos['fecha_fin']) {
                $_SESSION['mensaje']      = "La fecha de fin debe ser igual o posterior a la fecha de inicio";
                $_SESSION['tipo_mensaje'] = 'danger';
                header("Location: viewAdmin.php?accion=reservas");
                exit;
            } elseif ($datos['numero_camas'] < 1) {
                $_SESSION['mensaje']      = "Debe seleccionar al menos 1 cama";
                $_SESSION['tipo_mensaje'] = 'danger';
                header("Location: viewAdmin.php?accion=reservas");
                exit;
            } else {
                // Crear reserva especial
                $datos['motivo']     = $motivo_completo;
                $datos['id_usuario'] = $id_usuario_especial;
                if (crear_reserva_especial_admin($conexionPDO, $datos)) {
                    $_SESSION['mensaje']      = "Reserva especial creada exitosamente";
                    $_SESSION['tipo_mensaje'] = 'success';
                    header("Location: viewAdmin.php?accion=reservas&tab=aprobadas");
                    exit;
                } else {
                    $_SESSION['mensaje']      = "Error al crear la reserva especial. Verifica que haya camas disponibles.";
                    $_SESSION['tipo_mensaje'] = 'danger';
                    header("Location: viewAdmin.php?accion=reservas");
                    exit;
                }
            }
            break;

        case 'crear_reserva_socio':
            try {
                $id_usuario    = (int) $_POST['id_usuario'];
                $id_habitacion = 1; // Habitación default
                $numero_camas  = (int) $_POST['numero_camas'];
                $fecha_inicio  = sanitize_input($_POST['fecha_inicio']);
                $fecha_fin     = sanitize_input($_POST['fecha_fin']);

                // Validar fechas
                if ($fecha_inicio > $fecha_fin) {
                    throw new Exception("La fecha de fin debe ser igual o posterior a la fecha de inicio");
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
                    'actividad'     => sanitize_input($_POST['actividad']),
                ];

                $id_reserva = crear_reserva_para_socio($conexionPDO, $datos_reserva);

                if ($id_reserva) {
                    // Procesar acompañantes si existen
                    if (isset($_POST['acompanantes']) && is_array($_POST['acompanantes'])) {
                        foreach ($_POST['acompanantes'] as $acomp) {
                            if (! empty($acomp['dni']) && ! empty($acomp['nombre']) && ! empty($acomp['apellido1'])) {
                                $stmt_acomp = $conexionPDO->prepare("
                                    INSERT INTO acompanantes (id_reserva, num_socio, es_socio, dni, nombre, apellido1, apellido2)
                                    VALUES (:id_reserva, :num_socio, :es_socio, :dni, :nombre, :apellido1, :apellido2)
                                ");
                                $stmt_acomp->execute([
                                    ':id_reserva' => $id_reserva,
                                    ':num_socio'  => $acomp['num_socio'] ?? null,
                                    ':es_socio'   => isset($acomp['es_socio']) ? 1 : 0,
                                    ':dni'        => sanitize_input($acomp['dni']),
                                    ':nombre'     => sanitize_input($acomp['nombre']),
                                    ':apellido1'  => sanitize_input($acomp['apellido1']),
                                    ':apellido2'  => sanitize_input($acomp['apellido2'] ?? ''),
                                ]);
                            }
                        }
                    }

                    // Obtener nombre del socio
                    $stmt = $conexionPDO->prepare("SELECT nombre, apellido1 FROM usuarios WHERE id = :id");
                    $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
                    $stmt->execute();
                    $socio = $stmt->fetch(PDO::FETCH_ASSOC);

                    $_SESSION['mensaje']      = "Reserva creada y aprobada automáticamente para {$socio['nombre']} {$socio['apellido1']}";
                    $_SESSION['tipo_mensaje'] = 'success';

                    // Redirección para prevenir reenvío del formulario
                    header("Location: viewAdmin.php?accion=reservas&tab=aprobadas");
                    exit;
                } else {
                    throw new Exception("No hay suficientes camas disponibles");
                }
            } catch (Exception $e) {
                $_SESSION['mensaje']      = "Error al crear reserva: " . $e->getMessage();
                $_SESSION['tipo_mensaje'] = 'danger';
                header("Location: viewAdmin.php?accion=reservas");
                exit;
            }
            break;

        case 'crear_reserva_no_socio':
            try {
                // Recoger datos personales
                $dni       = sanitize_input($_POST['dni']);
                $nombre    = sanitize_input($_POST['nombre']);
                $apellido1 = sanitize_input($_POST['apellido1']);
                $apellido2 = sanitize_input($_POST['apellido2'] ?? '');
                $telefono  = sanitize_input($_POST['telefono']);
                $email     = sanitize_input($_POST['email'] ?? '');

                // Determinar grupo de montañeros
                $grupo = '';
                if (isset($_POST['pertenece_grupo_tenerife']) && $_POST['pertenece_grupo_tenerife'] === 'on') {
                    $grupo = 'Grupo de Montañeros de Tenerife';
                } elseif (! empty($_POST['grupo_personalizado'])) {
                    $grupo = sanitize_input($_POST['grupo_personalizado']);
                }

                                    // Datos de reserva
                $id_habitacion = 1; // Habitación default
                $numero_camas  = (int) $_POST['numero_camas'];
                $fecha_inicio  = sanitize_input($_POST['fecha_inicio']);
                $fecha_fin     = sanitize_input($_POST['fecha_fin']);
                $actividad     = sanitize_input($_POST['actividad']);

                // Validar fechas
                if ($fecha_inicio > $fecha_fin) {
                    throw new Exception("La fecha de fin debe ser igual o posterior a la fecha de inicio");
                }

                // Validar número de camas
                if ($numero_camas < 1) {
                    throw new Exception("Debe seleccionar al menos 1 cama");
                }

                // Crear datos estructurados para NO SOCIO
                // Formato: NO_SOCIO|nombre|dni|telefono|email|grupo|||ACTIVIDAD:actividad
                $datos_no_socio = "NO_SOCIO|$nombre $apellido1";
                if (! empty($apellido2)) {
                    $datos_no_socio .= " $apellido2";
                }

                $datos_no_socio .= "|DNI:$dni|Tel:$telefono";
                if (! empty($email)) {
                    $datos_no_socio .= "|Email:$email";
                }

                if (! empty($grupo)) {
                    $datos_no_socio .= "|Grupo:$grupo";
                }

                $datos_no_socio .= "|||ACTIVIDAD:$actividad";

                // Crear reserva usando la función de socio pero con id_usuario NULL
                $conexionPDO->beginTransaction();

                // Buscar camas disponibles
                $stmt = $conexionPDO->prepare("
                    SELECT id FROM camas
                    WHERE id_habitacion = :id_habitacion
                    AND id NOT IN (
                        SELECT DISTINCT c.id
                        FROM camas c
                        INNER JOIN reservas_camas rc ON c.id = rc.id_cama
                        INNER JOIN reservas r ON rc.id_reserva = r.id
                        WHERE c.id_habitacion = :id_habitacion
                        AND r.estado IN ('pendiente', 'reservada')
                        AND (r.fecha_inicio <= :fecha_fin AND r.fecha_fin >= :fecha_inicio)
                    )
                    ORDER BY numero
                    LIMIT :numero_camas
                ");

                $stmt->bindParam(':id_habitacion', $id_habitacion, PDO::PARAM_INT);
                $stmt->bindParam(':fecha_inicio', $fecha_inicio);
                $stmt->bindParam(':fecha_fin', $fecha_fin);
                $stmt->bindParam(':numero_camas', $numero_camas, PDO::PARAM_INT);
                $stmt->execute();

                $camas_disponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);

                if (count($camas_disponibles) < $numero_camas) {
                    throw new Exception("No hay suficientes camas disponibles en esta habitación");
                }

                // Crear reserva (id_usuario NULL para no socios)
                $stmt = $conexionPDO->prepare("
                    INSERT INTO reservas (id_usuario, id_habitacion, numero_camas, fecha_inicio, fecha_fin, estado, observaciones)
                    VALUES (NULL, :id_habitacion, :numero_camas, :fecha_inicio, :fecha_fin, 'reservada', :observaciones)
                ");

                $stmt->bindParam(':id_habitacion', $id_habitacion, PDO::PARAM_INT);
                $stmt->bindParam(':numero_camas', $numero_camas, PDO::PARAM_INT);
                $stmt->bindParam(':fecha_inicio', $fecha_inicio);
                $stmt->bindParam(':fecha_fin', $fecha_fin);
                $stmt->bindParam(':observaciones', $datos_no_socio);
                $stmt->execute();

                $id_reserva = $conexionPDO->lastInsertId();

                // Asignar camas
                $stmt_cama   = $conexionPDO->prepare("INSERT INTO reservas_camas (id_reserva, id_cama) VALUES (:id_reserva, :id_cama)");
                $stmt_update = $conexionPDO->prepare("UPDATE camas SET estado = 'reservada' WHERE id = :id_cama");

                foreach ($camas_disponibles as $id_cama) {
                    $stmt_cama->bindParam(':id_reserva', $id_reserva, PDO::PARAM_INT);
                    $stmt_cama->bindParam(':id_cama', $id_cama, PDO::PARAM_INT);
                    $stmt_cama->execute();

                    $stmt_update->bindParam(':id_cama', $id_cama, PDO::PARAM_INT);
                    $stmt_update->execute();
                }

                // Procesar acompañantes si existen
                if (isset($_POST['acompanantes']) && is_array($_POST['acompanantes'])) {
                    foreach ($_POST['acompanantes'] as $acomp) {
                        if (! empty($acomp['dni']) && ! empty($acomp['nombre']) && ! empty($acomp['apellido1'])) {
                            $stmt_acomp = $conexionPDO->prepare("
                                INSERT INTO acompanantes (id_reserva, dni, nombre, apellido1, apellido2)
                                VALUES (:id_reserva, :dni, :nombre, :apellido1, :apellido2)
                            ");
                            $stmt_acomp->execute([
                                ':id_reserva' => $id_reserva,
                                ':dni'        => sanitize_input($acomp['dni']),
                                ':nombre'     => sanitize_input($acomp['nombre']),
                                ':apellido1'  => sanitize_input($acomp['apellido1']),
                                ':apellido2'  => sanitize_input($acomp['apellido2'] ?? ''),
                            ]);
                        }
                    }
                }

                $conexionPDO->commit();

                $_SESSION['mensaje']      = "Reserva creada y aprobada automáticamente para NO SOCIO: $nombre $apellido1";
                $_SESSION['tipo_mensaje'] = 'success';

                header("Location: viewAdmin.php?accion=reservas&tab=aprobadas");
                exit;

            } catch (Exception $e) {
                if ($conexionPDO->inTransaction()) {
                    $conexionPDO->rollBack();
                }
                $_SESSION['mensaje']      = "Error al crear reserva: " . $e->getMessage();
                $_SESSION['tipo_mensaje'] = 'danger';
                header("Location: viewAdmin.php?accion=reservas");
                exit;
            }
            break;

        case 'export_pdf':
            $tipo_reserva = $_POST['tipo_reserva'] ?? 'pendiente';
            export_reservas_pdf($conexionPDO, $tipo_reserva, $_POST);
            exit;

        case 'export_csv':
            $tipo_reserva = $_POST['tipo_reserva'] ?? 'pendiente';
            $filename     = "reservas_{$tipo_reserva}_" . date('Y-m-d') . ".csv";

            // Configurar headers para CSV con UTF-8 BOM para Excel
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');

            // Añadir BOM UTF-8 para que Excel reconozca correctamente los caracteres especiales
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Encabezados CSV
            fputcsv($output, ['ID', 'Usuario', 'Email', 'Telefono', 'Habitacion', 'Camas', 'Actividad', 'Montanero', 'Entrada', 'Salida', 'Estado', 'Fecha Creacion'], ',', '"', '\\');

            // Obtener datos (sin paginación para exportar todo)
            $filtros = ['estado' => $tipo_reserva];

            // Aplicar filtros de búsqueda si existen
            if (! empty($_POST['search'])) {
                $filtros['search'] = $_POST['search'];
            }

            // Aplicar ordenamiento si existe
            if (! empty($_POST['sort'])) {
                $filtros['order_by']  = $_POST['sort'];
                $filtros['order_dir'] = $_POST['order_dir'] ?? 'DESC';
            }

            $reservas = listar_reservas($conexionPDO, $filtros);

            foreach ($reservas as $row) {
                $usuario_info = mostrar_usuario_reserva($row);

                // Extraer teléfono
                $telefono = '-';
                if (! empty($row['telf'])) {
                    $telefono = $row['telf'];
                } elseif (! empty($row['observaciones']) && strpos($row['observaciones'], 'Tel:') !== false) {
                    preg_match('/Tel:([^|]+)/', $row['observaciones'], $matches);
                    if (isset($matches[1])) {
                        $telefono = trim($matches[1]);
                    }
                }

                // Extraer email
                $email = '-';
                if (! empty($row['email'])) {
                    $email = $row['email'];
                } elseif (! empty($row['observaciones']) && strpos($row['observaciones'], 'Email:') !== false) {
                    preg_match('/Email:([^|]+)/', $row['observaciones'], $matches);
                    if (isset($matches[1])) {
                        $email = trim($matches[1]);
                    }
                }

                fputcsv($output, [
                    $row['id'],
                    $usuario_info['display'],
                    $email,
                    $telefono,
                    $row['habitacion_numero'] ?? 'Todo el Refugio',
                    $row['numero_camas'],
                    $usuario_info['actividad'],
                    $usuario_info['montanero'],
                    $row['fecha_inicio'],
                    $row['fecha_fin'],
                    $row['estado'],
                    $row['fecha_creacion'],
                ], ',', '"', '\\');
            }

            fclose($output);
            exit;
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
    // Parámetros de paginación y filtros para usuarios
    $page_usuarios      = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit_usuarios     = 10;
    $offset_usuarios    = ($page_usuarios - 1) * $limit_usuarios;
    $search_usuarios    = $_GET['search'] ?? '';
    $sort_usuarios      = $_GET['sort'] ?? 'num_socio';
    $order_dir_usuarios = $_GET['dir'] ?? 'ASC';

    $filtros_usuarios = [
        'page'      => $page_usuarios,
        'limit'     => $limit_usuarios,
        'search'    => $search_usuarios,
        'order_by'  => $sort_usuarios,
        'order_dir' => $order_dir_usuarios,
    ];

    $usuarios         = listar_usuarios_paginado($conexionPDO, $filtros_usuarios);
    $total_usuarios   = contar_usuarios($conexionPDO, ['search' => $search_usuarios]);
    $paginas_usuarios = ceil($total_usuarios / $limit_usuarios);

    if ($accion === 'editar_usuario' && isset($_GET['id'])) {
        $usuario_editar = obtener_usuario($conexionPDO, (int) $_GET['id']);
    }
    } elseif ($accion === 'reservas') {
    // Parámetros de paginación y filtros
    $page      = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit     = 10;
    $offset    = ($page - 1) * $limit;
    $search    = $_GET['search'] ?? '';
    $sort      = $_GET['sort'] ?? 'fecha_inicio';
    $order_dir = $_GET['dir'] ?? 'ASC';
    $tab       = $_GET['tab'] ?? 'pendientes'; // Para saber qué pestaña está activa

    // Filtros comunes
    $filtros_base = [
        'limit'     => $limit,
        'offset'    => $offset,
        'search'    => $search,
        'order_by'  => $sort,
        'order_dir' => $order_dir,
    ];

    // Reservas Pendientes
    $filtros_pendientes = array_merge($filtros_base, ['estado' => 'pendiente']);
    if ($tab !== 'pendientes') {
                                              // Si no es la pestaña activa, solo necesitamos el conteo o las primeras 5
                                              // Pero para simplificar, cargamos según la paginación si es la activa, o reset si no
        unset($filtros_pendientes['offset']); // Reset offset for non-active tabs if needed, but keeping simple for now
    }

    // Ajustar offset solo para la pestaña activa
    $filtros_pendientes['offset'] = ($tab === 'pendientes') ? $offset : 0;

    $reservas_pendientes = listar_reservas($conexionPDO, $filtros_pendientes);
    $total_pendientes    = contar_reservas($conexionPDO, array_merge($filtros_pendientes, ['limit' => null, 'offset' => null]));
    $paginas_pendientes  = ceil($total_pendientes / $limit);

    // Reservas Aprobadas
    $filtros_aprobadas           = array_merge($filtros_base, ['estado' => 'reservada']);
    $filtros_aprobadas['offset'] = ($tab === 'aprobadas') ? $offset : 0;

    $reservas_aprobadas = listar_reservas($conexionPDO, $filtros_aprobadas);
    $total_aprobadas    = contar_reservas($conexionPDO, array_merge($filtros_aprobadas, ['limit' => null, 'offset' => null]));
    $paginas_aprobadas  = ceil($total_aprobadas / $limit);

    // Reservas Canceladas
    $filtros_canceladas           = array_merge($filtros_base, ['estado' => 'cancelada']);
    $filtros_canceladas['offset'] = ($tab === 'canceladas') ? $offset : 0;

    $reservas_canceladas = listar_reservas($conexionPDO, $filtros_canceladas);
    $total_canceladas    = contar_reservas($conexionPDO, array_merge($filtros_canceladas, ['limit' => null, 'offset' => null]));
    $paginas_canceladas  = ceil($total_canceladas / $limit);

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
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🏔️</text></svg>">
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
        .card-stat.danger {
            border-color: #ef4444;
        }
        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
        }
        /* Estilos para encabezados ordenables */
        th a {
            cursor: pointer;
            user-select: none;
        }
        th a:hover {
            color: #3b82f6 !important;
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
                    <a class="nav-link                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         <?php echo $accion === 'dashboard' ? 'active' : '' ?>" href="?accion=dashboard">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a class="nav-link                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         <?php echo $accion === 'usuarios' || $accion === 'editar_usuario' ? 'active' : '' ?>" href="?accion=usuarios">
                        <i class="bi bi-people-fill"></i> Usuarios
                    </a>
                    <a class="nav-link                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         <?php echo $accion === 'reservas' ? 'active' : '' ?>" href="?accion=reservas">
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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-speedometer2"></i> Dashboard</h2>
                        <div>
                            <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#modalReservaSocio" onclick="console.log('Click en botón Socio');">
                                <i class="bi bi-person-plus"></i> Nueva Reserva Socio
                            </button>
                            <button class="btn btn-info me-2" data-bs-toggle="modal" data-bs-target="#modalReservaNoSocio" onclick="console.log('Click en botón No Socio');">
                                <i class="bi bi-person"></i> Nueva Reserva No Socio
                            </button>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalReservaEspecial" onclick="console.log('Click en botón Especial');">
                                <i class="bi bi-calendar-event"></i> Nueva Reserva Especial
                            </button>
                        </div>
                    </div>

                    <!-- Fila única de estadísticas -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <a href="?accion=reservas&tab=pendientes" class="text-decoration-none">
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
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="?accion=reservas&tab=aprobadas" class="text-decoration-none">
                                <div class="card card-stat success shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="text-muted">Reservas Aprobadas</h6>
                                                <h2><?php
                                                        $reservas_aprobadas_count = contar_reservas($conexionPDO, ['estado' => 'reservada']);
                                                    echo $reservas_aprobadas_count;
                                                    ?></h2>
                                            </div>
                                            <i class="bi bi-check-circle-fill fs-1 text-success"></i>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="?accion=reservas&tab=canceladas" class="text-decoration-none">
                                <div class="card card-stat danger shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="text-muted">Reservas Canceladas</h6>
                                                <h2><?php
                                                        $reservas_canceladas_count = contar_reservas($conexionPDO, ['estado' => 'cancelada']);
                                                    echo $reservas_canceladas_count;
                                                    ?></h2>
                                            </div>
                                            <i class="bi bi-x-circle-fill fs-1 text-danger"></i>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <div class="card card-stat primary shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted">Total Camas</h6>
                                            <h2>26</h2>
                                        </div>
                                        <i class="bi bi-grid-3x3-gap-fill fs-1 text-primary"></i>
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

                    <!-- DEBUG: Fin del calendario -->
                    <!-- DEBUG: Fin del Dashboard, antes del elseif usuarios -->

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
                            <!-- Controles de búsqueda, ordenación y exportación -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <form method="get" class="input-group">
                                        <input type="hidden" name="accion" value="usuarios">
                                        <input type="text" class="form-control" name="search"
                                               placeholder="Buscar por nombre, email, DNI..."
                                               value="<?php echo htmlspecialchars($search_usuarios) ?>">
                                        <button class="btn btn-outline-secondary" type="submit">
                                            <i class="bi bi-search"></i>
                                        </button>
                                    </form>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select" onchange="window.location.href='?accion=usuarios&search=<?php echo urlencode($search_usuarios) ?>&sort=' + this.value + '&dir=<?php echo $order_dir_usuarios ?>'">
                                        <option value="num_socio"                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <?php echo $sort_usuarios === 'num_socio' ? 'selected' : '' ?>>Ordenar por Nº Socio</option>
                                        <option value="nombre"                                                                                                                                                                                                                                                                                                                                                                                                                                                   <?php echo $sort_usuarios === 'nombre' ? 'selected' : '' ?>>Ordenar por Nombre</option>
                                        <option value="email"                                                                                                                                                                                                                                                                                                                                                                                                                                            <?php echo $sort_usuarios === 'email' ? 'selected' : '' ?>>Ordenar por Email</option>
                                    </select>
                                </div>
                                <div class="col-md-4 text-end">
                                    <a href="?accion=export_usuarios_csv&search=<?php echo urlencode($search_usuarios) ?>&sort=<?php echo urlencode($sort_usuarios) ?>&dir=<?php echo urlencode($order_dir_usuarios) ?>"
                                       class="btn btn-success me-2">
                                        <i class="bi bi-file-earmark-spreadsheet"></i> CSV
                                    </a>
                                    <a href="?accion=export_usuarios_pdf&search=<?php echo urlencode($search_usuarios) ?>&sort=<?php echo urlencode($sort_usuarios) ?>&dir=<?php echo urlencode($order_dir_usuarios) ?>"
                                       class="btn btn-danger">
                                        <i class="bi bi-file-earmark-pdf"></i> PDF
                                    </a>
                                </div>
                            </div>

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
                                                    <?php if ($usuario['email'] !== 'admin@hostel.com'): ?>
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
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">
                                                            <i class="bi bi-shield-lock-fill"></i> Protegido
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginación -->
                            <?php if ($paginas_usuarios > 1): ?>
                                <nav class="mt-3">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page_usuarios > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?accion=usuarios&page=<?php echo $page_usuarios - 1 ?>&search=<?php echo urlencode($search_usuarios) ?>&sort=<?php echo urlencode($sort_usuarios) ?>&dir=<?php echo urlencode($order_dir_usuarios) ?>">Anterior</a>
                                            </li>
                                        <?php endif; ?>

                                        <?php for ($i = 1; $i <= $paginas_usuarios; $i++): ?>
                                            <li class="page-item<?php echo $i === $page_usuarios ? 'active' : '' ?>">
                                                <a class="page-link" href="?accion=usuarios&page=<?php echo $i ?>&search=<?php echo urlencode($search_usuarios) ?>&sort=<?php echo urlencode($sort_usuarios) ?>&dir=<?php echo urlencode($order_dir_usuarios) ?>"><?php echo $i ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($page_usuarios < $paginas_usuarios): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?accion=usuarios&page=<?php echo $page_usuarios + 1 ?>&search=<?php echo urlencode($search_usuarios) ?>&sort=<?php echo urlencode($sort_usuarios) ?>&dir=<?php echo urlencode($order_dir_usuarios) ?>">Siguiente</a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
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
                                                <label class="form-label">Contraseña                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             <?php echo $usuario_editar ? '' : '*' ?></label>
                                                <input type="password" name="password" class="form-control"
                                                       <?php echo $usuario_editar ? '' : 'required' ?>>
                                                <?php if ($usuario_editar): ?>
                                                    <small class="text-muted">Dejar en blanco para mantener la actual</small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Rol *</label>
                                                <select name="rol" class="form-select" required>
                                                    <option value="user"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 <?php echo($usuario_editar && $usuario_editar['rol'] === 'user') ? 'selected' : '' ?>>User</option>
                                                    <option value="admin"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             <?php echo($usuario_editar && $usuario_editar['rol'] === 'admin') ? 'selected' : '' ?>>Admin</option>
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
                            <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#modalReservaSocio">
                                <i class="bi bi-person-plus"></i> Nueva Reserva Socio
                            </button>
                            <button class="btn btn-info me-2" data-bs-toggle="modal" data-bs-target="#modalReservaNoSocio">
                                <i class="bi bi-person"></i> Nueva Reserva No Socio
                            </button>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalReservaEspecial">
                                <i class="bi bi-calendar-event"></i> Nueva Reserva Especial
                            </button>
                        </div>
                    </div>

                    <!-- Tabs de Navegación -->
                    <ul class="nav nav-tabs mb-4">
                        <li class="nav-item">
                            <a class="nav-link                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           <?php echo $tab === 'pendientes' ? 'active' : '' ?>" href="?accion=reservas&tab=pendientes">
                                Pendientes <span class="badge bg-warning text-dark"><?php echo $total_pendientes ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           <?php echo $tab === 'aprobadas' ? 'active' : '' ?>" href="?accion=reservas&tab=aprobadas">
                                Aprobadas <span class="badge bg-success"><?php echo $total_aprobadas ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           <?php echo $tab === 'canceladas' ? 'active' : '' ?>" href="?accion=reservas&tab=canceladas">
                                Canceladas <span class="badge bg-danger"><?php echo $total_canceladas ?></span>
                            </a>
                        </li>
                    </ul>

                    <?php if ($tab === 'pendientes'): ?>
                        <!-- Reservas Pendientes -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Reservas Pendientes</h5>
                                <div class="d-flex gap-2">
                                    <form class="d-flex gap-2" method="get">
                                        <input type="hidden" name="accion" value="reservas">
                                        <input type="hidden" name="tab" value="pendientes">
                                        <input type="hidden" name="dir" value="ASC">
                                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar..." value="<?php echo htmlspecialchars($search) ?>">
                                        <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="fecha_inicio" <?php echo $sort === 'fecha_inicio' ? 'selected' : '' ?>>Fecha Entrada</option>
                                            <option value="fecha_creacion" <?php echo $sort === 'fecha_creacion' ? 'selected' : '' ?>>Fecha Solicitud</option>
                                            <option value="nombre" <?php echo $sort === 'nombre' ? 'selected' : '' ?>>Nombre</option>
                                        </select>
                                        <button type="submit" class="btn btn-light btn-sm"><i class="bi bi-search"></i></button>
                                    </form>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="accion" value="export_csv">
                                        <input type="hidden" name="tipo_reserva" value="pendiente">
                                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search) ?>">
                                        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-light"><i class="bi bi-file-earmark-spreadsheet"></i> CSV</button>
                                    </form>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="accion" value="export_pdf">
                                        <input type="hidden" name="tipo_reserva" value="pendiente">
                                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search) ?>">
                                        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort) ?>">
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-file-earmark-pdf"></i> PDF</button>
                                    </form>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (count($reservas_pendientes) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>
                                                        <a href="?accion=reservas&tab=pendientes&sort=nombre&dir=<?php echo($sort === 'nombre' && $order_dir === 'ASC') ? 'DESC' : 'ASC' ?>&search=<?php echo urlencode($search) ?>" class="text-decoration-none text-dark">
                                                            Usuario <?php if ($sort === 'nombre') {
                                                                            echo($order_dir === 'ASC' ? '▲' : '▼');
                                                                        }
                                                                    ?>
                                                        </a>
                                                    </th>
                                                    <th>Camas</th>
                                                    <th>
                                                        <a href="?accion=reservas&tab=pendientes&sort=fecha_inicio&dir=<?php echo($sort === 'fecha_inicio' && $order_dir === 'ASC') ? 'DESC' : 'ASC' ?>&search=<?php echo urlencode($search) ?>" class="text-decoration-none text-dark">
                                                            Entrada <?php if ($sort === 'fecha_inicio') {
                                                                            echo($order_dir === 'ASC' ? '▲' : '▼');
                                                                        }
                                                                    ?>
                                                        </a>
                                                    </th>
                                                    <th>
                                                        <a href="?accion=reservas&tab=pendientes&sort=fecha_fin&dir=<?php echo($sort === 'fecha_fin' && $order_dir === 'ASC') ? 'DESC' : 'ASC' ?>&search=<?php echo urlencode($search) ?>" class="text-decoration-none text-dark">
                                                            Salida <?php if ($sort === 'fecha_fin') {
                                                                           echo($order_dir === 'ASC' ? '▲' : '▼');
                                                                       }
                                                                   ?>
                                                        </a>
                                                    </th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($reservas_pendientes as $reserva): ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?php echo htmlspecialchars($reserva['nombre'] . ' ' . $reserva['apellido1']) ?></strong><br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($reserva['email']) ?> | <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($reserva['telf']) ?></small>
                                                        </td>
                                                        <td><?php echo $reserva['numero_camas'] ?></td>
                                                        <td><?php echo formatear_fecha($reserva['fecha_inicio']) ?></td>
                                                        <td><?php echo formatear_fecha($reserva['fecha_fin']) ?></td>
                                                        <td>
                                                            <form method="post" class="d-inline">
                                                                <input type="hidden" name="accion" value="aprobar_reserva">
                                                                <input type="hidden" name="id" value="<?php echo $reserva['id'] ?>">
                                                                <button type="submit" class="btn btn-sm btn-success" title="Aprobar"><i class="bi bi-check-lg"></i></button>
                                                            </form>
                                                            <form method="post" class="d-inline">
                                                                <input type="hidden" name="accion" value="rechazar_reserva">
                                                                <input type="hidden" name="id" value="<?php echo $reserva['id'] ?>">
                                                                <button type="submit" class="btn btn-sm btn-danger" title="Rechazar" onclick="return confirm('¿Rechazar reserva?')"><i class="bi bi-x-lg"></i></button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- Paginación -->
                                    <?php if ($paginas_pendientes > 1): ?>
                                        <nav>
                                            <ul class="pagination justify-content-center">
                                                <?php for ($i = 1; $i <= $paginas_pendientes; $i++): ?>
                                                    <li class="page-item<?php echo $page === $i ? 'active' : '' ?>">
                                                        <a class="page-link" href="?accion=reservas&tab=pendientes&page=<?php echo $i ?>&search=<?php echo urlencode($search) ?>&sort=<?php echo urlencode($sort) ?>&dir=ASC"><?php echo $i ?></a>
                                                    </li>
                                                <?php endfor; ?>
                                            </ul>
                                        </nav>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="text-center text-muted py-4">No hay reservas pendientes</p>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php elseif ($tab === 'aprobadas'): ?>
                        <!-- Reservas Aprobadas -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Reservas Aprobadas</h5>
                                <div class="d-flex gap-2">
                                    <form class="d-flex gap-2" method="get">
                                        <input type="hidden" name="accion" value="reservas">
                                        <input type="hidden" name="tab" value="aprobadas">
                                        <input type="hidden" name="dir" value="ASC">
                                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar..." value="<?php echo htmlspecialchars($search) ?>">
                                        <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="fecha_inicio" <?php echo $sort === 'fecha_inicio' ? 'selected' : '' ?>>Fecha Entrada</option>
                                            <option value="fecha_creacion" <?php echo $sort === 'fecha_creacion' ? 'selected' : '' ?>>Fecha Solicitud</option>
                                            <option value="nombre" <?php echo $sort === 'nombre' ? 'selected' : '' ?>>Nombre</option>
                                        </select>
                                        <button type="submit" class="btn btn-light btn-sm"><i class="bi bi-search"></i></button>
                                    </form>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="accion" value="export_csv">
                                        <input type="hidden" name="tipo_reserva" value="reservada">
                                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search) ?>">
                                        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-light"><i class="bi bi-file-earmark-spreadsheet"></i> CSV</button>
                                    </form>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="accion" value="export_pdf">
                                        <input type="hidden" name="tipo_reserva" value="reservada">
                                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search) ?>">
                                        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort) ?>">
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-file-earmark-pdf"></i> PDF</button>
                                    </form>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (count($reservas_aprobadas) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>
                                                        <a href="?accion=reservas&tab=aprobadas&sort=nombre&dir=<?php echo($sort === 'nombre' && $order_dir === 'ASC') ? 'DESC' : 'ASC' ?>&search=<?php echo urlencode($search) ?>" class="text-decoration-none text-dark">
                                                            Usuario <?php if ($sort === 'nombre') {
                                                                            echo($order_dir === 'ASC' ? '▲' : '▼');
                                                                        }
                                                                    ?>
                                                        </a>
                                                    </th>
                                                    <th>Camas</th>
                                                    <th>Actividad</th>
                                                    <th>Montañero</th>
                                                    <th>
                                                        <a href="?accion=reservas&tab=aprobadas&sort=fecha_inicio&dir=<?php echo($sort === 'fecha_inicio' && $order_dir === 'ASC') ? 'DESC' : 'ASC' ?>&search=<?php echo urlencode($search) ?>" class="text-decoration-none text-dark">
                                                            Entrada <?php if ($sort === 'fecha_inicio') {
                                                                            echo($order_dir === 'ASC' ? '▲' : '▼');
                                                                        }
                                                                    ?>
                                                        </a>
                                                    </th>
                                                    <th>
                                                        <a href="?accion=reservas&tab=aprobadas&sort=fecha_fin&dir=<?php echo($sort === 'fecha_fin' && $order_dir === 'ASC') ? 'DESC' : 'ASC' ?>&search=<?php echo urlencode($search) ?>" class="text-decoration-none text-dark">
                                                            Salida <?php if ($sort === 'fecha_fin') {
                                                                           echo($order_dir === 'ASC' ? '▲' : '▼');
                                                                       }
                                                                   ?>
                                                        </a>
                                                    </th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($reservas_aprobadas as $reserva):
                                                        $usuario_info = mostrar_usuario_reserva($reserva);
                                                ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?php echo $usuario_info['display'] ?></strong>
                                                        </td>
                                                        <td><?php echo $reserva['numero_camas'] ?></td>
                                                        <td><?php echo $usuario_info['actividad'] ?></td>
                                                        <td><?php echo $usuario_info['montanero'] ?></td>
                                                        <td><?php echo formatear_fecha($reserva['fecha_inicio']) ?></td>
                                                        <td><?php echo formatear_fecha($reserva['fecha_fin']) ?></td>
                                                        <td>
                                                            <button class="btn btn-sm btn-warning" onclick='editarReserva(<?php echo json_encode($reserva) ?>)' title="Editar">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            <form method="post" class="d-inline">
                                                                <input type="hidden" name="accion" value="cancelar_reserva_admin">
                                                                <input type="hidden" name="id" value="<?php echo $reserva['id'] ?>">
                                                                <button type="submit" class="btn btn-sm btn-danger" title="Cancelar" onclick="return confirm('¿Cancelar esta reserva?')"><i class="bi bi-x-circle"></i></button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- Paginación -->
                                    <?php if ($paginas_aprobadas > 1): ?>
                                        <nav>
                                            <ul class="pagination justify-content-center">
                                                <?php for ($i = 1; $i <= $paginas_aprobadas; $i++): ?>
                                                    <li class="page-item<?php echo $page === $i ? 'active' : '' ?>">
                                                        <a class="page-link" href="?accion=reservas&tab=aprobadas&page=<?php echo $i ?>&search=<?php echo urlencode($search) ?>&sort=<?php echo urlencode($sort) ?>&dir=ASC"><?php echo $i ?></a>
                                                    </li>
                                                <?php endfor; ?>
                                            </ul>
                                        </nav>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="text-center text-muted py-4">No hay reservas aprobadas</p>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php elseif ($tab === 'canceladas'): ?>
                        <!-- Reservas Canceladas -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Reservas Canceladas</h5>
                                <div class="d-flex gap-2">
                                    <form class="d-flex gap-2" method="get">
                                        <input type="hidden" name="accion" value="reservas">
                                        <input type="hidden" name="tab" value="canceladas">
                                        <input type="hidden" name="dir" value="ASC">
                                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar..." value="<?php echo htmlspecialchars($search) ?>">
                                        <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="fecha_inicio" <?php echo $sort === 'fecha_inicio' ? 'selected' : '' ?>>Fecha Entrada</option>
                                            <option value="fecha_creacion" <?php echo $sort === 'fecha_creacion' ? 'selected' : '' ?>>Fecha Solicitud</option>
                                            <option value="nombre" <?php echo $sort === 'nombre' ? 'selected' : '' ?>>Nombre</option>
                                        </select>
                                        <button type="submit" class="btn btn-light btn-sm"><i class="bi bi-search"></i></button>
                                    </form>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="accion" value="export_csv">
                                        <input type="hidden" name="tipo_reserva" value="cancelada">
                                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search) ?>">
                                        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-light"><i class="bi bi-file-earmark-spreadsheet"></i> CSV</button>
                                    </form>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="accion" value="export_pdf">
                                        <input type="hidden" name="tipo_reserva" value="cancelada">
                                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search) ?>">
                                        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort) ?>">
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-file-earmark-pdf"></i> PDF</button>
                                    </form>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (count($reservas_canceladas) > 0): ?>
                                    <div class="mb-3 d-flex gap-2 align-items-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleSelectAll()">
                                            <i class="bi bi-check-square"></i> Seleccionar Todas
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="eliminarSeleccionadas()" id="btnEliminarSeleccionadas" disabled>
                                            <i class="bi bi-trash"></i> Eliminar Seleccionadas
                                        </button>
                                        <span class="text-muted small" id="contadorSeleccionadas">0 seleccionadas</span>
                                    </div>
                                    <form method="post" id="formEliminarCanceladas">
                                        <input type="hidden" name="accion" value="eliminar_reservas_canceladas">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 40px;">
                                                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onchange="toggleSelectAll()">
                                                        </th>
                                                        <th>
                                                            <a href="?accion=reservas&tab=canceladas&sort=nombre&dir=<?php echo($sort === 'nombre' && $order_dir === 'ASC') ? 'DESC' : 'ASC' ?>&search=<?php echo urlencode($search) ?>" class="text-decoration-none text-dark">
                                                                Usuario <?php if ($sort === 'nombre') {
                                                                                echo($order_dir === 'ASC' ? '▲' : '▼');
                                                                            }
                                                                        ?>
                                                            </a>
                                                        </th>
                                                        <th>Camas</th>
                                                        <th>
                                                            <a href="?accion=reservas&tab=canceladas&sort=fecha_inicio&dir=<?php echo($sort === 'fecha_inicio' && $order_dir === 'ASC') ? 'DESC' : 'ASC' ?>&search=<?php echo urlencode($search) ?>" class="text-decoration-none text-dark">
                                                                Entrada <?php if ($sort === 'fecha_inicio') {
                                                                                echo($order_dir === 'ASC' ? '▲' : '▼');
                                                                            }
                                                                        ?>
                                                            </a>
                                                        </th>
                                                        <th>
                                                            <a href="?accion=reservas&tab=canceladas&sort=fecha_fin&dir=<?php echo($sort === 'fecha_fin' && $order_dir === 'ASC') ? 'DESC' : 'ASC' ?>&search=<?php echo urlencode($search) ?>" class="text-decoration-none text-dark">
                                                                Salida <?php if ($sort === 'fecha_fin') {
                                                                               echo($order_dir === 'ASC' ? '▲' : '▼');
                                                                           }
                                                                       ?>
                                                            </a>
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($reservas_canceladas as $reserva):
                                                            $usuario_info = mostrar_usuario_reserva($reserva);
                                                    ?>
                                                        <tr>
                                                            <td>
                                                                <input type="checkbox" class="form-check-input reserva-checkbox" name="ids_reservas[]" value="<?php echo $reserva['id'] ?>" onchange="actualizarContador()">
                                                            </td>
                                                            <td>
                                                                <strong><?php echo $usuario_info['display'] ?></strong>
                                                            </td>
                                                            <td><?php echo $reserva['numero_camas'] ?></td>
                                                            <td><?php echo formatear_fecha($reserva['fecha_inicio']) ?></td>
                                                            <td><?php echo formatear_fecha($reserva['fecha_fin']) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </form>
                                    <!-- Paginación -->
                                    <?php if ($paginas_canceladas > 1): ?>
                                        <nav>
                                            <ul class="pagination justify-content-center">
                                                <?php for ($i = 1; $i <= $paginas_canceladas; $i++): ?>
                                                    <li class="page-item<?php echo $page === $i ? 'active' : '' ?>">
                                                        <a class="page-link" href="?accion=reservas&tab=canceladas&page=<?php echo $i ?>&search=<?php echo urlencode($search) ?>&sort=<?php echo urlencode($sort) ?>&dir=ASC"><?php echo $i ?></a>
                                                    </li>
                                                <?php endfor; ?>
                                            </ul>
                                        </nav>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="text-center text-muted py-4">No hay reservas canceladas</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>
                <!-- Fin de la sección de reservas -->
                <!-- DEBUG: Después del endif de reservas -->

            </div> <!-- End col-md-10 -->
        </div> <!-- End row -->
    </div> <!-- End container-fluid -->

    <!-- DEBUG: Inicio de sección de modales -->
    <!-- Modales de Reservas (disponibles en todas las vistas) -->

    <!-- Modal para Crear Reserva Socio -->
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
                            <label class="form-label">Buscar Socio</label>
                            <input type="text" class="form-control mb-2" id="buscarSocio" placeholder="Buscar por nombre, apellidos o teléfono...">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Socio *</label>
                            <select class="form-select" name="id_usuario" required id="selectSocio">
                                <option value="">Seleccione un socio</option>
                                <?php
                                    $stmt = $conexionPDO->prepare("SELECT id, num_socio, nombre, apellido1, apellido2, telf FROM usuarios WHERE rol = 'user' ORDER BY num_socio");
                                    $stmt->execute();
                                    $socios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($socios as $socio): ?>
                                    <option value="<?php echo $socio['id'] ?>" data-nombre="<?php echo strtolower($socio['nombre'] . ' ' . $socio['apellido1'] . ' ' . $socio['apellido2']) ?>" data-telf="<?php echo $socio['telf'] ?>">
                                        <?php echo $socio['nombre'] ?> <?php echo $socio['apellido1'] ?> <?php echo $socio['apellido2'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <input type="hidden" name="id_habitacion" value="1">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Inicio *</label>
                                <input type="date" class="form-control" name="fecha_inicio" required
                                       id="fechaInicioSocio" min="<?php echo date('Y-m-d'); ?>"
                                       onchange="verificarDisponibilidadSocio()">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Fin *</label>
                                <input type="date" class="form-control" name="fecha_fin" required
                                       id="fechaFinSocio" min="<?php echo date('Y-m-d'); ?>"
                                       onchange="verificarDisponibilidadSocio()">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Número de Camas *</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-outline-secondary" onclick="cambiarCamasSocio(-1)" id="btnDecrementarSocio" disabled>
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" class="form-control text-center" name="numero_camas"
                                       id="numeroCamasSocio" value="1" min="1" max="26" required readonly>
                                <button type="button" class="btn btn-outline-secondary" onclick="cambiarCamasSocio(1)" id="btnIncrementarSocio" disabled>
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                            <small class="text-muted" id="infoCamasSocio">Selecciona las fechas para ver disponibilidad</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Actividad a Realizar *</label>
                            <textarea class="form-control" name="actividad" required rows="3" placeholder="Describe la actividad a realizar durante la estancia..."></textarea>
                        </div>

                        <!-- Sección de Acompañantes -->
                        <div id="seccionAcompanantesSocio" style="display: none;">
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">
                                    <i class="bi bi-people-fill"></i> Acompañantes
                                    <span id="badgeAcompanantesSocio" class="badge bg-info">0/0</span>
                                </h6>
                                <button type="button" class="btn btn-sm btn-primary" onclick="agregarAcompananteSocio()" id="btnAgregarAcompananteSocio">
                                    <i class="bi bi-person-plus-fill"></i> Agregar
                                </button>
                            </div>
                            <div id="containerAcompanantesSocio">
                                <p class="text-muted"><i class="bi bi-info-circle"></i> Debes agregar los datos de los acompañantes.</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success" id="btnSubmitReservaSocio">
                            <i class="bi bi-check-circle"></i> Crear Reserva
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Crear Reserva No Socio -->
    <div class="modal fade" id="modalReservaNoSocio" tabindex="-1" aria-labelledby="modalReservaNoSocioLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalReservaNoSocioLabel">
                        <i class="bi bi-person"></i> Crear Reserva para No Socio
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" id="formReservaNoSocio">
                    <input type="hidden" name="accion" value="crear_reserva_no_socio">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Las reservas creadas para no socios se aprueban automáticamente.
                        </div>

                        <!-- Datos Personales -->
                        <h6 class="mb-3"><i class="bi bi-person-badge"></i> Datos Personales</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">DNI *</label>
                                <input type="text" class="form-control" name="dni" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nombre *</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Apellido 1 *</label>
                                <input type="text" class="form-control" name="apellido1" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Apellido 2</label>
                                <input type="text" class="form-control" name="apellido2">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Teléfono *</label>
                                <input type="text" class="form-control" name="telefono" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                        </div>

                        <!-- Grupo de Montañeros -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="perteneceGrupoTenerife" name="pertenece_grupo_tenerife" onchange="toggleGrupoPersonalizado()">
                                <label class="form-check-label" for="perteneceGrupoTenerife">
                                    Pertenece al Grupo de Montañeros de Tenerife
                                </label>
                            </div>
                        </div>
                        <div class="mb-3" id="grupoPersonalizadoContainer" style="display: block;">
                            <label class="form-label">Otro Grupo o Asociación</label>
                            <input type="text" class="form-control" name="grupo_personalizado" id="grupoPersonalizado" placeholder="Nombre del grupo o asociación...">
                            <small class="text-muted">Si no pertenece a ningún grupo, dejar en blanco</small>
                        </div>

                        <hr>

                        <!-- Datos de Reserva -->
                        <h6 class="mb-3"><i class="bi bi-calendar"></i> Datos de Reserva</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Inicio *</label>
                                <input type="date" class="form-control" name="fecha_inicio" required
                                       id="fechaInicioNoSocio" min="<?php echo date('Y-m-d'); ?>"
                                       onchange="verificarDisponibilidadNoSocio()">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Fin *</label>
                                <input type="date" class="form-control" name="fecha_fin" required
                                       id="fechaFinNoSocio" min="<?php echo date('Y-m-d'); ?>"
                                       onchange="verificarDisponibilidadNoSocio()">
                            </div>
                        </div>

                        <input type="hidden" name="id_habitacion" value="1">

                        <div class="mb-3">
                            <label class="form-label">Número de Camas *</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-outline-secondary" onclick="cambiarCamasNoSocio(-1)">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" class="form-control text-center" name="numero_camas"
                                       id="numeroCamasNoSocio" value="1" min="1" required readonly>
                                <button type="button" class="btn btn-outline-secondary" onclick="cambiarCamasNoSocio(1)">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                            <small class="text-muted" id="infoCamasNoSocio">Selecciona las fechas para ver disponibilidad</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Actividad a Realizar *</label>
                            <textarea class="form-control" name="actividad" required rows="3" placeholder="Describe la actividad a realizar durante la estancia..."></textarea>
                        </div>

                        <!-- Sección de Acompañantes -->
                        <div id="seccionAcompanantesNoSocio" style="display: none;">
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">
                                    <i class="bi bi-people-fill"></i> Acompañantes
                                    <span id="badgeAcompanantesNoSocio" class="badge bg-info">0/0</span>
                                </h6>
                                <button type="button" class="btn btn-sm btn-primary" onclick="agregarAcompananteNoSocio()" id="btnAgregarAcompananteNoSocio">
                                    <i class="bi bi-person-plus-fill"></i> Agregar
                                </button>
                            </div>
                            <div id="containerAcompanantesNoSocio">
                                <p class="text-muted"><i class="bi bi-info-circle"></i> Debes agregar los datos de los acompañantes.</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-info" id="btnSubmitReservaNoSocio">
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

                        <div class="alert alert-secondary">
                            <strong>Tipo de Reserva:</strong> Selecciona una opción
                        </div>

                        <!-- Grupo de Montañeros de Tenerife -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="perteneceGrupoTenerifeEspecial" name="pertenece_grupo_tenerife_especial" onchange="toggleTipoReservaEspecial('gmt')">
                                <label class="form-check-label" for="perteneceGrupoTenerifeEspecial">
                                    <strong>Grupo de Montañeros de Tenerife</strong>
                                </label>
                            </div>
                        </div>

                        <!-- Asignar a un socio específico -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="asignarSocioEspecial" onchange="toggleTipoReservaEspecial('socio')">
                                <label class="form-check-label" for="asignarSocioEspecial">
                                    <strong>Asignar a un socio específico</strong>
                                </label>
                            </div>
                        </div>

                        <div id="socioEspecialContainer" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">Buscar Socio</label>
                                <input type="text" class="form-control mb-2" id="buscarSocioEspecial" placeholder="Buscar por nombre, apellidos o teléfono...">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Socio</label>
                                <select class="form-select" name="id_usuario_especial" id="selectSocioEspecial">
                                    <option value="">Seleccione un socio</option>
                                    <?php
                                        $stmt = $conexionPDO->prepare("SELECT id, num_socio, nombre, apellido1, apellido2, telf FROM usuarios WHERE rol = 'user' ORDER BY num_socio");
                                        $stmt->execute();
                                        $socios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($socios as $socio): ?>
                                        <option value="<?php echo $socio['id'] ?>" data-nombre="<?php echo strtolower($socio['nombre'] . ' ' . $socio['apellido1'] . ' ' . $socio['apellido2']) ?>" data-telf="<?php echo $socio['telf'] ?>">
                                            <?php echo $socio['nombre'] ?> <?php echo $socio['apellido1'] ?> <?php echo $socio['apellido2'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Otro grupo o asociación -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="otroGrupoEspecial" onchange="toggleTipoReservaEspecial('grupo')">
                                <label class="form-check-label" for="otroGrupoEspecial">
                                    <strong>Otro grupo o asociación</strong>
                                </label>
                            </div>
                        </div>

                        <div id="grupoPersonalizadoContainerEspecial" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">Nombre del Grupo o Asociación</label>
                                <input type="text" class="form-control" name="grupo_personalizado_especial" id="grupoPersonalizadoEspecial" placeholder="Nombre del grupo o asociación (opcional)">
                                <small class="text-muted">Puedes dejarlo en blanco si no quieres especificar</small>
                            </div>
                        </div>

                        <!-- Asignar a un no socio -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="asignarNoSocioEspecial" onchange="toggleTipoReservaEspecial('nosocio')">
                                <label class="form-check-label" for="asignarNoSocioEspecial">
                                    <strong>Asignar a un no socio</strong>
                                </label>
                            </div>
                        </div>

                        <div id="noSocioEspecialContainer" style="display: none;">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-title">Datos del Responsable (No Socio)</h6>
                                    <div class="row">
                                        <div class="col-md-12 mb-2">
                                            <label class="form-label">Nombre completo *</label>
                                            <input type="text" class="form-control" name="nosocio_nombre" id="noSocioNombre" placeholder="Nombre y apellidos">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">DNI/NIE</label>
                                            <input type="text" class="form-control" name="nosocio_dni" placeholder="12345678A">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">Teléfono</label>
                                            <input type="tel" class="form-control" name="nosocio_telf" placeholder="123456789">
                                        </div>
                                        <div class="col-md-12 mb-2">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="nosocio_email" placeholder="email@ejemplo.com">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Inicio *</label>
                                <input type="date" class="form-control" name="fecha_inicio" required
                                       id="fechaInicioEspecial" min="<?php echo date('Y-m-d'); ?>"
                                       onchange="verificarDisponibilidadEspecial()">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Fin *</label>
                                <input type="date" class="form-control" name="fecha_fin" required
                                       id="fechaFinEspecial" min="<?php echo date('Y-m-d'); ?>"
                                       onchange="verificarDisponibilidadEspecial()">
                            </div>
                        </div>

                        <input type="hidden" name="id_habitacion" value="1">

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
                            <small class="text-muted" id="infoCamasEspecial">Selecciona las fechas para ver disponibilidad</small>
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
        let maxCamasEspecial = 26; // Capacidad de habitación 1
        let camasDisponiblesEspecial = 26; // Se actualizará con AJAX

        // Función para verificar disponibilidad de camas para Reserva Especial
        function verificarDisponibilidadEspecial() {
            const fechaInicio = document.getElementById('fechaInicioEspecial').value;
            const fechaFin = document.getElementById('fechaFinEspecial').value;

            if (!fechaInicio || !fechaFin) {
                return;
            }

            fetch(`?ajax=verificar_disponibilidad&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        camasDisponiblesEspecial = data.disponibles;
                        maxCamasEspecial = data.disponibles;
                        actualizarInfoCamasEspecial();

                        // Ajustar valor actual si excede disponibilidad
                        const inputCamas = document.getElementById('numeroCamasEspecial');
                        if (parseInt(inputCamas.value) > maxCamasEspecial) {
                            inputCamas.value = Math.max(1, maxCamasEspecial);
                        }
                    }
                })
                .catch(error => console.error('Error al verificar disponibilidad:', error));
        }

        function actualizarInfoCamasEspecial() {
            const inputCamas = document.getElementById('numeroCamasEspecial');
            const infoCamas = document.getElementById('infoCamasEspecial');
            const numCamasSeleccionadas = parseInt(inputCamas.value) || 0;
            const restantes = camasDisponiblesEspecial - numCamasSeleccionadas;

            if (camasDisponiblesEspecial === 0) {
                infoCamas.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> No hay camas disponibles en estas fechas</span>';
            } else if (restantes < 0) {
                infoCamas.innerHTML = `<span class="text-danger">Solo hay ${camasDisponiblesEspecial} camas disponibles</span>`;
            } else {
                infoCamas.innerHTML = `<span class="text-success"><i class="bi bi-check-circle"></i> ${restantes} cama(s) disponible(s) restante(s)</span>`;
            }
        }
        let esTodoElRefugio = false;

        // ============================================================================
        // NOTA: Event listeners de selectores de habitación comentados porque
        // ahora se usa habitación 1 por defecto (oculta en la UI)
        // ============================================================================

        /*
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
        */

        function cambiarCamasEspecial(cambio) {
            const input = document.getElementById('numeroCamasEspecial');

            let nuevoValor = parseInt(input.value) + cambio;

            if (nuevoValor < 1) {
                nuevoValor = 1;
            } else if (nuevoValor > maxCamasEspecial) {
                nuevoValor = maxCamasEspecial;
            }

            input.value = nuevoValor;
            actualizarInfoCamasEspecial(); // Actualizar contador de disponibles
        }

        // Validar que fecha fin sea igual o posterior a fecha inicio
        // NOTA: Esta validación ya se maneja en los campos con onchange inline
        /*
        document.getElementById('fechaFinEspecial').addEventListener('change', function() {
            const fechaInicio = document.getElementById('fechaInicioEspecial').value;
            const fechaFin = this.value;

            if (fechaInicio && fechaFin && fechaFin < fechaInicio) {
                alert('La fecha de fin debe ser igual o posterior a la fecha de inicio');
                this.value = '';
            }
        });
        */

        // Control de número de camas para reserva de socio
        let maxCamasSocio = 26; // Capacidad de la habitación 1
        let camasDisponiblesSocio = 26; // Se actualizará con AJAX

        // Función para verificar disponibilidad de camas vía AJAX
        function verificarDisponibilidadSocio() {
            const fechaInicio = document.getElementById('fechaInicioSocio').value;
            const fechaFin = document.getElementById('fechaFinSocio').value;

            if (!fechaInicio || !fechaFin) {
                return;
            }

            fetch(`?ajax=verificar_disponibilidad&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        camasDisponiblesSocio = data.disponibles;
                        maxCamasSocio = data.disponibles;
                        actualizarInfoCamasSocio();

                        // Habilitar botones de incremento/decremento
                        document.getElementById('btnDecrementarSocio').disabled = false;
                        document.getElementById('btnIncrementarSocio').disabled = false;

                        // Ajustar valor actual si excede disponibilidad
                        const inputCamas = document.getElementById('numeroCamasSocio');
                        if (parseInt(inputCamas.value) > maxCamasSocio) {
                            inputCamas.value = Math.max(1, maxCamasSocio);
                        }
                    }
                })
                .catch(error => console.error('Error al verificar disponibilidad:', error));
        }

        function actualizarInfoCamasSocio() {
            const inputCamas = document.getElementById('numeroCamasSocio');
            const infoCamas = document.getElementById('infoCamasSocio');
            const numCamasSeleccionadas = parseInt(inputCamas.value) || 0;
            const restantes = camasDisponiblesSocio - numCamasSeleccionadas;

            if (camasDisponiblesSocio === 0) {
                infoCamas.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> No hay camas disponibles en estas fechas</span>';
            } else if (restantes < 0) {
                infoCamas.innerHTML = `<span class="text-danger">Solo hay ${camasDisponiblesSocio} camas disponibles</span>`;
            } else {
                infoCamas.innerHTML = `<span class="text-success"><i class="bi bi-check-circle"></i> ${restantes} cama(s) disponible(s) restante(s)</span>`;
            }
        }

        /*
        document.getElementById('selectHabitacionSocio').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const inputCamas = document.getElementById('numeroCamasSocio');
            const infoCamas = document.getElementById('infoCamasSocio');

            maxCamasSocio = parseInt(selectedOption.dataset.maxCamas) || 1;
            inputCamas.max = maxCamasSocio;
            inputCamas.value = 1;
            infoCamas.textContent = `Máximo ${maxCamasSocio} camas disponibles en esta habitación`;

            // Actualizar sección de acompañantes
            actualizarSeccionAcompanantesSocio(1);
        });
        */

        let contadorAcompanantesSocio = 0;
        let acompanantesActualesSocio = 0;

        function cambiarCamasSocio(cambio) {
            const input = document.getElementById('numeroCamasSocio');

            let nuevoValor = parseInt(input.value) + cambio;

            if (nuevoValor < 1) {
                nuevoValor = 1;
            } else if (nuevoValor > maxCamasSocio) {
                nuevoValor = maxCamasSocio;
            }

            input.value = nuevoValor;
            actualizarInfoCamasSocio(); // Actualizar contador de disponibles
            actualizarSeccionAcompanantesSocio(nuevoValor);
        }

        function actualizarSeccionAcompanantesSocio(numeroCamas) {
            const seccion = document.getElementById('seccionAcompanantesSocio');
            const container = document.getElementById('containerAcompanantesSocio');
            const badge = document.getElementById('badgeAcompanantesSocio');
            const btnAgregar = document.getElementById('btnAgregarAcompananteSocio');

            const acompanantesRequeridos = numeroCamas - 1;

            if (numeroCamas === 1) {
                // Ocultar sección
                seccion.style.display = 'none';
                container.innerHTML = '';
                acompanantesActualesSocio = 0;
            } else {
                // Mostrar sección
                seccion.style.display = 'block';
                badge.textContent = `${acompanantesActualesSocio}/${acompanantesRequeridos}`;

                // Limpiar y reiniciar
                container.innerHTML = '<p class="text-muted"><i class="bi bi-info-circle"></i> Debes agregar ' + acompanantesRequeridos + ' acompañante(s).</p>';
                acompanantesActualesSocio = 0;
                btnAgregar.disabled = false;
            }
        }

        function agregarAcompananteSocio() {
            const numeroCamas = parseInt(document.getElementById('numeroCamasSocio').value) || 0;
            const acompanantesRequeridos = numeroCamas - 1;

            if (acompanantesActualesSocio >= acompanantesRequeridos) {
                alert(`Solo puedes agregar ${acompanantesRequeridos} acompañante(s) para ${numeroCamas} cama(s).`);
                return;
            }

            contadorAcompanantesSocio++;
            acompanantesActualesSocio++;

            const container = document.getElementById('containerAcompanantesSocio');
            const badge = document.getElementById('badgeAcompanantesSocio');
            const btnAgregar = document.getElementById('btnAgregarAcompananteSocio');

            badge.textContent = `${acompanantesActualesSocio}/${acompanantesRequeridos}`;

            if (acompanantesActualesSocio >= acompanantesRequeridos) {
                btnAgregar.disabled = true;
            }

            const html = `
                <div class="border rounded p-3 mb-3" id="acompanante-socio-${contadorAcompanantesSocio}">
                    <div class="d-flex justify-content-between mb-2">
                        <strong><i class="bi bi-person"></i> Acompañante #${acompanantesActualesSocio}</strong>
                        <button type="button" class="btn btn-sm btn-danger" onclick="eliminarAcompananteSocio(${contadorAcompanantesSocio})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">DNI *</label>
                            <input type="text" name="acompanantes[${contadorAcompanantesSocio}][dni]" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="acompanantes[${contadorAcompanantesSocio}][nombre]" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Apellido 1 *</label>
                            <input type="text" name="acompanantes[${contadorAcompanantesSocio}][apellido1]" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellido 2</label>
                            <input type="text" name="acompanantes[${contadorAcompanantesSocio}][apellido2]" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">¿Es socio?</label>
                            <select name="acompanantes[${contadorAcompanantesSocio}][es_socio]" class="form-select form-select-sm" onchange="toggleNumSocioAdmin(${contadorAcompanantesSocio})">
                                <option value="no">No</option>
                                <option value="si">Sí</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="numSocioDiv-${contadorAcompanantesSocio}" style="display:none">
                            <label class="form-label">Nº Socio</label>
                            <input type="text" name="acompanantes[${contadorAcompanantesSocio}][num_socio]" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', html);
        }

        function eliminarAcompananteSocio(id) {
            document.getElementById(`acompanante-socio-${id}`).remove();
            acompanantesActualesSocio--;

            const numeroCamas = parseInt(document.getElementById('numeroCamasSocio').value) || 0;
            const acompanantesRequeridos = numeroCamas - 1;
            const badge = document.getElementById('badgeAcompanantesSocio');
            const btnAgregar = document.getElementById('btnAgregarAcompananteSocio');

            badge.textContent = `${acompanantesActualesSocio}/${acompanantesRequeridos}`;

            if (acompanantesActualesSocio < acompanantesRequeridos) {
                btnAgregar.disabled = false;
            }
        }

        function toggleNumSocioAdmin(id) {
            const select = document.querySelector(`select[name="acompanantes[${id}][es_socio]"]`);
            const div = document.getElementById(`numSocioDiv-${id}`);
            div.style.display = select.value === 'si' ? 'block' : 'none';
        }

        // Funcionalidad de búsqueda de socios
        document.getElementById('buscarSocio').addEventListener('input', function() {
            const searchText = this.value.toLowerCase().trim();
            const select = document.getElementById('selectSocio');
            const options = select.querySelectorAll('option');
            let visibleOptions = [];
            let exactMatch = null;

            options.forEach(option => {
                if (option.value === '') {
                    option.style.display = 'block';
                    return;
                }

                const nombre = option.dataset.nombre || '';
                const telf = option.dataset.telf || '';

                if (nombre.includes(searchText) || telf.includes(searchText)) {
                    option.style.display = 'block';
                    visibleOptions.push(option);

                    // Verificar coincidencia exacta
                    if (nombre === searchText || telf === searchText) {
                        exactMatch = option;
                    }
                } else {
                    option.style.display = 'none';
                }
            });

            // Autoseleccionar si hay coincidencia exacta o solo una opción visible
            if (searchText && exactMatch) {
                select.value = exactMatch.value;
            } else if (searchText && visibleOptions.length === 1) {
                select.value = visibleOptions[0].value;
            } else if (!searchText) {
                select.value = '';
            }
        });

        // Validación obligatoria de acompañantes en reserva de socio
        document.getElementById('formReservaSocio').addEventListener('submit', function(e) {
            const numeroCamas = parseInt(document.getElementById('numeroCamasSocio').value) || 1;
            const acompanantesRequeridos = numeroCamas - 1;

            if (numeroCamas > 1 && acompanantesActualesSocio < acompanantesRequeridos) {
                e.preventDefault();
                alert(`Debes agregar ${acompanantesRequeridos} acompañante(s) para completar la reserva de ${numeroCamas} camas.\n\nAcompañantes agregados: ${acompanantesActualesSocio}\nAcompañantes requeridos: ${acompanantesRequeridos}`);
                return false;
            }
        });

        // Validar fechas para reserva de socio
        document.getElementById('fechaFinSocio').addEventListener('change', function() {
            const fechaInicio = document.getElementById('fechaInicioSocio').value;
            const fechaFin = this.value;

            if (fechaInicio && fechaFin && fechaFin < fechaInicio) {
                alert('La fecha de fin debe ser igual o posterior a la fecha de inicio');
                this.value = '';
            }
        });

        // ========== FUNCIONES PARA MODAL DE RESERVA NO SOCIO ==========
        let maxCamasNoSocio = 26; // Capacidad de habitación 1
        let camasDisponiblesNoSocio = 26; // Se actualizará con AJAX

        // Función para verificar disponibilidad de camas para No Socio
        function verificarDisponibilidadNoSocio() {
            const fechaInicio = document.getElementById('fechaInicioNoSocio').value;
            const fechaFin = document.getElementById('fechaFinNoSocio').value;

            if (!fechaInicio || !fechaFin) {
                return;
            }

            fetch(`?ajax=verificar_disponibilidad&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        camasDisponiblesNoSocio = data.disponibles;
                        maxCamasNoSocio = data.disponibles;
                        actualizarInfoCamasNoSocio();

                        // Ajustar valor actual si excede disponibilidad
                        const inputCamas = document.getElementById('numeroCamasNoSocio');
                        if (parseInt(inputCamas.value) > maxCamasNoSocio) {
                            inputCamas.value = Math.max(1, maxCamasNoSocio);
                        }
                    }
                })
                .catch(error => console.error('Error al verificar disponibilidad:', error));
        }

        function actualizarInfoCamasNoSocio() {
            const inputCamas = document.getElementById('numeroCamasNoSocio');
            const infoCamas = document.getElementById('infoCamasNoSocio');
            const numCamasSeleccionadas = parseInt(inputCamas.value) || 0;
            const restantes = camasDisponiblesNoSocio - numCamasSeleccionadas;

            if (camasDisponiblesNoSocio === 0) {
                infoCamas.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> No hay camas disponibles en estas fechas</span>';
            } else if (restantes < 0) {
                infoCamas.innerHTML = `<span class="text-danger">Solo hay ${camasDisponiblesNoSocio} camas disponibles</span>`;
            } else {
                infoCamas.innerHTML = `<span class="text-success"><i class="bi bi-check-circle"></i> ${restantes} cama(s) disponible(s) restante(s)</span>`;
            }
        }
        let acompanantesActualesNoSocio = 0;
        let contadorAcompanantesNoSocio = 0;

        // Toggle grupo personalizado
        function toggleGrupoPersonalizado() {
            const checkbox = document.getElementById('perteneceGrupoTenerife');
            const container = document.getElementById('grupoPersonalizadoContainer');
            const input = document.getElementById('grupoPersonalizado');

            if (checkbox.checked) {
                container.style.display = 'none';
                input.value = '';
            } else {
                container.style.display = 'block';
            }
        }

        // Toggle grupo personalizado para Reserva Especial
        // Función unificada para manejar los tipos de reserva especial
        function toggleTipoReservaEspecial(tipo) {
            const gmtCheck = document.getElementById('perteneceGrupoTenerifeEspecial');
            const socioCheck = document.getElementById('asignarSocioEspecial');
            const grupoCheck = document.getElementById('otroGrupoEspecial');
            const noSocioCheck = document.getElementById('asignarNoSocioEspecial');

            const socioContainer = document.getElementById('socioEspecialContainer');
            const grupoContainer = document.getElementById('grupoPersonalizadoContainerEspecial');
            const noSocioContainer = document.getElementById('noSocioEspecialContainer');

            // Desmarcar todos los demás checkboxes excepto el actual
            if (tipo === 'gmt' && gmtCheck.checked) {
                socioCheck.checked = false;
                grupoCheck.checked = false;
                noSocioCheck.checked = false;
            } else if (tipo === 'socio' && socioCheck.checked) {
                gmtCheck.checked = false;
                grupoCheck.checked = false;
                noSocioCheck.checked = false;
            } else if (tipo === 'grupo' && grupoCheck.checked) {
                gmtCheck.checked = false;
                socioCheck.checked = false;
                noSocioCheck.checked = false;
            } else if (tipo === 'nosocio' && noSocioCheck.checked) {
                gmtCheck.checked = false;
                socioCheck.checked = false;
                grupoCheck.checked = false;
            }

            // Mostrar/ocultar contenedores según el checkbox activo
            socioContainer.style.display = socioCheck.checked ? 'block' : 'none';
            grupoContainer.style.display = grupoCheck.checked ? 'block' : 'none';
            noSocioContainer.style.display = noSocioCheck.checked ? 'block' : 'none';

            // Limpiar campos cuando se ocultan
            if (!socioCheck.checked) {
                document.getElementById('selectSocioEspecial').value = '';
            }
            if (!grupoCheck.checked) {
                document.getElementById('grupoPersonalizadoEspecial').value = '';
            }
            if (!noSocioCheck.checked) {
                document.getElementById('noSocioNombre').value = '';
                const noSocioFields = noSocioContainer.querySelectorAll('input');
                noSocioFields.forEach(field => field.value = '');
            }
        }

        // Funcionalidad de búsqueda de socios para Reserva Especial
        document.getElementById('buscarSocioEspecial').addEventListener('input', function() {
            const searchText = this.value.toLowerCase().trim();
            const select = document.getElementById('selectSocioEspecial');
            const options = select.querySelectorAll('option');
            let visibleOptions = [];
            let exactMatch = null;

            options.forEach(option => {
                if (option.value === '') {
                    option.style.display = 'block';
                    return;
                }

                const nombre = option.dataset.nombre || '';
                const telf = option.dataset.telf || '';

                if (nombre.includes(searchText) || telf.includes(searchText)) {
                    option.style.display = 'block';
                    visibleOptions.push(option);

                    // Verificar coincidencia exacta
                    if (nombre === searchText || telf === searchText) {
                        exactMatch = option;
                    }
                } else {
                    option.style.display = 'none';
                }
            });

            // Autoseleccionar si hay coincidencia exacta o solo una opción visible
            if (searchText && exactMatch) {
                select.value = exactMatch.value;
            } else if (searchText && visibleOptions.length === 1) {
                select.value = visibleOptions[0].value;
            } else if (!searchText) {
                select.value = '';
            }
        });

        // Event listener para cambio de habitación No Socio - COMENTADO (ahora usa habitación 1 por defecto)
        /*
        document.getElementById('selectHabitacionNoSocio').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                maxCamasNoSocio = parseInt(selectedOption.dataset.maxCamas);
                document.getElementById('numeroCamasNoSocio').max = maxCamasNoSocio;
                document.getElementById('numeroCamasNoSocio').value = 1;
                document.getElementById('infoCamasNoSocio').textContent = `Máximo ${maxCamasNoSocio} camas disponibles`;
                actualizarSeccionAcompanantesNoSocio(1);
            }
        });
        */

        function cambiarCamasNoSocio(cambio) {
            const input = document.getElementById('numeroCamasNoSocio');
            let nuevoValor = parseInt(input.value) + cambio;

            if (nuevoValor < 1) {
                nuevoValor = 1;
            } else if (nuevoValor > maxCamasNoSocio) {
                nuevoValor = maxCamasNoSocio;
            }

            input.value = nuevoValor;
            actualizarInfoCamasNoSocio(); // Actualizar contador de disponibles
            actualizarSeccionAcompanantesNoSocio(nuevoValor);
        }

        function actualizarSeccionAcompanantesNoSocio(numeroCamas) {
            const seccion = document.getElementById('seccionAcompanantesNoSocio');
            const container = document.getElementById('containerAcompanantesNoSocio');
            const badge = document.getElementById('badgeAcompanantesNoSocio');
            const btnAgregar = document.getElementById('btnAgregarAcompananteNoSocio');

            const acompanantesRequeridos = numeroCamas - 1;

            if (numeroCamas === 1) {
                seccion.style.display = 'none';
                container.innerHTML = '';
                acompanantesActualesNoSocio = 0;
            } else {
                seccion.style.display = 'block';
                badge.textContent = `${acompanantesActualesNoSocio}/${acompanantesRequeridos}`;
                container.innerHTML = '<p class="text-muted"><i class="bi bi-info-circle"></i> Debes agregar ' + acompanantesRequeridos + ' acompañante(s).</p>';
                acompanantesActualesNoSocio = 0;
                btnAgregar.disabled = false;
            }
        }

        function agregarAcompananteNoSocio() {
            const numeroCamas = parseInt(document.getElementById('numeroCamasNoSocio').value) || 1;
            const acompanantesRequeridos = numeroCamas - 1;

            if (acompanantesActualesNoSocio >= acompanantesRequeridos) {
                alert('Ya has agregado todos los acompañantes necesarios');
                return;
            }

            contadorAcompanantesNoSocio++;
            acompanantesActualesNoSocio++;

            const container = document.getElementById('containerAcompanantesNoSocio');
            const badge = document.getElementById('badgeAcompanantesNoSocio');
            const btnAgregar = document.getElementById('btnAgregarAcompananteNoSocio');

            badge.textContent = `${acompanantesActualesNoSocio}/${acompanantesRequeridos}`;

            if (acompanantesActualesNoSocio >= acompanantesRequeridos) {
                btnAgregar.disabled = true;
            }

            const html = `
                <div class="border rounded p-3 mb-3" id="acompanante-no-socio-${contadorAcompanantesNoSocio}">
                    <div class="d-flex justify-content-between mb-2">
                        <strong><i class="bi bi-person"></i> Acompañante #${acompanantesActualesNoSocio}</strong>
                        <button type="button" class="btn btn-sm btn-danger" onclick="eliminarAcompananteNoSocio(${contadorAcompanantesNoSocio})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">DNI *</label>
                            <input type="text" name="acompanantes[${contadorAcompanantesNoSocio}][dni]" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="acompanantes[${contadorAcompanantesNoSocio}][nombre]" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Apellido 1 *</label>
                            <input type="text" name="acompanantes[${contadorAcompanantesNoSocio}][apellido1]" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellido 2</label>
                            <input type="text" name="acompanantes[${contadorAcompanantesNoSocio}][apellido2]" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
            `;

            if (acompanantesActualesNoSocio === 1) {
                container.innerHTML = html;
            } else {
                container.insertAdjacentHTML('beforeend', html);
            }
        }

        function eliminarAcompananteNoSocio(id) {
            const elemento = document.getElementById(`acompanante-no-socio-${id}`);
            if (elemento) {
                elemento.remove();
                acompanantesActualesNoSocio--;

                const numeroCamas = parseInt(document.getElementById('numeroCamasNoSocio').value) || 1;
                const acompanantesRequeridos = numeroCamas - 1;
                const badge = document.getElementById('badgeAcompanantesNoSocio');
                const btnAgregar = document.getElementById('btnAgregarAcompananteNoSocio');

                badge.textContent = `${acompanantesActualesNoSocio}/${acompanantesRequeridos}`;
                btnAgregar.disabled = false;

                const container = document.getElementById('containerAcompanantesNoSocio');
                if (acompanantesActualesNoSocio === 0) {
                    container.innerHTML = '<p class="text-muted"><i class="bi bi-info-circle"></i> Debes agregar ' + acompanantesRequeridos + ' acompañante(s).</p>';
                }
            }
        }

        // Validación obligatoria de acompañantes para No Socio
        document.getElementById('formReservaNoSocio').addEventListener('submit', function(e) {
            const numeroCamas = parseInt(document.getElementById('numeroCamasNoSocio').value) || 1;
            const acompanantesRequeridos = numeroCamas - 1;

            if (numeroCamas > 1 && acompanantesActualesNoSocio < acompanantesRequeridos) {
                e.preventDefault();
                alert(`Debes agregar ${acompanantesRequeridos} acompañante(s) para completar la reserva de ${numeroCamas} camas.\n\nAcompañantes agregados: ${acompanantesActualesNoSocio}\nAcompañantes requeridos: ${acompanantesRequeridos}`);
                return false;
            }
        });

        // Validar fechas para No Socio
        document.getElementById('fechaFinNoSocio').addEventListener('change', function() {
            const fechaInicio = document.getElementById('fechaInicioNoSocio').value;
            const fechaFin = this.value;

            if (fechaInicio && fechaFin && fechaFin < fechaInicio) {
                alert('La fecha de fin debe ser igual o posterior a la fecha de inicio');
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

                        <!-- Teléfono y Email -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="editTelefono" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" id="editEmail" readonly>
                            </div>
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

                        <!-- Habitación (oculto - siempre habitación 1) -->
                        <input type="hidden" name="id_habitacion" id="editHabitacion" value="1">

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

                        <!-- Actividad a Realizar -->
                        <div class="mb-3" id="editActividadContainer">
                            <label class="form-label">Actividad a Realizar *</label>
                            <textarea class="form-control" name="actividad" id="editActividad" rows="3" required placeholder="Describe la actividad a realizar durante la estancia..."></textarea>
                        </div>

                        <!-- Sección de Acompañantes -->
                        <div id="seccionAcompanantesEditar" style="display: none;">
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">
                                    <i class="bi bi-people-fill"></i> Acompañantes
                                    <span id="badgeAcompanantesEditar" class="badge bg-info">0/0</span>
                                </h6>
                                <button type="button" class="btn btn-sm btn-primary" onclick="agregarAcompananteEditar()" id="btnAgregarAcompananteEditar">
                                    <i class="bi bi-person-plus-fill"></i> Agregar
                                </button>
                            </div>
                            <div id="containerAcompanantesEditar">
                                <p class="text-muted"><i class="bi bi-info-circle"></i> Debes agregar los datos de los acompañantes.</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitEditarReserva">
                            <i class="bi bi-check-circle"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Variables para el modal de editar
        let maxCamasEditar = 26; // Capacidad de habitación 1
        let esTodoElRefugioEditar = false;
        let acompanantesActualesEditar = 0;
        let contadorAcompanantesEditar = 0;

        function editarReserva(reserva) {
            // Abrir modal
            const modal = new bootstrap.Modal(document.getElementById('modalEditarReserva'));

            // Reiniciar acompañantes
            acompanantesActualesEditar = 0;
            contadorAcompanantesEditar = 0;
            document.getElementById('containerAcompanantesEditar').innerHTML = '<p class="text-muted"><i class="bi bi-info-circle"></i> Debes agregar los datos de los acompañantes.</p>';

            // Rellenar datos
            document.getElementById('editIdReserva').value = reserva.id;
            document.getElementById('editFechaInicio').value = reserva.fecha_inicio;
            document.getElementById('editFechaFin').value = reserva.fecha_fin;
            document.getElementById('editNumeroCamas').value = reserva.numero_camas;
            document.getElementById('editActividad').value = reserva.observaciones || '';

            // Actualizar sección de acompañantes según número de camas
            actualizarSeccionAcompanantesEditar(parseInt(reserva.numero_camas) || 1);

            // Usuario/Motivo (detectar si es especial)
            const esEspecial = !reserva.nombre;
            const esTodoRefugio = esEspecial && !reserva.id_habitacion;
            esTodoElRefugioEditar = esTodoRefugio;

            if (esEspecial) {
                document.getElementById('editUsuario').value = '🎫 RESERVA ESPECIAL: ' + (reserva.observaciones || 'Sin motivo');
                // Para reservas especiales, intentar extraer datos de no socio
                const observaciones = reserva.observaciones || '';
                if (observaciones.includes('Tel:')) {
                    const telMatch = observaciones.match(/Tel:([^|]+)/);
                    if (telMatch) {
                        document.getElementById('editTelefono').value = telMatch[1].trim();
                    } else {
                        document.getElementById('editTelefono').value = '-';
                    }
                } else {
                    document.getElementById('editTelefono').value = '-';
                }

                if (observaciones.includes('Email:')) {
                    const emailMatch = observaciones.match(/Email:([^|]+)/);
                    if (emailMatch) {
                        document.getElementById('editEmail').value = emailMatch[1].trim();
                    } else {
                        document.getElementById('editEmail').value = '-';
                    }
                } else {
                    document.getElementById('editEmail').value = '-';
                }
            } else {
                document.getElementById('editUsuario').value = reserva.nombre + ' ' + reserva.apellido1 + ' (' + reserva.num_socio + ')';
                document.getElementById('editTelefono').value = reserva.telf || '-';
                document.getElementById('editEmail').value = reserva.email || '-';
            }

            // Habitación - simplificado, ya no hay opción "TODO EL REFUGIO"
            const camasContainer = document.getElementById('editCamasContainer');
            const editHabitacion = document.getElementById('editHabitacion');

            // Siempre mostrar contenedor de camas (habitación 1 por defecto)
            camasContainer.style.display = 'block';
            document.getElementById('editNumeroCamas').setAttribute('required', 'required');

            if (reserva.id_habitacion) {
                editHabitacion.value = reserva.id_habitacion;
                // Capacidad fija de 26 camas para habitación 1
                maxCamasEditar = 26;
                document.getElementById('editInfoCamas').textContent = `Máximo ${maxCamasEditar} camas disponibles`;
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

            // Actualizar sección de acompañantes
            actualizarSeccionAcompanantesEditar(nuevoValor);
        }

        // Actualizar max camas cuando cambie habitación en edición - COMENTADO (habitación 1 siempre)
        /*
        document.getElementById('editHabitacion').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            maxCamasEditar = parseInt(selectedOption.dataset.maxCamas) || 1;
            document.getElementById('editNumeroCamas').max = maxCamasEditar;
            const numCamasActual = Math.min(document.getElementById('editNumeroCamas').value, maxCamasEditar);
            document.getElementById('editNumeroCamas').value = numCamasActual;
            document.getElementById('editInfoCamas').textContent = `Máximo ${maxCamasEditar} camas disponibles`;

            // Actualizar sección de acompañantes
            actualizarSeccionAcompanantesEditar(numCamasActual);
        });
        */

        // Funciones para gestionar acompañantes en modal de edición
        function actualizarSeccionAcompanantesEditar(numeroCamas) {
            const seccion = document.getElementById('seccionAcompanantesEditar');
            const container = document.getElementById('containerAcompanantesEditar');
            const badge = document.getElementById('badgeAcompanantesEditar');
            const btnAgregar = document.getElementById('btnAgregarAcompananteEditar');

            const acompanantesRequeridos = numeroCamas - 1;

            if (numeroCamas === 1) {
                // Ocultar sección
                seccion.style.display = 'none';
                container.innerHTML = '';
                acompanantesActualesEditar = 0;
            } else {
                // Mostrar sección
                seccion.style.display = 'block';
                badge.textContent = `${acompanantesActualesEditar}/${acompanantesRequeridos}`;

                // Limpiar y reiniciar
                container.innerHTML = '<p class="text-muted"><i class="bi bi-info-circle"></i> Debes agregar ' + acompanantesRequeridos + ' acompañante(s).</p>';
                acompanantesActualesEditar = 0;
                btnAgregar.disabled = false;
            }
        }

        function agregarAcompananteEditar() {
            const numeroCamas = parseInt(document.getElementById('editNumeroCamas').value) || 1;
            const acompanantesRequeridos = numeroCamas - 1;

            if (acompanantesActualesEditar >= acompanantesRequeridos) {
                alert('Ya has agregado todos los acompañantes necesarios');
                return;
            }

            contadorAcompanantesEditar++;
            acompanantesActualesEditar++;

            const container = document.getElementById('containerAcompanantesEditar');
            const badge = document.getElementById('badgeAcompanantesEditar');
            const btnAgregar = document.getElementById('btnAgregarAcompananteEditar');

            badge.textContent = `${acompanantesActualesEditar}/${acompanantesRequeridos}`;

            if (acompanantesActualesEditar >= acompanantesRequeridos) {
                btnAgregar.disabled = true;
            }

            const html = `
                <div class="border rounded p-3 mb-3" id="acompanante-editar-${contadorAcompanantesEditar}">
                    <div class="d-flex justify-content-between mb-2">
                        <strong><i class="bi bi-person"></i> Acompañante #${acompanantesActualesEditar}</strong>
                        <button type="button" class="btn btn-sm btn-danger" onclick="eliminarAcompananteEditar(${contadorAcompanantesEditar})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">DNI *</label>
                            <input type="text" name="acompanantes[${contadorAcompanantesEditar}][dni]" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="acompanantes[${contadorAcompanantesEditar}][nombre]" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Apellido 1 *</label>
                            <input type="text" name="acompanantes[${contadorAcompanantesEditar}][apellido1]" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellido 2</label>
                            <input type="text" name="acompanantes[${contadorAcompanantesEditar}][apellido2]" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="esSocioEditar${contadorAcompanantesEditar}"
                                       name="acompanantes[${contadorAcompanantesEditar}][es_socio]"
                                       onchange="toggleNumSocioEditar(${contadorAcompanantesEditar})">
                                <label class="form-check-label" for="esSocioEditar${contadorAcompanantesEditar}">
                                    Es socio
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6" id="numSocioContainerEditar${contadorAcompanantesEditar}" style="display: none;">
                            <label class="form-label">Nº Socio</label>
                            <input type="text" name="acompanantes[${contadorAcompanantesEditar}][num_socio]" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
            `;

            // Si es el primer acompañante, limpiar el mensaje
            if (acompanantesActualesEditar === 1) {
                container.innerHTML = html;
            } else {
                container.insertAdjacentHTML('beforeend', html);
            }
        }

        function eliminarAcompananteEditar(id) {
            const elemento = document.getElementById(`acompanante-editar-${id}`);
            if (elemento) {
                elemento.remove();
                acompanantesActualesEditar--;

                const numeroCamas = parseInt(document.getElementById('editNumeroCamas').value) || 1;
                const acompanantesRequeridos = numeroCamas - 1;
                const badge = document.getElementById('badgeAcompanantesEditar');
                const btnAgregar = document.getElementById('btnAgregarAcompananteEditar');

                badge.textContent = `${acompanantesActualesEditar}/${acompanantesRequeridos}`;
                btnAgregar.disabled = false;

                // Si no quedan acompañantes, mostrar mensaje
                const container = document.getElementById('containerAcompanantesEditar');
                if (acompanantesActualesEditar === 0) {
                    container.innerHTML = '<p class="text-muted"><i class="bi bi-info-circle"></i> Debes agregar ' + acompanantesRequeridos + ' acompañante(s).</p>';
                }
            }
        }

        function toggleNumSocioEditar(id) {
            const checkbox = document.getElementById(`esSocioEditar${id}`);
            const container = document.getElementById(`numSocioContainerEditar${id}`);
            container.style.display = checkbox.checked ? 'block' : 'none';
        }

        // Validación obligatoria de acompañantes en edición
        document.getElementById('formEditarReserva').addEventListener('submit', function(e) {
            const numeroCamas = parseInt(document.getElementById('editNumeroCamas').value) || 1;
            const acompanantesRequeridos = numeroCamas - 1;

            if (numeroCamas > 1 && acompanantesActualesEditar < acompanantesRequeridos) {
                e.preventDefault();
                alert(`Debes agregar ${acompanantesRequeridos} acompañante(s) para completar la reserva de ${numeroCamas} camas.\n\nAcompañantes agregados: ${acompanantesActualesEditar}\nAcompañantes requeridos: ${acompanantesRequeridos}`);
                return false;
            }
        });

        // Validar fechas en edición
        document.getElementById('editFechaFin').addEventListener('change', function() {
            const fechaInicio = document.getElementById('editFechaInicio').value;
            const fechaFin = this.value;

            if (fechaInicio && fechaFin && fechaFin < fechaInicio) {
                alert('La fecha de fin debe ser igual o posterior a la fecha de inicio');
                this.value = '';
            }
        });

        // Funciones para manejar selección de reservas canceladas
        function toggleSelectAll() {
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            const checkboxes = document.querySelectorAll('.reserva-checkbox');
            const isChecked = selectAllCheckbox ? selectAllCheckbox.checked : false;

            checkboxes.forEach(checkbox => {
                checkbox.checked = !checkbox.checked;
            });

            actualizarContador();
        }

        function actualizarContador() {
            const checkboxes = document.querySelectorAll('.reserva-checkbox:checked');
            const contador = checkboxes.length;
            const contadorElement = document.getElementById('contadorSeleccionadas');
            const btnEliminar = document.getElementById('btnEliminarSeleccionadas');

            if (contadorElement) {
                contadorElement.textContent = `${contador} seleccionada${contador !== 1 ? 's' : ''}`;
            }

            if (btnEliminar) {
                btnEliminar.disabled = contador === 0;
            }
        }

        function eliminarSeleccionadas() {
            const checkboxes = document.querySelectorAll('.reserva-checkbox:checked');
            const contador = checkboxes.length;

            if (contador === 0) {
                alert('Por favor, selecciona al menos una reserva para eliminar');
                return;
            }

            if (confirm(`¿Estás seguro de que deseas eliminar ${contador} reserva${contador !== 1 ? 's' : ''} cancelada${contador !== 1 ? 's' : ''}?\n\nEsta acción no se puede deshacer.`)) {
                document.getElementById('formEliminarCanceladas').submit();
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Debug: Verificar que los modales existen en el DOM
        document.addEventListener('DOMContentLoaded', function() {
            const modalSocio = document.getElementById('modalReservaSocio');
            const modalNoSocio = document.getElementById('modalReservaNoSocio');
            const modalEspecial = document.getElementById('modalReservaEspecial');

            console.log('Modal Socio existe:', modalSocio !== null);
            console.log('Modal No Socio existe:', modalNoSocio !== null);
            console.log('Modal Especial existe:', modalEspecial !== null);
        });
    </script>
</body>
</html>

