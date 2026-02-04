<?php
/**
 * Configuración de Email para el Sistema de Reservas del Refugio
 */

// Email del administrador (donde llegarán las notificaciones)
define('ADMIN_EMAIL', 'admin@refugio.com'); // CAMBIAR por el email real del administrador
define('ADMIN_NAME', 'Administrador del Refugio');

// Email desde el cual se envían las notificaciones
define('FROM_EMAIL', 'noreply@refugio.com'); // CAMBIAR por el email del sistema
define('FROM_NAME', 'Sistema de Reservas - Refugio');

// Nombre del refugio
define('REFUGIO_NAME', 'Refugio de Montaña');

/**
 * Función para enviar emails con formato HTML
 * @param string $to Email destinatario
 * @param string $toName Nombre del destinatario
 * @param string $subject Asunto del email
 * @param string $body Cuerpo del mensaje en HTML
 * @return bool True si se envió correctamente
 */
function enviar_email($to, $toName, $subject, $body)
{
    // Verificar si el email está configurado (modo desarrollo)
    if (ADMIN_EMAIL === 'admin@refugio.com' || FROM_EMAIL === 'noreply@refugio.com') {
        // Modo simulación: emails no configurados, no intentar enviar
        error_log("Email simulado (no configurado) - Para: $to - Asunto: $subject");
        return true; // Retornar true para no interrumpir el flujo
    }

    // Cabeceras del email
    $headers  = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . FROM_NAME . " <" . FROM_EMAIL . ">" . "\r\n";
    $headers .= "Reply-To: " . FROM_EMAIL . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // Enviar el email (suprimir warnings si el servidor SMTP no está configurado)
    $resultado = @mail($to, $subject, $body, $headers);

    // Log del resultado (opcional)
    if (! $resultado) {
        error_log("Error al enviar email a: $to - Asunto: $subject");
    }

    return $resultado;
}

/**
 * Genera el HTML base para los emails
 * @param string $contenido Contenido del email
 * @return string HTML completo
 */
function generar_plantilla_email($contenido)
{
    return "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
            }
            .header {
                background-color: #198754;
                color: white;
                padding: 20px;
                text-align: center;
                border-radius: 5px 5px 0 0;
            }
            .content {
                background-color: #f8f9fa;
                padding: 20px;
                border: 1px solid #ddd;
            }
            .info-box {
                background-color: white;
                padding: 15px;
                margin: 15px 0;
                border-left: 4px solid #198754;
                border-radius: 4px;
            }
            .info-box h3 {
                margin-top: 0;
                color: #198754;
            }
            .info-row {
                margin: 10px 0;
            }
            .info-label {
                font-weight: bold;
                color: #666;
            }
            .footer {
                background-color: #343a40;
                color: white;
                padding: 15px;
                text-align: center;
                font-size: 12px;
                border-radius: 0 0 5px 5px;
            }
            .button {
                display: inline-block;
                padding: 10px 20px;
                background-color: #198754;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                margin: 10px 0;
            }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>" . REFUGIO_NAME . "</h1>
            <p>Sistema de Gestión de Reservas</p>
        </div>
        <div class='content'>
            $contenido
        </div>
        <div class='footer'>
            <p>&copy; " . date('Y') . " " . REFUGIO_NAME . ". Todos los derechos reservados.</p>
            <p>Este es un correo automático, por favor no responder.</p>
        </div>
    </body>
    </html>
    ";
}
