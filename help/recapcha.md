# Google reCAPTCHA v2 - Configuración

Listo. He cambiado hCaptcha por Google reCAPTCHA v2, que es más simple y no muestra mensajes de advertencia molestos. Estoy usando las claves de prueba oficiales de Google:

**Site Key:** 6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI  
**Secret Key:** 6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe

Estas claves de prueba siempre pasan la validación y muestran un checkbox limpio sin advertencias. Cuando quieras usar claves reales, solo necesitas registrarte gratis en reCAPTCHA Admin y reemplazar las claves.

---

## 📋 Pasos para obtener tus propias claves de reCAPTCHA

### 1. Acceder al Admin Console
Visita: **https://www.google.com/recaptcha/admin**

### 2. Iniciar sesión
Inicia sesión con tu cuenta de Google.

### 3. Registrar un nuevo sitio
Haz clic en el botón **"+"** o **"Registrar un nuevo sitio"**.

### 4. Completar el formulario
- **Etiqueta**: "Refugio de Montaña" (o el nombre que prefieras para identificar el sitio)
- **Tipo de reCAPTCHA**: 
  - Selecciona **"reCAPTCHA v2"**
  - Luego **"Casilla de verificación 'No soy un robot'"**
- **Dominios**: Agrega tus dominios (uno por línea):
  - `localhost` (para desarrollo local)
  - `127.0.0.1` (opcional, para desarrollo)
  - `tudominio.com` (tu dominio de producción)
  - `www.tudominio.com` (si usas www)
- **Propietarios**: (Opcional) Agrega otros correos de Google que puedan administrar
- **Acepta** los términos de servicio de reCAPTCHA

### 5. Obtener las claves
Una vez registrado, obtendrás dos claves:

#### Clave del sitio (Site Key)
- Para usar en el **HTML del cliente** (frontend)
- Se muestra públicamente en el código
- Va en el atributo `data-sitekey` del div de reCAPTCHA

#### Clave secreta (Secret Key)
- Para usar en **PHP del servidor** (backend)
- **NUNCA debe ser pública** - mantenerla en el servidor
- Se usa para validar la respuesta con Google

### 6. Implementar en el proyecto

#### Archivo: `login.php`

**En el HTML (línea ~155):**
```html
<div class="g-recaptcha" data-sitekey="TU_SITE_KEY_AQUÍ"></div>
```

**En el PHP (línea ~30):**
```php
$secret_key = 'TU_SECRET_KEY_AQUÍ';
```

### 7. Configuración recomendada con .env

Para mayor seguridad, agrega las claves al archivo `.env`:

```env
RECAPTCHA_SITE_KEY=tu_site_key_real
RECAPTCHA_SECRET_KEY=tu_secret_key_real
```

Luego modifica `login.php` para leerlas:
```php
$secret_key = $_ENV['RECAPTCHA_SECRET_KEY'];
```

---

## 🔍 Verificación

Después de implementar tus claves reales:

1. Visita tu página de login
2. Completa el formulario
3. Marca la casilla "No soy un robot"
4. Si funciona correctamente, el login debería proceder normalmente
5. En el Admin Console de reCAPTCHA podrás ver estadísticas de uso

---

## ⚠️ Notas importantes

- Las **claves de prueba** funcionan en cualquier dominio pero **siempre pasan** la validación (no protegen realmente)
- Las **claves reales** solo funcionan en los dominios que especificaste
- Si cambias de dominio, debes agregarlo en el Admin Console
- Las claves son **gratuitas** y no tienen límite de uso para sitios normales
- reCAPTCHA v2 es compatible con todos los navegadores modernos

---

## 📊 Estadísticas y monitoreo

En el Admin Console podrás ver:
- Número de solicitudes
- Tasa de éxito/fallo
- Posibles intentos de bots
- Análisis de seguridad