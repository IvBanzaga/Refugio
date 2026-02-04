<?php
/**
 * Script de prueba para el sistema de emails
 * Ejecuta este archivo para verificar que el envío de emails funciona correctamente
 */

// Incluir archivos necesarios
require_once 'config_email.php';
require_once 'email_notificaciones.php';

// Mostrar configuración actual
echo "<h2>🔧 Configuración Actual</h2>";
echo "<pre>";
echo "ADMIN_EMAIL: " . ADMIN_EMAIL . "\n";
echo "FROM_EMAIL: " . FROM_EMAIL . "\n";
echo "REFUGIO_NAME: " . REFUGIO_NAME . "\n";
echo "</pre>";

// Datos de prueba para el socio
$datosSocioPrueba = [
    'nombre'    => 'Juan',
    'apellido1' => 'Pérez',
    'apellido2' => 'García',
    'num_socio' => '12345',
    'dni'       => '12345678A',
    'email'     => 'socio.prueba@example.com', // Cambiar por un email real para testing
    'telf'      => '123456789',
];

// Datos de prueba para la reserva
$datosReservaPrueba = [
    'id'           => 999,
    'fecha_inicio' => date('Y-m-d'),
    'fecha_fin'    => date('Y-m-d', strtotime('+3 days')),
    'numero_camas' => 2,
    'actividad'    => 'Excursión de montaña y observación de flora',
];

echo "<h2>📋 Datos de Prueba</h2>";
echo "<h3>Socio:</h3>";
echo "<pre>" . print_r($datosSocioPrueba, true) . "</pre>";
echo "<h3>Reserva:</h3>";
echo "<pre>" . print_r($datosReservaPrueba, true) . "</pre>";

echo "<hr>";
echo "<h2>📧 Pruebas de Envío de Emails</h2>";

// Test 1: Notificación al administrador de nueva reserva
echo "<h3>Test 1: Notificación al Administrador (Nueva Reserva)</h3>";
try {
    $resultado1 = notificar_admin_nueva_reserva($datosReservaPrueba, $datosSocioPrueba);
    if ($resultado1) {
        echo "<p style='color: green;'>✅ Email enviado correctamente a " . ADMIN_EMAIL . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Error al enviar email. Verifica la configuración de mail() en PHP.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Excepción: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// Test 2: Notificación al socio de reserva aprobada
echo "<h3>Test 2: Notificación al Socio (Reserva Aprobada)</h3>";
try {
    $resultado2 = notificar_socio_reserva_aprobada($datosReservaPrueba, $datosSocioPrueba);
    if ($resultado2) {
        echo "<p style='color: green;'>✅ Email enviado correctamente a " . $datosSocioPrueba['email'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Error al enviar email. Verifica la configuración de mail() en PHP.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Excepción: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// Test 3: Notificación al socio de reserva cancelada
echo "<h3>Test 3: Notificación al Socio (Reserva Cancelada)</h3>";
try {
    $resultado3 = notificar_socio_reserva_cancelada(
        $datosReservaPrueba,
        $datosSocioPrueba,
        'No hay disponibilidad para las fechas solicitadas'
    );
    if ($resultado3) {
        echo "<p style='color: green;'>✅ Email enviado correctamente a " . $datosSocioPrueba['email'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Error al enviar email. Verifica la configuración de mail() en PHP.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Excepción: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>📝 Instrucciones</h2>";
echo "<ul>";
echo "<li>Si ves errores, revisa el archivo <code>config_email.php</code></li>";
echo "<li>Cambia <code>ADMIN_EMAIL</code> por un email real</li>";
echo "<li>Cambia el email del socio de prueba por uno real</li>";
echo "<li>Verifica que tu servidor tenga configurado correctamente la función <code>mail()</code></li>";
echo "<li>Revisa la carpeta de SPAM si no recibes los emails</li>";
echo "</ul>";

echo "<p><strong>🔒 IMPORTANTE:</strong> Elimina o protege este archivo después de las pruebas.</p>";

echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
    h2 { color: #198754; }
    h3 { color: #0d6efd; }
    pre { background: #f5f5f5; padding: 15px; border-radius: 5px; }
    hr { margin: 30px 0; }
</style>";
