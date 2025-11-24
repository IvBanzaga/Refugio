# 📸 Funcionalidad de Foto de Perfil - Guía Completa

## 🎯 Resumen

Se ha implementado un sistema completo para que los usuarios registrados puedan **subir, cambiar y eliminar su foto de perfil** desde el panel de usuario.

---

## ✅ ¿Qué se agregó?

### 1️⃣ Base de Datos
- **Campo nuevo**: `foto_perfil` en tabla `usuarios`
- **Tipo**: VARCHAR(255) DEFAULT NULL
- **Ubicación**: Después del campo `password`

### 2️⃣ Archivos Nuevos

| Archivo | Descripción |
|---------|-------------|
| `subir_foto.php` | Endpoint AJAX para subir/eliminar fotos |
| `uploads/perfiles/` | Directorio para almacenar imágenes |
| `uploads/perfiles/.htaccess` | Protección de seguridad |
| `sql/actualizar_foto_perfil.sql` | Script para actualizar BD existente |
| `ACTUALIZACION_FOTO_PERFIL.md` | Guía de actualización detallada |

### 3️⃣ Archivos Modificados

| Archivo | Cambios |
|---------|---------|
| `sql/refugio_mysql.sql` | Agregado campo `foto_perfil` |
| `functions.php` | 5 funciones nuevas |
| `viewSocio.php` | Nueva sección "Mi Perfil" |

---

## 📋 Usuarios de Prueba

Recuerda que los usuarios de prueba ya creados son:

### 👤 **Usuario 1**
- **Email:** `user1@mail.com`
- **Password:** `user123`
- **Nombre:** Carlos Pérez Gómez
- **Nº Socio:** U001

### 👤 **Usuario 2**
- **Email:** `user2@mail.com`
- **Password:** `user123`
- **Nombre:** Lucía López Martín
- **Nº Socio:** U002

### 🔐 **Administrador**
- **Email:** `admin@hostel.com`
- **Password:** `admin123`
- **Nota:** Los administradores no tienen acceso a la sección de perfil (solo usuarios)

---

## 🚀 Instalación Nueva (Desde Cero)

Si estás instalando el sistema por primera vez:

```bash
# 1. Crear base de datos
mysql -u root -p
CREATE DATABASE refugio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;

# 2. Importar estructura (ya incluye foto_perfil)
mysql -u root -p refugio < sql\refugio_mysql.sql

# 3. Verificar instalación
php verificar_mysql.php

# 4. Iniciar servidor
php -S localhost:8000
```

**La columna `foto_perfil` ya está incluida** en el archivo `refugio_mysql.sql` actualizado.

---

## 🔄 Actualización (Sistema Existente)

Si ya tienes el sistema instalado:

### Método 1: Script automático
```bash
mysql -u root -p refugio < sql\actualizar_foto_perfil.sql
```

### Método 2: phpMyAdmin
1. Abre phpMyAdmin
2. Selecciona base de datos `refugio`
3. Pestaña **SQL**
4. Pega y ejecuta:
   ```sql
   ALTER TABLE usuarios 
   ADD COLUMN foto_perfil VARCHAR(255) DEFAULT NULL 
   AFTER password;
   ```

### Método 3: Línea de comandos
```bash
mysql -u root -p refugio
ALTER TABLE usuarios ADD COLUMN foto_perfil VARCHAR(255) DEFAULT NULL AFTER password;
exit;
```

---

## 🎮 Cómo Usar

### Para Usuarios:

1. **Iniciar sesión**
   ```
   http://localhost:8000
   Email: user1@mail.com
   Password: user123
   ```

2. **Ir a Mi Perfil**
   - En el menú lateral, clic en **"Mi Perfil"**

3. **Subir foto**
   - Clic en **"Seleccionar Foto"**
   - Elegir imagen (JPG, PNG o GIF)
   - Máximo 5MB
   - Se subirá automáticamente

4. **Cambiar foto**
   - Simplemente sube una nueva foto
   - La anterior se eliminará automáticamente

5. **Eliminar foto**
   - Clic en **"Eliminar Foto"**
   - Confirmar acción

---

## 🔒 Seguridad Implementada

