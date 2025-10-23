# ✅ Checklist de Despliegue - Sistema Refugio

Este checklist te guiará paso a paso para desplegar el sistema en producción de forma segura.

---

## 📋 PRE-DESPLIEGUE (Desarrollo)

### Instalación Inicial
- [ ] PostgreSQL instalado y funcionando
- [ ] PHP 7.4+ instalado con extensión pdo_pgsql
- [ ] Base de datos creada: `CREATE DATABASE refugio;`
- [ ] Esquema importado: `psql -U postgres -d refugio -f sql/refugio.sql`
- [ ] Archivo `conexion.php` configurado con credenciales correctas
- [ ] Servidor de desarrollo funcionando: `php -S localhost:8000`
- [ ] Login con usuarios de prueba funciona
- [ ] Panel admin accesible y funcional
- [ ] Panel usuario accesible y funcional

### Pruebas Funcionales
- [ ] Crear usuario desde panel admin
- [ ] Editar usuario existente
- [ ] Eliminar usuario de prueba
- [ ] Crear reserva como usuario
- [ ] Aprobar reserva como admin
- [ ] Rechazar reserva como admin
- [ ] Calendario muestra disponibilidad correctamente
- [ ] Agregar acompañantes a una reserva
- [ ] Cancelar reserva como usuario
- [ ] Verificar que las camas se actualizan correctamente

---

## 🔐 SEGURIDAD PRE-PRODUCCIÓN

### Credenciales
- [ ] **CRÍTICO:** Cambiar contraseña del admin por defecto
- [ ] **CRÍTICO:** Crear usuarios reales (eliminar usuarios de prueba)
- [ ] Generar contraseñas fuertes (mínimo 12 caracteres)
- [ ] Documentar credenciales en gestor de contraseñas seguro

### Configuración de BD
- [ ] Usuario de BD específico para la aplicación (no usar 'postgres')
- [ ] Contraseña de BD fuerte y única
- [ ] Permisos mínimos necesarios para el usuario de BD
- [ ] Backup automático configurado
- [ ] Script de restauración probado

### Archivos Sensibles
- [ ] **CRÍTICO:** Eliminar `update_passwords.php`
- [ ] Eliminar archivos de prueba/desarrollo
- [ ] Configurar `.gitignore` correctamente
- [ ] No versionar `conexion.php` (usar ejemplo)
- [ ] No versionar archivos de configuración con credenciales

---

## 🌐 CONFIGURACIÓN DEL SERVIDOR

### Servidor Web (Apache/Nginx)
- [ ] Virtual host configurado
- [ ] Directorio raíz apuntando a la carpeta del proyecto
- [ ] PHP-FPM configurado (si aplica)
- [ ] Permisos de archivos correctos (644 para archivos, 755 para directorios)
- [ ] Propietario correcto (www-data o similar)

### PHP
- [ ] `php.ini` configurado correctamente:
  - [ ] `display_errors = Off`
  - [ ] `log_errors = On`
  - [ ] `error_log = /ruta/logs/php-error.log`
  - [ ] `session.cookie_httponly = 1`
  - [ ] `session.cookie_secure = 1` (si HTTPS)
  - [ ] `session.use_strict_mode = 1`
  - [ ] `expose_php = Off`
  - [ ] `upload_max_filesize` adecuado
  - [ ] `post_max_size` adecuado

### Base de Datos
- [ ] PostgreSQL accesible solo desde localhost (o IP específica)
- [ ] Puerto no estándar (opcional, mayor seguridad)
- [ ] Logs de PostgreSQL habilitados
- [ ] `pg_hba.conf` configurado correctamente
- [ ] Backups automáticos programados

---

## 🔒 HTTPS Y CERTIFICADOS

