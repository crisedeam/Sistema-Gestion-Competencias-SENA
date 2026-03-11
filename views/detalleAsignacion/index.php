<?php
$pageTitle = 'Detalles de Asignación';
$userRole = $_GET['role'] ?? 'coordinador';

ob_start();
?>

<div class="space-y-6">
    <!-- Header con botón de regresar -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-calendar-check text-primary mr-2"></i>
                    Detalles de Asignación
                </h2>
                <p class="text-gray-600 mt-2">Visualización completa de horarios programados</p>
            </div>
            <?php if ($userRole === 'instructor'): ?>
                <a href="<?php echo url('instructor', 'misAsignaciones', ['inst_id' => $_GET['inst_id'] ?? '1', 'role' => $userRole]); ?>" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition">
                    <i class="fas fa-arrow-left mr-2"></i>Volver
                </a>
            <?php else: ?>
                <a href="<?php echo url('asignacion', 'show', isset($_GET['coor_id']) ? ['coor_id' => $_GET['coor_id']] : []); ?>" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition">
                    <i class="fas fa-arrow-left mr-2"></i>Volver
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($asignacion)): ?>
        <!-- Información de la asignación -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Información General</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Instructor</label>
                    <p class="text-gray-800 font-medium"><?php echo isset($instructor) ? htmlspecialchars($instructor->getNombres() . ' ' . $instructor->getApellidos()) : 'N/A'; ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Ficha</label>
                    <p class="text-gray-800 font-medium">
                        <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm">
                            <?php echo isset($ficha) ? htmlspecialchars($ficha->getId()) : 'N/A'; ?>
                        </span>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Ambiente</label>
                    <p class="text-gray-800 font-medium"><?php echo isset($ambiente) ? htmlspecialchars($ambiente->getNombre()) : 'N/A'; ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Competencia</label>
                    <p class="text-gray-800 font-medium"><?php echo isset($competencia) ? htmlspecialchars($competencia->getNombreCorto()) : 'N/A'; ?></p>
                    <p class="text-sm text-gray-500"><?php echo isset($competencia) ? $competencia->getHoras() . ' horas totales' : ''; ?></p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Fecha Inicio</label>
                    <p class="text-gray-800 font-medium">
                        <i class="fas fa-calendar-alt text-primary mr-2"></i>
                        <?php echo date('d/m/Y', strtotime($asignacion->getFechaInicio())); ?>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Fecha Fin</label>
                    <p class="text-gray-800 font-medium">
                        <i class="fas fa-calendar-alt text-primary mr-2"></i>
                        <?php echo date('d/m/Y', strtotime($asignacion->getFechaFin())); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Calendario con FullCalendar -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Calendario de Clases</h3>
            
            <?php if (isset($detallesAsignacion) && count($detallesAsignacion) > 0): ?>
                <div id="calendario" class="border border-gray-200 rounded-lg overflow-hidden"></div>
                
                <!-- Resumen general -->
                <?php 
                $horasTotales = DetalleAsignacion::calcularHorasTotales($detallesAsignacion);
                $fechaInicio = new DateTime($asignacion->getFechaInicio());
                $fechaFin = new DateTime($asignacion->getFechaFin());
                $diferencia = $fechaFin->diff($fechaInicio);
                $diasTotales = $diferencia->days + 1;
                ?>
                <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Card Horas Totales -->
                    <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Horas Totales</p>
                                <p class="text-4xl font-bold text-primary"><?php echo $horasTotales; ?></p>
                            </div>
                            <div class="w-14 h-14 bg-primary bg-opacity-10 rounded-full flex items-center justify-center">
                                <i class="fas fa-clock text-2xl text-primary"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Card Clases Programadas -->
                    <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Clases Programadas</p>
                                <p class="text-4xl font-bold text-primary"><?php echo count($detallesAsignacion); ?></p>
                            </div>
                            <div class="w-14 h-14 bg-primary bg-opacity-10 rounded-full flex items-center justify-center">
                                <i class="fas fa-chalkboard-teacher text-2xl text-primary"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Card Días de Duración -->
                    <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Días de Duración</p>
                                <p class="text-4xl font-bold text-primary"><?php echo $diasTotales; ?></p>
                            </div>
                            <div class="w-14 h-14 bg-primary bg-opacity-10 rounded-full flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-2xl text-primary"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Card Estado -->
                    <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Estado</p>
                                <?php if ($horasTotales >= $competencia->getHoras()): ?>
                                    <p class="text-4xl font-bold text-green-600"><i class="fas fa-check-circle"></i></p>
                                    <p class="text-xs text-green-600 mt-1">Completo</p>
                                <?php else: ?>
                                    <p class="text-4xl font-bold text-red-600"><?php echo $competencia->getHoras() - $horasTotales; ?>h</p>
                                    <p class="text-xs text-red-600 mt-1">Faltan</p>
                                <?php endif; ?>
                            </div>
                            <div class="w-14 h-14 bg-<?php echo $horasTotales >= $competencia->getHoras() ? 'green' : 'red'; ?>-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-<?php echo $horasTotales >= $competencia->getHoras() ? 'check' : 'exclamation'; ?>-circle text-2xl text-<?php echo $horasTotales >= $competencia->getHoras() ? 'green' : 'red'; ?>-600"></i>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-calendar-times text-6xl mb-4 text-gray-300"></i>
                    <p class="text-lg font-medium">No hay horarios definidos para esta asignación</p>
                    <?php if ($userRole !== 'instructor'): ?>
                        <a href="<?php echo url('asignacion', 'updateshow', ['id' => $asignacion->getId()] + (isset($_GET['coor_id']) ? ['coor_id' => $_GET['coor_id']] : [])); ?>" 
                           class="inline-block mt-4 px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-hover transition">
                            <i class="fas fa-plus mr-2"></i>Agregar horarios
                        </a>
                    <?php else: ?>
                        <p class="text-sm text-gray-400 mt-2">Contacta al coordinador para programar horarios</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <i class="fas fa-exclamation-triangle text-6xl text-yellow-500 mb-4"></i>
            <p class="text-lg text-gray-600">No se encontró la asignación solicitada</p>
        </div>
    <?php endif; ?>
