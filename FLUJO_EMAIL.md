# 📧 Sistema de Notificaciones por Email - Flujo Completo

## 🎯 Resumen Ejecutivo

Sistema 100% implementado y funcional que envía notificaciones automáticas por email en todo el ciclo de vida de una reserva.

---

## 🔄 Flujo de Notificaciones

### 1️⃣ Socio Crea una Reserva

```
┌─────────────────┐
│   SOCIO         │
│  Crea Reserva   │
└────────┬────────┘
         │
         ▼
┌─────────────────────────┐
│  Sistema guarda en BD   │
└────────┬────────────────┘
         │
         ▼
┌─────────────────────────────────┐
│  📧 EMAIL AL ADMINISTRADOR      │
│  ────────────────────────────   │
│  Para: admin@refugio.com        │
│  Asunto: Nueva Solicitud        │
│                                 │
│  Contenido:                     │
│  • Datos del socio              │
│  • Detalles de la reserva       │
│  • Botón: Ver en el Sistema     │
└─────────────────────────────────┘
         │
         ▼
┌─────────────────────────┐
│  Usuario ve mensaje:    │
│  "Reserva creada        │
│   exitosamente"         │
└─────────────────────────┘
```

**Archivo:** `viewSocio.php` líneas ~70-95  
**Función:** `notificar_admin_nueva_reserva()`

---

### 2️⃣ Administrador Aprueba la Reserva

```
┌─────────────────┐
│  ADMINISTRADOR  │
│ Aprueba Reserva │
└────────┬────────┘
         │
         ▼
┌──────────────────────────┐
│  Sistema actualiza BD    │
│  Estado: 'reservada'     │
└────────┬─────────────────┘
         │
         ▼
┌─────────────────────────────────┐
│  📧 EMAIL AL SOCIO              │
│  ────────────────────────────   │
│  Para: socio@email.com          │
│  Asunto: Reserva Aprobada       │
│                                 │
│  Contenido:                     │
│  • "Tu reserva fue aprobada"    │
│  • Detalles de la reserva       │
│  • Información importante       │
│  • Botón: Ver mis Reservas      │
└─────────────────────────────────┘
         │
         ▼
┌─────────────────────────┐
│  Admin ve mensaje:      │
│  "Reserva aprobada      │
│   exitosamente"         │
└─────────────────────────┘
```

**Archivo:** `viewAdmin.php` líneas ~281-318  
**Función:** `notificar_socio_reserva_aprobada()`

---

### 3️⃣ Administrador Rechaza la Reserva (Pendiente)

```
┌─────────────────┐
│  ADMINISTRADOR  │
│ Rechaza Reserva │
└────────┬────────┘
         │
         ▼
┌──────────────────────────┐
│  Sistema actualiza BD    │
│  Estado: 'cancelada'     │
└────────┬─────────────────┘
         │
         ▼
┌─────────────────────────────────┐
│  📧 EMAIL AL SOCIO              │
│  ────────────────────────────   │
│  Para: socio@email.com          │
│  Asunto: Reserva Rechazada      │
│                                 │
│  Contenido:                     │
│  • "Tu reserva fue rechazada"   │
│  • Detalles de la reserva       │
│  • Motivo del rechazo           │
│  • Botón: Hacer Nueva Reserva   │
└─────────────────────────────────┘
         │
         ▼
┌─────────────────────────┐
│  Admin ve mensaje:      │
│  "Reserva rechazada     │
│   exitosamente"         │
└─────────────────────────┘
```

**Archivo:** `viewAdmin.php` líneas ~320-362  
**Función:** `notificar_socio_reserva_cancelada()`

---

### 4️⃣ Administrador Cancela Reserva Aprobada

```
┌─────────────────┐
│  ADMINISTRADOR  │
│ Cancela Reserva │
│   (Aprobada)    │
└────────┬────────┘
         │
         ▼
┌──────────────────────────┐
│  Sistema actualiza BD    │
│  Estado: 'cancelada'     │
└────────┬─────────────────┘
         │
         ▼
┌─────────────────────────────────┐
│  📧 EMAIL AL SOCIO              │
│  ────────────────────────────   │
│  Para: socio@email.com          │
│  Asunto: Reserva Cancelada      │
│                                 │
│  Contenido:                     │
│  • "Tu reserva fue cancelada"   │
│  • Detalles de la reserva       │
│  • Motivo de cancelación        │
│  • Botón: Hacer Nueva Reserva   │
└─────────────────────────────────┘
         │
         ▼
┌─────────────────────────┐
│  Admin ve mensaje:      │
│  "Reserva cancelada     │
│   exitosamente"         │
└─────────────────────────┘
```

**Archivo:** `viewAdmin.php` líneas ~364-403  
**Función:** `notificar_socio_reserva_cancelada()`

---

## 📊 Tabla de Implementación

