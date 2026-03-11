<?php 
$pageTitle = "Mis Horarios";
$userRole = $_GET['role'] ?? 'instructor';
$instructorId = $_GET['inst_id'] ?? 1;

// Obtener horarios reales de la base de datos
require_once __DIR__ . '/../../Database.php';
require_once __DIR__ . '/../../models/Competencia.php';
require_once __DIR__ . '/../../models/Ambiente.php';
require_once __DIR__ . '/../../models/Ficha.php';

// Calcular estadísticas
$totalClases = 0;
$totalHoras = 0;
$fichasUnicas = [];

if (isset($horarios) && count($horarios) > 0) {
    foreach ($horarios as $horario) {
        if (!empty($horario['det_fecha']) && !empty($horario['det_hora_inicio']) && !empty($horario['det_hora_fin'])) {
            $totalClases++;
            
            $horaInicio = strtotime($horario['det_hora_inicio']);
            $horaFin = strtotime($horario['det_hora_fin']);
            $totalHoras += ($horaFin - $horaInicio) / 3600;
            
            if (!in_array($horario['fich_id'], $fichasUnicas)) {
                $fichasUnicas[] = $horario['fich_id'];
            }
        }
    }
}

ob_start();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-calendar-week text-primary mr-2"></i>
            Mis Horarios
        </h2>
        <p class="text-gray-600 mt-2">Visualiza tu calendario de clases programadas</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Clases Totales</p>
                    <p class="text-4xl font-bold text-primary"><?php echo $totalClases; ?></p>
                </div>
                <div class="w-14 h-14 bg-primary bg-opacity-10 rounded-full flex items-center justify-center">
                    <i class="fas fa-chalkboard-teacher text-2xl text-primary"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Horas Totales</p>
                    <p class="text-4xl font-bold text-primary"><?php echo number_format($totalHoras, 0); ?></p>
                </div>
                <div class="w-14 h-14 bg-primary bg-opacity-10 rounded-full flex items-center justify-center">
                    <i class="fas fa-clock text-2xl text-primary"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Fichas Asignadas</p>
                    <p class="text-4xl font-bold text-primary"><?php echo count($fichasUnicas); ?></p>
                </div>
                <div class="w-14 h-14 bg-primary bg-opacity-10 rounded-full flex items-center justify-center">
                    <i class="fas fa-id-card text-2xl text-primary"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Promedio/Día</p>
                    <p class="text-4xl font-bold text-primary"><?php echo $totalClases > 0 ? number_format($totalHoras / $totalClases, 1) : 0; ?>h</p>
                </div>
                <div class="w-14 h-14 bg-primary bg-opacity-10 rounded-full flex items-center justify-center">
                    <i class="fas fa-chart-line text-2xl text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendario -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Calendario de Clases</h3>
        
        <?php if (isset($horarios) && count($horarios) > 0): ?>
            <div id="calendario" class="border border-gray-200 rounded-lg overflow-hidden"></div>
        <?php else: ?>
            <div class="p-12 text-center">
                <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-lg">No tienes horarios asignados</p>
                <p class="text-gray-400 text-sm mt-2">Los horarios aparecerán aquí cuando el coordinador los asigne</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($horarios) && count($horarios) > 0): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendario');
    
    // Preparar eventos para FullCalendar
    const eventos = [
        <?php foreach ($horarios as $horario): ?>
        <?php if (!empty($horario['det_fecha']) && !empty($horario['det_hora_inicio']) && !empty($horario['det_hora_fin'])): ?>
        <?php
            $competencia = Competencia::searchById($horario['comp_id']);
            $ambiente = Ambiente::searchById($horario['amb_id']);
        ?>
        {
            title: '<?php echo substr($horario['det_hora_inicio'], 0, 5) . '-' . substr($horario['det_hora_fin'], 0, 5); ?>',
            start: '<?php echo $horario['det_fecha']; ?>T<?php echo $horario['det_hora_inicio']; ?>',
            end: '<?php echo $horario['det_fecha']; ?>T<?php echo $horario['det_hora_fin']; ?>',
            backgroundColor: '#d1fae5',
            borderColor: '#10b981',
            textColor: '#047857',
            display: 'block',
            extendedProps: {
                competencia: '<?php echo $competencia ? addslashes($competencia->getNombreCorto()) : 'N/A'; ?>',
                ambiente: '<?php echo $ambiente ? addslashes($ambiente->getNombre()) : 'N/A'; ?>',
                ficha: '<?php echo addslashes($horario['fich_id']); ?>',
                horaInicio: '<?php echo substr($horario['det_hora_inicio'], 0, 5); ?>',
                horaFin: '<?php echo substr($horario['det_hora_fin'], 0, 5); ?>'
            }
        },
        <?php endif; ?>
        <?php endforeach; ?>
    ];
    
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
                    <p class="text-sm opacity-90 mt-1">Mis clases programadas para este día</p>
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
        } else {
            contenido += `
            <div class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                <i class="fas fa-calendar-times text-5xl text-gray-300 mb-4"></i>
                <p class="text-lg font-medium text-gray-600">No hay clases programadas</p>
                <p class="text-sm text-gray-500 mt-2">Este día no tienes clases asignadas</p>
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
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            modal.remove();
        }
    }, { once: true });
    
    document.body.appendChild(modal);
}
</script>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/layout.php';
?>
