# Configuración de Notificaciones por Email

## 📧 Sistema de Notificaciones Implementado

El sistema ahora envía notificaciones automáticas por email en los siguientes casos:

1. **Al crear una reserva**: El administrador recibe un email con los datos del socio y la reserva
2. **Al aprobar una reserva**: El socio recibe un email confirmando la aprobación
3. **Al cancelar una reserva**: El socio recibe un email informando la cancelación

## ⚙️ Configuración Requerida

### 1. Editar `config_email.php`

Abre el archivo `config_email.php` y modifica las siguientes constantes con los datos reales:

```php
// Email del administrador (donde llegarán las notificaciones)
define('ADMIN_EMAIL', 'admin@refugio.com'); // ← CAMBIAR por el email real

// Email desde el cual se envían las notificaciones
define('FROM_EMAIL', 'noreply@refugio.com'); // ← CAMBIAR por un email válido

// Nombre del refugio
define('REFUGIO_NAME', 'Refugio de Montaña'); // ← CAMBIAR por el nombre real
```

### 2. Configurar el Servidor de Email

Para que funcione el envío de emails con la función `mail()` de PHP, necesitas:

#### Opción A: Servidor de Producción (Recomendado)
La mayoría de servidores web (cPanel, Plesk, etc.) tienen configurado automáticamente el envío de emails. Solo necesitas:
- Usar un email válido del dominio en `FROM_EMAIL`
- Verificar que el servidor tenga configurado el servidor SMTP

#### Opción B: Desarrollo Local con SMTP
Si estás trabajando en local (XAMPP, WAMP, etc.), necesitas configurar un servidor SMTP:

**Para Windows (XAMPP):**
1. Edita `php.ini` y configura:
```ini
[mail function]
SMTP = smtp.gmail.com
smtp_port = 587
sendmail_from = tu-email@gmail.com
```

2. Usa una herramienta como [Fake Sendmail](https://github.com/rnwood/smtp4dev) para testing

**Para Linux:**
1. Instala sendmail o postfix:
```bash
sudo apt-get install sendmail
# o
sudo apt-get install postfix
```

#### Opción C: Usar PHPMailer (Alternativa Robusta)
Si prefieres usar SMTP con autenticación (Gmail, Outlook, etc.):

1. Instala PHPMailer:
```bash
composer require phpmailer/phpmailer
```

2. Modifica `config_email.php` para usar PHPMailer con SMTP

### 3. URLs del Sistema

Edita las URLs en `email_notificaciones.php` para que apunten a tu dominio:

```php
// Cambiar de:
http://localhost/refugio/viewAdmin.php

// A tu dominio:
https://tudominio.com/viewAdmin.php
```

## 🧪 Probar el Sistema

### Test 1: Crear una Reserva
1. Inicia sesión como socio
2. Crea una nueva reserva
3. Verifica que el administrador reciba el email

### Test 2: Aprobar una Reserva
1. Inicia sesión como administrador
2. Aprueba una reserva pendiente
3. Verifica que el socio reciba el email de aprobación

## 🐛 Solución de Problemas

### Los emails no se envían

1. **Verifica los logs de PHP:**
```php
error_log("Test de email");
```

2. **Comprueba la configuración de `mail()`:**
Crea un archivo `test_email.php`:
```php
<?php
$to = "tu-email@example.com";
$subject = "Test Email";
$message = "Este es un email de prueba";
$headers = "From: test@refugio.com";

if (mail($to, $subject, $message, $headers)) {
    echo "Email enviado correctamente";
} else {
    echo "Error al enviar email";
}
```

3. **Verifica que el email del socio esté en la base de datos:**
```sql
SELECT email FROM usuarios WHERE id = X;
```

### Los emails van a SPAM

- Asegúrate de usar un email válido del dominio en `FROM_EMAIL`
- Configura registros SPF y DKIM en tu dominio
- Evita palabras como "GRATIS", "OFERTA", etc. en el asunto

## 📝 Personalización

### Modificar las Plantillas de Email

Edita `email_notificaciones.php` para cambiar:
- El contenido de los mensajes
- Los estilos CSS
- La información mostrada

### Agregar Nuevas Notificaciones

Para agregar más tipos de notificaciones, crea nuevas funciones en `email_notificaciones.php`:

```php
function notificar_nuevo_evento($datos) {
    $contenido = "...";
    $htmlEmail = generar_plantilla_email($contenido);
    return enviar_email($to, $nombre, $asunto, $htmlEmail);
}
```

## 📋 Archivos del Sistema

- `config_email.php` - Configuración general de emails
- `email_notificaciones.php` - Funciones de notificación
- `viewSocio.php` - ✅ Integración al crear reservas
- `viewAdmin.php` - ✅ Integración al aprobar/cancelar reservas

## ✅ Estado de Implementación

### Completado ✅
- [x] Enviar email al admin cuando un socio crea una reserva
- [x] Enviar email al socio cuando se aprueba su reserva
- [x] Enviar email al socio cuando se rechaza/cancela su reserva

**¡El sistema está 100% funcional!**

---

**Nota:** Por seguridad, nunca subas al repositorio archivos con credenciales reales. Usa variables de entorno o archivos `.env` para datos sensibles.