| Evento | Destinatario | Estado | Archivo | Función |
|--------|--------------|--------|---------|---------|
| Crear Reserva | Admin | ✅ | viewSocio.php | `notificar_admin_nueva_reserva()` |
| Aprobar Reserva | Socio | ✅ | viewAdmin.php | `notificar_socio_reserva_aprobada()` |
| Rechazar Reserva | Socio | ✅ | viewAdmin.php | `notificar_socio_reserva_cancelada()` |
| Cancelar Reserva | Socio | ✅ | viewAdmin.php | `notificar_socio_reserva_cancelada()` |

---

## 🎨 Diseño de los Emails

Todos los emails incluyen:

✨ **Header verde** con logo/nombre del refugio  
📋 **Cajas de información** con datos estructurados  
🎯 **Botones de acción** con enlaces directos  
📱 **Diseño responsive** compatible con móviles  
🔒 **Footer** con información legal  

### Ejemplo de Email (HTML):

```html
┌─────────────────────────────────────┐
│  🏔️ REFUGIO DE MONTAÑA             │
│  Sistema de Gestión de Reservas     │
└─────────────────────────────────────┘

  📋 Detalles de tu Reserva
  ────────────────────────────
  
  Nº Reserva:        #123
  Fecha de Entrada:  05/02/2026
  Fecha de Salida:   08/02/2026
  Número de Camas:   2
  
  ┌─────────────────────────┐
  │  Ver mis Reservas       │
  └─────────────────────────┘
  
─────────────────────────────────────
© 2026 Refugio de Montaña
Este es un correo automático
```

---

## 🔧 Configuración Rápida

### 1. Editar Config (1 minuto)

```php
// config_email.php
define('ADMIN_EMAIL', 'admin@refugio.com');    // ← TU EMAIL
define('FROM_EMAIL', 'noreply@refugio.com');   // ← EMAIL DEL SISTEMA
define('REFUGIO_NAME', 'Refugio de Montaña');  // ← NOMBRE DEL REFUGIO
```

### 2. Probar Sistema (2 minutos)

```bash
# Acceder a:
http://localhost/refugio/test_email.php

# Verificar que los 3 emails se envíen correctamente
```

### 3. Producción (opcional)

- Configurar SPF/DKIM en el dominio
- Usar un servicio SMTP profesional (SendGrid, Mailgun)
- Proteger/eliminar `test_email.php`

---

## 🚨 Manejo de Errores

El sistema es **resiliente**:

- ❌ Si falla el envío del email → **NO interrumpe** la operación
- 📝 Los errores se registran en `error_log` de PHP
- ✅ La reserva se crea/aprueba/cancela **independientemente** del email

```php
try {
    // Operación principal (crear/aprobar/cancelar)
    $exito = operacion_reserva();
    
    // Email es secundario, no bloquea
    try {
        enviar_email();
    } catch (Exception $e) {
        error_log("Email no enviado: " . $e->getMessage());
        // NO se lanza la excepción, continúa normal
    }
    
} catch (Exception $e) {
    // Solo falla si la operación principal falla
}
```

---

## 📈 Métricas y Monitoreo

### Logs a Revisar

```bash
# Ver errores de email en PHP error log
tail -f /var/log/php_errors.log | grep "Email"

# O en XAMPP/WAMP
C:\xampp\php\logs\php_error_log
```

### Qué Monitorear

- ✉️ Tasa de entrega de emails
- 📬 Emails que van a SPAM
- ⏱️ Tiempo de entrega
- 🔄 Bounces (emails rechazados)

---

## 🎯 Casos de Uso Reales

### Caso 1: Temporada Alta
```
100 reservas/día × 3 emails/reserva = 300 emails/día
```
**Recomendación:** Usar servicio SMTP profesional

### Caso 2: Desarrollo/Testing
```
5-10 reservas/día × 3 emails/reserva = 15-30 emails/día
```
**Recomendación:** Función `mail()` de PHP es suficiente

---

## 🔐 Seguridad

✅ **Implementado:**
- Emails NO contienen información sensible (contraseñas, datos bancarios)
- Validación de emails antes de enviar
- Protección contra inyección de headers
- Errores no expuestos al usuario final

⚠️ **Recomendaciones:**
- Agregar `.env` a `.gitignore` (✅ ya hecho)
- Usar variables de entorno en producción
- Rate limiting en producción (prevenir spam)

---

## 🚀 Próximas Mejoras (Opcionales)

1. **Recordatorios automáticos** (día antes de la reserva)
2. **Confirmación de llegada** (check-in por email)
3. **Encuesta de satisfacción** (después de la estancia)
4. **Notificaciones por SMS** (Twilio, Nexmo)
5. **Panel de estadísticas** de emails enviados

---

## 📞 Soporte

Si tienes problemas:

1. Revisa [README_EMAIL.md](README_EMAIL.md)
2. Ejecuta `test_email.php` para diagnosticar
3. Revisa los logs de PHP (`error_log`)
4. Verifica la configuración del servidor SMTP

---

**✨ Sistema 100% funcional y listo para producción ✨**
