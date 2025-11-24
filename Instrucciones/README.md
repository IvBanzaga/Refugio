# 🏔️ Sistema de Control de Camas - Refugio del Club

Sistema completo para la gestión y reserva de camas en un refugio de montaña.

## 📋 Requisitos

- **MySQL** 5.7+ o **MariaDB** 10.3+ (también compatible con PostgreSQL)
- **PHP** 7.4 o superior con extensión PDO para MySQL
- **Servidor Web** (Apache/Nginx) o PHP Built-in Server
- **Navegador Web** moderno

## 🚀 Instalación Rápida (MySQL)

### 1. Configurar la Base de Datos

#### Opción A: Con XAMPP/WAMP (Recomendado)
```bash
# Abrir phpMyAdmin: http://localhost/phpmyadmin
# Crear base de datos: refugio
# Importar archivo: sql/refugio_mysql.sql
```

#### Opción B: Línea de comandos
```bash
# Crear la base de datos
mysql -u root -p
CREATE DATABASE refugio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Importar el esquema
mysql -u root -p refugio < sql/refugio_mysql.sql
```

### 2. Configurar la Conexión

Editar el archivo `conexion.php` y ajustar las credenciales:

```php
$host     = "localhost";
$port     = "3306";
$dbname   = "refugio";
$username = "root";      // Tu usuario de MySQL
$password = "tu_password"; // Tu contraseña (vacía en XAMPP por defecto)
```

### 3. Verificar Configuración (Opcional pero recomendado)

```bash
php verificar_mysql.php
```

