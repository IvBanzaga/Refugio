# Organización de Archivos del Proyecto Refugio

## 📁 Estructura Correcta del Proyecto

```
Refugio/
├── 📂 api/                          # Endpoints API REST y AJAX
│   ├── check_availability.php      # Verificar disponibilidad de camas
│   ├── disponibilidad.php          # API de disponibilidad general
│   ├── disponibilidad_total.php    # Disponibilidad total del refugio
│   ├── fechas_completas.php        # Fechas con ocupación completa
│   ├── email_notificaciones.php    # Envío de notificaciones por email
│   └── subir_foto.php              # Upload de fotos de perfil
│
├── 📂 config/                       # Configuración de la aplicación
│   ├── bootstrap.php               # Inicialización y autoload
│   ├── config.php                  # Variables de configuración
│   └── Database.php                # Clase Singleton de conexión
│
├── 📂 src/                          # Código fuente MVC
│   ├── 📂 Controllers/              # Controladores de lógica de negocio
│   │   ├── ReservaController.php   # Gestión de reservas
│   │   └── UsuarioController.php   # Gestión de usuarios
│   │
│   ├── 📂 Models/                   # Modelos de datos
│   │   ├── Usuario.php             # Modelo de usuario
│   │   ├── Reserva.php             # Modelo de reserva
│   │   └── Habitacion.php          # Modelo de habitación
│   │
│   └── 📂 Services/                 # Servicios auxiliares
│       └── EmailService.php        # Servicio de envío de emails
│
├── 📂 views/                        # Vistas de presentación
│   ├── 📂 layouts/                  # Plantillas base
│   │   ├── layout-admin.php        # Layout para administrador
│   │   └── layout-socio.php        # Layout para usuario
│   │
│   ├── 📂 partials/                 # Componentes reutilizables
│   │   ├── 📂 headers/
│   │   │   ├── header-admin.php
│   │   │   └── header-socio.php
│   │   ├── 📂 sidebars/
│   │   │   ├── sidebar-admin.php
│   │   │   └── sidebar-socio.php
│   │   ├── 📂 modals/
│   │   │   ├── modal-usuario.php
│   │   │   ├── modal-reserva-socio.php
│   │   │   ├── modal-reserva-no-socio.php
│   │   │   ├── modal-reserva-especial.php
│   │   │   └── modal-editar-reserva.php
│   │   ├── footer.php
│   │   └── flash-messages.php
│   │
│   ├── 📂 admin/                    # Vistas de administrador
│   │   ├── dashboard.php
│   │   ├── usuarios.php
│   │   └── reservas.php
│   │
│   ├── 📂 socio/                    # Vistas de usuario
│   │   ├── calendario.php
│   │   ├── mis-reservas.php
│   │   ├── nueva-reserva.php
│   │   └── perfil.php
│   │
│   └── 📂 auth/                     # Vistas de autenticación
│       └── login.php
│
├── 📂 public/                       # Archivos públicos estáticos
│   ├── 📂 css/                      # Estilos personalizados
│   ├── 📂 js/                       # JavaScript personalizado
│   └── 📂 images/                   # Imágenes y recursos
│
├── 📂 utils/                        # Utilidades y scripts auxiliares
│   ├── generar_hashes.php          # Generar hashes de contraseñas
│   ├── test_email.php              # Probar configuración de email
│   └── verificar_mysql.php         # Verificar conexión MySQL
│
├── 📂 docs/                         # Documentación del proyecto
│   ├── PROGRESO_FASE3.md
│   ├── PROGRESO_FASE4.md
│   ├── PROGRESO_FASE5.md
│   ├── RESUMEN_EJECUTIVO.md
│   └── ORGANIZACION_ARCHIVOS.md    # Este archivo
│
├── 📂 sql/                          # Scripts de base de datos
│   └── refugio.sql                 # Schema y datos iniciales
│
├── 📂 vendor/                       # Dependencias de Composer
│   └── (PHPMailer, etc.)
│
├── 📂 actualizacion/                # Scripts de migración/actualización
│
├── 📂 help/                         # Archivos de ayuda
│
├── 📂 assets/                       # Assets del proyecto
│
├── 📄 Archivos MVC Principales (Raíz)
├── viewAdminMVC.php                # Controlador frontal admin (MVC)
├── viewSocioMVC.php                # Controlador frontal usuario (MVC)
├── login.php                       # Página de login (MVC)
├── logout.php                      # Cierre de sesión
├── index.php                       # Página de inicio
│
├── 📄 Archivos Legacy (Raíz - No Tocar)
├── viewAdmin.php                   # Admin panel legacy (BACKUP)
├── viewSocio.php                   # User panel legacy (BACKUP)
├── auth.php                        # Auth legacy (BACKUP)
│
├── 📄 Archivos de Compatibilidad (Raíz - Necesarios)
├── conexion.php                    # Conexión DB (usado por legacy y MVC)
├── functions.php                   # Funciones compartidas (usado por legacy y MVC)
│                                   # ⚠️ Estos deben estar en raíz porque el código legacy
│                                   # los busca aquí y cambiarlos rompería compatibilidad
│
├── 📄 Configuración del Proyecto
├── .env                            # Variables de entorno (no en git)
├── .env.example                    # Ejemplo de variables de entorno
├── composer.json                   # Dependencias PHP
├── composer.lock                   # Lock de versiones
├── .gitignore                      # Archivos ignorados por git
├── .htaccess                       # Configuración Apache
├── LICENSE                         # Licencia del proyecto
├── README.md                       # Documentación principal
├── ESTRUCTURA.md                   # Documentación de estructura
└── favicon.svg                     # Favicon del sitio

```

