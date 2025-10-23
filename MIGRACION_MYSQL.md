# 🔄 Migración a MySQL - Guía Completa

Has elegido usar MySQL en lugar de PostgreSQL. Aquí están todos los pasos para configurar el sistema correctamente.

---

## ✅ Cambios Realizados Automáticamente

1. **`conexion.php`** ✅ Ya actualizado
   - DSN cambiado de `pgsql` a `mysql`
   - Puerto cambiado de 5432 a 3306
   - Usuario cambiado de `postgres` a `root`
   - Charset `utf8mb4` añadido
   - Modo SQL configurado

---

## 📋 Pasos para Configurar MySQL

### 1️⃣ Instalar/Verificar MySQL

#### Opción A: XAMPP (Recomendado para desarrollo)
```powershell
# Descargar XAMPP desde https://www.apachefriends.org/
# Instalar y abrir el panel de control
# Iniciar MySQL
```

#### Opción B: MySQL Standalone
```powershell
# Descargar MySQL desde https://dev.mysql.com/downloads/
# Instalar y configurar
# Iniciar servicio
net start MySQL80
```

### 2️⃣ Crear la Base de Datos

#### Opción A: Con phpMyAdmin (XAMPP)
1. Abrir navegador: `http://localhost/phpmyadmin`
2. Clic en "Nueva" en el panel izquierdo
3. Nombre: `refugio`
4. Cotejamiento: `utf8mb4_unicode_ci`
5. Clic en "Crear"

#### Opción B: Con línea de comandos
```powershell
# Abrir PowerShell y ejecutar:
mysql -u root -p

# Dentro de MySQL, ejecutar:
CREATE DATABASE refugio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 3️⃣ Importar el Esquema

#### Opción A: Con phpMyAdmin
1. Seleccionar base de datos `refugio`
2. Ir a pestaña "Importar"
3. Seleccionar archivo: `sql/refugio_mysql.sql`
4. Clic en "Continuar"

#### Opción B: Con línea de comandos
```powershell
# Navegar a la carpeta del proyecto
cd f:\Proyectos\Refugio

# Importar el esquema
mysql -u root -p refugio < sql\refugio_mysql.sql
```

### 4️⃣ Verificar Importación

```sql
-- Ejecutar en MySQL
USE refugio;
SHOW TABLES;
-- Deberías ver: acompanantes, camas, habitaciones, reservas, usuarios

SELECT * FROM usuarios;
-- Deberías ver 3 usuarios de prueba
```

### 5️⃣ Configurar Contraseña en conexion.php

```php
// Editar f:\Proyectos\Refugio\conexion.php
$username = "root";        // Tu usuario de MySQL
$password = "tu_password"; // Tu contraseña de MySQL
```

**Nota:** Si usas XAMPP por defecto, la contraseña de root suele estar vacía: `$password = "";`

### 6️⃣ Iniciar el Servidor

```powershell
# En la carpeta del proyecto
cd f:\Proyectos\Refugio
php -S localhost:8000
```

### 7️⃣ Probar el Sistema

1. Abrir navegador: `http://localhost:8000`
2. Login con: `admin@hostel.com` / `admin123`
3. Verificar que todo funciona correctamente

---

## 🔍 Diferencias MySQL vs PostgreSQL

### Lo que NO cambia:
✅ **Toda la lógica PHP funciona igual**
- Las funciones en `functions.php` son idénticas
- PDO funciona igual en ambos motores
- Los formularios y vistas son los mismos
- La seguridad se mantiene igual

### Lo que SÍ cambia:

#### 1. Sintaxis SQL Específica

**PostgreSQL:**
```sql
-- SERIAL para auto incremento
id SERIAL PRIMARY KEY

-- ENUM como tipo de dato
CREATE TYPE rol_usuario AS ENUM ('admin', 'user');

-- Secuencias automáticas
SELECT currval('usuarios_id_seq');
```

**MySQL:**
```sql
-- AUTO_INCREMENT para auto incremento
id INT AUTO_INCREMENT PRIMARY KEY

-- ENUM en definición de columna
rol ENUM('admin', 'user')

-- LAST_INSERT_ID()
SELECT LAST_INSERT_ID();
```

#### 2. Archivos de Esquema

- **PostgreSQL:** Usa `sql/refugio.sql`
- **MySQL:** Usa `sql/refugio_mysql.sql`

#### 3. Conexión

- **PostgreSQL:** `pgsql:host=localhost;port=5432;dbname=refugio`
- **MySQL:** `mysql:host=localhost;port=3306;dbname=refugio;charset=utf8mb4`

