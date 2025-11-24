# Actualización: Agregar Foto de Perfil

## 📋 ¿Qué se ha agregado?

Se ha implementado la funcionalidad para que los usuarios puedan subir y gestionar su **foto de perfil** desde el panel de usuario.

## 🔧 Cambios realizados

### 1. Base de Datos
- Se agregó la columna `foto_perfil` a la tabla `usuarios`
- Esta columna almacena la ruta relativa de la imagen

### 2. Nuevos archivos
- `subir_foto.php` - Endpoint para procesar la subida y eliminación de fotos
- `uploads/perfiles/` - Directorio para almacenar las fotos de perfil

### 3. Archivos modificados
- `sql/refugio_mysql.sql` - Actualizado con el nuevo campo
- `functions.php` - Agregadas 5 nuevas funciones para gestionar fotos
- `viewSocio.php` - Agregada sección "Mi Perfil" con gestor de fotos

## 🚀 Actualización para usuarios existentes

Si ya tienes el sistema instalado, debes actualizar tu base de datos:

### Opción 1: Ejecutar SQL directo

Abre phpMyAdmin (o tu gestor MySQL) y ejecuta:

```sql
ALTER TABLE usuarios 
ADD COLUMN foto_perfil VARCHAR(255) DEFAULT NULL 
AFTER password;
```

### Opción 2: Desde línea de comandos

```bash
mysql -u root -p refugio
```

Y luego ejecuta:
```sql
ALTER TABLE usuarios ADD COLUMN foto_perfil VARCHAR(255) DEFAULT NULL AFTER password;
exit;
```

### Opción 3: Reinstalar base de datos

⚠️ **Esto borrará todos los datos existentes**

```bash
# Respaldar datos primero (opcional)
mysqldump -u root -p refugio > respaldo_refugio.sql

# Eliminar y recrear
mysql -u root -p
DROP DATABASE refugio;
CREATE DATABASE refugio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;

# Importar nueva estructura
mysql -u root -p refugio < sql\refugio_mysql.sql
```

## ✅ Verificar instalación

1. **Verificar directorio**:
   ```bash
   # Debe existir la carpeta
   uploads/perfiles/
   ```

2. **Verificar permisos** (Linux/Mac):
   ```bash
   chmod 755 uploads/perfiles
   ```

3. **Verificar columna en BD**:
   ```sql
   DESCRIBE usuarios;
   ```
   Deberías ver la columna `foto_perfil` de tipo `varchar(255)`

## 🎯 Cómo usar la nueva funcionalidad

1. Inicia sesión como usuario (no admin)
2. Ve a la sección **"Mi Perfil"** en el menú lateral
3. Haz clic en **"Seleccionar Foto"**
4. Elige una imagen (JPG, PNG o GIF, máximo 5MB)
5. La foto se subirá automáticamente

### Eliminar foto
- En la sección "Mi Perfil", haz clic en **"Eliminar Foto"**

## 📌 Características

✅ Validación de formato (JPG, PNG, GIF)  
✅ Validación de tamaño (máximo 5MB)  
✅ Validación de tipo MIME y extensión  
✅ Nombres únicos para evitar conflictos  
✅ Eliminación automática de foto anterior al subir nueva  
✅ Protección del directorio uploads con .htaccess  
✅ Vista previa circular con estilos Bootstrap  
✅ Subida AJAX sin recargar página  

## 🔒 Seguridad implementada

- ✅ Verificación de sesión activa
- ✅ Validación de tipo MIME real (no solo extensión)
- ✅ Validación con `getimagesize()` para confirmar que es imagen
- ✅ Nombres de archivo únicos (previene sobreescritura)
- ✅ .htaccess que bloquea ejecución de PHP en uploads/
- ✅ Límite de tamaño 5MB
- ✅ Solo formatos permitidos: JPG, PNG, GIF

## 🐛 Solución de problemas

### Error: "No se puede guardar la imagen"
- Verifica permisos del directorio `uploads/perfiles/`
- En Windows: Clic derecho > Propiedades > Seguridad
- En Linux/Mac: `chmod 755 uploads/perfiles`

### Error: "Formato no permitido"
- Solo se aceptan JPG, PNG y GIF
- Verifica que el archivo no esté corrupto

### La imagen no aparece
- Verifica la ruta en la base de datos
- Comprueba que el archivo físico existe en `uploads/perfiles/`
- Revisa los permisos del directorio

### Error 413: Payload Too Large
- Tu servidor tiene un límite menor a 5MB
- Edita `php.ini`:
  ```ini
  upload_max_filesize = 5M
  post_max_size = 5M
  ```

## 📊 Estructura de archivos

```
Refugio/
├── uploads/
│   └── perfiles/
│       ├── .htaccess           # Protección
│       ├── index.html          # Previene listado
│       └── perfil_1_*.jpg      # Fotos subidas
├── sql/
│   └── refugio_mysql.sql       # ✅ Actualizado
├── functions.php               # ✅ Actualizado
├── viewSocio.php              # ✅ Actualizado
└── subir_foto.php             # ✅ Nuevo
```

## 📚 Funciones agregadas a functions.php

1. `validar_imagen($file)` - Valida formato, tamaño y tipo
2. `subir_foto_perfil($conexion, $id_usuario, $file)` - Sube y guarda foto
3. `obtener_foto_perfil($conexion, $id_usuario)` - Obtiene ruta de foto
4. `eliminar_foto_perfil($conexion, $id_usuario)` - Elimina foto
5. `obtener_info_usuario($conexion, $id_usuario)` - Info completa del usuario

## 🎨 Interfaz de usuario

La sección "Mi Perfil" incluye:

- **Card de Foto**: Vista previa circular, botones para subir/eliminar
- **Card de Información**: Datos personales del usuario (solo lectura)
- **Alertas**: Mensajes de éxito/error con auto-cierre
- **Responsive**: Diseño adaptable a móviles

---

**Fecha de actualización:** 23 de octubre de 2025  
**Versión:** 1.1.0
