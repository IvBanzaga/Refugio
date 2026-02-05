<?php
/**
 * ReservaController - Controlador de Reservas
 *
 * Maneja todas las acciones relacionadas con reservas:
 * - Crear (socio, no socio, especial)
 * - Aprobar/Rechazar
 * - Editar
 * - Cancelar
 * - Exportar (CSV/PDF)
 */

class ReservaController
{
    private $conexion;
    private $emailService;

    /**
     * Constructor
     * @param PDO $conexion Conexión a la base de datos
     */
    public function __construct($conexion)
    {
        $this->conexion = $conexion;

        // Cargar EmailService si está disponible
        if (file_exists(__DIR__ . '/../Services/EmailService.php')) {
            require_once __DIR__ . '/../Services/EmailService.php';
            $this->emailService = new EmailService();
        }
    }

    /**
     * Crear nueva reserva de socio
     */
    public function crearReservaSocio()
    {
        try {
            $id_usuario    = (int) $_POST['id_usuario'];
            $id_habitacion = (int) $_POST['id_habitacion'];
            $numero_camas  = (int) $_POST['numero_camas'];
            $fecha_inicio  = $_POST['fecha_inicio'];
            $fecha_fin     = $_POST['fecha_fin'];
            $observaciones = $_POST['observaciones'] ?? '';

            // Validar fechas
            if (strtotime($fecha_fin) < strtotime($fecha_inicio)) {
                throw new Exception('La fecha de fin debe ser posterior a la fecha de inicio');
            }

            // Verificar disponibilidad
            $camas_disponibles = obtener_camas_disponibles($this->conexion, $id_habitacion, $fecha_inicio, $fecha_fin);

            if (count($camas_disponibles) < $numero_camas) {
                throw new Exception('No hay suficientes camas disponibles para las fechas seleccionadas');
            }

            // Crear reserva
            $stmt = $this->conexion->prepare("
                INSERT INTO reservas (id_usuario, id_habitacion, numero_camas, fecha_inicio, fecha_fin, observaciones, estado)
                VALUES (:id_usuario, :id_habitacion, :numero_camas, :fecha_inicio, :fecha_fin, :observaciones, 'pendiente')
            ");

            $stmt->execute([
                ':id_usuario'    => $id_usuario,
                ':id_habitacion' => $id_habitacion,
                ':numero_camas'  => $numero_camas,
                ':fecha_inicio'  => $fecha_inicio,
                ':fecha_fin'     => $fecha_fin,
                ':observaciones' => $observaciones,
            ]);

            $id_reserva = $this->conexion->lastInsertId();

            // Asignar camas automáticamente
            $camas_asignadas = array_slice($camas_disponibles, 0, $numero_camas);
            foreach ($camas_asignadas as $cama) {
                $stmt = $this->conexion->prepare("
                    INSERT INTO reservas_camas (id_reserva, id_cama)
                    VALUES (:id_reserva, :id_cama)
                ");
                $stmt->execute([
                    ':id_reserva' => $id_reserva,
                    ':id_cama'    => $cama['id'],
                ]);
            }

            // Enviar email al admin si el servicio está disponible
            if ($this->emailService) {
                $usuario = obtener_usuario($this->conexion, $id_usuario);
                $this->emailService->notificarAdminNuevaReserva($usuario, [
                    'id'           => $id_reserva,
                    'fecha_inicio' => $fecha_inicio,
                    'fecha_fin'    => $fecha_fin,
                    'numero_camas' => $numero_camas,
                ]);
            }

            $_SESSION['mensaje']      = 'Reserva creada exitosamente. Pendiente de aprobación.';
            $_SESSION['tipo_mensaje'] = 'success';

        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al crear la reserva: ' . $e->getMessage();
        }

        redirect($_POST['redirect_to'] ?? 'viewAdminMVC.php?accion=reservas');
    }

    /**
     * Crear reserva de no socio
     */
    public function crearReservaNoSocio()
    {
        try {
            $id_habitacion = (int) $_POST['id_habitacion'];
            $numero_camas  = (int) $_POST['numero_camas'];
            $fecha_inicio  = $_POST['fecha_inicio'];
            $fecha_fin     = $_POST['fecha_fin'];

            // Datos del no socio en formato específico
            $nombre    = $_POST['nombre_no_socio'];
            $dni       = $_POST['dni_no_socio'];
            $telefono  = $_POST['telefono_no_socio'];
            $email     = $_POST['email_no_socio'];
            $grupo     = $_POST['grupo_no_socio'] ?? 'Otro';
            $actividad = $_POST['actividad_no_socio'] ?? '';

            // Formato: NO_SOCIO|nombre|DNI:xxx|Tel:xxx|Email:xxx|Grupo:xxx|||ACTIVIDAD:xxx
            $observaciones = sprintf(
                'NO_SOCIO|%s|DNI:%s|Tel:%s|Email:%s|Grupo:%s|||ACTIVIDAD:%s',
                $nombre,
                $dni,
                $telefono,
                $email,
                $grupo,
                $actividad
            );

            // Verificar disponibilidad
            $camas_disponibles = obtener_camas_disponibles($this->conexion, $id_habitacion, $fecha_inicio, $fecha_fin);

            if (count($camas_disponibles) < $numero_camas) {
                throw new Exception('No hay suficientes camas disponibles');
            }

            // Crear reserva sin id_usuario (NULL)
            $stmt = $this->conexion->prepare("
                INSERT INTO reservas (id_usuario, id_habitacion, numero_camas, fecha_inicio, fecha_fin, observaciones, estado)
                VALUES (NULL, :id_habitacion, :numero_camas, :fecha_inicio, :fecha_fin, :observaciones, 'pendiente')
            ");

            $stmt->execute([
                ':id_habitacion' => $id_habitacion,
                ':numero_camas'  => $numero_camas,
                ':fecha_inicio'  => $fecha_inicio,
                ':fecha_fin'     => $fecha_fin,
                ':observaciones' => $observaciones,
            ]);

            $id_reserva = $this->conexion->lastInsertId();

            // Asignar camas
            $camas_asignadas = array_slice($camas_disponibles, 0, $numero_camas);
            foreach ($camas_asignadas as $cama) {
                $stmt = $this->conexion->prepare("
                    INSERT INTO reservas_camas (id_reserva, id_cama)
                    VALUES (:id_reserva, :id_cama)
                ");
                $stmt->execute([
                    ':id_reserva' => $id_reserva,
                    ':id_cama'    => $cama['id'],
                ]);
            }

            $_SESSION['mensaje']      = 'Reserva de no socio creada exitosamente.';
            $_SESSION['tipo_mensaje'] = 'success';

        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al crear la reserva: ' . $e->getMessage();
        }

        redirect('viewAdminMVC.php?accion=reservas');
    }

    /**
     * Crear reserva especial (todo el refugio, GMT, etc.)
     */
    public function crearReservaEspecial()
    {
        try {
            $tipo         = $_POST['tipo_reserva_especial'];
            $fecha_inicio = $_POST['fecha_inicio'];
            $fecha_fin    = $_POST['fecha_fin'];
            $motivo       = $_POST['motivo'] ?? '';
            $grupo        = $_POST['grupo_especial'] ?? '';

            $id_habitacion = 1;  // Habitación por defecto
            $numero_camas  = 26; // Todo el refugio

            // Construir observaciones según el tipo
            switch ($tipo) {
                case 'todo_refugio':
                    $observaciones = 'TODO EL REFUGIO: ' . $motivo;
                    if (! empty($grupo)) {
                        $observaciones .= '|Grupo:' . $grupo;
                    }
                    break;

                case 'gmt':
                    $id_socio       = (int) $_POST['id_socio_gmt'];
                    $observaciones  = 'GMT - Grupo de Montañeros de Tenerife: ' . $motivo;
                    $observaciones .= '|Grupo:Grupo de Montañeros de Tenerife';
                    // Asociar con el socio si se proporcionó
                    break;

                case 'otro_grupo':
                    $nombre_grupo   = $_POST['nombre_grupo'];
                    $observaciones  = 'GRUPO: ' . $nombre_grupo . ' - ' . $motivo;
                    $observaciones .= '|Grupo:' . $nombre_grupo;
                    break;

                default:
                    throw new Exception('Tipo de reserva especial no válido');
            }

            // Crear reserva
            $stmt  = $this->conexion->prepare("
                INSERT INTO reservas (id_usuario, id_habitacion, numero_camas, fecha_inicio, fecha_fin, observaciones, estado)
                VALUES (:id_usuario, :id_habitacion, :numero_camas, :fecha_inicio, :fecha_fin, :observaciones, 'reservada')
            ");

            $id_usuario_asociado = isset($id_socio) ? $id_socio : null;

            $stmt->execute([
                ':id_usuario'    => $id_usuario_asociado,
                ':id_habitacion' => $id_habitacion,
                ':numero_camas'  => $numero_camas,
                ':fecha_inicio'  => $fecha_inicio,
                ':fecha_fin'     => $fecha_fin,
                ':observaciones' => $observaciones,
            ]);

            $id_reserva  = $this->conexion->lastInsertId();

            // Para reservas especiales, asignar todas las camas
            $todas_camas = obtener_todas_camas($this->conexion, $id_habitacion);
            foreach ($todas_camas as $cama) {
                $stmt = $this->conexion->prepare("
                    INSERT INTO reservas_camas (id_reserva, id_cama)
                    VALUES (:id_reserva, :id_cama)
                ");
                $stmt->execute([
                    ':id_reserva' => $id_reserva,
                    ':id_cama'    => $cama['id'],
                ]);
            }

            $_SESSION['mensaje']      = 'Reserva especial creada y aprobada automáticamente.';
            $_SESSION['tipo_mensaje'] = 'success';

        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al crear la reserva especial: ' . $e->getMessage();
        }

        redirect('viewAdminMVC.php?accion=reservas');
    }

    /**
     * Aprobar una reserva pendiente
     */
    public function aprobarReserva()
    {
        try {
            $id_reserva = (int) $_POST['id'];

            // Actualizar estado
            $stmt = $this->conexion->prepare("
                UPDATE reservas
                SET estado = 'reservada'
                WHERE id = :id AND estado = 'pendiente'
            ");
            $stmt->execute([':id' => $id_reserva]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('La reserva no existe o ya fue procesada');
            }

            // Obtener datos de la reserva y usuario para enviar email
            $reserva = obtener_reserva($this->conexion, $id_reserva);

            if ($this->emailService && $reserva['id_usuario']) {
                $usuario = obtener_usuario($this->conexion, $reserva['id_usuario']);
                $this->emailService->notificarSocioReservaAprobada($usuario, $reserva);
            }

            $_SESSION['mensaje']      = 'Reserva aprobada exitosamente.';
            $_SESSION['tipo_mensaje'] = 'success';

        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al aprobar la reserva: ' . $e->getMessage();
        }

        redirect('viewAdminMVC.php?accion=reservas&tab=pendientes');
    }

    /**
     * Rechazar una reserva pendiente
     */
    public function rechazarReserva()
    {
        try {
            $id_reserva = (int) $_POST['id'];

            // Cambiar estado a cancelada
            $stmt = $this->conexion->prepare("
                UPDATE reservas
                SET estado = 'cancelada'
                WHERE id = :id AND estado = 'pendiente'
            ");
            $stmt->execute([':id' => $id_reserva]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('La reserva no existe o ya fue procesada');
            }

            // Obtener datos para notificación
            $reserva = obtener_reserva($this->conexion, $id_reserva);

            if ($this->emailService && $reserva['id_usuario']) {
                $usuario = obtener_usuario($this->conexion, $reserva['id_usuario']);
                $this->emailService->notificarSocioReservaCancelada($usuario, $reserva);
            }

            $_SESSION['mensaje']      = 'Reserva rechazada.';
            $_SESSION['tipo_mensaje'] = 'warning';

        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al rechazar la reserva: ' . $e->getMessage();
        }

        redirect('viewAdminMVC.php?accion=reservas&tab=canceladas');
    }

    /**
     * Cancelar una reserva (por admin o usuario)
     */
    public function cancelarReserva()
    {
        try {
            $id_reserva = (int) $_POST['id'];
            $es_admin   = $_SESSION['rol'] === 'admin';

            // Si es usuario, verificar que la reserva le pertenece
            if (! $es_admin) {
                $reserva = obtener_reserva($this->conexion, $id_reserva);
                if ($reserva['id_usuario'] != $_SESSION['userId']) {
                    throw new Exception('No tienes permiso para cancelar esta reserva');
                }
            }

            // Actualizar estado
            $stmt = $this->conexion->prepare("
                UPDATE reservas
                SET estado = 'cancelada'
                WHERE id = :id AND estado IN ('pendiente', 'reservada')
            ");
            $stmt->execute([':id' => $id_reserva]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('La reserva no existe o ya fue cancelada');
            }

            $_SESSION['mensaje']      = 'Reserva cancelada exitosamente.';
            $_SESSION['tipo_mensaje'] = 'info';

        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al cancelar la reserva: ' . $e->getMessage();
        }

        // Redirigir según el rol
        if ($_SESSION['rol'] === 'admin') {
            redirect('viewAdminMVC.php?accion=reservas&tab=canceladas');
        } else {
            redirect('viewSocioMVC.php?accion=mis_reservas');
        }
    }

    /**
     * Editar una reserva existente
     */
    public function editarReserva()
    {
        try {
            $id_reserva    = (int) $_POST['id'];
            $numero_camas  = (int) $_POST['numero_camas'];
            $fecha_inicio  = $_POST['fecha_inicio'];
            $fecha_fin     = $_POST['fecha_fin'];
            $observaciones = $_POST['observaciones'] ?? '';

            // Validar fechas
            if (strtotime($fecha_fin) < strtotime($fecha_inicio)) {
                throw new Exception('La fecha de fin debe ser posterior a la fecha de inicio');
            }

            // Obtener reserva actual
            $reserva_actual = obtener_reserva($this->conexion, $id_reserva);

            // Verificar permisos
            $es_admin = $_SESSION['rol'] === 'admin';
            if (! $es_admin && $reserva_actual['id_usuario'] != $_SESSION['userId']) {
                throw new Exception('No tienes permiso para editar esta reserva');
            }

            // Actualizar reserva
            $stmt = $this->conexion->prepare("
                UPDATE reservas
                SET numero_camas = :numero_camas,
                    fecha_inicio = :fecha_inicio,
                    fecha_fin = :fecha_fin,
                    observaciones = :observaciones
                WHERE id = :id
            ");

            $stmt->execute([
                ':numero_camas'  => $numero_camas,
                ':fecha_inicio'  => $fecha_inicio,
                ':fecha_fin'     => $fecha_fin,
                ':observaciones' => $observaciones,
                ':id'            => $id_reserva,
            ]);

            // Si cambió el número de camas, actualizar asignación
            if ($numero_camas != $reserva_actual['numero_camas']) {
                // Eliminar asignaciones actuales
                $stmt = $this->conexion->prepare("DELETE FROM reservas_camas WHERE id_reserva = :id");
                $stmt->execute([':id' => $id_reserva]);

                // Reasignar camas
                $camas_disponibles = obtener_camas_disponibles(
                    $this->conexion,
                    $reserva_actual['id_habitacion'],
                    $fecha_inicio,
                    $fecha_fin,
                    $id_reserva // Excluir esta reserva del cálculo
                );

                if (count($camas_disponibles) < $numero_camas) {
                    throw new Exception('No hay suficientes camas disponibles');
                }

                $camas_asignadas = array_slice($camas_disponibles, 0, $numero_camas);
                foreach ($camas_asignadas as $cama) {
                    $stmt = $this->conexion->prepare("
                        INSERT INTO reservas_camas (id_reserva, id_cama)
                        VALUES (:id_reserva, :id_cama)
                    ");
                    $stmt->execute([
                        ':id_reserva' => $id_reserva,
                        ':id_cama'    => $cama['id'],
                    ]);
                }
            }

            $_SESSION['mensaje']      = 'Reserva actualizada exitosamente.';
            $_SESSION['tipo_mensaje'] = 'success';

        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al editar la reserva: ' . $e->getMessage();
        }

        // Redirigir según el contexto
        if ($_SESSION['rol'] === 'admin') {
            redirect('viewAdminMVC.php?accion=reservas');
        } else {
            redirect('viewSocioMVC.php?accion=mis_reservas');
        }
    }

    /**
     * Eliminar reservas canceladas permanentemente
     */
    public function eliminarReservasCanceladas()
    {
        try {
            $ids = $_POST['ids'] ?? [];

            if (empty($ids)) {
                throw new Exception('No se seleccionaron reservas para eliminar');
            }

            // Eliminar solo reservas canceladas
            $placeholders = str_repeat('?,', count($ids) - 1) . '?';
            $stmt         = $this->conexion->prepare("
                DELETE FROM reservas
                WHERE id IN ($placeholders) AND estado = 'cancelada'
            ");
            $stmt->execute($ids);

            $eliminadas = $stmt->rowCount();

            $_SESSION['mensaje']      = "Se eliminaron $eliminadas reserva(s) cancelada(s).";
            $_SESSION['tipo_mensaje'] = 'success';

        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al eliminar reservas: ' . $e->getMessage();
        }

        redirect('viewAdminMVC.php?accion=reservas&tab=canceladas');
    }
}