### SSL/TLS
- [ ] Certificado SSL instalado (Let's Encrypt recomendado)
- [ ] Certificado válido y no expirado
- [ ] Redirección HTTP → HTTPS configurada
- [ ] HSTS header configurado
- [ ] Verificar en https://www.ssllabs.com/ssltest/

### Headers de Seguridad
```apache
# Agregar a .htaccess o configuración de servidor
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
Header set Referrer-Policy "strict-origin-when-cross-origin"
Header set Content-Security-Policy "default-src 'self'"
```

- [ ] Headers de seguridad configurados
- [ ] Verificar en https://securityheaders.com/

---

## 📝 MODIFICACIONES DEL CÓDIGO

### Archivo `conexion.php`
```php
// CAMBIAR EN PRODUCCIÓN:
$conexionPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// POR:
// $conexionPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
```
- [ ] Errores de PDO no se muestran en producción

### Debug Mode
- [ ] `error_reporting(0)` en producción (o nivel apropiado)
- [ ] Eliminar todos los `var_dump()` y `print_r()`
- [ ] Logs redirigidos a archivos, no a pantalla

### Sesiones
```php
// En conexion.php, agregar:
ini_set('session.cookie_secure', '1');      // Solo HTTPS
ini_set('session.cookie_httponly', '1');    // No accesible desde JS
ini_set('session.use_strict_mode', '1');    // IDs seguros
ini_set('session.cookie_samesite', 'Strict'); // CSRF protection
```
- [ ] Configuración de sesiones seguras implementada

---

## 🚀 DESPLIEGUE

### Copiar Archivos
- [ ] Archivos subidos al servidor
- [ ] Permisos correctos aplicados
- [ ] Propietario correcto asignado
- [ ] Estructura de directorios verificada

### Configuración
- [ ] `conexion.php` con credenciales de producción
- [ ] Rutas absolutas correctas
- [ ] Zonas horarias configuradas
- [ ] Logs habilitados y funcionando

### Base de Datos
- [ ] Importar esquema en servidor de producción
- [ ] Verificar que todas las tablas existen
- [ ] Crear usuario administrador de producción
- [ ] Eliminar usuarios de prueba

### Testing en Producción
- [ ] Login funciona
- [ ] Crear usuario
- [ ] Crear reserva
- [ ] Aprobar reserva
- [ ] Calendario funciona
- [ ] AJAX funciona (disponibilidad.php)
- [ ] Logout funciona
- [ ] Sesiones expiran correctamente

---

## 📊 MONITOREO Y LOGS

### Logs
- [ ] Directorio de logs creado y con permisos
- [ ] PHP error log activo
- [ ] PostgreSQL logs activos
- [ ] Web server logs (access/error) activos
- [ ] Rotación de logs configurada

### Monitoreo
- [ ] Uptime monitoring configurado (UptimeRobot, Pingdom, etc.)
- [ ] Alertas de caída configuradas
- [ ] Alertas de espacio en disco
- [ ] Alertas de uso de CPU/RAM
- [ ] Backup monitoring

---

## 🔄 BACKUPS

### Base de Datos
```bash
# Script de backup diario
#!/bin/bash
pg_dump -U usuario -d refugio > /backups/refugio_$(date +%Y%m%d).sql
```

- [ ] Script de backup de BD creado
- [ ] Cron job programado (diario mínimo)
- [ ] Backups probados (restauración)
- [ ] Backups almacenados fuera del servidor
- [ ] Retención de backups definida (ej: 30 días)

### Archivos
- [ ] Backup de código y archivos
- [ ] Backup de configuraciones
- [ ] Backup de logs importantes
- [ ] Sincronización con almacenamiento remoto

---

## 📧 NOTIFICACIONES (Opcional pero Recomendado)

### Email
- [ ] Servidor SMTP configurado
- [ ] Email de notificaciones configurado
- [ ] Plantillas de email creadas
- [ ] Prueba de envío de emails

### Notificaciones del Sistema
- [ ] Email al admin cuando hay nueva reserva
- [ ] Email al usuario cuando su reserva es aprobada/rechazada
- [ ] Email de recordatorio 24h antes de entrada

---

## 📱 OPTIMIZACIÓN

### Performance
- [ ] OPcache habilitado en PHP
- [ ] Compresión gzip habilitada
- [ ] Caché de navegador configurado
- [ ] Imágenes optimizadas (si las hay)
- [ ] CSS/JS minificado (si aplica)

### Base de Datos
- [ ] Índices verificados
- [ ] VACUUM ANALYZE ejecutado
- [ ] Estadísticas actualizadas
- [ ] Pool de conexiones configurado (si aplica)

---

## 📄 DOCUMENTACIÓN

### Para el Cliente/Usuario Final
- [ ] Manual de usuario creado
- [ ] Credenciales entregadas de forma segura
- [ ] Contacto de soporte definido
- [ ] FAQs creadas

### Para Mantenimiento
- [ ] Documentación técnica actualizada
- [ ] Diagrama de BD actualizado
- [ ] Procedimientos de backup documentados
- [ ] Procedimientos de recuperación documentados
- [ ] Contactos de emergencia definidos

---

## ✅ POST-DESPLIEGUE

### Verificación Final
- [ ] Sistema accesible desde internet
- [ ] HTTPS funciona correctamente
- [ ] Todos los enlaces funcionan
- [ ] No hay errores en logs
- [ ] Performance aceptable
- [ ] Backups funcionando

### Entrega
- [ ] Cliente informado de la URL
- [ ] Credenciales entregadas de forma segura
- [ ] Manual de usuario entregado
- [ ] Sesión de capacitación realizada (opcional)
- [ ] Soporte post-lanzamiento acordado

### Seguimiento
- [ ] Monitoreo activo primeras 48 horas
- [ ] Revisar logs diariamente primera semana
- [ ] Verificar backups diariamente
- [ ] Recopilar feedback de usuarios

---

## 🚨 CONTINGENCIAS

### Plan de Rollback
- [ ] Backup completo antes del despliegue
- [ ] Procedimiento de rollback documentado
- [ ] Versión anterior disponible
- [ ] DNS TTL bajo durante despliegue

### Contactos de Emergencia
- [ ] Contacto técnico principal: ___________
- [ ] Contacto técnico backup: ___________
- [ ] Proveedor de hosting: ___________
- [ ] DBA (si aplica): ___________

---

## 📝 NOTAS FINALES

**Fecha de despliegue planificada:** ___/___/_____

**Responsable del despliegue:** _________________

**Ventana de mantenimiento:** De _____:_____ a _____:_____

**Rollback trigger:** Si [condición] entonces ejecutar rollback

---

## ✨ ¡SISTEMA EN PRODUCCIÓN!

Una vez completado este checklist, el sistema estará listo para producción y uso real.

**Recuerda:**
- 🔐 La seguridad es continua, no puntual
- 📊 Monitorea regularmente
- 🔄 Mantén backups actualizados
- 📚 Documenta los cambios
- 🆘 Ten un plan de contingencia

---

**¡Éxito con el despliegue!** 🚀