✅ **Validación de sesión** - Solo usuarios autenticados  
✅ **Validación de tipo MIME** - No solo extensión  
✅ **Verificación real de imagen** - Con `getimagesize()`  
✅ **Límite de tamaño** - Máximo 5MB  
✅ **Formatos permitidos** - Solo JPG, PNG, GIF  
✅ **Nombres únicos** - `perfil_[ID]_[timestamp].[ext]`  
✅ **Protección .htaccess** - Bloquea scripts PHP en uploads/  
✅ **Eliminación automática** - Borra foto anterior  
✅ **Transacciones BD** - Rollback si falla guardado  

---

## 📁 Estructura de Archivos

```
Refugio/
│
├── uploads/                      # ✅ NUEVO
│   └── perfiles/                 # ✅ NUEVO
│       ├── .htaccess             # ✅ NUEVO - Seguridad
│       ├── index.html            # ✅ NUEVO - Previene listado
│       └── perfil_*.jpg          # Fotos subidas por usuarios
│
├── sql/
│   ├── refugio_mysql.sql         # ✅ ACTUALIZADO
│   └── actualizar_foto_perfil.sql # ✅ NUEVO
│
├── subir_foto.php                # ✅ NUEVO
├── functions.php                 # ✅ ACTUALIZADO
├── viewSocio.php                 # ✅ ACTUALIZADO
│
└── ACTUALIZACION_FOTO_PERFIL.md  # ✅ NUEVO - Guía detallada
```

---

## 🛠️ Funciones Agregadas

En `functions.php` se agregaron estas 5 funciones:

### 1. `validar_imagen($file)`
Valida formato, tamaño, extensión y tipo MIME real.

**Retorna:**
```php
['valido' => true/false, 'mensaje' => string, 'extension' => string]
```

### 2. `subir_foto_perfil($conexion, $id_usuario, $file)`
Sube la foto, la guarda en el servidor y actualiza la BD.

**Retorna:**
```php
['exito' => true/false, 'mensaje' => string, 'ruta' => string|null]
```

### 3. `obtener_foto_perfil($conexion, $id_usuario)`
Obtiene la ruta de la foto del usuario.

**Retorna:** `string|null`

### 4. `eliminar_foto_perfil($conexion, $id_usuario)`
Elimina la foto del servidor y de la BD.

**Retorna:**
```php
['exito' => true/false, 'mensaje' => string]
```

### 5. `obtener_info_usuario($conexion, $id_usuario)`
Obtiene toda la información del usuario incluyendo foto.

**Retorna:** `array|false`

---

## 🎨 Interfaz de Usuario

### Sección "Mi Perfil"

La nueva sección incluye:

#### 📷 Card de Foto de Perfil
- Vista previa circular (200x200px)
- Icono por defecto si no hay foto
- Botón "Seleccionar Foto"
- Botón "Eliminar Foto" (si existe)
- Mensajes de éxito/error

#### ℹ️ Card de Información Personal
- Número de Socio
- DNI
- Nombre completo
- Email
- Teléfono
- Nota: "Para modificar contacta al administrador"

---

## 🐛 Solución de Problemas

### ❌ "Error al guardar la imagen"

**Causa:** Permisos incorrectos

**Solución Windows:**
1. Clic derecho en carpeta `uploads/perfiles`
2. Propiedades > Seguridad
3. Dar permisos de escritura

**Solución Linux/Mac:**
```bash
chmod -R 755 uploads/perfiles
chown -R www-data:www-data uploads/perfiles  # Apache
# o
chown -R nginx:nginx uploads/perfiles         # Nginx
```

### ❌ "Formato no permitido"

**Causa:** Archivo no es imagen válida o formato incorrecto

**Solución:**
- Usa solo JPG, PNG o GIF
- Verifica que el archivo no esté corrupto
- Intenta con otra imagen

### ❌ La foto no se muestra

**Verificar:**
1. ¿Existe el archivo en `uploads/perfiles/`?
2. ¿La ruta en BD es correcta?
   ```sql
   SELECT id, email, foto_perfil FROM usuarios WHERE id = 2;
   ```
3. ¿Los permisos del directorio son correctos?

### ❌ Error 413: Request Entity Too Large

**Causa:** Límite del servidor menor a 5MB

