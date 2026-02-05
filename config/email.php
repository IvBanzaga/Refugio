<?php
/**
 * Configuración de Email para el Sistema de Reservas del Refugio
 */

// Cargar PHPMailer
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

// Cargar variables de entorno desde .env
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorar comentarios y líneas vacías
        if (strpos(trim($line), '#') === 0 || empty(trim($line))) {
            continue;
        }
        // Parsear línea KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key               = trim($key);
            $value             = trim($value);
            if (! empty($key)) {
                $_ENV[$key] = $value;
            }
        }
    }
}

// Email del administrador (donde llegarán las notificaciones)
define('ADMIN_EMAIL', $_ENV['ADMIN_EMAIL'] ?? 'admin@refugio.com');
define('ADMIN_NAME', $_ENV['ADMIN_NAME'] ?? 'Administrador del Refugio');

// Email desde el cual se envían las notificaciones
define('FROM_EMAIL', $_ENV['FROM_EMAIL'] ?? 'noreply@refugio.com');
define('FROM_NAME', $_ENV['FROM_NAME'] ?? 'Sistema de Reservas - Refugio');

// Nombre del refugio
define('REFUGIO_NAME', $_ENV['REFUGIO_NAME'] ?? 'Refugio de Montaña');

// Configuración SMTP
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? '');
define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? 587);
define('SMTP_USER', $_ENV['SMTP_USER'] ?? '');
define('SMTP_PASS', $_ENV['SMTP_PASS'] ?? '');
define('SMTP_SECURE', $_ENV['SMTP_SECURE'] ?? 'tls');

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
    $mail = new PHPMailer(true);

    try {
        // Verificar si SMTP está configurado
        if (empty(SMTP_HOST) || empty(SMTP_USER) || empty(SMTP_PASS)) {
            error_log("Email simulado (SMTP no configurado) - Para: $to - Asunto: $subject");
            return true;
        }

        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        // Remitente y destinatario
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($to, $toName);

        // Contenido del email
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        // Enviar
        $mail->send();
        error_log("Email enviado correctamente a: $to - Asunto: $subject");
        return true;

    } catch (Exception $e) {
        error_log("Error al enviar email: {$mail->ErrorInfo}");
        return false;
    }
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
