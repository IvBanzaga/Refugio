# 🤝 Guía de Contribución

¡Gracias por tu interés en contribuir al Sistema de Control de Camas del Refugio!

## 📋 Tabla de Contenidos

1. [Código de Conducta](#código-de-conducta)
2. [Cómo Contribuir](#cómo-contribuir)
3. [Proceso de Desarrollo](#proceso-de-desarrollo)
4. [Estándares de Código](#estándares-de-código)
5. [Testing](#testing)
6. [Documentación](#documentación)

---

## 📜 Código de Conducta

Este proyecto se adhiere a un código de conducta. Al participar, se espera que mantengas este código. Por favor reporta comportamiento inaceptable a [email de contacto].

---

## 🚀 Cómo Contribuir

### Reportar Bugs

Si encuentras un bug, por favor crea un issue con:

- **Título claro y descriptivo**
- **Pasos para reproducir** el problema
- **Comportamiento esperado** vs **comportamiento actual**
- **Screenshots** si es aplicable
- **Entorno:** SO, versión PHP, versión PostgreSQL

**Ejemplo:**
```markdown
## Bug: Calendario no muestra disponibilidad correctamente

**Pasos para reproducir:**
1. Ir a Panel Usuario
2. Acceder a Calendario
3. Seleccionar mes de Noviembre 2025

**Esperado:** Mostrar disponibilidad de camas
**Actual:** Muestra error 500

**Entorno:** Windows 11, PHP 8.1, PostgreSQL 14
```

### Sugerir Mejoras

Para sugerir una mejora o nueva funcionalidad:

1. Verificar que no existe un issue similar
2. Crear nuevo issue con etiqueta "enhancement"
3. Describir claramente la funcionalidad
4. Explicar el caso de uso
5. (Opcional) Proponer implementación

### Pull Requests

1. **Fork** el repositorio
2. Crear **rama** desde `develop`: `git checkout -b feature/nombre-feature`
3. Hacer **commits** descriptivos
4. **Push** a tu fork: `git push origin feature/nombre-feature`
5. Crear **Pull Request** a rama `develop`

---

## 🔧 Proceso de Desarrollo

### Estructura de Ramas

```
main (producción)
  └── develop (desarrollo)
       ├── feature/nueva-funcionalidad
       ├── bugfix/corregir-error
       └── hotfix/urgente
```

- `main`: Código en producción, siempre estable
- `develop`: Rama de desarrollo, pruebas antes de main
- `feature/*`: Nuevas funcionalidades
- `bugfix/*`: Corrección de bugs
- `hotfix/*`: Correcciones urgentes en producción

### Workflow

1. **Crear rama desde develop**
   ```bash
   git checkout develop
   git pull origin develop
   git checkout -b feature/mi-feature
   ```

2. **Desarrollar y commit frecuentemente**
   ```bash
   git add .
   git commit -m "feat: descripción clara del cambio"
   ```

3. **Mantener rama actualizada**
   ```bash
   git fetch origin develop
   git rebase origin/develop
   ```

4. **Push y crear PR**
   ```bash
   git push origin feature/mi-feature
   # Crear PR en GitHub/GitLab
   ```

5. **Code Review y Merge**
   - Esperar revisión de código
   - Realizar cambios solicitados
   - Aprobar y mergear

---

## 📝 Estándares de Código

### PHP

#### Convenciones de Nombres

```php
// Funciones: snake_case
function obtener_reservas($id_usuario) { }

// Variables: snake_case
$reservas_pendientes = [];

// Constantes: UPPER_CASE
define('MAX_RESERVAS', 10);

// Clases: PascalCase (futuro)
class ReservaManager { }
```

#### Comentarios

```php
/**
 * Descripción breve de la función
 * 
 * @param PDO $conexion Conexión a la base de datos
 * @param int $id ID del usuario
 * @return array Array de reservas o vacío
 */
function obtener_reservas($conexion, $id) {
    // Comentario de línea para lógica compleja
    $stmt = $conexion->prepare("SELECT * FROM reservas WHERE id_usuario = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```

#### Seguridad

```php
// ✅ BIEN: Usar prepared statements
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE email = :email");
$stmt->bindParam(':email', $email);

// ❌ MAL: Concatenar directamente
$stmt = $conexion->query("SELECT * FROM usuarios WHERE email = '$email'");

// ✅ BIEN: Sanitizar output
echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');

// ❌ MAL: Output directo
echo $user_input;

// ✅ BIEN: Validar input
if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // procesar
}

// ❌ MAL: Confiar en input del usuario
$id = $_POST['id']; // Sin validación
```

### JavaScript

```javascript
// Variables: camelCase
const miVariable = 'valor';

// Funciones: camelCase
function obtenerDatos() { }

// Constantes: UPPER_CASE
const MAX_INTENTOS = 3;

// Usar const/let, no var
const datos = [];
let contador = 0;

// Arrow functions cuando sea apropiado
const duplicar = (n) => n * 2;

// Template literals
const mensaje = `Hola ${nombre}`;
```

### SQL

```sql
-- Nombres en minúsculas con guión bajo
CREATE TABLE nombre_tabla (
    id SERIAL PRIMARY KEY,
    nombre_campo VARCHAR(100)
);

-- Palabras clave en MAYÚSCULAS
SELECT id, nombre 
FROM usuarios 
WHERE estado = 'activo'
ORDER BY fecha_creacion DESC;

-- Usar alias descriptivos
SELECT 
    u.nombre as usuario_nombre,
    r.fecha_inicio as reserva_fecha
FROM usuarios u
JOIN reservas r ON u.id = r.id_usuario;
```

### HTML/CSS

```html
<!-- IDs: kebab-case -->
<div id="panel-usuario"></div>

<!-- Clases: kebab-case -->
<div class="card card-reserva"></div>

<!-- Atributos en orden alfabético -->
<input 
    class="form-control"
    id="email"
    name="email"
    placeholder="Email"
    required
    type="email"
>
```

```css
/* Selectores en kebab-case */
.card-reserva {
    padding: 1rem;
}

/* Variables CSS con prefijo -- */
:root {
    --color-primary: #1e3a8a;
}

/* Propiedades en orden lógico */
.elemento {
    /* Posicionamiento */
    position: relative;
    top: 0;
    
    /* Display */
    display: flex;
    
    /* Dimensiones */
    width: 100%;
    height: auto;
    
    /* Estilos visuales */
    background: white;
    border: 1px solid #ccc;
}
```

---

## 🧪 Testing

### Testing Manual

Antes de crear un PR, verificar:

- [ ] Funcionalidad principal funciona
- [ ] No hay errores en consola de navegador
- [ ] No hay errores en logs de PHP
- [ ] Funciona en Chrome, Firefox, Safari
- [ ] Responsive (mobile, tablet, desktop)
- [ ] Accesibilidad básica (navegación teclado)

### Testing de Seguridad

- [ ] Validación de inputs
- [ ] Protección XSS
- [ ] Protección SQL Injection
- [ ] Verificación de roles
- [ ] Sesiones seguras

### Checklist de PR

```markdown
## Descripción
[Descripción de los cambios]

## Tipo de cambio
- [ ] Bug fix
- [ ] Nueva funcionalidad
- [ ] Breaking change
- [ ] Documentación

## Testing
- [ ] Probado localmente
- [ ] Probado en múltiples navegadores
- [ ] Probado responsive
- [ ] Sin errores en logs

## Checklist
- [ ] Código sigue estándares del proyecto
- [ ] Comentarios añadidos donde necesario
- [ ] Documentación actualizada
- [ ] Sin conflictos con develop
```

---

## 📚 Documentación

### Documentar Nuevas Funciones

```php
/**
 * Breve descripción de qué hace la función
 * 
 * Descripción más detallada si es necesario.
 * Puede incluir casos de uso, notas importantes, etc.
 * 
 * @param PDO $conexion Conexión a la base de datos
 * @param int $id_usuario ID del usuario que hace la reserva
 * @param string $fecha_inicio Fecha de inicio en formato Y-m-d
 * @param string $fecha_fin Fecha de fin en formato Y-m-d
 * 
 * @return array|false Array con datos de la reserva o false si falla
 * 
 * @throws PDOException Si hay error en la consulta
 * 
 * @example
 * $reserva = crear_reserva($conn, 1, '2025-11-01', '2025-11-05');
 * if ($reserva) {
 *     echo "Reserva creada: " . $reserva['id'];
 * }
 */
function crear_reserva($conexion, $id_usuario, $fecha_inicio, $fecha_fin) {
    // Implementación
}
```

### Actualizar README

Si añades funcionalidad mayor, actualiza:

- `README.md`: Sección de funcionalidades
- `MEJORAS_FUTURAS.md`: Marcar como completado
- Screenshots si cambia la UI

---

## 🎨 Commits Convencionales

Usar [Conventional Commits](https://www.conventionalcommits.org/):

```
tipo(alcance): descripción breve

[descripción detallada opcional]

[notas al pie opcionales]
```

### Tipos

- `feat`: Nueva funcionalidad
- `fix`: Corrección de bug
- `docs`: Cambios en documentación
- `style`: Formato, punto y coma, etc.
- `refactor`: Refactorización de código
- `test`: Añadir tests
- `chore`: Mantenimiento, dependencias

### Ejemplos

```bash
# Nueva funcionalidad
git commit -m "feat(reservas): añadir filtro por estado"

# Bug fix
git commit -m "fix(calendario): corregir cálculo de días disponibles"

# Documentación
git commit -m "docs(readme): actualizar instrucciones de instalación"

# Refactorización
git commit -m "refactor(functions): separar lógica de validación"

# Múltiples líneas
git commit -m "feat(usuarios): añadir campo de avatar

- Añadir columna avatar_url a tabla usuarios
- Crear formulario de upload
- Validar tipo y tamaño de imagen"
```

---

## 🏆 Reconocimientos

Los contribuidores serán añadidos al archivo `CONTRIBUTORS.md` con:

- Nombre/Usuario
- Contribuciones principales
- Enlaces (opcional)

---

## 📞 Contacto

- **Issues:** GitHub Issues
- **Discusiones:** GitHub Discussions
- **Email:** [email de contacto]

---

## 📄 Licencia

Al contribuir, aceptas que tus contribuciones serán licenciadas bajo la misma licencia del proyecto.

---

**¡Gracias por contribuir! 🎉**

Toda ayuda es bienvenida, desde reportar bugs hasta mejorar documentación.
