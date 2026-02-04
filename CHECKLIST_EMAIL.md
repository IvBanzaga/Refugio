# ✅ Checklist de Configuración y Prueba - Sistema de Emails

## 📋 Pre-requisitos

- [ ] Servidor web funcionando (Apache/Nginx)
- [ ] PHP instalado (versión 7.4 o superior)
- [ ] Base de datos configurada
- [ ] Al menos 1 usuario socio creado
- [ ] Al menos 1 usuario admin creado

---

## 🔧 Paso 1: Configuración (5 minutos)

### 1.1 Editar config_email.php

- [ ] Abrir archivo: `config_email.php`
- [ ] Cambiar `ADMIN_EMAIL` por email real del administrador
- [ ] Cambiar `FROM_EMAIL` por email válido del sistema
- [ ] Cambiar `REFUGIO_NAME` por nombre real del refugio
- [ ] Guardar cambios

```php
define('ADMIN_EMAIL', '_______________@________.com');  // ✏️ COMPLETAR
define('FROM_EMAIL', '_______________@________.com');   // ✏️ COMPLETAR
define('REFUGIO_NAME', '_____________________');        // ✏️ COMPLETAR
```

### 1.2 Verificar archivos creados

- [ ] `config_email.php` existe ✅
- [ ] `email_notificaciones.php` existe ✅
- [ ] `test_email.php` existe ✅
- [ ] `README_EMAIL.md` existe ✅
- [ ] `.env.example` existe ✅

---

## 🧪 Paso 2: Prueba del Sistema (10 minutos)

### 2.1 Prueba de Envío Básico

- [ ] Abrir navegador
- [ ] Ir a: `http://localhost/refugio/test_email.php`
- [ ] Verificar que aparece la configuración actual
- [ ] Revisar resultados de los 3 tests:
  - [ ] Test 1: Email al Admin (Nueva Reserva) - ¿Éxito? ⬜
  - [ ] Test 2: Email al Socio (Aprobada) - ¿Éxito? ⬜
  - [ ] Test 3: Email al Socio (Cancelada) - ¿Éxito? ⬜

**Si ves ✅ en los 3 tests:** ¡Perfecto! Continúa al siguiente paso.  
**Si ves ❌:** Revisa la sección "Solución de Problemas" abajo.

### 2.2 Verificar Recepción de Emails

- [ ] Abrir bandeja de entrada del `ADMIN_EMAIL`
- [ ] Buscar email: "Nueva Solicitud de Reserva"
- [ ] Revisar carpeta de SPAM si no aparece
- [ ] Verificar que el email se ve correctamente (HTML)

---

## 🎭 Paso 3: Prueba de Flujo Completo (15 minutos)

### 3.1 Como Socio: Crear Reserva

- [ ] Iniciar sesión como socio
- [ ] Ir a "Nueva Reserva"
- [ ] Completar formulario:
  - Fecha entrada: ____________
  - Fecha salida: ____________
  - Número de camas: _____
  - Actividad: ___________________
- [ ] Enviar reserva
- [ ] Ver mensaje: "Reserva creada exitosamente" ✅

**Verificar:**
- [ ] Email llegó al administrador ✉️
- [ ] Email contiene datos correctos del socio
- [ ] Email contiene datos correctos de la reserva

### 3.2 Como Admin: Aprobar Reserva

- [ ] Cerrar sesión del socio
- [ ] Iniciar sesión como admin
- [ ] Ir a "Reservas" → "Pendientes"
- [ ] Encontrar la reserva recién creada
- [ ] Clic en botón "Aprobar"
- [ ] Ver mensaje: "Reserva aprobada exitosamente" ✅

**Verificar:**
- [ ] Email llegó al socio ✉️
- [ ] Email dice "Tu reserva ha sido aprobada"
- [ ] Email contiene datos correctos de la reserva
- [ ] El socio ve la reserva en "Aprobadas"

### 3.3 Como Admin: Cancelar Reserva (opcional)

- [ ] Ir a "Reservas" → "Aprobadas"
- [ ] Encontrar una reserva
- [ ] Clic en botón "Cancelar"
- [ ] Ver mensaje: "Reserva cancelada exitosamente" ✅

**Verificar:**
- [ ] Email llegó al socio ✉️
- [ ] Email dice "Tu reserva ha sido cancelada"
- [ ] Email contiene motivo de cancelación

---

## 🐛 Solución de Problemas

### ❌ Test muestra "Error al enviar email"

**Posibles causas:**

1. **Función mail() no configurada**
   ```bash
   # Verificar configuración de PHP
   php -i | grep sendmail
   ```
   - [ ] Editar `php.ini` y configurar sendmail
   - [ ] Reiniciar servidor web

2. **Email inválido en FROM_EMAIL**
   - [ ] Usar un email real del dominio
   - [ ] No usar emails de Gmail/Hotmail en FROM_EMAIL

3. **Servidor local sin SMTP**
   - [ ] Instalar sendmail (Linux)
   - [ ] Configurar SMTP en php.ini (Windows)

### 📧 Emails no llegan

**Verificar:**

- [ ] Revisar carpeta de SPAM
- [ ] Verificar que el email del socio esté en la BD
  ```sql
  SELECT email FROM usuarios WHERE id = X;
  ```
- [ ] Revisar logs de PHP:
  ```bash
  tail -f /var/log/php_errors.log
  ```

### 🎨 Emails se ven mal (sin formato)

**Posibles causas:**

- [ ] Cliente de email no soporta HTML
- [ ] Headers incorrectos
- [ ] Verificar que `Content-type:text/html` esté en headers

---

## 📊 Checklist de Producción

Antes de pasar a producción:

### Configuración
- [ ] Email del admin configurado correctamente
- [ ] Email del sistema (FROM_EMAIL) es del dominio
- [ ] URLs en `email_notificaciones.php` apuntan al dominio real
- [ ] Registros SPF configurados en el dominio
- [ ] Registros DKIM configurados (opcional pero recomendado)

### Seguridad
- [ ] Archivo `.env` agregado a `.gitignore` ✅
- [ ] `test_email.php` eliminado o protegido con contraseña
- [ ] No hay credenciales hardcodeadas en el código
- [ ] Logs de error no expuestos públicamente

### Testing
- [ ] Al menos 5 reservas de prueba completadas
- [ ] Emails recibidos y verificados
- [ ] Emails NO van a SPAM
- [ ] Diseño correcto en móvil y escritorio
- [ ] Todos los links funcionan correctamente

### Monitoreo
- [ ] Configurar alertas si los emails fallan
- [ ] Revisar logs semanalmente
- [ ] Tener plan B si el servidor de email falla

---

## ✅ Resultado Final

Una vez completado este checklist:

✨ **El sistema debe:**
- Enviar email al admin cuando un socio crea una reserva ✅
- Enviar email al socio cuando se aprueba su reserva ✅
- Enviar email al socio cuando se cancela su reserva ✅
- Manejar errores sin interrumpir el flujo normal ✅
- Tener diseño HTML profesional y responsive ✅

**Estado del sistema:** ⬜ Desarrollo | ⬜ Testing | ⬜ Producción

---

## 📝 Notas Adicionales

Fecha de configuración: _______________
Configurado por: _______________
Email de prueba usado: _______________
Problemas encontrados: 

_______________________________________
_______________________________________
_______________________________________

---

**🎉 ¡Felicidades! Sistema de notificaciones implementado y funcionando.**
