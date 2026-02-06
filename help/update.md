# Actualizaciones del Sistema de Reservas - Refugio

## 📋 Cambios Implementados

### 1. Gestión de Reservas

#### 1.1 Ordenamiento de Reservas
- ✅ Tablas ordenables por fecha de entrada, fecha de salida y fecha de solicitud
- ✅ Encabezados clicables con iconos de ordenamiento bidireccional
- ✅ Por defecto ordenadas de la más antigua a la más reciente (por fecha de entrada)
- ✅ Alternancia entre orden ascendente y descendente con cada clic
- ✅ Aplica a reservas pendientes y aprobadas del usuario

#### 1.2 Acompañantes en Reservas
- ✅ Los usuarios pueden añadir datos de acompañantes cuando reservan más de una cama
- ✅ Validación automática del número de acompañantes requeridos
- ✅ Edición de acompañantes en reservas aprobadas (la reserva pasa a pendiente)

#### 1.3 Búsqueda de Socios Mejorada
- ✅ Búsqueda por teléfono o nombre
- ✅ Búsqueda por fechas en formato DD/MM/YYYY
- ✅ Conversión automática de formato DD/MM/YYYY a YYYY-MM-DD

#### 1.4 Campo Actividad/Motivo a Realizar
- ✅ Campo obligatorio para describir la actividad durante la estancia
- ✅ Aplica a todas las reservas (socios, no socios y especiales)
- ✅ Columna "Motivo" visible en tablas de pendientes y aprobadas del usuario
- ✅ Muestra "Sin especificar" si no hay actividad registrada

#### 1.5 Fechas de Entrada y Salida
- ✅ Permitido que fecha de entrada y salida sean iguales
- ✅ Aplica a usuarios y administradores

#### 1.6 Límite de Reservas para Usuarios
- ✅ Máximo 2 noches (3 días consecutivos) para reservas de socios
- ✅ Validación en servidor (PHP) al crear y editar reservas
- ✅ Validación en cliente (JavaScript) con mensaje de advertencia en rojo y negrita
- ✅ El administrador puede crear reservas sin límite de días

#### 1.7 Visualización de Información Completa
- ✅ Columnas en tablas de usuario (pendientes y aprobadas):
  - Nº Camas
  - Fecha Entrada
  - Fecha Salida
  - Motivo (actividad)
  - Días (duración)
  - Solicitado (fecha de creación)
  - Acciones

---

### 2. Reservas de No Socios

#### 2.1 Campos Adicionales
- ✅ Email obligatorio al crear reserva de no socio
- ✅ Campos: nombre, apellidos, DNI, teléfono, email

#### 2.2 Grupo de Montañeros
- ✅ Campo para especificar grupo de montañeros
- ✅ Por defecto: "Grupo de Montañeros de Tenerife" (GMT)
- ✅ Opción para especificar otro grupo o asociación
- ✅ Si no pertenece a ningún grupo, dejar en blanco

#### 2.3 Atributo "Montañero"
- ✅ Nuevo campo después de "Actividad"
- ✅ Si es GMT → muestra "GMT"
- ✅ Si es otro grupo → muestra el nombre del grupo
- ✅ Si no tiene grupo → muestra "Otro"
- ✅ Los usuarios registrados por defecto son GMT

---

### 3. Reservas Especiales

#### 3.1 Opciones de Asignación
- ✅ Opción 1: Grupo de Montañeros de Tenerife (GMT)
- ✅ Opción 2: Asignar a un socio específico (con búsqueda/selección de lista)
- ✅ Opción 3: Otro grupo o asociación (checkbox con campo opcional)
- ✅ Opción 4: Asignar a un no socio (agregar datos del responsable)

#### 3.2 Información en Edición
- ✅ Al editar, muestra teléfono y email
- ✅ Aplica a reservas de socios, no socios y especiales

---

### 4. Gestión de Usuarios

#### 4.1 Email Obligatorio
- ✅ Campo email obligatorio al crear nuevo socio
- ✅ Validación en todos los formularios

#### 4.2 Cambio de Contraseña
- ✅ Los usuarios pueden modificar su contraseña desde su perfil
- ✅ Interfaz intuitiva en la sección de perfil

#### 4.3 Importación de Usuarios CSV
- ✅ Opción en Dashboard del administrador → Usuarios
- ✅ Formato CSV con columnas: num_socio, nombre, apellido1, apellido2, dni, email, telefono
- ✅ Rol por defecto: `user`
- ✅ Contraseña por defecto: DNI sin letra
- ✅ Sistema de mapeo de columnas (asignar nombre si no coinciden)
- ✅ Auto-detección de nombres de columnas
- ✅ Vista previa antes de importar

---

### 5. Interfaz de Usuario

#### 5.1 Dashboard Principal
- ✅ Botones de gestión de reservas en la parte superior:
  - Nueva Reserva Socio
  - Nueva Reserva No Socio
  - Nueva Reserva Especial

#### 5.2 Visualización de Detalles
- ✅ Botón "Ver detalles" (👁️) en reservas pendientes
- ✅ Botón "Ver detalles" (👁️) en reservas aprobadas
- ✅ Modal con información completa del solicitante y acompañantes
- ✅ Botón "Editar" (✏️) en ambas secciones

