<p align="center">
  <img src="assets/banner.png" alt="ProgSENA Banner" width="100%">
</p>

<h1 align="center">ProgSENA</h1>

<p align="center">
  <strong>Sistema de Gestión de Horarios y Competencias</strong>
</p>

<p align="center">
  Sistema web MVC desarrollado para la gestión eficiente de asignaciones académicas, horarios y competencias de los instructores del SENA (Servicio Nacional de Aprendizaje).
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-%3E%3D%207.4-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Version">
  <img src="https://img.shields.io/badge/MySQL-%3E%3D%205.7-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL Version">
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Estado-Listo%20para%20Producci%C3%B3n-brightgreen?style=for-the-badge" alt="Estado">
</p>

---

## 📋 Tabla de Contenidos

- [Descripción del Proyecto](#-descripción-del-proyecto)
- [Requisitos del Sistema](#-requisitos-del-sistema)
- [Instalación y Configuración](#-instalación-y-configuración)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Características Principales](#-características-principales)
- [Roles y Permisos](#-roles-y-permisos)
- [Arquitectura del Software](#-arquitectura-del-software)
- [Tecnologías Utilizadas](#-tecnologías-utilizadas)
- [Funcionalidades Destacadas](#-funcionalidades-destacadas)
- [Manual de Usuario](#-manual-de-usuario)
- [Seguridad y Buenas Prácticas](#-seguridad-y-buenas-prácticas)
- [Guía de Desarrollo](#-guía-de-desarrollo)
- [Estado del Proyecto](#-estado-del-proyecto)
- [Notas de Producción](#-notas-de-producción)
- [Soporte y Contacto](#-soporte)
- [Licencia](#-licencia)
- [Historial de Cambios (Changelog)](#-changelog)

---

## 📖 Descripción del Proyecto

**ProgSENA** es un sistema de información web MVC robusto, concebido para optimizar la programación de horarios, la asignación de instructores a fichas y la gestión integral de competencias en el Centro de Formación del SENA. Su principal objetivo es simplificar los procesos organizacionales de planificación académica, previniendo conflictos de disponibilidad horaria y manteniendo una trazabilidad exhaustiva de todas las asignaciones.

### Características Clave:
*   **Gestión Centralizada de Recursos**: Control total sobre sedes, ambientes físicos, programas formativos, instructores y competencias asignadas.
*   **Programación Académica Inteligente**: Asignación ágil de instructores a fichas mediante un calendario interactivo con validación de restricciones horarias.
*   **Auditoría y Transparencia**: Seguimiento pormenorización de las modificaciones y eliminaciones de programaciones gracias a un sistema de auditoría basado en triggers.
*   **Seguridad Basada en Roles**: Control estricto de accesos y menús dinámicos personalizados para Centro de Formación, Coordinadores e Instructores.

---

## 🔧 Requisitos del Sistema

Para el correcto funcionamiento de ProgSENA, el entorno de ejecución debe cumplir con las siguientes especificaciones técnicas:

*   **Lenguaje**: PHP 7.4 o superior
*   **Base de Datos**: MySQL 5.7 o superior
*   **Servidor Web**: Apache (con módulo `mod_rewrite` habilitado) o Nginx
*   **Extensiones PHP requeridas**: 
    *   `PDO` (PHP Data Objects)
    *   `PDO_MySQL`

---

## 📦 Instalación y Configuración

Siga los siguientes pasos para desplegar el proyecto de manera local:

### 1. Clonar el repositorio
```bash
git clone https://github.com/crisedeam/Sistema-Gestion-Competencias-SENA.git
cd Sistema-Gestion-Competencias-SENA
```

### 2. Configurar la Base de Datos

#### Opción A: A través de Consola (CLI)
```bash
mysql -u tu_usuario -p < database.sql
```

#### Opción B: A través de Administradores Web (phpMyAdmin, DBeaver, etc.)
1.  Cree una base de datos llamada `ProgSENA`.
2.  Importe el script SQL contenido en el archivo [database.sql](file:///c:/Users/CRISTIHAN%2020/Desktop/cxcx/Sistema-Gestion-Competencias-SENA/database.sql).

### 3. Configurar Conexión a la Base de Datos

Edite o cree el archivo `config.php` a partir de `config.example.php` configurando los parámetros de su entorno local:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ProgSENA');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');
```

> [!IMPORTANT]
> El script base de datos se entrega completamente limpio de registros de prueba. El administrador deberá registrar el primer usuario para acceder al sistema.

### 4. Acceso en el Navegador
Inicie su servidor local (como XAMPP, Laragon o Apache) y acceda al directorio del proyecto:
```text
http://localhost/Sistema-Gestion-Competencias-SENA/
```

---

## 📁 Estructura del Proyecto

A continuación se detalla la organización de los directorios y archivos principales del sistema:

```text
Sistema-Gestion-Competencias-SENA/
├── config.php                 # Parámetros de configuración global y helpers auxiliares
├── index.php                  # Enrutador principal del sistema (Front Controller)
├── Database.php               # Singleton/Clase para la conexión mediante PDO
├── database.sql               # Esquema de base de datos con triggers de auditoría incorporados
│
├── controllers/               # Controladores MVC (18 controladores)
│   ├── Controller.php         # Controlador base con utilidades de renderizado y validación
│   ├── AuthController.php     # Gestión de sesiones, login, logout y roles
│   ├── DashboardController.php# Dashboard diferenciado según tipo de usuario
│   ├── AsignacionController.php# Lógica de asignaciones y comprobación de conflictos
│   └── ...
│
├── models/                    # Modelos de Datos - Patrón Active Record (14 modelos)
│   ├── Model.php              # Modelo base abstracto con métodos CRUD automatizados
│   ├── Asignacion.php         # Modelo para representar asignaciones horarias
│   ├── Instructor.php         # Modelo para almacenar información de instructores
│   └── ...
│
├── views/                     # Vistas (Plantillas HTML embebidas con PHP)
│   ├── layout/
│   │   └── layout.php         # Plantilla base (Header, Sidebar responsivo, Footer)
│   ├── assets/                # Recursos estáticos locales
│   │   ├── calendario-component.js   # Lógica JS para calendario reactivo
│   │   ├── calendario-styles.css     # Hoja de estilos del calendario
│   │   ├── horarios-styles.css       # Estilos específicos para FullCalendar
│   │   └── CALENDARIO_COMPONENT_README.md  # Documentación del calendario
│   ├── auth/                  # Formulario de inicio de sesión
│   ├── dashboard/             # Dashboards específicos por perfil
│   ├── asignacion/
│   │   ├── index.php          # Listado de programaciones
│   │   ├── form.php           # Formulario unificado de creación y edición
│   │   ├── form_script.php    # Scripts JS de validación dinámica de calendarios
│   │   └── historial.php      # Panel de auditoría de asignaciones
│   ├── detalleAsignacion/
│   │   ├── index.php          # Tabla semanal de detalles de horario por día
│   │   ├── create.php
│   │   └── edit.php
│   ├── instructor/
│   │   ├── mis-asignaciones.php
│   │   └── mis-horarios.php   # Horario interactivo personal del instructor
│   └── ...
```

---

## ✨ Características Principales

*   **Autenticación Segura**: 
    *   Módulo de login integrado con validación estricta de credenciales.
    *   Contraseñas encriptadas mediante la función nativa `password_hash()`.
    *   Expiración segura de sesión (timeout) y protección frente a accesos no autorizados.
*   **Panel de Control Personalizado (Dashboard)**:
    *   **Centro de Formación**: Métricas rápidas sobre cantidad de instructores, sedes y ambientes registrados en el centro.
    *   **Coordinador**: Visualización en tiempo real de ambientes ocupados y últimas programaciones realizadas.
    *   **Instructor**: Calendario con sus asignaciones actuales, horarios semanales y detalles de la ficha a impartir.
*   **Gestión Integral de Recursos**:
    *   Administración completa de Sedes, Ambientes (aulas de formación), Programas Académicos, Competencias Formativas, Fichas (grupos de aprendizaje) y Coordinaciones del Centro.
*   **Lógica Académica Avanzada**:
    *   Asociación dinámica de Competencias a Programas específicos.
    *   Vinculación de instructores con las competencias que están avalados para impartir.

---

## 👥 Roles y Permisos

El sistema implementa un robusto control de acceso basado en roles (RBAC) con permisos bien delimitados:

### 🏢 Centro de Formación (Administrador)
Posee privilegios de administración global sobre los recursos de su sede:
*   Visualización de estadísticas globales en el Dashboard.
*   Control total (Crear, Leer, Actualizar, Borrar) en Sedes, Ambientes, Programas, Competencias y Coordinaciones de su respectivo Centro de Formación.
*   Asociación y asignación automática de Centro de Formación a todos los recursos creados.

### 📋 Coordinador (Gestión Académica)
Maneja la operación y distribución horaria:
*   Asociación de competencias a programas formativos.
*   Gestión de fichas activas e Instructor por Competencia.
*   Programación y control de horarios en el Calendario Interactivo.
*   Acceso exclusivo al Historial de Auditoría para visualizar las operaciones realizadas.
*   Asignación automática de su área de coordinación a las fichas que registre.

### 👨‍🏫 Instructor (Vista de Consulta)
Perfil orientado a la visualización de su carga académica:
*   Dashboard personal simplificado con alertas y accesos directos.
*   Visualización interactiva de su horario semanal y mensual.
*   Acceso a las asignaciones vigentes y consulta rápida sobre información de las fichas asociadas.

---

## 🏗️ Arquitectura del Software

ProgSENA se basa en el patrón de arquitectura **MVC (Modelo-Vista-Controlador)** de desarrollo propio en PHP nativo, asegurando un acoplamiento débil entre componentes y facilidad de extensión.

### 1. Capa de Modelos (`models/Model.php`)
Los modelos heredan de la clase base abstracta `Model`, la cual implementa un ORM ligero mediante el patrón *Active Record* para resolver las operaciones CRUD automáticamente:

```php
Model::all();                    // Recuperar todos los registros
Model::find($id);                // Buscar un registro por su clave primaria
Model::search($term);            // Realizar búsquedas parametrizadas
Model::paginate($page, $perPage, $search);  // Paginación de registros
Model::save($model);             // Insertar nueva fila
Model::update($model);           // Modificar datos del registro
Model::delete($id);              // Borrar de la base de datos
Model::count();                  // Obtener total de registros
Model::exists($id);              // Validar existencia de clave
```

### 2. Capa de Controladores (`controllers/Controller.php`)
Los controladores heredan de `Controller.php`, simplificando la interacción con peticiones HTTP y carga de interfaces:

```php
$this->validatePost(['campo1', 'campo2']);  // Validación de obligatoriedad en POST
$this->getPost('campo', 'default');         // Sanitización y obtención segura de POST
$this->redirectWithMessage('Mensaje');      // Redirección con flujos de mensajes flash
$this->loadView('ruta/vista.php', $data);   // Renderizado de vistas con paso de variables
```

### 3. Funciones Globales de Conveniencia (`config.php`)
Proporciona helpers simplificados para ser consumidos en cualquier punto del flujo de ejecución:

| Categoría | Función | Propósito |
| :--- | :--- | :--- |
| **Navegación** | `url($controller, $action, $params)` | Generación estructurada de rutas de acceso. |
| **Navegación** | `redirect($controller, $action)` | Redirección de cabecera HTTP simplificada. |
| **Sesiones** | `currentRole()` | Retorna el rol actual del usuario autenticado. |
| **Sesiones** | `currentUserId()` | Retorna el ID de sesión del usuario actual. |
| **Sesiones** | `isAuthenticated()` | Evalúa si existe una sesión activa y válida. |
| **Seguridad** | `e($string)` | Sanitiza cadenas para evitar inyecciones XSS en el HTML. |
| **Formato** | `formatDate($date, $format)` | Da formato personalizado a fechas del sistema. |

---

## 💻 Tecnologías Utilizadas

*   **Backend**: 
    *   **PHP 7.4+** (Nativo, POO, MVC)
    *   **MySQL 5.7+** (Triggers, Relaciones InnoDB)
    *   **PDO** (Consultas preparadas, conexiones seguras)
*   **Frontend**:
    *   **HTML5** (Semántico)
    *   **Tailwind CSS 3** (Diseño moderno, fluido y responsivo)
    *   **JavaScript ES6** (Validaciones del lado del cliente, AJAX)
    *   **Font Awesome 6** (Librería de iconos vectoriales)
*   **Metodologías / Arquitectura**:
    *   Patrón de Diseño MVC y Active Record.
    *   Principios de diseño orientado a objetos **SOLID**.

---

## 🚀 Funcionalidades Destacadas

### 1. Validación Inteligente de Conflictos de Horarios
*   **Evita el solapamiento de horarios**: El motor de validación comprueba dinámicamente si un ambiente está ocupado en la misma fecha y rango de horas.
*   **Disponibilidad de instructores**: Valida que un instructor no esté asignado en otra sede o ambiente en el mismo horario.
*   **Validaciones en capas**: Seguridad implementada tanto en frontend (mediante JS interactivo) como en backend (validaciones PHP estrictas).

### 2. Calendario Interactivo Unificado
*   Desarrollado como un componente modular reutilizable a través del sistema.
*   **Modos dinámicos**: Admite modo `editable` (crear/arrastrar/modificar asignaciones para Coordinadores) y modo `readonly` (consulta para Instructores).
*   **Validación de Jornadas**: Emite alertas si las horas de asignación están fuera del rango establecido para la jornada de la ficha.
*   **Bloqueo de Domingos**: Restricción automática para evitar programar actividades en días no laborables.
*   **Contabilización en tiempo real**: Suma automática de las horas programadas para garantizar el cumplimiento del 80% mínimo de la competencia.

### 3. Registro de Auditoría Detallado (Triggers de BD)
*   Seguimiento absoluto mediante la tabla dedicada `AUDITORIA_ASIGNACION`.
*   Monitoreo automatizado a nivel de base de datos (`INSERT`, `UPDATE`, `DELETE`).
*   **Visor de cambios**: Un panel que compara el valor anterior vs. el valor nuevo en formato estructurado JSON.
*   Diferenciación de color para auditorías: Verde (Creación), Amarillo (Actualización) y Rojo (Eliminación).

---

## 📚 Manual de Usuario

El sistema incluye documentación de ayuda detallada con capturas de pantalla organizadas por módulos para guiar a cada tipo de usuario:

*   **Manual Web**: `MANUAL_USUARIO_PROGSENA_CORREGIDO.html`
*   **Manual Imprimible**: `MANUAL_USUARIO_PROGSENA_CORREGIDO.pdf`

El manual describe flujos detallados de login, adición de ambientes, gestión de competencias, asignación académica interactiva y revisión del historial de auditoría.

---

## 🔐 Seguridad y Buenas Prácticas

ProgSENA se ha diseñado bajo estrictas pautas de seguridad informática para proteger la integridad de los datos académicos:

*   **Prevención de SQL Injections**: Utilización estricta de sentencias preparadas de PDO en todas las consultas del Active Record.
*   **Control de XSS (Cross-Site Scripting)**: Salidas sanitizadas con la función helper `e()` para prevenir inyección de código malicioso en las vistas.
*   **Seguridad de Contraseñas**: Implementación de algoritmo `bcrypt` nativo.

> [!WARNING]
> **Recomendaciones esenciales para entornos de producción:**
> 1. Habilitar obligatoriamente protocolo seguro de transferencia **HTTPS**.
> 2. Ajustar directivas PHP `session.cookie_secure` y `session.cookie_httponly`.
> 3. Implementar protección anti-CSRF mediante tokens en formularios de actualización y eliminación.
> 4. Configurar mecanismos de respaldo de la base de datos de manera automatizada.

---

## 📝 Guía de Desarrollo

### 1. ¿Cómo crear un nuevo módulo?

#### Paso A: Definir el Modelo (`models/MiModelo.php`)
Cree una clase que extienda de `Model` configurando las columnas correspondientes a la tabla física:

```php
<?php
require_once __DIR__ . '/Model.php';

class MiModelo extends Model {
    protected static $table = 'MI_TABLA';
    protected static $primaryKey = 'id';
    protected static $columns = ['id', 'nombre', 'descripcion'];
    
    // Implemente getters, setters y constructor...
    
    protected static function fromArray($data) {
        return new self($data['id'], $data['nombre'], $data['descripcion']);
    }
    
    public function toArray() {
        return [
            'id' => $this->id, 
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion
        ];
    }
    
    public function validate() {
        $errors = [];
        if (empty($this->nombre)) {
            $errors[] = 'El campo Nombre es obligatorio.';
        }
        return $errors;
    }
}
```

#### Paso B: Definir el Controlador (`controllers/MiControlador.php`)
Cree su controlador heredando del controlador principal:

```php
<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/MiModelo.php';

class MiControlador extends Controller {
    protected $controllerName = 'micontrolador';
    
    public function __construct() {
        AuthController::requireAuth(); // Requerir inicio de sesión
    }
    
    public function show() {
        $page = (int)$this->getGet('page', 1);
        $search = $this->getGet('search', '');
        $pagination = MiModelo::paginate($page, 10, $search);
        
        $this->loadView('views/mimodelo/index.php', [
            'pagination' => $pagination
        ]);
    }
    
    public function save() {
        try {
            $this->validatePost(['nombre']);
            $modelo = new MiModelo(null, $this->getPost('nombre'), $this->getPost('descripcion'));
            MiModelo::save($modelo);
            $this->redirectWithMessage('¡Registro creado exitosamente!');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al guardar: ' . $e->getMessage(), 'error');
        }
    }
}
```

#### Paso C: Diseñar las Vistas
Cree el directorio `views/mimodelo/` y agregue las plantillas correspondientes (`index.php`, `create.php`, `edit.php`, `form.php`).

---

## 📊 Estado del Proyecto

### Módulos Implementados (13/13)
*   [x] **Sede** (Gestión de ubicaciones físicas)
*   [x] **Ambiente** (Gestión de aulas de formación)
*   [x] **Centro de Formación** (Datos generales de centros)
*   [x] **Coordinación** (Secciones de formación)
*   [x] **Título de Programa** (Grados académicos)
*   [x] **Programa** (Tecnologías, especializaciones)
*   [x] **Competencia** (Módulos de aprendizaje)
*   [x] **Competencia por Programa** (Asociación estructurada)
*   [x] **Instructor** (Perfil docente)
*   [x] **Instructor por Competencia** (Habilitación de enseñanza)
*   [x] **Ficha** (Grupos y jornadas académicas)
*   [x] **Asignación** (Lógica de horarios y calendario)
*   [x] **Detalle Asignación** (Distribución semanal de carga horaria)

---

## 🎯 Notas de Producción

*   **Migración**: El script `database.sql` contiene las estructuras de tablas indexadas y triggers optimizados.
*   **Limpieza**: No contiene registros basura ni cuentas por defecto para garantizar un inicio limpio y seguro.
*   **Versión Actual**: `1.0.0` (Listo para Producción)

---



---

## 📄 Licencia

Este sistema es software propietario y de uso educativo desarrollado específicamente para la gestión interna de asignación académica del **SENA**.

---

<p align="center">
  <sub>Desarrollado para el Servicio Nacional de Aprendizaje (SENA). Última actualización: Diciembre 2024.</sub>
</p>
