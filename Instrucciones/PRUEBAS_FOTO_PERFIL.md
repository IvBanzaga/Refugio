# 🧪 Instrucciones de Prueba - Foto de Perfil

## 📋 Checklist de Pruebas

Usa esta guía para verificar que la funcionalidad de foto de perfil funciona correctamente.

---

## ✅ Preparación

### Paso 1: Verificar instalación
```bash
# Verificar que existe el directorio
dir uploads\perfiles

# Debe mostrar:
# - .htaccess
# - index.html
```

### Paso 2: Verificar base de datos
```sql
USE refugio;
DESCRIBE usuarios;

-- Debe aparecer:
-- foto_perfil | varchar(255) | YES | NULL
```

### Paso 3: Iniciar servidor
```bash
php -S localhost:8000
```

---

## 🧪 Prueba 1: Login y Acceso al Perfil

### Acciones:
1. Abrir navegador: `http://localhost:8000`
2. Iniciar sesión con:
   - Email: `user1@mail.com`
   - Password: `user123`
3. Verificar que aparece el menú lateral
4. Hacer clic en **"Mi Perfil"**

### Resultado esperado:
✅ Debe mostrar la sección de perfil con:
- Card de foto con icono por defecto (persona)
- Botón "Seleccionar Foto"
- Card de información personal con datos del usuario

---

## 🧪 Prueba 2: Subir Primera Foto

### Preparación:
- Tener una imagen JPG de prueba (< 5MB)
- Recomendado: usar una foto cuadrada

### Acciones:
1. Clic en **"Seleccionar Foto"**
2. Elegir la imagen
3. Esperar mensaje de éxito

### Resultado esperado:
✅ Debe mostrar:
- Mensaje: "Foto actualizada correctamente"
- La página se recarga automáticamente
- Aparece la foto en lugar del icono
- La foto está en formato circular

### Verificar en BD:
```sql
SELECT id, email, foto_perfil FROM usuarios WHERE email = 'user1@mail.com';

-- Debe mostrar algo como:
-- foto_perfil: uploads/perfiles/perfil_2_1729706400.jpg
```

### Verificar archivo físico:
```bash
dir uploads\perfiles\perfil_2_*.jpg

# Debe existir el archivo
```

---

## 🧪 Prueba 3: Cambiar Foto

### Acciones:
1. Anotar el nombre del archivo actual
2. Clic en **"Seleccionar Foto"**
3. Elegir una imagen DIFERENTE
4. Esperar confirmación

### Resultado esperado:
✅ Debe:
- Mostrar la nueva foto
- Eliminar la foto anterior del servidor
- Solo debe existir 1 archivo en `uploads/perfiles/`

### Verificar:
```bash
dir uploads\perfiles\perfil_2_*.jpg

# Debe mostrar solo 1 archivo con timestamp diferente
```

---

## 🧪 Prueba 4: Eliminar Foto

### Acciones:
1. Clic en botón **"Eliminar Foto"** (rojo)
2. Confirmar en el diálogo
3. Esperar mensaje

### Resultado esperado:
✅ Debe:
- Mostrar mensaje: "Foto eliminada correctamente"
- Volver a mostrar icono por defecto
- Eliminar archivo del servidor
- Botón "Eliminar Foto" desaparece

### Verificar en BD:
```sql
SELECT id, email, foto_perfil FROM usuarios WHERE email = 'user1@mail.com';

-- Debe mostrar:
-- foto_perfil: NULL
```

### Verificar archivo:
```bash
dir uploads\perfiles\perfil_2_*.jpg

# No debe encontrar archivos
```

---

## 🧪 Prueba 5: Validación de Tamaño

### Preparación:
- Necesitas una imagen > 5MB

### Acciones:
1. Intentar subir imagen grande
2. Observar mensaje de error

### Resultado esperado:
❌ Debe mostrar error:
- "El archivo es demasiado grande (máximo 5MB)"
- NO se sube el archivo
- La foto anterior (si existe) permanece

---

## 🧪 Prueba 6: Validación de Formato

### Acciones:
1. Intentar subir archivo .txt, .pdf o .docx
2. Observar mensaje de error

### Resultado esperado:
❌ Debe mostrar error:
- "Formato no permitido. Solo JPG, PNG o GIF"
- NO se sube el archivo