## 🎯 Principios de Organización

### 1. **Separación por Capas**
- **api/**: Endpoints que devuelven JSON (AJAX)
- **src/**: Lógica de negocio (MVC backend)
- **views/**: Presentación HTML (MVC frontend)
- **public/**: Recursos estáticos accesibles públicamente
- **utils/**: Scripts de utilidad no accesibles por web

### 2. **Agrupación por Funcionalidad**
- Archivos relacionados juntos en la misma carpeta
- Modales en `views/partials/modals/`
- Controladores en `src/Controllers/`
- APIs en `api/`

### 3. **Archivos Legacy Intactos**
Los siguientes archivos NO deben modificarse (son backup):
- ✅ `viewAdmin.php` (3,578 líneas)
- ✅ `viewSocio.php` (1,948 líneas)
- ✅ `auth.php`

### 4. **Archivos de Compatibilidad en Raíz**
Estos archivos DEBEN permanecer en la raíz por razones técnicas:

#### **`conexion.php`** (Conexión a Base de Datos)
- ❗ **Por qué en raíz:** El código legacy (viewAdmin.php, viewSocio.php) usa `require 'conexion.php'` sin ruta
- También usado por archivos MVC, APIs y utilidades
- Moverlo rompería la compatibilidad con legacy
- **Solución:** Los archivos en subcarpetas usan `require __DIR__ . '/../conexion.php'`

#### **`functions.php`** (Funciones Compartidas)
- ❗ **Por qué en raíz:** Similar a conexion.php, el legacy lo busca en raíz
- Contiene funciones usadas por TODO el sistema (legacy + MVC)
- Migrarlas causaría duplicación y problemas de compatibilidad
- **Solución:** Los archivos en subcarpetas usan `require __DIR__ . '/../functions.php'`

#### **`composer.json`** (Gestión de Dependencias)
- ❗ **Por qué en raíz:** **Estándar obligatorio de Composer**
- Composer SIEMPRE busca este archivo en la raíz del proyecto
- No es negociable, es parte del estándar PHP moderno

#### **Archivos MVC Principales**
Estos sí deben estar en raíz porque son los puntos de entrada de la aplicación:
- `viewAdminMVC.php` - Controlador frontal del panel admin
- `viewSocioMVC.php` - Controlador frontal del panel usuario
- `login.php` - Punto de entrada de autenticación
- `logout.php` - Cierre de sesión
- `index.php` - Landing page del sitio

### 4. **Rutas Relativas**
Desde las vistas, las rutas deben ser relativas al documento raíz:
```php
// Desde views/socio/nueva-reserva.php
../../api/check_availability.php      // ✅ Correcto
check_availability.php                 // ❌ Incorrecto

// Desde viewSocioMVC.php (raíz)
api/check_availability.php             // ✅ Correcto
```

## 📊 Cambios Realizados

### Archivos Movidos a `api/`
- ✅ `check_availability.php` → `api/check_availability.php`
- ✅ `disponibilidad.php` → `api/disponibilidad.php`
- ✅ `email_notificaciones.php` → `api/email_notificaciones.php`
- ✅ `subir_foto.php` → `api/subir_foto.php`
- ✅ `fechas_completas.php` → `api/fechas_completas.php` (ya estaba)
- ✅ `disponibilidad_total.php` → `api/disponibilidad_total.php` (ya estaba)

### Archivos Movidos a `utils/`
- ✅ `generar_hashes.php` → `utils/generar_hashes.php`
- ✅ `test_email.php` → `utils/test_email.php`
- ✅ `verificar_mysql.php` → `utils/verificar_mysql.php`

### Archivos Actualizados
- ✅ `views/socio/nueva-reserva.php` - Ruta de API actualizada

## 🔍 Cómo Encontrar Archivos

### Si necesitas...

**Agregar un nuevo endpoint API:**
→ Crear archivo en `api/nombre_endpoint.php`

**Crear un nuevo controlador:**
→ Crear archivo en `src/Controllers/NombreController.php`

**Crear un nuevo modelo:**
→ Crear archivo en `src/Models/NombreModelo.php`

**Crear una nueva vista:**
→ Crear archivo en `views/admin/` o `views/socio/`

**Crear un componente reutilizable:**
→ Crear archivo en `views/partials/` o `views/partials/modals/`

**Agregar JavaScript personalizado:**
→ Crear archivo en `public/js/script.js`

**Agregar CSS personalizado:**
→ Crear archivo en `public/css/style.css`

**Script de utilidad (no web):**
→ Crear archivo en `utils/nombre_script.php`

## ⚠️ Reglas Importantes

### ✅ SI HACER
1. Colocar endpoints API en `api/`
2. Usar rutas relativas correctas
3. Mantener archivos legacy intactos
4. Agrupar archivos por funcionalidad
5. Documentar nuevos archivos

### ❌ NO HACER
1. Modificar archivos legacy (viewAdmin.php, viewSocio.php)
2. Dejar archivos sueltos en la raíz (excepto MVC principales)
3. Mezclar lógica de negocio con presentación
4. Duplicar código en múltiples lugares
5. Crear archivos sin documentar

## 🚀 Próximos Pasos

### Fase 6: PSR-4 y Autoloading
Una vez completada la Fase 6, la estructura será:
```php
// En lugar de:
require_once __DIR__ . '/src/Controllers/ReservaController.php';

// Usaremos:
use App\Controllers\ReservaController;
$controller = new ReservaController($conexion);
```

### Limpieza Final
Después de la migración completa:
1. Mover archivos legacy a `archive/`
2. Renombrar `viewAdminMVC.php` → `viewAdmin.php`
3. Renombrar `viewSocioMVC.php` → `viewSocio.php`

## 📚 Referencias

- [PROGRESO_FASE3.md](./PROGRESO_FASE3.md) - Sistema de vistas
- [PROGRESO_FASE4.md](./PROGRESO_FASE4.md) - Integración con legacy
- [PROGRESO_FASE5.md](./PROGRESO_FASE5.md) - Controladores y lógica
- [RESUMEN_EJECUTIVO.md](./RESUMEN_EJECUTIVO.md) - Visión general

---

**Última actualización:** Fase 5 completada - Organización de archivos  
**Estado:** Estructura MVC correctamente organizada ✅
