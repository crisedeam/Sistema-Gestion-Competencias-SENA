# Componente de Calendario Unificado - ProgSENA

## Descripción

Este componente proporciona una interfaz unificada para mostrar calendarios en todo el sistema ProgSENA. Utiliza FullCalendar como base y añade funcionalidades específicas del sistema.

## Archivos del Componente

- `calendario-component.js` - Clase JavaScript del componente
- `calendario-styles.css` - Estilos CSS personalizados

## Modos de Operación

### 1. Modo `readonly` (Solo Lectura)
- Para visualización de horarios
- Usado por: Instructores, Vista de detalles
- Permite: Ver eventos, hacer clic para detalles
- No permite: Agregar, editar o eliminar eventos

### 2. Modo `editable` (Editable)
- Para gestión de asignaciones
- Usado por: Coordinadores (crear/editar asignaciones)
- Permite: Ver, agregar y eliminar eventos
- Incluye validaciones de conflictos

## Uso Básico

### Inicialización Simple (Modo Readonly)

```javascript
const calendario = new CalendarioProgSENA('miCalendario', {
    modo: 'readonly',
    eventos: [
        {
            title: '08:00-10:00',
            start: '2024-03-15T08:00:00',
            end: '2024-03-15T10:00:00',
            backgroundColor: '#d1fae5',
            borderColor: '#10b981',
            textColor: '#047857',
            extendedProps: {
                competencia: 'Programación Básica',
                ambiente: 'Aula 101',
                ficha: '2558963',
                horaInicio: '08:00',
                horaFin: '10:00',
                horaTexto: '08:00-10:00',
                label: 'Clase',
                icon: 'fa-chalkboard-teacher'
            }
        }
    ]
});
```

### Inicialización Avanzada (Modo Editable)

```javascript
const calendario = new CalendarioProgSENA('miCalendario', {
    modo: 'editable',
    eventos: eventosArray,
    fechaInicio: '2024-03-01',
    
    // Callback cuando se hace clic en una fecha
    onDateClick: function(fecha) {
        console.log('Fecha seleccionada:', fecha);
        // Abrir modal para agregar evento
        abrirModalAgregarEvento(fecha);
    },
    
    // Callback cuando se hace clic en un evento
    onEventClick: function(evento) {
        console.log('Evento seleccionado:', evento);
    },
    
    // Callback cuando se elimina un evento
    onEventDelete: function(fecha, index) {
        console.log('Eliminar evento:', fecha, index);
        // Lógica para eliminar el evento
    }
});

// Guardar referencia global para acceso desde HTML
window.calendarioInstance = calendario;
```

## Estructura de Eventos

Cada evento debe tener la siguiente estructura:

```javascript
{
    // Propiedades de FullCalendar
    title: 'Texto visible en el calendario',
    start: 'YYYY-MM-DDTHH:mm:ss',
    end: 'YYYY-MM-DDTHH:mm:ss',
    backgroundColor: '#color',
    borderColor: '#color',
    textColor: '#color',
    
    // Propiedades extendidas (personalizadas)
    extendedProps: {
        competencia: 'Nombre de la competencia',
        ambiente: 'Nombre del ambiente',
        ficha: 'Número de ficha',
        horaInicio: 'HH:mm',
        horaFin: 'HH:mm',
        horaTexto: 'HH:mm-HH:mm',
        label: 'Etiqueta del tipo',
        icon: 'fa-icon-name',
        tipo: 'nueva_asignacion|esta_asignacion|instructor|ambiente|combinado',
        index: 0, // Para eventos eliminables
        eliminable: true // Si se puede eliminar
    }
}
```

## Colores Estándar por Tipo

