<script>
    let horariosProgramados = [];
    let asignacionesAmbiente = [];
    let asignacionesInstructor = [];
    <?php if ($esEdicion): ?>
    let asignacionesFicha = [];
    <?php endif; ?>
    let fechaSeleccionadaModal = null;
    let jornadaActual = <?php echo $esEdicion ? "'" . ($ficha ? $ficha->getJornada() : 'Mixta') . "'" : 'null'; ?>;
    let calendar;

    <?php if ($esEdicion && isset($detallesAsignacion) && count($detallesAsignacion) > 0): ?>
    // Cargar horarios existentes de la asignación
    horariosProgramados = [
        <?php foreach ($detallesAsignacion as $detalle): ?>
        {
            fecha: '<?php echo $detalle->getFecha(); ?>',
            hora_inicio: '<?php echo substr($detalle->getHoraInicio(), 0, 5); ?>',
            hora_fin: '<?php echo substr($detalle->getHoraFin(), 0, 5); ?>'
        },
        <?php endforeach; ?>
    ];
    <?php endif; ?>

    // Inicializar
    document.addEventListener('DOMContentLoaded', async function () {
        <?php if ($esEdicion): ?>
        // Modo edición: cargar asignaciones existentes en paralelo
        await Promise.all([
            cargarAsignacionesFicha(),
            cargarAsignacionesInstructor(),
            cargarAsignacionesAmbiente()
        ]);

        actualizarHorariosData();
        actualizarProgreso();
        generarCalendario();
        
        // Event listeners para edición
        document.getElementById('sede_id_edit').addEventListener('change', filtrarAmbientesPorSedeEdit);
        document.getElementById('amb_id').addEventListener('change', cargarAsignacionesAmbiente);
        document.getElementById('asig_fecha_ini').addEventListener('change', actualizarCalendario);
        <?php else: ?>
        // Modo creación: mostrar mensaje inicial
        const calendarEl = document.getElementById('calendario');
        calendarEl.innerHTML = `
            <div class="flex flex-col items-center justify-center py-16 text-gray-500">
                <i class="fas fa-calendar-alt text-6xl mb-4 text-gray-300"></i>
                <p class="text-lg font-medium">Seleccione los datos básicos para comenzar</p>
                <p class="text-sm mt-2">Complete: Sede → Ambiente → Ficha → Competencia → Instructor → Fecha Inicio</p>
            </div>
        `;
        
        // Event listeners para creación
        document.getElementById('sede_id').addEventListener('change', cargarAmbientesPorSede);
        document.getElementById('amb_id').addEventListener('change', cargarAsignacionesAmbiente);
        document.getElementById('fich_id').addEventListener('change', function() {
            actualizarJornada();
            cargarCompetenciasPendientes();
        });
        document.getElementById('comp_id').addEventListener('change', function() {
            cargarInstructoresPorCompetencia();
            mostrarInfoCompetencia();
        });
        document.getElementById('inst_id').addEventListener('change', function() {
            cargarAsignacionesInstructor();
            habilitarFecha();
        });
        document.getElementById('asig_fecha_ini').addEventListener('change', actualizarCalendario);
        <?php endif; ?>
    });

    <?php if (!$esEdicion): ?>
    // ========== FUNCIONES SOLO PARA MODO CREACIÓN ==========
    
    async function cargarAmbientesPorSede() {
        const sedeId = document.getElementById('sede_id').value;
        const ambSelect = document.getElementById('amb_id');
        const fichSelect = document.getElementById('fich_id');

        ambSelect.innerHTML = '<option value="">Cargando ambientes...</option>';
        ambSelect.disabled = true;
        fichSelect.value = '';
        fichSelect.disabled = true;
        resetearDesdeFicha();

        if (!sedeId) {
            ambSelect.innerHTML = '<option value="">Seleccione primero una sede</option>';
            return;
        }

        try {
            const url = `<?php echo url('ambiente', 'getAmbientesPorSede'); ?>&sede_id=${sedeId}`;
            const response = await fetch(url);
            const data = await response.json();

            if (data.success && data.ambientes.length > 0) {
                ambSelect.innerHTML = '<option value="">Seleccione un ambiente</option>';
                data.ambientes.forEach(ambiente => {
                    const option = document.createElement('option');
                    option.value = ambiente.id;
                    option.textContent = ambiente.nombre;
                    ambSelect.appendChild(option);
                });
                ambSelect.disabled = false;
            } else {
                ambSelect.innerHTML = '<option value="">No hay ambientes en esta sede</option>';
            }
        } catch (error) {
            console.error('Error al cargar ambientes:', error);
            ambSelect.innerHTML = '<option value="">Error al cargar ambientes</option>';
        }
    }

    function resetearDesdeFicha() {
        const compSelect = document.getElementById('comp_id');
        compSelect.innerHTML = '<option value="">Seleccione primero una ficha</option>';
        compSelect.disabled = true;
        document.getElementById('mensajeCompetencias').classList.add('hidden');
        document.getElementById('infoCompetencia').classList.add('hidden');
        document.getElementById('infoJornada').classList.add('hidden');

        const instSelect = document.getElementById('inst_id');
        instSelect.innerHTML = '<option value="">Seleccione primero una competencia</option>';
        instSelect.disabled = true;
        document.getElementById('mensajeInstructores').classList.add('hidden');

        const fechaInput = document.getElementById('asig_fecha_ini');
        fechaInput.value = '';
        fechaInput.disabled = true;

        horariosProgramados = [];
        asignacionesInstructor = [];

        document.getElementById('resumenHoras').classList.add('hidden');

        actualizarHorariosData();
        generarCalendario();
    }

    function actualizarJornada() {
        const select = document.getElementById('fich_id');
        const info = document.getElementById('infoJornada');
        const jornadaTexto = document.getElementById('jornadaTexto');

        if (select.value) {
            const jornada = select.options[select.selectedIndex].dataset.jornada;
            jornadaActual = jornada;

            let horarios = '';
            switch (jornada) {
                case 'Mañana':
                    horarios = 'Horario recomendado: 6:00 - 12:00';
                    break;
                case 'Tarde':
                    horarios = 'Horario recomendado: 12:00 - 18:00';
                    break;
                case 'Noche':
                    horarios = 'Horario recomendado: 18:00 - 22:00';
                    break;
                case 'Mixta':
                    horarios = 'Horario flexible: cualquier horario permitido';
                    break;
                default:
                    horarios = 'Jornada no definida';
            }

            jornadaTexto.textContent = `Jornada ${jornada} - ${horarios}`;
            info.classList.remove('hidden');
        } else {
            info.classList.add('hidden');
            jornadaActual = null;
        }
    }

    async function cargarCompetenciasPendientes() {
        const fichaId = document.getElementById('fich_id').value;
        const compSelect = document.getElementById('comp_id');
        const mensajeDiv = document.getElementById('mensajeCompetencias');
        const textoMensaje = document.getElementById('textoMensaje');

        if (!fichaId) {
            compSelect.innerHTML = '<option value="">Seleccione primero una ficha</option>';
            compSelect.disabled = true;
            mensajeDiv.classList.add('hidden');
            resetearDesdeCompetencia();
            return;
        }

        actualizarJornada();

        try {
            const response = await fetch(`<?php echo url('asignacion', 'getCompetenciasDisponibles'); ?>&fich_id=${fichaId}`);
            const data = await response.json();

            if (data.success && data.competencias.length > 0) {
                compSelect.innerHTML = '<option value="">Seleccione una competencia</option>';
                data.competencias.forEach(comp => {
                    const option = document.createElement('option');
                    option.value = comp.id;
                    option.textContent = comp.nombre_corto;
                    option.setAttribute('data-horas', comp.horas);
                    compSelect.appendChild(option);
                });

                compSelect.disabled = false;
                mensajeDiv.classList.remove('hidden');
                textoMensaje.textContent = `${data.competencias.length} competencia(s) pendiente(s) para esta ficha`;
                textoMensaje.className = 'text-green-600';
            } else {
                compSelect.innerHTML = '<option value="">No hay competencias pendientes</option>';
                compSelect.disabled = true;
                mensajeDiv.classList.remove('hidden');
                textoMensaje.textContent = 'Todas las competencias del programa ya han sido asignadas';
                textoMensaje.className = 'text-orange-600';
            }

            resetearDesdeCompetencia();

        } catch (error) {
            console.error('Error al cargar competencias:', error);
            compSelect.innerHTML = '<option value="">Error al cargar competencias</option>';
            compSelect.disabled = true;
        }
    }

    function resetearDesdeCompetencia() {
        const instSelect = document.getElementById('inst_id');
        instSelect.innerHTML = '<option value="">Seleccione primero una competencia</option>';
        instSelect.disabled = true;
        document.getElementById('mensajeInstructores').classList.add('hidden');

        const fechaInput = document.getElementById('asig_fecha_ini');
        fechaInput.value = '';
        fechaInput.disabled = true;

        horariosProgramados = [];
        asignacionesInstructor = [];

        document.getElementById('resumenHoras').classList.add('hidden');
        document.getElementById('infoCompetencia').classList.add('hidden');

        actualizarHorariosData();
        generarCalendario();
    }

    async function cargarInstructoresPorCompetencia() {
        const competenciaId = document.getElementById('comp_id').value;
        const instSelect = document.getElementById('inst_id');
        const mensajeDiv = document.getElementById('mensajeInstructores');
        const textoMensaje = document.getElementById('textoMensajeInst');

        if (!competenciaId) {
            instSelect.innerHTML = '<option value="">Seleccione primero una competencia</option>';
            instSelect.disabled = true;
            mensajeDiv.classList.add('hidden');
            resetearDesdeInstructor();
            return;
        }

        try {
            const urlParams = new URLSearchParams(window.location.search);
            const coorId = urlParams.get('coor_id') || '';

            let url = `<?php echo url('asignacion', 'getInstructoresPorCompetencia'); ?>&comp_id=${competenciaId}`;
            if (coorId) url += `&coor_id=${coorId}`;

            const response = await fetch(url);
            const data = await response.json();

            if (data.success && data.instructores.length > 0) {
                instSelect.innerHTML = '<option value="">Seleccione un instructor</option>';
                data.instructores.forEach(instructor => {
                    const option = document.createElement('option');
                    option.value = instructor.id;
                    option.textContent = instructor.nombre_completo;
                    instSelect.appendChild(option);
                });

                instSelect.disabled = false;
                mensajeDiv.classList.remove('hidden');
                textoMensaje.textContent = `${data.instructores.length} instructor(es) certificado(s) para esta competencia`;
                textoMensaje.className = 'text-green-600';
            } else {
                instSelect.innerHTML = '<option value="">No hay instructores disponibles</option>';
                instSelect.disabled = true;
                mensajeDiv.classList.remove('hidden');
                textoMensaje.textContent = 'No hay instructores certificados para esta competencia';
                textoMensaje.className = 'text-orange-600';
            }

            resetearDesdeInstructor();

        } catch (error) {
            console.error('Error al cargar instructores:', error);
            instSelect.innerHTML = '<option value="">Error al cargar instructores</option>';
            instSelect.disabled = true;
        }
    }

    function resetearDesdeInstructor() {
        const fechaInput = document.getElementById('asig_fecha_ini');
        fechaInput.value = '';
        fechaInput.disabled = true;

        horariosProgramados = [];
        asignacionesInstructor = [];

        actualizarHorariosData();
        generarCalendario();
    }

    function habilitarFecha() {
        const instSelect = document.getElementById('inst_id');
        const fechaInput = document.getElementById('asig_fecha_ini');

        if (instSelect.value) {
            fechaInput.disabled = false;
        } else {
            fechaInput.disabled = true;
            fechaInput.value = '';
        }
    }

    function mostrarInfoCompetencia() {
        const select = document.getElementById('comp_id');
        const info = document.getElementById('infoCompetencia');
        const horasTexto = document.getElementById('horasCompetencia');

        if (select.value) {
            const horas = select.options[select.selectedIndex].dataset.horas;
            horasTexto.textContent = `Requiere ${horas} horas totales`;
            info.classList.remove('hidden');
            document.getElementById('horasRequeridas').textContent = horas;
            document.getElementById('resumenHoras').classList.remove('hidden');
            actualizarProgreso();
        } else {
            info.classList.add('hidden');
            document.getElementById('resumenHoras').classList.add('hidden');
        }
    }
    <?php endif; ?>

    <?php if ($esEdicion): ?>
    // ========== FUNCIONES SOLO PARA MODO EDICIÓN ==========
    
    function filtrarAmbientesPorSedeEdit() {
        const sedeId = document.getElementById('sede_id_edit').value;
        const ambSelect = document.getElementById('amb_id');
        const ambienteActualId = '<?php echo $asignacion->getAmbienteId(); ?>';
        
        if (!sedeId) {
            // Mostrar todos los ambientes si no hay sede seleccionada
            Array.from(ambSelect.options).forEach(option => {
                if (option.value) {
                    option.style.display = '';
                }
            });
            return;
        }
        
        // Filtrar ambientes por sede
        let hayOpciones = false;
        Array.from(ambSelect.options).forEach(option => {
            if (option.value) {
                const sedeDato = option.getAttribute('data-sede');
                if (sedeDato == sedeId) {
                    option.style.display = '';
                    hayOpciones = true;
                } else {
                    option.style.display = 'none';
                }
            }
        });
        
        // Si el ambiente actual no pertenece a la sede seleccionada, resetear
        const ambienteActualOption = ambSelect.querySelector(`option[value="${ambienteActualId}"]`);
        if (ambienteActualOption && ambienteActualOption.style.display === 'none') {
            ambSelect.value = '';
        }
        
        if (!hayOpciones) {
            showNotification('No hay ambientes disponibles en esta sede', 'warning');
        }
    }
    
    async function cargarAsignacionesFicha() {
        const fichaId = '<?php echo $asignacion->getFichaId(); ?>';
        const asignacionActualId = '<?php echo $asignacion->getId(); ?>';

        try {
            const response = await fetch(`<?php echo url('asignacion', 'getAsignacionesFicha'); ?>&fich_id=${fichaId}`);
            const data = await response.json();

            if (data.success) {
                asignacionesFicha = (data.asignaciones || []).filter(a => a.asignacion_id != asignacionActualId);
            } else {
                asignacionesFicha = [];
            }

        } catch (error) {
            console.error('Error al cargar asignaciones de la ficha:', error);
            asignacionesFicha = [];
        }
    }
    <?php endif; ?>

    // ========== FUNCIONES COMUNES ==========

    function actualizarCalendario() {
        const fechaInicio = document.getElementById('asig_fecha_ini').value;

        if (fechaInicio && calendar) {
            calendar.setOption('validRange', { start: fechaInicio });
            calendar.gotoDate(fechaInicio);
        } else {
            generarCalendario();
        }
    }

    async function cargarAsignacionesInstructor() {
        const instructorId = <?php echo $esEdicion ? "'" . $asignacion->getInstructorId() . "'" : 'document.getElementById("inst_id").value'; ?>;
        <?php if ($esEdicion): ?>
        const asignacionActualId = '<?php echo $asignacion->getId(); ?>';
        <?php endif; ?>

        if (!instructorId) {
            asignacionesInstructor = [];
            generarCalendario();
            return;
        }

        try {
            const response = await fetch(`<?php echo url('asignacion', 'getAsignacionesInstructor'); ?>&inst_id=${instructorId}`);
            const data = await response.json();

            if (data.success) {
                <?php if ($esEdicion): ?>
                asignacionesInstructor = (data.asignaciones || []).filter(a => a.asignacion_id != asignacionActualId);
                <?php else: ?>
                asignacionesInstructor = data.asignaciones || [];
                <?php endif; ?>
            } else {
                asignacionesInstructor = [];
            }

            generarCalendario();

        } catch (error) {
            console.error('Error al cargar asignaciones del instructor:', error);
            asignacionesInstructor = [];
            generarCalendario();
        }
    }

    async function cargarAsignacionesAmbiente() {
        const ambienteId = document.getElementById('amb_id').value;
        <?php if ($esEdicion): ?>
        const asignacionActualId = '<?php echo $asignacion->getId(); ?>';
        <?php else: ?>
        const fichSelect = document.getElementById('fich_id');
        <?php endif; ?>

        if (!ambienteId) {
            asignacionesAmbiente = [];
            <?php if (!$esEdicion): ?>
            fichSelect.disabled = true;
            resetearDesdeFicha();
            <?php endif; ?>
            generarCalendario();
            return;
        }

        try {
            const response = await fetch(`<?php echo url('asignacion', 'getAsignacionesAmbiente'); ?>&amb_id=${ambienteId}`);
            const data = await response.json();

            if (data.success) {
                <?php if ($esEdicion): ?>
                asignacionesAmbiente = (data.asignaciones || []).filter(a => a.asignacion_id != asignacionActualId);
                <?php else: ?>
                asignacionesAmbiente = data.asignaciones || [];
                <?php endif; ?>
            } else {
                asignacionesAmbiente = [];
            }

            <?php if (!$esEdicion): ?>
            fichSelect.disabled = false;
            if (fichSelect.options[0].value === '') {
                fichSelect.options[0].text = 'Seleccione una ficha';
            }
            <?php endif; ?>

            generarCalendario();

        } catch (error) {
            console.error('Error al cargar asignaciones del ambiente:', error);
            asignacionesAmbiente = [];
            generarCalendario();
        }
    }

    function generarCalendario() {
        const calendarEl = document.getElementById('calendario');
        
        const fechaInicio = document.getElementById('asig_fecha_ini').value;
        
        <?php if (!$esEdicion): ?>
        if (!fechaInicio) {
            calendarEl.innerHTML = `
                <div class="flex flex-col items-center justify-center py-16 text-gray-500">
                    <i class="fas fa-calendar-alt text-6xl mb-4 text-gray-300"></i>
                    <p class="text-lg font-medium">Seleccione la fecha de inicio para ver el calendario</p>
                    <p class="text-sm mt-2 text-gray-400">Complete todos los campos anteriores primero</p>
                </div>
            `;
            return;
        }
        <?php endif; ?>
        
        if (calendar) {
            calendar.destroy();
        }

        let validRange = {};
        if (fechaInicio) {
            validRange.start = fechaInicio;
        }

        // Agrupar asignaciones por fecha y horario para detectar combinaciones
        const asignacionesPorDia = {};

        asignacionesInstructor.forEach(a => {
            const key = `${a.fecha}_${a.hora_inicio}_${a.hora_fin}`;
            if (!asignacionesPorDia[key]) {
                asignacionesPorDia[key] = {
                    fecha: a.fecha,
                    hora_inicio: a.hora_inicio,
                    hora_fin: a.hora_fin,
                    instructor: null,
                    ambiente: null
                };
            }
            asignacionesPorDia[key].instructor = a;
        });

        asignacionesAmbiente.forEach(a => {
            const key = `${a.fecha}_${a.hora_inicio}_${a.hora_fin}`;
            if (!asignacionesPorDia[key]) {
                asignacionesPorDia[key] = {
                    fecha: a.fecha,
                    hora_inicio: a.hora_inicio,
                    hora_fin: a.hora_fin,
                    instructor: null,
                    ambiente: null
                };
            }
            asignacionesPorDia[key].ambiente = a;
        });

        // Crear eventos combinando cuando sea necesario
        const eventos = [];

        Object.values(asignacionesPorDia).forEach(grupo => {
            if (grupo.instructor && grupo.ambiente && 
                grupo.instructor.ficha === grupo.ambiente.ficha) {
                eventos.push({
                    title: `${grupo.instructor.hora_inicio.substring(0,5)}-${grupo.instructor.hora_fin.substring(0,5)}`,
                    start: `${grupo.fecha}T${grupo.instructor.hora_inicio}`,
                    end: `${grupo.fecha}T${grupo.instructor.hora_fin}`,
                    backgroundColor: '#e9d5ff',
                    borderColor: '#a855f7',
                    textColor: '#6b21a8',
                    display: 'block',
                    extendedProps: { 
                        tipo: 'combinado',
                        ficha: grupo.instructor.ficha,
                        competencia: grupo.instructor.competencia,
                        horaTexto: `${grupo.instructor.hora_inicio.substring(0,5)}-${grupo.instructor.hora_fin.substring(0,5)}`
                    }
                });
            } else {
                if (grupo.ambiente) {
                    eventos.push({
                        title: `${grupo.ambiente.hora_inicio.substring(0,5)}-${grupo.ambiente.hora_fin.substring(0,5)}`,
                        start: `${grupo.fecha}T${grupo.ambiente.hora_inicio}`,
                        end: `${grupo.fecha}T${grupo.ambiente.hora_fin}`,
                        backgroundColor: '#dbeafe',
                        borderColor: '#3b82f6',
                        textColor: '#1e40af',
                        display: 'block',
                        extendedProps: { 
                            tipo: 'ambiente',
                            ficha: grupo.ambiente.ficha,
                            competencia: grupo.ambiente.competencia,
                            horaTexto: `${grupo.ambiente.hora_inicio.substring(0,5)}-${grupo.ambiente.hora_fin.substring(0,5)}`
                        }
                    });
                }
                if (grupo.instructor) {
                    eventos.push({
                        title: `${grupo.instructor.hora_inicio.substring(0,5)}-${grupo.instructor.hora_fin.substring(0,5)}`,
                        start: `${grupo.fecha}T${grupo.instructor.hora_inicio}`,
                        end: `${grupo.fecha}T${grupo.instructor.hora_fin}`,
                        backgroundColor: '#fef3c7',
                        borderColor: '#f59e0b',
                        textColor: '#92400e',
                        display: 'block',
                        extendedProps: { 
                            tipo: 'instructor',
                            ficha: grupo.instructor.ficha,
                            competencia: grupo.instructor.competencia,
                            horaTexto: `${grupo.instructor.hora_inicio.substring(0,5)}-${grupo.instructor.hora_fin.substring(0,5)}`
                        }
                    });
                }
            }
        });
        
        // Agregar horarios programados
        horariosProgramados.forEach((h, index) => {
             eventos.push({
                title: `${h.hora_inicio.substring(0,5)}-${h.hora_fin.substring(0,5)}`,
                start: `${h.fecha}T${h.hora_inicio}`,
                end: `${h.fecha}T${h.hora_fin}`,
                backgroundColor: '#d1fae5',
                borderColor: '#10b981',
                textColor: '#047857',
                display: 'block',
                extendedProps: { 
                    tipo: <?php echo $esEdicion ? "'esta_asignacion'" : "'nueva_asignacion'"; ?>,
                    index: index,
                    horaTexto: `${h.hora_inicio.substring(0,5)}-${h.hora_fin.substring(0,5)}`
                }
            });
        });

        // Inicializar FullCalendar
        calendar = new FullCalendar.Calendar(calendarEl, {
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
            navLinks: false,
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
            validRange: validRange,
            initialDate: fechaInicio || new Date(),
            events: eventos,
            
            dayMaxEvents: 4,
            moreLinkText: function(num) {
                return '+' + num + ' más';
            },
            
            selectable: false,
            
            dateClick: function(info) {
                 const fechaSeleccionada = info.dateStr;
                 
                 if (fechaInicio && info.date < new Date(fechaInicio + 'T00:00:00')) {
                     return; 
                 }
                 
                 if (info.date.getDay() === 0) {
                     return;
                 }
                 
                 const eventosDelDia = eventos.filter(e => e.start.split('T')[0] === fechaSeleccionada);
                 
                 if (eventosDelDia.length > 0) {
                     mostrarDetallesDia(fechaSeleccionada);
                 } else {
                     abrirModalHorario(fechaSeleccionada);
                 }
            },
            
            eventClick: function(arg) {
                 const evento = arg.event;
                 const fechaE = evento.startStr.split('T')[0];
                 
                 mostrarDetallesDia(fechaE);
                 
                 arg.jsEvent.preventDefault();
            },
            
            moreLinkClick: function(info) {
                const fechaE = info.date.toISOString().split('T')[0];
                mostrarDetallesDia(fechaE);
                return 'popover';
            },
            
            eventDidMount: function(info) {
                 info.el.classList.add('evento-calendario');
                 
                 if (info.el) {
                     const props = info.event.extendedProps;
                     let tooltipText = `${props.horaTexto}`;
                     if (props.competencia) {
                         tooltipText += `\n${props.competencia}`;
                     }
                     if (props.ficha) {
                         tooltipText += `\nFicha: ${props.ficha}`;
                     }
                     info.el.title = tooltipText;
                 }
            },
            
            dayCellDidMount: function(info) {
                const hoy = new Date();
                hoy.setHours(0, 0, 0, 0);
                const cellDate = new Date(info.date);
                cellDate.setHours(0, 0, 0, 0);
                
                if (cellDate.getTime() === hoy.getTime()) {
                    info.el.style.backgroundColor = '#eff6ff';
                }
                
                info.el.style.minHeight = '120px';
            }
        });

        calendar.render();
        
        // Listener para redimensionar el calendario cuando cambia el tamaño de la ventana o se oculta el menú
        window.addEventListener('resize', function() {
            if (calendar) {
                calendar.updateSize();
            }
        });
        
        // Observer para detectar cambios en el sidebar (cuando se oculta/muestra el menú)
        const mainContent = document.querySelector('main') || document.querySelector('.main-content');
        if (mainContent) {
            const resizeObserver = new ResizeObserver(function() {
                if (calendar) {
                    setTimeout(function() {
                        calendar.updateSize();
                    }, 300); // Esperar a que termine la animación del sidebar
                }
            });
            resizeObserver.observe(mainContent);
        }
    }

    function abrirModalHorario(fecha) {
        fechaSeleccionadaModal = fecha;
        const [year, month, day] = fecha.split('-');
        const fechaObj = new Date(year, month - 1, day);
        const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('fechaSeleccionada').textContent =
            fechaObj.toLocaleDateString('es-ES', opciones);

        document.getElementById('modalHorario').classList.remove('hidden');
    }

    function cerrarModal() {
        document.getElementById('modalHorario').classList.add('hidden');
        fechaSeleccionadaModal = null;
    }

    function guardarHorario() {
        const horaInicio = document.getElementById('horaInicio').value;
        const horaFin = document.getElementById('horaFin').value;

        if (horaInicio >= horaFin) {
            mostrarAlerta('La hora de fin debe ser posterior a la hora de inicio', 'warning');
            return;
        }

        // VALIDACIÓN: Verificar que no se excedan las horas de la competencia
        const horasRequeridas = parseInt(document.getElementById('horasRequeridas').textContent) || 0;
        let horasProgramadas = 0;

        horariosProgramados.forEach(horario => {
            const inicio = new Date(`2000-01-01T${horario.hora_inicio}`);
            const fin = new Date(`2000-01-01T${horario.hora_fin}`);
            horasProgramadas += (fin - inicio) / (1000 * 60 * 60);
        });

        // Calcular horas del nuevo horario
        const inicioNuevo = new Date(`2000-01-01T${horaInicio}`);
        const finNuevo = new Date(`2000-01-01T${horaFin}`);
        const horasNuevas = (finNuevo - inicioNuevo) / (1000 * 60 * 60);
        const totalConNuevo = horasProgramadas + horasNuevas;

        if (totalConNuevo > horasRequeridas) {
            const exceso = totalConNuevo - horasRequeridas;
            mostrarAlerta(
                `NO SE PUEDE PROGRAMAR: Se excederían las horas de la competencia.\n\n` +
                `Horas requeridas: ${horasRequeridas}h\n` +
                `Horas ya programadas: ${horasProgramadas}h\n` +
                `Horas del nuevo horario: ${horasNuevas}h\n` +
                `Total: ${totalConNuevo}h\n` +
                `Exceso: ${exceso}h\n\n` +
                `Para agregar este horario, primero debe desasignar ${exceso}h de otros días o reducir las horas de este horario.`,
                'error'
            );
            return;
        }

        const conflicto = horariosProgramados.find(h =>
            h.fecha === fechaSeleccionadaModal &&
            ((horaInicio < h.hora_fin && horaFin > h.hora_inicio))
        );

        if (conflicto) {
            mostrarAlerta('Ya hay una clase programada en ese horario para esta asignación', 'warning');
            return;
        }

        const conflictoAmbiente = asignacionesAmbiente.find(a =>
            a.fecha === fechaSeleccionadaModal &&
            ((horaInicio < a.hora_fin && horaFin > a.hora_inicio))
        );

        if (conflictoAmbiente) {
            mostrarAlerta(
                `NO SE PUEDE PROGRAMAR: El ambiente está ocupado en este horario.\n\n` +
                `Conflicto: ${conflictoAmbiente.hora_inicio} - ${conflictoAmbiente.hora_fin}\n` +
                `Ficha: ${conflictoAmbiente.ficha}\n` +
                `Competencia: ${conflictoAmbiente.competencia}\n\n` +
                `Por favor, seleccione otro horario.`,
                'error'
            );
            return;
        }

        // VALIDACIÓN: Verificar conflictos de instructor
        const conflictoInstructor = asignacionesInstructor.find(a =>
            a.fecha === fechaSeleccionadaModal &&
            ((horaInicio < a.hora_fin && horaFin > a.hora_inicio))
        );

        if (conflictoInstructor) {
            mostrarAlerta(
                `NO SE PUEDE PROGRAMAR: El instructor está ocupado en este horario.\n\n` +
                `Conflicto: ${conflictoInstructor.hora_inicio} - ${conflictoInstructor.hora_fin}\n` +
                `Ficha: ${conflictoInstructor.ficha}\n` +
                `Competencia: ${conflictoInstructor.competencia}\n` +
                `Ambiente: ${conflictoInstructor.ambiente}\n\n` +
                `Por favor, seleccione otro horario.`,
                'error'
            );
            return;
        }

        if (jornadaActual && !validarHorarioJornada(horaInicio, horaFin, jornadaActual)) {
            mostrarConfirmacion(
                `El horario ${horaInicio}-${horaFin} no corresponde a la jornada ${jornadaActual}.\n\n` +
                `¿Desea continuar de todas formas?`
            ).then(confirmar => {
                if (confirmar) {
                    agregarHorarioAlCalendario(fechaSeleccionadaModal, horaInicio, horaFin);
                }
            });
            return;
        }

        agregarHorarioAlCalendario(fechaSeleccionadaModal, horaInicio, horaFin);
    }

    function agregarHorarioAlCalendario(fecha, horaInicio, horaFin) {
        horariosProgramados.push({
            fecha: <?php echo $esEdicion ? 'fechaSeleccionadaModal' : 'fecha'; ?>,
            hora_inicio: horaInicio,
            hora_fin: horaFin
        });

        actualizarHorariosData();
        actualizarProgreso();
        generarCalendario();
        cerrarModal();
    }

    function validarHorarioJornada(horaInicio, horaFin, jornada) {
        const inicio = parseInt(horaInicio.split(':')[0]);
        const fin = parseInt(horaFin.split(':')[0]);

        switch (jornada) {
            case 'Mañana':
                return inicio >= 6 && fin <= 12;
            case 'Tarde':
                return inicio >= 12 && fin <= 18;
            case 'Noche':
                return inicio >= 18 && fin <= 22;
            case 'Mixta':
                return true;
            default:
                return true;
        }
    }

    function actualizarHorariosData() {
        document.getElementById('horariosData').value = JSON.stringify(horariosProgramados);
    }

    function actualizarProgreso() {
        const horasRequeridas = parseInt(document.getElementById('horasRequeridas').textContent) || 0;
        let horasProgramadas = 0;

        horariosProgramados.forEach(horario => {
            const inicio = new Date(`2000-01-01T${horario.hora_inicio}`);
            const fin = new Date(`2000-01-01T${horario.hora_fin}`);
            horasProgramadas += (fin - inicio) / (1000 * 60 * 60);
        });

        document.getElementById('horasProgramadas').textContent = horasProgramadas;

        const porcentaje = horasRequeridas > 0 ? (horasProgramadas / horasRequeridas) * 100 : 0;
        document.getElementById('barraProgreso').style.width = Math.min(porcentaje, 100) + '%';

        <?php if ($esEdicion): ?>
        const estado = document.getElementById('estadoProgreso');
        if (horasProgramadas >= horasRequeridas) {
            estado.textContent = '✓ Todas las horas programadas';
            estado.className = 'mt-2 text-xs text-green-600';
        } else {
            const faltantes = horasRequeridas - horasProgramadas;
            estado.textContent = `Faltan ${faltantes} horas por programar`;
            estado.className = 'mt-2 text-xs text-red-600';
        }
        <?php else: ?>
        const minimoRequerido = Math.ceil(horasRequeridas * 0.8);
        const estado = document.getElementById('estadoProgreso');

        if (horasProgramadas >= horasRequeridas) {
            estado.textContent = '✓ Todas las horas programadas (100%)';
            estado.className = 'text-xs text-green-600 font-medium';
        } else if (horasProgramadas >= minimoRequerido) {
            const porcentajeActual = Math.round(porcentaje);
            estado.textContent = `✓ Mínimo alcanzado (${porcentajeActual}% - Faltan ${horasRequeridas - horasProgramadas}h para completar)`;
            estado.className = 'text-xs text-blue-600 font-medium';
        } else {
            const faltantes = minimoRequerido - horasProgramadas;
            estado.textContent = `⚠ Faltan ${faltantes}h para alcanzar el mínimo requerido (80%)`;
            estado.className = 'text-xs text-red-600 font-medium';
        }
        <?php endif; ?>
    }

    function mostrarDetallesDia(fecha) {
        const [year, month, day] = fecha.split('-');
        const fechaObj = new Date(year, month - 1, day);
        const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const fechaFormateada = fechaObj.toLocaleDateString('es-ES', opciones);

        const asignacionesInstructorDelDia = asignacionesInstructor.filter(a => a.fecha === fecha);
        const asignacionesAmbienteDelDia = asignacionesAmbiente.filter(a => a.fecha === fecha);
        const horariosDelDia = horariosProgramados.filter(h => h.fecha === fecha);

        let contenido = `
        <div class="bg-gradient-to-r from-primary to-primary-hover p-6 -m-6 mb-6 rounded-t-lg">
            <div class="flex items-center text-white">
                <i class="fas fa-calendar-day text-3xl mr-4"></i>
                <div>
                    <h3 class="text-xl font-bold">${fechaFormateada}</h3>
                    <p class="text-sm opacity-90 mt-1">Horarios programados para este día</p>
                </div>
            </div>
        </div>`;

        const todasAsignaciones = [];
        const horariosAgrupados = {};

        asignacionesInstructorDelDia.forEach(a => {
            const key = `${a.hora_inicio}-${a.hora_fin}`;
            if (!horariosAgrupados[key]) {
                horariosAgrupados[key] = { instructor: null, ambiente: null };
            }
            horariosAgrupados[key].instructor = a;
        });

        asignacionesAmbienteDelDia.forEach(a => {
            const key = `${a.hora_inicio}-${a.hora_fin}`;
            if (!horariosAgrupados[key]) {
                horariosAgrupados[key] = { instructor: null, ambiente: null };
            }
            horariosAgrupados[key].ambiente = a;
        });

        Object.keys(horariosAgrupados).forEach(key => {
            const grupo = horariosAgrupados[key];

            if (grupo.instructor && grupo.ambiente && grupo.instructor.ficha === grupo.ambiente.ficha) {
                todasAsignaciones.push({
                    hora_inicio: grupo.instructor.hora_inicio,
                    hora_fin: grupo.instructor.hora_fin,
                    tipo: 'combinado',
                    competencia: grupo.instructor.competencia,
                    ficha: grupo.instructor.ficha || 'N/A',
                    color: '#e9d5ff',
                    borderColor: '#a855f7',
                    textColor: 'text-purple-900',
                    label: 'Ambiente/Instructor',
                    icon: 'fa-layer-group'
                });
            } else {
                if (grupo.ambiente) {
                    todasAsignaciones.push({
                        hora_inicio: grupo.ambiente.hora_inicio,
                        hora_fin: grupo.ambiente.hora_fin,
                        tipo: 'ambiente',
                        competencia: grupo.ambiente.competencia,
                        ficha: grupo.ambiente.ficha || 'N/A',
                        color: '#dbeafe',
                        borderColor: '#3b82f6',
                        textColor: 'text-blue-900',
                        label: 'Ambiente',
                        icon: 'fa-door-open'
                    });
                }
                if (grupo.instructor) {
                    todasAsignaciones.push({
                        hora_inicio: grupo.instructor.hora_inicio,
                        hora_fin: grupo.instructor.hora_fin,
                        tipo: 'instructor',
                        competencia: grupo.instructor.competencia,
                        ficha: grupo.instructor.ficha || 'N/A',
                        color: '#fef3c7',
                        borderColor: '#f59e0b',
                        textColor: 'text-amber-900',
                        label: 'Instructor',
                        icon: 'fa-chalkboard-teacher'
                    });
                }
            }
        });

        <?php if ($esEdicion): ?>
        const competenciaNombre = '<?php echo $competencia ? $competencia->getNombreCorto() : "N/A"; ?>';
        const fichaNombre = '<?php echo $ficha ? $ficha->getId() . " - " . $ficha->getJornada() : "N/A"; ?>';
        <?php else: ?>
        const compSelect = document.getElementById('comp_id');
        const fichSelect = document.getElementById('fich_id');
        const competenciaNombre = compSelect.options[compSelect.selectedIndex]?.text || 'N/A';
        const fichaNombre = fichSelect.options[fichSelect.selectedIndex]?.text || 'N/A';
        <?php endif; ?>

        horariosDelDia.forEach((h, index) => {
            todasAsignaciones.push({
                hora_inicio: h.hora_inicio,
                hora_fin: h.hora_fin,
                tipo: <?php echo $esEdicion ? "'esta_asignacion'" : "'nueva_asignacion'"; ?>,
                competencia: competenciaNombre,
                ficha: fichaNombre,
                color: '#d1fae5',
                borderColor: '#10b981',
                textColor: 'text-green-900',
                label: <?php echo $esEdicion ? "'Esta Asignación'" : "'Nueva Asignación'"; ?>,
                icon: <?php echo $esEdicion ? "'fa-edit'" : "'fa-plus-circle'"; ?>,
                index: index,
                eliminable: true
            });
        });

        todasAsignaciones.sort((a, b) => {
            if (a.hora_inicio < b.hora_inicio) return -1;
            if (a.hora_inicio > b.hora_inicio) return 1;
            return 0;
        });

        if (todasAsignaciones.length > 0) {
            contenido += `
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Horario
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Tipo
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Competencia
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Ficha
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Acción
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">`;

            todasAsignaciones.forEach((asig, idx) => {
                const rowBg = idx % 2 === 0 ? 'bg-white' : 'bg-gray-50';
                contenido += `
                <tr class="${rowBg} hover:bg-${asig.color.replace('#', '')}10 transition-colors duration-150" style="border-left: 4px solid ${asig.borderColor};">
                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full" style="background-color: ${asig.color};">
                                <i class="fas fa-clock ${asig.textColor} text-sm"></i>
                            </div>
                            <div class="ml-3">
                                <div class="text-sm font-bold ${asig.textColor}">${asig.hora_inicio}</div>
                                <div class="text-xs text-gray-500">${asig.hora_fin}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold" style="background-color: ${asig.color}; color: ${asig.borderColor};">
                            <i class="fas ${asig.icon} mr-2"></i>
                            ${asig.label}
                        </span>
                    </td>
                    <td class="px-4 py-4">
                        <div class="text-sm font-medium text-gray-900">${asig.competencia}</div>
                    </td>
                    <td class="px-4 py-4">
                        <div class="text-sm text-gray-700">${asig.ficha}</div>
                    </td>
                    <td class="px-4 py-4 text-center">
                        ${asig.eliminable ? `
                            <button type="button" onclick="eliminarHorario('${fecha}', ${asig.index})" 
                                    class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-150" 
                                    title="Eliminar">
                                <i class="fas fa-trash mr-1"></i>
                                Eliminar
                            </button>
                        ` : '<span class="text-gray-400 text-xs italic">No editable</span>'}
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
                <p class="text-lg font-medium text-gray-600">No hay asignaciones programadas</p>
                <p class="text-sm text-gray-500 mt-2">Este día está disponible para programar clases</p>
            </div>`;
        }

        const fechaInicioStr = document.getElementById('asig_fecha_ini').value;
        if (fechaInicioStr) {
            const [yearInicio, monthInicio, dayInicio] = fechaInicioStr.split('-');
            const fechaInicioObj = new Date(yearInicio, monthInicio - 1, dayInicio);
            const diaSemana = fechaObj.getDay();

            let horasOcupadas = 0;
            todasAsignaciones.forEach(asig => {
                const inicio = new Date(`2000-01-01T${asig.hora_inicio}`);
                const fin = new Date(`2000-01-01T${asig.hora_fin}`);
                horasOcupadas += (fin - inicio) / (1000 * 60 * 60);
            });

            if (fechaObj >= fechaInicioObj && diaSemana !== 0 && horasOcupadas < 16) {
                contenido += `
                <div class="mt-6 pt-6 border-t-2 border-gray-200">
                    <button type="button" onclick="cerrarModalDetalles(); abrirModalHorario('${fecha}')" 
                            class="w-full flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-gradient-to-r from-primary to-primary-hover hover:from-primary-hover hover:to-primary shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-150">
                        <i class="fas fa-plus-circle mr-2 text-xl"></i>
                        Agregar Clase en este Día
                    </button>
                </div>`;
            } else if (horasOcupadas >= 16) {
                contenido += `
                <div class="mt-6 pt-6 border-t-2 border-gray-200">
                    <div class="text-center py-3 bg-gray-100 rounded-lg border border-gray-300">
                        <i class="fas fa-info-circle text-gray-500 mr-2"></i>
                        <span class="text-gray-600 font-medium">Este día ya tiene todas las horas ocupadas</span>
                    </div>
                </div>`;
            }
        }

        document.getElementById('detallesDiaContenido').innerHTML = contenido;
        document.getElementById('modalDetallesDia').classList.remove('hidden');
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
                actualizarHorariosData();
                actualizarProgreso();
                generarCalendario();
                cerrarModalDetalles();
            }
        }
    }

    function cerrarModalDetalles() {
        document.getElementById('modalDetallesDia').classList.add('hidden');
    }

    // ========== FUNCIONES DE MODALES PERSONALIZADOS ==========
    
    function mostrarAlerta(mensaje, tipo = 'error') {
        const modal = document.getElementById('modalAlerta');
        const icono = document.getElementById('iconoAlerta');
        const titulo = document.getElementById('tituloAlerta');
        const mensajeEl = document.getElementById('mensajeAlerta');

        if (tipo === 'error') {
            icono.className = 'fas fa-exclamation-circle text-3xl text-red-500';
            titulo.textContent = 'Error';
        } else if (tipo === 'warning') {
            icono.className = 'fas fa-exclamation-triangle text-3xl text-yellow-500';
            titulo.textContent = 'Advertencia';
        } else {
            icono.className = 'fas fa-info-circle text-3xl text-blue-500';
            titulo.textContent = 'Información';
        }

        mensajeEl.textContent = mensaje;
        modal.classList.remove('hidden');
    }

    function cerrarModalAlerta() {
        document.getElementById('modalAlerta').classList.add('hidden');
    }

    let confirmacionCallback = null;

    function mostrarConfirmacion(mensaje) {
        return new Promise((resolve) => {
            const modal = document.getElementById('modalConfirmacion');
            const mensajeEl = document.getElementById('mensajeConfirmacion');

            mensajeEl.textContent = mensaje;
            modal.classList.remove('hidden');

            confirmacionCallback = resolve;
        });
    }

    function cerrarModalConfirmacion(resultado) {
        document.getElementById('modalConfirmacion').classList.add('hidden');
        if (confirmacionCallback) {
            confirmacionCallback(resultado);
            confirmacionCallback = null;
        }
    }

    // ========== VALIDACIÓN DEL FORMULARIO ==========
    
    document.getElementById('formAsignacion').addEventListener('submit', async function (e) {
        e.preventDefault();

        const horasRequeridas = parseInt(document.getElementById('horasRequeridas').textContent) || 0;
        const horasProgramadas = parseInt(document.getElementById('horasProgramadas').textContent) || 0;

        <?php if ($esEdicion): ?>
        if (horasProgramadas < horasRequeridas) {
            mostrarAlerta(`Debe programar todas las horas de la competencia. Faltan ${horasRequeridas - horasProgramadas} horas.`, 'error');
            return;
        }
        <?php else: ?>
        const minimoRequerido = Math.ceil(horasRequeridas * 0.8);

        if (horasProgramadas < minimoRequerido) {
            mostrarAlerta(
                `Debe programar al menos el 80% de las horas de la competencia.\n\n` +
                `Mínimo requerido: ${minimoRequerido} horas\n` +
                `Horas programadas: ${horasProgramadas} horas\n` +
                `Faltan: ${minimoRequerido - horasProgramadas} horas`,
                'error'
            );
            return;
        } else if (horasProgramadas < horasRequeridas) {
            const porcentaje = Math.round((horasProgramadas / horasRequeridas) * 100);
            const confirmar = await mostrarConfirmacion(
                `Ha programado ${horasProgramadas} de ${horasRequeridas} horas (${porcentaje}%).\n\n` +
                `¿Desea continuar con esta asignación incompleta?`
            );

            if (!confirmar) {
                return;
            }
        }
        <?php endif; ?>

        this.submit();
    });
</script>
