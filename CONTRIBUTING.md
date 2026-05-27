# Guía de Contribución - ProgSENA

¡Gracias por tu interés en contribuir a ProgSENA! Este documento proporciona las pautas para contribuir al proyecto.

## 📋 Tabla de Contenidos

- [Código de Conducta](#código-de-conducta)
- [Cómo Contribuir](#cómo-contribuir)
- [Reportar Bugs](#reportar-bugs)
- [Sugerir Mejoras](#sugerir-mejoras)
- [Proceso de Pull Request](#proceso-de-pull-request)
- [Estándares de Código](#estándares-de-código)
- [Estructura de Commits](#estructura-de-commits)

## 📜 Código de Conducta

Este proyecto se adhiere a un código de conducta. Al participar, se espera que mantengas este código. Por favor, reporta comportamientos inaceptables.

## 🤝 Cómo Contribuir

### Configuración del Entorno de Desarrollo

1. **Fork el repositorio**
   ```bash
   git clone https://github.com/crisedeam/Sistema-Gestion-Competencias-SENA.git
   cd ProgSENA
   ```

2. **Configurar la base de datos**
   ```bash
   mysql -u root -p < database.sql
   ```

3. **Configurar el archivo de configuración**
   ```bash
   cp config.example.php config.php
   # Editar config.php con tus credenciales
   ```

4. **Crear una rama para tu feature**
   ```bash
   git checkout -b feature/nombre-descriptivo
   ```

## 🐛 Reportar Bugs

Si encuentras un bug, por favor crea un issue con la siguiente información:

### Plantilla de Reporte de Bug

```markdown
**Descripción del Bug**
Una descripción clara y concisa del bug.

**Pasos para Reproducir**
1. Ir a '...'
2. Hacer clic en '...'
3. Scroll hasta '...'
4. Ver error

**Comportamiento Esperado**
Una descripción clara de lo que esperabas que sucediera.

**Comportamiento Actual**
Una descripción clara de lo que realmente sucede.

**Capturas de Pantalla**
Si es aplicable, agrega capturas de pantalla.

**Entorno**
- Navegador: [ej. Chrome 120]
- PHP: [ej. 7.4]
- MySQL: [ej. 5.7]
- Sistema Operativo: [ej. Windows 10]

**Información Adicional**
Cualquier otra información relevante sobre el problema.
```

## 💡 Sugerir Mejoras

Las sugerencias de mejoras son bienvenidas. Por favor, crea un issue con:

- **Título descriptivo**
- **Descripción detallada** de la mejora propuesta
- **Justificación** de por qué sería útil
- **Ejemplos** de cómo funcionaría (si aplica)

## 🔄 Proceso de Pull Request

1. **Asegúrate de que tu código sigue los estándares**
   - Código limpio y bien documentado
   - Sin errores de sintaxis
   - Funcionalidad probada

2. **Actualiza la documentación**
   - README.md si es necesario
   - Comentarios en el código
   - Manual de usuario si aplica

3. **Crea el Pull Request**
   - Título descriptivo
   - Descripción detallada de los cambios
   - Referencias a issues relacionados

4. **Espera la revisión**
   - Responde a los comentarios
   - Realiza los cambios solicitados
   - Mantén la conversación profesional

## 📝 Estándares de Código

### PHP

#### Nomenclatura
- **Clases**: PascalCase (`SedeController`, `Instructor`)
- **Métodos**: camelCase (`save()`, `searchById()`)
- **Variables**: camelCase (`$userName`, `$totalHoras`)
- **Constantes**: UPPER_SNAKE_CASE (`DB_HOST`, `SESSION_TIMEOUT`)

#### Estructura de Archivos
```php
<?php
/**
 * Descripción breve del archivo
 * 
 * Descripción más detallada si es necesario
 */

// Requires
require_once __DIR__ . '/Model.php';

// Clase
class MiClase extends ClaseBase {
    // Propiedades
    private $propiedad;
    
    // Constructor
    public function __construct() {
        // ...
    }
    
    // Métodos públicos
    public function metodoPublico() {
        // ...
    }
    
    // Métodos privados
    private function metodoPrivado() {
        // ...
    }
}
```

#### Seguridad
- **Siempre** usar prepared statements para consultas SQL
- **Siempre** escapar output con `htmlspecialchars()` o `e()`
- **Siempre** validar y sanitizar inputs
- **Nunca** confiar en datos del usuario

### JavaScript

#### Nomenclatura
- **Variables**: camelCase (`userName`, `totalHoras`)
- **Constantes**: UPPER_SNAKE_CASE (`MAX_ATTEMPTS`)
- **Funciones**: camelCase (`mostrarModal()`, `validarHorario()`)

#### Estructura
```javascript
// Constantes
const MAX_HORAS = 16;

// Variables globales (evitar si es posible)
let horariosProgramados = [];

// Funciones
function nombreDescriptivo(parametro) {
    // Validaciones
    if (!parametro) {
        return;
    }
    
    // Lógica
    // ...
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Inicialización
});
```

### SQL

#### Nomenclatura
- **Tablas**: MAYÚSCULAS con guiones bajos (`SEDE`, `AMBIENTE`)
- **Columnas**: minúsculas con guiones bajos (`sede_id`, `amb_nombre`)
- **Claves primarias**: `tabla_id` (`sede_id`, `amb_id`)
- **Claves foráneas**: `TABLA_tabla_id` (`SEDE_sede_id`)

### CSS

#### Nomenclatura
- **Clases**: kebab-case (`evento-calendario`, `modal-detalle`)
- **IDs**: camelCase (`calendarioInstructor`, `modalHorario`)

## 📦 Estructura de Commits

Usa mensajes de commit descriptivos siguiendo esta convención:

```
tipo(alcance): descripción breve

Descripción más detallada si es necesario.

Fixes #123
```

### Tipos de Commit
- `feat`: Nueva funcionalidad
- `fix`: Corrección de bug
- `docs`: Cambios en documentación
- `style`: Cambios de formato (no afectan el código)
- `refactor`: Refactorización de código
- `test`: Agregar o modificar tests
- `chore`: Tareas de mantenimiento

### Ejemplos
```bash
feat(asignacion): agregar validación de horas máximas

Implementa validación para evitar que se excedan las horas
totales de la competencia al programar clases.

Fixes #45

---

fix(calendario): corregir alineación del día actual

El contenido del día actual estaba desalineado debido a
estilos CSS conflictivos.

Fixes #67

---

docs(readme): actualizar sección de instalación

Agrega instrucciones más detalladas para la configuración
inicial del proyecto.
```

## ✅ Checklist antes de Enviar PR

- [ ] El código sigue los estándares del proyecto
- [ ] Los cambios están probados y funcionan correctamente
- [ ] La documentación está actualizada
- [ ] Los commits tienen mensajes descriptivos
- [ ] No hay conflictos con la rama principal
- [ ] Se agregaron comentarios donde sea necesario
- [ ] Se validó la seguridad del código

## 🧪 Testing

Antes de enviar un PR, asegúrate de probar:

1. **Funcionalidad básica**
   - Login/Logout
   - CRUD de módulos afectados
   - Navegación entre páginas

2. **Validaciones**
   - Campos requeridos
   - Formatos de datos
   - Mensajes de error

3. **Seguridad**
   - XSS
   - SQL Injection
   - Autenticación/Autorización

4. **Compatibilidad**
   - Diferentes navegadores
   - Diferentes tamaños de pantalla
   - Diferentes roles de usuario

## 📞 Contacto

Si tienes preguntas sobre cómo contribuir, no dudes en:
- Abrir un issue con la etiqueta `question`
- Contactar al equipo de desarrollo

---

¡Gracias por contribuir a ProgSENA! 🎓
