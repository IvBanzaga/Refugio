<?php
namespace Services;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Servicio de Email
 *
 * Gestiona el envío de emails usando PHPMailer con SMTP
 */
class EmailService
{

    private $mailer;
    private $simulationMode = false;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->configure();
    }

    /**
     * Configurar PHPMailer con SMTP
     */
    private function configure()
    {
        // Verificar si SMTP está configurado
        if (empty(SMTP_HOST) || empty(SMTP_USER) || empty(SMTP_PASS)) {
            $this->simulationMode = true;
            return;
        }

        try {
            $this->mailer->isSMTP();
            $this->mailer->Host       = SMTP_HOST;
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = SMTP_USER;
            $this->mailer->Password   = SMTP_PASS;
            $this->mailer->SMTPSecure = SMTP_SECURE;
            $this->mailer->Port       = SMTP_PORT;
            $this->mailer->CharSet    = 'UTF-8';
            $this->mailer->setFrom(FROM_EMAIL, FROM_NAME);
        } catch (Exception $e) {
            $this->simulationMode = true;
            error_log("Error al configurar email: " . $e->getMessage());
        }
    }

    /**
     * Enviar email
     *
     * @param string $to Email destinatario
     * @param string $toName Nombre del destinatario
     * @param string $subject Asunto
     * @param string $body Cuerpo HTML
     * @return bool
     */
    public function send($to, $toName, $subject, $body)
    {
        if ($this->simulationMode) {
            error_log("Email simulado - Para: {$to} - Asunto: {$subject}");
            return true;
        }

        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to, $toName);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;

            $this->mailer->send();
            error_log("Email enviado correctamente a: {$to}");
            return true;

        } catch (Exception $e) {
            error_log("Error al enviar email: {$this->mailer->ErrorInfo}");
            return false;
        }
    }

    /**
     * Plantilla base para emails
     */
    private function getTemplate($content)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #007bff; color: white; padding: 20px; text-align: center; }
                .content { background: #f8f9fa; padding: 20px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .button {
                    display: inline-block;
                    padding: 10px 20px;
                    background: #007bff;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>" . REFUGIO_NAME . "</h1>
                </div>
                <div class='content'>
                    {$content}
                </div>
                <div class='footer'>
                    <p>Este es un email automático, por favor no responder.</p>
                    <p>&copy; " . date('Y') . " " . REFUGIO_NAME . "</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Notificar al admin sobre nueva reserva
     */
    public function notificarAdminNuevaReserva($datosReserva, $datosSocio)
    {
        $content = "
            <h2>Nueva Solicitud de Reserva</h2>
            <p>Se ha recibido una nueva solicitud de reserva:</p>
            <hr>
            <h3>Datos del Socio:</h3>
            <ul>
                <li><strong>Nombre:</strong> {$datosSocio['nombre']} {$datosSocio['apellido1']} {$datosSocio['apellido2']}</li>
                <li><strong>Nº Socio:</strong> {$datosSocio['num_socio']}</li>
                <li><strong>DNI:</strong> {$datosSocio['dni']}</li>
                <li><strong>Email:</strong> {$datosSocio['email']}</li>
                <li><strong>Teléfono:</strong> {$datosSocio['telf']}</li>
            </ul>
            <h3>Datos de la Reserva:</h3>
            <ul>
                <li><strong>Fecha entrada:</strong> {$datosReserva['fecha_inicio']}</li>
                <li><strong>Fecha salida:</strong> {$datosReserva['fecha_fin']}</li>
                <li><strong>Número de camas:</strong> {$datosReserva['numero_camas']}</li>
                <li><strong>Actividad:</strong> {$datosReserva['actividad']}</li>
            </ul>
            <p style='text-align: center; margin-top: 30px;'>
                <a href='" . BASE_URL . "/viewAdmin.php?accion=reservas&tab=pendientes' class='button'>
                    Ver Reservas Pendientes
                </a>
            </p>
        ";

        return $this->send(
            ADMIN_EMAIL,
            ADMIN_NAME,
            'Nueva Solicitud de Reserva - ' . REFUGIO_NAME,
            $this->getTemplate($content)
        );
    }

    /**
     * Notificar al socio que su reserva fue aprobada
     */
    public function notificarSocioReservaAprobada($datosReserva, $datosSocio)
    {
        $content = "
            <h2>¡Reserva Aprobada!</h2>
            <p>Estimado/a {$datosSocio['nombre']} {$datosSocio['apellido1']},</p>
            <p>Tu solicitud de reserva ha sido <strong style='color: green;'>APROBADA</strong>.</p>
            <hr>
            <h3>Detalles de tu Reserva:</h3>
            <ul>
                <li><strong>Fecha entrada:</strong> {$datosReserva['fecha_inicio']}</li>
                <li><strong>Fecha salida:</strong> {$datosReserva['fecha_fin']}</li>
                <li><strong>Número de camas:</strong> {$datosReserva['numero_camas']}</li>
            </ul>
            <p>Te esperamos en el refugio. ¡Que disfrutes de tu estancia!</p>
        ";

        return $this->send(
            $datosSocio['email'],
            $datosSocio['nombre'] . ' ' . $datosSocio['apellido1'],
            'Reserva Aprobada - ' . REFUGIO_NAME,
            $this->getTemplate($content)
        );
    }

    /**
     * Notificar al socio que su reserva fue cancelada/rechazada
     */
    public function notificarSocioReservaCancelada($datosReserva, $datosSocio, $motivo = '')
    {
        $motivoTexto = ! empty($motivo) ? "<p><strong>Motivo:</strong> {$motivo}</p>" : '';

        $content = "
            <h2>Reserva Cancelada</h2>
            <p>Estimado/a {$datosSocio['nombre']} {$datosSocio['apellido1']},</p>
            <p>Lamentamos informarte que tu reserva ha sido <strong style='color: red;'>CANCELADA</strong>.</p>
            <hr>
            <h3>Detalles de la Reserva:</h3>
            <ul>
                <li><strong>Fecha entrada:</strong> {$datosReserva['fecha_inicio']}</li>
                <li><strong>Fecha salida:</strong> {$datosReserva['fecha_fin']}</li>
                <li><strong>Número de camas:</strong> {$datosReserva['numero_camas']}</li>
            </ul>
            {$motivoTexto}
            <p>Si tienes alguna duda, no dudes en contactarnos.</p>
        ";

        return $this->send(
            $datosSocio['email'],
            $datosSocio['nombre'] . ' ' . $datosSocio['apellido1'],
            'Reserva Cancelada - ' . REFUGIO_NAME,
            $this->getTemplate($content)
        );
    }
}
