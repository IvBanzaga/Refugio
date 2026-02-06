# Contraseña de Aplicación de Google - SMTP

Esta guía explica cómo obtener la **Contraseña de Aplicación** de Google necesaria para enviar emails desde el sistema de reservas usando Gmail SMTP.

---

## 🔑 ¿Qué es una Contraseña de Aplicación?

Es una contraseña especial de 16 caracteres que Google genera para aplicaciones o dispositivos que no soportan la verificación en 2 pasos. Permite que tu aplicación se conecte de forma segura a Gmail sin usar tu contraseña real.

**Ejemplo de formato:** `moto ohdq fxxp zmdj`

---

## 🔗 Acceso directo

**URL:** https://myaccount.google.com/apppasswords

---

## 📋 Pasos para generar una Contraseña de Aplicación

### Requisito previo obligatorio:
✅ **Verificación en 2 pasos debe estar activada** en tu cuenta de Google

### Paso 1: Acceder a tu cuenta de Google
Visita: **https://myaccount.google.com**

### Paso 2: Ir a Seguridad
- Click en **"Seguridad"** en el menú lateral izquierdo

### Paso 3: Activar verificación en 2 pasos (si no está activada)
- Busca la sección **"Verificación en 2 pasos"**
- Click en **"Empezar"** o **"Activar"**
- Sigue los pasos para configurarla:
  - Verifica tu número de teléfono
  - Elige el método de verificación (SMS, llamada, o app Google Authenticator)
  - Completa la configuración

### Paso 4: Acceder a Contraseñas de aplicaciones
Una vez activada la verificación en 2 pasos:
- Regresa a **"Seguridad"**
- Busca **"Contraseñas de aplicaciones"** o **"App passwords"**
  - Puede estar en la sección "Acceso a Google"
- Click en la opción

### Paso 5: Generar nueva contraseña
1. **Seleccionar aplicación:**
   - Dropdown: Selecciona **"Correo"** o **"Mail"**

2. **Seleccionar dispositivo:**
   - Dropdown: Selecciona **"Otro (nombre personalizado)"** o **"Other (Custom name)"**
   - Escribe un nombre descriptivo: **"Refugio Sistema"** o **"Sistema de Reservas"**

3. **Generar:**
   - Click en el botón **"Generar"** o **"Generate"**

### Paso 6: Copiar la contraseña
Google te mostrará una contraseña de 16 caracteres en este formato:
```
xxxx xxxx xxxx xxxx
```

**Ejemplo:** `moto ohdq fxxp zmdj`

⚠️ **IMPORTANTE:** 
- **Copia la contraseña INMEDIATAMENTE**
- No podrás verla de nuevo
- Si la pierdes, deberás generar una nueva

### Paso 7: Configurar en el proyecto
Pega la contraseña en tu archivo `.env`:

```env
SMTP_PASS=moto ohdq fxxp zmdj
```

**Nota:** Puedes copiarla con espacios o sin espacios, ambas formas funcionan.

---

## 🔒 Seguridad y buenas prácticas

### ✅ Hacer:
- Genera una contraseña **diferente para cada aplicación**
- Guárdala en un **gestor de contraseñas** o archivo `.env`
- Revoca contraseñas de aplicaciones que ya no uses
- Mantén el archivo `.env` en **`.gitignore`** (nunca lo subas a GitHub)

### ❌ No hacer:
- **NO uses tu contraseña normal de Gmail** en SMTP
- **NO compartas** la contraseña de aplicación públicamente
- **NO la guardes** en el código fuente
- **NO la subas** a repositorios públicos

---

## 🔄 Gestionar contraseñas existentes

### Ver contraseñas generadas:
- Ve a https://myaccount.google.com/apppasswords
- Verás una lista de todas las contraseñas de aplicación creadas
- **NO puedes ver** la contraseña en sí, solo el nombre que le diste

### Revocar una contraseña:
1. Ve a https://myaccount.google.com/apppasswords
2. Encuentra la contraseña que quieres eliminar
3. Click en el ícono de **"X"** o **"Revocar"**
4. Confirma la revocación

Una vez revocada, cualquier aplicación que la usaba dejará de funcionar.

---

## ⚠️ Solución de problemas

### Error: "No encuentro Contraseñas de aplicaciones"
**Causa:** La verificación en 2 pasos no está activada.  
**Solución:** Ve a Seguridad → Verificación en 2 pasos → Activar

### Error: "535-5.7.8 Username and Password not accepted"
**Causa:** La contraseña de aplicación es incorrecta o fue revocada.  
**Solución:** Genera una nueva contraseña de aplicación y actualiza el `.env`

### Error: "La opción no aparece"
**Causa:** Algunas cuentas de Google Workspace pueden tener restricciones.  
**Solución:** Contacta con tu administrador de Workspace o usa una cuenta personal de Gmail

---

## 🔐 Alternativa: OAuth2

Para mayor seguridad y control, puedes usar **OAuth2** en lugar de contraseñas de aplicación:

### Ventajas de OAuth2:
- ✅ No requiere contraseña de aplicación
- ✅ Puedes revocar el acceso sin cambiar contraseñas
- ✅ Permisos más granulares
- ✅ Más seguro para aplicaciones públicas

### Desventajas de OAuth2:
- ❌ Configuración más compleja
- ❌ Requiere crear proyecto en Google Cloud Console
- ❌ Necesita refresh token y token de acceso
- ❌ Mayor mantenimiento

**Recomendación:** Para proyectos internos o pequeños, las contraseñas de aplicación son suficientes y más simples.

---

## 📚 Recursos adicionales

- **Google Account Help:** https://support.google.com/accounts/answer/185833
- **Gmail SMTP Settings:** https://support.google.com/mail/answer/7126229
- **PHPMailer Documentation:** https://github.com/PHPMailer/PHPMailer

---

## 📝 Configuración completa en .env

```env
# ===================================
# CONFIGURACIÓN SMTP DE GMAIL
# ===================================

SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=tu_email@gmail.com
SMTP_PASS=xxxx xxxx xxxx xxxx  # ← Contraseña de aplicación aquí
SMTP_SECURE=tls

# Alternativamente con SSL (puerto 465):
# SMTP_PORT=465
# SMTP_SECURE=ssl
```

---

## ✅ Verificar que funciona

Después de configurar:

1. Envía un email de prueba desde el sistema
2. Revisa los logs de PHP/servidor para errores
3. Verifica que el email llegó a la bandeja de entrada
4. Si hay errores, revisa que:
   - La verificación en 2 pasos está activada
   - La contraseña de aplicación es correcta (sin errores de tipeo)
   - El usuario SMTP coincide con la cuenta que generó la contraseña

---

**Última actualización:** 6 de febrero de 2026
