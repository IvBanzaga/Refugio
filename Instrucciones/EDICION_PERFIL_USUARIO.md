# 📝 Actualización: Edición de Perfil para Usuarios

## ✅ Nueva Funcionalidad Implementada

Los usuarios ahora pueden **modificar su email y teléfono** directamente desde la sección "Mi Perfil".

---

## 🎯 ¿Qué pueden editar los usuarios?

### ✅ **Campos EDITABLES:**
- **Email** - Usado para iniciar sesión (se valida que no esté en uso)
- **Teléfono** - Opcional, validación de 9-15 dígitos

### 🔒 **Campos NO EDITABLES:**
- Número de Socio
- DNI
- Nombre completo (Nombre, Apellido1, Apellido2)

---

## 📋 Características de la Edición

### Validaciones:
- ✅ Email válido (formato correcto)
- ✅ Email único (no puede estar en uso por otro usuario)
- ✅ Teléfono opcional (9-15 dígitos si se proporciona)
- ✅ Actualización de sesión automática si cambia el email

### Seguridad:
- ✅ Solo el usuario autenticado puede editar su propio perfil
- ✅ Sanitización de datos con `sanitize_input()`
- ✅ Prepared statements para evitar SQL injection
- ✅ Mensajes de éxito/error claros

---

## 🎨 Interfaz de Usuario

### Sección "Mi Perfil"
La sección ahora incluye:

1. **Datos NO editables** (en gris con etiqueta "No editable")
   - Número de Socio
   - DNI
   - Nombre completo

2. **Separador visual** con texto "Datos Editables"

3. **Formulario editable**
   - Campo Email (requerido)
   - Campo Teléfono (opcional)
   - Botón "Guardar Cambios"

4. **Alerta informativa**
   - Indica qué campos son editables
   - Sugiere contactar al admin para otros cambios

---

## 🔧 Archivos Modificados

### 1. `functions.php`
Nueva función agregada:
```php
actualizar_perfil_usuario($conexion, $id_usuario, $email, $telf)
```

**Características:**
- Valida que el email no esté en uso
- Actualiza ambos campos en la BD
- Retorna array con éxito/mensaje

### 2. `viewSocio.php`
**Procesamiento POST:**
- Nuevo case `'actualizar_perfil'`
- Actualiza sesión si cambia el email
- Muestra mensajes de éxito/error

**HTML actualizado:**
- Formulario completo con campos editables
- Validación HTML5 (email, tel pattern)
- Diseño mejorado con Bootstrap 5

---

## 🧪 Cómo Probar

### Paso 1: Login como usuario
```
Email: user1@mail.com
Password: user123
```

### Paso 2: Ir a "Mi Perfil"
- Clic en el menú lateral

### Paso 3: Editar datos
- Cambiar el email (ej: `carlos.nuevo@mail.com`)
- Cambiar el teléfono (ej: `655444333`)
- Clic en "Guardar Cambios"

### Paso 4: Verificar
- Debe mostrar mensaje: "Perfil actualizado correctamente"
- Los nuevos datos deben aparecer en el formulario
- Si cambió el email, úsalo para el siguiente login

---

## ⚠️ Validaciones y Errores

### Error: "El email ya está en uso"
**Causa:** Otro usuario tiene ese email

**Solución:** Usar un email diferente

### Error: "Email inválido"
**Causa:** Formato incorrecto (HTML5 validation)

**Solución:** Usar formato correcto (usuario@dominio.com)

### Error: "Teléfono inválido"
**Causa:** No cumple patrón 9-15 dígitos

**Solución:** Usar solo números, entre 9 y 15 dígitos

---

## 📊 Comparación Antes/Después

| Aspecto | Antes | Después |
|---------|-------|---------|
| Email | Solo lectura | ✅ Editable |
| Teléfono | Solo lectura | ✅ Editable |
| Nombre | Solo lectura | ❌ Solo lectura |
| DNI | Solo lectura | ❌ Solo lectura |
| Validación email único | No aplicaba | ✅ Implementada |
| Actualización sesión | No | ✅ Automática |

---

## 🎯 Casos de Uso

### Caso 1: Usuario cambia de email
1. Edita el email en "Mi Perfil"
2. Guarda cambios
3. Cierra sesión
4. Inicia sesión con el nuevo email

### Caso 2: Usuario actualiza teléfono
1. Edita el teléfono (puede dejarlo vacío)
2. Guarda cambios
3. El teléfono se actualiza sin afectar el login

### Caso 3: Usuario intenta usar email existente
1. Intenta cambiar a email de otro usuario
2. Sistema muestra error
3. Email no se actualiza
4. Usuario debe elegir otro email

---

## 💡 Recomendaciones

### Para Usuarios:
- ✅ Usa un email válido y que revises regularmente
- ✅ El teléfono es opcional pero recomendado
- ✅ Si cambias el email, anótalo para no olvidarlo
- ✅ Contacta al admin para cambiar otros datos

### Para Administradores:
- ✅ Los usuarios NO pueden cambiar su rol
- ✅ Los usuarios NO pueden modificar datos sensibles (DNI, nombre)
- ✅ Puedes ver todos los cambios en la tabla `usuarios`
- ✅ Si un usuario olvida su email, verifica en la BD

---

## 🔍 Verificación en Base de Datos

Para ver los cambios en la BD:

```sql
-- Ver todos los usuarios con sus emails y teléfonos
SELECT id, email, telf, nombre, apellido1 
FROM usuarios 
WHERE rol = 'user'
ORDER BY id;

-- Ver cambios de un usuario específico
SELECT * FROM usuarios WHERE id = 2;
```

---

## 🚀 Mejoras Futuras (Opcional)

Ideas para versiones futuras:

- [ ] Cambio de contraseña desde el perfil
- [ ] Confirmación por email al cambiar el email
- [ ] Historial de cambios en el perfil
- [ ] Verificación de email (código por correo)
- [ ] Avatar/foto de perfil con recorte
- [ ] Preferencias de notificaciones
- [ ] Datos adicionales opcionales (dirección, etc.)

---

**Versión:** 1.2.0  
**Fecha:** 23 de octubre de 2025  
**Nueva funcionalidad:** Edición de email y teléfono por el usuario