</div>

<?php if (isset($asignacion) && isset($detallesAsignacion) && count($detallesAsignacion) > 0): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendario');
    
    if (!calendarEl) {
        console.error('Elemento calendario no encontrado');
        return;
    }
    
    // Preparar eventos para FullCalendar
    const eventos = [
        <?php foreach ($detallesAsignacion as $detalle): ?>
        {
            title: '<?php echo substr($detalle->getHoraInicio(), 0, 5) . '-' . substr($detalle->getHoraFin(), 0, 5); ?>',
            start: '<?php echo $detalle->getFecha(); ?>T<?php echo $detalle->getHoraInicio(); ?>',
            end: '<?php echo $detalle->getFecha(); ?>T<?php echo $detalle->getHoraFin(); ?>',
            backgroundColor: '#d1fae5',
            borderColor: '#10b981',
            textColor: '#047857',
            display: 'block',
            extendedProps: {
                competencia: '<?php echo isset($competencia) ? addslashes($competencia->getNombreCorto()) : 'N/A'; ?>',
                ambiente: '<?php echo isset($ambiente) ? addslashes($ambiente->getNombre()) : 'N/A'; ?>',
                ficha: '<?php echo isset($ficha) ? addslashes($ficha->getId()) : 'N/A'; ?>',
                horaInicio: '<?php echo substr($detalle->getHoraInicio(), 0, 5); ?>',
                horaFin: '<?php echo substr($detalle->getHoraFin(), 0, 5); ?>'
            }
        },
        <?php endforeach; ?>
    ];
    
    if (eventos.length === 0) {
        console.warn('No hay eventos para mostrar en el calendario');
        return;
    }
    
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            day: 'Día',
            list: 'Lista'
        },
        height: 'auto',
        fixedWeekCount: false,
        showNonCurrentDates: false,
        slotMinTime: '06:00:00',
        slotMaxTime: '22:00:00',
        slotDuration: '01:00:00',
        allDaySlot: false,
        nowIndicator: true,
        businessHours: {
            daysOfWeek: [ 1, 2, 3, 4, 5, 6 ],
            startTime: '06:00',
            endTime: '22:00',
        },
        hiddenDays: [0],
        initialDate: '<?php echo $asignacion->getFechaInicio(); ?>',
        events: eventos,
        dayMaxEvents: 4,
        moreLinkText: function(num) {
            return '+' + num + ' más';
        },
        
        eventClick: function(info) {
            // Obtener todos los eventos del día
            const fechaEvento = info.event.startStr.split('T')[0];
            mostrarDetallesDia(fechaEvento);
            info.jsEvent.preventDefault();
        },
        
        eventDidMount: function(info) {
            info.el.classList.add('evento-calendario');
            const props = info.event.extendedProps;
            info.el.title = `${props.horaInicio}-${props.horaFin}\n${props.competencia}\nAmbiente: ${props.ambiente}`;
        },
        
        dayCellDidMount: function(info) {
            // No aplicar estilos inline - dejar que CSS maneje todo
        }
    });
    
    calendar.render();
    
    // Listener para redimensionar el calendario
    window.addEventListener('resize', function() {
        calendar.updateSize();
    });
    
    const mainContent = document.querySelector('main') || document.querySelector('.main-content');
    if (mainContent) {
        const resizeObserver = new ResizeObserver(function() {
            setTimeout(function() {
                calendar.updateSize();
            }, 300);
        });
        resizeObserver.observe(mainContent);
    }

    function mostrarDetallesDia(fecha) {
        const [year, month, day] = fecha.split('-');
        const fechaObj = new Date(year, month - 1, day);
        const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const fechaFormateada = fechaObj.toLocaleDateString('es-ES', opciones);

        // Filtrar eventos del día
        const eventosDelDia = eventos.filter(e => e.start.split('T')[0] === fecha);

        let contenido = `
        <div class="bg-gradient-to-r from-primary to-primary-hover p-6 -m-6 mb-6 rounded-t-lg">
            <div class="flex items-center text-white">
                <i class="fas fa-calendar-day text-3xl mr-4"></i>
                <div>
                    <h3 class="text-xl font-bold">${fechaFormateada}</h3>
                    <p class="text-sm opacity-90 mt-1">Clases programadas para este día</p>
                </div>
            </div>
        </div>`;

        if (eventosDelDia.length > 0) {
            // Ordenar eventos por hora
            eventosDelDia.sort((a, b) => {
                const horaA = a.extendedProps.horaInicio || a.start.split('T')[1];
                const horaB = b.extendedProps.horaInicio || b.start.split('T')[1];
                return horaA.localeCompare(horaB);
            });

            contenido += `
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Horario
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Competencia
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Ambiente
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Ficha
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">`;

            eventosDelDia.forEach((evento, idx) => {
                const props = evento.extendedProps;
                const rowBg = idx % 2 === 0 ? 'bg-white' : 'bg-gray-50';
                const horaInicio = props.horaInicio || evento.start.split('T')[1].substring(0, 5);
                const horaFin = props.horaFin || evento.end.split('T')[1].substring(0, 5);
                
                contenido += `
                <tr class="${rowBg} hover:bg-opacity-50 transition-colors duration-150" style="border-left: 4px solid ${evento.borderColor};">
                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full" style="background-color: ${evento.backgroundColor};">
                                <i class="fas fa-clock text-sm" style="color: ${evento.borderColor};"></i>
                            </div>
                            <div class="ml-3">
                                <div class="text-sm font-bold" style="color: ${evento.borderColor};">${horaInicio}</div>
                                <div class="text-xs text-gray-500">${horaFin}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-4">
                        <div class="text-sm font-medium text-gray-900">${props.competencia || 'N/A'}</div>
                    </td>
                    <td class="px-4 py-4">
                        <div class="text-sm text-gray-700">${props.ambiente || 'N/A'}</div>
                    </td>
                    <td class="px-4 py-4">
                        <div class="text-sm text-gray-700">${props.ficha || 'N/A'}</div>
                    </td>
                </tr>`;
            });

            contenido += `
                    </tbody>
                </table>
            </div>`;

            // Mostrar resumen de horas del día
            let horasTotalesDia = 0;
            eventosDelDia.forEach(evento => {
                const inicio = new Date(`2000-01-01T${evento.extendedProps.horaInicio}`);
                const fin = new Date(`2000-01-01T${evento.extendedProps.horaFin}`);
                horasTotalesDia += (fin - inicio) / (1000 * 60 * 60);
            });

            contenido += `
            <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        <span class="text-sm font-medium text-blue-800">Total de horas este día:</span>
                    </div>
                    <span class="text-lg font-bold text-blue-900">${horasTotalesDia}h</span>
                </div>
            </div>`;
        } else {
            contenido += `
            <div class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                <i class="fas fa-calendar-times text-5xl text-gray-300 mb-4"></i>
                <p class="text-lg font-medium text-gray-600">No hay clases programadas</p>
                <p class="text-sm text-gray-500 mt-2">Este día no tiene clases asignadas</p>
            </div>`;
        }

        mostrarModal(contenido);
    }
});

function mostrarModal(contenido) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[75vh] overflow-y-auto transform transition-all">
            <div class="p-6">
                ${contenido}
            </div>
            <div class="bg-gray-50 px-6 py-4 rounded-b-xl flex justify-end">
                <button onclick="this.closest('.fixed').remove()" 
                        class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition">
                    Cerrar
                </button>
            </div>
        </div>
    `;
    
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    });
    
    document.body.appendChild(modal);
}
</script>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/layout.php';
?>
