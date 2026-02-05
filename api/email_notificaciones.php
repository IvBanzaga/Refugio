<?php
/**
 * Funciones para envío de notificaciones por email
 */

require_once __DIR__ . '/../config/email.php';

/**
 * Enviar notificación al administrador cuando un socio crea una nueva reserva
 * @param array $datosReserva Datos de la reserva creada
 * @param array $datosSocio Datos del socio que creó la reserva
 * @return bool True si se envió correctamente
 */
function notificar_admin_nueva_reserva($datosReserva, $datosSocio)
{
    $contenido = "
        <h2>📋 Nueva Solicitud de Reserva</h2>
        <p>Se ha recibido una nueva solicitud de reserva que requiere tu aprobación.</p>

        <div class='info-box'>
            <h3>👤 Datos del Socio</h3>
            <div class='info-row'>
                <span class='info-label'>Nombre:</span>
                {$datosSocio['nombre']} {$datosSocio['apellido1']} {$datosSocio['apellido2']}
            </div>
            <div class='info-row'>
                <span class='info-label'>Nº Socio:</span> {$datosSocio['num_socio']}
            </div>
            <div class='info-row'>
                <span class='info-label'>DNI:</span> {$datosSocio['dni']}
            </div>
            <div class='info-row'>
                <span class='info-label'>Email:</span> {$datosSocio['email']}
            </div>
            <div class='info-row'>
                <span class='info-label'>Teléfono:</span> {$datosSocio['telf']}
            </div>
        </div>

        <div class='info-box'>
            <h3>🏠 Datos de la Reserva</h3>
            <div class='info-row'>
                <span class='info-label'>Nº Reserva:</span> #{$datosReserva['id']}
            </div>
            <div class='info-row'>
                <span class='info-label'>Fecha de Entrada:</span> " . date('d/m/Y', strtotime($datosReserva['fecha_inicio'])) . "
            </div>
            <div class='info-row'>
                <span class='info-label'>Fecha de Salida:</span> " . date('d/m/Y', strtotime($datosReserva['fecha_fin'])) . "
            </div>
            <div class='info-row'>
                <span class='info-label'>Número de Camas:</span> {$datosReserva['numero_camas']}
            </div>
            <div class='info-row'>
                <span class='info-label'>Actividad a Realizar:</span><br>
                {$datosReserva['actividad']}
            </div>
            <div class='info-row'>
                <span class='info-label'>Fecha de Solicitud:</span> " . date('d/m/Y H:i:s') . "
            </div>
        </div>

        <p style='text-align: center; margin-top: 30px;'>
            <a href='http://localhost/refugio/viewAdmin.php' class='button'>
                Ver Reserva en el Sistema
            </a>
        </p>

        <p style='color: #666; font-size: 14px;'>
            💡 <strong>Recuerda:</strong> Debes revisar y aprobar esta reserva desde el panel de administración.
        </p>
    ";

    $htmlEmail = generar_plantilla_email($contenido);

    $asunto = "Nueva Solicitud de Reserva - " . $datosSocio['nombre'] . " " . $datosSocio['apellido1'];

    return enviar_email(ADMIN_EMAIL, ADMIN_NAME, $asunto, $htmlEmail);
}

/**
 * Enviar notificación al socio cuando su reserva es aprobada
 * @param array $datosReserva Datos de la reserva aprobada
 * @param array $datosSocio Datos del socio
 * @return bool True si se envió correctamente
 */
