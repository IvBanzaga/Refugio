# ⚡ Guía Rápida de Inicio

Esta guía te ayudará a poner en marcha el sistema en **menos de 10 minutos**.

## 🎯 Pasos Rápidos

### 1️⃣ Preparar PostgreSQL (5 minutos)

```powershell
# Abrir PowerShell como Administrador
# Iniciar servicio PostgreSQL (si no está iniciado)
net start postgresql-x64-12

# Abrir consola de PostgreSQL
psql -U postgres

# Dentro de psql, ejecutar:
CREATE DATABASE refugio;
\q
```

### 2️⃣ Importar Base de Datos (2 minutos)

```powershell
# Navegar a la carpeta del proyecto
cd f:\Proyectos\Refugio

# Importar el esquema
psql -U postgres -d refugio -f sql\refugio.sql
```

### 3️⃣ Configurar Conexión (1 minuto)

Editar `conexion.php` líneas 11-12:

```php
$username = "postgres";  // Tu usuario
$password = "TU_PASSWORD_AQUI";  // Tu contraseña
```

### 4️⃣ Iniciar Servidor (1 minuto)

```powershell
# En la carpeta del proyecto
php -S localhost:8000
```

### 5️⃣ ¡Listo! Acceder al Sistema

Abrir navegador: **http://localhost:8000**

**Credenciales de prueba:**
- Admin: `admin@hostel.com` / `admin123`
- Usuario: `user1@mail.com` / `user123`

---

## 🔧 Solución de Problemas Comunes

### ❌ "Could not find driver"
```powershell
# Verificar extensión PDO PostgreSQL
php -m | findstr pdo_pgsql

# Si no aparece, editar php.ini y descomentar:
# extension=pdo_pgsql
```

### ❌ "Connection refused"
```powershell
# Verificar que PostgreSQL esté ejecutándose
net start postgresql-x64-12

# O verificar el servicio
services.msc
# Buscar PostgreSQL y asegurarse que está "Iniciado"
```

### ❌ "Database does not exist"
```powershell
# Crear manualmente la base de datos
psql -U postgres
CREATE DATABASE refugio;
\q
```

### ❌ Contraseñas no funcionan
```powershell
# Ejecutar script de actualización
php update_passwords.php
# Luego eliminar ese archivo
```

---

## 📱 Alternativa con XAMPP/WAMP

### Si prefieres usar Apache y MySQL:

1. **Cambiar a MySQL:**
   - Editar `conexion.php` para usar MySQL (ver ejemplo en el archivo)
   - Importar `sql/refugio_mysql.sql` en phpMyAdmin

2. **Copiar proyecto:**
   ```powershell
   # Copiar a htdocs (XAMPP) o www (WAMP)
   xcopy /E /I f:\Proyectos\Refugio C:\xampp\htdocs\refugio
   ```

3. **Acceder:**
   ```
   http://localhost/refugio
   ```

---

## 🎨 Primeros Pasos Después de Instalar

### Como Administrador:
1. ✅ Cambiar contraseña del admin
2. ✅ Crear usuarios reales
3. ✅ Revisar configuración de habitaciones
4. ✅ Verificar que el calendario funcione

### Como Usuario:
1. ✅ Explorar el calendario
2. ✅ Crear una reserva de prueba
3. ✅ Revisar "Mis Reservas"

---

## 📞 ¿Necesitas Ayuda?

- **Documentación completa:** Ver `README.md`
- **Problemas conocidos:** Ver sección de troubleshooting en README
- **Mejoras futuras:** Ver `MEJORAS_FUTURAS.md`

---

## ✨ ¡Disfruta del Sistema!

Una vez que todo funcione, no olvides:
- 🔐 Cambiar las contraseñas por defecto
- 🗑️ Eliminar `update_passwords.php`
- 📝 Personalizar los mensajes según tu club
- 🎨 Ajustar los colores si lo deseas

**¡El sistema está listo para gestionar las reservas de tu refugio!** 🏔️
