# 📁 Estructura del Proyecto - Refugio

## Arquitectura MVC Refactorizada

Este proyecto ha sido refactorizado siguiendo el patrón MVC (Modelo-Vista-Controlador) para mejorar la mantenibilidad, escalabilidad y organización del código.

## 📂 Estructura de Carpetas

```
refugio/
├── config/                  # Configuración de la aplicación
│   ├── app.php             # Configuración general
│   ├── database.php        # Conexión a base de datos
│   ├── email.php           # Configuración de emails
│   └── bootstrap.php       # Inicialización de la app
│
├── src/                     # Código fuente de la aplicación
│   ├── Controllers/        # Controladores (lógica de negocio)
│   ├── Models/             # Modelos (acceso a datos)
│   ├── Services/           # Servicios (email, notificaciones, etc.)
│   └── Helpers/            # Funciones auxiliares
│       └── functions.php
│
├── views/                   # Vistas (presentación)
│   ├── admin/              # Vistas del panel de administración
│   ├── socio/              # Vistas del panel de socios
│   ├── auth/               # Vistas de autenticación
│   └── partials/           # Componentes reutilizables
│
├── public/                  # Archivos públicos accesibles
│   ├── index.php           # Punto de entrada principal
│   ├── assets/             # CSS, JS, imágenes
│   │   ├── css/
│   │   └── js/
│   └── uploads/            # Archivos subidos por usuarios
│
├── api/                     # Endpoints API REST
│   ├── disponibilidad.php
│   └── fechas_completas.php
│
├── sql/                     # Scripts de base de datos
├── vendor/                  # Dependencias de Composer
├── .env                     # Variables de entorno (NO SUBIR A GIT)
├── .env.example            # Plantilla de variables de entorno
└── composer.json           # Dependencias del proyecto
```

## 🎯 Convenciones de Código

### Nomenclatura
- **Clases**: PascalCase (`UsuarioController`, `ReservaModel`)
- **Métodos**: camelCase (`crearReserva()`, `obtenerUsuarios()`)
- **Variables**: camelCase (`$nombreUsuario`, `$fechaInicio`)
- **Constantes**: UPPER_SNAKE_CASE (`MAX_CAMAS_HABITACION`, `BASE_URL`)
- **Archivos**: snake_case para scripts, PascalCase para clases

### Estructura de Clases

#### Controladores
```php
namespace Controllers;

class ReservaController {
    public function index() { }      // Listar
    public function show($id) { }    // Ver detalle
    public function create() { }     // Formulario crear
    public function store() { }      // Guardar
    public function edit($id) { }    // Formulario editar
    public function update($id) { }  // Actualizar
    public function delete($id) { }  // Eliminar
}
```

#### Modelos
```php
namespace Models;

class Reserva {
    public static function find($id) { }
    public static function all() { }
    public static function create($data) { }
    public static function update($id, $data) { }
    public static function delete($id) { }
}
```

#### Servicios
```php
namespace Services;

class EmailService {
    public function send($to, $subject, $body) { }
    public function notify($userId, $template) { }
}
```

## 🔧 Configuración

### Variables de Entorno

Copia `.env.example` a `.env` y configura tus valores:

```bash
cp .env.example .env
```

### Base de Datos

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=refugio
DB_USER=root
DB_PASS=tu_contraseña
```

### Email SMTP

```env
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=tu-email@gmail.com
SMTP_PASS=contraseña-de-aplicación
```

## 🚀 Flujo de la Aplicación

1. **Entrada**: `public/index.php` - Punto de entrada único
2. **Bootstrap**: `config/bootstrap.php` - Carga configuraciones
3. **Routing**: Determina qué controlador ejecutar
4. **Controller**: Procesa la lógica de negocio
5. **Model**: Accede a los datos (base de datos)
6. **View**: Renderiza la presentación
7. **Response**: Devuelve HTML al navegador

## 📝 Helpers Disponibles

```php
// Vistas
view('admin/dashboard', ['data' => $data]);

// Redirecciones
redirect('/admin/reservas');

// Datos de formulario
$nombre = post('nombre', 'default');
$id = get('id');

// Autenticación
requireAuth();      // Requiere estar logueado
requireAdmin();     // Requiere ser admin
isAuthenticated();  // Verifica si está logueado
isAdmin();         // Verifica si es admin
```

## 🔐 Seguridad

- ✅ Todas las consultas usan **PDO con prepared statements**
- ✅ Variables de entorno en `.env` (excluido de Git)
- ✅ Sanitización de entradas con `sanitize_input()`
- ✅ Validación de sesiones y roles
- ✅ Protección CSRF (pendiente implementar)

## 📦 Dependencias

### Composer
```json
{
    "phpmailer/phpmailer": "^7.0"
}
```

Instalar dependencias:
```bash
composer install
```

## 🛠️ Próximos Pasos de Refactorización

- [ ] Implementar autoloading PSR-4
- [ ] Crear sistema de routing
- [ ] Separar lógica de viewAdmin.php y viewSocio.php en controladores
- [ ] Implementar modelos para Reserva, Usuario, Habitacion
- [ ] Crear middleware para autenticación
- [ ] Implementar sistema de templates (Blade/Twig)
- [ ] Añadir validación de formularios
- [ ] Implementar patrón Repository
- [ ] Tests unitarios y de integración

## 📚 Recursos

- [PSR-4 Autoloading](https://www.php-fig.org/psr/psr-4/)
- [MVC Pattern](https://es.wikipedia.org/wiki/Modelo%E2%80%93vista%E2%80%93controlador)
- [PHP Best Practices](https://phptherightway.com/)

---

**Versión**: 2.0  
**Última actualización**: Febrero 2026
