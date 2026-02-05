# Reglas de Carga de Archivos

## ⚠️ IMPORTANTE: Evitar Conflictos de Redeclaración

El proyecto tiene dos sistemas paralelos:
- **Sistema Legacy**: usa `functions.php` (raíz)
- **Sistema MVC**: usa `src/Helpers/functions.php`

Ambos archivos contienen funciones con los mismos nombres, por lo que **NUNCA** deben cargarse simultáneamente.

## Reglas de Carga

### 1. Archivos que usan Bootstrap (Sistema MVC)

Si un archivo carga `config/bootstrap.php`, **NO DEBE** cargar `functions.php`:

```php
// ✅ CORRECTO
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/conexion.php';
// NO cargar functions.php aquí

// ❌ INCORRECTO
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/functions.php'; // ❌ CAUSA CONFLICTO
```

**Archivos afectados:**
- `login.php`
- `viewAdminMVC.php`
- `viewSocioMVC.php`
- `api/check_availability.php`
- Cualquier archivo nuevo que use bootstrap

### 2. Archivos Legacy (Sistema antiguo)

Los archivos legacy que **NO** cargan bootstrap, pueden cargar `functions.php`:

```php
// ✅ CORRECTO (sistema legacy)
require_once 'conexion.php';
require_once 'functions.php';
```

**Archivos legacy:**
- `viewAdmin.php`
- `viewSocio.php`
- `api/disponibilidad.php`
- `api/fechas_completas.php`
- `api/subir_foto.php`
- `api/disponibilidad_total.php`

### 3. Archivos de Configuración

**conexion.php:**
- NO carga ningún archivo de funciones
- Solo maneja la conexión PDO e inicia sesión

**config/bootstrap.php:**
- Carga automáticamente `src/Helpers/functions.php`
- Carga configuraciones (app.php, database.php, email.php)
- Carga autoload de Composer

### 4. Sesiones

Siempre usar verificación condicional para `session_start()`:

```php
// ✅ CORRECTO
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ❌ INCORRECTO
session_start(); // Puede causar "session already active"
```

## Checklist al Crear Nuevos Archivos

Cuando crees un archivo nuevo, verifica:

- [ ] ¿Necesita funciones? → Elige **UNA** opción:
  - Sistema MVC: carga `bootstrap.php` → NO cargar `functions.php`
  - Sistema Legacy: carga `functions.php` → NO cargar `bootstrap.php`
  
- [ ] ¿Necesita base de datos? → Carga `conexion.php`

- [ ] ¿Inicia sesión? → Usa verificación condicional

## Errores Comunes

### Error: "Cannot redeclare function X"

**Causa:** Se están cargando ambos archivos de funciones.

**Solución:**
1. Identifica si el archivo usa bootstrap
2. Si usa bootstrap, elimina `require.*functions.php`
3. Si no usa bootstrap, asegúrate de que no se cargue indirectamente

### Error: "session already active"

**Causa:** `session_start()` se llama sin verificar.

**Solución:** Usa verificación condicional:
```php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

## Migración Futura

Cuando el sistema MVC esté completo y se elimine el sistema legacy:
1. Eliminar `functions.php` de la raíz
2. Mantener solo `src/Helpers/functions.php`
3. Actualizar todos los `require` restantes
4. Eliminar esta documentación (ya no será necesaria)