**Solución:** Editar `php.ini`:
```ini
upload_max_filesize = 5M
post_max_size = 5M
memory_limit = 128M
```

Reiniciar servidor:
```bash
# XAMPP: Reiniciar desde panel de control
# Standalone: Detener y volver a iniciar servidor PHP
```

### ❌ "No autorizado"

**Causa:** Sesión expirada o no iniciada

**Solución:**
- Cerrar sesión y volver a iniciar
- Verificar que las cookies estén habilitadas

---

## 📊 Validaciones Implementadas

### Frontend (JavaScript)
- Detección de archivo seleccionado
- Feedback visual durante subida
- Confirmación antes de eliminar
- Recarga automática tras éxito

### Backend (PHP)

#### Validaciones de Seguridad:
1. ✅ Sesión activa (`$_SESSION['user_id']`)
2. ✅ Archivo subido (`$_FILES['foto']['error'] === UPLOAD_ERR_OK`)
3. ✅ Tamaño máximo 5MB
4. ✅ Tipo MIME permitido (image/jpeg, image/png, image/gif)
5. ✅ Extensión válida (jpg, jpeg, png, gif)
6. ✅ Verificación real con `getimagesize()`

#### Protección de Directorio (.htaccess):
```apache
# Solo permitir imágenes
<FilesMatch "\.(jpg|jpeg|png|gif)$">
    Order Allow,Deny
    Allow from all
</FilesMatch>

# Bloquear PHP
<FilesMatch "\.php$">
    Order Deny,Allow
    Deny from all
</FilesMatch>
```

---

## 🧪 Pruebas Recomendadas

### Test 1: Subir foto válida
1. Login como `user1@mail.com`
2. Ir a "Mi Perfil"
3. Subir imagen JPG de 1MB
4. ✅ Debe aparecer la foto

### Test 2: Cambiar foto
1. Subir otra imagen
2. ✅ La anterior debe eliminarse
3. ✅ Solo debe existir la nueva

### Test 3: Eliminar foto
1. Clic en "Eliminar Foto"
2. ✅ Debe aparecer icono por defecto
3. ✅ Archivo físico eliminado

### Test 4: Validación tamaño
1. Intentar subir imagen >5MB
2. ✅ Debe mostrar error

### Test 5: Validación formato
1. Intentar subir archivo .txt o .pdf
2. ✅ Debe mostrar error

### Test 6: Sin sesión
1. Cerrar sesión
2. Intentar acceder a `subir_foto.php` directamente
3. ✅ Debe mostrar "No autorizado"

---

## 📈 Mejoras Futuras (Opcional)

Ideas para versiones futuras:

- [ ] Recorte de imagen (crop) antes de subir
- [ ] Redimensionamiento automático a tamaño óptimo
- [ ] Múltiples tamaños (thumbnail, medium, large)
- [ ] Integración con CDN para almacenamiento
- [ ] Galería de avatares prediseñados
- [ ] Soporte para WebP (formato moderno)
- [ ] Preview antes de subir
- [ ] Drag & Drop para subir
- [ ] Editor de fotos básico (filtros, rotación)
- [ ] Compresión automática para optimizar tamaño

---

## 📞 Soporte

Si encuentras algún problema:

1. Revisa la sección "Solución de Problemas"
2. Verifica los logs de PHP: `php -S localhost:8000 2>&1 | tee server.log`
3. Revisa los logs de MySQL
4. Verifica permisos de archivos y directorios

---

## 📝 Notas Importantes

⚠️ **Importante:**
- Esta funcionalidad solo está disponible para usuarios con rol `'user'`
- Los administradores NO tienen acceso a la sección "Mi Perfil"
- Las fotos se almacenan localmente en el servidor
- El campo `foto_perfil` acepta `NULL` (usuarios sin foto)
- Las fotos se nombran con ID + timestamp para evitar conflictos

💡 **Recomendaciones:**
- Hacer backup antes de actualizar BD
- Probar primero en ambiente de desarrollo
- Configurar límites de PHP según necesidades
- Monitorear espacio en disco si hay muchos usuarios

---

**Versión:** 1.1.0  
**Fecha:** 23 de octubre de 2025  
**Autor:** Sistema de Gestión de Refugio
