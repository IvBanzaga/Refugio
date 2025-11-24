# 📚 Índice de Documentación - Sistema Refugio

Bienvenido al Sistema de Control de Camas del Refugio. Esta página te guiará a través de toda la documentación disponible.

---

## 🚀 Para Empezar

### Lectura Obligatoria
1. **[INICIO_RAPIDO.md](INICIO_RAPIDO.md)** ⚡
   - Instalación en menos de 10 minutos
   - Configuración básica
   - Primeros pasos
   - Solución de problemas comunes

2. **[README.md](README.md)** 📖
   - Descripción completa del sistema
   - Requisitos detallados
   - Instalación paso a paso
   - Funcionalidades completas
   - Usuarios de prueba
   - Estructura del proyecto

3. **[RESUMEN_PROYECTO.md](RESUMEN_PROYECTO.md)** 📊
   - Vista general del proyecto
   - Objetivos cumplidos
   - Estadísticas
   - Arquitectura
   - Estado actual

---

## 🔧 Desarrollo y Contribución

### Para Desarrolladores
4. **[CONTRIBUTING.md](CONTRIBUTING.md)** 🤝
   - Cómo contribuir al proyecto
   - Estándares de código
   - Proceso de desarrollo
   - Pull requests
   - Commits convencionales

5. **[CHANGELOG.md](CHANGELOG.md)** 📝
   - Historial de cambios
   - Versionado
   - Próximas versiones
   - Roadmap

---

## 🚀 Despliegue y Producción

### Para Administradores de Sistemas
6. **[CHECKLIST_DESPLIEGUE.md](CHECKLIST_DESPLIEGUE.md)** ✅
   - Lista de verificación completa
   - Configuración de seguridad
   - Setup del servidor
   - HTTPS y certificados
   - Backups
   - Monitoreo
   - Post-despliegue

---

## 🔮 Planificación Futura

### Roadmap
7. **[MEJORAS_FUTURAS.md](MEJORAS_FUTURAS.md)** 💡
   - Funcionalidades planificadas
   - Mejoras de seguridad
   - Optimizaciones
   - Integraciones
   - Sistema de notificaciones
   - App móvil
   - Y mucho más...

---

## 📄 Documentación Técnica

### Archivos del Sistema

#### Núcleo de la Aplicación
- **`conexion.php`** - Conexión a base de datos (PostgreSQL/MySQL)
- **`functions.php`** - 30+ funciones del sistema
- **`index.php`** - Página de inicio (redirección a login)
- **`login.php`** - Sistema de autenticación
- **`logout.php`** - Cierre de sesión
- **`disponibilidad.php`** - API AJAX para camas disponibles

#### Paneles de Usuario
- **`viewAdmin.php`** - Panel administrador completo
  - Dashboard con estadísticas
  - CRUD de usuarios
  - Gestión de reservas
  
- **`viewSocio.php`** - Panel de usuario
  - Calendario de disponibilidad
  - Nueva reserva
  - Mis reservas

#### Base de Datos
- **`sql/refugio.sql`** - Esquema PostgreSQL
- **`sql/refugio_mysql.sql`** - Esquema MySQL (alternativo)

#### Configuración
- **`config.example.php`** - Plantilla de configuración
- **`.gitignore`** - Archivos excluidos de git
- **`update_passwords.php`** - Script de actualización de contraseñas

#### Assets
- **`assets/css/style.css`** - Estilos personalizados
- **`assets/js/utils.js`** - Utilidades JavaScript

#### Legal
- **`LICENSE`** - Licencia MIT del proyecto

---

## 🎓 Tutoriales y Guías

### Tutoriales por Rol

#### 👤 Usuario Final
1. **Cómo hacer una reserva**
   - Acceder al sistema
   - Ver calendario de disponibilidad
   - Crear nueva reserva
   - Agregar acompañantes
   - Seguimiento de reserva

2. **Gestionar mis reservas**
   - Ver reservas pendientes
   - Ver reservas aprobadas
   - Cancelar una reserva
   - Ver historial

#### 👨‍💼 Administrador
1. **Gestión de usuarios**
   - Crear nuevo usuario
   - Editar usuario existente
   - Cambiar rol de usuario
   - Eliminar usuario

2. **Gestión de reservas**
   - Revisar solicitudes pendientes
   - Aprobar reservas
   - Rechazar reservas
   - Ver ocupación de habitaciones

