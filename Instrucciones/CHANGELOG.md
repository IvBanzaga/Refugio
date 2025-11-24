# 📝 Changelog

Todos los cambios notables en este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere a [Versionado Semántico](https://semver.org/lang/es/).

---

## [1.0.0] - 2025-10-23

### 🎉 Lanzamiento Inicial

Primera versión funcional completa del Sistema de Control de Camas del Refugio.

### ✨ Añadido

#### Base de Datos
- Esquema PostgreSQL completo con tipos ENUM nativos
- Esquema MySQL alternativo para compatibilidad
- Tabla `usuarios` con roles (admin, user)
- Tabla `habitaciones` (4 habitaciones, 26 camas total)
- Tabla `camas` con estados (libre, pendiente, reservada)
- Tabla `reservas` con estados (pendiente, reservada, cancelada)
- Tabla `acompanantes` para gestión de acompañantes
- Datos de prueba con 3 usuarios predefinidos
- Contraseñas hasheadas con bcrypt

#### Autenticación y Seguridad
- Sistema de login con email y contraseña
- Autenticación con `password_hash()` y `password_verify()`
- Protección contra SQL Injection con PDO prepared statements
- Protección XSS con `htmlspecialchars()`
- Regeneración de ID de sesión para prevenir fijación
- Cookies HttpOnly para mayor seguridad
- Validación de roles en cada página
- Sistema de logout seguro

#### Panel Administrador (viewAdmin.php)
- Dashboard con estadísticas generales
- Gestión completa de usuarios (CRUD):
  - Crear nuevos usuarios (admin o user)
  - Editar usuarios existentes
  - Eliminar usuarios
  - Cambio de contraseñas con hash
- Gestión de reservas:
  - Lista de reservas pendientes de aprobación
  - Lista de reservas aprobadas
  - Aprobar reservas
  - Rechazar/cancelar reservas
- Visualización de estado de habitaciones
- Interfaz moderna con Bootstrap 5
- Diseño responsive

#### Panel Usuario (viewSocio.php)
- Calendario interactivo de disponibilidad:
  - Vista mensual con navegación
  - Indicadores visuales de camas disponibles
  - Código de colores (verde: muchas, amarillo: pocas, rojo: ninguna)
  - Días pasados deshabilitados
- Formulario de nueva reserva:
  - Selección de fecha de entrada/salida
  - Selección dinámica de cama según disponibilidad
  - Campo para describir actividad
  - Gestión de acompañantes con opción socio/no socio
  - Campo de comentarios
- Mis Reservas:
  - Vista de reservas pendientes
  - Vista de reservas aprobadas
  - Vista de reservas canceladas
  - Opción de cancelar reservas pendientes

#### Funciones del Sistema (functions.php)
- `comprobar_username()` - Autenticación de usuarios
- `listar_usuarios()` - Listar todos los usuarios
- `obtener_usuario()` - Obtener usuario por ID
- `crear_usuario()` - Crear nuevo usuario con hash
- `actualizar_usuario()` - Actualizar datos de usuario
- `eliminar_usuario()` - Eliminar usuario
- `listar_habitaciones()` - Listar habitaciones con estadísticas
- `obtener_disponibilidad()` - Disponibilidad de camas por rango
- `contar_camas_libres_por_fecha()` - Contar camas libres
- `listar_reservas()` - Listar reservas con filtros
- `obtener_reserva()` - Obtener reserva con acompañantes
- `crear_reserva()` - Crear nueva reserva
- `actualizar_estado_reserva()` - Cambiar estado de reserva
- `cancelar_reserva()` - Cancelar reserva
- `obtener_acompanantes()` - Listar acompañantes
- `agregar_acompanante()` - Añadir acompañante
- `eliminar_acompanante()` - Eliminar acompañante
- `sanitize_input()` - Sanitizar entradas
- `formatear_fecha()` - Formatear fechas
- `fecha_en_rango()` - Validar rangos de fechas

#### API y AJAX
- `disponibilidad.php` - API para obtener camas disponibles
- Actualización dinámica de camas disponibles en formulario
- Respuestas JSON para integración frontend

#### Archivos de Configuración
- `conexion.php` - Conexión PDO a PostgreSQL/MySQL
- `config.example.php` - Plantilla de configuración
- `.gitignore` - Exclusión de archivos sensibles

#### Documentación
- `README.md` - Documentación completa del sistema
- `INICIO_RAPIDO.md` - Guía de instalación rápida (10 minutos)
- `MEJORAS_FUTURAS.md` - Roadmap de funcionalidades
- `RESUMEN_PROYECTO.md` - Resumen ejecutivo del proyecto
- `CHECKLIST_DESPLIEGUE.md` - Lista de verificación para producción
- `CONTRIBUTING.md` - Guía para contribuidores
- `CHANGELOG.md` - Este archivo

#### Assets
- `assets/css/style.css` - Estilos personalizados
- `assets/js/utils.js` - Utilidades JavaScript
- Animaciones y transiciones CSS
- Funciones auxiliares JS

#### Utilidades
- `update_passwords.php` - Script para hashear contraseñas
- `logout.php` - Cierre de sesión seguro

---

## [Unreleased] - Próximas Versiones

### 🔮 Planificado para v1.1.0
- Sistema de notificaciones por email
- Exportación de reservas a PDF
- Dashboard con gráficos estadísticos
- Búsqueda avanzada de reservas
- Filtros mejorados en listados

### 🔮 Planificado para v1.2.0
- Recuperación de contraseña
- Autenticación de dos factores (2FA)
- API REST completa
- App móvil (iOS/Android)

### 🔮 Planificado para v2.0.0
- Sistema de pagos
- Multi-idioma
- Tema oscuro
- Integración con Google Calendar

---

## Tipos de Cambios

- `Añadido` - Para nuevas funcionalidades
- `Cambiado` - Para cambios en funcionalidades existentes
- `Deprecado` - Para funcionalidades que serán eliminadas
- `Eliminado` - Para funcionalidades eliminadas
- `Corregido` - Para corrección de bugs
- `Seguridad` - En caso de vulnerabilidades

---

## Versionado

Este proyecto usa [SemVer](http://semver.org/) para el versionado:

- **MAJOR** (X.0.0): Cambios incompatibles con versiones anteriores
- **MINOR** (0.X.0): Nueva funcionalidad compatible con versiones anteriores
- **PATCH** (0.0.X): Correcciones de bugs compatibles

---

## Enlaces

- [Repositorio](https://github.com/tu-usuario/refugio) (ejemplo)
- [Issues](https://github.com/tu-usuario/refugio/issues)
- [Pull Requests](https://github.com/tu-usuario/refugio/pulls)

---

**Última actualización:** 23 de Octubre de 2025