---

## ⚠️ Notas Importantes

### Contraseñas Hasheadas
Las contraseñas en `sql/refugio_mysql.sql` ya están hasheadas correctamente:

```
admin@hostel.com → admin123
user1@mail.com → user123
user2@mail.com → user123
```

### Estructura de Datos
```
✅ 4 habitaciones
✅ 26 camas (4+4+4+14)
✅ 3 usuarios de prueba
✅ 1 reserva de ejemplo
✅ Tabla de acompañantes
```

### Puerto MySQL
- **3306** es el puerto por defecto
- Si usas otro puerto, actualiza en `conexion.php`

---

## 🐛 Solución de Problemas

### ❌ Error: "Access denied for user 'root'@'localhost'"

**Solución 1:** Contraseña incorrecta
```php
// Si XAMPP sin contraseña:
$password = "";

// Si MySQL con contraseña:
$password = "tu_password_real";
```

**Solución 2:** Usuario no existe
```sql
-- Crear usuario
CREATE USER 'root'@'localhost' IDENTIFIED BY 'tu_password';
GRANT ALL PRIVILEGES ON refugio.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
```

### ❌ Error: "Unknown database 'refugio'"

**Solución:**
```sql
CREATE DATABASE refugio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### ❌ Error: "Can't connect to MySQL server"

**Solución:**
```powershell
# Verificar que MySQL está ejecutándose
# En XAMPP: Abrir panel y Start MySQL
# En MySQL standalone:
net start MySQL80
```

### ❌ Error: "PDO driver not found"

**Solución:**
```ini
# Editar php.ini y descomentar:
extension=pdo_mysql

# Reiniciar servidor PHP
```

### ❌ Contraseñas no funcionan

**Solución:**
```powershell
# Verificar que las contraseñas estén hasheadas
# Opción 1: Reimportar sql/refugio_mysql.sql

# Opción 2: Ejecutar update_passwords.php
php update_passwords.php
# Luego eliminar el archivo
```

---

## 📊 Comparación de Rendimiento

Para este proyecto (26 camas, uso moderado):

| Característica | PostgreSQL | MySQL |
|----------------|-----------|-------|
| Velocidad lecturas | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| Velocidad escrituras | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ |
| Facilidad instalación | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| Herramientas GUI | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| Compatibilidad hosting | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |

**Conclusión:** Para este proyecto, ambos son excelentes. MySQL es más común en hosting compartido.

---

## ✅ Checklist de Migración

- [ ] MySQL instalado y funcionando
- [ ] Base de datos `refugio` creada
- [ ] Esquema `sql/refugio_mysql.sql` importado
- [ ] `conexion.php` actualizado con credenciales correctas
- [ ] Servidor PHP iniciado
- [ ] Login funciona con usuarios de prueba
- [ ] Panel admin accesible
- [ ] Panel usuario accesible
- [ ] Crear reserva funciona
- [ ] Calendario muestra disponibilidad

---

## 🚀 Resumen de Comandos Rápidos

```powershell
# 1. Crear BD y usuario
mysql -u root -p
CREATE DATABASE refugio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# 2. Importar esquema
cd f:\Proyectos\Refugio
mysql -u root -p refugio < sql\refugio_mysql.sql

# 3. Configurar conexion.php (editar archivo)

# 4. Iniciar servidor
php -S localhost:8000

# 5. Acceder
# http://localhost:8000
# admin@hostel.com / admin123
```

---

## 📞 Soporte Adicional

Si encuentras algún problema:

1. **Verifica versión de MySQL:**
   ```powershell
   mysql --version
   ```
   Recomendado: MySQL 5.7+ o 8.0+

2. **Verifica extensión PDO:**
   ```powershell
   php -m | findstr pdo_mysql
   ```

3. **Revisa logs de errores:**
   - Logs de MySQL: Generalmente en carpeta `data` de MySQL
   - Logs de PHP: Configurados en `php.ini`

---

## 🎉 ¡Listo!

Una vez completados estos pasos, tu sistema estará funcionando completamente con MySQL.

**Ventajas de usar MySQL en este proyecto:**
- ✅ Más común en hostings compartidos
- ✅ phpMyAdmin incluido en XAMPP
- ✅ Amplia documentación en español
- ✅ Fácil de instalar en Windows
- ✅ Menor consumo de recursos

**¡Disfruta tu sistema de gestión de refugio!** 🏔️