#### 👨‍💻 Desarrollador
1. **Ambiente de desarrollo**
   ```bash
   # Configurar entorno local
   git clone [repo]
   cd refugio
   # Configurar BD
   psql -U postgres -d refugio -f sql/refugio.sql
   # Configurar conexion.php
   php -S localhost:8000
   ```

2. **Agregar nueva funcionalidad**
   - Crear rama feature
   - Implementar cambios
   - Seguir estándares de código
   - Testing
   - Crear PR

---

## 🔍 Búsqueda Rápida

### Por Tema

#### Seguridad 🔐
- Contraseñas: `README.md` sección Seguridad
- SQL Injection: `CONTRIBUTING.md` sección Estándares
- XSS: `functions.php` función `sanitize_input()`
- Sesiones: `conexion.php` y `login.php`

#### Base de Datos 🗄️
- Esquema: `sql/refugio.sql`
- Conexión: `conexion.php`
- Migraciones: Ver `MEJORAS_FUTURAS.md`

#### Frontend 🎨
- Estilos: `assets/css/style.css`
- JavaScript: `assets/js/utils.js`
- Bootstrap: Todos los archivos .php usan Bootstrap 5

#### Backend ⚙️
- Funciones: `functions.php`
- API: `disponibilidad.php`
- Autenticación: `login.php`

---

## 📞 Soporte y Ayuda

### Preguntas Frecuentes

**Q: ¿Cómo cambio la contraseña del admin?**  
A: Ver `README.md` sección "Usuarios de prueba" y `update_passwords.php`

**Q: ¿Cómo agrego más habitaciones?**  
A: Ejecutar INSERT en tabla `habitaciones` y crear las camas correspondientes

**Q: ¿Puedo usar MySQL en lugar de PostgreSQL?**  
A: Sí, usa `sql/refugio_mysql.sql` y modifica `conexion.php`

**Q: ¿Cómo activo las notificaciones por email?**  
A: Ver `MEJORAS_FUTURAS.md` - Planificado para v1.1.0

**Q: ¿Es seguro para producción?**  
A: Sí, siguiendo el `CHECKLIST_DESPLIEGUE.md`

### Obtener Ayuda

- **GitHub Issues**: Reportar bugs o problemas
- **GitHub Discussions**: Preguntas y discusiones
- **Email**: [Contacto del proyecto]
- **Documentación**: Esta página

---

## 🗺️ Mapa del Sitio

```
Sistema Refugio
│
├── 🏠 Login (index.php → login.php)
│
├── 👨‍💼 Panel Admin (viewAdmin.php)
│   ├── Dashboard
│   ├── Gestión de Usuarios
│   └── Gestión de Reservas
│
└── 👤 Panel Usuario (viewSocio.php)
    ├── Calendario
    ├── Nueva Reserva
    └── Mis Reservas
```

---

## 📊 Diagrama de Flujo

```
Usuario accede al sistema
         ↓
    Login (email + password)
         ↓
    ¿Autenticado?
    /           \
  No            Sí
  ↓             ↓
Error       ¿Rol?
            /    \
        Admin    User
          ↓        ↓
    viewAdmin  viewSocio
```

---

## 🎯 Próximos Pasos

### Si eres nuevo:
1. ✅ Lee `INICIO_RAPIDO.md`
2. ✅ Instala el sistema localmente
3. ✅ Explora con usuarios de prueba
4. ✅ Lee `README.md` completo

### Si vas a desarrollar:
1. ✅ Lee `CONTRIBUTING.md`
2. ✅ Revisa `CHANGELOG.md`
3. ✅ Estudia `functions.php`
4. ✅ Crea tu primera feature

### Si vas a desplegar:
1. ✅ Revisa `CHECKLIST_DESPLIEGUE.md`
2. ✅ Configura servidor y BD
3. ✅ Implementa medidas de seguridad
4. ✅ Realiza backups

---

## 📚 Recursos Adicionales

### Tecnologías Usadas
- [PHP Manual](https://www.php.net/manual/es/)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
- [Bootstrap 5](https://getbootstrap.com/docs/5.3/)
- [PDO Documentation](https://www.php.net/manual/es/book.pdo.php)

### Seguridad
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security](https://www.php.net/manual/es/security.php)
- [Password Hashing](https://www.php.net/manual/es/function.password-hash.php)

---

## ✨ Conclusión

Esta documentación cubre todos los aspectos del Sistema de Control de Camas del Refugio. Si no encuentras lo que buscas, por favor crea un issue o contacta al equipo de desarrollo.

**¡Bienvenido al equipo!** 🎉

---

**Última actualización:** 23 de Octubre de 2025  
**Versión de la documentación:** 1.0.0