Este script verificará que todo está configurado correctamente.
$host     = "localhost";
$port     = "5432";
$dbname   = "refugio";
$username = "postgres";  // Tu usuario de PostgreSQL
$password = "tu_password"; // Tu contraseña
```

### 3. Iniciar el Servidor

#### Con PHP Built-in Server (Desarrollo):
```bash
cd f:\Proyectos\Refugio
php -S localhost:8000
```

Acceder a: http://localhost:8000

#### Con XAMPP/WAMP:
1. Copiar la carpeta del proyecto a `htdocs/` o `www/`
2. Acceder a: http://localhost/Refugio

## 👥 Usuarios de Prueba

### Administrador
- **Email:** admin@hostel.com
- **Contraseña:** admin123

### Usuario Normal
- **Email:** user1@mail.com
- **Contraseña:** user123

## 📱 Funcionalidades

### Panel Administrador (`viewAdmin.php`)

#### Dashboard
- Estadísticas generales (habitaciones, camas, reservas pendientes)
- Lista de reservas pendientes de aprobación
- Estado de ocupación de habitaciones

#### Gestión de Usuarios
- ✅ Listar todos los usuarios
- ✅ Crear nuevos usuarios (admin o user)
- ✅ Editar usuarios existentes
- ✅ Eliminar usuarios
- ✅ Contraseñas hasheadas con bcrypt

#### Gestión de Reservas
- ✅ Ver todas las reservas (pendientes, aprobadas, canceladas)
- ✅ Aprobar reservas pendientes
- ✅ Rechazar/cancelar reservas
- ✅ Información detallada de cada reserva

### Panel Usuario (`viewSocio.php`)

#### Calendario de Disponibilidad
- 📅 Vista mensual interactiva
- 🟢 Indicador visual de camas disponibles por día
- 🟡 Alerta de pocas camas disponibles
- 🔴 Días sin disponibilidad
- ⏮️ Navegación entre meses

#### Nueva Reserva
- 📆 Selección de fecha de entrada y salida
- 🛏️ Selección de cama según disponibilidad
- 📝 Campo para describir actividad a realizar
- 👥 Gestión de acompañantes:
  - Indicar si es socio o no
  - DNI, nombre y apellidos
  - Número de socio (si aplica)
- 💬 Sección de comentarios adicionales

#### Mis Reservas
- 📊 Vista de reservas pendientes de aprobación
- ✅ Vista de reservas aprobadas
- ❌ Historial de reservas canceladas
- 🗑️ Opción de cancelar reservas pendientes

#### Mi Perfil 🆕
- 📸 Subir foto de perfil
- 🔄 Cambiar foto existente
- 🗑️ Eliminar foto de perfil
- 👤 Visualización de información personal
- 🔒 Formatos permitidos: JPG, PNG, GIF (máx. 5MB)

## 🏗️ Estructura de la Base de Datos

### Tablas Principales

1. **usuarios**
   - Información de usuarios (socios)
   - Roles: admin, user
   - Contraseñas hasheadas con bcrypt
   - Foto de perfil (opcional) 🆕

2. **habitaciones**
   - 4 habitaciones con diferentes capacidades
   - Total: 26 camas

3. **camas**
   - Estados: libre, pendiente, reservada
   - Asociadas a habitaciones

4. **reservas**
   - Estados: pendiente, reservada, cancelada
   - Fechas de inicio y fin
   - Relación con usuario y cama

5. **acompanantes**
   - Datos de acompañantes por reserva
   - Campo para indicar si es socio
   - Actividad a realizar

## 🔐 Seguridad

- ✅ Contraseñas hasheadas con `password_hash()` (bcrypt)
- ✅ Verificación con `password_verify()`
- ✅ Protección contra SQL Injection (PDO preparadas)
- ✅ Protección XSS (`htmlspecialchars`)
- ✅ Regeneración de ID de sesión
- ✅ Cookies HttpOnly
- ✅ Validación de roles en cada página

## 📝 Flujo de Trabajo

### Para Usuarios:
1. Login con email y contraseña
2. Ver calendario de disponibilidad
3. Crear nueva reserva seleccionando fechas y cama
4. Agregar acompañantes y detalles
5. Esperar aprobación del administrador
6. Ver estado de reservas

### Para Administradores:
1. Login con credenciales de admin
2. Dashboard con resumen de actividad
3. Gestionar usuarios (crear, editar, eliminar)
4. Revisar reservas pendientes
5. Aprobar o rechazar reservas
6. Monitorear ocupación de habitaciones

## 🛠️ Solución de Problemas

### Error de conexión a PostgreSQL
- Verificar que PostgreSQL esté ejecutándose
- Comprobar credenciales en `conexion.php`
- Verificar extensión PDO PostgreSQL: `php -m | grep pdo_pgsql`

### Error "Call to undefined function password_hash()"
- Actualizar PHP a versión 5.5 o superior

### Las camas no se muestran disponibles
- Verificar que las fechas estén bien formateadas
- Comprobar el archivo `disponibilidad.php`
- Revisar la consola del navegador para errores AJAX

## 📄 Archivos Principales

```
Refugio/
├── conexion.php                    # Conexión a BD MySQL
├── functions.php                   # Funciones principales
├── index.php                      # Redirección al login
├── login.php                      # Página de autenticación
├── logout.php                     # Cierre de sesión
├── viewAdmin.php                  # Panel administrador
├── viewSocio.php                  # Panel usuario
├── disponibilidad.php             # API AJAX para camas
├── subir_foto.php                 # API para fotos de perfil 🆕
├── uploads/
│   └── perfiles/                  # Fotos de perfil 🆕
├── sql/
│   ├── refugio_mysql.sql          # Esquema MySQL
│   ├── refugio.sql                # Esquema PostgreSQL
│   └── actualizar_foto_perfil.sql # Script actualización 🆕
└── docs/
    ├── GUIA_FOTO_PERFIL.md        # Guía completa fotos 🆕
    └── ACTUALIZACION_FOTO_PERFIL.md # Guía actualización 🆕
```

## 🎨 Tecnologías Utilizadas

- **Backend:** PHP 7.4+ con PDO
- **Base de Datos:** MySQL 5.7+ / MariaDB 10.3+ (también PostgreSQL 12+)
- **Frontend:** Bootstrap 5.3.2
- **Icons:** Bootstrap Icons 1.11.1
- **JavaScript:** Vanilla JS (AJAX para subida de fotos) 🆕
- **Seguridad:** Bcrypt, PDO Prepared Statements, MIME validation 🆕

## 🆕 Novedades - Versión 1.1.0

### Funcionalidad de Foto de Perfil
- Los usuarios ahora pueden subir, cambiar y eliminar su foto de perfil
- Validación completa de seguridad (tipo MIME, tamaño, formato)
- Almacenamiento local en `uploads/perfiles/`
- Interfaz intuitiva con vista previa circular
- Documentación completa en `GUIA_FOTO_PERFIL.md`

### ¿Ya tienes el sistema instalado?
Si ya tenías una versión anterior, consulta `ACTUALIZACION_FOTO_PERFIL.md` para actualizar tu base de datos.

```bash
# Actualización rápida
mysql -u root -p refugio < sql/actualizar_foto_perfil.sql
```

## 📞 Soporte

Para reportar problemas o sugerencias, contactar con el equipo de desarrollo.

---

**Versión:** 1.0  
**Última actualización:** Octubre 2025