#### 5.3 Mensajes Informativos
- ✅ Reservas Pendientes: "Puedes editar fecha y acompañantes o anular tus reservas"
- ✅ Reservas Aprobadas: "Puedes editar el número de acompañantes y la reserva pasará a pendiente o anular tus reservas aprobadas que aún no han comenzado. La anulación no se puede deshacer"
- ✅ Validación de 3 días: Mensaje en rojo y negrita cuando se excede el límite

#### 5.4 Selección de Fechas
- ✅ Integración con Flatpickr para selector de fechas
- ✅ API de fechas completas (api/fechas_completas.php)
- ✅ API de disponibilidad total (api/disponibilidad_total.php)
- ✅ Validación en tiempo real de fechas disponibles
- ✅ Advertencia visual cuando se seleccionan más de 3 días consecutivos

---

### 6. Seguridad

#### 6.1 reCAPTCHA
- ✅ Implementado reCAPTCHA v2 en el login
- ✅ Validación en servidor

#### 6.2 Gestión de Sesiones
- ✅ Verificación condicional de `session_start()`
- ✅ Sin conflictos de redeclaración de funciones
- ✅ Regeneración de ID de sesión para mayor seguridad

#### 6.3 Configuración de Entorno
- ✅ Archivo .env para variables de entorno sensibles
- ✅ config/env.php para cargar variables antes de bootstrap
- ✅ BASE_URL configurable desde .env
- ✅ Credenciales SMTP en .env

---

### 7. Sistema de Camas

#### 7.1 Simplificación
- ✅ Eliminadas las habitaciones individuales
- ✅ Sistema basado únicamente en número de camas
- ✅ Total de camas disponibles: 26

---

### 8. Notificaciones por Email

#### 8.1 Email al Administrador
- ✅ Notificación cuando un socio solicita una nueva reserva
- ✅ Incluye: datos del socio y detalles de la reserva

#### 8.2 Email al Socio
- ✅ Notificación cuando su reserva es aprobada
- ✅ Incluye: datos completos de la reserva aprobada
- ✅ Notificación cuando el administrador crea una reserva en su nombre
- ✅ Configuración SMTP desde variables de entorno

---

### 9. Edición de Reservas

#### 9.1 Reservas Aprobadas
- ✅ Los usuarios pueden editar reservas aprobadas para agregar/modificar acompañantes
- ✅ Al editar, la reserva pasa automáticamente a estado "pendiente"
- ✅ Requiere nueva aprobación del administrador
- ✅ Solo se pueden editar reservas que aún no han comenzado
- ✅ Mensaje informativo claro sobre el cambio a pendiente

#### 9.2 Reservas Pendientes
- ✅ Los usuarios pueden editar fecha de entrada y salida
- ✅ Pueden agregar, modificar o eliminar acompañantes
- ✅ Validación de número de acompañantes según camas reservadas
- ✅ Validación de límite de 3 días para usuarios
- ✅ Mensaje informativo sobre las acciones disponibles

#### 9.3 Información Completa
- ✅ Muestra teléfono y email en todos los modales de edición
- ✅ Datos completos del solicitante y acompañantes
- ✅ Vista previa de cambios antes de guardar

---

## 🔧 Correcciones de Errores Recientes

### 10.1 Login y Autenticación
- ✅ Corregido error "Cannot redeclare function comprobar_username()"
- ✅ Eliminado bootstrap.php de login.php para evitar conflictos
- ✅ Corregido bucle infinito de redirección (ERR_TOO_MANY_REDIRECTS)
- ✅ Formulario de login embebido directamente en login.php

### 10.2 API y Rutas
- ✅ Corregidos paths de API: api/fechas_completas.php
- ✅ Corregidos paths de API: api/disponibilidad_total.php
- ✅ Error 404 resuelto en llamadas fetch de JavaScript
- ✅ Flatpickr inicializa correctamente con fechas bloqueadas

### 10.3 Configuración BASE_URL
- ✅ BASE_URL actualizado de http://localhost/refugio a http://localhost:3000
- ✅ config/env.php carga .env antes que otros archivos de configuración
- ✅ Variables de entorno disponibles globalmente mediante $_ENV

---

## 🎯 Estado del Proyecto

**Última actualización:** 6 de febrero de 2026  
**Rama actual:** `feature/update`  
**Estado:** ✅ Todas las funcionalidades implementadas y probadas

## 📝 Notas Técnicas

### Arquitectura
- Sistema híbrido: Legacy PHP + inicio de estructura MVC
- PDO para conexiones a base de datos
- Bootstrap 5 para UI
- Flatpickr para selectores de fecha
- PHPMailer para notificaciones por email

### Validaciones
- Cliente (JavaScript): Validación inmediata de fechas y acompañantes
- Servidor (PHP): Validación definitiva antes de guardar en BD
- Límite de 3 días solo para usuarios, administrador sin límite

### Ordenamiento
- JavaScript con data-attributes para ordenamiento preciso
- Almacenamiento de dirección de ordenamiento por tabla y columna
- Actualización dinámica de iconos de ordenamiento