```javascript
// Nueva asignación / Esta asignación
backgroundColor: '#d1fae5'
borderColor: '#10b981'
textColor: '#047857'

// Instructor ocupado
backgroundColor: '#fef3c7'
borderColor: '#f59e0b'
textColor: '#92400e'

// Ambiente ocupado
backgroundColor: '#dbeafe'
borderColor: '#3b82f6'
textColor: '#1e40af'

// Instructor + Ambiente (combinado)
backgroundColor: '#e9d5ff'
borderColor: '#a855f7'
textColor: '#6b21a8'
```

## Métodos Públicos

### `actualizarEventos(eventos)`
Actualiza los eventos del calendario.

```javascript
calendario.actualizarEventos(nuevosEventos);
```

### `irAFecha(fecha)`
Navega a una fecha específica.

```javascript
calendario.irAFecha('2024-03-15');
```

### `actualizarRangoValido(fechaInicio)`
Actualiza el rango válido de fechas.

```javascript
calendario.actualizarRangoValido('2024-03-01');
```

### `refrescar()`
Refresca el calendario.

```javascript
calendario.refrescar();
```

### `destruir()`
Destruye la instancia del calendario.

```javascript
calendario.destruir();
```

## Ejemplos de Uso

### Ejemplo 1: Instructor - Mis Horarios (Readonly)

```php
<!-- HTML -->
<div id="calendarioInstructor"></div>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const eventos = [
        <?php foreach ($horarios as $horario): ?>
        {
            title: '<?php echo $horario['hora_inicio'] . '-' . $horario['hora_fin']; ?>',
            start: '<?php echo $horario['fecha']; ?>T<?php echo $horario['hora_inicio']; ?>',
            end: '<?php echo $horario['fecha']; ?>T<?php echo $horario['hora_fin']; ?>',
            backgroundColor: '#d1fae5',
            borderColor: '#10b981',
            textColor: '#047857',
            extendedProps: {
                competencia: '<?php echo $horario['competencia']; ?>',
                ambiente: '<?php echo $horario['ambiente']; ?>',
                ficha: '<?php echo $horario['ficha']; ?>',
                horaInicio: '<?php echo $horario['hora_inicio']; ?>',
                horaFin: '<?php echo $horario['hora_fin']; ?>',
                horaTexto: '<?php echo $horario['hora_inicio'] . '-' . $horario['hora_fin']; ?>',
                label: 'Clase',
                icon: 'fa-chalkboard-teacher'
            }
        },
        <?php endforeach; ?>
    ];
    
    window.calendarioInstance = new CalendarioProgSENA('calendarioInstructor', {
        modo: 'readonly',
        eventos: eventos
    });
});
</script>
```

### Ejemplo 2: Coordinador - Crear Asignación (Editable)