function notificar_socio_reserva_aprobada($datosReserva, $datosSocio)
{
    $contenido = "
        <h2>✅ Tu Reserva ha sido Aprobada</h2>
        <p>Hola <strong>{$datosSocio['nombre']}</strong>,</p>
        <p>Nos complace informarte que tu solicitud de reserva ha sido <strong>APROBADA</strong>.</p>

        <div class='info-box'>
            <h3>📋 Detalles de tu Reserva</h3>
            <div class='info-row'>
                <span class='info-label'>Nº Reserva:</span> #{$datosReserva['id']}
            </div>
            <div class='info-row'>
                <span class='info-label'>Fecha de Entrada:</span> " . date('d/m/Y', strtotime($datosReserva['fecha_inicio'])) . "
            </div>
            <div class='info-row'>
                <span class='info-label'>Fecha de Salida:</span> " . date('d/m/Y', strtotime($datosReserva['fecha_fin'])) . "
            </div>
            <div class='info-row'>
                <span class='info-label'>Número de Camas:</span> {$datosReserva['numero_camas']}
            </div>
            <div class='info-row'>
                <span class='info-label'>Actividad a Realizar:</span><br>
                {$datosReserva['actividad']}
            </div>
        </div>

        <div style='background-color: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 20px 0;'>
            <strong>📌 Información Importante:</strong>
            <ul>
                <li>Por favor, llega al refugio a la hora acordada.</li>
                <li>Trae tu documentación de socio.</li>
                <li>Si tienes alguna pregunta, no dudes en contactarnos.</li>
            </ul>
        </div>

        <p style='text-align: center; margin-top: 30px;'>
            <a href='http://localhost/refugio/viewSocio.php?accion=mis_reservas' class='button'>
                Ver mis Reservas
            </a>
        </p>

        <p style='text-align: center; color: #666;'>
            ¡Te esperamos en el refugio! 🏔️
        </p>
    ";

    $htmlEmail = generar_plantilla_email($contenido);

    $asunto = "Reserva Aprobada - " . REFUGIO_NAME . " - Reserva #" . $datosReserva['id'];

    return enviar_email($datosSocio['email'], $datosSocio['nombre'], $asunto, $htmlEmail);
}

/**
 * Enviar notificación al socio cuando su reserva es cancelada
 * @param array $datosReserva Datos de la reserva cancelada
 * @param array $datosSocio Datos del socio
 * @param string $motivo Motivo de la cancelación (opcional)
 * @return bool True si se envió correctamente
 */
function notificar_socio_reserva_cancelada($datosReserva, $datosSocio, $motivo = '')
{
    $motivoHtml = '';
    if (! empty($motivo)) {
        $motivoHtml = "
            <div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                <strong>Motivo de la cancelación:</strong><br>
                $motivo
            </div>
        ";
    }

    $contenido = "
        <h2>❌ Reserva Cancelada</h2>
        <p>Hola <strong>{$datosSocio['nombre']}</strong>,</p>
        <p>Lamentamos informarte que tu reserva ha sido <strong>CANCELADA</strong>.</p>

        <div class='info-box'>
            <h3>📋 Detalles de la Reserva Cancelada</h3>
            <div class='info-row'>
                <span class='info-label'>Nº Reserva:</span> #{$datosReserva['id']}
            </div>
            <div class='info-row'>
                <span class='info-label'>Fecha de Entrada:</span> " . date('d/m/Y', strtotime($datosReserva['fecha_inicio'])) . "
            </div>
            <div class='info-row'>
                <span class='info-label'>Fecha de Salida:</span> " . date('d/m/Y', strtotime($datosReserva['fecha_fin'])) . "
            </div>
            <div class='info-row'>
                <span class='info-label'>Número de Camas:</span> {$datosReserva['numero_camas']}
            </div>
        </div>

        $motivoHtml

        <p>Si tienes alguna duda o deseas realizar una nueva reserva, puedes hacerlo desde tu panel de usuario.</p>

        <p style='text-align: center; margin-top: 30px;'>
            <a href='http://localhost/refugio/viewSocio.php?accion=nueva_reserva' class='button'>
                Hacer Nueva Reserva
            </a>
        </p>
    ";

    $htmlEmail = generar_plantilla_email($contenido);

    $asunto = "Reserva Cancelada - " . REFUGIO_NAME . " - Reserva #" . $datosReserva['id'];

    return enviar_email($datosSocio['email'], $datosSocio['nombre'], $asunto, $htmlEmail);
}
