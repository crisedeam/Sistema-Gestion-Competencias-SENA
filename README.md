# ProgSENA - Sistema de Gestión de Horarios y Competencias

Sistema web MVC para la gestión de asignaciones, horarios y competencias de instructores del SENA (Servicio Nacional de Aprendizaje).

## 📋 Tabla de Contenidos

- [Descripción del Proyecto](#descripción-del-proyecto)
- [Requisitos](#requisitos)
- [Instalación](#instalación)
- [Estructura del Proyecto](#estructura-del-proyecto)
- [Características Principales](#características-principales)
- [Roles y Permisos](#roles-y-permisos)
- [Arquitectura](#arquitectura)
- [Tecnologías](#tecnologías)
- [Funcionalidades Destacadas](#funcionalidades-destacadas)
- [Manual de Usuario](#manual-de-usuario)
- [Guía de Desarrollo](#guía-de-desarrollo)
- [Seguridad](#seguridad)
- [Notas de Producción](#notas-de-producción)

---

## 📖 Descripción del Proyecto

ProgSENA es un sistema integral diseñado para gestionar la programación académica del SENA, permitiendo:

- **Gestión de recursos**: Sedes, ambientes, programas, instructores y competencias
- **Programación académica**: Asignación de instructores a fichas con validación de horarios
- **Control de conflictos**: Detección automática de solapamientos de horarios
- **Auditoría completa**: Registro de todos los cambios realizados en asignaciones
- **Roles diferenciados**: Permisos específicos para Centro de Formación, Coordinadores e Instructores

---

## 🔧 Requisitos

- **PHP**: 7.4 o superior
- **MySQL**: 5.7 o superior
- **Servidor web**: Apache o Nginx
- **Extensiones PHP**: PDO, PDO_MySQL

---

## 📦 Instalación

### 1. Clonar o descargar el proyecto
```bash
git clone [url-del-repositorio]
cd ProgSENA
```

### 2. Configurar la base de datos

**Opción A: Línea de comandos**
```bash
mysql -u root -p < database.sql
```

**Opción B: phpMyAdmin**
1. Crear nueva base de datos: `ProgSENA`
2. Importar el archivo `database.sql`

### 3. Configurar conexión

Editar `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ProgSENA');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 4. Acceder al sistema
```
http://localhost/ProgSENA/
```

**Nota**: La base de datos se entrega limpia. El administrador debe crear las credenciales de acceso iniciales.

---

## 📁 Estructura del Proyecto

```
ProgSENA/
├── config.php                 # Configuración global y funciones auxiliares
├── index.php                  # Enrutador principal
├── Database.php               # Clase de conexión a BD
├── database.sql               # Script de base de datos con auditoría
│
├── controllers/               # Controladores MVC (18 controladores)
│   ├── Controller.php         # Clase base con métodos helper
│   ├── AuthController.php     # Autenticación y sesiones
│   ├── DashboardController.php
│   ├── AsignacionController.php  # Con validación de conflictos
│   └── ...
│
├── models/                    # Modelos de datos (14 modelos)
│   ├── Model.php              # Clase base con CRUD completo
│   ├── Asignacion.php
│   ├── Instructor.php
│   └── ...
│
├── views/                     # Vistas (HTML/PHP)
│   ├── layout/
│   │   └── layout.php         # Layout con menú dinámico mejorado
│   ├── assets/                # Recursos compartidos
│   │   ├── calendario-component.js      # Componente de calendario unificado
│   │   ├── calendario-styles.css        # Estilos del calendario
│   │   ├── horarios-styles.css          # Estilos de FullCalendar
│   │   └── CALENDARIO_COMPONENT_README.md  # Documentación del componente
│   ├── auth/
│   ├── dashboard/             # Dashboards por rol
│   ├── asignacion/
│   │   ├── index.php
│   │   ├── form.php           # Formulario unificado crear/editar
│   │   ├── form_script.php    # Validaciones y calendario interactivo
│   │   └── historial.php      # Vista de auditoría
│   ├── detalleAsignacion/
│   │   ├── index.php          # Vista con tabla de detalles por día
│   │   ├── create.php
│   │   └── edit.php
│   ├── instructor/
│   │   ├── mis-asignaciones.php
│   │   └── mis-horarios.php   # Vista con tabla de horarios
│   └── ...
│
├── manual_capturas/           # Capturas de pantalla del manual
│   ├── 01_acceso/
│   ├── 02_centro_formacion/
│   ├── 03_coordinador/
│   └── 04_instructor/
│
├── MANUAL_USUARIO_PROGSENA_CORREGIDO.html  # Manual de usuario
├── MANUAL_USUARIO_PROGSENA_CORREGIDO.pdf   # Manual en PDF
└── Historias de Usuario - Gestion Competencias.docx
```

---

## ✨ Características Principales

### Sistema de Autenticación
- Login seguro con correo y contraseña
- Contraseñas hasheadas con `password_hash()`
- Tres roles: Centro de Formación, Coordinador, Instructor
- Protección de rutas por autenticación y rol
- Sesiones seguras con timeout

### Dashboard Personalizado por Rol
- **Centro de Formación**: Estadísticas de recursos del centro
- **Coordinador**: Asignaciones y disponibilidad de ambientes
- **Instructor**: Horarios personales y próximas clases

### Gestión de Recursos
- **Sedes**: Ubicaciones físicas del centro
- **Ambientes**: Aulas, laboratorios, talleres con capacidad
- **Programas**: Programas de formación (ADSO, Contabilidad, etc.)
- **Instructores**: Gestión por centro con competencias asignadas
- **Competencias**: Unidades de competencia de los programas
- **Coordinaciones**: Coordinadores por centro de formación
- **Fichas**: Grupos de aprendices con jornadas

### Gestión Académica Avanzada
- **Competencias x Programa**: Asociación de competencias a programas
- **Instructor x Competencia**: Asignación de competencias que puede dictar cada instructor
- **Asignaciones**: Programación de clases con calendario interactivo
- **Detalles de Asignación**: Horarios específicos por fecha con validaciones

---

## 👥 Roles y Permisos

### 🏢 Centro de Formación (Administrador)
**Permisos completos sobre:**
- Dashboard con estadísticas generales
- Sedes, Ambientes, Programas
- Instructores (solo de su centro)
- Competencias
- Coordinaciones (solo de su centro)

**Características especiales:**
- Ve solo los recursos de su centro
- Asignación automática de centro al crear recursos
- No puede modificar el centro en ediciones

### 📋 Coordinador (Gestión Académica)
**Permisos sobre:**
- Dashboard con asignaciones y disponibilidad
- Competencias x Programa
- Fichas (con coordinación auto-asignada)
- Instructor x Competencia
- Asignaciones (con calendario interactivo)
- Detalles de Asignación
- Historial de Auditoría

**Características especiales:**
- Ve solo datos de su centro de formación
- Coordinación asignada automáticamente al crear fichas
- Validaciones automáticas de conflictos
- Acceso al historial completo de cambios

### 👨‍🏫 Instructor (Consulta)
**Permisos de solo lectura:**
- Dashboard personal
- Ver sus asignaciones
- Ver sus horarios (semanal/mensual)
- Consultar información de fichas asignadas

**Características especiales:**
- Vista simplificada y enfocada
- Horario del día actual
- Próxima clase programada

---

## 🏗️ Arquitectura

### Patrón MVC (Modelo-Vista-Controlador)

#### Modelos (Model.php)
Clase base abstracta con CRUD completo:
```php
Model::all()                    // Obtener todos los registros
Model::find($id)                // Buscar por ID
Model::search($term)            // Buscar por término
Model::paginate($page, $perPage, $search)  // Paginación
Model::save($model)             // Insertar
Model::update($model)           // Actualizar
Model::delete($id)              // Eliminar
Model::count()                  // Contar registros
Model::exists($id)              // Verificar existencia
```

#### Controladores (Controller.php)
Clase base con métodos helper:
```php
$this->validatePost(['campo1', 'campo2'])  // Validar POST
$this->getPost('campo', 'default')         // Obtener POST
$this->redirectWithMessage('Mensaje')      // Redirigir con mensaje
$this->loadView('ruta/vista.php', $data)   // Cargar vista
```

#### Vistas
- Layout principal con menú dinámico según rol
- Menú con resaltado automático del elemento activo
- Formularios unificados (create/edit en un solo archivo)
- Diseño responsive con Tailwind CSS
- Validación HTML5 y JavaScript

### Funciones Auxiliares Globales (config.php)

**Navegación:**
```php
url($controller, $action, $params)    // Construir URL
redirect($controller, $action)        // Redirigir
```

**Autenticación:**
```php
currentRole()        // Obtener rol actual
currentUserId()      // Obtener ID del usuario
currentUserName()    // Obtener nombre del usuario
isAuthenticated()    // Verificar si está autenticado
hasRole($role)       // Verificar rol específico
```

**Utilidades:**
```php
e($string)                    // Escapar HTML (XSS)
formatDate($date, $format)    // Formatear fecha
formatTime($time)             // Formatear hora
```

---

## 💻 Tecnologías

### Backend
- **PHP 7.4+**: Lenguaje de programación
- **MySQL 5.7+**: Base de datos relacional con triggers de auditoría
- **PDO**: Capa de abstracción de base de datos

### Frontend
- **HTML5**: Estructura semántica
- **Tailwind CSS 3**: Framework CSS utility-first
- **JavaScript ES6**: Interactividad y validaciones
- **Font Awesome 6**: Iconos

### Arquitectura
- **MVC**: Patrón Modelo-Vista-Controlador
- **Active Record**: Patrón de acceso a datos
- **SOLID**: Principios de diseño orientado a objetos

---

## 🚀 Funcionalidades Destacadas

### 1. Sistema de Menú Inteligente
- **Resaltado automático**: El elemento del menú se resalta según la página actual
- **Soporte CRUD completo**: Funciona en show, create, edit, save, update, delete
- **Efectos visuales**: Gradientes, sombras, bordes e indicadores
- **Responsive**: Se adapta a diferentes tamaños de pantalla

### 2. Gestión de Fichas para Coordinadores
- **Coordinación automática**: Se asigna automáticamente al coordinador logueado
- **Validación de duplicados**: Mensaje claro si el número de ficha ya existe
- **Campo no editable**: Los coordinadores no pueden cambiar su coordinación
- **Flexibilidad**: Otros roles mantienen funcionalidad completa

### 3. Sistema de Auditoría Completo
- **Tabla**: `AUDITORIA_ASIGNACION` con campos JSON
- **Triggers automáticos**: INSERT, UPDATE, DELETE
- **Vista de historial**: Accesible desde botón en lista de asignaciones
- **Detalles de cambios**: Muestra campos modificados con valores antes/después
- **Colores por acción**: Verde (creación), Amarillo (modificación), Rojo (eliminación)
- **Resolución de IDs**: Muestra nombres legibles en lugar de IDs
- **Captura de usuario**: Registra quién hizo cada cambio

### 4. Validación de Conflictos de Horarios
- **Conflictos de instructor**: Detecta si el instructor ya está asignado en otro ambiente
- **Conflictos de ambiente**: Detecta si el ambiente ya está ocupado
- **Validación en tiempo real**: Frontend y backend
- **Mensajes claros**: Indica exactamente dónde está el conflicto
- **Edición segura**: Excluye la asignación actual al validar

### 5. Calendario Interactivo Unificado
- **Componente reutilizable**: Un solo componente para todos los roles
- **Dos modos de operación**:
  - `readonly`: Solo visualización (Instructor, Ver Detalles)
  - `editable`: Crear/editar horarios (Coordinador)
- **Programación visual**: Selección de fechas y horarios en calendario
- **Validación de jornadas**: Advertencias si los horarios no coinciden con la jornada
- **Validación de horas**: Impide exceder las horas totales de la competencia
- **Bloqueo de domingos**: No permite programar clases los domingos
- **Cálculo automático**: Suma de horas programadas en tiempo real
- **Validación 80%**: Verifica que se cumpla el mínimo de horas
- **Modales unificados**: Alertas y confirmaciones con diseño consistente
- **Vista de tabla**: Muestra todos los detalles del día en formato tabla
- **Trazabilidad completa**: Soporte para múltiples clases en el mismo día

### 6. Filtrado Dinámico
- **Por centro**: Coordinadores ven solo datos de su centro
- **Por sede**: Ambientes se filtran según la sede seleccionada
- **Por programa**: Competencias se filtran según el programa
- **Por instructor**: Competencias disponibles según el instructor

### 7. Sistema de Notificaciones Mejorado
- **Modales personalizados**: Reemplazo de alert() y confirm() nativos
- **Tres tipos de alertas**: Error, Advertencia, Información
- **Confirmaciones elegantes**: Diseño profesional con botones claros
- **Consistencia visual**: Mismo estilo en creación y edición
- **Accesibilidad**: Soporte para teclado (ESC para cerrar)

---

## 📚 Manual de Usuario

El sistema incluye un manual de usuario completo con 64 capturas de pantalla:

- **Archivo HTML**: `MANUAL_USUARIO_PROGSENA_CORREGIDO.html`
- **Archivo PDF**: `MANUAL_USUARIO_PROGSENA_CORREGIDO.pdf`

### Contenido del Manual:
1. **Acceso al Sistema**: Login, dashboard, cerrar sesión
2. **Centro de Formación**: Gestión de sedes, ambientes, programas, instructores, competencias, coordinadores
3. **Coordinador**: Competencias x programa, fichas, instructor x competencia, asignaciones, historial de auditoría
4. **Instructor**: Ver asignaciones, horarios, calendario personal

### Capturas de Pantalla:
- **01_acceso**: 6 capturas (login, dashboard, errores)
- **02_centro_formacion**: 19 capturas (gestión de recursos)
- **03_coordinador**: 29 capturas (gestión académica, asignaciones, auditoría)
- **04_instructor**: 11 capturas (vista de horarios)

---

## 🔐 Seguridad

### Implementaciones de Seguridad
- **Contraseñas**: Hasheadas con `password_hash()` y verificadas con `password_verify()`
- **XSS**: Prevención con `htmlspecialchars()` en todas las salidas
- **SQL Injection**: Prepared statements en todas las consultas
- **Sesiones**: Configuración segura con timeout
- **Validación**: HTML5 en frontend + servidor en backend
- **Auditoría**: Registro completo de cambios en asignaciones

### Recomendaciones para Producción
- ⚠️ Usar HTTPS obligatorio
- ⚠️ Configurar `session.cookie_secure` y `session.cookie_httponly`
- ⚠️ Implementar rate limiting en el login
- ⚠️ Agregar CAPTCHA después de varios intentos fallidos
- ⚠️ Implementar CSRF tokens en formularios
- ⚠️ Configurar backups automáticos de la base de datos
- ⚠️ Revisar y actualizar contraseñas regularmente

---

## 📝 Guía de Desarrollo

### Agregar un Nuevo Módulo

#### 1. Crear el Modelo
```php
<?php
require_once __DIR__ . '/Model.php';

class MiModelo extends Model {
    protected static $table = 'MI_TABLA';
    protected static $primaryKey = 'id';
    protected static $columns = ['id', 'nombre', 'descripcion'];
    
    // Constructor, getters, setters
    
    protected static function fromArray($data) {
        return new self($data['id'], $data['nombre']);
    }
    
    public function toArray() {
        return ['id' => $this->id, 'nombre' => $this->nombre];
    }
    
    public function validate() {
        $errors = [];
        if (empty($this->nombre)) {
            $errors[] = 'El nombre es obligatorio';
        }
        return $errors;
    }
}
```

#### 2. Crear el Controlador
```php
<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/MiModelo.php';

class MiControlador extends Controller {
    protected $controllerName = 'micontrolador';
    
    function __construct() {
        AuthController::requireAuth();
    }
    
    function show() {
        $page = (int)$this->getGet('page', 1);
        $search = $this->getGet('search', '');
        $pagination = MiModelo::paginate($page, 10, $search);
        $this->loadView('views/mimodelo/index.php', ['pagination' => $pagination]);
    }
    
    function save() {
        try {
            $this->validatePost(['nombre']);
            $modelo = new MiModelo(null, $this->getPost('nombre'));
            MiModelo::save($modelo);
            $this->redirectWithMessage('Registro creado exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error: ' . $e->getMessage(), 'error');
        }
    }
}
```

#### 3. Crear las Vistas
- `form.php`: Formulario unificado para crear/editar
- `create.php`: Incluye form.php
- `edit.php`: Incluye form.php con datos
- `index.php`: Lista con búsqueda y paginación

#### 4. Agregar al Menú
Editar `views/layout/layout.php` y agregar el item al array `$menuItems` del rol correspondiente.

### Convenciones de Código

#### Nomenclatura
- **Modelos**: Singular, PascalCase (`Sede`, `Ambiente`)
- **Controladores**: Singular + "Controller" (`SedeController`)
- **Vistas**: Carpeta plural minúscula (`views/sede/`)
- **Tablas**: Mayúsculas con guiones bajos (`SEDE`, `AMBIENTE`)
- **Claves primarias**: `tabla_id` (`sede_id`, `amb_id`)

#### Validación
- HTML5 en frontend (required, pattern, minlength, maxlength)
- Método `validate()` en modelo
- Validación adicional en controlador si es necesario

#### Seguridad
- Siempre usar `e()` o `htmlspecialchars()` al mostrar datos
- Prepared statements en todas las consultas SQL
- Validar y sanitizar todos los inputs
- Verificar autenticación y permisos

---

## 📊 Estado del Proyecto

### Módulos Completamente Funcionales (13/13)
1. ✅ Sede
2. ✅ Ambiente
3. ✅ Centro de Formación
4. ✅ Coordinación
5. ✅ Título de Programa
6. ✅ Programa
7. ✅ Competencia
8. ✅ Competencia por Programa
9. ✅ Instructor
10. ✅ Instructor por Competencia
11. ✅ Ficha (con mejoras para coordinadores)
12. ✅ Asignación (con validación de conflictos y auditoría)
13. ✅ Detalle Asignación (con calendario interactivo)

### Funcionalidades Implementadas
- ✅ Sistema de autenticación completo
- ✅ Dashboards personalizados por rol
- ✅ CRUD completo para todos los módulos
- ✅ Validación de conflictos de horarios (instructor y ambiente)
- ✅ Validación de horas de competencia (no exceder límite)
- ✅ Sistema de auditoría con historial
- ✅ Menú con resaltado automático
- ✅ Calendario interactivo unificado (componente reutilizable)
- ✅ Modales personalizados para alertas y confirmaciones
- ✅ Vista de tabla para detalles de asignación por día
- ✅ Filtrado dinámico por centro/sede/programa
- ✅ Búsqueda y paginación en todos los módulos
- ✅ Mensajes de sesión con auto-cierre
- ✅ Modal de confirmación para eliminaciones
- ✅ Trazabilidad completa de múltiples clases por día

---

## 🎯 Notas de Producción

### Base de Datos
- La base de datos se entrega limpia (sin usuarios de prueba)
- El administrador debe crear las credenciales iniciales
- Incluye triggers de auditoría para la tabla ASIGNACION
- Estructura completa con 15 tablas + 1 tabla de auditoría

### Configuración Inicial
1. Importar `database.sql`
2. Configurar `config.php` con credenciales de BD
3. Crear usuario administrador (Centro de Formación)
4. Configurar sedes y ambientes
5. Crear coordinadores e instructores
6. Configurar programas y competencias

### Mantenimiento
- Revisar logs de auditoría regularmente
- Hacer backups de la base de datos
- Actualizar contraseñas periódicamente
- Monitorear conflictos de horarios
- Revisar asignaciones completadas

---

## 📞 Soporte

Para reportar problemas o solicitar nuevas características:
1. Revisar el manual de usuario
2. Consultar la documentación técnica
3. Contactar al equipo de desarrollo

---

## 📄 Licencia

Sistema desarrollado para la gestión académica del SENA.

---

**Última actualización**: Diciembre 2024  
**Versión**: 1.0.0  
**Estado**: ✅ PRODUCCIÓN READY

---

## 🎓 Créditos

Desarrollado para el SENA (Servicio Nacional de Aprendizaje) como sistema integral de gestión de horarios y competencias.

---

## 🔄 Changelog

### Versión 1.0.0 (Diciembre 2024)
- ✅ Implementación completa de todos los módulos
- ✅ Sistema de auditoría con triggers automáticos
- ✅ Calendario interactivo unificado con componente reutilizable
- ✅ Validación de conflictos de horarios (instructor y ambiente)
- ✅ Validación de horas de competencia (límite máximo)
- ✅ Modales personalizados para alertas y confirmaciones
- ✅ Vista de tabla para detalles de asignación
- ✅ Soporte para múltiples clases en el mismo día
- ✅ Limpieza de código y eliminación de archivos no utilizados
- ✅ Documentación completa del componente de calendario
- ✅ Manual de usuario con 64 capturas de pantalla