```javascript
let horariosProgramados = [];

// Inicializar calendario
const calendario = new CalendarioProgSENA('calendarioAsignacion', {
    modo: 'editable',
    eventos: prepararEventos(),
    fechaInicio: document.getElementById('asig_fecha_ini').value,
    
    onDateClick: function(fecha) {
        abrirModalHorario(fecha);
    },
    
    onEventDelete: function(fecha, index) {
        eliminarHorario(fecha, index);
    }
});

window.calendarioInstance = calendario;

function prepararEventos() {
    const eventos = [];
    
    // Agregar horarios programados
    horariosProgramados.forEach((h, index) => {
        eventos.push({
            title: `${h.hora_inicio}-${h.hora_fin}`,
            start: `${h.fecha}T${h.hora_inicio}`,
            end: `${h.fecha}T${h.hora_fin}`,
            backgroundColor: '#d1fae5',
            borderColor: '#10b981',
            textColor: '#047857',
            extendedProps: {
                tipo: 'nueva_asignacion',
                index: index,
                horaInicio: h.hora_inicio,
                horaFin: h.hora_fin,
                horaTexto: `${h.hora_inicio}-${h.hora_fin}`,
                eliminable: true,
                label: 'Nueva Asignación',
                icon: 'fa-plus-circle'
            }
        });
    });
    
    // Agregar conflictos de instructor
    asignacionesInstructor.forEach(a => {
        eventos.push({
            title: `${a.hora_inicio}-${a.hora_fin}`,
            start: `${a.fecha}T${a.hora_inicio}`,
            end: `${a.fecha}T${a.hora_fin}`,
            backgroundColor: '#fef3c7',
            borderColor: '#f59e0b',
            textColor: '#92400e',
            extendedProps: {
                tipo: 'instructor',
                competencia: a.competencia,
                ficha: a.ficha,
                horaInicio: a.hora_inicio,
                horaFin: a.hora_fin,
                horaTexto: `${a.hora_inicio}-${a.hora_fin}`,
                label: 'Instructor',
                icon: 'fa-chalkboard-teacher'
            }
        });
    });
    
    return eventos;
}

function agregarHorario(fecha, horaInicio, horaFin) {
    horariosProgramados.push({ fecha, hora_inicio: horaInicio, hora_fin: horaFin });
    calendario.actualizarEventos(prepararEventos());
}

function eliminarHorario(fecha, index) {
    const horariosDelDia = horariosProgramados.filter(h => h.fecha === fecha);
    if (index < horariosDelDia.length) {
        const horarioAEliminar = horariosDelDia[index];
        const indiceGlobal = horariosProgramados.findIndex(h =>
            h.fecha === horarioAEliminar.fecha &&
            h.hora_inicio === horarioAEliminar.hora_inicio &&
            h.hora_fin === horarioAEliminar.hora_fin
        );
        if (indiceGlobal !== -1) {
            horariosProgramados.splice(indiceGlobal, 1);
            calendario.actualizarEventos(prepararEventos());
        }
    }
}
```

## Personalización

### Cambiar Colores

Edita `calendario-styles.css` para cambiar los colores del tema:

```css
.fc-button {
    background-color: #1abc9c !important;
    border-color: #1abc9c !important;
}

.fc-button:hover {
    background-color: #16a085 !important;
}
```

### Agregar Nuevos Tipos de Eventos

1. Define los colores en tu código:

```javascript
const COLORES_EVENTOS = {
    mi_nuevo_tipo: {
        backgroundColor: '#color1',
        borderColor: '#color2',
        textColor: '#color3'
    }
};
```

2. Usa los colores al crear eventos:

```javascript
{
    ...COLORES_EVENTOS.mi_nuevo_tipo,
    extendedProps: {
        tipo: 'mi_nuevo_tipo',
        label: 'Mi Nuevo Tipo',
        icon: 'fa-mi-icono'
    }
}
```

## Notas Importantes

1. **Referencia Global**: Siempre guarda la instancia en `window.calendarioInstance` para acceso desde HTML.

2. **Domingos Bloqueados**: El calendario bloquea automáticamente los domingos (no se pueden seleccionar).

3. **Horario Laboral**: El calendario muestra horarios de 06:00 a 22:00.

4. **Responsive**: El calendario se adapta automáticamente al tamaño de la pantalla.

5. **Modales**: Los modales se crean dinámicamente y se destruyen al cerrar.

6. **Eventos Eliminables**: Solo los eventos con `eliminable: true` mostrarán el botón de eliminar.

## Solución de Problemas

### El calendario no se muestra
- Verifica que el elemento HTML existe: `document.getElementById('tuId')`
- Verifica que FullCalendar está cargado
- Revisa la consola del navegador para errores

### Los eventos no aparecen
- Verifica la estructura de los eventos
- Asegúrate de que las fechas están en formato ISO: `YYYY-MM-DDTHH:mm:ss`
- Revisa que `extendedProps` tiene todas las propiedades necesarias

### Los callbacks no funcionan
- Verifica que estás pasando funciones, no strings
- Asegúrate de que la referencia global `window.calendarioInstance` existe
- Revisa que los callbacks están definidos antes de inicializar el calendario

## Soporte

Para más información sobre FullCalendar, consulta: https://fullcalendar.io/docs