---

## 🧪 Prueba 7: Múltiples Usuarios

### Acciones:
1. Cerrar sesión
2. Login con `user2@mail.com` / `user123`
3. Ir a "Mi Perfil"
4. Subir una foto diferente

### Resultado esperado:
✅ Debe:
- Cada usuario tiene su propia foto
- Los archivos tienen nombres únicos (perfil_2_xxx.jpg y perfil_3_xxx.jpg)
- Las fotos NO se mezclan entre usuarios

### Verificar:
```bash
dir uploads\perfiles\

# Debe mostrar:
# perfil_2_xxxxx.jpg  (user1)
# perfil_3_xxxxx.jpg  (user2)
```

---

## 🧪 Prueba 8: Seguridad - Sin Sesión

### Acciones:
1. Cerrar sesión (logout)
2. Intentar acceder directamente a:
   ```
   http://localhost:8000/subir_foto.php
   ```

### Resultado esperado:
❌ Debe mostrar:
```json
{"exito":false,"mensaje":"No autorizado"}
```

---

## 🧪 Prueba 9: Seguridad - Archivo PHP

### Acciones:
1. Crear archivo de prueba `test.php` en `uploads/perfiles/`
2. Intentar acceder:
   ```
   http://localhost:8000/uploads/perfiles/test.php
   ```

### Resultado esperado:
❌ Debe mostrar error 403 Forbidden (bloqueado por .htaccess)

---

## 🧪 Prueba 10: Diferentes Formatos

### Acciones:
Probar subir:
- ✅ Imagen JPG
- ✅ Imagen PNG
- ✅ Imagen GIF

### Resultado esperado:
✅ Todos los formatos deben funcionar correctamente

---

## 🧪 Prueba 11: Panel Admin

### Acciones:
1. Login como `admin@hostel.com` / `admin123`
2. Verificar menú lateral

### Resultado esperado:
✅ Los administradores NO deben ver la opción "Mi Perfil"
- Esta funcionalidad es exclusiva para usuarios con rol 'user'

---

## 🧪 Prueba 12: Responsive

### Acciones:
1. Login como usuario
2. Ir a "Mi Perfil"
3. Redimensionar ventana del navegador
4. Probar en modo móvil (F12 > Toggle Device Toolbar)

### Resultado esperado:
✅ El diseño debe adaptarse:
- En desktop: 2 columnas (foto | info)
- En móvil: 1 columna apilada
- Foto siempre circular
- Botones accesibles

---

## 📊 Resumen de Pruebas

| # | Prueba | Estado | Notas |
|---|--------|--------|-------|
| 1 | Acceso al perfil | ⬜ | |
| 2 | Subir primera foto | ⬜ | |
| 3 | Cambiar foto | ⬜ | |
| 4 | Eliminar foto | ⬜ | |
| 5 | Validación tamaño | ⬜ | |
| 6 | Validación formato | ⬜ | |
| 7 | Múltiples usuarios | ⬜ | |
| 8 | Seguridad sin sesión | ⬜ | |
| 9 | Seguridad archivo PHP | ⬜ | |
| 10 | Diferentes formatos | ⬜ | |
| 11 | Panel admin | ⬜ | |
| 12 | Responsive | ⬜ | |

**Leyenda:**
- ⬜ Pendiente
- ✅ Pasó
- ❌ Falló

---

## 🐛 Reporte de Bugs

Si encuentras algún problema, documenta:

```
PRUEBA #: [número]
DESCRIPCIÓN: [qué estabas haciendo]
RESULTADO ESPERADO: [qué debería pasar]
RESULTADO ACTUAL: [qué pasó realmente]
PASOS PARA REPRODUCIR:
  1. [paso 1]
  2. [paso 2]
  3. [paso 3]
ERROR (si aplica): [mensaje de error]
NAVEGADOR: [Chrome/Firefox/Edge/etc.]
SISTEMA: [Windows/Linux/Mac]
```

---

## ✅ Pruebas Completas

Una vez que todas las pruebas pasen exitosamente:

1. ✅ La funcionalidad está lista para producción
2. 📝 Documenta cualquier configuración especial necesaria
3. 🎉 ¡El sistema de fotos de perfil está funcionando!

---

**Última actualización:** 23 de octubre de 2025  
**Versión:** 1.1.0
